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
		
			$model = Mage::getModel('demo/translator');
			$register = $model->_getJobRegister();
			$data = $register->data;
			$message = $data->message;
			
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
			
			$data->message = $message;
			$register->data = $data;
			$model->_setJobRegister($register);

			$file = file_get_contents($template);
			if ($file)
			{
				preg_match_all("/\-\>\_\_\('.*'(\)|,)/U", $file, $matches, PREG_OFFSET_CAPTURE);
				foreach ($matches[0] as $match)
				{
					if (count($match))
					{
						$string = preg_replace("/\-\>\_\_\('(.*)'(\)|,)/U", "$1", $match[0]);
						
						$collection = Mage::getModel('demo/translator')
						->getCollection()
						->addAttributeToSelect('string')
						->addAttributeToSelect('module')
						->addFieldToFilter('string',array('eq'=>$string))
						->addFieldToFilter('module',array('eq'=>$module_name));
						
						if (count($collection)) {
							$item = $collection->getFirstItem();
							$targets = $item->getTargets();
							$targets[] = array('type' => 'template', 'path' => $data->path, 'location' => $template, 'offset' => $match[1]);
							$collection->getFirstItem()->setTargets($targets)->save();
						} else {
							$item = new Magestance_Demo_Model_Translator();
							$item->setString($string);
							$item->setModule($module_name);
							$item->setTarget(array(array('type' => 'template', 'path' => $data->path, 'location' => $template, 'offset' => $match[1])));
							Mage::getModel('demo/translator')
								->getCollection()
								->addItem($item)->save();
						}
					}
				}
			}
		}
	}
	public function notifyCompletion($observer)
	{
		if (array_key_exists(self::FLAG_SHOW_LAYOUT, $_GET))
		{
			$model = Mage::getModel('demo/translator');
			$model->_setJobRegister(array('state' => false, 'data' => array(), 'action' => 'translate_path_sync'));
		}
	}
}