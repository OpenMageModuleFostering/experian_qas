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
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$this->addAttribute('customer_address', 'missing_verification', array(
    'type' => 'int',
    'input' => 'select',
    'source' => 'eav/entity_attribute_source_boolean',
    'label' => 'Missing Verification',
    'global' => 1,
    'visible' => 0,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 0
));


$eavConfig = Mage::getSingleton('eav/config');
$attribute = $eavConfig->getAttribute('customer_address', 'missing_verification');
$attribute->setData('used_in_forms', array('adminhtml_customer_address'));
$attribute->save();

/**
 * Add missing_verification column in sales_flat_quote_address table
 **/
$installer->getConnection()->addColumn(
    $installer->getTable('sales_flat_quote_address'),
    'missing_verification',
    'int(2) NULL DEFAULT NULL'
);

/**
 * Add missing_verification column in sales_flat_order_address table
 **/
$installer->getConnection()->addColumn(
    $installer->getTable('sales_flat_order_address'),
    'missing_verification',
    'int(2) NULL DEFAULT NULL'
);

$installer->endSetup();
