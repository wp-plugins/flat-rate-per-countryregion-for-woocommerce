=== Flat Rate per Country/Region for WooCommerce ===
Contributors: webdados, wonderm00n
Tags: woocommerce, shipping, delivery, ecommerce, e-commerce, country, countries, region, continent, continents, world
Author URI: http://www.webdados.pt
Plugin URI: http://www.webdados.pt/produtos-e-servicos/internet/desenvolvimento-wordpress/flat-rate-per-countryregion-woocommerce-wordpress/
Requires at least: 3.8
Tested up to: 4.0
Stable tag: 1.4.1

This plugin allows you to set a flat delivery rate per countries or world regions (and a fallback "Rest of the World" rate) on WooCommerce.

== Description ==

If you need a simple way to specify a delivery flat rate, based on the country and/or world region of the buyer, on WooCommerce for WordPress, this plugin is for you!

A simple example of this plugin usage is to set a value for delivery in your own country (e.g. Portugal), a different value for your continent (e.g. Europe) and a third one for the rest of the world.

You can create groups for countries and world regions and specify delivery rates for them.

You can also choose either to apply the shipping fee for the whole order or multiply it per each item.

= Features: =

* Create any number of countries groups and set a specific delivery rate for each group;
* Create any number of world regions groups and set a specific delivery rate for each group;
* Specify a fallback "Rest of the World" rate for any destinations not on the groups;
* Apply the shipping fee for the whole order or multiply it per each item;

== Installation ==

Use the included automatic install feature on your WordPress admin panel and search for "Flat Rate per Country/Region for WooCommerce".

== Frequently Asked Questions ==

= Why is there no FAQs? =

The plugin is new, so no question is frequent. Ask us something ;-)

== Changelog ==

= 1.4 =
* Fix: Minor localization issues solved

= 1.4 =
* It's now possible to remove the "(Free)" text from the shipping label if the rate is 0. This can be useful if you need to get a quote for the shipping cost from the carrier. (Thanks Saad Sohail)
* (Temporary) plugin icon for the plugin installer
* Fix: the setlocale() function now uses only LC_COLLATE instead of LC_ALL in order to get the countries array sorted correctly in all languages. LC_ALL would cause interference with WPML. (Thanks Mihai Grigori from OnTheGoSystems)

= 1.3 =
* Fix: Free shipping was not possible because stupid php considers "0" as empty. (Thanks Simone Mastrogiacomo)

= 1.2 =
* It's now possible to choose either to apply the shipping fee for the whole order or multiply it per each item.
* There's new options regardin the title the costumer will see suring checkout.

= 1.1 =
* It's now possible to choose either to show the region or country name when a region or "rest of the world" rate is used.
* Fix: make sure the price fields now respect the localized settings.
* Fix: when changing countries on the checkout page the plugin does not stall the ajax call anymore and the totals are updated.

= 1.0 =
* Initial release.