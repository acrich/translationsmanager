<?php
class Magestance_Demo_Block_Adminhtml_Store_Switcher extends Mage_Adminhtml_Block_Store_Switcher
{
	public function getStoreId()
	{
		return Mage::helper('demo')->getCurrentStore();
	}
	
	public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }
        return $this->getUrl('*/*/*', array('_current' => true, 'switch' => true, $this->_storeVarName => null));
    }
}