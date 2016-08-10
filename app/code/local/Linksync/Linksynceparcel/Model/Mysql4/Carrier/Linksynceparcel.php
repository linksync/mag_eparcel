<?php
class Linksync_Linksynceparcel_Model_Mysql4_Carrier_Linksynceparcel extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('linksynceparcel/linksynceparcel', 'pk');
    }

    public function getRate(Mage_Shipping_Model_Rate_Request $request)
    {
        $read = $this->_getReadAdapter();
        $write = $this->_getWriteAdapter();

		$postcode = $request->getDestPostcode();
        $table = $this->getMainTable();

        $insurance = (int)Mage::getStoreConfig('carriers/linksynceparcel/insurance');
        $insuranceCost = (float)Mage::getStoreConfig('carriers/linksynceparcel/default_insurance_value');
		
		$region_id = $request->getDestRegionId();
		if(is_numeric($region_id))
		{
			$region_id = Mage::helper('linksynceparcel')->getRegion($region_id);
		}
		
        for ($j=0;$j<5;$j++)
		{
            $select = $read->select()->from($table);

			switch($j) 
			{
                case 0:
                    $select->where(
                        $read->quoteInto(" (dest_country_id=? ", $request->getDestCountryId()).
                            $read->quoteInto(" AND dest_region_id=? ", $region_id).
                            $read->quoteInto(" AND dest_zip=?) ", $postcode)
                        );
                    break;
                case 1:
                    $select->where(
                       $read->quoteInto("  (dest_country_id=? ", $request->getDestCountryId()).
                            $read->quoteInto(" AND dest_region_id=? AND dest_zip='') ", $region_id)
                       );
                    break;

                case 2:
                    $select->where(
                       $read->quoteInto("  (dest_country_id=? AND (dest_region_id='0' OR dest_region_id='' OR dest_region_id  IS NULL OR dest_region_id='*') AND dest_zip='') ", $request->getDestCountryId())
                    );
                    break;
                case 3:
                    $select->where(
                        $read->quoteInto("  (dest_country_id=? AND (dest_region_id='0' OR dest_region_id='' OR dest_region_id  IS NULL OR dest_region_id='*') ", $request->getDestCountryId()).
                        $read->quoteInto("  AND dest_zip=?) ", $postcode)
                        );
                    break;
                case 4:
                    $select->where(
                            "  (dest_country_id='0' AND (dest_region_id='0' OR dest_region_id='' OR dest_region_id  IS NULL OR dest_region_id='*') AND dest_zip='')"
                );
                    break;
            }

            if (is_array($request->getConditionName())) 
			{
                $i = 0;
                foreach ($request->getConditionName() as $conditionName) 
				{
                    if ($i == 0) 
					{
                        $select->where('condition_name=?', $conditionName);
                    } 
					else 
					{
                        $select->orWhere('condition_name=?', $conditionName);
                    }
                    $select->where('condition_from_value<=?', $request->getData($conditionName));
                    $select->where('condition_to_value>=?', $request->getData($conditionName));

                    $i++;
                }
            } 
			else 
			{
                $select->where('condition_name=?', $request->getConditionName());
                $select->where('condition_from_value<=?', $request->getData($request->getConditionName()));
                $select->where('condition_to_value>=?', $request->getData($request->getConditionName()));
            }
            $select->where('website_id=?', $request->getWebsiteId());

            $select->order('dest_country_id DESC');
            $select->order('dest_region_id DESC');
            $select->order('dest_zip DESC');
            $select->order('condition_from_value DESC');

            $newdata=array();
            $row = $read->fetchAll($select);
            if (!empty($row) && ($j<5))
            {
                foreach ($row as $data) 
				{
		            try 
					{
                        $price = (float)($data['price']);
                        $conditionValue = (float)($request->getData($request->getConditionName()));
                        $price += (float)($data['price_per_kg']) * $conditionValue;

						if($insurance == 1)
						{
                        	$price += $insuranceCost;
						}
						$data['price'] = (string)$price;
						$newdata[]=$data;
                    } 
					catch(Exception $e) 
					{
                        Mage::log($e->getMessage(), null, 'linksync_eparcel.log', true);
                    }
                }
                break;
            }
		}
        return $newdata;
    }

    public function uploadAndImport(Varien_Object $object)
    {
        $csvFile = $_FILES["groups"]["tmp_name"]["linksynceparcel"]["fields"]["import"]["value"];

        if (!empty($csvFile)) 
		{
            $csv = trim(file_get_contents($csvFile));

            $table = Mage::getSingleton('core/resource')->getTableName('linksync_linksynceparcel_tabelrate');

            $websiteId = $object->getScopeId();
            $websiteModel = Mage::app()->getWebsite($websiteId);

            if (isset($_POST['groups']['linksynceparcel']['fields']['condition_name']['inherit'])) 
			{
                $conditionName = (string)Mage::getConfig()->getNode('default/carriers/linksynceparcel/condition_name');
            } 
			else 
			{
                $conditionName = $_POST['groups']['linksynceparcel']['fields']['condition_name']['value'];
            }

            $conditionFullName = Mage::getModel('linksynceparcel/carrier_linksynceparcel')->getCode('condition_name_short', $conditionName);
            
            if (!empty($csv)) 
			{
                $exceptions = array();
                $csvLines = explode("\n", $csv);
                $csvLine = array_shift($csvLines);
                $csvLine = $this->getCsvValues($csvLine);
                if (count($csvLine) < 8) 
				{
                    $exceptions[0] = Mage::helper('shipping')->__('Invalid Table Rates File Format');
                }

                $countryCodes = array();
                $regionCodes = array();
                foreach ($csvLines as $k=>$csvLine) 
				{
                    $csvLine = $this->getCsvValues($csvLine);
                    if (count($csvLine) > 0 && count($csvLine) < 8) 
					{
                        $exceptions[0] = Mage::helper('shipping')->__('Invalid Table Rates File Format');
                    }
					else 
					{
                        $countryCodes[] = $csvLine[0];
                        $regionCodes[] = $csvLine[1];
                    }
                }

                if (empty($exceptions)) 
				{
                    $data = array();
                    $countryCodesToIds = array();
                    $regionCodesToIds = array();
                    $countryCodesIso2 = array();

                    $countryCollection = Mage::getResourceModel('directory/country_collection')->addCountryCodeFilter($countryCodes)->load();
                    foreach ($countryCollection->getItems() as $country)
					{
                        $countryCodesToIds[$country->getData('iso3_code')] = $country->getData('country_id');
                        $countryCodesToIds[$country->getData('iso2_code')] = $country->getData('country_id');
                        $countryCodesIso2[] = $country->getData('iso2_code');
                    }

                    $regionCollection = Mage::getResourceModel('directory/region_collection')
                        ->addRegionCodeFilter($regionCodes)
                        ->addCountryFilter($countryCodesIso2)
                        ->load();

                    foreach ($regionCollection->getItems() as $region) 
					{
                        $regionCodesToIds[$region->getData('code')] = $region->getData('region_id');
                    }

                    foreach ($csvLines as $k=>$csvLine) 
					{
                        $csvLine = $this->getCsvValues($csvLine);

                        if (empty($countryCodesToIds) || !array_key_exists($csvLine[0], $countryCodesToIds)) 
						{
                            $countryId = '0';
                            if ($csvLine[0] != '*' && $csvLine[0] != '') {
                                $exceptions[] = Mage::helper('shipping')->__('Invalid Country "%s" in the Row #%s', $csvLine[0], ($k+1));
                            }
                        } 
						else 
						{
                            $countryId = $countryCodesToIds[$csvLine[0]];
                        }

						if(is_integer($csvLine[1]))
						{
							if (empty($regionCodesToIds) || !array_key_exists($csvLine[1], $regionCodesToIds)) 
							{
								$regionId = '0';
								if ($csvLine[1] != '*' && $csvLine[1] != '') 
								{
									$exceptions[] = Mage::helper('shipping')->__('Invalid Region/State "%s" in the Row #%s', $csvLine[1], ($k+1));
								}
							} 
							else 
							{
								$regionId = $regionCodesToIds[$csvLine[1]];
							}
						}
						else
						{
							$regionId = $csvLine[1];
						}

                        if ($csvLine[2] == '*' || $csvLine[2] == '') 
						{
                            $zip = '';
                        } 
						else 
						{
                            $zip = $csvLine[2];
                        }

                        if (!$this->isPositiveDecimalNumber($csvLine[3]) || $csvLine[3] == '*' || $csvLine[3] == '') 
						{
                            $exceptions[] = Mage::helper('shipping')->__('Invalid %s "%s" in the Row #%s', $conditionFullName, $csvLine[3], ($k+1));
                        } 
						else 
						{
                            $csvLine[3] = (float)$csvLine[3];
                        }

                        if (!$this->isPositiveDecimalNumber($csvLine[4]) || $csvLine[4] == '*' || $csvLine[4] == '') 
						{
                            $exceptions[] = Mage::helper('shipping')->__('Invalid %s "%s" in the Row #%s', $conditionFullName, $csvLine[4], ($k+1));
                        } 
						else 
						{
                            $csvLine[4] = (float)$csvLine[4];
                        }

                        if (!$this->isPositiveDecimalNumber($csvLine[5])) 
						{
                            $exceptions[] = Mage::helper('shipping')->__('Invalid Shipping Price "%s" in the Row #%s', $csvLine[5], ($k+1));
                        } 
						else 
						{
                            $csvLine[5] = (float)$csvLine[5];
                        }

                        if (!$this->isPositiveDecimalNumber($csvLine[6])) 
						{
                            $exceptions[] = Mage::helper('shipping')->__('Invalid Shipping Price per Kg "%s" in the Row #%s', $csvLine[6], ($k+1));
                        } 
						else 
						{
                            $csvLine[6] = (float)$csvLine[6];
                        }
                        
                        if (isset($csvLine[8]) AND !$this->isValidChargeCode($csvLine[8])) 
						{
                            $exceptions[] = Mage::helper('shipping')->__('Invalid Charge Code "%s" in the Row #%s', $csvLine[8], ($k+1));
                        } 
						else 
						{
                            $csvLine[8] = isset($csvLine[8]) ? (string)$csvLine[8] : null;
                        }
                        
                   
                        $data[] = array(
                            'website_id' => $websiteId,
                            'dest_country_id' => $countryId,
                            'dest_region_id' => $regionId,
                            'dest_zip' => $zip,
                            'condition_name' => $conditionName,
                            'condition_from_value' => $csvLine[3],
                            'condition_to_value' => $csvLine[4],
                            'price' => $csvLine[5],
                            'price_per_kg' => $csvLine[6],
                            'delivery_type' => $csvLine[7],
                            'charge_code' => $csvLine[8]
                        );
                        
                        $dataDetails[] = array(
                            'country' => $csvLine[0],
                            'region' => $csvLine[1]
                        );
                    }
                }

                if (empty($exceptions)) 
				{
                    $connection = $this->_getWriteAdapter();

                    $condition = array(
                        $connection->quoteInto('website_id = ?', $websiteId),
                        $connection->quoteInto('condition_name = ?', $conditionName),
                    );
                    $connection->delete($table, $condition);
					
                    foreach($data as $k=>$dataLine) 
					{
                        try 
						{
                            $postcodes = array();
                            foreach(explode(',', $dataLine['dest_zip']) as $postcodeEntry) 
							{
                                $postcodeEntry = explode("-", trim($postcodeEntry));
                                if(count($postcodeEntry) == 1) 
								{
                                    $postcodes[] = $postcodeEntry[0];
                                } 
								else 
								{
                                    $pcode1 = (int)$postcodeEntry[0];
                                    $pcode2 = (int)$postcodeEntry[1];
                                    $postcodes = array_merge($postcodes, range(min($pcode1, $pcode2), max($pcode1, $pcode2)));
                                }
                            }

                            foreach($postcodes as $postcode) 
							{
                                $dataLine['dest_zip'] = str_pad($postcode, 4, "0", STR_PAD_LEFT);
                                $connection->insert($table, $dataLine);
                            }
                        } 
						catch (Exception $e) 
						{
                            Mage::log($e->getMessage(), null, 'linksync_eparcel.log', true);
                             $exceptionas[] = $e->getMessage();
                        }
                    }
                }

                if (!empty($exceptions)) 
				{
                    throw new Exception( "\n" . implode("\n", $exceptions) );
                }
				else
				{
					$extensionPath = Mage::helper('linksynceparcel')->getExtensionPath();
					$etcPath = $extensionPath.DS.'etc';
					$filename = 'linksync_eparcel_tablerate.csv';
					$handle = fopen($etcPath.DS.$filename,'w+');
					fwrite($handle, trim(file_get_contents($csvFile)));
				}
            }
        }
    }

    protected function getCsvValues($string, $separator=",")
    {
        $elements = explode($separator, trim($string));
        for ($i = 0; $i < count($elements); $i++) 
		{
            $nquotes = substr_count($elements[$i], '"');
            if ($nquotes %2 == 1) 
			{
                for ($j = $i+1; $j < count($elements); $j++) {
                    if (substr_count($elements[$j], '"') %2 == 1) 
					{ 
                        array_splice($elements, $i, $j-$i+1, implode($separator, array_slice($elements, $i, $j-$i+1)));
                        break;
                    }
                }
            }
            if ($nquotes > 0) 
			{
                $qstr =& $elements[$i];
                $qstr = substr_replace($qstr, '', strpos($qstr, '"'), 1);
                $qstr = substr_replace($qstr, '', strrpos($qstr, '"'), 1);
                $qstr = str_replace('""', '"', $qstr);
            }
            $elements[$i] = trim($elements[$i]);
        }
        return $elements;
    }

    protected function isPositiveDecimalNumber($n)
    {
        return preg_match ("/^[0-9]+(\.[0-9]*)?$/", $n);
    }
	
	protected function isValidChargeCode($chargeCode)
	{
		$chargeCodes = Mage::helper('linksynceparcel')->getChargeCodes();
		return array_key_exists($chargeCode, $chargeCodes);
	}

}
