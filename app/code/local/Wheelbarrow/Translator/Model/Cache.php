<?php

class Wheelbarrow_Translator_Model_Cache extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('translator/cache');
    }

    public function createItem($name, $register)
    {
        $this->setData(array(
                    'name' => $name,
                    'register' => serialize($register)
                ))->save();

        return $this->getId();
    }
}