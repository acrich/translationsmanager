<?php
class Magestance_Translator_Model_Translate_Inline extends Mage_Core_Model_Translate_Inline
{
    /**
     * Parse and save edited translate
     *
     * @param array $translate
     * @return Magestance_Translator_Model_Translate_Inline
     */
    public function processAjaxPost($translate)
    {
        if (!$this->isAllowed()) {
            return $this;
        }

        $resource = Mage::getResourceModel('translator/translate_string');
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
