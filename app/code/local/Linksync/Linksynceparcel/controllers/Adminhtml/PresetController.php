<?php

class Linksync_Linksynceparcel_Adminhtml_PresetController extends Mage_Adminhtml_Controller_Action 
{
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('linksynceparcel/settings/preset');
	}
	
    public function indexAction() {
        $this->loadLayout();
        $this->_setActiveMenu('linksynceparcel/preset');
        $this->renderLayout();
    }
	
	public function gridAction()
    {
		$this->loadLayout();
        $this->_setActiveMenu('linksynceparcel/preset');
        $this->renderLayout();
    }

     public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('linksynceparcel/preset')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('preset_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('linksynceparcel/preset');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Article Preset'), Mage::helper('adminhtml')->__('Article Preset'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Article Preset'), Mage::helper('adminhtml')->__('Article Preset'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('linksynceparcel/adminhtml_preset_edit'))
                    ->_addLeft($this->getLayout()->createBlock('linksynceparcel/adminhtml_preset_edit_tabs'));


            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('linksynceparcel')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
       	$id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('linksynceparcel/preset')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('preset_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('linksynceparcel/preset');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Article Preset'), Mage::helper('adminhtml')->__('Article Preset'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Article Preset'), Mage::helper('adminhtml')->__('Article Preset'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('linksynceparcel/adminhtml_preset_edit'))
                    ->_addLeft($this->getLayout()->createBlock('linksynceparcel/adminhtml_preset_edit_tabs'));


            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('linksynceparcel')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
     
            $model = Mage::getModel('linksynceparcel/preset');
 
            $collection = Mage::getModel('linksynceparcel/preset')->getCollection();
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
                $model = Mage::getModel('linksynceparcel/preset');
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
         $ids = $this->getRequest()->getParam('preset');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $preset = Mage::getModel('linksynceparcel/preset')->load($id);
                    $preset->delete();
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
        $ids = $this->getRequest()->getParam('preset');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $preset = Mage::getSingleton('linksynceparcel/preset')
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