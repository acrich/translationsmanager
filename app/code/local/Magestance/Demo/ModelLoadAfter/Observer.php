<?php
class Magestance_Demo_ModelLoadAfter_Observer
{
	public function notify_my_log($observer)
	{
		$output = $observer->getEvent();
	}
}
