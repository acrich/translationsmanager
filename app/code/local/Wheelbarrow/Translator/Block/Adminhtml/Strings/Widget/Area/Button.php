<?php
class Wheelbarrow_Translator_Block_Adminhtml_Strings_Widget_Area_Button extends Mage_Adminhtml_Block_Widget_Button
{
    protected $_input_value;

    protected function _toHtml()
    {
        $html = '<span class="filter">'
                . '<label>'.Mage::helper('translator')->__('Area:').' </label>'
                . '<select id="stringGrid_area_path" name="area">';

        foreach (array('', 'Frontend', 'Adminhtml', 'Install') as $area) {
            $html .= '<option value="'.$area.'" class="input-text no-changes" '.$this->isSelected($area).'>'.$area.'</option>';
        }

        $html .= '</select>&nbsp;&nbsp;&nbsp;';

        return $html;
    }

    public function isSelected($option)
    {
        return (strtolower($option) == $this->getInputValue()) ? 'selected' : '';
    }

    public function getInputValue()
    {
        return $this->_input_value;
    }

    public function setInputValue($value)
    {
        $this->_input_value = $value;
    }
}
