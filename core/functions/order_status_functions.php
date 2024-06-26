<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

// *****************************************************************************
// Purpose        Insert prdefined order status into ORDER_STATUES_TABLE.
//                                This status correspondes to completed orders.
// Inputs
// Remarks        this function is called in CreateTablesStructureXML
// Returns        nothing
function ostInstall() {
    db_query( "INSERT into " . ORDER_STATUES_TABLE .
        " ( status_name, sort_order ) " .
        " VALUES( '" . STRING_CANCELED_ORDER_STATUS . "', 0 ) " );
}

// *****************************************************************************
// Purpose        gets status id corresponded to canceled order
// Inputs
// Remarks
// Returns        nothing
function ostGetCanceledStatusId() {
    return 1;
}

function ostGetActiveStatusById( $orderID ) {
    $sql="SELECT statusID FROM " .
        ORDERS_TABLE . " WHERE orderID=" . (int)$orderID ;
    $q = db_query( $sql);
    $order    = db_fetch_row( $q );
    $statusID = $order["statusID"];

    if ( $statusID == CONF_ACTIVE_ORDER_STATUS ) {
        return true;
    } else {
        return false;
    }

}

// *****************************************************************************
// Purpose        if order status is status of canceled order
// Inputs
// Remarks
// Returns        nothing
function _correctOrderStatusName( &$orderStatus ) {
    if ( $orderStatus["statusID"] == ostGetCanceledStatusId() ) {
        $orderStatus["status_name"] = STRING_CANCELED_ORDER_STATUS;
    }

}

// *****************************************************************************
// Purpose        get any status that differents from status with $statusID ID
// Inputs
//                                $statusID - status ID
// Remarks
// Returns        item
//                                "statusID"                - status ID
//                                "status_name"        - status name
//                                "sort_order"        - status order
function ostGetOtherStatus( $statusID ) {
    $q = db_query( "SELECT statusID, status_name, sort_order from " .
        ORDER_STATUES_TABLE . " where statusID!=" . (int)$statusID .
        " AND statusID!=" . (int)ostGetCanceledStatusId() );
    if ( $row = db_fetch_row( $q ) ) {
        _correctOrderStatusName( $row );
        return $row;
    } else {
        return false;
    }

}

// *****************************************************************************
// Purpose        get status ID corresponded to new order
// Inputs
// Remarks
// Returns  status ID
function ostGetNewOrderStatus() {
    if ( defined( "CONF_NEW_ORDER_STATUS" ) ) {
        $begin_status = CONF_NEW_ORDER_STATUS;
        $q            = db_query( "SELECT count(*) FROM " . ORDER_STATUES_TABLE .
            " WHERE statusID=" . (int)$begin_status );
        $row = db_fetch_row( $q );
        if ( $row[0] ) {
            return $begin_status;
        } else {
            return null;
        }

    }
    return null;
}

// *****************************************************************************
// Purpose        get status name ID corresponded to status ID
// Inputs
//                        $statusID - status ID
// Remarks
// Returns  status ID
function ostGetOrderStatusName( $statusID ) {
    $q = db_query( "SELECT status_name FROM " . ORDER_STATUES_TABLE .
        " WHERE statusID=" . (int)$statusID );
    $row = db_fetch_row( $q );
    if ( $statusID == ostGetCanceledStatusId() ) {
        $row["status_name"] = STRING_CANCELED_ORDER_STATUS;
    }

    return $row["status_name"];
}

// *****************************************************************************
// Purpose        get status ID corresponded to comleted order
// Inputs
// Remarks
// Returns  status ID
function ostGetCompletedOrderStatus() {
    if ( defined( "CONF_COMPLETED_ORDER_STATUS" ) ) {
        $end_status = CONF_COMPLETED_ORDER_STATUS;
        $q          = db_query( "SELECT count(*) FROM " . ORDER_STATUES_TABLE .
            " WHERE statusID=" . (int)$end_status );
        $row = db_fetch_row( $q );
        if ( $row[0] ) {
            return $end_status;
        } else {
            return null;
        }

    }
    return null;
}

// *****************************************************************************
// Purpose        get all order statuses
// Inputs
// Remarks
// Returns        item
//                                "statusID"                - status ID
//                                "status_name"        - status name
//                                "sort_order"        - status order
function ostGetOrderStatuses( $fullList = true, $format = 'just' ) {
    $data = array();
    if ( $fullList ) {
        $q = db_query( "SELECT statusID, status_name, sort_order FROM " .
            ORDER_STATUES_TABLE . " WHERE statusID=" . (int)ostGetCanceledStatusId() );
        $row = db_fetch_row( $q );

        $r = array( "statusID" => $row["statusID"],
            "status_name"         => $row["status_name"],
            "sort_order"          => $row["sort_order"] );
        _correctOrderStatusName( $r );
        $data[] = $r;
    }

    $q = db_query( "SELECT statusID, status_name, sort_order FROM " .
        ORDER_STATUES_TABLE . " WHERE statusID!=" . (int)ostGetCanceledStatusId() .
        " order by sort_order " );
    while ( $r = db_fetch_row( $q ) ) {
        $data[] = $r;
    }
// debugfile($data,"ordersatatus");
    return $data;
}

