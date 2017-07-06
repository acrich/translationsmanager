<?php

class Wheelbarrow_Translator_Block_Adminhtml_Strings_Edit_Tab_Paths extends Mage_Adminhtml_Block_Widget_Grid
{
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setId('translator_paths');
		$this->setDefaultSort('position');
		$this->setDefaultDir('ASC');
		$this->setUseAjax(true);
	}
	
	/**
	 * Prepare grid collection object
	*
	* @return Wheelbarrow_Translator_Block_Adminhtml_String_Edit_Tab_Paths
	*/
	protected function _prepareCollection()
	{
		$string_id = $this->getRequest()->getParam('id', 0);
		$collection = Mage::getModel('translator/path')->getCollection()
			->addFieldToFilter('string_id', $string_id);
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	/**
	 * prepare columns
	 *
	 * @return Wheelbarrow_Translator_Block_Adminhtml_Strings_Edit_Tab_Paths
	 */
	protected function _prepareColumns()
	{
		$this->addColumn('path_id', array(
				'header'    => Mage::helper('translator')->__('Path ID'),
				'width'     => '50px',
				'index'     => 'path_id',
		));
		
		$this->addColumn('path', array(
				'header'    => Mage::helper('translator')->__('Path'),
				'width'     => '100px',
				'index'     => 'path',
		));
		
		$this->addColumn('file', array(
				'header'    => Mage::helper('translator')->__('File'),
				'width'     => '100px',
				'index'     => 'file',
		));
		
		$this->addColumn('offset', array(
				'header'    => Mage::helper('translator')->__('Offset'),
				'width'     => '50px',
				'index'     => 'offset',
		));
		return parent::_prepareColumns();
	}
	
	public function getGridUrl()
	{
		return $this->getUrl('*/*/pathsGrid', array('_current' => true));
	}
	
	public function getSelectedPaths()
	{
		$paths = array();
		$collection = Mage::getModel('translator/path')->getCollection()
			->addFieldToFilter('string_id', Mage::registry('translator_data')->getTranslationId());
		foreach ($collection as $path) {
			$paths[$path->getPathId()] = $path->getData();
		}
		return $paths;
	}
}