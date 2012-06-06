<?php

class Magestance_Demo_Block_Adminhtml_Demo_Addpath_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(array(
                                      'id' => 'edit_form',
                                      'action' => $this->getUrl('*/*/initpathsync'),
                                      'method' => 'post',
        							  'enctype' => 'multipart/form-data'
                                   ));
		$form->setUseContainer(true);
		$this->setForm($form);
		$fieldset = $form->addFieldset('getpage_form_fieldset', array('legend'=>Mage::helper('demo')->__('Get a New Page')));
		
		$fieldset->addField('path', 'text', array(
				'label'     => Mage::helper('demo')->__('Path'),
				'class'     => 'required-entry',
				'required'  => true,
				'name'      => 'path',
		));
		return parent::_prepareForm();
	}
}