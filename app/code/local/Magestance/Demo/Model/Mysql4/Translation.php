<?php
class Magestance_Demo_Model_Mysql4_Translation extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('demo/translation', 'translation_id');
    }
}