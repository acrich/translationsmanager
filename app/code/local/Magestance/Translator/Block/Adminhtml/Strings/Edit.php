<?php

class Magestance_Translator_Block_Adminhtml_Strings_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        
        $this->_objectId = 'id';
        $this->_blockGroup = 'translator';
        $this->_controller = 'adminhtml_strings';
        $this->_mode = 'edit';
        
        $this->_updateButton('save', 'label', Mage::helper('translator')->__('Save Item'));
        $this->_updateButton('save', 'onclick', 'processTable()');
        $this->_updateButton('delete', 'label', Mage::helper('translator')->__('Delete Item'));
		
        $this->_addButton('delete_trans', array(
                'label'     => Mage::helper('translator')->__('Delete Translation'),
                'class'     => 'delete',
                'onclick'   => 'deleteConfirm(\''. Mage::helper('translator')->__('Are you sure you want to do this?')
                    .'\', \'' . $this->getDeleteTransUrl() . '\')',
            ));
        
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('translator')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('string_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'string_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'string_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }
    
    
    public function getDeleteTransUrl()
    {
    	if (Mage::registry('translator_data') && Mage::registry('translator_data')->getTranslationId()) {
    		return $this->getUrl('*/*/deleteTranslation', array(
    					'translation_id' => Mage::registry('translator_data')->getTranslationId(),
    					$this->_objectId => $this->getRequest()->getParam($this->_objectId)
    				));
    	} else {
    		return $this->getUrl('*/*/deleteTranslation', array('translation_id' => 0));
    	}
    }

    public function getHeaderText()
    {
        if( Mage::registry('translator_data') && Mage::registry('translator_data')->getId() ) {
            return Mage::helper('translator')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('translator_data')->getString()));
        } else {
            return Mage::helper('translator')->__('Add Item');
        }
    }
}