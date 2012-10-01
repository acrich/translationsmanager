<?php

class Wheelbarrow_Translator_Model_Translate extends Mage_Core_Model_Translate
{

	/**
	 * Retrieve DB resource model
	 *
	 * @return unknown
	 */
	public function getResource()
	{
		return Mage::getResourceSingleton('translator/string');
	}

    /**
     * Initialization translation data
     *
     * @param   string $area
     * @return  Wheelbarrow_Translator_Model_Translate
     */
    public function init($area, $forceReload = false)
    {
    	$this->setConfig(array(parent::CONFIG_KEY_AREA=>$area));
    
    	$this->_translateInline = Mage::getSingleton('core/translate_inline')
    		->isAllowed($area=='adminhtml' ? 'admin' : null);
    
    	if (!$forceReload) {
    		if ($this->_canUseCache()) {
    			$this->_data = $this->_loadCache();
    			if ($this->_data !== false) {
    				return $this;
    			}
    		}
    		Mage::app()->removeCache($this->getCacheId());
    	}
    
    	$this->_data = array();
    	$disabled = Mage::getModel('translator/status')->getDisabledCode();
    	
    	if (Mage::getStoreConfig('translator/options/module_override') == $disabled) {
	    	foreach ($this->getModulesConfig() as $moduleName=>$info) {
	    		$info = $info->asArray();
	    		$this->_loadModuleTranslation($moduleName, $info['files'], $forceReload);
	    	}
    	}
    	if (Mage::getStoreConfig('translator/options/theme_override') == $disabled) {
    		$this->_loadThemeTranslation($forceReload);
    	}
    	
    	$this->_loadDbTranslation($forceReload);
    
    	if (!$forceReload && $this->_canUseCache()) {
    		$this->_saveCache();
    	}
    	return $this;
    }
    
    /**
     * Loading current store translation from DB
     *
     * @return Wheelbarrow_Translator_Model_Translate
     */
    protected function _loadDbTranslation($forceReload = false)
    {
    	$arr = $this->getResource()->getTranslationArrayByModule($this->getLocale(), $this->getConfig(parent::CONFIG_KEY_AREA));
    	foreach ($arr as $scope => $pairs) {
    		$this->_addData($pairs, $scope, $forceReload);
    	}
    	return $this;
    }
    
    /**
     * Translate
     *
     * @param   array $args
     * @return  string
     */
    public function translate($args)
    {
    	$text = array_shift($args);

    	$param = Mage::getModel('translator/observer')->getObserverFlag();
    	if (array_key_exists($param, $_GET))
		{
	    	if (!(is_string($text) && ''==$text)
	    			&& !is_null($text)
	    			&& !(is_bool($text) && false===$text)
	    			&& !(is_object($text) && ''==$text->getText())) {
	    		
		    	if ($text instanceof Mage_Core_Model_Translate_Expr) {
		    		$module = $text->getModule();
		    		$text = $text->getText();
		    	}
		    	$data = array('string' => $text);
		    	if (isset($module)) {
		    		$data['module'] = $module;
		    	}
		    	
	    		$string_id = Mage::getModel('translator/string')->createItem($data);
	    		$register = Mage::helper('translator/sync')->getRegister();
	    		$path = $register['data']['path'];
	    		//@todo add a condition statement to check that the register returned a value.
	    		Mage::getModel('translator/path')->createItem(array(
							'path' => $path,
							'string_id' => $string_id
						));
	    	}
		}
    	
		if ($text instanceof Mage_Core_Model_Translate_Expr) {
			$string_id = Mage::getModel('translator/string')->getIdByString($text->getCode());
			if (!$string_id) {
				$string_id = Mage::getModel('translator/string')->getResource()->getIdByParams(array(
						'string' => $text->getText(),
						'module' => null
						));
			}
			$string = Mage::getModel('translator/string')->load($string_id);
		} else {
			$string = Mage::getModel('translator/string')->getItemByString($text);
		}
		$args2 = $args;
		
		if ($string->getStatus() != Mage::getModel('translator/status')->getDisabledCode()) {
			$params = unserialize($string->getParameters());
			if (is_array($params)) {
				$storeId = Mage::app()->getStore()->getId();
				//@todo remove $key.
				foreach ($params as $key => $param) {
					if ($param['hardcoded']) {
						$args2[$param['position']] = isset($args[$param['code_position']]) ? $args[$param['code_position']] : '';
					} else {
						$splits = explode('{{customVar ', $param['value']);
						$param['value'] = '';
						foreach ($splits as $split) {
							preg_match("/code=(.*)}}/U", $split, $matches);
							if (count($matches)) {
								$string = Mage::getModel('core/variable')->setStoreId($storeId)->loadByCode($matches[1])->getValue('html');
								$split = preg_replace("/code=(.*)}}/U", $string, $split);
							}
							$param['value'] .= $split;
						}
						$args2[$param['position']] = $param['value'];
					}
				}
				ksort($args2);
			}
		}
		array_unshift($args2, $text);
    	return parent::translate($args2);
    }
    
    public function migrateCoreDb()
    {
    	$model = Mage::getModel('core/translate');
    	$resource = $model->getResource();
    	$adapter = $this->getResource()->getReadConnection();
    
    	if (!$adapter) {
    		Mage::log('could not get the adapter.');
    		return array();
    	}
		//@todo remove the static table name.
    	$select = $adapter->select()
    	->from('core_translate', array('*'));
    	$data = $adapter->fetchAll($select);
    
    	foreach ($data as $item) {
    		$item['translation'] = $item['translate'];    		
    		$this->addEntry($item);
    	}

    	return $this;
    }
    
    public function addEntry($item)
    {
    	$item['string_id'] = Mage::getModel('translator/string')->setItem($item);
    	
    	Mage::getModel('translator/translation')->setItem($item);
    	
    	return $item['string_id'];
    }
    
 	public function addMultipleEntries($batch)
    {
    	$string_col = Mage::getModel('translator/string')->getCollection()->load();
    	$translation_col = Mage::getModel('translator/translation')->getCollection()->load();
    	foreach ($batch as $key => $item) {
    		$item['string_id'] = Mage::getModel('translator/string')->prepareForSave($string_col, $item);
    		Mage::getModel('translator/translation')->setItem($item);
    	}
    }
    
    public function deleteEntry($string_id)
    {
    	Mage::getModel('translator/string')->load($string_id)->delete();
    }
    
    //@todo Transfer this to the sole location where it's currently being used.
    public function getEntriesByString($string)
    {
    	$string_id = Mage::getModel('translator/string')->getIdByString($string);
    	$translation_model = Mage::getModel('translator/translation');
    	$items = $translation_model->getCollection()
    		->addFieldToFilter('string_id', $string_id)
    		->addFieldToFilter($this->getConfig(parent::CONFIG_KEY_AREA), true)
    		->load();
    	$result = array();
    	foreach ($items as $item) {
    		$result[$item['store_id']] = $item['translation_id'];
    	}
    	return $result;
    }
}