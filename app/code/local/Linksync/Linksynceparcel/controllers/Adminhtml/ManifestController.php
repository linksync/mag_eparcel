<?php

class Linksync_Linksynceparcel_Adminhtml_ManifestController extends Mage_Adminhtml_Controller_Action 
{
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('linksynceparcel/manifest');
	}
	
    public function indexAction() 
	{
        $this->loadLayout();
        $this->_setActiveMenu('linksync/linksynceparcel/manifest');
		$this->getLayout()->getBlock('head')->setTitle($this->__('eParcel Manifest View'));
        $this->renderLayout();
    }
	
	public function massGenerateLabelsAction() 
	{
        $ids = $this->getRequest()->getParam('manifest_id');

        if (!is_array($ids)) 
		{
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } 
		else 
		{
            try 
			{
                foreach ($ids as $id) 
				{
					$manifest = Mage::getModel('linksynceparcel/manifest')->load($id);
					$manifestNumber = $manifest->getManifestNumber();

					try 
					{
						$labelContent = Mage::getModel('linksynceparcel/api')->printManifest($manifestNumber);

						if($labelContent)
						{
							$filename = $manifestNumber.'.pdf';
							$filepath = Mage::getBaseDir().DS.'media'.DS.'linksync'.DS.'label'.DS.'manifest'.DS.$filename;
							$handle = fopen($filepath,'wb');
							fwrite($handle, $labelContent);
							fclose($handle);
			
							Mage::helper('linksynceparcel')->updateManifestTable($manifestNumber,'label',$filename);
						}
						else
						{
							$error = Mage::helper('linksynceparcel')->__('Manifest label content is empty');
							Mage::getSingleton('adminhtml/session')->addError($error);
						}
		
					}
					catch (Exception $e) 
					{
						$error = Mage::helper('linksynceparcel')->__('Failed to get manifest label content #%s, Error: %s', $manifestNumber, $e->getMessage());
						Mage::getSingleton('adminhtml/session')->addError($error);
					}
				}
	        } 
			catch (Exception $e) 
			{
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}