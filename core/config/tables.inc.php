<?php

## ant
if ( !defined( 'INVOICES_TABLE' ) ) {
    define( 'INVOICES_TABLE', 'UTF_invoices' );
}
if ( !defined( 'CONTRACTS_TABLE' ) ) {
    define( 'CONTRACTS_TABLE', 'UTF_contracts' );
}
if ( !defined( 'COMPANIES_TABLE' ) ) {
    define( 'COMPANIES_TABLE', 'UTF_companies' );
}
if ( !defined( 'NANO_INVOICES_TABLE' ) ) {
    define( 'NANO_INVOICES_TABLE', 'NANO_invoices' );
}
## end ant

if ( !defined( 'SYSTEM_TABLE' ) ) {
    define( 'SYSTEM_TABLE', 'UTF_system' );
}
if ( !defined( 'SESSION_TABLE' ) ) {
    define( 'SESSION_TABLE', 'UTF_session' );
}
if ( !defined( 'BLOCKS_TABLE' ) ) {
    define( 'BLOCKS_TABLE', 'UTF_blocks' );
}
if ( !defined( 'RELATED_CONTENT_CAT_TABLE' ) ) {
    define( 'RELATED_CONTENT_CAT_TABLE', 'UTF_related_content_cat' );
}
if ( !defined( 'RELATED_CONTENT_TABLE' ) ) {
    define( 'RELATED_CONTENT_TABLE', 'UTF_related_content' );
}
if ( !defined( 'ONLINE_TABLE' ) ) {
    define( 'ONLINE_TABLE', 'UTF_online' );
}
if ( !defined( 'SURVEY_TABLE' ) ) {
    define( 'SURVEY_TABLE', 'UTF_survey' );
}
if ( !defined( 'ERROR_LOG_TABLE' ) ) {
    define( 'ERROR_LOG_TABLE', 'UTF_error_log' );
}
if ( !defined( 'MYSQL_ERROR_LOG_TABLE' ) ) {
    define( 'MYSQL_ERROR_LOG_TABLE', 'UTF_mysql_error_log' );
}
if ( !defined( 'COUNTER_TABLE' ) ) {
    define( 'COUNTER_TABLE', 'UTF_counter' );
}
if ( !defined( 'DUMP_TABLE' ) ) {
    define( 'DUMP_TABLE', 'UTF_dump' );
}
if ( !defined( 'ORDERS_TABLE' ) ) {
    define( 'ORDERS_TABLE', 'UTF_orders' );
}

