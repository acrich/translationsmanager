<?php

class Magestance_Demo_Model_Translate extends Mage_Core_Model_Translate
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('demo/string');
    }
    
    /**
     * Retrieve DB resource model
     *
     * @return unknown
     */
    public function getResource()
    {
    	return Mage::getResourceSingleton('demo/string');
    }
    
    /**
     * Translate
     *
     * @param   array $args
     * @return  string
     */
    public function translate($args)
    {
    	if (array_key_exists('magestanceScan', $_GET))
		{
	    	$text = array_shift($args);
	    	if (!(is_string($text) && ''==$text)
	    			&& !is_null($text)
	    			&& !(is_bool($text) && false===$text)
	    			&& !(is_object($text) && ''==$text->getText())) {
	    		
		    	if ($text instanceof Mage_Core_Model_Translate_Expr) {
		    		$module = $text->getModule();
		    		$text = $text->getText();
		    		
		    		$string_id = Mage::getModel('demo/string')->createItem(array(
										'string' => $text, 
										'module' => $module
									));
		    		$queue = Mage::helper('demo/queue')->getFirst('sync');
		    		$path = $queue['data']['path'];
					Mage::getModel('demo/path')->createItem(array(
								'path' => $path,
								'string_id' => $string_id
							));
		    	} else {
		    		if (!empty($_REQUEST['theme'])) {
		    			$module = 'frontend/default/'.$_REQUEST['theme'];
		    		} else {
		    			$module = 'frontend/default/default';
		    		}
		    		$string_id = Mage::getModel('demo/string')->createItem(array(
		    				'string' => $text,
		    				'module' => $module
		    		));
		    		$queue = Mage::helper('demo/queue')->getFirst('sync');
		    		$path = $queue['data']['path'];
		    		Mage::getModel('demo/path')->createItem(array(
								'path' => $path,
								'string_id' => $string_id
							));
		    	}
	    	}
	    
	    	array_unshift($args, $text);
		}
    	    
    	return parent::translate($args);
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
    	$item['string_id'] = Mage::getModel('demo/string')->createItem($item);
    	Mage::getModel('demo/translation')->createItem($item);
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
    		$item['string_id'] = Mage::getModel('demo/string')->createItem($item);
    	}
    	$model = Mage::getModel('demo/string')->load($item['string_id']);
    	if (array_key_exists('module', $item)) {
    		$model->setModule($item['module']);
    	}
    	if (array_key_exists('status', $item)) {
    		$model->setStatus($item['status']);
    	}
    	$model->save();
    	Mage::getModel('demo/translation')->createItem($item);
    }
    
    public function deleteEntry($string_id)
    {
    	Mage::getModel('demo/string')->load($string_id)->delete();
    }
    
    public function getEntryByString($string)
    {
    	$string_item = getItemByString($string);
    	$translation_model = Mage::getModel('demo/translation');
    	$translation_item = $translation_model->load($translation_model->getIdByStringId($string_item['string_id']));
    	
    	return array(
    				'string' => $string_item['module'] . self::SCOPE_SEPARATOR . $string_item['string'],
    				'translate' => $translation_item['translation'],
    				'store_id' => $translation_item['store_id'],
    				'locale' => $translation_item['locale']
    			);
    }
}