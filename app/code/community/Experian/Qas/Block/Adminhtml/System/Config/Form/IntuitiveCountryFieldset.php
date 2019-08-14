<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Config form fieldset renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Experian_Qas_Block_Adminhtml_System_Config_Form_IntuitiveCountryFieldset
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_experianDatasets = array();

    /**
     * Retrieve countries available
     *
     * @return array
     */
    protected function _getCountryAvailables()
    {
        if (empty($this->_experianDatasets)) {
            $this->_experianDatasets = Mage::helper('experian_qas')->getCountryAvailable();

            // Remove FRI if the client get FR3 activated
            if (isset($this->_experianDatasets[Experian_Qas_Helper_Data::INTUITIVE_DATASET_FRANCE_BIS])) {
                unset($this->_experianDatasets[Experian_Qas_Helper_Data::INTUITIVE_DATASET_FRANCE_BIS]);
            }

            // Remove FRI if the client get FRI activated
            if (isset($this->_experianDatasets[Experian_Qas_Helper_Data::INTUITIVE_DATASET_FRANCE])) {
                unset($this->_experianDatasets[Experian_Qas_Helper_Data::INTUITIVE_DATASET_FRANCE]);
            }
        }

        return $this->_experianDatasets;
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     */
    protected function _addCountryElements(Varien_Data_Form_Element_Abstract $element)
    {
        $countries  = $this->_getCountryAvailables();
        $configData = $this->getConfigData();

        foreach ($countries as $countryId => $countryName) {
            $path       = 'qas_address_verifier/address_search_engine/search_engine_'.$countryId;
            $fieldId    = 'qas_address_verifier_address_search_engine_search_engine_'.$countryId;

            if (isset($configData[$path])) {
                $data = $configData[$path];
                $inherit = false;
            } else {
                $data    = (int)(string)$this->getForm()->getConfigRoot()->descend($path);
                $inherit = true;

                if ($data == 0) {
                    $data = '';
                    $inherit = false;
                }
            }

            $element->addField(
                $fieldId,
                'select',
                array(
                    'name' => 'groups[address_search_engine][fields][search_engine_'.$countryId.'][value]',
                    'label' => $countryName . ' [' . $countryId . ']',
                    'scope' => 'default',
                    'scope_label' => Mage::helper('adminhtml')->__('[GLOBAL]'),
                    'values' => Mage::getModel('experian_qas/system_config_source_engine')->toOptionArray(),
                    'value'  => $data,
                    'inherit' => $inherit,
                    'can_use_default_value' => '0',
                    'can_use_website_value' => '0',
                    'can_use_store_value' => '0',
                ),
                $after=false
            )
            ->setRenderer(Mage::getBlockSingleton('adminhtml/system_config_form_field'));
        }
    }

    /**
     * Delete old country configuration
     */
    protected function _refreshCountryDb()
    {
        $countries  = $this->_getCountryAvailables();

        // Do not delete configuration if 0 country is available (maybe conenction pb)
        if (!empty($countries)) {
            $configs = Mage::getModel('core/config_data')->getCollection()
                ->addFieldToFilter('path', array('like' => 'qas_address_verifier/address_search_engine/search_engine_%'));

            foreach ($configs as $config) {
                $configPath = $config->getData('path');
                $countryId  = substr($configPath, -3);

                if (!array_key_exists($countryId, $countries)) {
                    $config->delete();
                }
            }
        }
    }

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $html = $this->_getHeaderHtml($element);

        $this->_addCountryElements($element);
        $this->_refreshCountryDb();

        foreach ($element->getSortedElements() as $field) {
            $html.= $field->toHtml();
        }

        $html .= $this->_getFooterHtml($element);

        return $html;
    }
}
