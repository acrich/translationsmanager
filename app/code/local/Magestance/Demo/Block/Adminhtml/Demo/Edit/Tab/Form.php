<?php

class Magestance_Demo_Block_Adminhtml_Demo_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('demo_form', array('legend'=>Mage::helper('demo')->__('Item information')));
     
      $fieldset->addType('table', 'Magestance_Demo_Block_Adminhtml_Demo_Edit_Tab_Field_Table');
      
      $fieldset->addField('string', 'editor', array(
      		'name'      => 'string',
      		'label'     => Mage::helper('demo')->__('String'),
      		'title'     => Mage::helper('demo')->__('String'),
      		'style'     => 'width:700px; height:75px;',
      		'wysiwyg'   => false,
      		'required'  => true,
      ));
      
      $fieldset->addField('parameters', 'table', array(
      		'name'      => 'translation',
      		'label'     => Mage::helper('demo')->__('Parameters'),
      		'title'     => Mage::helper('demo')->__('Parameters'),
      		'required'  => false,
      ));
      
      $fieldset->addField('translation_id', 'hidden', array(
      		'name'      => 'translation_id',
      		'required'  => false,
      ));
      
      $fieldset->addField('translation', 'editor', array(
      		'name'      => 'translation',
      		'label'     => Mage::helper('demo')->__('Translation'),
      		'title'     => Mage::helper('demo')->__('Translation'),
      		'style'     => 'width:700px; height:75px;',
      		'wysiwyg'   => false,
      		'required'  => false,
      ));
      
      $fieldset->addField('module', 'text', array(
      		'name'      => 'module',
      		'label'     => Mage::helper('demo')->__('Module'),
      		'title'     => Mage::helper('demo')->__('Module'),
      		'required'  => false,
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
          $form->getElement('string')->setDisabled(1);
      }
      return parent::_prepareForm();
  }
}