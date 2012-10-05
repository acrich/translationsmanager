<?php
class Wheelbarrow_Translator_Block_Adminhtml_Sources extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_sources';
    $this->_blockGroup = 'translator';
    $this->_headerText = Mage::helper('translator')->__('Module CSV Files');
    $this->_addButtonLabel = Mage::helper('translator')->__('Scan For Module CSV Files');
    parent::__construct();
    $this->updateButton('add', 'onclick', "deleteConfirm('This action will reset current sync status indicators. Are you sure?', '".$this->getUrl('*/*/scanResources')."')");
    $this->_addButton('clear', array(
    		'label'     => 'Clear Sync Queue',
    		'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/clearCache') .'\')',
    		'class'     => 'add',
    ));
  }
}