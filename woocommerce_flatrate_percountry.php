<?php
/**
 * Plugin Name: Flat Rate per State/Country/Region for WooCommerce
 * Plugin URI: http://www.webdados.pt/produtos-e-servicos/internet/desenvolvimento-wordpress/flat-rate-per-countryregion-woocommerce-wordpress/
 * Description: This plugin allows you to set a flat delivery rate per States, Countries or World Regions (and a fallback "Rest of the World" rate) on WooCommerce.
 * Version: 2.3.1
 * Author: Webdados
 * Author URI: http://www.webdados.pt
 * Text Domain: flat-rate-per-countryregion-for-woocommerce
 * Domain Path: /lang
**/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Check if WooCommerce is active
 **/
// Get active network plugins - "Stolen" from Novalnet Payment Gateway
function frpc_active_nw_plugins() {
	if (!is_multisite())
		return false;
	$frpc_activePlugins = (get_site_option('active_sitewide_plugins')) ? array_keys(get_site_option('active_sitewide_plugins')) : array();
	return $frpc_activePlugins;
}
if (in_array('woocommerce/woocommerce.php', (array) get_option('active_plugins')) || in_array('woocommerce/woocommerce.php', (array) frpc_active_nw_plugins())) {
	
	
	function woocommerce_flatrate_percountry_init() {
		
		if ( ! class_exists( 'WC_Flat_Rate_Per_Country_Region' ) ) {
		class WC_Flat_Rate_Per_Country_Region extends WC_Shipping_Method {
			/**
			 * Constructor for your shipping class
			 *
			 * @access public
			 * @return void
			 */
			public function __construct() {
				$this->id					= 'woocommerce_flatrate_percountry';
				load_plugin_textdomain('flat-rate-per-countryregion-for-woocommerce', false, dirname(plugin_basename(__FILE__)) . '/lang/');
				$this->method_title			= __('Flat Rate per State/Country/Region', 'flat-rate-per-countryregion-for-woocommerce');
				$this->method_description	= __('Allows you to set a flat delivery rate per country and/or world region.<br/><br/>If you set a rate for the client\'s country it will be used. Otherwise, if you set a rate for client\'s region it will be used.<br/>If none of the rates are set, the "Rest of the World" rate will be used.', 'flat-rate-per-countryregion-for-woocommerce').'<br/><br/>'.__('You can also choose either to apply the shipping fee for the whole order or multiply it per each item.', 'flat-rate-per-countryregion-for-woocommerce');
				$this->shipping_classes=array();
				if ( WC()->shipping->get_shipping_classes() ) {
					foreach ( WC()->shipping->get_shipping_classes() as $shipping_class ) {
						$this->shipping_classes[$shipping_class->slug]=$shipping_class->name;
					}
				}
				$this->init();
				$this->init_form_fields_per_region();
				$this->init_form_fields_per_country();
				//Fix 1.4.2 - Change "per_country_1_country" to "per_country_1_c" 
				$count=$this->settings['per_region_count'];
				for($counter = 1; $count >= $counter; $counter++) {
					if (isset($this->settings['per_country_'.$counter.'_country'])) {
						$this->settings['per_country_'.$counter.'_c']=$this->settings['per_country_'.$counter.'_country'];
						unset($this->settings['per_country_'.$counter.'_country']);
					}
				}
				$this->init_form_fields_per_state();
			}

			/* Init the settings */
			function init() {
				//Let's sort arrays the right way
				setlocale(LC_COLLATE, get_locale());
				//Regions - Source: http://www.geohive.com/earth/gen_codes.aspx
				$this->regions = array(
					//Africa
					'AF_EA' => array(
						'name' => __('Africa - Eastern Africa', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('BI', 'KM' ,'DJ', 'ER', 'ET', 'KE', 'MG', 'MW', 'MU', 'YT', 'MZ', 'RE', 'RW', 'SC', 'SO', 'TZ', 'UG', 'ZM', 'ZW'),
					),
					'AF_MA' => array(
						'name' => __('Africa - Middle Africa', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('AO', 'CM', 'CF', 'TD', 'CG', 'CD', 'GQ', 'GA', 'ST'),
					),
					'AF_NA' => array(
						'name' => __('Africa - Northern Africa', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('DZ', 'EG', 'LY', 'MA', 'SS', 'SD', 'TN', 'EH'),
					),
					'AF_SA' => array(
						'name' => __('Africa - Southern Africa', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('BW', 'LS', 'NA', 'ZA', 'SZ'),
					),
					'AF_WA' => array(
						'name' => __('Africa - Western Africa', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('BJ', 'BF', 'CV', 'CI', 'GM', 'GH', 'GN', 'GW', 'LR', 'ML', 'MR', 'NE', 'NG', 'SH', 'SN', 'SL', 'TG'),
					),
					//Americas
					'AM_LAC' => array(
						'name' => __('Americas - Latin America and the Caribbean', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('AI', 'AG', 'AW', 'BS', 'BB', 'BQ', 'VG', 'KY', 'CU', 'CW', 'DM', 'DO', 'GD', 'GP', 'HT', 'JM', 'MQ', 'MS', 'PR', 'BL', 'KN', 'LC', 'MF', 'VC', 'SX', 'TT', 'TC', 'VI'),
					),
					'AM_CA' => array(
						'name' => __('Americas - Central America', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('BZ', 'CR', 'SV', 'GT', 'HN', 'MX', 'NI', 'PA'),
					),
					'AM_SA' => array(
						'name' => __('Americas - South America', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('AR', 'BO', 'BR', 'CL', 'CO', 'EC', 'FK', 'GF', 'GY', 'PY', 'PE', 'SR', 'UY', 'VE'),
					),
					'AM_NA' => array(
						'name' => __('Americas - Northern America', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('BM', 'CA', 'GL', 'PM', 'US'),
					),
					//Asia
					'AS_CA' => array(
						'name' => __('Asia - Central Asia', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('KZ', 'KG', 'TJ', 'TM', 'UZ'),
					),
					'AS_EA' => array(
						'name' => __('Asia - Eastern Asia', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('CN', 'HK', 'MO', 'JP', 'KP', 'KR', 'MN', 'TW'),
					),
					'AS_SA' => array(
						'name' => __('Asia - Southern Asia', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('AF', 'BD', 'BT', 'IN', 'IR', 'MV', 'NP', 'PK', 'LK'),
					),
					'AS_SEA' => array(
						'name' => __('Asia - South-Eastern Asia', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('BN', 'KH', 'ID', 'LA', 'MY', 'MM', 'PH', 'SG', 'TH', 'TL', 'VN'),
					),
					'AS_WA' => array(
						'name' => __('Asia - Western Asia', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('AM', 'AZ', 'BH', 'CY', 'GE', 'IQ', 'IL', 'JO', 'KW', 'LB', 'PS', 'OM', 'QA', 'SA', 'SY', 'TR', 'AE', 'YE'),
					),
					//Europe
					'EU_EE' => array(
						'name' => __('Europe - Eastern Europe', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('BY', 'BG', 'CZ', 'HU', 'MD', 'PL', 'RO', 'RU', 'SK', 'UA'),
					),
					'EU_NE' => array(
						'name' => __('Europe - Northern Europe', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('AX', 'DK', 'EE', 'FO', 'FI', 'GG', 'IS', 'IE', 'JE', 'LV', 'LT', 'IM', 'NO', 'SJ', 'SE', 'GB'),
					),
					'EU_SE' => array(
						'name' => __('Europe - Southern Europe', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('AL', 'AD', 'BA', 'HR', 'GI', 'GR', 'VA', 'IT', 'MK', 'MT', 'ME', 'PT', 'SM', 'RS', 'SI', 'ES'),
					),
					'EU_WE' => array(
						'name' => __('Europe - Western Europe', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('AT', 'BE', 'FR', 'DE', 'LI', 'LU', 'MC', 'NL', 'CH'),
					),
					//Special EU Group
					'EU_EU' => array(
						'name' => __('European Union', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('BE', 'BG', 'CZ', 'DK', 'DE', 'EE', 'IE', 'GR', 'ES', 'FR', 'HR', 'IT', 'CY', 'LV', 'LT', 'LU', 'HU', 'MT', 'NL', 'AT', 'PL', 'PT', 'RO', 'SI', 'SK', 'FI', 'SE', 'GB'),
					),
					//Oceania
					'OC_ANZ' => array(
						'name' => __('Oceania - Australia and New Zealand', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('AU', 'CX', 'CC', 'NZ', 'NF'),
					),
					'OC_ML' => array(
						'name' => __('Oceania - Melanesia', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('FJ', 'NC', 'PG', 'SB', 'VU'),
					),
					'OC_MN' => array(
						'name' => __('Oceania - Micronesia', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('GU', 'KI', 'MH', 'FM', 'NR', 'MP', 'PW'),
					),
					'OC_PL' => array(
						'name' => __('Oceania - Polynesia', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('AS', 'CK', 'PF', 'NU', 'PN', 'WS', 'TK', 'TO', 'TV', 'WF'),
					),
					/*
					'UNCLASSIFIED' => array(
						'name' => __('Unclassified', 'flat-rate-per-countryregion-for-woocommerce'),
						'countries' => array('AQ', 'BV', 'IO', 'TF', 'HM', 'GS', 'UM'),
					),
					*/
				);
				$this->regionslist=array();
				foreach($this->regions as $key => $temp) {
					$this->regionslist[$key]=$temp['name'];
				}
				asort($this->regionslist, SORT_LOCALE_STRING);

				// Load the settings API
				$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
				$this->init_settings(); // This is part of the settings API. Loads settings you previously init.

				$this->title				= $this->settings['title'];
				$this->enabled				= $this->settings['enabled'];

				if (isset($this->settings['remove_free'])) {
					if ($this->settings['remove_free']=='yes') {
						add_filter('woocommerce_cart_shipping_method_full_label', array($this, 'remove_free_price_text'), 10, 2);
					}
				}

				// Save settings in admin if you have any defined
				add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

			}

			/* The form */
			function init_form_fields() {
				$fields = array(
					'global_def' => array(
						'title'		 => __('Global settings', 'flat-rate-per-countryregion-for-woocommerce'),
						'type'		  => 'title'
					),
					'enabled' => array(
						'title'		=> __('Enable/Disable', 'woocommerce'),
						'type'			=> 'checkbox',
						'label'		=> __('Enable this shipping method', 'woocommerce'),
						'default'		=> 'no',
						'desc_tip'		=> true
					),
					'title' => array(
						'title'		=> __('Method Title', 'woocommerce'),
						'type'			=> 'text',
						'description'	=> __('This controls the title which the user sees during checkout.', 'woocommerce').' '.__('(If chosen below)', 'flat-rate-per-countryregion-for-woocommerce'),
						'default'		=> __('Flat Rate per State/Country/Region', 'flat-rate-per-countryregion-for-woocommerce'),
						'desc_tip'		=> true
					),
					'show_region_country' => array(
						'title'		=> __('Label to show to the user', 'flat-rate-per-countryregion-for-woocommerce'),
						'type'			=> 'select',
						'description'	=> __('Choose either to show the region name, the country name, the method title (or a combination of these) on the checkout screen.', 'flat-rate-per-countryregion-for-woocommerce'),
						'default'		=> 'region',
						'options'		=> array(
								'country'		=> __('Country', 'flat-rate-per-countryregion-for-woocommerce'),
								'region'		=> __('State or Country or Region name or "Rest of the World"', 'flat-rate-per-countryregion-for-woocommerce'),
								'title'		=> __('Method Title', 'woocommerce').' '.__('(as defined above)', 'flat-rate-per-countryregion-for-woocommerce'),
								'title_country'	=> __('Method Title', 'woocommerce').' + '.__('Country', 'flat-rate-per-countryregion-for-woocommerce'),
								'title_region'	=> __('Method Title', 'woocommerce').' + '.__('State or Country or Region name or "Rest of the World"', 'flat-rate-per-countryregion-for-woocommerce'),
								'rule_name'	=> __('Rule name', 'flat-rate-per-countryregion-for-woocommerce'),
							),
						'desc_tip'		=> true
					),
					'tax_status' => array(
						'title'		=> __('Tax Status', 'woocommerce'),
						'type'			=> 'select',
						'description'	=> '',
						'default'		=> 'taxable',
						'options'		=> array(
								'taxable'	=> __('Taxable', 'woocommerce'),
								'none'		=> __('None', 'woocommerce'),
							),
						'desc_tip'		=> true
					),
					'remove_free' => array(
						'title'		=> __('Remove "(Free)"', 'flat-rate-per-countryregion-for-woocommerce'),
						'type'			=> 'checkbox',
						'description'	=> __('If the final rate is zero, remove the "(Free)" text from the checkout screen. Useful if you need to get a quote for the shipping cost from the carrier.', 'flat-rate-per-countryregion-for-woocommerce'),
						'label'		=> __('Remove "(Free)" from checkout if delivery rate equals zero', 'flat-rate-per-countryregion-for-woocommerce'),
						'default'		=> 'no',
						'desc_tip'		=> true
					),
					'world_title' => array(
						'title'		 => __('"Rest of the World" Rates', 'flat-rate-per-countryregion-for-woocommerce'),
						'type'		 => 'title'
					),
					'world_rulename' => array(
						'title'		=> '<span class="rules_items">'.__( 'Rule name', 'flat-rate-per-countryregion-for-woocommerce').'</span>',
						'type'			=> 'text',
						'description'	=> __('The name for this rule, if you choose to show it to the client.', 'flat-rate-per-countryregion-for-woocommerce'),
						'default'		=> '',
						'placeholder'	=> '',
						'desc_tip'		=> true
					),
					'tax_type' => array(
						'title'		=> '<span class="rules_items">'.__('Apply rate', 'flat-rate-per-countryregion-for-woocommerce').'</span>',
						'type'			=> 'select',
						'description'	=> __('Choose either to apply the shipping fee for the whole order or multiply it per each item.', 'flat-rate-per-countryregion-for-woocommerce'),
						'default'		=> 'per_order',
						'options'		=> array(
								'per_order'	=> __('Per order', 'flat-rate-per-countryregion-for-woocommerce'),
								'per_item'		=> __('Per item', 'flat-rate-per-countryregion-for-woocommerce'),
							),
						'desc_tip'		=> true
					),
					'fee_world' => array(
						'title'		=> '<span class="rules_items">'.__('Rate', 'flat-rate-per-countryregion-for-woocommerce').' ('.get_woocommerce_currency().')</span>',
						'type'			=> 'price',
						'description'	=> __('The shipping fee for all the Countries/Regions not specified bellow.', 'flat-rate-per-countryregion-for-woocommerce'),
						'default'		=> '',
						'placeholder'	=> '0',
						'desc_tip'		=> true
					),
					'world_free_above' => array(
						'title'		=> '<span class="rules_items">'.__('Free for orders above', 'flat-rate-per-countryregion-for-woocommerce').' ('.get_woocommerce_currency().')</span>',
						'type'			=> 'price',
						'description'	=> __('The shipping fee will be free if the order total reaches this value. Empty or zero for no free shipping.', 'flat-rate-per-countryregion-for-woocommerce'),
						'default'		=> '',
						'placeholder'	=> '',
						'desc_tip'		=> true
					),
					'world_free_class' => array(
						'title'		=> '<span class="rules_items">'.__('Free for shipping classes', 'flat-rate-per-countryregion-for-woocommerce').'</span>',
						'type'		=> 'multiselect',
						'description'	=> __('The shipping fee will be free if at least one item belongs to the selected shipping classes.', 'flat-rate-per-countryregion-for-woocommerce'),
						'class'		=> 'chosen_select',
						'css'		=> 'width: 450px;',
						'default'	=> '',
						'options'	=> $this->shipping_classes,
						'desc_tip'		=> true
					)
				);
				$this->form_fields=$fields;
			}

			/* Per Region form fields */
			function init_form_fields_per_region() {
				//global $woocommerce;
				$this->form_fields['per_region']=array(
					'title'		 => __('Per Region Rates', 'flat-rate-per-countryregion-for-woocommerce'),
					'type'		 => 'title'
				);
				//global $woocommerce;
				$this->form_fields['per_region_count']=array(
					'title'		=> __('Number of Region rules', 'flat-rate-per-countryregion-for-woocommerce'),
					'type'			=> 'number',
					'description'	=> __('How many diferent "per region" rates do you want to set?', 'flat-rate-per-countryregion-for-woocommerce').' '.__('Please save the options after changing this value.', 'flat-rate-per-countryregion-for-woocommerce'),
					'default'		=> 0,
					'desc_tip'		=> true
				);
				$count=intval($this->settings['per_region_count']);
				for($counter = 1; $count >= $counter; $counter++) {
					$this->form_fields['per_region_'.$counter.'_sep']=array(
						'title'		=> sprintf(__( 'Region rule #%s', 'flat-rate-per-countryregion-for-woocommerce'), $counter),
						'class'		=> 'rules_sep',
						'type'		 => 'rules_sep'
					);
					$this->form_fields['per_region_'.$counter.'_region']=array(
						'title'		=> '<span class="rules_items">'.__( 'Region', 'flat-rate-per-countryregion-for-woocommerce').'</span>',
						'type'		=> 'multiselect',
						'description'	=> __('Choose one or more regions for this rule.', 'flat-rate-per-countryregion-for-woocommerce'),
						'class'		=> 'chosen_select',
						'css'		=> 'width: 450px;',
						'default'	=> '',
						'options'	=> $this->regionslist,
						'desc_tip'		=> true
					);
					$this->form_fields['per_region_'.$counter.'_txt']=array(
						'title'		=> '<span class="rules_items">'.__( 'Rule name', 'flat-rate-per-countryregion-for-woocommerce').'</span>',
						'type'			=> 'text',
						'description'	=> __('The name for this rule, if you choose to show it to the client.', 'flat-rate-per-countryregion-for-woocommerce'),
						'default'		=> '',
						'placeholder'	=> '',
						'desc_tip'		=> true
					);
					$this->form_fields['per_region_'.$counter.'_t']= array(
						'title'		=> '<span class="rules_items">'.__('Apply rate', 'flat-rate-per-countryregion-for-woocommerce').'</span>',
						'type'			=> 'select',
						'description'	=> __('Choose either to apply the shipping fee for the whole order or multiply it per each item.', 'flat-rate-per-countryregion-for-woocommerce'),
						'default'		=> 'per_order',
						'options'		=> array(
								'per_order'	=> __('Per order', 'flat-rate-per-countryregion-for-woocommerce'),
								'per_item'		=> __('Per item', 'flat-rate-per-countryregion-for-woocommerce'),
							),
						'desc_tip'		=> true
					);
					$this->form_fields['per_region_'.$counter.'_fee']=array(
						'title'		=> '<span class="rules_items">'.__( 'Rate', 'flat-rate-per-countryregion-for-woocommerce').' ('.get_woocommerce_currency().')</span>',
						'type'			=> 'price',
						'description'	=> __('The shipping fee for the regions specified above.', 'flat-rate-per-countryregion-for-woocommerce'),
						'default'		=> '',
						'placeholder'	=> '0',
						'desc_tip'		=> true
					);
					$this->form_fields['per_region_'.$counter.'_fr']=array(
						'title'		=> '<span class="rules_items">'.__( 'Free for orders above', 'flat-rate-per-countryregion-for-woocommerce').' ('.get_woocommerce_currency().')</span>',
						'type'			=> 'price',
						'description'	=> __('The shipping fee will be free if the order total reaches this value. Empty or zero for no free shipping.', 'flat-rate-per-countryregion-for-woocommerce'),
						'default'		=> '',
						'placeholder'	=> '',
						'desc_tip'		=> true
					);
					$this->form_fields['per_region_'.$counter.'_fr_class']=array(
						'title'		=> '<span class="rules_items">'.__('Free for shipping classes', 'flat-rate-per-countryregion-for-woocommerce').'</span>',
						'type'		=> 'multiselect',
						'description'	=> __('The shipping fee will be free if at least one item belongs to the selected shipping classes.', 'flat-rate-per-countryregion-for-woocommerce'),
						'class'		=> 'chosen_select',
						'css'		=> 'width: 450px;',
						'default'	=> '',
						'options'	=> $this->shipping_classes,
						'desc_tip'		=> true
					);
				}
			}

			/* Per Country form fields */
			function init_form_fields_per_country() {
				global $woocommerce;
				$this->form_fields['per_country']=array(
					'title'		 => __('Per Country Rates', 'flat-rate-per-countryregion-for-woocommerce'),
					'type'		  => 'title'
				);
				$this->form_fields['per_country_count']=array(
					'title'		=> __('Number of Country rules', 'flat-rate-per-countryregion-for-woocommerce'),
					'type'			=> 'number',
					'description'	=> __('How many diferent "per country" rates do you want to set?', 'flat-rate-per-countryregion-for-woocommerce').' '.__('Please save the options after changing this value.', 'flat-rate-per-countryregion-for-woocommerce'),
					'default'		=> 0,
					'desc_tip'		=> true
				);
				$count=intval($this->settings['per_country_count']);
				for($counter = 1; $count >= $counter; $counter++) {
					$this->form_fields['per_country_'.$counter.'_sep']=array(
						'title'		=> sprintf(__( 'Country rule #%s', 'flat-rate-per-countryregion-for-woocommerce'), $counter),
						'class'		=> 'rules_sep',
						'type'		=> 'rules_sep'
					);
					$this->form_fields['per_country_'.$counter.'_c']=array(
						'title'		=> '<span class="rules_items">'.__( 'Country', 'flat-rate-per-countryregion-for-woocommerce').'</span>',
						'type'		=> 'multiselect',
						'description'	=> __('Choose one or more countries for this rule.', 'flat-rate-per-countryregion-for-woocommerce'),
						'class'		=> 'chosen_select',
						'css'		=> 'width: 450px;',
						'default'	=> '',
						'options'	=> $woocommerce->countries->countries,
						'desc_tip'		=> true
					);
					$this->form_fields['per_country_'.$counter.'_txt']=array(
						'title'		=> '<span class="rules_items">'.__( 'Rule name', 'flat-rate-per-countryregion-for-woocommerce').'</span>',
						'type'			=> 'text',
						'description'	=> __('The name for this rule, if you choose to show it to the client.', 'flat-rate-per-countryregion-for-woocommerce'),
						'default'		=> '',
						'placeholder'	=> '',
						'desc_tip'		=> true
					);
					$this->form_fields['per_country_'.$counter.'_t']= array(
						'title'		=> '<span class="rules_items">'.__('Apply rate', 'flat-rate-per-countryregion-for-woocommerce').'</span>',
						'type'			=> 'select',
						'description'	=> __('Choose either to apply the shipping fee for the whole order or multiply it per each item.', 'flat-rate-per-countryregion-for-woocommerce'),
						'default'		=> 'per_order',
						'options'		=> array(
								'per_order'	=> __('Per order', 'flat-rate-per-countryregion-for-woocommerce'),
								'per_item'		=> __('Per item', 'flat-rate-per-countryregion-for-woocommerce'),
							),
						'desc_tip'		=> true
					);
					$this->form_fields['per_country_'.$counter.'_fee']=array(
						'title'		=> '<span class="rules_items">'.__( 'Rate', 'flat-rate-per-countryregion-for-woocommerce').' ('.get_woocommerce_currency().')</span>',
						'type'			=> 'price',
						'description'	=> __('The shipping fee for the countries specified above.', 'flat-rate-per-countryregion-for-woocommerce'),
						'default'		=> '',
						'placeholder'	=> '0',
						'desc_tip'		=> true
					);
					$this->form_fields['per_country_'.$counter.'_fr']=array(
						'title'		=> '<span class="rules_items">'.__( 'Free for orders above', 'flat-rate-per-countryregion-for-woocommerce').' ('.get_woocommerce_currency().')</span>',
						'type'			=> 'price',
						'description'	=> __('The shipping fee will be free if the order total reaches this value. Empty or zero for no free shipping.', 'flat-rate-per-countryregion-for-woocommerce'),
						'default'		=> '',
						'placeholder'	=> '0',
						'desc_tip'		=> true
					);
					$this->form_fields['per_country_'.$counter.'_fr_class']=array(
						'title'		=> '<span class="rules_items">'.__('Free for shipping classes', 'flat-rate-per-countryregion-for-woocommerce').'</span>',
						'type'		=> 'multiselect',
						'description'	=> __('The shipping fee will be free if at least one item belongs to the selected shipping classes.', 'flat-rate-per-countryregion-for-woocommerce'),
						'class'		=> 'chosen_select',
						'css'		=> 'width: 450px;',
						'default'	=> '',
						'options'	=> $this->shipping_classes,
						'desc_tip'		=> true
					);
				}
			}

			/* Per State form fields */
			function init_form_fields_per_state() {
				global $woocommerce;
				$this->form_fields['per_state']=array(
					'title'		 => __('Per State Rates', 'flat-rate-per-countryregion-for-woocommerce'),
					'type'		  => 'title'
				);
				$this->form_fields['per_state_count']=array(
					'title'		=> __('Number of State rules', 'flat-rate-per-countryregion-for-woocommerce'),
					'type'			=> 'number',
					'description'	=> __('How many diferent "per state" rates do you want to set?', 'flat-rate-per-countryregion-for-woocommerce').' '.__('Please save the options after changing this value.', 'flat-rate-per-countryregion-for-woocommerce'),
					'default'		=> 0,
					'desc_tip'		=> true
				);
				$count=intval($this->settings['per_state_count']);
				for($counter = 1; $count >= $counter; $counter++) {
					$this->form_fields['per_state_'.$counter.'_sep']=array(
						'title'		=> sprintf(__( 'State rule #%s', 'flat-rate-per-countryregion-for-woocommerce'), $counter),
						'class'		=> 'rules_sep',
						'type'		=> 'rules_sep'
					);
					$this->form_fields['per_state_'.$counter.'_c']=array(
						'title'		=> '<span class="rules_items">'.__( 'Country', 'flat-rate-per-countryregion-for-woocommerce').'</span>',
						'type'		=> 'select',
						'description'	=> __('Choose the country for this rule.', 'flat-rate-per-countryregion-for-woocommerce').' '.__('Please save the options after changing this value.', 'flat-rate-per-countryregion-for-woocommerce'),
						'class'		=> 'chosen_select',
						'css'		=> 'width: 450px;',
						'default'	=> '',
						'options'	=> $woocommerce->countries->countries,
						'desc_tip'		=> true
					);
					if (isset($this->settings['per_state_'.$counter.'_c']) && !empty($this->settings['per_state_'.$counter.'_c'])) {
						$this->form_fields['per_state_'.$counter.'_s']=array(
							'title'		=> '<span class="rules_items">'.__( 'State', 'flat-rate-per-countryregion-for-woocommerce').'</span>',
							'type'		=> 'multiselect',
							'description'	=> __('Choose one or more states for this rule.', 'flat-rate-per-countryregion-for-woocommerce'),
							'class'		=> 'chosen_select',
							'css'		=> 'width: 450px;',
							'default'	=> '',
							'options'	=> $woocommerce->countries->get_states($this->settings['per_state_'.$counter.'_c']),
							'desc_tip'		=> true
						);
						$this->form_fields['per_state_'.$counter.'_txt']=array(
							'title'		=> '<span class="rules_items">'.__( 'Rule name', 'flat-rate-per-countryregion-for-woocommerce').'</span>',
							'type'			=> 'text',
							'description'	=> __('The name for this rule, if you choose to show it to the client.', 'flat-rate-per-countryregion-for-woocommerce'),
							'default'		=> '',
							'placeholder'	=> '',
							'desc_tip'		=> true
						);
						$this->form_fields['per_state_'.$counter.'_t']= array(
							'title'		=> '<span class="rules_items">'.__('Apply rate', 'flat-rate-per-countryregion-for-woocommerce').'</span>',
							'type'			=> 'select',
							'description'	=> __('Choose either to apply the shipping fee for the whole order or multiply it per each item.', 'flat-rate-per-countryregion-for-woocommerce'),
							'default'		=> 'per_order',
							'options'		=> array(
									'per_order'	=> __('Per order', 'flat-rate-per-countryregion-for-woocommerce'),
									'per_item'		=> __('Per item', 'flat-rate-per-countryregion-for-woocommerce'),
								),
							'desc_tip'		=> true
						);
						$this->form_fields['per_state_'.$counter.'_fee']=array(
							'title'		=> '<span class="rules_items">'.__( 'Rate', 'flat-rate-per-countryregion-for-woocommerce').' ('.get_woocommerce_currency().')</span>',
							'type'			=> 'price',
							'description'	=> __('The shipping fee for the states specified above.', 'flat-rate-per-countryregion-for-woocommerce'),
							'default'		=> '',
							'placeholder'	=> '0',
							'desc_tip'		=> true
						);
						$this->form_fields['per_state_'.$counter.'_fr']=array(
							'title'		=> '<span class="rules_items">'.__( 'Free for orders above', 'flat-rate-per-countryregion-for-woocommerce').' ('.get_woocommerce_currency().')</span>',
							'type'			=> 'price',
							'description'	=> __('The shipping fee will be free if the order total reaches this value. Empty or zero for no free shipping.', 'flat-rate-per-countryregion-for-woocommerce'),
							'default'		=> '',
							'placeholder'	=> '0',
							'desc_tip'		=> true
						);
						$this->form_fields['per_state_'.$counter.'_fr_class']=array(
							'title'		=> '<span class="rules_items">'.__('Free for shipping classes', 'flat-rate-per-countryregion-for-woocommerce').'</span>',
							'type'		=> 'multiselect',
							'description'	=> __('The shipping fee will be free if at least one item belongs to the selected shipping classes.', 'flat-rate-per-countryregion-for-woocommerce'),
							'class'		=> 'chosen_select',
							'css'		=> 'width: 450px;',
							'default'	=> '',
							'options'	=> $this->shipping_classes,
							'desc_tip'		=> true
						);
					} else {
						//País ainda não escolhido.
					}
					
				}
			}

			function generate_rules_sep_html($key, $data) {
				$defaults = array(
					'title'	=> '',
					'class'	=> ''
				);
				$data = wp_parse_args($data, $defaults);
				ob_start();
				?>
				<tr valign="top">
					<th colspan="2" class="<?php echo esc_attr( $data['class'] ); ?>"><?php echo wp_kses_post($data['title']); ?></th>
				</tr>
				<?php
				return ob_get_clean();
			}

			function admin_options() {
				global $woocommerce;
				?>
				<h3><?php echo $this->method_title; ?></h3>
				<p><?php echo $this->method_description; ?></p>
				<p><a href="#" onclick="jQuery('#WC_FRPC_Country_List').show();"><?php _e('Click here to see list of regions, and the countries included on each one.', 'flat-rate-per-countryregion-for-woocommerce'); ?></a></p>
				<div id="WC_FRPC_Country_List" style="display: none; margin: 10px; padding: 10px; background-color: #EEE;">
					<?php
					foreach($this->regionslist as $key => $region) {
						?>
						<p><b><?php echo $region; ?>:</b><br/>
						<?php
						$countries=array();
						foreach($this->regions[$key]['countries'] as $country) {
							if (trim($woocommerce->countries->countries[$country])!='') $countries[]=$woocommerce->countries->countries[$country];
						}
						sort($countries, SORT_LOCALE_STRING);
						echo implode(', ', $countries);
						?>
						</p>
						<?php
					}
					?>
					<p style="text-align: center;">[<a href="#" onclick="jQuery('#WC_FRPC_Country_List').hide();"><?php _e('Close country list', 'flat-rate-per-countryregion-for-woocommerce'); ?></a>]</p>
				</div>
				<table class="form-table">
				<?php $this->generate_settings_html(); ?>
				</table>
				<style type="text/css">
					.form-table th {
						width: 250px;
					}
					.woocommerce_page_wc-settings h4.wc-settings-sub-title {
						font-size: 1.4em;
						padding-bottom: 0.5em;
						border-bottom: 1px solid #444;
					}
					.woocommerce_page_wc-settings .rules_sep {
						border-bottom: 1px solid #CCC;
					}
					.woocommerce_page_wc-settings .rules_items {
						padding-left: 2em;
						font-weight: normal;
					}
				</style>
				<?php
			}

			/* Removes the "(Free)" text from the shipping label if the rate is zero */
			public function remove_free_price_text($full_label, $method) {
				return str_replace(' ('.__('Free', 'woocommerce').')', '', $full_label);
			}

			/* Find shipping classes on the ordered items - Stolen from flat-rate shipping */
			public function find_shipping_classes( $package ) {
				$found_shipping_classes = array();
				// Find shipping classes for products in the cart
				if ( sizeof( $package['contents'] ) > 0 ) {
					foreach ( $package['contents'] as $item_id => $values ) {
						if ( $values['data']->needs_shipping() ) {
							$found_class = $values['data']->get_shipping_class();
							if ( ! isset( $found_shipping_classes[ $found_class ] ) ) {
								$found_shipping_classes[ $found_class ] = array();
							}
							$found_shipping_classes[ $found_class ][ $item_id ] = $values;
						}
					}
				}
				return $found_shipping_classes;
			}

			/* Calculate the rate */
			public function calculate_shipping($package = array()) {
				// This is where you'll add your rates
				global $woocommerce;
				//Per order by default
				$tax_type='per_order';
				//Order total
				if (WC()->cart->prices_include_tax)
					$order_total = WC()->cart->cart_contents_total + array_sum( WC()->cart->taxes );
				else
					$order_total = WC()->cart->cart_contents_total;
				//Label
				$label='';
				if(trim($package['destination']['country'])!='') {
					$final_rate=-1;
					//State
					if ($final_rate==-1) {
						$count=intval($this->settings['per_state_count']);
						for($i=1; $i<=$count; $i++){
							if (is_array($this->settings['per_state_'.$i.'_s'])) {
								if (trim($package['destination']['country'])==$this->settings['per_state_'.$i.'_c']) { //País correcto
									$states=$woocommerce->countries->get_states($this->settings['per_state_'.$counter.'_c']);
									//var_dump($states);
									if (in_array(trim($package['destination']['state']), $this->settings['per_state_'.$i.'_s'])) { //State found in this state rule
										if (isset($this->settings['per_state_'.$i.'_fee']) && is_numeric($this->settings['per_state_'.$i.'_fee'])) { //Rate is set for this rule
											//The rate
											$final_rate=$this->settings['per_state_'.$i.'_fee'];
											//Free based on price?
											if (isset($this->settings['per_state_'.$i.'_fr']) && ! empty($this->settings['per_state_'.$i.'_fr'])) {
												if (intval($this->settings['per_state_'.$i.'_fr'])>0) {
													if ($order_total>=intval($this->settings['per_state_'.$i.'_fr'])) $final_rate=0; //Free
												}
											}
											//Free based on shipping class?
											if (is_array($this->settings['per_state_'.$i.'_fr_class'])) {
												if (count($this->settings['per_state_'.$i.'_fr_class'])>0) {
													foreach ($this->find_shipping_classes($package) as $shipping_class => $items) {
														if (trim($shipping_class)!='') {
															if (in_array($shipping_class, $this->settings['per_state_'.$i.'_fr_class'])) {
																$final_rate=0; //Free
																break;
															}
														}
													}
												}
											}
											//Per order or per item?
											if (isset($this->settings['per_state_'.$i.'_t']) && ! empty($this->settings['per_state_'.$i.'_t'])) $tax_type=$this->settings['per_state_'.$i.'_t'];
											//The label
											if ($this->settings['show_region_country']=='rule_name') {
												$label=$this->settings['per_state_'.$i.'_txt'];
											} else {
												$label=$states[trim($package['destination']['country'])][trim($package['destination']['state'])];
											}
											break;
										}
									}
								}
							}
						}
					}
					//Country
					if ($final_rate==-1) {
						$count=intval($this->settings['per_country_count']);
						for($i=1; $i<=$count; $i++){
							if (is_array($this->settings['per_country_'.$i.'_c'])) {
								if (in_array(trim($package['destination']['country']), $this->settings['per_country_'.$i.'_c'])) { //Country found in this country rule
									if (isset($this->settings['per_country_'.$i.'_fee']) && is_numeric($this->settings['per_country_'.$i.'_fee'])) { //Rate is set for this rule
										//The rate
										$final_rate=$this->settings['per_country_'.$i.'_fee'];
										//Free based on price?
										if (isset($this->settings['per_country_'.$i.'_fr']) && ! empty($this->settings['per_country_'.$i.'_fr'])) {
											if (intval($this->settings['per_country_'.$i.'_fr'])>0) {
												if ($order_total>=intval($this->settings['per_country_'.$i.'_fr'])) $final_rate=0; //Free
											}
										}
										//Free based on shipping class?
										if (is_array($this->settings['per_country_'.$i.'_fr_class'])) {
											if (count($this->settings['per_country_'.$i.'_fr_class'])>0) {
												foreach ($this->find_shipping_classes($package) as $shipping_class => $items) {
													if (trim($shipping_class)!='') {
														if (in_array($shipping_class, $this->settings['per_country_'.$i.'_fr_class'])) {
															$final_rate=0; //Free
															break;
														}
													}
												}
											}
										}
										//Per order or per item?
										if (isset($this->settings['per_country_'.$i.'_t']) && ! empty($this->settings['per_country_'.$i.'_t'])) $tax_type=$this->settings['per_country_'.$i.'_t'];
										//The label
										if ($this->settings['show_region_country']=='rule_name') {
											$label=$this->settings['per_country_'.$i.'_txt'];
										} else {
											$label=$woocommerce->countries->countries[trim($package['destination']['country'])];
										}
										break;
									}
								}
							}
						}
					}
					//Region
					if ($final_rate==-1) {
						$count=intval($this->settings['per_region_count']);
						for($i=1; $i<=$count; $i++){
							if (is_array($this->settings['per_region_'.$i.'_region'])) {
								foreach($this->settings['per_region_'.$i.'_region'] as $region) {
									if (in_array(trim($package['destination']['country']), $this->regions[trim($region)]['countries'])) { //Country found in this region rule
										if (isset($this->settings['per_region_'.$i.'_fee']) && is_numeric($this->settings['per_region_'.$i.'_fee'])) { //Rate is set for this rule
											//The rate
											$final_rate=$this->settings['per_region_'.$i.'_fee'];
											//Free based on price?
											if (isset($this->settings['per_region_'.$i.'_fr']) && ! empty($this->settings['per_region_'.$i.'_fr'])) {
												if (intval($this->settings['per_region_'.$i.'_fr'])>0) {
													if ($order_total>=intval($this->settings['per_region_'.$i.'_fr'])) $final_rate=0; //Free
												}
											}
											//Free based on shipping class?
											if (is_array($this->settings['per_region_'.$i.'_fr_class'])) {
												if (count($this->settings['per_region_'.$i.'_fr_class'])>0) {
													foreach ($this->find_shipping_classes($package) as $shipping_class => $items) {
														if (trim($shipping_class)!='') {
															if (in_array($shipping_class, $this->settings['per_region_'.$i.'_fr_class'])) {
																$final_rate=0; //Free
																break;
															}
														}
													}
												}
											}
											//Per order or per item?
											if (isset($this->settings['per_region_'.$i.'_t']) && ! empty($this->settings['per_region_'.$i.'_t'])) $tax_type=$this->settings['per_region_'.$i.'_t'];
											//The label
											if ($this->settings['show_region_country']=='rule_name') {
												$label=$this->settings['per_region_'.$i.'_txt'];
											} else {
												$label=$this->regions[trim($region)]['name'];
											}
											break;
										}
									}
								}
								if ($final_rate!=-1) break; //Region rate found, break for
							}
						}
					}
					//Rest of the World
					if ($final_rate==-1) {
						if (isset($this->settings['fee_world']) && is_numeric($this->settings['fee_world'])) {
							//The rate
							$final_rate=$this->settings['fee_world'];
							//Free based on price?
							if (isset($this->settings['world_free_above']) && ! empty($this->settings['world_free_above'])) {
								if (intval($this->settings['world_free_above'])>0) {
									if ($order_total>=intval($this->settings['world_free_above'])) $final_rate=0; //Free
								}
							}
							//Free based on shipping class?
							if (is_array($this->settings['world_free_class'])) {
								if (count($this->settings['world_free_class'])>0) {
									foreach ($this->find_shipping_classes($package) as $shipping_class => $items) {
										if (trim($shipping_class)!='') {
											if (in_array($shipping_class, $this->settings['world_free_class'])) {
												$final_rate=0; //Free
												break;
											}
										}
									}
								}
							}
							//Per order or per item?
							if (isset($this->settings['tax_type']) && ! empty($this->settings['tax_type'])) $tax_type=$this->settings['tax_type'];
							//The label
							if ($this->settings['show_region_country']=='rule_name') {
								$label=$this->settings['world_rulename'];
							} else {
								$label=__('Rest of the World', 'flat-rate-per-countryregion-for-woocommerce');
							}
						}
					}
					//Let's customize the label
					if (isset($this->settings['show_region_country']) && ! empty($this->settings['show_region_country'])) {
						switch($this->settings['show_region_country']) {
							case 'region':
							case 'rule_name':
								//The default or already set
								break;
							case 'country':
								$label=$woocommerce->countries->countries[trim($package['destination']['country'])];
								break;
							case 'title':
								$label=$this->title;
								break;
							case 'title_region':
								$label=$this->title.' - '.$label;
								break;
							case 'title_country':
								$label=$this->title.' - '.$woocommerce->countries->countries[trim($package['destination']['country'])];
								break;
							default:
								//The default - already set
								break;
						}
					}
					//Still no rate found. Well... That means it's free right?
					if ($final_rate==-1) {
						$final_rate=0;
						$label=__('Flat rate not set', 'flat-rate-per-countryregion-for-woocommerce');
					}
				} else {
					$final_rate=0; //No country? Is the client from outer world?
				}
				$rate = array(
					'id'	   => $this->id,
					'label'	=> (trim($label)!='' ? $label : $this->title),
					'cost'	 => floatval($final_rate),
					'calc_tax' => 'per_order'
				);
				//Is it per item?
				//if (isset($this->settings['tax_type']) && ! empty($this->settings['tax_type'])) {
					//switch($this->settings['tax_type']) {
					switch($tax_type) {
						case 'per_order':
							//The default - already set
							break;
						case 'per_item':
							$final_rate_items=0;
							foreach ($package['contents'] as $item_id => $values) {
								$_product=$values['data'];
								if ($values['quantity']>0 && $_product->needs_shipping()) {
									$final_rate_items+=floatval($final_rate)*floatval($values['quantity']);
								}
							}
							$rate['cost']=$final_rate_items;
							//$rate['calc_tax']='per_item'; //Not really needed, is it?
							break;
						default:
							//The default - already set
							break;
					}
				//}
				// Register the rate
				$this->add_rate($rate);
			}

		}
		}

	}
	add_action( 'woocommerce_shipping_init', 'woocommerce_flatrate_percountry_init' );

	/* Add to WooCommerce */
	function woocommerce_flatrate_percountry_add( $methods ) {
		$methods[] = 'WC_Flat_Rate_Per_Country_Region'; 
		return $methods;
	}
	add_filter( 'woocommerce_shipping_methods', 'woocommerce_flatrate_percountry_add' );

	/* If you're reading this you must know what you're doing ;-) Greetings from sunny Portugal! */
	
}