<?php

class Wheelbarrow_Translator_Model_Translate_String extends Mage_Core_Model_Translate_String
{
    protected function _construct()
    {
        $this->_init('translator/translate_string');
    }
}