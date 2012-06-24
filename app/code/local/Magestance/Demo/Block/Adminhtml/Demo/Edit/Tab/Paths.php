<?php

class Magestance_Demo_Block_Adminhtml_Demo_Edit_Tab_Paths extends Mage_Adminhtml_Block_Widget_Grid
{
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setId('demo_paths');
		$this->setDefaultSort('position');
		$this->setDefaultDir('ASC');
	}
	
	/**
	 * Prepare grid collection object
	*
	* @return Magestance_Demo_Block_Adminhtml_Demo_Edit_Tab_Paths
	*/
	protected function _prepareCollection()
	{
		$string_id = $this->getRequest()->getParam('id', 0);
		$collection = Mage::getModel('demo/path')->getCollection()
			->addFieldToFilter('string_id', $string_id);
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	/**
	 * prepare columns
	 *
	 * @return Magestance_Demo_Block_Adminhtml_Demo_Edit_Tab_Paths
	 */
	protected function _prepareColumns()
	{
		$this->addColumn('path_id', array(
				'header'    => Mage::helper('demo')->__('Path ID'),
				'width'     => '50px',
				'index'     => 'path_id',
		));
		
		$this->addColumn('path', array(
				'header'    => Mage::helper('demo')->__('Path'),
				'width'     => '100px',
				'index'     => 'path',
		));
		
		$this->addColumn('file', array(
				'header'    => Mage::helper('demo')->__('File'),
				'width'     => '100px',
				'index'     => 'file',
		));
		
		$this->addColumn('offset', array(
				'header'    => Mage::helper('demo')->__('Offset'),
				'width'     => '50px',
				'index'     => 'offset',
		));
		return parent::_prepareColumns();
	}
}