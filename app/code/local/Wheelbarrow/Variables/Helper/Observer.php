<?php
class Wheelbarrow_Variables_Helper_Observer extends Mage_Core_Helper_Abstract
{
	public function parseCustomVars($observer)
	{
		$response = $observer->getEvent()->getFront()->getResponse();
		$html = $response->getBody();
		
		$callback = function($matches) {
			$var = Mage::getModel('core/variable');
			$var->setStoreId(Mage::app()->getStore()->getId());
			return $var->loadByCode($matches[1])->getValue('html');
		};
		
		if (!$this->isAdmin()) {
			$html = preg_replace_callback("/{{customVar code=(.*)}}/U", $callback, $html);
		}
		$response->setBody($html);
		Mage::app()->setResponse($response);

		return $this;
	}
	
	public function isAdmin()
	{
		if(Mage::app()->getStore()->isAdmin())
		{
			return true;
		}
	
		if(Mage::getDesign()->getArea() == 'adminhtml')
		{
			return true;
		}
	
		return false;
	}
}