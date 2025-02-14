<?php
/**
 * Plugin name: Woo Discount Rules
 * Plugin URI: http://www.flycart.org
 * Description: Simple Discount Rules for WooCommerce.
 * Author: Flycart Technologies LLP
 * Author URI: https://www.flycart.org
 * Version: 1.7.16
 * Slug: woo-discount-rules
 * Text Domain: woo-discount-rules
 * Domain Path: /i18n/languages/
 * Requires at least: 4.6.1
 * WC requires at least: 2.4
 * WC tested up to: 3.6
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Plugin Base Name.
 */
define('WOO_DISCOUNT_PLUGIN_BASENAME', plugin_basename(__FILE__));

include_once(dirname(__FILE__).'/helper/activation-helper.php');

include_once(dirname(__FILE__).'/loader.php');