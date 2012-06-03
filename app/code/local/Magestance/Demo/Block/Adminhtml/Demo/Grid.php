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

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('demo/translator')->_getAggregatedCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('string', array(
          'header'    => Mage::helper('demo')->__('String'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'string',
      ));

      $this->addColumn('translate', array(
          'header'    => Mage::helper('demo')->__('Translation'),
          'align'     =>'left',
          'index'     => 'translate',
      ));

      $this->addColumn('store_id', array(
			'header'    => Mage::helper('demo')->__('Store ID'),
			'width'     => '150px',
			'index'     => 'store_id',
      ));
      
      $this->addColumn('locale', array(
      		'header'    => Mage::helper('demo')->__('Locale'),
      		'width'     => '150px',
      		'index'     => 'locale',
      ));
/*
      $this->addColumn('status', array(
          'header'    => Mage::helper('demo')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  */
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
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}