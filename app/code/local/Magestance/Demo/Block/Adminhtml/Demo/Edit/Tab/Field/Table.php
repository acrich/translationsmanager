<?php
class Magestance_Demo_Block_Adminhtml_Demo_Edit_Tab_Field_Table extends Varien_Data_Form_Element_Abstract
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
    }
    
    public function getElementHtml()
    {
        
    	$html = '<table style="width:705px; border-collapse:collapse;" id="' . $this->getHtmlId() . '"'. $this->serialize($this->getHtmlAttributes()) .'>';
        $html .= '<tr><th>Hard Coded</th><th>Position</th><th>Value</th></tr>';
        $values = unserialize($this->getValue());
        foreach ($values as $key => $value) {
        	$checked = $value['hardcoded'] ? 'checked' : '';
        	$disabled = $value['hardcoded'] ? 'disabled' : '';
        	$html .= '<tr id="' . $key .'"><td style="border: 1px solid #AAA; width:50px;"><input style="width:100%;" class="input-text hardcoded" name="hardcoded"  type="checkbox"' . $checked . ' /></td>';
        	$html .= '<td style="border: 1px solid #AAA; padding: 2px; width: 40px;"><input class="input-text position" name="position"  style="width:70%; padding: 3px;" type="text"' . $disabled . ' value="' . $value['position'] . '" /></td>';
        	$html .= '<td style="border: 1px solid #AAA; padding: 2px; width: 400px;"><input class="input-text param" name="param" style="width:96%; padding: 3px;" type="text"' . $disabled . ' value="' . $value['value'] . '" /></td>';
        	$html .= '</tr>';
        }
        $html .= '</table>';
        $html .= '<input class="input-text" name="params" id="params" style="display:none;" type="text" value="" />';
        $html .= $this->getAfterElementHtml();
        $html .= '<script type="text/javascript">
        			$$("#parameters input.hardcoded").each(function(e) {
        				e.observe("click", hardcodeChange);
        			});
					function hardcodeChange(event) {
						var element = event.element();
						var checked = element.getValue();
						var tds = element.ancestors()[1];
						var param = tds.select("[type=\'text\']");
						if (checked) {
							param.each(function(v) {v.disabled = true;})
						} else {
							param.each(function(v) {v.disabled = false;})
						}
					}
        			
        			function processTable() {
        				elems = $$("table#parameters input.input-text");
        				var data = "";
        				for (var i=0;i<elems.length;i++) {
        					data += elems[i].ancestors()[1].identify() + ">>>" + elems[i].getAttribute("name") + ">>>" + elems[i].getValue() + "&&&";
        				}
        				$("params").setAttribute("value", data);
        				editForm.submit();
    				} 
        		</script>';
        return $html;
     }
}