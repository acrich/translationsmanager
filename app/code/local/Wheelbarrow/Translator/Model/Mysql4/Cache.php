<?php

class Wheelbarrow_Translator_Model_Mysql4_Cache extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('translator/cache', 'id');
    }
}