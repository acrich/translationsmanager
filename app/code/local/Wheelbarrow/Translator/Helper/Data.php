<?php

class Wheelbarrow_Translator_Helper_Data extends Mage_Core_Helper_Abstract
{	
	const DEFAULT_STORE = Mage_Core_Model_App::ADMIN_STORE_ID;
	const DEFAULT_AREA = '';
	
	/**
	 * Retrieves the default value for the sought after session variable.
	 * 
	 * @param string $type
	 * The suffix of the session variable's name. Used to know which value to fetch.
	 * 
	 * @return mixed
	 * The default value for the requested session variable.
	 */
	protected function _getDefault($type)
	{
		return constant('self::DEFAULT_'.$type);
	}
	
	/**
	 * A setter for the session variables used by this helper.
	 * 
	 * @param string $type
	 * The session variable's suffix. Used to identify what to fetch.
	 * 
	 * @param mixed $value
	 * The value to save to that session variable.
	 * 
	 * @return
	 * The value passed in as parameter is returned, without regard to success or failure.
	 */
	public function setStoredSession($type, $value)
	{
		Mage::getSingleton('adminhtml/session')->setData('wheelbarrow_'.$type, $value);
		return $value;
	}
	
	/**
	 * A setter for the session variables used by this helper.
	 * 
	 * @param string $type
	 * The value to save to that session variable.
	 * 
	 * @return
	 * Either the value in the session variable (if one exists) or the default for the requested
	 * variable.
	 */
	public function getStoredSession($type)
	{
		$value = Mage::getSingleton('adminhtml/session')->getData('wheelbarrow_'.$type);
		if (is_null($value)) {
			$value = $this->_getDefault(strtoupper($type));
		}
		return $value;
	}
}