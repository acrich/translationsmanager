<?php
class Wheelbarrow_Translator_Block_Adminhtml_Strings_Widget_Button extends Mage_Adminhtml_Block_Widget_Button
{
    protected $_input_value;

    protected function _toHtml()
    {
        $html = '<span class="filter">'
                . '<label>'.Mage::helper('translator')->__('Path:').' </label>'
                . '<input type="text" id="stringGrid_filter_path" name="path" class="input-text no-changes" value="'
                . $this->getInputValue()
                . '" style="width:180px;"></input></span>&nbsp;&nbsp;&nbsp;';

        return $html;
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