if ( !defined( 'ORDER_STATUES_TABLE' ) ) {
    define( 'ORDER_STATUES_TABLE', 'UTF_order_status' );
}
if ( !defined( 'ORDERED_CARTS_TABLE' ) ) {
    define( 'ORDERED_CARTS_TABLE', 'UTF_ordered_carts' );
}
if ( !defined( 'PRODUCTS_TABLE' ) ) {
    define( 'PRODUCTS_TABLE', 'UTF_products' );
}
if ( !defined( 'CATEGORIES_TABLE' ) ) {
    define( 'CATEGORIES_TABLE', 'UTF_categories' );
}
if ( !defined( 'GOODS_TABLE__NIXRU' ) ) {
    define( 'GOODS_TABLE__NIXRU', 'PRICE_Products__nixru' );
}
if ( !defined( 'GROUPS_TABLE__NIXRU' ) ) {
    define( 'GROUPS_TABLE__NIXRU', 'PRICE_Groups' );
}
if ( !defined( 'CATEGORIY_PRODUCT_TABLE' ) ) {
    define( 'CATEGORIY_PRODUCT_TABLE', 'UTF_category_product' );
}
if ( !defined( 'SHOPPING_CARTS_TABLE' ) ) {
    define( 'SHOPPING_CARTS_TABLE', 'UTF_shopping_carts' );
}
if ( !defined( 'NEWS_TABLE' ) ) {
    define( 'NEWS_TABLE', 'UTF_news_table' );
}
if ( !defined( 'DISCUSSIONS_TABLE' ) ) {
    define( 'DISCUSSIONS_TABLE', 'UTF_discussions' );
}
if ( !defined( 'MAILING_LIST_TABLE' ) ) {
    define( 'MAILING_LIST_TABLE', 'UTF_subscribers' );
}
if ( !defined( 'RELATED_PRODUCTS_TABLE' ) ) {
    define( 'RELATED_PRODUCTS_TABLE', 'UTF_related_items' );
}
if ( !defined( 'PRODUCT_OPTIONS_TABLE' ) ) {
    define( 'PRODUCT_OPTIONS_TABLE', 'UTF_product_options' );
}
if ( !defined( 'PRODUCT_OPTIONS_VALUES_TABLE' ) ) {
    define( 'PRODUCT_OPTIONS_VALUES_TABLE', 'UTF_product_options_values' );
}
if ( !defined( 'PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE' ) ) {
    define( 'PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE', 'UTF_products_opt_val_variants' );
}
if ( !defined( 'PRODUCTS_OPTIONS_SET_TABLE' ) ) {
    define( 'PRODUCTS_OPTIONS_SET_TABLE', 'UTF_product_options_set' );
}
if ( !defined( 'CUSTOMERS_TABLE' ) ) {
    define( 'CUSTOMERS_TABLE', 'UTF_customers' );
}
if ( !defined( 'CUSTGROUPS_TABLE' ) ) {
    define( 'CUSTGROUPS_TABLE', 'UTF_custgroups' );
}
if ( !defined( 'COUNTRIES_TABLE' ) ) {
    define( 'COUNTRIES_TABLE', 'UTF_countries' );
}
if ( !defined( 'ZONES_TABLE' ) ) {
    define( 'ZONES_TABLE', 'UTF_zones' );
}
if ( !defined( 'CUSTOMER_LOG_TABLE' ) ) {
    define( 'CUSTOMER_LOG_TABLE', 'UTF_customer_log' );
}
if ( !defined( 'CUSTOMER_ADDRESSES_TABLE' ) ) {
    define( 'CUSTOMER_ADDRESSES_TABLE', 'UTF_customer_addresses' );
}
if ( !defined( 'CUSTOMER_REG_FIELDS_TABLE' ) ) {
    define( 'CUSTOMER_REG_FIELDS_TABLE', 'UTF_customer_reg_fields' );
}
if ( !defined( 'CUSTOMER_REG_FIELDS_VALUES_TABLE' ) ) {
    define( 'CUSTOMER_REG_FIELDS_VALUES_TABLE', 'UTF_customer_reg_fields_values' );
}
if ( !defined( 'CUSTOMER_REG_FIELDS_VALUES_TABLE_QUICKREG' ) ) {
    define( 'CUSTOMER_REG_FIELDS_VALUES_TABLE_QUICKREG', 'UTF_customer_reg_fields_values_quickreg' );
}
if ( !defined( 'SHIPPING_METHODS_TABLE' ) ) {
    define( 'SHIPPING_METHODS_TABLE', 'UTF_shipping_methods' );
}
if ( !defined( 'PAYMENT_TYPES_TABLE' ) ) {
    define( 'PAYMENT_TYPES_TABLE', 'UTF_payment_types' );
}
if ( !defined( 'SHIPPING_METHODS_PAYMENT_TYPES_TABLE' ) ) {
    define( 'SHIPPING_METHODS_PAYMENT_TYPES_TABLE', 'UTF_payment_types__shipping_methods' );
}
if ( !defined( 'CURRENCY_TYPES_TABLE' ) ) {
    define( 'CURRENCY_TYPES_TABLE', 'UTF_currency_types' );
}
if ( !defined( 'SPECIAL_OFFERS_TABLE' ) ) {
    define( 'SPECIAL_OFFERS_TABLE', 'UTF_special_offers' );
}
if ( !defined( 'SHOPPING_CART_ITEMS_TABLE' ) ) {
    define( 'SHOPPING_CART_ITEMS_TABLE', 'UTF_shopping_cart_items' );
}
if ( !defined( 'SHOPPING_CART_ITEMS_CONTENT_TABLE' ) ) {
    define( 'SHOPPING_CART_ITEMS_CONTENT_TABLE', 'UTF_shopping_cart_items_content' );
}
if ( !defined( 'PRODUCT_PICTURES' ) ) {
    define( 'PRODUCT_PICTURES', 'UTF_product_pictures' );
}
if ( !defined( 'AUX_PAGES_TABLE' ) ) {
    define( 'AUX_PAGES_TABLE', 'UTF_aux_pages' );
}
if ( !defined( 'SETTINGS_GROUPS_TABLE' ) ) {
    define( 'SETTINGS_GROUPS_TABLE', 'UTF_settings_groups' );
}
if ( !defined( 'SETTINGS_TABLE' ) ) {
    define( 'SETTINGS_TABLE', 'UTF_settings' );
}
if ( !defined( 'CATEGORY_PRODUCT_OPTIONS_TABLE' ) ) {
    define( 'CATEGORY_PRODUCT_OPTIONS_TABLE', 'UTF_category__product_options' );
}
if ( !defined( 'CATEGORY_PRODUCT_OPTION_VARIANTS' ) ) {
    define( 'CATEGORY_PRODUCT_OPTION_VARIANTS', 'UTF_category_product_options__variants' );
}
if ( !defined( 'TAX_CLASSES_TABLE' ) ) {
    define( 'TAX_CLASSES_TABLE', 'UTF_tax_classes' );
}
if ( !defined( 'TAX_RATES_TABLE' ) ) {
    define( 'TAX_RATES_TABLE', 'UTF_tax_rates' );
}
if ( !defined( 'TAX_RATES_ZONES_TABLE' ) ) {
    define( 'TAX_RATES_ZONES_TABLE', 'UTF_tax_rates__zones' );
}
if ( !defined( 'TAX_ZIP_TABLE' ) ) {
    define( 'TAX_ZIP_TABLE', 'UTF_tax_zip' );
}
if ( !defined( 'MODULES_TABLE' ) ) {
    define( 'MODULES_TABLE', 'UTF_modules' );
}
if ( !defined( 'ORDER_PRICE_DISCOUNT_TABLE' ) ) {
    define( 'ORDER_PRICE_DISCOUNT_TABLE', 'UTF_order_price_discount' );
}
if ( !defined( 'ORDER_STATUS_CHANGE_LOG_TABLE' ) ) {
    define( 'ORDER_STATUS_CHANGE_LOG_TABLE', 'UTF_order_status_changelog' );
}
if ( !defined( 'LINK_EXCHANGE_CATEGORIES_TABLE' ) ) {
    define( 'LINK_EXCHANGE_CATEGORIES_TABLE', 'UTF_linkexchange_categories' );
}
if ( !defined( 'LINK_EXCHANGE_LINKS_TABLE' ) ) {
    define( 'LINK_EXCHANGE_LINKS_TABLE', 'UTF_linkexchange_links' );
}
if ( !defined( 'AFFILIATE_COMMISSIONS_TABLE' ) ) {
    define( 'AFFILIATE_COMMISSIONS_TABLE', 'UTF_aff_commissions' );
}
if ( !defined( 'AFFILIATE_PAYMENTS_TABLE' ) ) {
    define( 'AFFILIATE_PAYMENTS_TABLE', 'UTF_aff_payments' );
}
?>