<?php
class Wheelbarrow_Translator_Model_Mysql4_Translation extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('translator/translation', 'translation_id');
    }
    
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
    	$translation = $object->getData('translation');
    	$translation = $this->_getWriteAdapter()->quote($translation);
    	$object->setData('translation', $translation);
    
    	return $this;
    }
    
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
    	$translation = $object->getData('translation');
    	$translation = preg_replace( "/^\'(.*)\'$/U", "$1", $translation);
    	$translation = stripslashes($translation);
    	$object->setData('translation', $translation);
    
    	return $this;
    }
}