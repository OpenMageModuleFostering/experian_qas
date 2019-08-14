<?php
/**
 * Experian Qas Controller
 *
 * @category  Experian
 * @package   Experian_Qas
 * @copyright 2012 Experian
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Experian_Qas_ProcessController extends Mage_Core_Controller_Front_Action
{
    const CACHE_INTUITIVE_TAG      = 'EXPERIAN_INTUITIVE';
    const CACHE_INTUITIVE_LIFETIME = 3600;

    /**
     * Call the Intuitive Gngine
     *
     * @return string
     */
    public function intuitiveEngineAction()
    {
        $helper = Mage::helper('experian_qas');
        $isIntuitiveEnabled = $helper->isIntuitiveEnabled();
        $searchAddress = $this->getRequest()->getParam(Experian_Qas_Helper_Data::QUERY_PARAM, false);
        $country = $this->getRequest()->getParam(Experian_Qas_Helper_Data::QUERY_COUNTRY, false);
        $api = Mage::getSingleton('experian_qas/qascall');

        if (($searchAddress == false) || ($isIntuitiveEnabled == false) || ($country != 'FR')) {
            $message = $helper->__('Intuitive Engine is disabled for all Datasets except France (FRI).');
            $helper->log($message);
            return;
        }

        // Check if we need use FRI or FR3
        $datasetAvailable   = $helper->getCountryAvailable();
        $countryDataset     = Experian_Qas_Helper_Data::INTUITIVE_DATASET_FRANCE;
        if (isset($datasetAvailable[Experian_Qas_Helper_Data::INTUITIVE_DATASET_FRANCE_BIS])) {
            $countryDataset = Experian_Qas_Helper_Data::INTUITIVE_DATASET_FRANCE_BIS;
        }

        $args = array(
                        'Country' => $countryDataset,
                        'Search' => $searchAddress,                            //get all the address info @todo
                        'Engine' => Experian_Qas_Helper_Data::INTUITIVE_ENGINE_CODE,
                        'Layout' => $helper->getAddressLayout()
        );

        $html     = '';

        if ($api->getCanSearch($args) == true) {
            $cacheKey = self::CACHE_INTUITIVE_TAG . '_' . md5(serialize($args));

            if ($helper->isCacheEnabled() &&
                false !== ($result = Mage::app()->getCache()->load($cacheKey))) {
                $html = $result;
            } else {
                $qaPicklist = $api->getDoSearch($args)->QAPicklist;

                if ($qaPicklist->Total > 0) {
                    $picklistEntries = $qaPicklist->PicklistEntry;
                    $html = $this->getLayout()->createBlock('experian_qas/autocomplete')
                    ->setPicklistEntries($picklistEntries)
                    ->toHtml();
                }

                // Cache the result
                if ($helper->isCacheEnabled()) {
                    Mage::app()->getCache()->save(
                        $html,
                        $cacheKey,
                        array(self::CACHE_INTUITIVE_TAG),
                        self::CACHE_INTUITIVE_LIFETIME
                    );
                }
            }
        }

        return $this->getResponse()->setBody($html);
    }

    /**
     * Retrieve the address using the moniker and send it normalized
     *
     * @return string
     */
    public function normalizeAddressAction()
    {
        $mappedAddress = array();
        $moniker = $this->getRequest()->getParam(Experian_Qas_Helper_Data::QUERY_MONIKER, false);
        // find sth better for these below
        $country = substr($moniker, 0, 2);
        $dataset = substr($moniker, 0, 3);

        $api    = Mage::getSingleton('experian_qas/qascall');
        $layout = Mage::helper('experian_qas')->getAddressLayout();

        $normalizedAddress = $api->getNormalizedAddress($moniker, $layout);
        if (isset($normalizedAddress->QAAddress->AddressLine)) {
            $mappedAddress     = $api->getMappedAddress($normalizedAddress->QAAddress->AddressLine, $country);
        }

        return $this->getResponse()
                     ->setBody(Mage::helper('core')->jsonEncode($mappedAddress));
    }

    /**
     * Test the validity of an email
     *
     * @return string
     */
    public function validateEmailAction()
    {
        $content = '{"Certainty":"unknown", "Message":"OK"}';
        $helper  = Mage::helper('experian_qas');

        if ($helper->isEmailValidateEnabled()) {
            $email = $this->getRequest()->getParam('email', false);
            if ($email != false) {
                $content = $helper->isEmailValid($email);
            }
        }

        return $this->getResponse()->setBody($content);
    }

    /**
     * Prepare the query string with address parameters
     *
     * @return string
     */
    protected function _getAddressParams()
    {
        $params  = $this->getRequest()->getParams();
        $address = new Varien_Object($params);

        if ($address->getBilling()) {
            $address = $address->getBilling();
        } elseif ($address->getShipping()) {
            $address = $address->getShipping();
        }

        return Mage::helper('experian_qas')->getQueryString($address);
    }

    /**
     * Retrieve the country code from the request (form)
     *
     * @return string
     */
    protected function _getCountryId()
    {
        $params  = $this->getRequest()->getParams();
        $address = new Varien_Object($params);

        if ($address->getBilling()) {
            $address = new Varien_Object($address->getBilling());
        } elseif ($address->getShipping()) {
            $address = new Varien_Object($address->getShipping());
        }

        return $address->getCountryId();
    }

    /**
     * Make the request to retrieve addresses from the API
     *
     * @return string
     */
    public function addressSearchEngineAction()
    {
        $html    = '';
        $helper  = Mage::helper('experian_qas');
        $api     = Mage::getSingleton('experian_qas/qascall');
        $country = $this->_getCountryId();
        $edit    = $this->getRequest()->getParam('edit');
        $dataset = $helper->getCountryDataset($country);
        $session = Mage::getSingleton('core/session');
        $sessionMissingValidationKey = 'experian_missing_validation_';
        $searchEngine                = $helper->getSearchEngine($dataset);
        if (!empty($searchEngine)) {
            $args = array(
                            'Country' => $dataset,
                            'Search' => $this->_getAddressParams(),
                            'Engine' => $searchEngine,
                            'Layout' => $helper->getAddressLayout()
            );

            if ($api->getCanSearch($args) == true) {
                if ($searchEngine == Experian_Qas_Helper_Data::VERIFICATION_ENGINE_CODE) {
                    $result = $api->getDoSearch($args);
                    $html = $this->getLayout()->createBlock('experian_qas/verification')
                    ->setResult($result)
                    ->setCountryId($country)
                    ->setEdit($edit)
                    ->toHtml();
                    $session->setData($sessionMissingValidationKey.$edit, false);
                } elseif ($searchEngine == Experian_Qas_Helper_Data::SINGLELINE_ENGINE_CODE) {
                    $result = $api->getDoSearch($args);
                    $picklistEntries = array();
                    if (isset($result->QAPicklist) && isset($result->QAPicklist->PicklistEntry)) {
                        $picklistEntries = $api->getDoSearch($args)->QAPicklist->PicklistEntry;
                    }

                    $html = $this->getLayout()->createBlock('experian_qas/interaction')
                    ->setPicklistEntries($picklistEntries)
                    ->toHtml();
                    $session->setData($sessionMissingValidationKey.$edit, false);
                }
            } else {
                // Service not available
                $session->setData($sessionMissingValidationKey.$edit, true);
            }
        }

        return $this->getResponse()->setBody($html);
    }
}
