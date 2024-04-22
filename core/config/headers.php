<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

    // if (!isset ( $_GET["do"] ) ||  in_array($_GET["do"],array("cart","invoice_jur","invoice_phys","configurator","wishcat","wishlist","wishprod"))) {
    if (!isset ( $_GET["do"] ) ||  in_array($_GET["do"],array("cart","invoice_jur","invoice_phys","configurator","wishcat","wishlist","wishprod","price","ajaxfilter"))) {
        if (@extension_loaded('zlib') && CONF_USE_GZIP) {
        @ini_set('zlib.output_compression_level', 9);
        // ob_end_flush();
        ob_start('ob_gzhandler');
        }

        // setlocale(LC_ALL, "ru_RU.CP1251");
        // setlocale(LC_TIME, 'ru_RU.CP1251');

        header ("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
        header ("Content-Type: text/html; charset=".DEFAULT_CHARSET_HTML);
    }
?>