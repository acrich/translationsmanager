<?php

class Wheelbarrow_Translator_Block_Adminhtml_Strings_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('string_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('translator')->__('Edit String'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('item_form', array(
          'label'     => Mage::helper('translator')->__('Item Information'),
          'title'     => Mage::helper('translator')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('translator/adminhtml_strings_edit_tab_form')->toHtml(),
      ));
      
      $paths = $this->getLayout()->createBlock('translator/adminhtml_strings_edit_tab_paths', 'translator_strings.paths')->toHtml();
      $serialize_block = $this->getLayout()->createBlock('adminhtml/widget_grid_serializer');
      $serialize_block->initSerializerBlock('translator_strings.paths', 'getSelectedPaths', 'id', 'id');
      $paths .= $serialize_block->toHtml();
      
      $this->addTab('item_paths', array(
      		'label'     => Mage::helper('translator')->__('Associated Paths'),
      		'title'     => Mage::helper('translator')->__('Associated Paths'),
      		'content'   => $paths,
      ));
     
      return parent::_beforeToHtml();
  }
}