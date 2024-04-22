<?php
// custord_orders.php
// custord_orders_table.php

//orders list

// core/includes/admin/sub/custord_orders_list.php

$order_statuses = ostGetOrderStatuses();

function _setCallBackParamsToSearchOrders( &$callBackParam ) {
    if ( isset( $_GET["sort"] ) ) {
        $callBackParam["sort"] = $_GET["sort"];
    }

    if ( isset( $_GET["direction"] ) ) {
        $callBackParam["direction"] = $_GET["direction"];
    }

    if ( $_GET["order_search_type"] == "SearchByOrderID" ) {
        $callBackParam["orderID"] = (int)$_GET["orderID_textbox"];
    } elseif ( $_GET["order_search_type"] == "SearchByStatusID" ) {
        $orderStatuses = array();
        $data          = ScanGetVariableWithId( array( "checkbox_order_status" ) );
        foreach ( $data as $key => $val ) {
            if ( $val["checkbox_order_status"] == "1" ) {
                $orderStatuses[] = $key;
            }
        }

        $callBackParam["orderStatuses"] = $orderStatuses;
    }
}

function _copyDataFromGetToPage( &$smarty, &$order_statuses ) {
    if ( isset( $_GET["order_search_type"] ) ) {
        $smarty->assign( "order_search_type", $_GET["order_search_type"] );
    }

    if ( isset( $_GET["orderID_textbox"] ) ) {
        $smarty->assign( "orderID", (int)$_GET["orderID_textbox"] );
    }

    $data = ScanGetVariableWithId( array( "checkbox_order_status" ) );
    for ( $i = 0; $i < count( $order_statuses ); $i++ ) {
        $order_statuses[$i]["selected"] = 0;
    }

    foreach ( $data as $key => $val ) {
        if ( $val["checkbox_order_status"] == "1" ) {
            for ( $i = 0; $i < count( $order_statuses ); $i++ ) {
                if ( (int)$order_statuses[$i]["statusID"] == (int)$key ) {
                    $order_statuses[$i]["selected"] = 1;
                }
            }

        }
    }
}

function _getReturnUrl() {
    $url = ADMIN_FILE . "?dpt=custord&sub=orders";
    if ( isset( $_GET["order_search_type"] ) ) {
        $url .= "&order_search_type=" . $_GET["order_search_type"];
    }

    if ( isset( $_GET["orderID_textbox"] ) ) {
        $url .= "&orderID_textbox=" . $_GET["orderID_textbox"];
    }

    $data = ScanGetVariableWithId( array( "checkbox_order_status" ) );
    foreach ( $data as $key => $val ) {
        $url .= "&checkbox_order_status_" . $key . "=" . $val["checkbox_order_status"];
    }

    if ( isset( $_GET["offset"] ) ) {
        $url .= "&offset=" . $_GET["offset"];
    }

    if ( isset( $_GET["show_all"] ) ) {
        $url .= "&show_all=" . $_GET["show_all"];
    }

    $data                  = ScanGetVariableWithId( array( "set_order_status" ) );
    $changeStatusIsPressed = ( count( $data ) != 0 );
    if ( isset( $_GET["search"] ) || $changeStatusIsPressed ) {
        $url .= "&search=1";
    }

    if ( isset( $_GET["sort"] ) ) {
        $url .= "&sort=" . $_GET["sort"];
    }

    if ( isset( $_GET["direction"] ) ) {
        $url .= "&direction=" . $_GET["direction"];
    }

    return base64_encode( $url );
}

function _getUrlToNavigate() {
    $url = ADMIN_FILE . "?dpt=custord&sub=orders";
    if ( isset( $_GET["order_search_type"] ) ) {
        $url .= "&order_search_type=" . $_GET["order_search_type"];
    }

    if ( isset( $_GET["orderID_textbox"] ) ) {
        $url .= "&orderID_textbox=" . $_GET["orderID_textbox"];
    }

    $data = ScanGetVariableWithId( array( "checkbox_order_status" ) );
    foreach ( $data as $key => $val ) {
        $url .= "&checkbox_order_status_" . $key . "=" . $val["checkbox_order_status"];
    }

    $data                  = ScanGetVariableWithId( array( "set_order_status" ) );
    $changeStatusIsPressed = ( count( $data ) != 0 );

    if ( isset( $_GET["search"] ) || $changeStatusIsPressed ) {
        $url .= "&search=1";
    }

    if ( isset( $_GET["sort"] ) ) {
        $url .= "&sort=" . $_GET["sort"];
    }

    if ( isset( $_GET["direction"] ) ) {
        $url .= "&direction=" . $_GET["direction"];
    }

    return $url;
}

