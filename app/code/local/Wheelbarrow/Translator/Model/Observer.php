<?php
class Wheelbarrow_Translator_Model_Observer
{
    const FLAG_SHOW_LAYOUT             = 'translateScan';

    public function getObserverFlag()
    {
        return self::FLAG_SHOW_LAYOUT;
    }

    public function getBlockTemplate($observer)
    {
        if (array_key_exists(self::FLAG_SHOW_LAYOUT, $_GET))
        {
            $block = $observer->getEvent()->getBlock();

            $module_name = $block->getModuleName();
            $alias = $block->getBlockAlias();
            $template = Mage::getBaseDir() . DS . 'app' . DS . 'design' . DS . $block->getTemplateFile();

            $register = Mage::helper('translator/sync')->getRegister();

            $message = $register['data']['message'];

            if ($alias) {
                $message .= 'Scanned block: ' . $alias;
            }
            if ($block->getTemplateFile()) {
                if ($alias) {
                    $message .= ', with template: ' . $template;
                } else {
                    $message .= 'Scanned template: ' . $template;
                }
            }
            $message .= '<br />';

            $register['data']['message'] = $message;

            $path = $register['data']['path'];

            Mage::helper('translator/sync')->setRegister($register);

            $file = file_get_contents($template);
            if ($file)
            {
                preg_match_all("/\-\>\_\_\('.*'(\)|,)/U", $file, $matches, PREG_OFFSET_CAPTURE);
                foreach ($matches[0] as $match)
                {
                    if (count($match))
                    {
                        $string = preg_replace("/\-\>\_\_\('(.*)'(\)|,)/U", "$1", $match[0]);
                        $string_id = Mage::getModel('translator/string')->createItem(array(
                                    'string' => $string,
                                    'module' => $module_name
                                ));

                        Mage::getModel('translator/path')->createItem(array(
                                'path' => $path,
                                'file' => $template,
                                'offset' => $match[1],
                                'string_id' => $string_id
                                ));
                    }
                }
            }
        }
    }
    public function notifyCompletion($observer)
    {
        if (array_key_exists(self::FLAG_SHOW_LAYOUT, $_GET))
        {
            Mage::helper('translator/sync')->setRegister(array('state' => false));
        }
    }
}