<?php

class Magestance_Translator_Block_Adminhtml_Strings_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('string_form', array('legend'=>Mage::helper('translator')->__('Item information')));
     
      $fieldset->addType('table', 'Magestance_Translator_Block_Adminhtml_Strings_Edit_Tab_Field_Table');
      
      $fieldset->addField('string_id', 'hidden', array(
      		'name'      => 'identifier',
      		'required'  => false,
      ));
      
      $fieldset->addField('string', 'editor', array(
      		'name'      => 'string',
      		'label'     => Mage::helper('translator')->__('String'),
      		'title'     => Mage::helper('translator')->__('String'),
      		'style'     => 'width:700px; height:75px;',
      		'wysiwyg'   => false,
      		'required'  => true,
      ));
      
      $fieldset->addField('parameters', 'table', array(
      		'name'      => 'translation',
      		'label'     => Mage::helper('translator')->__('Parameters'),
      		'title'     => Mage::helper('translator')->__('Parameters'),
      		'required'  => false,
      ));
      
      $fieldset->addField('translation_id', 'hidden', array(
      		'name'      => 'translation_id',
      		'required'  => false,
      ));
      
      $fieldset->addField('translation', 'editor', array(
      		'name'      => 'translation',
      		'label'     => Mage::helper('translator')->__('Translation'),
      		'title'     => Mage::helper('translator')->__('Translation'),
      		'style'     => 'width:700px; height:75px;',
      		'wysiwyg'   => false,
      		'required'  => false,
      ));

      $fieldset->addField('module', 'select', array(
      		'name'      => 'module',
      		'values' 	=> Mage::getModel('translator/modules')->toOptionArray(),
      		'label'     => Mage::helper('translator')->__('Module'),
      		'title'     => Mage::helper('translator')->__('Module'),
      		'required'  => false,
      ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('translator')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('translator')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('translator')->__('Disabled'),
              ),
          ),
      ));
      
      if (Mage::getSingleton('adminhtml/session')->getTranslatorData())
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getTranslatorData());
          Mage::getSingleton('adminhtml/session')->setTranslatorData(null);
      } elseif (Mage::registry('translator_data')) {
          $form->setValues(Mage::registry('translator_data')->getData());
      }
      return parent::_prepareForm();
  }
}