<?php

class Wheelbarrow_Translator_Model_Modules extends Varien_Object
{

    public function toOptionArray()
    {
        $modules = Mage::getConfig()->getNode('modules')->children();
        $result = array(0 => array('value' => '', 'label' => 'None'));
        foreach ($modules as $module) {
            if ($module->active) {
                $name = $module->getName();
                $result[] = array('value' => $name, 'label' => $name);
            }
        }
        return $result;
    }
}
