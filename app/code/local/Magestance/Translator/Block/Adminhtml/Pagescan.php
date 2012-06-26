<?php
class Magestance_Translator_Block_Adminhtml_Pagescan extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();

        $this->_blockGroup = 'translator';
        $this->_controller = 'adminhtml';
        $this->_objectId = 'id';
        $this->_mode = 'pagescan';
        
        $this->_updateButton('save', 'label', Mage::helper('translator')->__('Scan Page'));
        
        $this->_headerText = Mage::helper('translator')->__('Scan A Page');
	}
}