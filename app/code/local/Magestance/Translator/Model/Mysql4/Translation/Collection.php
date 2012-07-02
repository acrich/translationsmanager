<?php
class Magestance_Translator_Model_Mysql4_Translation_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct()
	{
		$this->_init('translator/translation');
	}
	
	protected function _afterLoad()
	{
		foreach ($this as $item) {
			$translation = $item->getData('translation');
    		$translation = preg_replace( "/^\'(.*)\'$/U", "$1", $translation);
    		$translation = preg_replace( "/\'\'/U", "\'", $translation);
    		$item->setData('translation', $translation);
		}

    	return $this;
	}
}