<?php
if ( !$_SESSION["INSERT_PROCESS_ACTIVE"] ) {
    $_SESSION["PASS_NUMBER"]     = 0;
    $_SESSION["PRODUCTS_NUMBER"] = 0;
    # ствавим  всех is processed=1;
    # ствавим  всех is processed=0 для всех неопубликованных (is_published=0);
    $r1 = dbSet_is_processed( GOODS_TABLE__NIXRU, 1, $where_clauseAll );

    $sql_resetAllProcesses = "
    UPDATE PRICE_Products__nixru
    SET is_processed = 0
    WHERE enabled = 1 AND is_processed != 0  AND is_published = 0;
    ";

    $q = db_query( $sql_resetAllProcesses );

    $sql_processed_to_7 = "";

    $sql_processed_to_7 .= "
    UPDATE PRICE_Products__nixru t1
    LEFT JOIN UTF_products t2 ON (t1.product_code = t2.product_code)
    AND (t1.vendorID = t2.vendorID)
    SET t1.is_processed = 7,
        t1.is_published = 0
    WHERE t1.enabled = 1
      AND t2.product_code IS NULL
      AND t1.parent_id > 0
      AND t1.parent_id NOT IN (
      SELECT t3.id AS skip_groups
      FROM PRICE_Groups t3 WHERE (t3.enabled = 0) OR (t3.categoriesID_AssocLink < 1) );
       ";
/*
WHERE ((t3.enabled = 0) OR (t3.categoriesID_AssocLink < 1)
)
AND (t3.vendorID = {$vendorID})) ;
 */

    $q                                 = db_query( $sql_processed_to_7 );
    $_SESSION["INSERT_PROCESS_ACTIVE"] = true;
}

$_SESSION["PASS_NUMBER"]++;
$fields_array = sqlGetFieldsNamesOfTable( "PRICE_Products__nixru" );
$fields_list  = "t1." . implode( ",t1.", $fields_array );
$fields_list .= "#END";
$fields_list = str_replace( "#END", " ", $fields_list );

$sql_pass = "
    SELECT {$fields_list}, t3.categoriesID_AssocLink AS AssocLink
    FROM PRICE_Products__nixru t1
    LEFT JOIN {$GroupsTable} t3 ON t3.id = t1.parent_id
    WHERE (t1.enabled = 1)
      AND (t1.is_processed = 7)
      AND (t1.is_published = 0)
      AND t3.categoriesID_AssocLink > 1
      AND (t1.vendorID = {$vendorID})
    ORDER BY t1.id LIMIT 100
    ";

$data = array();
// $selected_ids = array();
$q = db_query( $sql_pass );

while ( $row = db_fetch_row( $q ) ) {

    $data[]                    = $row;
    $data_permanent_categoryID = $row["AssocLink"];
    // $selected_ids[]            = (int)$row["id"];
}

$USE_CHUNK = false;

if ( !is_array( $data ) || !count( $data ) ) {
    $_SESSION["INSERT_PROCESS_ACTIVE"] = false;
    $chunk                             = "";
} else {
    // $selected_id_List = implode( ",", $selected_ids );
    # конвертируем товары в основной формат
    $convertedProducts = prdConvertFromPriceToCatalog( $data, null, "" );
    if ( is_array( $convertedProducts ) && count( $convertedProducts ) > 0 ) {
        $counterUpdateProduct = 0;
        $counterAddProduct    = 0;
        $newproduct_DATA      = array();
        foreach ( $convertedProducts as $key => $_product ) {

            $newproduct_DATA = $_product;
            $aaaa            = prdAddProductFromData(
                $newproduct_DATA,
                $updateGCV = 0,
                $USE_CHUNK
            );

            if ( $USE_CHUNK ) {
                $chunk .= trim( str_replace( "   ", " ", $aaaa ) );
                $chunk .= "; ";
                $chunk .= "UPDATE " . GOODS_TABLE__NIXRU . "  SET `is_processed` = 1,  `is_published` = 1 WHERE " . " product_code =" . $_product['product_code'] . '  AND vendorID=' . $vendorID . "; ";
            } else {
                // $aaaa = prdAddProductFromData( $newproduct_DATA, $updateGCV = 0, $USE_CHUNK );
                if ( $aaaa > 0 ) {
                    $counterAddProduct++;
                    dbSet_publish_processed( GOODS_TABLE__NIXRU, 1, " product_code ='" . $_product['product_code'] . "' AND vendorID=" . (int)$vendorID );
                }
            }

        }
    }

    if ( $USE_CHUNK && ( $chunk != "" ) ) {
        $mres1 = db_multiquery( $chunk, DB_HOST, DB_USER, DB_PASS, DB_NAME );
        $chunk = "";
        if ( $mres1 ) {
            $_SESSION["PRODUCTS_NUMBER"] += count( $convertedProducts );
        }
    } else {
        $_SESSION["PRODUCTS_NUMBER"] += $counterAddProduct;
    }
}

if ( $_SESSION["INSERT_PROCESS_ACTIVE"] ) {
    $continue_function = "process_insert_linked_by_AssocLink";
} else {
    $continue_function = false;
}
?>