<?php
class Magestance_Translator_Block_Adminhtml_Strings_Renderer_String extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$value =  $row->getData($this->getColumn()->getIndex());
		return unserialize($value);	
	}
}