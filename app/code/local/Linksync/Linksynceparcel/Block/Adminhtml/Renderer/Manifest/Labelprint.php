<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Manifest_Labelprint extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$label =  $row->getData($this->getColumn()->getIndex());
		if($label)
		{
			$manifest =  $row->getData('manifest_number');
			$labelLink = $this->getUrl('linksynceparcel/index/processDownloadPdf/') . '?f_type=manifest&f_key='. $manifest;
			$html = '<a href="'.$labelLink.'" target="_blank" >View</a>';
		}
		else
		{
			$html ='&nbsp;';
		}
		return $html;
	}
}
