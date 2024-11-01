<?php
/**
 * Plugin Name:       ZPT Metals
 * Plugin URI:        https://developers.zactonz.com/wp/plugins/zpt-metals
 * Description:       Display Precious Metals(Gold, Silver, Platinum & 30+ more metals) rates in desired currencies (USD, GBP, CAD etc). This plugin supports WC Product auto pricing to display metal product price live and add to cart.
 * Author:            Zactonz Technologies
 * Author URI:        https://zactonz.com
 * Version:           1.2.1
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       zpt-metals
 * 
 * 
*/

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

define( "ZPTMETALSPATH", dirname( __FILE__ ) );

define('ALTERNATE_WP_CRON', true);

include_once( ZPTMETALSPATH.'/autoload.php' );



