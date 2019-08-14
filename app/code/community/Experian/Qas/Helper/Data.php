<?php
/**
 * Experian Qas Helper
 *
 * @category  Experian
 * @package   Experian_Qas
 * @copyright 2012 Experian
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Experian_Qas_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLED = 'qas_address_verifier/general/enable';
    const XML_PATH_API_USER = 'qas_address_verifier/general/api_user';
    const XML_PATH_API_PASSWORD = 'qas_address_verifier/general/api_password';
    const XML_PATH_WSDL_NAME = 'qas_address_verifier/general/wsdl_name';
    const XML_PATH_ONDEMAND_URL = 'qas_address_verifier/general/on_demand_url';

    const XML_PATH_INTUTIVE_ENABLED = 'qas_address_verifier/address_search_engine/enable_intuitive_search';
    const XML_PATH_SEARCH_ENGINE = 'qas_address_verifier/address_search_engine/search_engine';
    const XML_PATH_DATASETS = 'qas_address_verifier/address_search_engine/search_datasets';
    const XML_PATH_CACHE_ENABLED = 'qas_address_verifier/address_search_engine/enable_cache';

    const XML_PATH_EMAIL_VALIDATE_ENABLED = 'qas_address_verifier/email_validate/enable_email_validate';
    const XML_PATH_EMAIL_VALIDATE_LICENSE = 'qas_address_verifier/email_validate/license_email_validate';
    const XML_PATH_EMAIL_VALIDATE_URL = 'qas_address_verifier/email_validate/email_validate_url';
    const XML_PATH_EMAIL_ACCEPT_UNKNOWNS = 'qas_address_verifier/email_validate/accept_unknowns_emails';

    const NODE_PATH_ADDRESS_MAPPER = 'global/mapping/address';
    const NODE_PATH_DATASET_MAPPER = 'global/mapping/dataset';
    const NODE_PATH_NOMAP = 'global/mapping/nomap/countries';
    const NODE_PATH_NOMAP_ADDRESS_KEYS = 'global/mapping/nomap/address_keys';
    const INTUITIVE_DATASET_FRANCE = 'FRI';
    const INTUITIVE_DATASET_FRANCE_BIS = 'FR3';

    const INTUITIVE_ENGINE_CODE = 'Intuitive';
    const SINGLELINE_ENGINE_CODE = 'Singleline';
    const VERIFICATION_ENGINE_CODE = 'Verification';

    const ADDRESS_LAYOUT = 'MAGENTO2012';

    const LOG_FILENAME = 'experian_qas.log';

    const QUERY_PARAM = 'query';
    const QUERY_MONIKER = 'moniker';
    const QUERY_COUNTRY = 'country_id';

    const INTERACTION_FORM_NAME = 'interaction-form';

    protected $_datasets = NULL;

    /**
     * Checks if QAS Address & Email Verifier is enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_ENABLED);
    }

    /**
     * Retrive the Api user from configuration
     *
     * @return string
     */
    public function getApiUser()
    {
        return Mage::getStoreConfig(self::XML_PATH_API_USER);
    }

    /**
     * Retrieve Api password from congfiguration
     *
     * @return string
     */
    public function getApiPassword()
    {
        return Mage::getStoreConfig(self::XML_PATH_API_PASSWORD);
    }

    /**
     * Retrieve WSDL Name
     *
     * @return string
     */
    public function getWsdlName()
    {
        return Mage::getStoreConfig(self::XML_PATH_WSDL_NAME);
    }

    /**
     * Retrieve Ondemand website URL
     *
     * @return string
     */
    public function getOndemandUrl()
    {
        return Mage::getStoreConfig(self::XML_PATH_ONDEMAND_URL);
    }

    /**
     * Check if Intuitive Engine is enabled
     *
     * @return boolean
     */
    public function isIntuitiveEnabled()
    {
        return $this->isEnabled() &&
        Mage::getStoreConfig(self::XML_PATH_INTUTIVE_ENABLED);
    }

    public function isCacheEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_CACHE_ENABLED);
    }

    /**
     * Check if Singleline or Verification is enabled
     *
     * @return boolean
     */
    public function isSearchEngineEnabled()
    {
        return $this->isEnabled();
    }

    /**
     * Check if Email Validate is enabled and can be used
     *
     * @return boolean
     */
    public function isEmailValidateEnabled()
    {
        return $this->isEnabled() &&
        Mage::getStoreConfig(self::XML_PATH_EMAIL_VALIDATE_ENABLED) &&
        (boolean)Mage::getStoreConfig(self::XML_PATH_EMAIL_VALIDATE_LICENSE);
    }

    /**
     * Retrieve Email Validate License key
     *
     * @return string
     */
    public function getEmailValidateLicense()
    {
        return Mage::getStoreConfig(self::XML_PATH_EMAIL_VALIDATE_LICENSE);
    }

    /**
     * Retrieve Email Validate URL request
     *
     * @return string
     */
    public function getEmailValidateUrl()
    {
        return Mage::getStoreConfig(self::XML_PATH_EMAIL_VALIDATE_URL);
    }

    public function isAcceptUnknowsEmails()
    {
        return Mage::getStoreConfig(self::XML_PATH_EMAIL_ACCEPT_UNKNOWNS);
    }

    /**
     * This willl probably be removed, with its const
     *
     * @return type
     */
    public function getInteractionFormName()
    {
        return self::INTERACTION_FORM_NAME;
    }

    /**
     * Retrieve the currently used search engine (Singleline or Verification)
     *
     * @param string $dataset $dataset
     *
     * @return string
     */
    public function getSearchEngine($dataset)
    {
        $path = self::XML_PATH_SEARCH_ENGINE . '_' . $dataset;
        return Mage::getStoreConfig($path);
    }

    /**
     * Get the dataset used for the country
     * This information is retrieved from config.xml file
     *
     * @param string $country $country
     *
     * @return string
     */
    public function getCountryDataset($country)
    {
        $dataset = $country;

        if (is_null($this->_datasets)) {
            $this->_datasets = Mage::getConfig()
            ->getNode(self::NODE_PATH_DATASET_MAPPER)
            ->asArray();
        }

        if (isset($this->_datasets[$country])) {
            $dataset = $this->_datasets[$country];
        }

        return $dataset;
    }

    public function getCountryAvailable()
    {
        $login       = $this->getApiUser();
        $password    = $this->getApiPassword();
        $api         = Mage::getSingleton('experian_qas/qascall');
        $apiDatasets = $api->getDataSets(false);
        $datasets    = array();

        foreach ($apiDatasets as $ds) {
            $datasets[$ds->ID] = $ds->Name;
        }

        return $datasets;
    }

    /**
     * Prepare the query string for use in the API call
     *
     * @param array $address $address
     *
     * @return string
     */
    public function getQueryString($address)
    {
        $query = '';

        if (isset($address['street'][0])) {
            $query .= $address['street'][0];
        }

        if (isset($address['street'][1])) {
            $query .= ' ' . $address['street'][1];
        }

        if (isset($address['postcode'])) {
            $query .= ', ' . $address['postcode'];
        }

        // need the region

        if (isset($address['city'])) {
            $query .= ', ' . $address['city'];
        }

        return $query;
    }

    /**
     * Check if a dataset is within the selected available datasets
     *
     * @param string $dataset $dataset
     *
     * @return boolean
     */
    public function isDatasetEnabled($dataset)
    {
        $enabledDatasets = Mage::getStoreConfig(self::XML_PATH_DATASETS);
        return in_array($dataset, explode(',', $enabledDatasets));
    }

    /**
     * Log a message inside experian_qas.log filename
     *
     * @param string $message $messages
     *
     * @return void
     */
    public function log($message)
    {
        Mage::log($message, NULL, self::LOG_FILENAME, true);
    }

    /**
     * Return the layout that will be used to normalize the address
     *
     * @return string
     */
    public function getAddressLayout()
    {
        return self::ADDRESS_LAYOUT;
    }

    /**
     * Map QAS address fields with the module
     * Retrived from config.xml <mapping> tag
     *
     * @return array
     */
    public function getAddressFieldMapper()
    {
        $map = array();
        $node = Mage::getConfig()->getNode(self::NODE_PATH_ADDRESS_MAPPER);
        $mapping = $node->asArray();

        foreach ($mapping as $index => $keyvalue) {
            $map[$keyvalue['key']] = $keyvalue['value'];
        }

        return $map;
    }

    /**
     * Get not mapped address by using an array of countries where we can't match
     * with our internal config.
     *
     * @return array
     */
    public function getNotMappedCountries()
    {
        $map = array();
        $node = Mage::getConfig()->getNode(self::NODE_PATH_NOMAP);
        $mapping = $node->asArray();
        return array_keys($mapping);
    }

    /**
     * Combine address keys with the web server resource to be able to recognize each input .
     *
     * @param array $arrayResult $arrayResult
     *
     * @return array
     */
    public function mapAddressKeys($arrayResult)
    {
        $map = array();
        $node = Mage::getConfig()->getNode(self::NODE_PATH_NOMAP_ADDRESS_KEYS);
        $mapping = $node->asArray();

        foreach ($mapping as $index => $keyvalue) {
            $map[$keyvalue['key']] = $keyvalue['value'];
        }

        $myKeys = array_keys($map);
        foreach ($arrayResult as $key => $arr) {
            $arr->Label = $myKeys[$key];
        }
        return $arrayResult;
    }
    /**
     * Prepare an empty mapper so that we have all the necessary params for
     * the addresses like street_1, street_2, city, zip and country
     *
     * @return array
     */
    protected function _prepareEmptyMapper()
    {
        $address = array();
        $mapper = $this->getAddressFieldMapper();

        foreach ($mapper as $value) {
            $address[$value] = '';
        }

        return $address;
    }

    /**
     * Map address values retrieved from API to Magento fields
     * The country is needed to load regions
     *
     * @param stdClass $addressLines $addressLines
     * @param strind   $country      $country
     *
     * @return array
     */
    public function getMappedAddress($addressLines, $country)
    {
        $mapper = $this->getAddressFieldMapper();
        $address = $this->_prepareEmptyMapper();
        //we whether is there any special country
        if (in_array($country, $this->getNotMappedCountries())) {
            $addressLines = $this->mapAddressKeys($addressLines);
        }

        foreach ($addressLines as $line) {
            if (isset($mapper[$line->Label]) && !empty($line->Line)) {

                if (isset($mapper[$line->Label]) && $mapper[$line->Label] == 'region') {
                    // needs refactoring here
                    $region = Mage::getSingleton('directory/region');
                    $region->loadByName($line->Line, $country);
                    if (!$region->getId()) {
                        $region->loadByCode($line->Line, $country);
                    }
                    $regionId = $region->getId();
                    if (!empty($regionId) && isset($regionId)) {
                        $address[$mapper[$line->Label]] = $region->getId();
                    } else {
                        $address[$mapper[$line->Label]] = $line->Line;
                    }

                } else {
                    if (isset($address[$mapper[$line->Label]])
                                    && !empty($address[$mapper[$line->Label]])) {
                        $address[$mapper[$line->Label]] .= ' ' . $line->Line;
                    } else {
                        $address[$mapper[$line->Label]] = $line->Line;
                    }
                }
            }
        }

        // if street_1 is empty, put the content of street_2 inside
        if (isset($address['street_1']) && isset($address['street_2'])
                && empty($address['street_1']) && !empty($address['street_2'])) {
            $address['street_1'] = $address['street_2'];
            $address['street_2'] = '';
        }

        return $address;
    }

    /**
     * Validate email from new platform
     * https://api.experianmarketingservices.com/sync/queryresult/EmailValidate/1.0/
     *
     * @param string $email email
     *
     * @return void
     */
    public function isEmailValid($email)
    {
        $url        = $this->getEmailValidateUrl();
        $license    = $this->getEmailValidateLicense();
        $arr        = array('email' => $email);
        $postFields = json_encode($arr);

        $headers = array("POST / HTTP/1.1",
            "Auth-Token:{$license}",
        );

        ob_start();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_VERBOSE, '1');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        if (!empty($result)) {
            echo $result;
        } else {
            echo '{"Certainty":"verified", "Message":"Curl Error '.curl_error($ch).'"}';
        }
        curl_close($ch);
        $content = ob_get_clean();

        $this->log($content);

        return $content;
    }
}
