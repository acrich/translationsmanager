<?php

class Magestance_Translator_Model_Translate extends Mage_Core_Model_Translate
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('translator/string');
    }
    
    /**
     * Initialization translation data
     *
     * @param   string $area
     * @return  Magestance_Translator_Model_Translate
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
    
    	$this->_loadDbTranslation($forceReload);
    
    	if (!$forceReload && $this->_canUseCache()) {
    		$this->_saveCache();
    	}
    	return $this;
    }
    
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
     * Translate
     *
     * @param   array $args
     * @return  string
     */
    public function translate($args)
    {
    	$text = array_shift($args);
    	
    	$param = Mage::getModel('translator/observer');
    	$param = $param::FLAG_SHOW_LAYOUT;
    	
    	if (array_key_exists($param, $_GET))
		{
	    	if (!(is_string($text) && ''==$text)
	    			&& !is_null($text)
	    			&& !(is_bool($text) && false===$text)
	    			&& !(is_object($text) && ''==$text->getText())) {
	    		
		    	if ($text instanceof Mage_Core_Model_Translate_Expr) {
		    		$module = $text->getModule();
		    		$text = $text->getText();
		    	} else {
		    		if (!empty($_REQUEST['theme'])) {
		    			$module = 'frontend/default/'.$_REQUEST['theme'];
		    		} else {
		    			$module = 'frontend/default/default';
		    		}
		    	}
	    		$string_id = Mage::getModel('translator/string')->createItem(array(
	    				'string' => $text,
	    				'module' => $module,
	    		));
	    		$queue = Mage::helper('translator/queue')->getFirst('sync');
	    		$path = $queue['data']['path'];
	    		Mage::getModel('translator/path')->createItem(array(
							'path' => $path,
							'string_id' => $string_id
						));
	    	}
		}
    	
		if ($text instanceof Mage_Core_Model_Translate_Expr) {
			$string = $text->getText();
		} else {
			$string = $text;
		}
		$string = Mage::getModel('translator/string')->getItemByString($string);
		$params = unserialize($string->getParameters());
		$args2 = $args;
		if (is_array($params)) {
			foreach ($params as $key => $param) {
				if (!$param['hardcoded']) {
					if ($param['orig_position'] != $param['position']) {
						$args2[$param['orig_position']] = $args[$param['position']];
					}
					if ($param['value'] != '') {
						$callback = function($matches) {
							$var = Mage::getModel('core/variable');
							$var->setStoreId(Mage::app()->getStore()->getId());
							return $var->loadByCode($matches[1])->getValue('html');
						};
						$param['value'] = preg_replace_callback("/{{(.*)}}/U", $callback, $param['value']);
						$args2[$param['position']] = $param['value'];
					}
				}
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
    	$item['string_id'] = Mage::getModel('translator/string')->createItem($item);
    	Mage::getModel('translator/translation')->createItem($item);
    }
    
    public function addMultipleEntries($batch)
    {
    	foreach ($batch as $item) {
    		$this->addEntry($item);
    	}
    }
    
    public function addEntryWithId($item)
    {
    	if (!array_key_exists('string_id', $item)) {
    		$item['string_id'] = Mage::getModel('translator/string')->createItem($item);
    	}
    	$model = Mage::getModel('translator/string')->load($item['string_id']);
    	if (array_key_exists('module', $item)) {
    		$model->setModule($item['module']);
    	}
    	if (array_key_exists('status', $item)) {
    		$model->setStatus($item['status']);
    	}
    	$model->save();
    	Mage::getModel('translator/string')->updateItem($item);
    	Mage::getModel('translator/translation')->createItem($item);
    }
    
    public function deleteEntry($string_id)
    {
    	Mage::getModel('translator/string')->load($string_id)->delete();
    }
    
    public function getEntriesByString($string)
    {
    	$string_id = getIdByString($string);
    	$translation_model = Mage::getModel('translator/translation');
    	$items = $translation_model->getCollection()
    		->addFieldToFilter('string_id', $string_id)
    		->load();
    	$result = array();
    	foreach ($items as $item) {
    		$result[$item['store_id']] = $item['translation_id'];
    	}
    	return $result;
    }
}