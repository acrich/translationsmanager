<?php
class Magestance_Translator_Block_Adminhtml_Strings extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_strings';
    $this->_blockGroup = 'translator';
    $this->_headerText = Mage::helper('translator')->__('Manage Translations');
    $this->_addButtonLabel = Mage::helper('translator')->__('Add A New String');
    parent::__construct();
  }  
}