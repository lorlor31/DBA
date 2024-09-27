=== Woocommerce Marketplace Cart ===
Contributors: OneTeamSoftware
Tags: woocommerce, amazon, shopify, ebay, ecommerce, marketplace, cart, order, packages, warehouses, woocommerce cart, woocommerce cart packages, shipping packages, order packages, woocommerce shipping packages, multiple packages, woocommerce multiple packages, amazon cart, shopify cart, ebay cart, marketplace cart, marketplace packages, multiple warehouses
Text Domain: wc-marketplace-cart
Requires at least: 5.0
Tested up to: 6.6
Stable tag: 1.1.8
Requires PHP: 7.3
Copyright: © 2024 FlexRC, 604-1097 View St, V8V 0G9, Canada. Voice 604 800-7879 
License: Any usage / copying / extension or modification without prior authorization is prohibited

Redesigns Cart, Checkout and Order Review pages to show contents grouped into packages with shipping method selection under each package to offer similar to Amazon and eBay shopping experience.

== Description ==
If your WooCommerce store or marketplace (similar to eBay and Amazon) is selling products that have to be shipped from different locations or by different shipping methods then default implementation of WooCommerce Cart might create a lot of confusion to the customers.

**Woocommerce Marketplace Cart** aims to improve customer experience and follows proven design of Amazon, eBay and Shopify by reimplementing cart, checkout and review order pages to display cart contents grouped into packages (visually separate containers) with shipping method selection under them as well as moving Totals into a separate container on the side.

**Woocommerce Marketplace Cart** has responsive design which will work on Desktop and Mobile screens of any size.

