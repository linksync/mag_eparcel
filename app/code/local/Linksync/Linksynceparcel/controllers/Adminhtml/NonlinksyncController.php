<?php

class Linksync_Linksynceparcel_Adminhtml_NonlinksyncController extends Mage_Adminhtml_Controller_Action 
{
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('linksynceparcel/settings/nonlinksync');
	}
	
    public function indexAction() {
        $this->loadLayout();
        $this->_setActiveMenu('linksynceparcel/nonlinksync');
        $this->renderLayout();
    }
	
	public function gridAction()
    {
		$this->loadLayout();
        $this->_setActiveMenu('linksynceparcel/nonlinksync');
        $this->renderLayout();
    }

     public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('linksynceparcel/nonlinksync')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('nonlinksync_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('linksynceparcel/nonlinksync');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('linksynceparcel/adminhtml_nonlinksync_edit'))
                    ->_addLeft($this->getLayout()->createBlock('linksynceparcel/adminhtml_nonlinksync_edit_tabs'));


            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('linksynceparcel')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
       	$id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('linksynceparcel/nonlinksync')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('nonlinksync_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('linksynceparcel/nonlinksync');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('linksynceparcel/adminhtml_nonlinksync_edit'))
                    ->_addLeft($this->getLayout()->createBlock('linksynceparcel/adminhtml_nonlinksync_edit_tabs'));


            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('linksynceparcel')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('linksynceparcel/nonlinksync');
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
                
            } 
            $model->setData($data);

            Mage::getSingleton('adminhtml/session')->setFormData($data);
            try {
                if ($id) {
                    $model->setId($id);
                }
				
				$method = $data['method'];

                $collection = Mage::getModel('linksynceparcel/nonlinksync')->getCollection();
                
                if ($id)
                {
                    ;
                }
                else
                {
                    $collection->getSelect()->where("method = '$method'");
					 if (sizeof($collection) > 0)
					{
						Mage::getSingleton('adminhtml/session')->addError(Mage::helper('linksynceparcel')->__('For this shipping method, already charge code assigned'));
						$this->_redirect('*/*/edit', array('id' => $model->getId()));
						return;
					}
                }
               
                $model->save();

                if (!$model->getId()) {
                    Mage::throwException(Mage::helper('linksynceparcel')->__('Error while saving'));
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('linksynceparcel')->__('Successfully saved.'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                // The following line decides if it is a "save" or "save and continue"
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                if ($model && $model->getId()) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            }

            return;
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('linksynceparcel')->__('No data found to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('linksynceparcel/nonlinksync');
                $model->setId($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('linksynceparcel')->__('The item has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to find the item to delete.'));
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
         $ids = $this->getRequest()->getParam('nonlinksync');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $nonlinksync = Mage::getModel('linksynceparcel/nonlinksync')->load($id);
                    $nonlinksync->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were successfully deleted', count($ids)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $ids = $this->getRequest()->getParam('nonlinksync');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $nonlinksync = Mage::getSingleton('linksynceparcel/nonlinksync')
                            ->load($id)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($ids))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}