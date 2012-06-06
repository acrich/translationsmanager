<?php

class Magestance_Demo_Block_Adminhtml_Demo_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('demo_form', array('legend'=>Mage::helper('demo')->__('Item information')));
     
      $fieldset->addField('string', 'editor', array(
      		'name'      => 'string',
      		'label'     => Mage::helper('demo')->__('String'),
      		'title'     => Mage::helper('demo')->__('String'),
      		'style'     => 'width:700px; height:75px;',
      		'wysiwyg'   => false,
      		'required'  => true,
      ));
       
      $fieldset->addField('translate', 'editor', array(
      		'name'      => 'translate',
      		'label'     => Mage::helper('demo')->__('Translate'),
      		'title'     => Mage::helper('demo')->__('Translate'),
      		'style'     => 'width:700px; height:75px;',
      		'wysiwyg'   => false,
      		'required'  => false,
      ));
      /*
      $fieldset->addField('store_id', 'text', array(
          'label'     => Mage::helper('demo')->__('Store Id'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'store_id',
      ));
      */
      $fieldset->addField('locale', 'text', array(
      		'label'     => Mage::helper('demo')->__('Locale'),
      		'class'     => 'required-entry',
      		'required'  => true,
      		'name'      => 'locale',
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