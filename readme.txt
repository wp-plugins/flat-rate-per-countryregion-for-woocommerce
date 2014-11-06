=== Flat Rate per State/Country/Region for WooCommerce ===
Contributors: webdados, wonderm00n
Tags: woocommerce, shipping, delivery, ecommerce, e-commerce, country, countries, region, continent, continents, world, states, state, districts
Author URI: http://www.webdados.pt
Plugin URI: http://www.webdados.pt/produtos-e-servicos/internet/desenvolvimento-wordpress/flat-rate-per-countryregion-woocommerce-wordpress/
Requires at least: 3.8
Tested up to: 4.0
Stable tag: 2.3.1

This plugin allows you to set a flat delivery rate per States, Countries or World Regions on WooCommerce.

== Description ==

If you need a simple way to specify a delivery flat rate, based on the state and/or country and/or world region of the delivery address, on WooCommerce for WordPress, this plugin is for you!

A simple example of this plugin usage is to set a value for delivery in a specific state (e.g. Lisbon), the rest of your own country (e.g. Portugal), a different value for your continent (e.g. Europe) and a last one for the rest of the world.

You can create groups for states, countries and world regions and specify delivery rates for them.

For each group you can choose either to apply the shipping fee for the whole order or multiply it per each item. You can also set a total order value from which the shipping will be free.

= Features: =

* Create any number of states groups/rules and set a specific delivery rate for each group;
* Create any number of countries groups/rules and set a specific delivery rate for each group;
* Create any number of world regions groups/rules and set a specific delivery rate for each group;
* Specify a fallback "Rest of the World" rate for any destinations not specified on the groups;
* For each group/rule, apply the shipping fee for the whole order or multiply it per each item;
* For each group/rule, set total order value from which the shipping is free;

== Installation ==

Use the included automatic install feature on your WordPress admin panel and search for "Flat Rate per Country/Region for WooCommerce".

== Frequently Asked Questions ==

= Why is there no FAQs? =

The plugin is new, so no question is frequent. Ask us something ;-)

== Changelog ==

= 2.3.1 =
* Fix: Great Britain was declared on the European Union group as UK and not GB

= 2.3 =
* It's now possible to set a free shipping fee (for the all order), if there's at least one item that belongs to a specific shipping class. This is set per rule.
* Fix: Free shipping fee for "rest of the world" order above some amount was not working

= 2.2 =
* WordPress Multisite support

= 2.1 =
* You can now set a name per each rule and then choose to show that field as the title on the checkout

= 2.0.1 =
* The wrong files were uploaded on 2.0. This is the correct version. Please upgrade.

= 2.0 =
* It's now also possible to set rates for states
* Apply rate "Per order" / "Per item" setting is now individual for each group
* Possibility to set a total order value from which the shipping fee is free, also individual for each group
* New special "European Union" region group
* Some tweaks on the settings screen for improved usability
* Fix: changed the textdomain argument to string instead of variable

= 1.4.2 =
* Fix: In some server configurations the single countries field was not being saved to the database

= 1.4.1 =
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