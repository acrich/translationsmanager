<?php
class Wheelbarrow_Translator_Block_Adminhtml_Sync extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_blockGroup = 'translator';
        $this->_controller = 'adminhtml';
        $this->_objectId = 'id';
        $this->_mode = 'sync';

        $this->_removeButton('save');
        $this->_removeButton('reset');
        $this->_removeButton('back');

        $url = $this->getUrl('*/*/*', array('status' => 'migrateCoreDb'));
        $this->_addButton('sync_db', array(
                'label'     => Mage::helper('translator')->__('Migrate Data From Database'),
                'onclick'   => "setLocation('".$url."');",
            ));

        $url = $this->getUrl('*/*/*', array('status' => 'importCsvFiles'));
        $this->_addButton('import_modules', array(
                'label'     => Mage::helper('translator')->__('Import Csv Data From Modules'),
                'onclick'   => "deleteConfirm('This action is extremely resource-intensive. It is recommended that you import the files one by one through the table below. Would you still like to attempt to import all the files together?', '".$url."');",
        ));

        $url = $this->getUrl('*/*/*', array('status' => 'importThemeCsvs'));
        $this->_addButton('import_themes', array(
                'label'     => Mage::helper('translator')->__('Import Csv Data From Themes'),
                'onclick'   => "setLocation('".$url."');",
        ));

        $this->_headerText = Mage::helper('translator')->__('Sync Data From External Resources');
    }
}