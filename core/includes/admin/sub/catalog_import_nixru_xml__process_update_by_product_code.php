<?php

$FIELDLIST_UPDATEPRODUCT = "name,Price,in_stock,list_price,date_modified,vendorID,weight,sort_order"; // ,description,brief_description,weight,meta_description,meta_keywords,title
$FIELDLIST_UPDATEPRODUCT = "name,Price,in_stock,list_price,date_modified";
$FIELDLIST_UPDATEPRODUCT = "name,Price,in_stock,list_price,date_modified,description,brief_description,weight,meta_description,meta_keywords,title";

$_SESSION['default_categoryID'] = 1;

if ( !$_SESSION["UPDATE_PROCESS_ACTIVE"] ) {

    $_SESSION["UPDATEPRODUCT_COUNTER"] = 0;
    $_SESSION["FUZE"]                  = 0;

    $s = "
    SELECT id AS disabled_group_id
    FROM {$GroupsTable}
    WHERE enabled=0
      AND vendorID={$vendorID} ;
     ";

    $q = db_query( $s );
    while ( $row = db_fetch_row( $q ) ) {
        $disabledGroups[] = (int)$row[0];
    }
    if ( is_array( $disabledGroups ) && count( $disabledGroups ) > 0 ) {
        $disabledGroups_list                 = implode( ",", $disabledGroups );
        $where_clause_not_in_disabled_groups = " AND parent_id NOT IN (" . $disabledGroups_list . ") ";
    } else {
        $where_clause_not_in_disabled_groups = "";
    }

    // consolelog( $where_clause_not_in_disabled_groups );
    # ствавим  всех is processed=1;
    # ствавим  всех is processed=0 для всех неопубликованных (is_published=0);
    $r1 = dbSet_is_processed( GOODS_TABLE__NIXRU, 1, $where_clauseAll );
    $r2 = dbSet_is_processed( GOODS_TABLE__NIXRU, 0, $where_clause_not_published . $where_clause_not_in_disabled_groups );

    $_SESSION["UPDATE_PROCESS_ACTIVE"] = true;
}

$_SESSION["FUZE"]++;
$s50 = "
SELECT *
FROM {$GoodsTable}
WHERE {$where_clause_not_processed}
ORDER BY id LIMIT 100;";

$data         = array();
$selected_ids = array();
$q            = db_query( $s50 );
while ( $row = db_fetch_row( $q ) ) {
    $data[]         = $row;
    $selected_ids[] = (int)$row["id"];
}

$USE_CHUNK = true;

if ( is_array( $data ) && count( $data ) > 0 ) {

    $selected_id_List     = implode( ",", $selected_ids );
    $convertedProducts    = prdConvertFromPriceToCatalog( $data, $_SESSION['default_categoryID'], "update_process_chunk" . $_SESSION["FUZE"] );
    $updatingProduct_DATA = array();

    foreach ( $convertedProducts as $key => $_product ) {
        if ( $USE_CHUNK == 1 ) {
            $chunk = "";
            // $chunk = "OPTIMIZE TABLE `" . PRODUCTS_TABLE . "`;";
            // $chunk .= "OPTIMIZE TABLE `" . GOODS_TABLE__NIXRU . "`;";
        }

        $updatingProduct_DATA              = $_product;
        $updatingProduct_DATA["currentID"] = $_product["product_code"];

        $aaaa = prdUpdateProductFieldsBySelectedData(
            "product_code",
            $_product["product_code"],
            $updatingProduct_DATA,
            $FIELDLIST_UPDATEPRODUCT,
            0,
            $USE_CHUNK
        );

        $where_clause_in_selected_list = "  `id` IN ({$selected_id_List}) ";
        if ( $USE_CHUNK ) {
            $chunk .= trim( $aaaa );
            $chunk .= ";    ";
            $chunk .= "UPDATE " . GOODS_TABLE__NIXRU . "  SET `is_processed` = 1,  `is_published` = 1 WHERE " . $where_clause_in_selected_list . "; ";
        } else {
            $_SESSION["UPDATEPRODUCT_COUNTER"]++;
            dbSet_publish_processed( GOODS_TABLE__NIXRU, 1, $where_clause_in_selected_list );
        }

    }
    if ( $USE_CHUNK && ( $chunk != "" ) ) {
        db_multiquery( $chunk, DB_HOST, DB_USER, DB_PASS, DB_NAME );
        $chunk = "";
    }

    $convertedProducts    = array();
    $updatingProduct_DATA = array();
    $data                 = array();
    $selected_ids         = array();
    $ccchaun              = mb_strlen( $chunk );
    $chunk                = "";
    // consolelog($chunk);

} else {

    $_SESSION["UPDATE_PROCESS_ACTIVE"] = false;
    $chunk                             = "";

    // update_psCount( 1 );
}

$continue_function = false;

if ( $_SESSION["UPDATE_PROCESS_ACTIVE"] ) {
    $continue_function = "process_update_by_product_code";
} else {
    $continue_function = false;
}

if ( $_SESSION["FUZE"] > 10000 ) {
    break;
}

?>