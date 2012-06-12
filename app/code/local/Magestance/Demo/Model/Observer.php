<?php
class Magestance_Demo_Model_Observer
{
	const FLAG_SHOW_LAYOUT 			= 'magestanceScan';
	
	public function getBlockTemplate($observer)
	{
		if (array_key_exists(self::FLAG_SHOW_LAYOUT, $_GET))
		{
			$block = $observer->getEvent()->getBlock();

			$module_name = $block->getModuleName();
			$alias = $block->getBlockAlias();
			$template = Mage::getBaseDir() . DS . 'app' . DS . 'design' . DS . $block->getTemplateFile();
		
			$queue = Mage::helper('demo/queue')->pop('sync');
			
			$queue = Mage::helper('demo/queue')->popAndPush('sync');
			$message = $queue->_data['message'];
			
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
			
			$queue->_data['message'] = $message;
			Mage::helper('demo/queue')->replace('sync', $queue);
			
			$path = $queue->_data['path'];
			
			$file = file_get_contents($template);
			if ($file)
			{
				preg_match_all("/\-\>\_\_\('.*'(\)|,)/U", $file, $matches, PREG_OFFSET_CAPTURE);
				foreach ($matches[0] as $match)
				{
					if (count($match))
					{
						$string = preg_replace("/\-\>\_\_\('(.*)'(\)|,)/U", "$1", $match[0]);
						$string_id = Mage::getModel('demo/string')->createItem($string, $module_name);

						Mage::getModel('demo/path')->createItem($path, $template, $match[1], $string_id);
					}
				}
			}
		}
	}
	public function notifyCompletion($observer)
	{
		if (array_key_exists(self::FLAG_SHOW_LAYOUT, $_GET))
		{
			Mage::helper('demo/sync')->close();
		}
	}
}