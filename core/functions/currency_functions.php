<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

// *****************************************************************************
// Purpose        sets current currency
// Inputs             nothing
// Remarks
// Returns        nothing
function currSetCurrentCurrency( $currencyID ) {
    //register current currency type in session vars
    $_SESSION["current_currency"] = (int)$currencyID;

    if ( isset( $_SESSION["log"] ) ) {
        db_query( "UPDATE " . CUSTOMERS_TABLE . " SET CID=" . (int)$currencyID .
            " WHERE Login='" . xEscSQL( $_SESSION["log"] ) . "'" );
    }
}

// *****************************************************************************
// Purpose        gets current selected by user currency unit
// Inputs             nothing
// Remarks
// Returns        currency unit ID ( see CURRENCY_TYPES_TABLE table in DataBase )
function currGetCurrentCurrencyUnitID() {

    if ( isset( $_SESSION["log"] ) ) {
        $q = db_query( "select b.CID, s.cust_password, s.CID FROM " . CUSTOMERS_TABLE .
            " AS s INNER JOIN " . CURRENCY_TYPES_TABLE . " AS b on (s.CID=b.CID) WHERE s.Login='" . xEscSQL( $_SESSION["log"] ) . "'" );
        $customerInfo                 = db_fetch_row( $q );
        $_SESSION["current_currency"] = $customerInfo["CID"];
        if ( $_SESSION["current_currency"] != null && $_SESSION["current_currency"] > 0 ) {
            return $_SESSION["current_currency"];
        }

    }

    if ( isset( $_SESSION["current_currency"] ) ) {
        $q                            = db_query( "select currency_value FROM " . CURRENCY_TYPES_TABLE . " WHERE CID=" . (int)$_SESSION["current_currency"] );
        $customerInfo                 = db_fetch_row( $q );
        $_SESSION["current_currency"] = $customerInfo["CID"];
        if ( $_SESSION["current_currency"] != null && $_SESSION["current_currency"] > 0 ) {
            return $_SESSION["current_currency"];
        }

    }

    $q     = db_query( "select count(*) from " . CURRENCY_TYPES_TABLE . " where CID=" . (int)CONF_DEFAULT_CURRENCY );
    $count = db_fetch_row( $q );
    if ( $count[0] ) {
        return CONF_DEFAULT_CURRENCY;
    } else {
        return null;
    }

}

// *****************************************************************************
// Purpose        gets current selected by user currency unit
// Inputs             nothing
// Remarks
// Returns        currency unit ID ( see CURRENCY_TYPES_TABLE table in DataBase )
function currGetCurrencyByID( $currencyID = CONF_DEFAULT_CURRENCY ) {
    $q = db_query( "SELECT CID, Name, code, currency_value, where2show, sort_order, currency_iso_3, roundval FROM " .
        CURRENCY_TYPES_TABLE . " WHERE CID=" . (int)$currencyID );
    $row = db_fetch_row( $q );
    if ( !$row ) {
        $row = NULL;
    }
    return $row;
}

function currGetCurrencyRateByID( $currencyID = CONF_DEFAULT_CURRENCY ) {
    $q = db_query( "SELECT currency_value FROM " .
        CURRENCY_TYPES_TABLE . " WHERE CID=" . (int)$currencyID );
    $row = db_fetch_row( $q );
    if ( !$row ) {
        $row = NULL;
    }
    return (float)$row[0];
}

// *****************************************************************************
// Purpose        get all currencies
// Inputs             nothing
// Remarks
// Returns        currency array
function currGetAllCurrencies() {
    $q = db_query( "select Name, code, currency_iso_3, currency_value, where2show, CID, sort_order, roundval from " .
        CURRENCY_TYPES_TABLE . " order by sort_order" );
    $data = array();
    while ( $row = db_fetch_row( $q ) ) {
        $row["hasVATIncluded"] = 0;
        if ( $row["CID"] == 5 ) {
            $row["hasVATIncluded"] = 1;
        }

        $data[] = $row;

    }

    return $data;
}

// *****************************************************************************
// Purpose        delete currency by ID
// Inputs             CID
// Remarks
// Returns        nothing
function currDeleteCurrency( $CID ) {
    $q = db_query( "select CID from " . CURRENCY_TYPES_TABLE . " where CID!=" . (int)$CID );
    if ( $currency = db_fetch_row( $q ) ) {
        db_query( "update " . CUSTOMERS_TABLE . " set CID=" . $currency["CID"] . " where CID=" . (int)$CID );
    } else {
        db_query( "update " . CUSTOMERS_TABLE . " set CID=NULL where CID=" . (int)$CID );
    }

    db_query( "delete from " . CURRENCY_TYPES_TABLE . " where CID=" . (int)$CID );
}

// *****************************************************************************
// Purpose        update currency by ID
// Inputs             CID
// Remarks
// Returns        nothing
function currUpdateCurrency(
    $CID,
    $name,
    $code,
    $currency_iso_3,
    $value,
    $where,
    $sort_order,
    $roundval
) {
    db_query( "update " .
        CURRENCY_TYPES_TABLE .
        " set " .
        "        Name='" . xToText( trim( $name ) ) . "', " .
        "        code='" . xEscSQL( $code ) . "', " .
        "        currency_value='" . xEscSQL( trim( $value ) ) . "', " .
        "        where2show=" . (int)$where . ", " .
        "        sort_order=" . (int)$sort_order . ", " .
        "        currency_iso_3='" . xToText( trim( $currency_iso_3 ) ) . "', " .
        "        roundval=" . (int)$roundval . " " .
        " where CID=" . (int)$CID );
}

// *****************************************************************************
// Purpose        add currency by ID
// Inputs             CID
// Remarks
// Returns        nothing
function currAddCurrency(
    $name,
    $code,
    $currency_iso_3,
    $value,
    $where,
    $sort_order,
    $roundval
) {
    db_query( "INSERT INTO " . CURRENCY_TYPES_TABLE .
        " (Name, code, currency_value, where2show, sort_order, currency_iso_3, roundval) " .
        " VALUES ('" . xToText( trim( $name ) ) . "', '" . xEscSQL( $code ) . "', '" . xEscSQL( trim( $value ) ) . "', '" . (int)$where . "', '" .
        (int)$sort_order . "', '" . xToText( trim( $currency_iso_3 ) ) . "', '" . (int)$roundval . "')" );
}

function currGetCurrencyByISO3(
    $_ISO3,
    $FIELD_TO_RETURN = ""
) {

    if ( $FIELD_TO_RETURN == "" ) {
        $sql = "SELECT CID, Name, code, currency_value, where2show, sort_order, currency_iso_3 FROM " .
        CURRENCY_TYPES_TABLE . " WHERE `currency_iso_3`='" . xEscSQL( $_ISO3 ) . "' ";
    } else {
        $sql = "SELECT `{$FIELD_TO_RETURN}` FROM " .
        CURRENCY_TYPES_TABLE . " WHERE `currency_iso_3`='" . xEscSQL( $_ISO3 ) . "' ";
    }
    $q   = db_query( $sql );
    $row = db_fetch_row( $q );
    if ( !$row ) {
        $row = NULL;
        return NULL;
    }
    $result = ( $FIELD_TO_RETURN != "" ) ? $row["{$FIELD_TO_RETURN}"] : $row;

    return $result;
}

?>