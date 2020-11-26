<?php
class Wheelbarrow_Translator_Block_Adminhtml_Sources_Renderer_Locale extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $item = unserialize($row->getData($this->getColumn()->getIndex()));
        return $item['locale'];
    }
}
?>