function _getUrlToSort() {
    $url = ADMIN_FILE . "?dpt=custord&sub=orders";
    if ( isset( $_GET["order_search_type"] ) ) {
        $url .= "&order_search_type=" . $_GET["order_search_type"];
    }

    if ( isset( $_GET["orderID_textbox"] ) ) {
        $url .= "&orderID_textbox=" . $_GET["orderID_textbox"];
    }

    $data = ScanGetVariableWithId( array( "checkbox_order_status" ) );
    foreach ( $data as $key => $val ) {
        $url .= "&checkbox_order_status_" . $key . "=" . $val["checkbox_order_status"];
    }

    if ( isset( $_GET["offset"] ) ) {
        $url .= "&offset=" . $_GET["offset"];
    }

    if ( isset( $_GET["show_all"] ) ) {
        $url .= "&show_all=" . $_GET["show_all"];
    }

    $data                  = ScanGetVariableWithId( array( "set_order_status" ) );
    $changeStatusIsPressed = ( count( $data ) != 0 );

    if ( isset( $_GET["search"] ) || $changeStatusIsPressed ) {
        $url .= "&search=1";
    }

    return $url;
}

if ( isset( $_POST["status_cpast"] ) ) {
    $dataup = ScanPostVariableWithId( array( "ordsel" ) );
    foreach ( $dataup as $key => $val ) {
        ostSetOrderStatusToOrder( (int)$key, $_POST["status_cpast"], '', '' );
    }
    $smarty->assign( "status_cpast_ok", 1 );
} else {
    $smarty->assign( "status_cpast_ok", 0 );
}

if ( isset( $_POST["orders_delete"] ) ) {
    $dataup2 = ScanPostVariableWithId( array( "ordsel" ) );
    foreach ( $dataup2 as $key => $val ) {
        ordDeleteOrder( (int)$key );
    }
    $smarty->assign( "orders_delete_ok", 1 );
} else {
    $smarty->assign( "orders_delete_ok", 0 );
}

$data                  = ScanGetVariableWithId( array( "set_order_status" ) );
$changeStatusIsPressed = ( count( $data ) != 0 );

if ( isset( $_GET["search"] ) || $changeStatusIsPressed ) {
    _copyDataFromGetToPage( $smarty, $order_statuses );

    $callBackParam = array();
    _setCallBackParamsToSearchOrders( $callBackParam );
    $orders        = array();
    $count         = 0;
    $navigatorHtml = GetNavigatorHtml( _getUrlToNavigate(), 20,
        'ordGetOrders', $callBackParam, $orders, $offset, $count );
    $smarty->assign( "orders", $orders );

    $ordersCount=ordGetCountOfOrders( $callBackParam );
    $smarty->assign( "ordersCount", $ordersCount["ordersCount"] );
    $smarty->assign( "navigator", $navigatorHtml );
}

if ( isset( $_GET["offset"] ) ) {
    $smarty->assign( "offset", $_GET["offset"] );
}

if ( isset( $_GET["show_all"] ) ) {
    $smarty->assign( "show_all", $_GET["show_all"] );
}

if ( isset( $_GET["status_del"] ) ) {

    DelOrdersBySDL( (int)$_GET["status_del"] );
    $smarty->assign( "status_del_ok", 1 );
} else {
    $smarty->assign( "status_del_ok", 0 );
}

## ant
if ( isset( $_GET["delete_single_order"] ) && isset( $_GET["killID"] ) ) {
    if ( (int)$_GET["killID"] > 0 ) {
        ostSetOrderStatusToOrder( (int)$_GET["killID"], (int)ostGetCanceledStatusId(), $comment = '', $notify = 0 );
        ordDeleteOrder( (int)$_GET["killID"] );
    }

    Redirect( base64_decode( $_GET["urlToReturn"] ) );
}

$smarty->hassign( "urlToSort", _getUrlToSort() );
$smarty->hassign( "urlToReturn", _getReturnUrl() );
$smarty->assign( "order_statuses", $order_statuses );

?>
