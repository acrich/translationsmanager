<?php

class Magestance_Translator_Adminhtml_TranslatorController extends Mage_Adminhtml_Controller_Action
{	
	protected function _setStore() {
		if ($this->getRequest()->getParam('switch')) {
			$store_id = Mage::helper('translator')->setCurrentStore($this->getRequest()->getParam('store'));
		} else {
			$store_id = Mage::helper('translator')->getCurrentStore();
		}
		return $store_id;
	}
 
	public function indexAction() {

		$this->loadLayout()
			->_setActiveMenu('translator/manage')
			->_addBreadcrumb(Mage::helper('translator')->__('Translations Manager'), Mage::helper('translator')->__('translations Manager'));

		$this->_setStore();

		$this->_addContent($this->getLayout()->createBlock('translator/adminhtml_store_switcher'))
			->_addContent($this->getLayout()->createBlock('translator/adminhtml_strings'));
		
		$this->renderLayout();
	}
	
	
	public function manageAction() {
		$this->_forward('index');
	}

	public function editAction() {
		$store_id = $this->_setStore();
		
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('translator/string')->load($id);
		
		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
			
			$translation_id = Mage::getModel('translator/translation')->getIdByParams($model->getId(), $store_id);
			if ($translation_id) {
				$translation = Mage::getModel('translator/translation')->load($translation_id)->getTranslation();	
			} else {
				$translation_id = 0;
				$translation = '';
			}
			$model->setTranslationId($translation_id)
					->setTranslation($translation);
			
			Mage::register('translator_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('translator/manage');

			$this->_addBreadcrumb(Mage::helper('translator')->__('Translations Manager'), Mage::helper('translator')->__('Translations Manager'));
			
			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->getLayout()->getBlock('head')->addJs('magestance/edit.js');
			
			$this->_addContent($this->getLayout()->createBlock('translator/adminhtml_strings_edit'))
				->_addLeft($this->getLayout()->createBlock('translator/adminhtml_store_switcher'))
				->_addLeft($this->getLayout()->createBlock('translator/adminhtml_strings_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('translator')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
	
	public function pathsGridAction()
	{
		$this->getResponse()->setBody(
				$this->getLayout()->createBlock('translator/adminhtml_strings_edit_tab_paths', 'translator.strings.paths')
				->toHtml()
		);
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			try {
				$params = explode('&&&', $data['param'], -1);
				unset($data['param']);
				$data['parameters'] = array();
				foreach ($params as $param) {
					$param = explode('>>>', $param);
					if (!is_array($data['parameters'][$param[0]])) {
						$data['parameters'][$param[0]] = array();
					}
					if ($param[1] == 'hardcoded') {
						$param[2] = ($param[2] == 'null') ? false : true;
					}
					$data['parameters'][$param[0]][$param[1]] = $param[2];
				}
				$string_id = $this->getRequest()->getParam('id');
				if (!is_null($string_id) && $string_id != 0) {
					$data['string_id'] = $string_id;
					$string = Mage::getModel('translator/string')->load($string_id);
					if ($string->getString() != $data['string']) {
						$path_ids = Mage::getModel('translator/path')->getPathIdsByStringId($string_id);
						foreach ($path_ids as $path_id) {
							Mage::getModel('translator/path')->load($path_id)->delete();
						}
					}
				}
				
				$data['store_id'] = Mage::helper('translator')->getCurrentStore();
				$string_id = Mage::getModel('translator/translate')->addEntry($data);

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('translator')->__('Item was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('translator')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('translator/translate')
					->deleteEntry($this->getRequest()->getParam('id'));
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('translator')->__('Item was successfully deleted'));
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
				Mage::getModel('translator/translation')
				->load($this->getRequest()->getParam('translation_id'))->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('translator')->__('Translation was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $string_ids = $this->getRequest()->getParam('strings');
        if(!is_array($string_ids)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('translator')->__('Please select item(s)'));
        } else {
            try {
                foreach ($string_ids as $string_id) {
                    Mage::getModel('translator/translate')->deleteEntry($string_id);
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('translator')->__(
                        'Total of %d record(s) were successfully deleted', count($string_ids)
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
        $string_ids = $this->getRequest()->getParam('strings');
        if(!is_array($string_ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('translator')->__('Please select item(s)'));
        } else {
            try {
                foreach ($string_ids as $string_id) {
                    Mage::getSingleton('translator/string')
                        ->load($string_id)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    Mage::helper('translator')->__('Total of %d record(s) were successfully updated', count($string_ids))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    public function massDeleteTransAction() {
    	$string_ids = $this->getRequest()->getParam('strings');
    	$store_id = Mage::helper('translator')->getCurrentStore();
    	if(!is_array($string_ids)) {
    		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('translator')->__('Please select item(s)'));
    	} else {
    		try {
    			foreach ($string_ids as $string_id) {
    				$id = Mage::getModel('translator/translation')->getIdByParams($string_id, $store_id);
    				Mage::getModel('translator/translation')->load($id)->delete();
    			}
    			Mage::getSingleton('adminhtml/session')->addSuccess(
    					Mage::helper('translator')->__(
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
        $fileName   = 'translations.csv';
        $content    = $this->getLayout()->createBlock('translator/adminhtml_strings_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'translations.xml';
        $content    = $this->getLayout()->createBlock('translator/adminhtml_strings_grid')
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
    
    public function syncResourcesAction() {
    	$this->loadLayout();
    	$this->_setActiveMenu('translator/syncResources');
    	$this->_addBreadcrumb(Mage::helper('translator')->__('Sync Resources'), Mage::helper('translator')->__('Sync Resources'));
    	
    	$form = $this->getLayout()->createBlock('translator/adminhtml_sync');
    	$this->_addContent($form);
    	
    	$messages = $this->getLayout()->createBlock('core/text');
    	
    	$status = $this->getRequest()->getParam('status');
    	switch ($status) {
    		case 'importCsvFiles':
    			
    			$sync = Mage::helper('translator/sync');
    			$sync->init($sync::CSV_SCAN_ACTION);
    			Mage::helper('translator/queue')->init($sync::CSV_QUEUE_NAME);
    			Mage::helper('translator/importer')->pushCsvFilesToQueue();
    			
    			$messages->setText('<div id="magestance-messages"></div>');
    			
    			$head = $this->getLayout()->getBlock('head');
    			$head->addJs('magestance/sync.js');
    			break;
    		case 'importThemeCsvs':
    			
    			$sync = Mage::helper('translator/sync');
    			$sync->init($sync::THEME_SCAN_ACTION);
    			Mage::helper('translator/queue')->init($sync::THEME_QUEUE_NAME);
    			Mage::helper('translator/importer')->pushThemeCsvsToQueue();
    			 
    			$messages->setText('<div id="magestance-messages"></div>');
    			 
    			$head = $this->getLayout()->getBlock('head');
    			$head->addJs('magestance/sync.js');
    			break;
    		
    		case 'migrateCoreDb':
    			Mage::getModel('translator/translate')->migrateCoreDb();

    			$messages->setText('<div id="magestance-messages">Migration Complete. Please check the translations manager page for changes.</div>');
    			break;
    	}
    	
    	$this->_addContent($messages);
    	$this->renderLayout();
    }
    
    public function syncAction() {
    	$output = Mage::helper('translator/sync')->iterator();
    	$this->getResponse()->setBody(json_encode($output));
    }

    public function pagescanAction() {
    	$this->loadLayout();
    	$this->_setActiveMenu('translator/pagescan');
    	$this->_addBreadcrumb(Mage::helper('translator')->__('Scan A Page'), Mage::helper('translator')->__('Scan A Page'));
    
    	$form = $this->getLayout()->createBlock('translator/adminhtml_pagescan');
    	$this->_addContent($form);
    
    	$head = $this->getLayout()->getBlock('head');
    	$status = $this->getRequest()->getParam('status');
    	if ($status == "1")
    	{
    		$messages = $this->getLayout()->createBlock('core/text');
    		$messages->setText('<div id="magestance-messages">No Progress Yet.</div>');
    		$this->_addContent($messages);
    		$head->addJs('magestance/sync.js');
    	}
    
    	$this->renderLayout();
    }
    
    public function pageScanCallbackAction() {
    	$data = $this->getRequest()->getPost();
    
    	$sync = Mage::helper('translator/sync');
    	$sync->init($sync::PATH_SCAN_ACTION);
    
    	Mage::helper('translator/queue')->setRegisterData('sync', array(
    			'go_to_url' => true,
    			'path' => $data['path'],
    			'message' => ''
    	));
    
    	$this->_redirect('*/*/pagescan/status/1');
    }
}