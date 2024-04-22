<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

if ( !function_exists( 'session_unregister' ) ) // deprecated в 5.3, убранo в 5.4
{
    function session_unregister( $var ) {
        unset( $_SESSION[$var] );
        return true;
    }
}

if ( !function_exists( 'session_register' ) ) // deprecated в 5.3, убранo в 5.4
{
    function session_register( $var ) {
        $_SESSION[$var];
        return true;
    }
}

if ( !function_exists( 'session_is_registered' ) ) // deprecated в 5.3, убранo в 5.4
{
    function session_is_registered( $var ) {
        return isset( $_SESSION[$var] );
    }
}

if ( !function_exists( 'ereg' ) ) // deprecated в 5.4, убранo в 7.0
{
    function ereg( $tpl, $var ) {
        return preg_match( '/' . $tpl . '/', $var );
    }
}

if ( !function_exists( 'eregi' ) ) // deprecated в 5.4, убранo в 7.0
{
    function eregi( $tpl, $var1, $var2 ) {
        return preg_match( '/' . $tpl . '/i', $var1, $var2 );
    }
}

// For earlier versions of PHP, you can polyfill the str_contains function using the following snippet:
// based on original work from the PHP Laravel framework
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}



?>