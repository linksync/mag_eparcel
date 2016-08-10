<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Consignment_Customdocsprinted extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$customdocs =  $row->getData('customdocs');
		if(!empty($customdocs))
		{
			$valid =  $row->getData('is_customdocs_printed');
			if($valid)
			{
				$imgLink = $this->getSkinUrl("linksynceparcel/images/icon-enabled.png");
				$labelLink = Mage::helper('linksynceparcel')->getConsignmentLabelUrl();
				$image = '<img src="'.$imgLink.'" />';
				$html = '<a class="print_label" lang="int'.$row->getData('consignment_number').'" href="'.$labelLink.$customdocs.'?'.time().'" target="_blank" border="0">'.$image.'</a>';
				
			}
			else if($row->getData('is_label_created'))
			{
				$imgLink = $this->getSkinUrl("linksynceparcel/images/cancel_icon.gif");
				$labelLink = Mage::helper('linksynceparcel')->getConsignmentLabelUrl();
				$image = '<img src="'.$imgLink.'" />';
				$html = '<a class="print_label" lang="int'.$row->getData('consignment_number').'" href="'.$labelLink.$customdocs.'?'.time().'" target="_blank" border="0">'.$image.'</a>';
			}
			else
			{
				$imgLink = $this->getSkinUrl("linksynceparcel/images/cancel_icon.gif");
				$link = $this->getUrl('linksynceparcel/adminhtml_consignment/massGenerateLabels', array('order_consignment' => $row->getData('order_consignment')));
				$image = '<img src="'.$imgLink.'" />';
				$html = '<a href="'.$link.'" border="0">'.$image.'</a>';
			}
		} else {
			$html = '';
		}
		
		return $html;
	}
}
