<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Manifest_Totalconsignments extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$value =  $row->getData($this->getColumn()->getIndex());
		/*$despatch_date =  $row->getData('despatch_date');
		if(!$despatch_date)
		{
			$manifests = Mage::getModel('linksynceparcel/api')->getManifest();
			$xml = simplexml_load_string($manifests);
			if($xml)
			{
				$manifest_number =  $row->getData('manifest_number');
				foreach($xml->manifest as $manifest)
				{
					$manifestNumber = $manifest->manifestNumber;
					if($manifestNumber == $manifest_number)
					{
						$numberOfArticles = (int)$manifest->numberOfArticles;
						Mage::getSingleton('core/session')->setNumberOfArticles($numberOfArticles);
						$numberOfConsignments = (int)$manifest->numberOfConsignments;
						if($numberOfConsignments > 0)
						{
							$value = $numberOfConsignments;
							Mage::helper('linksynceparcel')->updateManifest($manifestNumber,$numberOfArticles,$numberOfConsignments);
						}
					}
				}
			}
		}*/
		return $value;
	}
}
