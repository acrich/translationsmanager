<?php
class Magestance_Demo_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/demo?id=15 
    	 *  or
    	 * http://site.com/demo/id/15 	
    	 */
    	/* 
		$demo_id = $this->getRequest()->getParam('id');

  		if($demo_id != null && $demo_id != '')	{
			$demo = Mage::getModel('demo/demo')->load($demo_id)->getData();
		} else {
			$demo = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($demo == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$demoTable = $resource->getTableName('demo');
			
			$select = $read->select()
			   ->from($demoTable,array('demo_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$demo = $read->fetchRow($select);
		}
		Mage::register('demo', $demo);
		*/

		//echo 'testing.';
    	
		$this->loadLayout();
		$this->renderLayout();
    }
}