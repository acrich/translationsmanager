<?php

class Magestance_Demo_Block_Adminhtml_Demo_Newpage extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();

        $this->_blockGroup = 'demo';
        $this->_controller = 'adminhtml_demo';
        $this->_objectId = 'id';
        $this->_mode = 'getpage';
        
        //$this->_updateButton('save', 'onclick', 'saveAndContinueEdit();');
        $this->_updateButton('save', 'label', Mage::helper('demo')->__('Get Page Contents'));
        
        $this->_headerText = Mage::helper('demo')->__('Get a New Page');
	}
}