Please install our free [WooCommerce Shipping Packages plugin](https://wordpress.org/plugins/wc-shipping-packages) in order to take advantage of **Shipping Packages** feature.

== Features of Woocommerce Marketplace Cart ==
* Redesigns Cart, Checkout pages.
* Groups cart contents into packages.
* Displays bigger product thumbnails.
* Ability to select shipping method for each package.
* Shows total for shipping and handling.
* Collapses Billing and Shipping address to save realestate space.
* Responsive design / style to properly display across various devices.

== Feedback ==
* We are open for your suggestions and feedback - Thank you for using or trying out one of our plugins!
* With any questions and requests, please feel free to contact us at: http://1teamsoftware.com/

== Installation ==
1. Make sure that you have [WooCommerce Shipping Packages plugin](https://wordpress.org/plugins/wc-shipping-packages) already installed
2. Upload **Woocommerce Marketplace Cart** to the "/wp-content/plugins/" directory.
3. Activate the plugin through the "Plugins" menu in WordPress.
4. Installation complete

== Configuration ==
It does not require any configuration

== Screenshots ==
1. Example of Cart page
2. Example of Checkout page

== Changelog ==
= 1.1.8 =
* Updated compatibility
= 1.1.7 =
* Fixed edge case fatal error.
= 1.1.6 =
* Improved efficiency of license key handling.
= 1.1.5 =
* Updated compatibility
= 1.1.4 =
* Updated compatibility
= 1.1.3 =
* Updated compatibility
= 1.1.2 =
* Updated compatibility
= 1.1.1 =
* Minor style changes on checkout page to work with Riode theme
* Fixed typo in the settings of the plugin
= 1.1.0 =
* Changed structure of the files
* Changed detection of cart and checkout pages
* Removed border from TD elements
= 1.0.36 =
* Fixed PHP warning when package_name filter is called for invalid package array
= 1.0.35 =
* Style improvements for Besa theme
= 1.0.34 =
* Change text had to be printed via translation function
* Make sure that Shipping to a different checkbox is visible
= 1.0.33 =
* Improved CSS support for Besa theme
* Included language template (.pot) file
* Included Dutch language (.po) file
* Changed everything to use the single wc-marketplace-cart text domain
= 1.0.32 = 
* Improved compatibility with Besa theme
* Improved compatibility with the latest WooCommerce
= 1.0.31 =
* Allow to change the domain of the license by deactivating plugin on the old domain and activating it on the new domain
= 1.0.30 =
* Do not display empty packages
= 1.0.29 =
* Updated to include logo with the plugin
= 1.0.28 =
* Added OneTeamSoftware menu
= 1.0.27 =
* Fixed shipping method selection when one of the packages is unchecked
= 1.0.26 =
* Fixed custom package name issue
* Implemented support for Shipping Method per CART, it will display single shipping method selection
* Modified templates to support single shipping method selection
= 1.0.25 =
* Extended templates to allow further extension
* Fixed default styling of thumbnail column header
* Display cart template even when it is empty if we have packages set
= 1.0.24 =
* Fixed totals labels for mobile view of flatsome theme
* Added support for auto refresh on quantity change option in flatsome theme
= 1.0.23 =
* Ability to hide Coupon field by choosing Do Not Display option in the location setting
* Ability to enable / disable Cart and Checkout layout customizations. Disabling will fallback to default theme design.
= 1.0.22 =
* Improved handling of thumbnails
= 1.0.21 =
* Fixed: Incorrect method has been called
* Fixed: translation file
= 1.0.20 =
* Removed translation function over html template
= 1.0.19 =
* Added styles for checkout page
* Carry payment-method template so we can restore correct behavior of checkout page in vendify template
= 1.0.18 =
* Fixed the self-duplication issue of square payment gateway fields
= 1.0.17 =
* Fixed PHP notices
* Ability to control visibility of the contents before/after cart and checkout forms
* We have to completely overwrite checkout form for Vendify theme
= 1.0.16 =
* Unified main containers classes to woocommerce-cart-form, woocommerce-cart-packages, shipping-methods and woocommerce-cart-totals for both CART and CHECKOUT pages
* Added data-title attributes to CHECKOUT totals table columns
* Added Subtotal row under contents of the package on CHECKOUT page
* Added multiple and single classes to shipping-method-selection-title, so we can a better styling flexibility
* Added ability to display single shipping method on the same line
* Added colon after Shipping Method selection text
* Added ability to change width, text alignment, font weight, padding and margin to the majority of the CART and CHECKOUT cells
* Added ability to define styles for Desktop and Mobile devices that will be based on the screen width > 979px
= 1.0.15 =
* Added ability to control position and visibility of order notes
* Use flex container for items and totals columns
* improved updating of notices when quantity is changed or other cart  events are triggered
* Ability to resize cart and checkout images
* Ability to change width of cart and checkout columns
* Ability to provide custom css
= 1.0.14 =
* Update cart button should be in the same containing element with the coupon field
= 1.0.13 =
* Fixed the issue that when coupon is applied WooCommerce extracted all the notices and moved them to the top
= 1.0.12 =
* Ability to choose location of cart notice
* Ability to display cart notice on checkout page
* Package notice with ability to choose its location and where it will be displayed
* Improved resposivness 
= 1.0.11 =
* CSS changes to improve support of Flatsome and Electro themes
* Fixed thumbnail link on checkout page
= 1.0.10 =
* Ability to choose location of shipping method selection
* Support for Shipping Method Per Cart
* Custom package name
= 1.0.9 =
* Fixed shipping method selection in the cart
* Product thumbnails in the cart and checkout pages should have the same size
* Use inline-flex for vertical alignment of the radio buttons
= 1.0.8 =
* Removed extra space between rows when remove button is on the right
= 1.0.7 =
* Bug fixes
= 1.0.6 =
* Ability to choose position of cart remove button
* Ability to set cart remove button text or default to X icon
= 1.0.5 =
* Support overwriting of the cart and checkout pages layout of wider variety of themes
= 1.0.4 =
* Fixed updater, for cases when there is no new version available
= 1.0.3 =
* Ability to customize cart notice
* Ability to customize package title
= 1.0.2 =
* Adds support for packages with virtual products
* Display policy text and payment method info before payment button
* Ability to choose location of the payment button
* Ability to choose location of the coupon field
* Removed terms template in favour of original one
* Removed version from the script and css
= 1.0 =
* Initial release.

