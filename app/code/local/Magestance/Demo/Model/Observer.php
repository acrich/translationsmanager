<?php
class Magestance_Demo_Model_Observer
{
	const FLAG_SHOW_LAYOUT 			= 'magestanceScan';
	
	public function getBlockTemplate($observer)
	{
		if (array_key_exists(self::FLAG_SHOW_LAYOUT, $_GET))
		{
			$block = $observer->getEvent()->getBlock();
			$alias = $block->getBlockAlias();
			$template = Mage::getBaseDir() . DS . 'app' . DS . 'design' . DS . $block->getTemplateFile();
			$model = Mage::getModel('demo/demo');
			$model->load('page_scan_data');
			$current_data = json_decode($model->getValue());
			if (is_array($current_data))
			{
				$current_data[] = array('alias' => $alias, 'template' => $template);
			} else {
				$current_data = array(array('alias' => $alias, 'template' => $template));
			}
			$model->setValue(json_encode($current_data));
			$model->save();
			
			
			$my_file = file_get_contents($template);
			if ($my_file)
			{
				preg_match_all("/\-\>\_\_\('.*'\)/", $my_file, $matches, PREG_OFFSET_CAPTURE);
				foreach ($matches[0] as $match)
				{
					if (count($match))
					{
						var_dump($match);
					}
				}
				

				/* Archived:
				 * //@todo loop through this to find all occurances in each file, and see whether there's some better way...
				$str = strstr($my_file, "->__('");
				$sub = substr($str, 6);
				Mage::log($sub, null, 'shay.log');
				$end_pos = strpos($sub, "'");
				Mage::log($end_pos, null, 'shay.log');
				$sub = substr($sub, 0, $end_pos);
				Mage::log($sub, null, 'shay.log');
				*/
			}
		}
	}
	public function notifyCompletion($observer)
	{
		if (array_key_exists(self::FLAG_SHOW_LAYOUT, $_GET))
		{
			$state = Mage::getModel('demo/demo');
			$state->load('page_scan_state');
			$state->setValue('0');
			$state->save();
			$data = Mage::getModel('demo/demo');
			$data->load('page_scan_data');
			$data->setValue('');
			$data->save();
			Mage::log('done.', null, 'shay.log');
		}
	}
}