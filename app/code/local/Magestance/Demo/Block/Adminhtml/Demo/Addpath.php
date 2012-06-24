<?php

class Magestance_Demo_Block_Adminhtml_Demo_Addpath extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();

        $this->_blockGroup = 'demo';
        $this->_controller = 'adminhtml_demo';
        $this->_objectId = 'id';
        $this->_mode = 'addpath';
        
        $this->_updateButton('save', 'label', Mage::helper('demo')->__('Get Page Contents'));
        
        $this->_headerText = Mage::helper('demo')->__('Add a New Path');
	}
}