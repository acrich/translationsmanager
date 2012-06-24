<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Button widget
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magestance_Demo_Block_Adminhtml_Widget_Button extends Mage_Adminhtml_Block_Widget_Button
{
	protected $_input_value;
	
	protected function _toHtml()
	{
		$html = '<span class="filter">'
				. '<label>Path: </label>'
				. '<input type="text" id="demoGrid_filter_path" name="path" class="input-text no-changes" value="'
				. $this->getInputValue()
				. '" style="width:330px;"></input></span>&nbsp;&nbsp;&nbsp;';
	
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
