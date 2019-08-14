<?php
/**
 * Experian Qas
 *
 * @category  Experian
 * @package   Experian_Qas
 * @copyright 2012 Experian
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Experian_Qas_Block_AddressField_Billing extends Experian_Qas_Block_AddressField
{
    protected $_suffix = 'BILLING';
    protected $_street1 = 'billing:street1';
    protected $_street2 = 'billing:street2';
    protected $_zip = 'billing:postcode';
    protected $_city = 'billing:city';
    protected $_region = 'billing:region_id';
    protected $_country = 'billing:country_id';
    protected $_formName = 'co-billing-form';
}
