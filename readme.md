## WooCommerce Companies

WooCommerce Companies is a User hasMany Companies, User hasMany Addresses and Credit Limit Gateway solution for WooCommerce.

Currently, WooCommerce stores Customer Address and Custom Company information very rudimentarily as single text fields, simply stored as WP_Usermeta.

## How it works

WooCommerce Companies provides User hasMany Companies whereby Customers can manage their Company relationships from their My Account area and choose which Company is their Primary Company. Customers can choose which Company to represent at Checkout too. Companies are then related to the Order both statically (postmeta) and dynamic using the Company ID.

WooCommerce Companies provides User hasMany Addresses whereby Customers can manage their addresses from their My Account area, choose which one is their Primary Billing and Shipping address respectively. Customers can choose which Address to use at Checkout too. Addresses are then related to the Order both statically and dynamically using the Address ID.

## Features

All of the templates in the templates/ folder are overridable by including the file(s) in your theme folder.

Companies and Addresses are both made to be Objects using WP_Post (they are post types in their own right).

## Coming Soon

There are loads Actions & Filters you can hook into, we will be releasing documentation that covers this i its entirety.

## Installation

1. Upload the plugin to the **/wp-content/plugins/** directory
2. Activate the plugin through the 'Plugins' menu in WordPress

## Requirements

PHP 5.4+

Wordpress 4+

WooCommerce 2.5+

## License

[GNU General Public License v3.0](http://www.gnu.org/licenses/gpl-3.0.html)