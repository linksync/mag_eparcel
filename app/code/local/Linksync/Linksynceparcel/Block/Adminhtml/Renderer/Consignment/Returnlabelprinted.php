<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Consignment_Returnlabelprinted extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$print_return_labels =  $row->getData('print_return_labels');
		if($print_return_labels)
		{
			$value =  $row->getData('consignment_number');
			if(!$value)
			{
				$html = '';
			}
			else
			{
				
				$valid =  $row->getData('is_return_label_printed');
				if($valid)
				{
					$imgLink = $this->getSkinUrl("linksynceparcel/images/icon-enabled.png");
					$label =  $value.'.pdf';
					$labelLink = Mage::helper('linksynceparcel')->getConsignmentReturnLabelUrl();
					$image = '<img src="'.$imgLink.'" />';
					$html = '<a href="'.$labelLink.$label.'?'.time().'" target="_blank" border="0">'.$image.'</a>';
					
				}
				else if(Mage::helper('linksynceparcel')->isReturnLabelFileExists($value))
				{
					$imgLink = $this->getSkinUrl("linksynceparcel/images/cancel_icon.gif");
					$label =  $value.'.pdf';
					$labelLink = Mage::helper('linksynceparcel')->getConsignmentReturnLabelUrl();
					$image = '<img src="'.$imgLink.'" />';
					$html = '<a class="print_return_label" lang="'.$row->getData('consignment_number').'" href="'.$labelLink.$label.'?'.time().'" target="_blank" border="0">'.$image.'</a>';
				}
				else
				{
					$imgLink = $this->getSkinUrl("linksynceparcel/images/cancel_icon.gif");
					$link = $this->getUrl('linksynceparcel/adminhtml_consignment/massGenerateReturnLabels', array('order_consignment' => $row->getData('order_consignment')));
					$image = '<img src="'.$imgLink.'" />';
					$html = '<a href="'.$link.'" border="0">'.$image.'</a>';
				}
			}
			return $html;
		}
	}
}
