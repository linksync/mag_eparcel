<?php

class Linksync_Linksynceparcel_Adminhtml_FreeshippingController extends Mage_Adminhtml_Controller_Action 
{
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('linksynceparcel/settings/freeshipping');
	}
	
    public function indexAction() {
        $this->loadLayout();
        $this->_setActiveMenu('linksynceparcel/freeshipping');
        $this->renderLayout();
    }
	
	public function gridAction()
    {
		$this->loadLayout();
        $this->_setActiveMenu('linksynceparcel/freeshipping');
        $this->renderLayout();
    }

     public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('linksynceparcel/freeshipping')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('freeshipping_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('linksynceparcel/freeshipping');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('linksynceparcel/adminhtml_freeshipping_edit'))
                    ->_addLeft($this->getLayout()->createBlock('linksynceparcel/adminhtml_freeshipping_edit_tabs'));


            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('linksynceparcel')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
       	$id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('linksynceparcel/freeshipping')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('freeshipping_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('linksynceparcel/freeshipping');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('linksynceparcel/adminhtml_freeshipping_edit'))
                    ->_addLeft($this->getLayout()->createBlock('linksynceparcel/adminhtml_freeshipping_edit_tabs'));


            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('linksynceparcel')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
     
            $model = Mage::getModel('linksynceparcel/freeshipping');
 
            //$collection = Mage::getModel('linksynceparcel/freeshipping')->getCollection();
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
				
				$from_amount = $data['from_amount'];
                $to_amount = $data['to_amount'];
				$charge_code = $data['charge_code'];
                
				if(empty($to_amount))
					$tempToAmount = 0;
				else
	                $tempToAmount = $to_amount;
                
                if($tempToAmount > 0 && $tempToAmount < $from_amount)
                {
                     Mage::getSingleton('adminhtml/session')->addError(Mage::helper('linksynceparcel')->__('Please add valid rule, To amount should be greater than from amount.'));
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                
                $collection = Mage::getModel('linksynceparcel/freeshipping')->getCollection();
                
                if ($id)
                {
                    $collection->getSelect()->where("((from_amount <= $from_amount and to_amount >= $from_amount) or (from_amount <= '$to_amount' and to_amount >= '$to_amount')) and id != $id and charge_code = '$charge_code'");
                }
                else
                {
                    $collection->getSelect()->where("((from_amount <= $from_amount and to_amount >= $from_amount) or (from_amount <= '$to_amount' and to_amount >= '$to_amount')) and charge_code = '$charge_code'");
                }
                
                if (sizeof($collection) > 0)
                {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('linksynceparcel')->__('Please add valid rule, this rule may falls within other existing rules range'));
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
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
                $model = Mage::getModel('linksynceparcel/freeshipping');
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
         $ids = $this->getRequest()->getParam('freeshipping');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $freeshipping = Mage::getModel('linksynceparcel/freeshipping')->load($id);
                    $freeshipping->delete();
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
        $ids = $this->getRequest()->getParam('freeshipping');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $freeshipping = Mage::getSingleton('linksynceparcel/freeshipping')
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