<?php
class Magestance_Translator_Model_Mysql4_Translation extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('translator/translation', 'translation_id');
    }
}