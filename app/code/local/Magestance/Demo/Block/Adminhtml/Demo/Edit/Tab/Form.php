<?php

class Magestance_Demo_Block_Adminhtml_Demo_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('demo_form', array('legend'=>Mage::helper('demo')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('demo')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('demo')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('demo')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('demo')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('demo')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('demo')->__('Content'),
          'title'     => Mage::helper('demo')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getDemoData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getDemoData());
          Mage::getSingleton('adminhtml/session')->setDemoData(null);
      } elseif ( Mage::registry('demo_data') ) {
          $form->setValues(Mage::registry('demo_data')->getData());
      }
      return parent::_prepareForm();
  }
}