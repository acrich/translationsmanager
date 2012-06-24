<?php
class Magestance_Demo_Block_Adminhtml_Demo_Renderer_Store extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$store_ids =  $row->getData('store_id');
		var_dump($store_ids);
	}
}