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
        $this->_updateButton('save', 'onclick', 'processTable()');
        $this->_updateButton('delete', 'label', Mage::helper('demo')->__('Delete Item'));
		
        $this->_addButton('delete_trans', array(
                'label'     => Mage::helper('adminhtml')->__('Delete Translation'),
                'class'     => 'delete',
                'onclick'   => 'deleteConfirm(\''. Mage::helper('adminhtml')->__('Are you sure you want to do this?')
                    .'\', \'' . $this->getDeleteTransUrl() . '\')',
            ));
        
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
    
    
    public function getDeleteTransUrl()
    {
    	if (Mage::registry('demo_data') && Mage::registry('demo_data')->getTranslationId()) {
    		return $this->getUrl('*/*/deleteTranslation', array(
    					'translation_id' => Mage::registry('demo_data')->getTranslationId(),
    					$this->_objectId => $this->getRequest()->getParam($this->_objectId)
    				));
    	} else {
    		return $this->getUrl('*/*/deleteTranslation', array('translation_id' => 0));
    	}
    }

    public function getHeaderText()
    {
        if( Mage::registry('demo_data') && Mage::registry('demo_data')->getId() ) {
            return Mage::helper('demo')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('demo_data')->getString()));
        } else {
            return Mage::helper('demo')->__('Add Item');
        }
    }
}