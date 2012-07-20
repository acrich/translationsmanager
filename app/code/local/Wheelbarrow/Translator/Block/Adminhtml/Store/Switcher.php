<?php
class Wheelbarrow_Translator_Block_Adminhtml_Store_Switcher extends Mage_Adminhtml_Block_Store_Switcher
{
	public function getStoreId()
	{
		return Mage::helper('translator')->getCurrentStore();
	}
	
	public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }
        return $this->getUrl('*/*/*', array('_current' => true, 'switch' => true, $this->_storeVarName => null));
    }
    
    public function fetchView($fileName)
    {
    	Varien_Profiler::start($fileName);
    	extract ($this->_viewVars, EXTR_SKIP);
    	$do = $this->getDirectOutput();
    
    	if (!$do) {
    		ob_start();
    	}
    
    	include Mage::getModuleDir(null, 'Wheelbarrow_Translator') .DS.'Templates'.DS.'switcher.phtml';
    
    	if (!$do) {
    		$html = ob_get_clean();
    	}
    	else { $html = '';
    	}
    	Varien_Profiler::stop($fileName);
    	return $html;
    }
}