<?php

class Wheelbarrow_Translator_Block_Adminhtml_Pagescan_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(array(
                                      'id' => 'edit_form',
                                      'action' => $this->getUrl('*/*/pageScanCallback'),
                                      'method' => 'post',
        							  'enctype' => 'multipart/form-data'
                                   ));
		$form->setUseContainer(true);
		$this->setForm($form);
		$fieldset = $form->addFieldset('pagescan_form_fieldset', array('legend'=>Mage::helper('translator')->__('Scan A Page')));
		
		$fieldset->addField('path', 'text', array(
				'label'     => Mage::helper('translator')->__('Full Path'),
				'class'     => 'required-entry',
				'required'  => true,
				'name'      => 'path',
		));
		return parent::_prepareForm();
	}
}