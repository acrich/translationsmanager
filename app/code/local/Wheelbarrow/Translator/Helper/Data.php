<?php

class Wheelbarrow_Translator_Helper_Data extends Mage_Core_Helper_Abstract
{	
	const DEFAULT_store = Mage_Core_Model_App::ADMIN_STORE_ID;
	const DEFAULT_area = '';
	
	protected function _getDefault($type)
	{
		return constant('self::DEFAULT_'.$type);
	}
	
	protected function _setVar($type, $value)
	{
		if (is_null($value)) {
			$value = $this->_getDefault($type);
		}
		Mage::getSingleton('adminhtml/session')->setData('wheelbarrow_'.$type, $value);
		return $value;
	}
	
	protected function _getVar($type)
	{
		$value = Mage::getSingleton('adminhtml/session')->getData('wheelbarrow_'.$type);
		if (is_null($value)) {
			$value = $this->_getDefault($type);
		}
		return $value;
	}
	
	public function setCurrentStore($store_id)
	{
		return $this->_setVar('store', $store_id);
	}
	
	public function getCurrentStore()
	{
		return $this->_getVar('store');
	}
	
	public function setArea($area)
	{
		return $this->_setVar('area', $area);
	}
	
	public function getArea()
	{
		return $this->_getVar('area');
	}
}