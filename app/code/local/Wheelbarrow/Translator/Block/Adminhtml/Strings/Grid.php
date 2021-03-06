<?php

class Wheelbarrow_Translator_Block_Adminhtml_Strings_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('stringGrid');
        $this->setDefaultSort('string');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $store_id = Mage::helper('translator')->getStoredSession('store');
        $collection = Mage::getModel('translator/string')->getCollection();

        $this->setCollection($collection);

        if ($this->getCollection()) {

            $this->_preparePage();

            $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
            $dir      = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
            $filter   = $this->getParam($this->getVarNameFilter(), null);

            if (is_null($filter)) {
                $filter = $this->_defaultFilter;
                //@todo move all db-related code out of the block.

                $where = 'main_table.string_id = tt.string_id'
                    .' AND tt.store_id = '.$store_id;


                $ar = Mage::helper('translator')->getStoredSession('area');
                if ($ar != '') {
                    $where .= ' AND tt.'.$ar.'=1';
                } else {
                    $duplicates = Mage::getModel('translator/translation')->getDuplicatesList($store_id);
                    $where .= ($duplicates == '') ? '' : ' AND tt.translation_id NOT IN ('.$duplicates.')';
                }

                $this->getCollection()
                    ->getSelect()
                    ->joinLeft(array('tt' => Mage::getModel('translator/translation')->getResource()->getMainTable()),$where, array('translation', 'translation_id'));
            }

            if (is_string($filter)) {
                $data = $this->helper('adminhtml')->prepareFilterString($filter);

                if (array_key_exists('path', $data)) {
                    $string_ids = Mage::getModel('translator/path')->getStringIdsByPath($data['path']);
                    $this->getCollection()->addFieldToFilter('main_table.string_id' , array('in'=>$string_ids));
                    $this->getChild('path_filter')->setInputValue($data['path']);
                }

                if (array_key_exists('area', $data)) {
                    $area = strtolower($data['area']);
                } else {
                    $area = '';
                }

                if ($area == '') {
                  $where = 'main_table.string_id = tt.string_id'
                        .' AND tt.store_id = '.$store_id;

                  $ar = Mage::helper('translator')->getStoredSession('area');
                  if ($ar != '') {
                      $where .= ' AND tt.'.$ar.'=1';
                  } else {
                      $duplicates = Mage::getModel('translator/translation')->getDuplicatesList($store_id);
                      $where .= ($duplicates == '') ? '' : ' AND tt.translation_id NOT IN ('.$duplicates.')';
                  }

                  $this->getCollection()
                      ->getSelect()
                      ->joinLeft(array('tt' => Mage::getModel('translator/translation')->getResource()->getMainTable()),$where, array('translation', 'translation_id'));

                } else {

                    $where = 'main_table.string_id = tt.string_id'
                    .' AND tt.store_id = '.$store_id;

                    $ar = Mage::helper('translator')->getStoredSession('area');
                    if ($ar != '') {
                        $where .= ' AND tt.'.$ar.'=1';
                    } else {
                        $duplicates = Mage::getModel('translator/translation')->getDuplicatesList($store_id);
                        $where .= ($duplicates == '') ? '' : ' AND tt.translation_id NOT IN ('.$duplicates.')';
                    }

                    $this->getCollection()
                        ->getSelect()
                        ->joinLeft(array('tt' =>  Mage::getModel('translator/translation')->getResource()->getMainTable()),$where, array('translation', '  translation_id'));

                    $this->getCollection()->addAreaFilter($area);

                    $this->getChild('area_filter')->setInputValue($area);
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
        $this->addColumn('id', array(
            'header'    => Mage::helper('translator')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'string_id',
            'filter_index' => 'main_table.string_id',
        ));

        $this->addColumn('string', array(
            'header'    => Mage::helper('translator')->__('String'),
            'align'     =>'left',
            'index'     => 'string',
            'filter_index' => 'main_table.string',
        ));

        $this->addColumn('translation', array(
            'header'    => Mage::helper('translator')->__('Translation'),
            'align'     =>'left',
            'index'     => 'translation',
            'filter_index' => 'tt.translation',
        ));

        $this->addColumn('module', array(
            'header'    => Mage::helper('translator')->__('Scope'),
            'width'     => '150px',
            'index'     => 'module',
            'filter_index' => 'main_table.module',
        ));

        $this->addColumn('store_id', array(
            'header'        => Mage::helper('translator')->__('Translated Views'),
            'index'         => 'store_id',
            'filter_index' => 'tt.store_id',
            'type'          => 'store',
            'store_all'     => true,
            'store_view'    => true,
            'sortable'      => true,
            'filter_condition_callback'
                => array($this, '_filterStoreCondition'),
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('translator')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'status',
            'filter_index' => 'main_table.status',
            'type'      => 'options',
            'options'   => Mage::getModel('translator/status')->getOptionArray(),
        ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('translator')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'index'     => 'action',
                'sortable'  => false,
                'filter'  => false,
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('translator')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('translator')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('translator')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        foreach ($this->getCollection() as $item) {
            $translation = $item->getData('translation');
            $translation = preg_replace( "/^\'(.*)\'$/U", "$1", $translation);
            $translation = preg_replace( "/\'\'/U", "\'", $translation);
            $item->setData('translation', $translation);
        }
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
        $this->setMassactionIdField('main_table.string_id');
        $this->getMassactionBlock()->setFormFieldName('strings');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => Mage::helper('translator')->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => Mage::helper('translator')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('translator/status')->getOptionArray();
        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
            'label'=> Mage::helper('translator')->__('Change status'),
            'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('translator')->__('Status'),
                    'values' => $statuses
                )
            )
        ));

        $this->getMassactionBlock()->addItem('delete_trans', array(
            'label'=> Mage::helper('translator')->__('Delete Translations'),
            'url'  => $this->getUrl('*/*/massDeleteTrans'),
            'confirm'  => Mage::helper('translator')->__('This will delete the selected translations for the currently selected store view. Are you sure?')
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

    public function getAreaFilterHtml()
    {
        return $this->getChildHtml('area_filter');
    }

    public function getMainButtonsHtml()
    {
        $html = $this->getAreaFilterHtml();
        $html .= $this->getPathFilterHtml();
        $html .= parent::getMainButtonsHtml();
        return $html;
    }

    protected function _prepareLayout()
    {
      $this->setChild('path_filter',
          $this->getLayout()->createBlock('translator/adminhtml_strings_widget_button')
          ->setData(array(
              'label'     => Mage::helper('translator')->__('Path'),
              'onclick'   => $this->getJsObjectName().'.doFilter()',
              'class'   => 'task'
          ))
      );
      $this->setChild('area_filter',
          $this->getLayout()->createBlock('translator/adminhtml_strings_widget_area_button')
          ->setData(array(
              'label'     => Mage::helper('translator')->__('Area'),
              'onclick'   => $this->getJsObjectName().'.doFilter()',
              'class'   => 'task'
          ))
      );
      return parent::_prepareLayout();
    }
}
