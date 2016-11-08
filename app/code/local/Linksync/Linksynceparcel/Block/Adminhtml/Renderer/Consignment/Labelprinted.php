<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Consignment_Labelprinted extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$value =  $row->getData('consignment_number');
		if(!$value)
		{
			$html = '';
		}
		else
		{
			$valid =  $row->getData('is_label_printed');
			if($valid)
			{
				$imgLink = $this->getSkinUrl("linksynceparcel/images/icon-enabled.png");
				$labelLink = $this->getUrl('linksynceparcel/index/processDownloadPdf/') . '?f_type=consignment&f_key='. $row->getData('consignment_number');
				$image = '<img src="'.$imgLink.'" />';
				$html = '<a class="print_label" href="'.$labelLink.'" target="_blank" border="0">'.$image.'</a>';
				
			}
			else if($row->getData('is_label_created'))
			{
				$imgLink = $this->getSkinUrl("linksynceparcel/images/cancel_icon.gif");
				$labelLink = $this->getUrl('linksynceparcel/index/processDownloadPdf/') . '?f_type=consignment&f_key='. $row->getData('consignment_number');
				$image = '<img src="'.$imgLink.'" />';
				$html = '<a class="print_label" lang="'.$row->getData('consignment_number').'" href="'.$labelLink.'" target="_blank" border="0">'.$image.'</a>';
			}
			else
			{
				$imgLink = $this->getSkinUrl("linksynceparcel/images/cancel_icon.gif");
				$link = $this->getUrl('linksynceparcel/adminhtml_consignment/massGenerateLabels', array('order_consignment' => $row->getData('order_consignment')));
				$image = '<img src="'.$imgLink.'" />';
				$html = '<a href="'.$link.'" border="0">'.$image.'</a>';
			}
		}
		
		return $html;
	}
}
