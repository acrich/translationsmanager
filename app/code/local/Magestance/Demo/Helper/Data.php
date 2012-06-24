<?php

class Magestance_Demo_Helper_Data extends Mage_Core_Helper_Abstract
{
	protected $_storeId = 0;
	
	public function getStoreId()
	{
		return $this->_storeId;
	}
	
	public function setStoreId($store)
	{
		$this->_storeId = $store;
	}
}