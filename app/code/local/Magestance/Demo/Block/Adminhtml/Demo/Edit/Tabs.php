<?php

class Magestance_Demo_Block_Adminhtml_Demo_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('demo_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('demo')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('item_form', array(
          'label'     => Mage::helper('demo')->__('Item Information'),
          'title'     => Mage::helper('demo')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('demo/adminhtml_demo_edit_tab_form')->toHtml(),
      ));
      
      $this->addTab('item_paths', array(
      		'label'     => Mage::helper('demo')->__('Associated Paths'),
      		'title'     => Mage::helper('demo')->__('Associated Paths'),
      		'content'   => $this->getLayout()->createBlock('demo/adminhtml_demo_edit_tab_paths')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}