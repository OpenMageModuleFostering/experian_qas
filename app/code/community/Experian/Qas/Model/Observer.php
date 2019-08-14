<?php
/**
 * Experian Qas Observer
 *
 * @category  Experian
 * @package   Experian_Qas
 * @copyright 2012 Experian
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Experian_Qas_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * Check authentification and Retrieve the available datasets for the user.
     */
    public function confirmationCredentialsAndDs($observer)
    {
        $login = Mage::helper('experian_qas')->getApiUser();
        $password = Mage::helper('experian_qas')->getApiPassword();
        $api = Mage::getSingleton('experian_qas/qascall');
        $api->getConfigDataSets();
    }

    /**
     * Add missing_verification flag of customer address
     *
     * @param unknown $observer
     *
     * @return void
     */
    public function flagCustomerAddress($observer)
    {
        if (Mage::helper('experian_qas')->isEnabled()) {
            $customerAddress = $observer->getCustomerAddress();
            $session         = Mage::getSingleton('core/session');
            $sessionKey      = 'experian_missing_validation_' . Experian_Qas_Block_Verification::TYPE_ADDRESS;

            $missingVerification = $session->getData($sessionKey);
            $customerAddress->setData('missing_verification', $missingVerification);
            $session->setData($sessionKey, false);
        }
    }

    /**
     * Add missing_verification flag of order address
     *
     * @param Varien_Event $observer
     *
     * @return void
     */
    public function flagOrderAddress($observer)
    {
        if (Mage::helper('experian_qas')->isEnabled()) {
            $session            = Mage::getSingleton('core/session');
            $sessionBillingKey  = 'experian_missing_validation_' . Experian_Qas_Block_Verification::TYPE_BILLING;
            $sessionShippingKey = 'experian_missing_validation_' . Experian_Qas_Block_Verification::TYPE_SHIPPING;

            $event = $observer->getEvent();
            $order = $event->getOrder();

            $missingBillingVerification = $session->getData($sessionBillingKey);
            $billingAddress  = $order->getBillingAddress();
            $billingAddress->setData('missing_verification', $missingBillingVerification);
            $session->setData($sessionBillingKey, false);

            $missingShippingVerification = $session->getData($sessionShippingKey);
            $shippingAddress = $order->getShippingAddress();
            $shippingAddress->setData('missing_verification', $missingShippingVerification);
            $session->setData($sessionShippingKey, false);
        }
    }
}