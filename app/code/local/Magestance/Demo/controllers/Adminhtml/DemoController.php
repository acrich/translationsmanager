<?php

class Magestance_Demo_Adminhtml_DemoController extends Mage_Adminhtml_Controller_Action
{	
	protected function _setStore() {
		if ($this->getRequest()->getParam('switch')) {
			$store_id = Mage::helper('demo')->setCurrentStore($this->getRequest()->getParam('store'));
		} else {
			$store_id = Mage::helper('demo')->getCurrentStore();
		}
	}
 
	public function indexAction() {
		$this->loadLayout()
			->_setActiveMenu('demo/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

		$this->_setStore();

		$this->_addContent($this->getLayout()->createBlock('demo/adminhtml_store_switcher'))
			->_addContent($this->getLayout()->createBlock('demo/adminhtml_demo'));
		
		$this->renderLayout();
	}
	
	public function migrateCoreDbAction() {
		Mage::getModel('demo/translate')->migrateCoreDb();
		$this->_redirect('*/*/index');
	}
	
	public function importCsvFilesAction() {
		$this->loadLayout();
		
		$sync = Mage::helper('demo/sync');
		$sync->init($sync::CSV_SCAN_ACTION);		
		Mage::helper('demo/queue')->init($sync::CSV_QUEUE_NAME);
		Mage::helper('demo/importer')->pushCsvFilesToQueue();
		
		$messages = $this->getLayout()->createBlock('core/text');
		$messages->setText('<div id="magestance-messages"></div>');
		$this->_addContent($messages);
		
		$head = $this->getLayout()->getBlock('head');
		$head->addJs('magestance/sync.js');
		
		$this->renderLayout();
	}
	
	public function syncAction() {
		$output = Mage::helper('demo/sync')->iterator();
		$this->getResponse()->setBody(json_encode($output));
	}
	
	public function addpathAction() {
		$this->loadLayout();
		$this->_setActiveMenu('demo/addpath');
		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Add a New Path'), Mage::helper('adminhtml')->__('Add a New Path'));		
		
		$form = $this->getLayout()->createBlock('demo/adminhtml_demo_addpath');
		$this->_addContent($form);
		
		$head = $this->getLayout()->getBlock('head');
		$status = $this->getRequest()->getParam('status');
		if ($status == "1")
		{
			$messages = $this->getLayout()->createBlock('core/text');
			$messages->setText('<div id="magestance-messages">Progress:<br /></div>');
			$this->_addContent($messages);
			$head->addJs('magestance/sync.js');
		}
		
		$this->renderLayout();
	}
	
	public function addpathresponseAction() {
		$data = $this->getRequest()->getPost();
		
		$sync = Mage::helper('demo/sync');
		$sync->init($sync::PATH_SCAN_ACTION);
		
		Mage::helper('demo/queue')->setRegisterData('sync', array(
					'go_to_url' => true, 
					'path' => $data['path'], 
					'message' => ''
				));

		$this->_redirect('*/*/addpath/status/1');
	}

	public function editAction() {
		$this->_setStore();
		
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('demo/string')->load($id);
		
		$string = $model->getString();
		$model->setString(unserialize($string));
		
		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
			
			$translation_id = Mage::getModel('demo/translation')->getIdByParams($model->getId(), $store_id);
			if ($translation_id) {
				$translation = Mage::getModel('demo/translation')->load($translation_id)->getTranslation();	
				$translation = unserialize($translation);
			} else {
				$translation_id = 0;
				$translation = '';
			}
			$model->setTranslationId($translation_id)
					->setTranslation($translation);
			
			Mage::register('demo_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('demo/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));
			
			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('demo/adminhtml_demo_edit'))
				->_addLeft($this->getLayout()->createBlock('demo/adminhtml_store_switcher'))
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
			try {
				$params = explode('&&&', $data['params'], -1);
				$data['param'] = array();
				foreach ($params as $param) {
					$param = explode('>>>', $param);
					if (array_key_exists($param[0], $data['param']) && is_array($data['param'][$param[0]])) {
						$data['param'][$param[0]][$param[1]] = $param[2];
					} else {
						$data['param'][$param[0]] = array($param[1] => $param[2]);
					}
				}
				$string_id = $this->getRequest()->getParam('id');
				if ($string_id != 0) {
					$data['string_id'] = $string_id;
				}
				
				$data['store_id'] = Mage::helper('demo')->getCurrentStore();
				
				$model = Mage::getModel('demo/translate')->addEntryWithId($data);

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('demo')->__('Item was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $string_id));
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
				$model = Mage::getModel('demo/translate')
					->deleteEntry($this->getRequest()->getParam('id'));
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}
	
	public function deleteTranslationAction() {
		if( $this->getRequest()->getParam('translation_id') > 0 ) {
			try {
				Mage::getModel('demo/translation')
				->load($this->getRequest()->getParam('translation_id'))->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Translation was successfully deleted'));
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
                    $demo = Mage::getModel('demo/translate')->deleteEntry($demoId);
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
                    $demo = Mage::getSingleton('demo/string')
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
    
    public function massDeleteTransAction() {
    	$string_ids = $this->getRequest()->getParam('demo');
    	$store_id = Mage::helper('demo')->getCurrentStore();
    	if(!is_array($string_ids)) {
    		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
    	} else {
    		try {
    			foreach ($string_ids as $string_id) {
    				$id = Mage::getModel('demo/translation')->getIdByParams($string_id, $store_id);
    				Mage::getModel('demo/translation')->load($id)->delete();
    			}
    			Mage::getSingleton('adminhtml/session')->addSuccess(
    					Mage::helper('adminhtml')->__(
    							'Total of %d record(s) were successfully deleted', count($string_ids)
    					)
    			);
    		} catch (Exception $e) {
    			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
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