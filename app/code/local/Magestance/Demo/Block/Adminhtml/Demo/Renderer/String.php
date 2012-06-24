<?php
class Magestance_Demo_Block_Adminhtml_Demo_Renderer_String extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$value =  $row->getData($this->getColumn()->getIndex());
		return unserialize($value);	
	}
}