<?php
class Wheelbarrow_Translator_Block_Adminhtml_Sources_Renderer_Area extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $item = unserialize($row->getData($this->getColumn()->getIndex()));
        $output = '';
        foreach ($item['areas'] as $area) {
            $output .= $area . ', ';
        }
        $output = substr($output, 0, -2);
        return $output;
    }
}
?>