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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Inline Translations PHP part
 *
 * @category   Mage
 * @package    Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magestance_Demo_Model_Translate_Inline extends Mage_Core_Model_Translate_Inline
{
    /**
     * Parse and save edited translate
     *
     * @param array $translate
     * @return Mage_Core_Model_Translate_Inline
     */
    public function processAjaxPost($translate)
    {
        if (!$this->isAllowed()) {
            return $this;
        }

        $resource = Mage::getResourceModel('demo/translate_string');
        foreach ($translate as $t) {
            if (Mage::getDesign()->getArea() == 'adminhtml') {
                $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
            } else if (empty($t['perstore'])) {
                $resource->deleteTranslate($t['original'], null, false);
                $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
            } else {
                $storeId = Mage::app()->getStore()->getId();
            }

            $resource->saveTranslate($t['original'], $t['custom'], null, $storeId);
        }

        return $this;
    }
}
