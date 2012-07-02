<?php
class Magestance_Translator_Block_Adminhtml_Strings_Edit_Tab_Field_Table extends Varien_Data_Form_Element_Abstract
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
    }
    
    public function getElementHtml()
    {
        
    	$html = '<table style="width:705px; border-collapse:collapse;" id="' . $this->getHtmlId() . '"'. $this->serialize($this->getHtmlAttributes()) .'>';
        $html .= '<tr><th>Hard Coded</th><th>Position</th><th>Value</th><th></th></tr>';
        $values = unserialize($this->getValue());
        if (count($values)) {
	        foreach ($values as $key => $value) {
	        	$checked = ($value['hardcoded'] == '0') ? '' : 'checked';
	        	$disabled = ($value['hardcoded'] == '0') ? '' : 'disabled';
	        	$html .= '<tr id="' . $key .'"><td style="border: 1px solid #AAA; width:80px;"><input style="width:100%;" class="input-text hardcoded" name="hardcoded"  type="checkbox"' . $checked . ' /></td>';
	        	$html .= '<td style="border: 1px solid #AAA; padding: 2px; width: 80px;"><input class="input-text position" name="position"  style="width:70%; padding: 3px;" type="text"' . $disabled . ' value="' . $value['position'] . '" /></td>';
	        	$html .= '<td style="border: 1px solid #AAA; padding: 2px; width: 400px;"><input class="input-text param" name="param" style="width:96%; padding: 3px;" type="text"' . $disabled . ' value="' . $value['param'] . '" /></td>';
	        	$html .= '<td style="border: 1px solid #AAA; text-align: right; padding: 2px;"><input type="button" value="Remove Parameter" class="remove-param" onclick="str_form.removeParam(this)" /><td>';
	        	$html .= '</tr>';
	        }
        }
        $html .= '<tr><td></td><td></td><td></td><td style="text-align:right;">';
        $html .= '<input type="button" value="Add Another Parameter" id="add-param" onclick="str_form.addParam(this)" /></td></tr>';
        $html .= '</table>';
        $html .= '<input class="input-text" name="param" id="param" style="display:none;" type="text" value="" />';
        $html .= $this->getAfterElementHtml();
        return $html;
     }
}