// *****************************************************************************
// Purpose        add order status
// Inputs
// Remarks
// Returns  status ID
function ostAddOrderStatus( $name, $sort_order ) {
    db_query( "INSERT INTO " . ORDER_STATUES_TABLE . "(status_name, sort_order) " .
        "VALUES( '" . xToText( $name ) . "', " . (int)$sort_order . " )" );
    return db_insert_id();
}

// *****************************************************************************
// Purpose        update order status
// Inputs
// Remarks
// Returns  status ID
function ostUpdateOrderStatus( $statusID, $status_name, $sort_order ) {
    db_query( "UPDATE " . ORDER_STATUES_TABLE . " SET " .
        "status_name ='" . xToText( $status_name ) . "', " .
        "sort_order  = " . (int)$sort_order .
        " WHERE statusID=" . (int)$statusID );
}

// *****************************************************************************
// Purpose        delete order status
// Inputs
// Remarks
// Returns  status ID
function ostDeleteOrderStatus( $statusID ) {
    $q = db_query( "select count(*) from " . ORDERS_TABLE . " where statusID=" . (int)$statusID );
    $r = db_fetch_row( $q );
    if ( $r[0] != 0 ) {
        return false;
    }

    db_query( "delete from " . ORDER_STATUES_TABLE . " where statusID=" . (int)$statusID );
    return true;
}

function _changeIn_stock( $orderID, $increase ) {
    if ( !CONF_CHECKSTOCK ) {
        return;
    }

    $q = db_query( "select itemID, Quantity from " . ORDERED_CARTS_TABLE .
        " where enabled=1 AND orderID=" . (int)$orderID );
    while ( $item = db_fetch_row( $q ) ) {
        $Quantity = $item["Quantity"];
        $q1       = db_query( "select productID from " . SHOPPING_CART_ITEMS_TABLE .
            " where itemID=" . (int)$item["itemID"] );
        $product = db_fetch_row( $q1 );
        if ( $product["productID"] != null &&
            trim( $product["productID"] ) != "" ) {
            if ( $increase ) {
                db_query( "update " . PRODUCTS_TABLE . " set in_stock=in_stock + " . (int)$Quantity .
                    " where productID=" . (int)$product["productID"] );
            } else {
                db_query( "update " . PRODUCTS_TABLE . " set in_stock=in_stock - " . (int)$Quantity .
                    " where productID=" . (int)$product["productID"] );

            }

        }
    }
}

function _changeSOLD_counter( $orderID, $increase ) {
    $q = db_query( "select itemID, Quantity from " . ORDERED_CARTS_TABLE .
        " where enabled=1 AND orderID=" . (int)$orderID );
    while ( $item = db_fetch_row( $q ) ) {
        $Quantity = $item["Quantity"];
        $q1       = db_query( "select productID from " . SHOPPING_CART_ITEMS_TABLE .
            " where itemID=" . (int)$item["itemID"] );
        $product = db_fetch_row( $q1 );
        if ( $product["productID"] != null &&
            trim( $product["productID"] ) != "" ) {
            if ( $increase ) {
                db_query( "update " . PRODUCTS_TABLE . " set items_sold=items_sold + " . (int)$Quantity .
                    " where productID=" . (int)$product["productID"] );
            } else {
                db_query( "update " . PRODUCTS_TABLE . " set items_sold=items_sold - " . (int)$Quantity .
                    " where productID=" . (int)$product["productID"] );
            }
        }
    }
}

// *****************************************************************************
// Purpose        set order status to order
// Inputs
// Remarks
// Returns  status ID
function ostSetOrderStatusToOrder( $orderID, $statusID, $comment = '', $notify = 0 ) {
    $q1            = db_query( "SELECT statusID FROM " . ORDERS_TABLE . " WHERE orderID=" . (int)$orderID );
    $row           = db_fetch_row( $q1 );
    $pred_statusID = $row["statusID"];

    if ( (int)$pred_statusID == (int)$statusID ) {
        return;
    }

    db_query( "UPDATE " . ORDERS_TABLE . " SET statusID=" . (int)$statusID . " WHERE orderID=" . (int)$orderID );

    // if ( $statusID == CONF_COMPLETED_ORDER_STATUS ) {
    //     affp_addCommissionFromOrder( $orderID );
    // }

    //update product 'in stock' quantity
    if ( $pred_statusID != ostGetCanceledStatusId() &&
        $statusID == ostGetCanceledStatusId() ) {
        _changeIn_stock( $orderID, true );
    } elseif (
        $pred_statusID == ostGetCanceledStatusId() &&
        $statusID != ostGetCanceledStatusId() ) {
        _changeIn_stock( $orderID, false );
    }

    //update sold counter
    if ( $pred_statusID != CONF_COMPLETED_ORDER_STATUS &&
        $statusID == CONF_COMPLETED_ORDER_STATUS ) {
        _changeSOLD_counter( $orderID, true );
    } elseif (
        $pred_statusID == CONF_COMPLETED_ORDER_STATUS &&
        $statusID != CONF_COMPLETED_ORDER_STATUS ) {
        _changeSOLD_counter( $orderID, false );
    }
    stChangeOrderStatus( $orderID, $statusID, $comment, $notify );
}

?>