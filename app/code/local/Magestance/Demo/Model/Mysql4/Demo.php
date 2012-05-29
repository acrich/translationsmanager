<?php

class Magestance_Demo_Model_Mysql4_Demo extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the demo_id refers to the key field in your database table.
        $this->_init('demo/demo', 'demo_id');
    }
}