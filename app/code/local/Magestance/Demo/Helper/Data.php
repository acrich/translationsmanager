<?php

class Magestance_Demo_Helper_Data extends Mage_Core_Helper_Abstract
{	
	public function setCurrentStore($store_id)
	{
		if (is_null($store_id)) {
			$store_id = Mage_Core_Model_App::ADMIN_STORE_ID;
		}
		Mage::getSingleton('adminhtml/session')->setData('magestance_store', $store_id);
		return $store_id;
	}
	
	public function getCurrentStore()
	{
		$id = Mage::getSingleton('adminhtml/session')->getData('magestance_store');
		if (is_null($id)) {
			$id = Mage_Core_Model_App::ADMIN_STORE_ID;
		}
		return $id;
	}
}