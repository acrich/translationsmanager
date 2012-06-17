<?php

class Magestance_Demo_Block_Adminhtml_Demo_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('demoGrid');
      $this->setDefaultSort('string');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }
  
  protected function _getStore()
  {
  	$storeId = (int) $this->getRequest()->getParam('store', 0);
  	return Mage::app()->getStore($storeId);
  }

  protected function _prepareCollection()
  {
	  $store = $this->_getStore();
      $collection = Mage::getModel('demo/string')->getCollection();
      $collection->getSelect()->joinLeft('demo_translation', 'main_table.string_id = demo_translation.string_id AND demo_translation.store_id = ' . $store->getId(),array('translation'));

      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
	  $this->addColumn('string_id', array(
  		  'header'    => Mage::helper('demo')->__('ID'),
  		  'align'     =>'right',
  		  'width'     => '50px',
		  'index'     => 'string_id',
	  	  'filter_index' => 'main_table.string_id',
	  ));
	  
      $this->addColumn('string', array(
          'header'    => Mage::helper('demo')->__('String'),
          'align'     =>'left',
          'index'     => 'string',
      	  'filter_index' => 'main_table.string',
      	  'renderer' => 'Magestance_Demo_Block_Adminhtml_Demo_Renderer_String'
      ));

      $this->addColumn('translation', array(
          'header'    => Mage::helper('demo')->__('Translation'),
          'align'     =>'left',
          'index'     => 'translation',
      	  'filter_index' => 'demo_translation.translation',
      	  'renderer' => 'Magestance_Demo_Block_Adminhtml_Demo_Renderer_Translation'
      ));
      
      $this->addColumn('module', array(
      		'header'    => Mage::helper('demo')->__('Scope'),
      		'width'     => '150px',
      		'index'     => 'module',
      		'filter_index' => 'main_table.module',
      ));
      
  		/**
         * Check is single store mode
         */
      
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('demo')->__('Store View'),
                'index'         => 'store_id',
            	'filter_index' => 'demo_translation.store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => true,
                'filter_condition_callback'
                                => array($this, '_filterStoreCondition'),
            ));
        }

      $this->addColumn('status', array(
          'header'    => Mage::helper('demo')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
      	  'filter_index' => 'main_table.status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('demo')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('demo')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('demo')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('demo')->__('XML'));
		
      return parent::_prepareColumns();
  }
  
  protected function _afterLoadCollection()
  {
  	$this->getCollection()->walk('afterLoad');
  	parent::_afterLoadCollection();
  }
  
  protected function _filterStoreCondition($collection, $column)
  {
  	if (!$value = $column->getFilter()->getValue()) {
  		return;
  	}
  
  	$this->getCollection()->addStoreFilter($value);
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('string');
        $this->getMassactionBlock()->setFormFieldName('demo');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('demo')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('demo')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('demo/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('demo')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('demo')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array(
      			'store'=>$this->getRequest()->getParam('store'), 
      			'id' => $row->getId()
      		));
  }

}