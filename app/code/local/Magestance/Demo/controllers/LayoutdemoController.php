<?php
class Magestance_Demo_LayoutdemoController extends Mage_Core_Controller_Front_Action
{
	public function _initLayout()
	{
		$layout = Mage::getSingleton('core/layout');
		$layout->addOutputBlock('root');
		$additional_head = $layout->createBlock('nofrills_booklayout/template', 'additional_head')
		->setTemplate('simple-page/head.phtml');
		
		$sidebar = $layout->createBlock('core/template')
		->setTemplate('simple-page/sidebar.phtml');
		
		$content = $layout->createBlock('core/text-list', 'content');
		
		$root = $layout->createBlock('core/template', 'root')
		->setTemplate('simple-page/2col.phtml')
		->insert($additional_head)
		->insert($sidebar)
		->insert($content);
		
		return $layout;		
	}
	
	public function indexAction()
	{
		$layout = $this->_initLayout();
		
		$text = $layout->createBlock('core/text', 'words');
		$text->setText('bla bla bla bla');
		
		$content = $layout->getBlock('content');
		
		$layout->setDirectOutput('true');
		$layout->getOutput();
		
		exit;
	}
}