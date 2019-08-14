<?php
/**
 * Experian Qas
 *
 * @category  Experian
 * @package   Experian_Qas
 * @copyright 2012 Experian
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Experian_Qas_Block_AddressField_Shipping extends Experian_Qas_Block_AddressField
{
    protected $_suffix = 'SHIPPING';
    protected $_street1 = 'shipping:street1';
    protected $_street2 = 'shipping:street2';
    protected $_zip = 'shipping:postcode';
    protected $_city = 'shipping:city';
    protected $_region = 'shipping:region_id';
    protected $_country = 'shipping:country_id';
    protected $_formName = 'co-shipping-form';
}
