<?php

// debugfile( $_POST["ADD_COMMAND"] );

$true_CMD = ( isset( $_POST["ADD_COMMAND"] ) && ( $_POST["ADD_COMMAND"] == "prod_off" || $_POST["ADD_COMMAND"] == "prod_on" || $_POST["ADD_COMMAND"] == "prod_dell" || $_POST["ADD_COMMAND"] == "prod_move" || $_POST["ADD_COMMAND"] == "change_price_selected" || $_POST["ADD_COMMAND"] == "roundpriceselected" ) );

if ( CONF_BACKEND_SAFEMODE ) //this action is forbidden when SAFE MODE is ON
{
    Redirect( _getUrlToSubmit() . "&safemode=yes" );
}

//save changes in current category
$data = ScanPostVariableWithId( array( "price", "enable", "left", "sort_order", "checkbox_products_id" ) );
// debugfile( $_POST, "POST" );
// debugfile( $data, "DATA" );
foreach ( $data as $key => $val ) {

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
    ## РАБОТА С ВЫБРАННЫМИ ОТМЕЧЕННЫМИ ПРОДУКТАМИ
    if ( isset( $val["checkbox_products_id"] ) ) {

        if ( $_POST["ADD_COMMAND"] == "prod_off" ) {
            db_query( "UPDATE " . PRODUCTS_TABLE . " SET enabled = 0 WHERE productID=" . (int)$key );
        } elseif ( $_POST["ADD_COMMAND"] == "prod_on" ) {
            db_query( "UPDATE " . PRODUCTS_TABLE . " SET enabled = 1 WHERE productID=" . (int)$key );
        } elseif ( $_POST["ADD_COMMAND"] == "prod_dell" ) {
            if ( !DeleteProduct( $key ) ) {
                Redirect( _getUrlToSubmit() . "&couldntToDelete=1" );
            }
        } elseif ( $_POST["ADD_COMMAND"] == "prod_move" ) {
            // BEGIN перенос из добавочной категории

            $prd = db_fetch_assoc( db_query( "SELECT categoryID FROM " . PRODUCTS_TABLE . " WHERE productID=" . (int)$key . " LIMIT 1" ) );
            if ( isset( $_GET['search'] ) || !isset( $_GET['categoryID'] ) || $prd['categoryID'] == (int)$_GET['categoryID'] ) // это основная категория товара (либо результат поиска, где категория у товаров разная)
            {
                db_query( "UPDATE " . PRODUCTS_TABLE . " SET categoryID = " . (int)$_POST["prod_categoryID"] . " WHERE productID=" . (int)$key );
            } else // это доп категория товара
            {
                db_query( "UPDATE " . CATEGORIY_PRODUCT_TABLE . " SET categoryID = " . (int)$_POST["prod_categoryID"] . " WHERE categoryID=" . (int)$_GET['categoryID'] . " AND productID=" . (int)$key );
            }
        } elseif ( $_POST["ADD_COMMAND"] == "change_price_selected" ) {
            // BEGIN Percentable-change
            if ( strlen( $_POST["newpriceselected"] ) > 0 and preg_match( ";^[+-]*[0-9]+[.][0-9]+|[+-]*[0-9]+$;", $_POST["newpriceselected"] ) ) {
                $new_price = trim( $_POST["newpriceselected"] );
                $catid     = $_GET["categoryID"];
                $func      = 'ROUND';
                $cc        = currGetCurrencyByID( $_SESSION["current_currency"] );
                $roundval  = isset( $cc["roundval"] ) ? $cc["roundval"] : 2;
                if ( (int)$roundval == 0 ) {
                    $func = 'TRUNCATE';
                }

                if ( $_POST["edselected"] == 2 ) {
                    $expression = "(Price*$new_price/100)";
                } elseif ( $_POST["edselected"] == 3 ) {
                    $expression = "($new_price)";
                }
                db_query( "UPDATE " . PRODUCTS_TABLE . " SET Price=" . $func . "(Price+" . $expression . ", $roundval) WHERE productID=" . (int)$key );
            }
        } // END Percentable-change
        elseif ( $_POST["ADD_COMMAND"] == "roundpriceselected" ) {
            // BEGIN RoundPrice
            db_query( $round_str . "WHERE productID=" . (int)$key );
        } //END RoundPrice
          // END перенос из добавочной категории
    } //if ( isset( $val["checkbox_products_id"] ) )
} // END FOREACH

if ( CONF_UPDATE_GCV == '1' ) {
    update_psCount( 1 );
}

Redirect( _getUrlToSubmit() );

?>