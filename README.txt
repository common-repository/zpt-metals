=== ZPT Metals ===

Contributors: zactonz
Plugin Name: ZPT Metals
Plugin URI: https://developers.zactonz.com/wp/plugins/zpt-metals/
Tags: metals api, gold rates, silver rates, metals rates, zinc, copper, platinum
Author URI: https://zactonz.com/
Author: Zactonz Technologies
Requires at least: 5.0
Tested up to: 6.1
Version: 1.2.1 
Stable tag: 1.2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

A solution provided to display precious Metals(Gold, Silver, Platinum and 36+ metals) rates in the desired currencies (USD,GBP, CAD etc). 

Plugin comes up with a comprehensive controls to display desired date metal rates as well as latest, with the help of short codes with custom WP Editor. Now it support woocommerce, user can enable auto pricing for products by connecting metal from the given metals dropdown.

Features:
-   Latest metals rate
-   Shortcode to display latest/historical rates
-   Rates in desired currency
-   Cron job option ( Custom Settings )
-   Support Woo-commerce
-   WC Product auto pricing for desired metal

Supported metals:
-   Gold
-   Silver
-   Platinum
-   Palladium
-   Rhodium
-   Ruthenium
-   Copper
-   Aluminum
-   Nickel
-   Zinc
-   Tin
-   Cobalt
-   Iridium
-   Lead
-   Iron Ore
-   LBMA GOLD AM
-   LBMA GOLD PM
-   LBMA Platinum AM
-   LBMA Platinum PM
-   LBMA Palladium AM
-   LBMA Palladium PM
-   LME Aluminium
-   LME Copper
-   LME Zinc
-   LME Nickel
-   LME Lead
-   LME Tin
-   Uranium
-   STEEL-SC
-   STEEL-RE
-   STEEL-HR
-   BRONZE
-   MG
-   OSMIUM
-   RHENIUM
-   INDIUM
-   MO
-   TUNGSTEN


*Installing and using ZPT Metals Plugin*
[youtube https://www.youtube.com/watch?v=mxMidL-b-q0]

*How to use ZPT Metals with WooCommerce*
[youtube https://www.youtube.com/watch?v=BT_pv0Nj9Uc]


<strong>Short code details</strong>       
Use shortcode [zpt-metals] to display metal rates on your wp website.

Following are the params that you can pass to display your desired shortcode output:

>type
Its required to display which metal rates you want to display. Possible value can be any of; gold, silver, platinum, palladium, rhodium, ruthenium
Example: [zpt-metals type="gold"]
                
>date-format
Its optional to display desired date format. Possible value can be any of; Y-m-d, m-d-Y etc. 
Example: [zpt-metals date-format="Y-m-d"]

>base
Its optional to display rates of metal in a specific currency. Possible value can be any of; USD, GBP etc.
Example: [zpt-metals price-round="USD"]

>price-round
Its optional to display desired digits after decimal. Possible value can be any of integer.
Example: [zpt-metals price-round="2"]

>date
Its optional to display rates for a specific date. Possible value can be a date(YYYY-MM-DD) format.
Example: [zpt-metals date="2022-03-01"]


DISCLAIMER:  This plugin is relying on a 3rd party(metals-api.com) as a service. You will need to get an API key from <a href="https://metals-api.com">https://metals-api.com</a> and use that key to get metals rate. This plugin is just an interface to fetch latest rates from metals-api.com on the behalf of your API key. <a href="https://metals-api.com/privacy/">Read privacy policies of metals-api</a>

== Installation ==

Upload the extracted plugin files to the /wp-content/plugins directory, or install the plugin through the WordPress plugins screen directly.

Activate the plugin through the "Plugins" menu in WordPress and then enter your metals-api.com API key.


== Screenshots ==

1. Setup Metals plugin settings
2. Shortcode usage in editor
3. Shortcode resultant
4. Choose product to edit for auto pricing
5. Select auto pricing product data tab
6. Enable product auto pricing
7. Before and After product price


== Changelog ==

= 1.0 =
* Initial release
= 1.1.0 =
* Added new metals (17)
* Added new attribute (carat) only for GOLD
* Fixed bugs
= 1.2.0 =
* Added Woocommerce support
* Added Products auto pricing in product data tabs for Woocommerce
* Added Cron job option
* Fixed bugs
= 1.2.1 =
* Added new metals (10) support from metals api.

== Frequently Asked Questions ==

= Is API required to run this plugin? = 

Yes. Metals API key is required to fetch latest metal rates.

= Does Plugin handles API rate limits? = 

Yes. Plugin will request latest rates from API after above set minutes from its last run and then save rates into database. Until next run, plugin will serve saved rates from database to visitors.
