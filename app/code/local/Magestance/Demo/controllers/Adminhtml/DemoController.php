<?php

class Magestance_Demo_Adminhtml_DemoController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('demo/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}
 
	public function indexAction() {
		$this->_initAction();
		
		$grid = $this->getLayout()->createBlock('demo/adminhtml_demo');
		$this->_addContent($grid);
		
		$this->renderLayout();
	}
	
	public function migrateTranslationsAction() {
		Mage::getModel('demo/translator')->migrateDbTranslations();
		$this->_redirect('*/*/index');
	}
	
	public function syncTranslationsAction() {
		$this->loadLayout();
		
		Mage::getModel('demo/translator')->syncFileTranslations();

		$messages = $this->getLayout()->createBlock('core/text');
		$messages->setText('<div id="magestance-messages"></div>');
		$this->_addContent($messages);
		
		$head = $this->getLayout()->getBlock('head');
		$head->addJs('magestance/sync.js');
		
		$this->renderLayout();
	}
	
	public function syncAction() {
		
		$register = Mage::getModel('demo/translator')->_getJobRegister();

		//switch ($state['action']) {
			//case 'translation_files_sync':
				$output = Mage::getModel('demo/translator')->iterateSyncJob($register->action);
				//break;
		//}		
		
		$this->getResponse()->setBody(json_encode($output));
	}
	
	public function addpathAction() {
		$this->loadLayout();
		$this->_setActiveMenu('demo/addpath');	
		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Add a New Path'), Mage::helper('adminhtml')->__('Add a New Path'));		
		
		$form = $this->getLayout()->createBlock('demo/adminhtml_demo_addpath');
		$this->_addContent($form);
		
		$messages = $this->getLayout()->createBlock('core/text');
		$messages->setText('<div id="magestance-messages"></div>');
		$this->_addContent($messages);
		
		$head = $this->getLayout()->getBlock('head');
		$status = $this->getRequest()->getParam('status');
		if ($status == "1")
		{	
			$head->addJs('magestance/sync.js');
		}
		
		$this->renderLayout();
	}
	
	public function initpathsyncAction() {
		$data = $this->getRequest()->getPost();
		
		Mage::getModel('demo/translator')->_initJobRegister('translate_path_sync', array('init' => true, 'path' => $data['path'], 'message' => ''));
		
		$this->_redirect('*/*/addpath/status/1');
	}

	
	public function checkstateAction() {
		$state = Mage::getModel('demo/demo');
		$state->load('page_scan_state');
		if ($state->getValue() == '1')
		{
			$url = Mage::getModel('demo/demo');
			$url->load('page_scan_url');
			$data = Mage::getModel('demo/demo');
			$data->load('page_scan_data');
			$this->getResponse()->setBody(json_encode(array('state' => true, 'url' => $url->getValue(), 'data' => $data->getValue())));
		} else {
			$data = Mage::getModel('demo/demo');
			$data->load('page_scan_data');
			$this->getResponse()->setBody(json_encode(array('state' => false, 'data' => $data->getValue())));
		}
	}

	public function editAction() {
		
		$store_id = $this->getRequest()->getParam('store');
		
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('demo/translator')->load($id);
		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('demo_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('demo/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));
			
			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('demo/adminhtml_demo_edit'))
				->_addLeft($this->getLayout()->createBlock('adminhtml/store_switcher'))
				->_addLeft($this->getLayout()->createBlock('demo/adminhtml_demo_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('demo')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			
			$model = Mage::getModel('demo/translator');
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('demo')->__('Item was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('demo')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('demo/translator');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $demoIds = $this->getRequest()->getParam('demo');
        if(!is_array($demoIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($demoIds as $demoId) {
                    $demo = Mage::getModel('demo/translator')->load($demoId);
                    $demo->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($demoIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $demoIds = $this->getRequest()->getParam('demo');
        if(!is_array($demoIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($demoIds as $demoId) {
                    $demo = Mage::getSingleton('demo/translator')
                        ->load($demoId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($demoIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'demo.csv';
        $content    = $this->getLayout()->createBlock('demo/adminhtml_demo_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'demo.xml';
        $content    = $this->getLayout()->createBlock('demo/adminhtml_demo_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}