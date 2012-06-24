<?php
class Magestance_Demo_Block_Adminhtml_Demo extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_demo';
    $this->_blockGroup = 'demo';
    $this->_headerText = Mage::helper('demo')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('demo')->__('Add Item');
    parent::__construct();
  }  
}