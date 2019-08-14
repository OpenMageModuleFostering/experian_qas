<?php
/**
 * Experian Qas
 *
 * @category  Experian
 * @package   Experian_Qas
 * @copyright 2012 Experian
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Experian_Qas_Block_AddressField extends Mage_Core_Block_Template
{
    protected $_suffix = '';
    protected $_street1 = 'street_1';
    protected $_street2 = 'street_2';
    protected $_zip = 'zip';
    protected $_city = 'city';
    protected $_region = 'region_id';
    protected $_country = 'country';
    protected $_formName = 'form-validate';
    
    
    public function getSuffix()
    {
        return $this->_suffix;
    }
    
    public function getStreet1()
    {
        return $this->_street1;
    }
    
    public function getStreet2()
    {
        return $this->_street2;
    }
    
    public function getZip()
    {
        return $this->_zip;
    }
    
    public function getCity()
    {
        return $this->_city;
    }
    
    public function getRegion()
    {
        return $this->_region;
    }
    
    /**
     * return the right name of the input or select
     * @param input type
     * @return string
     */
    public function getRegionInput($type)
    {
        if($type == 'text') {
            return $this->_region;
        } else {
            $name = explode("_", $this->_region);
            return $name[0];
        }
    }
    
    public function getCountry()
    {
        return $this->_country;
    }
    
    public function getFormName()
    {
        return $this->_formName;
    }
}
