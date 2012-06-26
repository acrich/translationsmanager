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
	  $store_id = Mage::helper('demo')->getCurrentStore();
      $collection = Mage::getModel('demo/string')->getCollection();
      $collection->getSelect()->joinLeft('demo_translation', 'main_table.string_id = demo_translation.string_id AND demo_translation.store_id = '.$store_id, array('translation', 'translation_id'));

      $this->setCollection($collection);
      
      if ($this->getCollection()) {
      
      	$this->_preparePage();
      
      	$columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
      	$dir      = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
      	$filter   = $this->getParam($this->getVarNameFilter(), null);
      
      	if (is_null($filter)) {
      		$filter = $this->_defaultFilter;
      	}
      
      	if (is_string($filter)) {
      		$data = $this->helper('adminhtml')->prepareFilterString($filter);
      		if (array_key_exists('path', $data)) {
      			$string_ids = Mage::getModel('demo/path')->getStringIdsByPath($data['path']);
      			$this->getCollection()->addFieldToFilter('main_table.string_id' , array('in'=>$string_ids));
      			$this->getChild('path_filter')->setInputValue($data['path']);
      		}
      		$this->_setFilterValues($data);
      	}
      	else if ($filter && is_array($filter)) {
      		$this->_setFilterValues($filter);
      	}
      	else if(0 !== sizeof($this->_defaultFilter)) {
      		$this->_setFilterValues($this->_defaultFilter);
      	}
      
      	if (isset($this->_columns[$columnId]) && $this->_columns[$columnId]->getIndex()) {
      		$dir = (strtolower($dir)=='desc') ? 'desc' : 'asc';
      		$this->_columns[$columnId]->setDir($dir);
      		$this->_setCollectionOrder($this->_columns[$columnId]);
      	}
      
      	if (!$this->_isExport) {
      		$this->getCollection()->load();
      		$this->_afterLoadCollection();
      	}
      }
      
      return $this;
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
        
        $this->getMassactionBlock()->addItem('delete_trans', array(
        		'label'=> Mage::helper('demo')->__('Delete Translations'),
        		'url'  => $this->getUrl('*/*/massDeleteTrans'),
        		'confirm'  => Mage::helper('demo')->__('This will delete the selected translations for the currently selected store view. Are you sure?')
        ));
        
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
  
  public function getPathFilterHtml()
  {
  	return $this->getChildHtml('path_filter');
  }
  
  public function getMainButtonsHtml()
  {
  	$html = $this->getPathFilterHtml();
  	
  	$html .= parent::getMainButtonsHtml();
  	
  	return $html;
  }
  
  protected function _prepareLayout()
  {
  	$this->setChild('path_filter',
  			$this->getLayout()->createBlock('demo/adminhtml_widget_button')
  			->setData(array(
  					'label'     => Mage::helper('demo')->__('Filter By Path'),
  					'onclick'   => $this->getJsObjectName().'.doFilter()',
  					'class'   => 'task'
  			))
  	);
  	return parent::_prepareLayout();
  }

}