<?php
/**
 * Experian Qas Block - used for Verification Engine
 *
 * @category  Experian
 * @package   Experian_Qas
 * @copyright 2012 Experian
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Experian_Qas_Block_Verification extends Mage_Core_Block_Abstract
{
    const LEVEL_NONE = 'None';
    const LEVEL_INTERACTION_REQUIRED = 'InteractionRequired';
    const LEVEL_STREET_PARTIAL = 'StreetPartial';
    const LEVEL_MULTIPLE = 'Multiple';
    const LEVEL_VERIFIED = 'Verified';
    const TYPE_ADDRESS = 1;
    const TYPE_SHIPPING = 2;
    const TYPE_BILLING = 3;

    /**
     * Format the addresses response for Intuitive Engine
     *
     * @return array
     */
    protected function _toHtml()
    {
        $addressesHtml = '';
        $helper = $this->helper('experian_qas');
        $result = $this->getResult();

        if ($result && $result->VerifyLevel) {
            $level = $result->VerifyLevel;

            switch ($level) {
                case self::LEVEL_NONE:
                case self::LEVEL_INTERACTION_REQUIRED:
                    $addressesHtml = "
                                    <script>
                                        $$('.interactionWindow .display').each(function(item){
                                            item.hide();
                                        });
                                    </script>
                                    Your address could not be verified. Please make sure it's correct or click on <strong>Keep my address</strong>.";
                    break;

                case self::LEVEL_STREET_PARTIAL:
                case self::LEVEL_MULTIPLE:
                    $picklistEntries = $result->QAPicklist->PicklistEntry;
                    $addressesHtml = $this->getLayout()->createBlock('experian_qas/interaction')
                    ->setPicklistEntries($picklistEntries)
                    ->toHtml() .
                                    "<script>
                                        $$('.interactionWindow .display').each(function(item){
                                            item.show();
                                        });
                                    </script>";
                    break;

                case self::LEVEL_VERIFIED:
                    $params = null;
                    switch ( $this->getEdit())
                    {
                        case self::TYPE_ADDRESS:
                            $params = Mage::getSingleton('core/layout')->createBlock("experian_qas/addressField");
                            break;
                        case self::TYPE_SHIPPING:
                            $params = Mage::getSingleton('core/layout')->createBlock("experian_qas/addressField_shipping");
                            break;
                        case self::TYPE_BILLING:
                            $params =Mage::getSingleton('core/layout')->createBlock("experian_qas/addressField_billing");
                            break;
                        default:
                            break;
                    }
                    $interResult = $helper->getMappedAddress($result->QAAddress->AddressLine, $this->getCountryId());
                    if (ctype_digit($interResult['region'])) {
                        $inputType = 'select';
                    } else {
                        $inputType = 'text';
                    }
                    $addressesHtml =
                                    "<script>
                                        $$('.interactionWindow .display').each(function(item){
                                            item.hide();
                                        });
                                        if(!isNaN(".$interResult['region'].")) {
                                                        var current_select = $('".$params->getRegion()."');
                                                        var current_non = current_select.select('option[value=\"".$interResult['region']."\"]');
                                                        var options = current_select.select(\"option\");
                                                        if (current_non[0]) {
                                                        options.each(function(item)
                                                        {
                                                            if(item.value == current_non[0].value) {
                                                                item.selected = true;
                                                            }
                                                        });
                                                        }
                                                        }
                                        $('".$params->getStreet1()."').value = '".$interResult['street_1']."';
                                        $('".$params->getStreet2()."').value = '".$interResult['street_2']."';
                                        $('".$params->getCity()."').value = '".$interResult['city']."';
                                        $('".$params->getRegionInput($inputType)."').value = '".$interResult['region']."';
                                        $('".$params->getZip()."').value = '".$interResult['zip']."';
                                     </script>
                                     Your address has been verified and it's valid. Please click on <strong>Keep my address</strong>.";
                    break;

                default:
                    $addressesHtml = "
                                    <script>
                                        $$('.interactionWindow .display').each(function(item){
                                            item.hide();
                                        });
                                    </script>
                                    Your address could not be verified. Please make sure it's correct and click on <strong>Keep my address</strong>. Click on <strong>Close</strong> if you want to modify it.";
                    break;
            }
        }

        return $addressesHtml;
    }
}
