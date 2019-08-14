<?php
/**
 * Experian Qas Api
 *
 * @category  Experian
 * @package   Experian_Qas
 * @copyright 2012 Experian
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Experian_Qas_Model_Qascall
{
    protected $_helper = NULL;
    protected $_apiUser;
    protected $_apiPassword;
    protected $_wsdlName;
    protected $_apiClient = NULL;
    protected $_datasets = NULL;


    public function __construct()
    {
        $helper = $this->_getHelper();
        $this->_apiUser = $helper->getApiUser();
        $this->_apiPassword = $helper->getApiPassword();
        $this->_wsdlName = $helper->getWsdlName();
        $this->_ondemandUrl = $helper->getOndemandUrl();

        $options = array(
                        'trace'        => 1,
                        'soap_version' => SOAP_1_1,
                        'encoding'     => 'UTF-8',
                        'cache_wsdl'   => WSDL_CACHE_BOTH
        );
        //Getting the base dir of our wsdl located in the etc of experian module
        $baseWsdlFile = Mage::getConfig()->getModuleDir('etc', "Experian_Qas") . DS . $this->_wsdlName;
        try {
            $this->_apiClient = new SoapClient($baseWsdlFile, $options);
            $this->_setAuthHeader();
        } catch (SoapFault $e) {
            $helper->log($e->getMessage());
        }
    }

    /**
     * Set header authentication in Soap Client
     */
    protected function _setAuthHeader()
    {
        $params = array(
                        'Security'=>'',
                        'QAAuthentication' => array(
                        'Username'=>$this->_apiUser,
                        'Password'=>$this->_apiPassword
                        )
        );

        $authheader = new SoapHeader(
                        $this->_ondemandUrl,
                        'QAQueryHeader',
                        $params
        );

        $this->_apiClient->__setSoapHeaders($authheader);
    }

    /**
     * Retrieve Experian Qas Helper
     *
     * @return Experian_Qas_Helper_Data
     */
    protected function _getHelper()
    {
        if (is_null($this->_helper)) {
            $this->_helper = Mage::helper('experian_qas');
        }

        return $this->_helper;
    }

    /**
     * Retrieve the instanciated API SOAP Client
     *
     * @return type
     */
    public function getApiClient()
    {
        return $this->_apiClient;
    }

    /**
     * Retrieve the available datasets
     *
     * @return array
     */
    public function getDataSets($displayError = true)
    {
        if (is_null($this->_datasets)) {
            try {
                $this->_datasets = $this->_apiClient->DoGetData()->DataSet;
            } catch (Exception $e) {
                if ($displayError) {
                    Mage::getSingleton('core/session')->addError($e->getMessage());
                }
                $this->_helper->log($e->getMessage());
                $this->_datasets = array();
            }
        }

        return $this->_datasets;
    }

    /**
     * Check if the address search can be made on a certain dataset
     *
     * @param array $args
     * @return boolean
     */
    public function getCanSearch($args)
    {
        try {
            return $this->_apiClient->DoCanSearch($args)->IsOk;
        } catch (SoapFault $e) {
            $this->_helper->log($e->getMessage());
        }

        return false;
    }

    /**
     * Retrieve the list of addresses returned by the API
     *
     * @param array $args
     * @return stdClass Object QAPicklist list of addresses
     */
    public function getDoSearch($args)
    {
        return $this->_apiClient->DoSearch($args);
    }

    /**
     * Format the address basing on the dataset or the country
     *
     * @param array $addressLines
     * @param string $country
     * @return array
     */
    public function getMappedAddress($addressLines, $country = NULL)
    {
        return $this->_helper->getMappedAddress($addressLines, $country);
    }

    /**
     * Retrieved the normalized address with the API using a moniker and a layout
     *
     * @param string $moniker
     * @param string $layout
     * @return stdClass
     */
    public function getNormalizedAddress($moniker, $layout = NULL)
    {
        $address = NULL;
        $args = array(
                        'Moniker' => $moniker,
                        'Layout' => $layout
        );

        try {
            $address = $this->_apiClient->DoGetAddress($args);
        } catch (SoapFault $e) {
            $this->_helper->log($e->getMessage());
        }

        return $address;
    }

    /**
     * Retrieve all the available address layouts for a certain dataset/country
     *
     * @param string $dataset
     * @return stdClass
     */
    public function getAddressLayouts($dataset)
    {
        $layouts = NULL;
        $args = array(
                        'Country' => $dataset
        );

        try {
            $layouts = $this->_apiClient->DoGetLayouts($args);
        } catch (SoapFault $e) {
            $this->_helper->log($e->getMessage());
        }

        return $layouts;
    }
    /**
     * Check authentification and Retrieve the available datasets to configuration page
     */
    public function getConfigDataSets()
    {

        if (is_null($this->_datasets)) {
            try {
                $this->_datasets = $this->_apiClient->DoGetData()->DataSet;
                Mage::getSingleton('core/session')->addNotice('Allowed datasets :');
                foreach($this->_datasets as $ds)
                {
                    Mage::getSingleton('core/session')->addNotice($ds->ID.': '.$ds->Name);
                }

            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addError($e->getMessage());
            }
        }
    }
}
