<?php
class Wheelbarrow_Translator_Block_Adminhtml_Area_Switcher extends Mage_Adminhtml_Block_Store_Switcher
{	
	public function getSwitchUrl()
    {
        return $this->getUrl('*/*/*', array('_current' => true, 'area_switch' => true));
    }
    
    public function isSelected($option)
    {
    	return (strtolower($option) == Mage::helper('translator')->getStoredSession('area')) ? 'selected="selected"' : '';
    }
    
    public function getOptions()
    {
    	return array('', 'Frontend', 'Adminhtml', 'Install');
    }
    
    public function fetchView($fileName)
    {
    	Varien_Profiler::start($fileName);
    	extract ($this->_viewVars, EXTR_SKIP);
    	$do = $this->getDirectOutput();
    
    	if (!$do) {
    		ob_start();
    	}
    
    	include Mage::getModuleDir(null, 'Wheelbarrow_Translator') .DS.'Templates'.DS.'area_switcher.phtml';
    
    	if (!$do) {
    		$html = ob_get_clean();
    	}
    	else { $html = '';
    	}
    	Varien_Profiler::stop($fileName);
    	return $html;
    }
}