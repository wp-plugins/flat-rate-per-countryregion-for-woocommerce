<?php
/**
 * Plugin Name: Flat Rate per Country/Region for WooCommerce
 * Plugin URI: http://www.webdados.pt/produtos-e-servicos/internet/desenvolvimento-wordpress/flat-rate-per-countryregion-woocommerce-wordpress/
 * Description: This plugin allows you to set a flat delivery rate per countries or world regions (and a fallback "Rest of the World" rate) on WooCommerce.
 * Version: 1.4.1
 * Author: Webdados
 * Author URI: http://www.webdados.pt
 * Text Domain: woocommerce_flatrate_percountry
 * Domain Path: /lang
**/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
	
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
            	load_plugin_textdomain($this->id, false, dirname(plugin_basename(__FILE__)) . '/lang/');
				$this->method_title			= __('Flat Rate per Country/Region', $this->id);
				$this->method_description	= __('Allows you to set a flat delivery rate per country and/or world region.<br/><br/>If you set a rate for the client\'s country it will be used. Otherwise, if you set a rate for client\'s region it will be used.<br/>If none of the rates are set, the "Rest of the World" rate will be used.', $this->id).'<br/><br/>'.__('You can also choose either to apply the shipping fee for the whole order or multiply it per each item.', $this->id);
				$this->init();
        		$this->init_form_fields_per_region();
        		$this->init_form_fields_per_country();
			}

			/* Init the settings */
			function init() {
				//Let's sort arrays the right way
				setlocale(LC_COLLATE, get_locale());
				//Regions - Source: http://www.geohive.com/earth/gen_codes.aspx
				$this->regions = array(
					//Africa
					'AF_EA' => array(
						'name' => __('Africa - Eastern Africa', $this->id),
						'countries' => array('BI', 'KM' ,'DJ', 'ER', 'ET', 'KE', 'MG', 'MW', 'MU', 'YT', 'MZ', 'RE', 'RW', 'SC', 'SO', 'TZ', 'UG', 'ZM', 'ZW'),
					),
					'AF_MA' => array(
						'name' => __('Africa - Middle Africa', $this->id),
						'countries' => array('AO', 'CM', 'CF', 'TD', 'CG', 'CD', 'GQ', 'GA', 'ST'),
					),
					'AF_NA' => array(
						'name' => __('Africa - Northern Africa', $this->id),
						'countries' => array('DZ', 'EG', 'LY', 'MA', 'SS', 'SD', 'TN', 'EH'),
					),
					'AF_SA' => array(
						'name' => __('Africa - Southern Africa', $this->id),
						'countries' => array('BW', 'LS', 'NA', 'ZA', 'SZ'),
					),
					'AF_WA' => array(
						'name' => __('Africa - Western Africa', $this->id),
						'countries' => array('BJ', 'BF', 'CV', 'CI', 'GM', 'GH', 'GN', 'GW', 'LR', 'ML', 'MR', 'NE', 'NG', 'SH', 'SN', 'SL', 'TG'),
					),
					//Americas
					'AM_LAC' => array(
						'name' => __('Americas - Latin America and the Caribbean', $this->id),
						'countries' => array('AI', 'AG', 'AW', 'BS', 'BB', 'BQ', 'VG', 'KY', 'CU', 'CW', 'DM', 'DO', 'GD', 'GP', 'HT', 'JM', 'MQ', 'MS', 'PR', 'BL', 'KN', 'LC', 'MF', 'VC', 'SX', 'TT', 'TC', 'VI'),
					),
					'AM_CA' => array(
						'name' => __('Americas - Central America', $this->id),
						'countries' => array('BZ', 'CR', 'SV', 'GT', 'HN', 'MX', 'NI', 'PA'),
					),
					'AM_SA' => array(
						'name' => __('Americas - South America', $this->id),
						'countries' => array('AR', 'BO', 'BR', 'CL', 'CO', 'EC', 'FK', 'GF', 'GY', 'PY', 'PE', 'SR', 'UY', 'VE'),
					),
					'AM_NA' => array(
						'name' => __('Americas - Northern America', $this->id),
						'countries' => array('BM', 'CA', 'GL', 'PM', 'US'),
					),
					//Asia
					'AS_CA' => array(
						'name' => __('Asia - Central Asia', $this->id),
						'countries' => array('KZ', 'KG', 'TJ', 'TM', 'UZ'),
					),
					'AS_EA' => array(
						'name' => __('Asia - Eastern Asia', $this->id),
						'countries' => array('CN', 'HK', 'MO', 'JP', 'KP', 'KR', 'MN', 'TW'),
					),
					'AS_SA' => array(
						'name' => __('Asia - Southern Asia', $this->id),
						'countries' => array('AF', 'BD', 'BT', 'IN', 'IR', 'MV', 'NP', 'PK', 'LK'),
					),
					'AS_SEA' => array(
						'name' => __('Asia - South-Eastern Asia', $this->id),
						'countries' => array('BN', 'KH', 'ID', 'LA', 'MY', 'MM', 'PH', 'SG', 'TH', 'TL', 'VN'),
					),
					'AS_WA' => array(
						'name' => __('Asia - Western Asia', $this->id),
						'countries' => array('AM', 'AZ', 'BH', 'CY', 'GE', 'IQ', 'IL', 'JO', 'KW', 'LB', 'PS', 'OM', 'QA', 'SA', 'SY', 'TR', 'AE', 'YE'),
					),
					//Europe
					'EU_EE' => array(
						'name' => __('Europe - Eastern Europe', $this->id),
						'countries' => array('BY', 'BG', 'CZ', 'HU', 'MD', 'PL', 'RO', 'RU', 'SK', 'UA'),
					),
					'EU_NE' => array(
						'name' => __('Europe - Northern Europe', $this->id),
						'countries' => array('AX', 'DK', 'EE', 'FO', 'FI', 'GG', 'IS', 'IE', 'JE', 'LV', 'LT', 'IM', 'NO', 'SJ', 'SE', 'GB'),
					),
					'EU_SE' => array(
						'name' => __('Europe - Southern Europe', $this->id),
						'countries' => array('AL', 'AD', 'BA', 'HR', 'GI', 'GR', 'VA', 'IT', 'MK', 'MT', 'ME', 'PT', 'SM', 'RS', 'SI', 'ES'),
					),
					'EU_WE' => array(
						'name' => __('Europe - Western Europe', $this->id),
						'countries' => array('AT', 'BE', 'FR', 'DE', 'LI', 'LU', 'MC', 'NL', 'CH'),
					),
					//Oceania
					'OC_ANZ' => array(
						'name' => __('Oceania - Australia and New Zealand', $this->id),
						'countries' => array('AU', 'CX', 'CC', 'NZ', 'NF'),
					),
					'OC_ML' => array(
						'name' => __('Oceania - Melanesia', $this->id),
						'countries' => array('FJ', 'NC', 'PG', 'SB', 'VU'),
					),
					'OC_MN' => array(
						'name' => __('Oceania - Micronesia', $this->id),
						'countries' => array('GU', 'KI', 'MH', 'FM', 'NR', 'MP', 'PW'),
					),
					'OC_PL' => array(
						'name' => __('Oceania - Polynesia', $this->id),
						'countries' => array('AS', 'CK', 'PF', 'NU', 'PN', 'WS', 'TK', 'TO', 'TV', 'WF'),
					),
					/*
					'UNCLASSIFIED' => array(
						'name' => __('Unclassified', $this->id),
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
						'title'         => __('Global settings', $this->id),
						'type'          => 'title'
					),
					'enabled' => array(
						'title' 		=> __('Enable/Disable', 'woocommerce'),
						'type' 			=> 'checkbox',
						'label' 		=> __('Enable this shipping method', 'woocommerce'),
						'default' 		=> 'no',
						'desc_tip'		=> true
					),
					'title' => array(
						'title' 		=> __('Method Title', 'woocommerce'),
						'type' 			=> 'text',
						'description' 	=> __('This controls the title which the user sees during checkout.', 'woocommerce').' '.__('(If chosen below)', $this->id),
						'default'		=> __('Flat Rate per Country/Region', $this->id),
						'desc_tip'		=> true
					),
					'show_region_country' => array(
						'title' 		=> __('Label to show to the user', $this->id),
						'type' 			=> 'select',
						'description' 	=> __('Choose either to show the region name, the country name, the method title (or a combination of these) on the checkout screen.', $this->id),
						'default' 		=> 'region',
						'options'		=> array(
								'country' 		=> __('Country', $this->id),
								'region' 		=> __('Country or Region name or "Rest of the World"', $this->id),
								'title' 		=> __('Method Title', 'woocommerce').' '.__('(as defined above)', $this->id),
								'title_country'	=> __('Method Title', 'woocommerce').' + '.__('Country', $this->id),
								'title_region'	=> __('Method Title', 'woocommerce').' + '.__('Country or Region name or "Rest of the World"', $this->id),
							),
						'desc_tip'		=> true
					),
					'tax_status' => array(
						'title' 		=> __('Tax Status', 'woocommerce'),
						'type' 			=> 'select',
						'description' 	=> '',
						'default' 		=> 'taxable',
						'options'		=> array(
								'taxable' 	=> __('Taxable', 'woocommerce'),
								'none' 		=> __('None', 'woocommerce'),
							),
						'desc_tip'		=> true
					),
					'tax_type' => array(
						'title' 		=> __('Apply rate', $this->id),
						'type' 			=> 'select',
						'description' 	=> __('Choose either to apply the shipping fee for the whole order or multiply it per each item.', $this->id),
						'default' 		=> 'per_order',
						'options'		=> array(
								'per_order' 	=> __('Per order', $this->id),
								'per_item' 		=> __('Per item', $this->id),
							),
						'desc_tip'		=> true
					),
					'remove_free' => array(
						'title' 		=> __('Remove "(Free)"', $this->id),
						'type' 			=> 'checkbox',
						'description'	=> __('If the final rate is zero, remove the "(Free)" text from the checkout screen. Useful if you need to get a quote for the shipping cost from the carrier.', $this->id),
						'label' 		=> __('Remove "(Free)" from checkout if delivery rate equals zero', 'woocommerce'),
						'default' 		=> 'no',
						'desc_tip'		=> true
					),
					'fee_world' => array(
						'title' 		=> __('"Rest of the World" Rate', $this->id).' ('.get_woocommerce_currency().')',
						'type' 			=> 'price',
						'description'	=> __('The shipping fee for all the Countries/Regions not specified bellow.', $this->id),
						'default'		=> '',
						'placeholder'	=> '',
						'desc_tip'		=> true
					),
					'per_region_count' => array(
						'title' 		=> __('Number of Region rules', $this->id),
						'type' 			=> 'number',
						'description'	=> __('How many diferent "per region" rates do you want to set?', $this->id).' '.__('Please save the options after changing this value.', $this->id),
						'default'		=> 1,
						'desc_tip'		=> true
					),
					'per_country_count' => array(
						'title' 		=> __('Number of Country rules', $this->id),
						'type' 			=> 'number',
						'description'	=> __('How many diferent "per country" rates do you want to set?', $this->id).' '.__('Please save the options after changing this value.', $this->id),
						'default'		=> 1,
						'desc_tip'		=> true
					)
				);
				$this->form_fields=$fields;
			}

			/* Per Region form fields */
			function init_form_fields_per_region() {
				//global $woocommerce;
				$this->form_fields['per_region']=array(
					'title'         => __('Per Region Rates', $this->id),
					'type'          => 'title'
				);
				$count=$this->settings['per_region_count'];
				for($counter = 1; $count >= $counter; $counter++) {
					$this->form_fields['per_region_'.$counter.'_region']=array(
						'title'		=> sprintf(__( 'Region #%s', $this->id), $counter),
						'type'		=> 'multiselect',
						'description'	=> __('Choose one or more regions for this rule.', $this->id),
						'class'		=> 'chosen_select',
						'css'		=> 'width: 450px;',
						'default'	=> '',
						'options'	=> $this->regionslist,
						'desc_tip'		=> true
					);
					$this->form_fields['per_region_'.$counter.'_fee']=array(
						'title' 		=> sprintf(__( 'Rate #%s', $this->id), $counter).' ('.get_woocommerce_currency().')',
						'type' 			=> 'price',
						'description'	=> __('The shipping fee for the regions specified above.', $this->id),
						'default'		=> '',
						'placeholder'	=> '',
						'desc_tip'		=> true
					);
				}
			}

			/* Per Country form fields */
			function init_form_fields_per_country() {
				global $woocommerce;
				$this->form_fields['per_country']=array(
					'title'         => __('Per Country Rates', $this->id),
					'type'          => 'title'
				);
				$count=$this->settings['per_country_count'];
				for($counter = 1; $count >= $counter; $counter++) {
					$this->form_fields['per_country_'.$counter.'_country']=array(
						'title'		=> sprintf(__( 'Country #%s', $this->id), $counter),
						'type'		=> 'multiselect',
						'description'	=> __('Choose one or more countries for this rule.', $this->id),
						'class'		=> 'chosen_select',
						'css'		=> 'width: 450px;',
						'default'	=> '',
						'options'	=> $woocommerce->countries->countries,
						'desc_tip'		=> true
					);
					$this->form_fields['per_country_'.$counter.'_fee']=array(
						'title' 		=> sprintf(__( 'Rate #%s', $this->id), $counter).' ('.get_woocommerce_currency().')',
						'type' 			=> 'price',
						'description'	=> __('The shipping fee for the countries specified above.', $this->id),
						'default'		=> '',
						'placeholder'	=> '',
						'desc_tip'		=> true
					);
				}
			}

			function admin_options() {
				global $woocommerce;
 				?>
 				<h3><?php echo $this->method_title; ?></h3>
 				<p><?php echo $this->method_description; ?></p>
 				<p><a href="#" onclick="jQuery('#WC_FRPC_Country_List').show();"><?php _e('Click here to see list of regions, and the countries included on each one.', $this->id); ?></a></p>
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
 					<p style="text-align: center;">[<a href="#" onclick="jQuery('#WC_FRPC_Country_List').hide();"><?php _e('Close country list', $this->id); ?></a>]</p>
 				</div>
 				<table class="form-table">
 				<?php $this->generate_settings_html(); ?>
 				</table> <?php
 			}

			/* Removes the "(Free)" text from the shipping label if the rate is zero */
			public function remove_free_price_text($full_label, $method) {
				return str_replace(' ('.__('Free', 'woocommerce').')', '', $full_label);
			}

			/* Calculate the rate */
			public function calculate_shipping($package = array()) {
				// This is where you'll add your rates
				global $woocommerce;
				$label='';
				if(trim($package['destination']['country'])!='') {
					$final_rate=-1;
					//Country
					$count=$this->settings['per_country_count'];
					for($i=1; $i<=$count; $i++){
						if (is_array($this->settings['per_country_'.$i.'_country'])) {
							if (in_array(trim($package['destination']['country']), $this->settings['per_country_'.$i.'_country'])) { //Country found in this country rule
								if (isset($this->settings['per_country_'.$i.'_fee']) && is_numeric($this->settings['per_country_'.$i.'_fee'])) { //Rate is set for this rule
									$final_rate=$this->settings['per_country_'.$i.'_fee'];
									$label=$woocommerce->countries->countries[trim($package['destination']['country'])];
									break;
								}
							}
						}
					}
					//Region
					if ($final_rate==-1) {
						$count=$this->settings['per_region_count'];
						for($i=1; $i<=$count; $i++){
							if (is_array($this->settings['per_region_'.$i.'_region'])) {
								foreach($this->settings['per_region_'.$i.'_region'] as $region) {
									if (in_array(trim($package['destination']['country']), $this->regions[trim($region)]['countries'])) { //Country found in this region rule
										if (isset($this->settings['per_region_'.$i.'_fee']) && is_numeric($this->settings['per_region_'.$i.'_fee'])) { //Rate is set for this rule
											$final_rate=$this->settings['per_region_'.$i.'_fee'];
											$label=$this->regions[trim($region)]['name'];
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
							$final_rate=$this->settings['fee_world'];
							$label=__('Rest of the World', $this->id);
						}
					}
					//Let's customize the label
					if (isset($this->settings['show_region_country']) && ! empty($this->settings['show_region_country'])) {
						switch($this->settings['show_region_country']) {
							case 'region':
								//The default - already set
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
						$label=__('Flat rate not set', $this->id);
					}
				} else {
					$final_rate=0; //No country? Is the client from outer world?
				}
				$rate = array(
					'id'       => $this->id,
					'label'    => (trim($label)!='' ? $label : $this->title),
					'cost'     => floatval($final_rate),
					'calc_tax' => 'per_order'
				);
				//Is it per item?
				if (isset($this->settings['tax_type']) && ! empty($this->settings['tax_type'])) {
					switch($this->settings['tax_type']) {
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
				}
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