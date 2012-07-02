<?php

class Magestance_Translator_Model_Status extends Varien_Object
{
    const STATUS_ENABLED	= 1;
    const STATUS_DISABLED	= 2;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED    => Mage::helper('translator')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('translator')->__('Disabled')
        );
    }
    
    public function toOptionArray()
    {
    	return array(
    		array('value'=>self::STATUS_ENABLED, 'label'=>Mage::helper('translator')->__('Yes')),
            array('value'=>self::STATUS_DISABLED, 'label'=>Mage::helper('translator')->__('No')),
    	);
    }
}