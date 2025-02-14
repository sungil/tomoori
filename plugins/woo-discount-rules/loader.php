<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Plugin Directory.
 */
define('WOO_DISCOUNT_DIR', untrailingslashit(plugin_dir_path(__FILE__)));

/**
 * Plugin Directory URI.
 */
define('WOO_DISCOUNT_URI', untrailingslashit(plugin_dir_url(__FILE__)));

if(!function_exists('get_plugin_data')){
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

/**
 * Version of Woo Discount Rules.
 */
$pluginDetails = get_plugin_data(plugin_dir_path(__FILE__).'woo-discount-rules.php');
define('WOO_DISCOUNT_VERSION', $pluginDetails['Version']);

if(!class_exists('FlycartWooDiscountRules')){
    class FlycartWooDiscountRules{

        private static $instance;
        public $discountBase;
        public $pricingRules;
        public $config;

        public static $product_variations = array();

        /**
         * To run the plugin
         * */
        public static function init() {
            if ( self::$instance == null ) {
                self::$instance = new FlycartWooDiscountRules();
            }
            return self::$instance;
        }

        /**
         * FlycartWooDiscountRules constructor
         * */
        public function __construct() {
            $this->hasWPML();
            $this->includeFiles();
            $this->discountBase = new FlycartWooDiscountBase();
            $this->runUpdater();
            $this->pricingRules = new FlycartWooDiscountRulesPricingRules();
            if (is_admin()) {
                $this->loadAdminScripts();
            }
            if(FlycartWooDiscountRulesGeneralHelper::doIHaveToRun()){
                $this->loadSiteScripts();
            }
            $this->loadCommonScripts();
        }

        /**
         * To check for WPML
         * */
        protected function hasWPML(){
            if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
                define('WOO_DISCOUNT_AVAILABLE_WPML', true);
            } else {
                define('WOO_DISCOUNT_AVAILABLE_WPML', false);
            }
        }

        /**
         * To include Files
         * */
        protected function includeFiles(){
            include_once(dirname(__FILE__).'/helper/woo-function.php');
            include_once(dirname(__FILE__).'/includes/pricing-rules.php');
            include_once(dirname(__FILE__).'/helper/general-helper.php');
            include_once(dirname(__FILE__).'/includes/cart-rules.php');
            include_once(dirname(__FILE__).'/includes/discount-base.php');
            include_once(dirname(__FILE__).'/helper/purchase.php');
            include_once(dirname(__FILE__).'/includes/compatibility.php');
            require_once __DIR__ . '/vendor/autoload.php';
        }

        /**
         * Run Plugin updater
         * */
        protected function runUpdater(){
            try{
                require plugin_dir_path( __FILE__ ).'/vendor/yahnis-elsts/plugin-update-checker/plugin-update-checker.php';

                $purchase_helper = new FlycartWooDiscountRulesPurchase();
                $purchase_helper->init();
                $update_url = $purchase_helper->getUpdateURL();
                if(!$purchase_helper->isPro()){
                    $dlid = $this->discountBase->getConfigData('license_key', null);
                    if(empty($dlid)) return false;
                }
                $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
                    $update_url,
                    plugin_dir_path( __FILE__ ).'woo-discount-rules.php',
                    'woo-discount-rules'
                );
                add_action( 'after_plugin_row', array($purchase_helper, 'woodisc_after_plugin_row'),10,3 );

                add_action('wp_ajax_forceValidateLicenseKey', array($purchase_helper, 'forceValidateLicenseKey'));

                add_action( 'admin_notices', array($purchase_helper, 'errorNoticeInAdminPages'));
            } catch (Exception $e){}
        }

        /**
         * Load Admin scripts
         * */
        protected function loadAdminScripts(){
            // Init in Admin Menu
            add_action('admin_menu', array($this->discountBase, 'adminMenu'));
            add_action('wp_ajax_savePriceRule', array($this->discountBase, 'savePriceRule'));
            add_action('wp_ajax_saveCartRule', array($this->discountBase, 'saveCartRule'));
            add_action('wp_ajax_saveConfig', array($this->discountBase, 'saveConfig'));
            add_action('wp_ajax_resetWDRCache', array($this->discountBase, 'resetWDRCache'));
            add_action('wp_ajax_loadProductSelectBox', array($this->discountBase, 'loadProductSelectBox'));
            add_action('wp_ajax_loadCoupons', array($this->discountBase, 'loadCoupons'));

            add_action('wp_ajax_UpdateStatus', array($this->discountBase, 'updateStatus'));
            add_action('wp_ajax_RemoveRule', array($this->discountBase, 'removeRule'));
            add_action('wp_ajax_doBulkAction', array($this->discountBase, 'doBulkAction'));
            add_action('wp_ajax_createDuplicateRule', array($this->discountBase, 'createDuplicateRule'));
            add_action('admin_enqueue_scripts', array($this->discountBase, 'woo_discount_adminPageScript'), 100 );
            $display_you_saved_text = $this->discountBase->getConfigData('display_you_saved_text', 'no');
            if(in_array($display_you_saved_text, array('on_each_line_item', 'both_line_item_and_after_total'))){
                add_action( 'woocommerce_after_order_itemmeta', array( $this->pricingRules, 'addAdditionalContentInAfterOrderItemMeta'), 1000, 3);
            }
            if(in_array($display_you_saved_text, array('after_total', 'both_line_item_and_after_total'))){
                add_action( 'woocommerce_admin_order_totals_after_total', array( $this->pricingRules, 'displayTotalSavingsThroughDiscountInOrder'), 10);
            }
            add_filter( 'plugin_action_links_' . WOO_DISCOUNT_PLUGIN_BASENAME, array('FlycartWooDiscountBase', 'addActionLinksInPluginPage') );
        }

        /**
         * Apply discount rules
         * */
        public function applyDiscountRules(){
            $this->discountBase->handlePriceDiscount();
            $removeTheEvent = apply_filters('woo_discount_rules_remove_event_woocommerce_before_calculate_totals', false);
            if(!$removeTheEvent){
                remove_action('woocommerce_before_calculate_totals', array($this, 'applyDiscountRules'), 1000);
            }
        }

        /**
         * Apply discount rules
         * */
        public function applyCartDiscountRules(){
            remove_action('woocommerce_cart_loaded_from_session', array($this, 'applyCartDiscountRules'), 97);
            $this->discountBase->handleCartDiscount();
        }

        /**
         * Script on product page for loading variant strikeout
         * */
        public function script_on_product_page(){
            $runVariationStrikeoutAjax = apply_filters('woo_discount_rules_run_variation_strikeout_through_ajax', true);
            $script = '<script>';
            $script .= 'if(flycart_woo_discount_rules_strikeout_script_executed == undefined){';
            $script .= 'jQuery( document ).ready( function() {';
            $do_product_page_strikeout = $this->discountBase->getConfigData('show_price_discount_on_product_page', 'show');
            $enable_variable_product_cache = $this->discountBase->getConfigData('enable_variable_product_cache', 0);
            if($do_product_page_strikeout == 'show' && $runVariationStrikeoutAjax) {
                $script .= 'jQuery( ".single_variation_wrap" ).on( "show_variation", function ( event, variation, purchasable ) {';
                $script .= '        var container = jQuery(".single_variation .woocommerce-variation-price");';
                $script .= '        var current_object = jQuery(this);
                                    current_object.trigger("woo_discount_rules_before_variant_strikeout");/*container.hide("slow");*/';
                $script .= '        jQuery.ajax({
                                    url: woo_discount_rules.ajax_url,
                                    dataType: "json",
                                    type: "POST",
                                    data: {action: "loadWooDiscountedPriceForVariant", id: variation.variation_id, price_html: variation.price_html},
                                    beforeSend: function() {
                                    },
                                    complete: function() {
                                    },
                                    success: function (response) {
                                        if(response.status == 1){
                                            jQuery(".single_variation .woocommerce-variation-price").html(response.price_html);
                                        }
                                        current_object.trigger("woo_discount_rules_after_variant_strikeout");
                                        /*container.show("slow");*/
                                    }
                                });';
                $script .= '    });';
            }
            if($enable_variable_product_cache){
                $script .= '    var woo_discount_rules_session_storage_id = "woo_discount_rules_session_storage_id_";
                                var woo_discount_rules_session_storage_time_id = "woo_discount_rules_session_storage_time_id_";
                                const WOO_DISCOUNT_RULES = {
                                    checkSessionStorageExists: function (id) {
                                        var name = woo_discount_rules_session_storage_id+id;
                                        if (sessionStorage.getItem(name) === null) {
                                            return false;
                                        }
                                        return true;
                                    },
                                    setSessionStorage: function (id, value) {
                                        var name = woo_discount_rules_session_storage_id+id;
                                        sessionStorage.setItem(name, value);
                                    },
                                    getSessionStorage: function (id) {
                                        var name = woo_discount_rules_session_storage_id+id;
                                        return sessionStorage.getItem(name);
                                    },
                                    setSessionStorageTime: function (id, value) {
                                        var name = woo_discount_rules_session_storage_time_id+id;
                                        sessionStorage.setItem(name, value);
                                    },
                                    getSessionStorageTime: function (id) {
                                        var name = woo_discount_rules_session_storage_time_id+id;
                                        return sessionStorage.getItem(name);
                                    }
                                }
                            ';
            }

            $script .= '    if(jQuery(".woo_discount_rules_variant_table").length > 0){
                                var p_id = jQuery( ".woo_discount_rules_variant_table" ).attr("data-id");';
            if($enable_variable_product_cache) {
                $script .= '        var already_exists = WOO_DISCOUNT_RULES.checkSessionStorageExists(p_id);';
                $script .= '        var last_storage_time = WOO_DISCOUNT_RULES.getSessionStorageTime(p_id);';
            } else {
                $script .= '        var already_exists = 0;';
                $script .= '        var last_storage_time = "";';
            }
            $script .= '        setTimeout(function(){
                                    jQuery.ajax({
                                        url: woo_discount_rules.ajax_url,
                                        type: "POST",
                                        data: {action: "loadWooDiscountedDiscountTable", id: p_id, loaded: already_exists, time: last_storage_time},
                                        beforeSend: function() {
                                        },
                                        complete: function() {
                                        },
                                        success: function (response) {
                                            responseData = jQuery.parseJSON(response);
                                            if(responseData.cookie == "1" && already_exists){';
            if($enable_variable_product_cache) {
                $script .= '                    jQuery(".woo_discount_rules_variant_table").html(WOO_DISCOUNT_RULES.getSessionStorage(p_id));';
            }
            $script .= '                    } else {
                                                jQuery(".woo_discount_rules_variant_table").html(responseData.html);';
            if($enable_variable_product_cache) {
                $script .= '                    WOO_DISCOUNT_RULES.setSessionStorage(p_id, responseData.html);
                                                WOO_DISCOUNT_RULES.setSessionStorageTime(p_id, responseData.time);';
            }
            $script .= '                    }
                                        }
                                    });
                                }, 1);';
            $script .= '    }';
            $script .= '});';
            $script .= 'var flycart_woo_discount_rules_strikeout_script_executed = 1; }';
            $script .= '</script>';

            echo $script;
        }

        /**
         * Load common scripts
         * */
        protected function loadCommonScripts(){
            add_filter( 'woocommerce_email_styles', array($this, 'add_additional_woocommerce_email_styles'), 100);
            $display_you_saved_text = $this->discountBase->getConfigData('display_you_saved_text', 'no');
            if(in_array($display_you_saved_text, array('on_each_line_item', 'both_line_item_and_after_total'))){
                add_filter( 'woocommerce_order_formatted_line_subtotal', array( $this->pricingRules, 'addAdditionalContentInOrderItemSubTotal'), 1000, 3);
            }
            if(in_array($display_you_saved_text, array('after_total', 'both_line_item_and_after_total'))){
                add_action( 'woocommerce_email_after_order_table', array( $this->pricingRules, 'displayTotalSavingsThroughDiscountInOrder'), 10);
            }
            add_action( 'woo_discount_rules_get_total_savings_through_discount_in_cart', array( $this->pricingRules, 'displayTotalSavingsThroughDiscountInCart'), 10);
            add_action( 'woo_discount_rules_get_total_savings_through_discount_from_order', array( $this->pricingRules, 'displayTotalSavingsThroughDiscountInOrder'), 10);
        }

        /**
         * Add additional css in emails
         * */
        public function add_additional_woocommerce_email_styles($css){
            return $css.'.wdr_you_saved_con {
                        color: green;
                    }';
        }

        /**
         * Load Admin scripts
         * */
        protected function loadSiteScripts(){
            $woocommerce_version = '2.0.0';
            $pluginDetails = get_plugin_data(WP_PLUGIN_DIR.'/woocommerce/woocommerce.php');
            if(isset($pluginDetails['Version'])){
                $woocommerce_version = $pluginDetails['Version'];
            }

            add_action('woocommerce_after_cart_item_quantity_update', array($this->discountBase, 'handleDiscount'), 100);
            if(version_compare($woocommerce_version, '3.0', '>=')){
                add_action('woocommerce_before_calculate_totals', array($this, 'applyDiscountRules'), 1000);
                add_action('woocommerce_cart_loaded_from_session', array($this, 'applyCartDiscountRules'), 97);
                add_action( 'woocommerce_after_cart_item_quantity_update', array($this->pricingRules, 'handleBOGODiscountOnUpdateQuantity'), 10, 4 );
            } else {
                add_action( 'woocommerce_after_cart_item_quantity_update', array($this->pricingRules, 'handleBOGODiscountOnUpdateQuantity'), 10, 3 );
                add_action('woocommerce_cart_loaded_from_session', array($this->discountBase, 'handleDiscount'), 100);
            }

            add_action('woocommerce_after_calculate_totals', array($this->discountBase, 'removeThirdPartyCoupon'), 20);

            add_action('woocommerce_add_to_cart', array($this->pricingRules, 'handleBOGODiscount'), 10, 6);

            $add_free_product_on_coupon_applied = $this->discountBase->getConfigData('add_free_product_on_coupon_applied', 0);
            if($add_free_product_on_coupon_applied){
                add_action('woocommerce_applied_coupon', array($this->pricingRules, 'handleBOGODiscountAfterApplyCoupon'), 10, 1);
            }

            add_action( 'woocommerce_checkout_create_order_line_item', array( $this->pricingRules, 'onCreateWoocommerceOrderLineItem'), 10, 4);
            $display_you_saved_text = $this->discountBase->getConfigData('display_you_saved_text', 'no');
            if(in_array($display_you_saved_text, array('on_each_line_item', 'both_line_item_and_after_total'))){
                add_filter( 'woocommerce_cart_item_subtotal', array( $this->pricingRules, 'addAdditionalContentInCartItemSubTotal'), 1000, 3);
            }
            if(in_array($display_you_saved_text, array('after_total', 'both_line_item_and_after_total'))){
                add_action( 'woocommerce_cart_totals_after_order_total', array( $this->pricingRules, 'displayTotalSavingsThroughDiscountInCart'), 10);
                add_action( 'woocommerce_review_order_after_order_total', array( $this->pricingRules, 'displayTotalSavingsThroughDiscountInCart'), 10);
                add_action( 'woocommerce_order_details_after_order_table', array( $this->pricingRules, 'displayTotalSavingsThroughDiscountInOrder'), 10);
            }

            // Manually Update Line Item Name.
            add_filter('woocommerce_cart_item_name', array($this->discountBase, 'modifyName'));

            // Remove Filter to make the previous one as last filter.
            remove_filter('woocommerce_cart_item_name', 'filter_woocommerce_cart_item_name', 10, 3);

            // Alter the Display Price HTML.
            add_filter('woocommerce_cart_item_price', array($this->pricingRules, 'replaceVisiblePricesCart'), 1000, 3);

            //replace visible price in product page
            add_filter('woocommerce_get_price_html', array($this->pricingRules, 'replaceVisiblePricesOptimized'), 100, 3);
            add_filter('woocommerce_get_price_html', array($this->pricingRules, 'replaceVisiblePricesForSalePriceAdjustment'), 99, 3);

            //replace visible price in product page for variant
            add_filter('woocommerce_available_variation', array($this->pricingRules, 'replaceVisiblePricesForVariant'), 100, 3);

            // Older Version support this hook.
            add_filter('woocommerce_cart_item_price_html', array($this->pricingRules, 'replaceVisiblePricesCart'), 1000, 3);

            //For changing the sale tag text
            add_filter( 'woocommerce_sale_flash', array($this->pricingRules, 'replaceSaleTagText'), 100, 3);

            // Pricing Table of Individual Product.
            $discount_table_placement = $this->discountBase->getConfigData('discount_table_placement', 'before_cart_form');
            if($discount_table_placement == 'before_cart_form'){
                add_filter('woocommerce_before_add_to_cart_form', array($this->pricingRules, 'priceTable'));
                add_filter('woocommerce_before_add_to_cart_form', array($this, 'script_on_product_page'));
            } else {
                add_filter('woocommerce_after_add_to_cart_form', array($this->pricingRules, 'priceTable'));
                add_filter('woocommerce_after_add_to_cart_form', array($this, 'script_on_product_page'));
            }

            // Updating Log After Creating Order
            add_action('woocommerce_thankyou', array($this->discountBase, 'storeLog'));

            add_action( 'woocommerce_after_checkout_form', array($this->discountBase, 'addScriptInCheckoutPage'));

            //To enable on-sale tag
            add_filter('woocommerce_product_is_on_sale', array($this->pricingRules, 'displayProductIsOnSaleTagOptimized'), 10, 2);

            $force_refresh_cart_widget = $this->discountBase->getConfigData('force_refresh_cart_widget', 0);
            if($force_refresh_cart_widget){
                if (isset($_REQUEST['wc-ajax']) && ($_REQUEST['wc-ajax'] == 'add_to_cart' || $_REQUEST['wc-ajax'] == 'remove_from_cart')) {
                    add_action('woocommerce_before_mini_cart', array($this, 'applyRulesBeforeMiniCart'), 10);
                }
            }

            add_action('wp_ajax_loadWooDiscountedPriceForVariant', array($this->pricingRules, 'getWooDiscountedPriceForVariant'));
            add_action('wp_ajax_nopriv_loadWooDiscountedPriceForVariant', array($this->pricingRules, 'getWooDiscountedPriceForVariant'));
            add_action('wp_ajax_loadWooDiscountedDiscountTable', array($this->pricingRules, 'getWooDiscountedPriceTableForVariant'));
            add_action('wp_ajax_nopriv_loadWooDiscountedDiscountTable', array($this->pricingRules, 'getWooDiscountedPriceTableForVariant'));
            add_action( 'wp_enqueue_scripts', array($this, 'includeScriptAndStyles') );

            add_action('woocommerce_before_checkout_form', array($this, 'displayAppliedDiscountMessagesForPriceRules'));
            add_action('woocommerce_before_checkout_form', array($this, 'displayAppliedDiscountMessagesForCartRules'));
            add_action('woocommerce_before_cart', array($this, 'displayAppliedDiscountMessagesForPriceRules'));
            add_action('woocommerce_before_cart', array($this, 'displayAppliedDiscountMessagesForCartRules'));

            add_filter('woo_discount_rule_products_to_exclude', array($this, 'woo_discount_get_variations'), 3, 10);
            add_filter('woo_discount_rule_products_to_include', array($this, 'woo_discount_get_variations'), 3, 10);
        }

        /**
         * Include the variant product as well while choose parent product
         *
         * @param array $excluded_products
         * @return array
         * */
        public function woo_discount_get_variations($excluded_products = array(), $rule, $variants = null) {
            $include_variants_on_select_parent_product = $this->discountBase->getConfigData('include_variants_on_select_parent_product', 0);
            if($include_variants_on_select_parent_product){
                // Load from Rules if we already saved with rules
                if($variants !== null && is_array($variants)){
                    if(!empty($variants)){
                        $excluded_products = array_merge($excluded_products, $variants);
                    }
                    return $excluded_products;
                }
                static $sets;
                if (!is_array($sets)) {
                    $sets = array();
                }

                if(count($excluded_products) < 1) return $excluded_products;
                $string = json_encode($excluded_products);

                if (!isset($sets[$string])) {
                    $all_excluded_products = $excluded_products;
                    foreach ($excluded_products as $exclude_id) {
                        if(isset(self::$product_variations[$exclude_id])){} else {
                            $product = FlycartWoocommerceProduct::wc_get_product($exclude_id);
                            if (is_object($product) && method_exists($product, 'get_type') && $product->get_type() == 'variable') {
                                self::$product_variations[$exclude_id] = $children_ids = FlycartWoocommerceProduct::get_children($product);//$product->get_children();
                                //$all_excluded_products = array_merge($all_excluded_products, $children_ids);
                            }
                        }
                        if(isset(self::$product_variations[$exclude_id])){
                            if(!empty(self::$product_variations[$exclude_id]) && is_array(self::$product_variations[$exclude_id])){
                                $all_excluded_products = array_merge($all_excluded_products, self::$product_variations[$exclude_id]);
                            }
                        }
                    }
                    $sets[$string] = $all_excluded_products;
                }

                return $sets[$string];
            } else {
                return $excluded_products;
            }
        }

        /**
         * To include the styles
         * */
        public function includeScriptAndStyles(){
            wp_register_style('woo_discount_rules_front_end', WOO_DISCOUNT_URI . '/assets/css/woo_discount_rules.css', array(), WOO_DISCOUNT_VERSION);
            wp_enqueue_style('woo_discount_rules_front_end');
            // Enqueued script with localized data.
            wp_register_script( 'woo_discount_rules_site', WOO_DISCOUNT_URI . '/assets/js/woo_discount_rules.js', array('jquery'), WOO_DISCOUNT_VERSION, true );
            wp_localize_script('woo_discount_rules_site', 'woo_discount_rules', array(
                'home_url' => get_home_url(),
                'admin_url' => admin_url(),
                'ajax_url' => admin_url('admin-ajax.php')
            ));
            wp_enqueue_script( 'woo_discount_rules_site');
        }

        /**
         * To load the dynamic data in mini-cart/cart widget while add to cart and remove from cart through widget
         * */
        public function applyRulesBeforeMiniCart(){
            WC()->cart->get_cart_from_session();
            $this->discountBase->handlePriceDiscount();
            WC()->cart->calculate_totals();
        }

        /**
         * To display applied discount messages for cart rules
         * */
        public function displayAppliedDiscountMessagesForCartRules(){
            $message_on_apply_cart_discount = $this->discountBase->getConfigData('message_on_apply_cart_discount', 'no');
            if($message_on_apply_cart_discount == "yes"){
                if(!empty($this->cart_rules)){
                    if(!empty($this->cart_rules->matched_discounts)){
                        $matched_discounts = $this->cart_rules->matched_discounts;
                        if(!empty($matched_discounts['name'])){
                            foreach ($matched_discounts['name'] as $key => $matched_discount_name){
                                $rule_sets = $this->cart_rules->rule_sets;
                                $rule_title = $matched_discount_name;
                                $rule_description = '';
                                if(isset($rule_sets[$key])){
                                    if(!empty($rule_sets[$key]['descr'])) $rule_description = $rule_sets[$key]['descr'];
                                }
                                $message_on_apply_cart_discount_text = $this->discountBase->getConfigData('message_on_apply_cart_discount_text', 'Discount <strong>"{{title}}"</strong> has been applied to your cart.');
                                $message_on_apply_cart_discount_text = __($message_on_apply_cart_discount_text, 'woo-discount-rules');
                                $message_on_apply_cart_discount_text = str_replace('{{title}}', $rule_title, $message_on_apply_cart_discount_text);
                                $message_on_apply_cart_discount_text = str_replace('{{description}}', $rule_description, $message_on_apply_cart_discount_text);
                                wc_print_notice( apply_filters('woo_discount_rules_message_on_apply_cart_rules', $message_on_apply_cart_discount_text, $rule_sets), 'success' );
                            }
                        }
                    }
                }
            }
        }

        /**
         * To display applied discount messages for cart rules
         * */
        public function displayAppliedDiscountMessagesForPriceRules(){
            $message_on_apply_price_discount = $this->discountBase->getConfigData('message_on_apply_price_discount', 'no');
            if($message_on_apply_price_discount == "yes"){
                $applied_discount_rules = FlycartWooDiscountRulesPricingRules::$applied_discount_rules;
                if(!empty($applied_discount_rules)){
                    foreach ($applied_discount_rules as $key => $matched_discount_rules){
                        $rule_title = $matched_discount_rules['name'];
                        $rule_description = $matched_discount_rules['descr'];
                        $message_on_apply_cart_discount_text = $this->discountBase->getConfigData('message_on_apply_price_discount_text', 'Discount <strong>"{{title}}"</strong> has been applied to your cart.');
                        $message_on_apply_cart_discount_text = __($message_on_apply_cart_discount_text, 'woo-discount-rules');
                        $message_on_apply_cart_discount_text = str_replace('{{title}}', $rule_title, $message_on_apply_cart_discount_text);
                        $message_on_apply_cart_discount_text = str_replace('{{description}}', $rule_description, $message_on_apply_cart_discount_text);
                        wc_print_notice( apply_filters('woo_discount_rules_message_on_apply_price_rules', $message_on_apply_cart_discount_text, $matched_discount_rules), 'success' );
                    }
                }
            }
        }
    }
}

add_filter('woocommerce_screen_ids', function($screen_ids){
    $screen_ids[] = 'woocommerce_page_woo_discount_rules';
    return $screen_ids;
});

/**
 * init Woo Discount Rules
 */
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
    global $flycart_woo_discount_rules;
    $flycart_woo_discount_rules = FlycartWooDiscountRules::init();
    $purchase_helper = new FlycartWooDiscountRulesPurchase();
    if($purchase_helper->isPro()){
        include_once(dirname(__FILE__).'/includes/advanced/free_shipping_method.php');
        include_once(dirname(__FILE__).'/includes/advanced/pricing-productdependent.php');
        include_once(dirname(__FILE__).'/includes/advanced/cart-totals.php');
        include_once(dirname(__FILE__).'/includes/advanced/advanced-helper.php');
    }
}