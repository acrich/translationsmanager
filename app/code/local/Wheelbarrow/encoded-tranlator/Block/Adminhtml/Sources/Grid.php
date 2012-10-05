<?php

class Wheelbarrow_Translator_Block_Adminhtml_Sources_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('sourcesGrid');
      $this->setDefaultSort('register');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
  	  $collection = Mage::getModel('translator/cache')->getCollection()->addFieldToFilter('name', 'resource');
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
	  $this->addColumn('module', array(
  		  'header'    => Mage::helper('translator')->__('Resource'),
  		  'align'     =>'left',
  		  'width'     => '100px',
		  'index'     => 'register',
	  	  'sortable'  => false,
	  	  'filter'	=> false,
	  	  'renderer' => 'Wheelbarrow_Translator_Block_Adminhtml_Sources_Renderer_Module',
	  ));
	  
	  $this->addColumn('locale', array(
	  		'header'    => Mage::helper('translator')->__('Locale'),
	  		'align'     =>'left',
	  		'width'     => '100px',
	  		'index'     => 'register',
	  		'sortable'  => false,
	  		'filter'	=> false,
	  		'renderer' => 'Wheelbarrow_Translator_Block_Adminhtml_Sources_Renderer_Locale',
	  ));
	  
	  $this->addColumn('areas', array(
	  		'header'    => Mage::helper('translator')->__('Areas'),
	  		'align'     =>'left',
	  		'width'     => '100px',
	  		'index'     => 'register',
	  		'sortable'  => false,
	  		'filter'	=> false,
	  		'renderer' => 'Wheelbarrow_Translator_Block_Adminhtml_Sources_Renderer_Area',
	  ));
	  
	  $this->addColumn('status', array(
	  		'header'    => Mage::helper('translator')->__('Status'),
	  		'align'     =>'left',
	  		'width'     => '100px',
	  		'index'     => 'register',
	  		'sortable'  => false,
	  		'filter'	=> false,
	  		'renderer' => 'Wheelbarrow_Translator_Block_Adminhtml_Sources_Renderer_Status',
	  ));

      return parent::_prepareColumns();
  }
  
  protected function _prepareLayout()
  {

  	parent::_prepareLayout();
  	$this->unsetChild('reset_filter_button');
  	$this->unsetChild('search_button');
  	return $this;
  }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/syncResource', array('id' => $row->getId()));
  }

}