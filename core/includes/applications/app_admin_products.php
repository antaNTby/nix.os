<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-09-11       #
#          http://nixby.pro              #
##########################################

// $PDO_connect = array(
//     "user"    => DB_USER,
//     "pass"    => DB_PASS,
//     "db"      => DB_NAME,
//     "host"    => DB_HOST,
//     "charset" => "utf8mb3",
// );
// require "core/classes/class.adminSSP.php";

function _generateClausesForProductsTable( $params ) {

    if ( is_array( $params ) && count( $params ) ) {
                                       // yes
        _deletePercentSymbol( $params ); // как? это же массив

        $searchstring_clause = "";
        if ( isset( $params["searchstring"] ) and $params["searchstring"] != "" ) {
            $ccode = array();
            $cname = array();
            foreach ( explode( " ", trim( $params["searchstring"] ) ) as $word ) {
                if ( strlen( $word ) >= 1 ) {
                    $ccode[] = "lower(`product_code`) LIKE '%" . xToText( strtolower( $word ) ) . "%'";
                    $cname[] = "lower(`name`) LIKE '%" . xToText( strtolower( $word ) ) . "%'";
                }
            }
            $searchstring_clause = "(" . implode( " and ", $ccode ) . ")" . " OR " . "(" . implode( " and ", $cname ) . ")";
        }

        $where_clause = "";
        if ( $searchstring_clause ) {
            $where_clause .= ( $where_clause == "" ) ? "    ( {$searchstring_clause} )" : "  AND  ( {$searchstring_clause} )";
        }

    }

    return $where_clause;
}

function _generateClausesForOrderedCartTable( $params ) {

    $where_clause = "";

    if ( is_array( $params ) && count( $params ) ) {
        // yes

        $searchstring_clause = "";
        if ( isset( $params["searchstring"] ) and $params["searchstring"] != "" ) {
            $cname = array();
            foreach ( explode( " ", trim( $params["searchstring"] ) ) as $word ) {
                if ( strlen( $word ) >= 1 ) {
                    $cname[] = "lower(`name`) LIKE '%" . xToText( strtolower( $word ) ) . "%'";
                }
            }
            $searchstring_clause = "(" . implode( " and ", $cname ) . ")";
        }

        if ( $searchstring_clause ) {
            $where_clause .= ( $where_clause == "" ) ? "    ( {$searchstring_clause} )" : "  AND  ( {$searchstring_clause} )";
        }

    }

    return $where_clause;
}

// main
if ( isset( $_GET["app"] ) && $_GET["app"] == "app_admin_products" ) {


    $data  = json_decode( file_get_contents( "php://input" ), true, JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_IGNORE );
    $reslt = false;

    $status_name = ostGetOrderStatusName( $statusID );

    $allowed_statuses = array( 2, 3 );

    if ( isset( $_GET["operation"] ) ) {

        $operation = $_GET["operation"];

        $currencyValue = $_POST["DATA"]["currencyValue"];
        $tableID       = $_POST["DATA"]["tableID"];

        $DO_RELOAD   = false;
        $whereAll    = "";
        $whereResult = "";

        if ( isset( $_POST["DATA"]["params"] ) && count( $_POST["DATA"]["params"] ) ) {
            $params      = $_POST["DATA"]["params"];
            $whereResult = ( $tableID == 1 )
            ? _generateClausesForProductsTable( $params )
            : _generateClausesForOrderedCartTable( $params );
        }

        switch ( $operation ) {

            case "getDataToInsertItemToOrderedCart":

                $res = array();

                switch ( $tableID ) {
                    case 0:
                        $ssp_table  = ORDERED_CARTS_TABLE;
                        $primaryKey = "itemID";

                        $DT_columns = array(
                            array( "db" => "itemID", "dt" => "id" ),
                            array( "db" => "orderID", "dt" => "from" ),
                            array( "db" => "name", "dt" => "name" ),
                            array( "db" => "Price", "dt" => "price" ),
                        );

                        $res = array();
                        $res = adminSSP::complex( $_POST, $PDO_connect, $ssp_table, $primaryKey, $DT_columns, $whereResult, $whereAll ); #1
                        jlog($res);

                        for ( $ii = 0; $ii < count( $res["data"] ); $ii++ ) {
                            // code...
                            $res["data"][$ii]["category"]     = "Заказанные товары";
                            $res["data"][$ii]["priceOUT"]     = _formatPrice( $res["data"][$ii]["price"] * $currencyValue );
                            $res["data"][$ii]["price"]        = _formatPrice( $res["data"][$ii]["price"] );
                            $res["data"][$ii]["from"]         = $res["data"][$ii]["from"];
                            $res["data"][$ii]["product_code"] = "";
                        }
                        break;

                    case 1:
                        $ssp_table  = PRODUCTS_TABLE;
                        $primaryKey = "productID";

                        $DT_columns = array(
                            array( "db" => "productID", "dt" => "id" ),
                            array( "db" => "categoryID", "dt" => "from" ),
                            array( "db" => "name", "dt" => "name" ),
                            array( "db" => "Price", "dt" => "price" ),
                            array( "db" => "product_code", "dt" => "product_code" ),
                        );

                        $res = array();
                        $res = adminSSP::complex( $_POST, $PDO_connect, $ssp_table, $primaryKey, $DT_columns, $whereResult, $whereAll ); //

                        for ( $ii = 0; $ii < count( $res["data"] ); $ii++ ) {
                            $res["data"][$ii]["category"]     = catname( (int)$res["data"][$ii]["from"] );
                            $res["data"][$ii]["priceOUT"]     = _formatPrice( $res["data"][$ii]["price"] * $currencyValue );
                            $res["data"][$ii]["price"]        = _formatPrice( $res["data"][$ii]["price"] );
                            $res["data"][$ii]["product_code"] = "#" . $res["data"][$ii]["product_code"];
                        }
                        break;

                    default:
                        $ssp_table  = ORDERED_CARTS_TABLE;
                        $primaryKey = "itemID";

                        $DT_columns = array(
                            array( "db" => "itemID", "dt" => "id" ),
                            array( "db" => "orderID", "dt" => "from" ),
                            array( "db" => "name", "dt" => "name" ),
                            array( "db" => "Price", "dt" => "price" ),
                        );

//
                        $res = array();
                        $res = adminSSP::complex( $_POST, $PDO_connect, $ssp_table, $primaryKey, $DT_columns, $whereResult, $whereAll ); //

                        for ( $ii = 0; $ii < count( $res["data"] ); $ii++ ) {
                            // code...
                            $res["data"][$ii]["category"] = "Заказанные товары";
                            $res["data"][$ii]["priceOUT"] = _formatPrice( $res["data"][$ii]["price"] * $currencyValue );
                            $res["data"][$ii]["price"]    = _formatPrice( $res["data"][$ii]["price"] );
                            $res["data"][$ii]["from"]     = "#" . $res["data"][$ii]["from"];
                        }
                        break;
                }

                $operation_result = $res;
                header( "Content-Type: application/json; charset=utf-8" );
                die( json_encode( $operation_result ) );
                break;

        }

        // На основе успешной или не успешной отправки сообщаем SUCCESS или FAILED
        // !!! Больше никакого вывода из данного файла быть не должно
        // Никаких распечаток через echo, print_r и т.п. !!!
        // if ( !$DO_RELOAD ) {
        //     if ( $result ) {
        //         echo "SUCCESS";
        //     } else {
        //         echo "FAILED";
        //     }
        // } else {
        //     echo $urlToReload;
        // }

    }

}
; /* if ( isset( $_GET["app"] ) && $_GET["app"] == "app_admin_products" ) */

?>