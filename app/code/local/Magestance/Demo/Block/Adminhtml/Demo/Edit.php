<?php

class Magestance_Demo_Block_Adminhtml_Demo_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'demo';
        $this->_controller = 'adminhtml_demo';
        
        $this->_updateButton('save', 'label', Mage::helper('demo')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('demo')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('demo_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'demo_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'demo_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('demo_data') && Mage::registry('demo_data')->getId() ) {
            return Mage::helper('demo')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('demo_data')->getTitle()));
        } else {
            return Mage::helper('demo')->__('Add Item');
        }
    }
}