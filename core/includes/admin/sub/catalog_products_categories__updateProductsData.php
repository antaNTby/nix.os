<?php

if ( CONF_BACKEND_SAFEMODE ) //this action is forbidden when SAFE MODE is ON
{
    Redirect( _getUrlToSubmit() . "&safemode=yes" );
}

//save changes in current category
// BEGIN изменение характеристик прямо в таблице товаров
//$data = ScanPostVariableWithId( array( "price", "enable", "left", "sort_order" ) );
$data = ScanPostVariableWithId( array( "price", "enable", "left", "sort_order", "type", "value" ) );
if ( isset( $_GET["optionID"] ) ) {
    $_SESSION['optionID'] = $_GET['optionID'];
}
// END изменение характеристик прямо в таблице товаров

foreach ( $data as $key => $val ) {
    # BEGIN изменение характеристик прямо в таблице товаров
    if ( isset( $val['type'] ) && isset( $_GET['optionID'] ) ) {
        if ( $val['type'] == 0 ) {
            db_query( "DELETE FROM " . PRODUCTS_OPTIONS_SET_TABLE . " WHERE productID=$key AND optionID=" . $_GET['optionID'] );
            db_query( "DELETE FROM " . PRODUCT_OPTIONS_VALUES_TABLE . " WHERE productID=$key AND optionID=" . $_GET['optionID'] );
        } elseif ( $val['type'] == 1 ) {
            db_query( "DELETE FROM " . PRODUCTS_OPTIONS_SET_TABLE . " WHERE productID=$key AND optionID=" . $_GET['optionID'] );
            if ( mysql_result( mysql_query( "SELECT COUNT(*) FROM " . PRODUCT_OPTIONS_VALUES_TABLE . " WHERE productID=$key AND optionID=" . $_GET['optionID'] ), 0 ) ) {
                db_query( "UPDATE " . PRODUCT_OPTIONS_VALUES_TABLE . " SET option_type=0,option_value='" . xToText( $val['value'] ) . "' WHERE productID=$key AND optionID=" . $_GET['optionID'] );
            } else {
                db_query( "INSERT INTO " . PRODUCT_OPTIONS_VALUES_TABLE . " SET optionID=" . $_GET['optionID'] . ", productID=$key, option_type=0, option_value='" . xToText( $val['value'] ) . "'" );
            }

        } elseif ( $val['type'] >= 2 ) {
            if ( mysql_result( mysql_query( "SELECT COUNT(*) FROM " . PRODUCT_OPTIONS_VALUES_TABLE . " WHERE productID=$key AND optionID=" . $_GET['optionID'] ), 0 ) ) {
                db_query( "UPDATE " . PRODUCT_OPTIONS_VALUES_TABLE . " SET option_type=1 WHERE productID=$key AND optionID=" . $_GET['optionID'] );
            } else {
                db_query( "INSERT INTO " . PRODUCT_OPTIONS_VALUES_TABLE . " SET optionID=" . $_GET['optionID'] . ", productID=$key, option_type=1" );
            }

            if ( isset( $val['value'] ) ) {
                db_query( "DELETE FROM " . PRODUCTS_OPTIONS_SET_TABLE . " WHERE productID=$key AND optionID=" . $_GET['optionID'] . " AND variantID NOT IN(" . implode( ',', $val['value'] ) . ")" );
                foreach ( $val['value'] as $val1 ) {
                    if ( $val1 != 0 && !mysql_result( mysql_query( "SELECT COUNT(*) FROM " . PRODUCTS_OPTIONS_SET_TABLE . " WHERE productID=$key AND optionID=" . $_GET['optionID'] . " AND variantID=$val1" ), 0 ) ) {
                        db_query( "INSERT INTO " . PRODUCTS_OPTIONS_SET_TABLE . " SET optionID=" . $_GET['optionID'] . ", productID=$key, variantID=$val1" );
                    }
                }

            } else {
                db_query( "DELETE FROM " . PRODUCTS_OPTIONS_SET_TABLE . " WHERE productID=$key AND optionID=" . $_GET['optionID'] );
            }

        }
    }
    ## END изменение характеристик прямо в таблице товаров
    if ( isset( $val["price"] ) ) {
        $temp = $val["price"];
        $temp = round( $temp * 100 ) / 100;
        db_query( "UPDATE " . PRODUCTS_TABLE . " SET Price=" . (double)$temp . " " .
            " WHERE productID=" . (int)$key );
    }

    if ( isset( $val["enable"] ) ) {
        db_query( "update " . PRODUCTS_TABLE .
            " set enabled=" . (int)$val["enable"] . " " .
            " WHERE productID=" . (int)$key );
    }

    if ( isset( $val["left"] ) ) {
        db_query( "UPDATE " . PRODUCTS_TABLE .
            " SET in_stock = " . (int)$val["left"] . " WHERE productID=" . (int)$key );
    }

    if ( isset( $val["sort_order"] ) ) {
        db_query( "UPDATE " . PRODUCTS_TABLE .
            " SET sort_order = " . (int)$val["sort_order"] . " WHERE productID=" . (int)$key );
    }
}

if ( CONF_UPDATE_GCV == '1' ) {
    update_psCount( 1 );
}

Redirect( _getUrlToSubmit() );

?>