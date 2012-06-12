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
    		$this->addEntry($item);
    	}

    	return $this;
    }
    
    public function addEntry($item)
    {
    	$item['string_id'] = Mage::getModel('demo/string')->createItem($item['string']);
    	Mage::getModel('demo/translation')->createItem($item);
    }
    
    public function addMultipleEntries($batch)
    {
    	foreach ($batch as $item) {
    		$this->addEntry($item);
    	}
    }
    
    public function getEntriesCollection()
    {
    	$aggregated = new Varien_Data_Collection();
    
    	$store_id = Mage::helper('demo')->getStoreId();
    
    	$strings = Mage::getModel('demo/string')->getCollection();
    	foreach ($strings as $string) {
    		$translation = Mage::getModel('demo/translation')
    			->getCollection()
    			->addFieldToFilter('string_id', $string->getId())
    			->getFirstItem();
    		$item = new Varien_Object();
    		$item->setData($string->getData());
    		$item->setTranslation($translation->getTranslation());
    		$item->setId($translation->getTranslationId());
    		$aggregated->addItem($item);
    	}
    	$size = $aggregated->load()->getSize();
    	Mage::log($aggregated, null, 'shay.log');
    	return $aggregated;
    }
}