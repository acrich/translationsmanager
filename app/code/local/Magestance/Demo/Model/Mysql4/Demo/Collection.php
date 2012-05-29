<?php

class Magestance_Demo_Model_Mysql4_Demo_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('demo/demo');
    }
}