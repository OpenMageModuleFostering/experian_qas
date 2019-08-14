<?php
/**
 * Experian Qas Engine type source
 *
 * @category  Experian
 * @package   Experian_Qas
 * @copyright 2012 Experian
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Experian_Qas_Model_System_Config_Source_Engine 
    extends Mage_Eav_Model_Entity_Attribute_Source_Config
{
    public function __construct()
    {
        $this->_configNodePath = 'global/experian_qas/engine/type';
    }
    
    /**
     * Retrieve Search Engine type options
     * 
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = parent::getAllOptions();
            array_unshift($this->_options, array('value' => '', 'label' => Mage::helper('experian_qas')->__('-- Please Select --')));
        }
        return $this->_options;
    }
}
