<?php
# обработка старых продуктов, которые уже есть в базе
$ProductsToUpdate     = array();
$sql_ProductsToUpdate = <<<SQL
SELECT t1.product_code
FROM UTF_products t1
LEFT JOIN PRICE_Products__nixru t2
ON (t1.product_code = t2.product_code) AND (t1.vendorID = t2.vendorID)
WHERE (t2.product_code IS NOT NULL)
AND (t1.enabled = 1) AND (t2.enabled = 1)
AND (t2.GroupID IN (SELECT GroupID FROM PRICE_Groups WHERE enabled =1))
ORDER BY t2.GroupID,t1.product_code;
SQL;
$q_ProductsToUpdate = db_query( $sql_ProductsToUpdate , 1 );
while ( $r = db_fetch_row( $q_ProductsToUpdate ) ) {
    $ProductsToUpdate[] = $r[0];
}

$count_ProductsToUpdate = count( $ProductsToUpdate );
$smarty->assign( "count_ProductsToUpdate", $count_ProductsToUpdate );
// debugfile( $count_ProductsToUpdate, "catalog_import_nixru_xml__update_published_products.php:21 count_ProductsToUpdate" );
// die();

if ( is_array( $ProductsToUpdate ) && $count_ProductsToUpdate >= 1 ) {
    $sql_update = "";
    $ii         = 0;
    foreach ( $ProductsToUpdate as $_update_product_code ) {

        $dataNew = array();
        $_sql    = <<<SQL
SELECT Prefix, Name as name, Col1 as Price, Col6 as list_price, ApproxAmount, href,vendorID
FROM PRICE_Products__nixru
WHERE product_code = {$_update_product_code}
ORDER BY product_code
LIMIT 1
SQL;

        $dataNew = db_fetch_assoc( db_query( $_sql ) );
        if ( is_array( $dataNew ) ) {

            $new_name            = xEscSQL( $dataNew["Prefix"] . " [обновление сущ.товара] " . $dataNew["name"] ); // "[XXX] [XXX] " .
            $new_Price           = (float)( $dataNew["Price"] );
            $new_list_price      = (float)( $dataNew["list_price"] );
            $new_date_from_price = ( $dataNew["date_from_price"] );
            $new_in_stock        = Encode_ApproxAmount( $dataNew["ApproxAmount"] );
            $new_description     = ( "<a data-vendor=" . $dataNew["vendorID"] . " class=\"btn btn-default btn-sm btn-block\" target=\"_blank\" href=\"" . $dataNew["href"] . "\">Информация nix.ru</a>" );
            // debugfile( $dataNew["in_stock"], "\dataNew[ApproxAmount] = {$dataNew['ApproxAmount']} " );

            $sql_update = <<<SQL
UPDATE  UTF_products
SET
name ='{$new_name}',
Price ='{$new_Price}',
list_price ='{$new_list_price}',
in_stock ='{$new_in_stock}',
date_modified = '{$_start_time}',
description = '{$new_description}'
WHERE product_code = '{$_update_product_code}'
AND vendorID=1;
SQL;
#date_modified = '{$_start_time}',
            #date_modified = NOW()
            $ii++;
            // debugfile($ii,"catalog_import_nixru_xml__update_published_products.php:61 ii");
            $queryUpdate = db_query( $sql_update );
        }
          // if ( $ii >= 500 ) {break; }
    } //foreach

# отмечаем Продукты  как обработанные и как опубликованные
    $ProductsToUpdate_list = implode( ",", $ProductsToUpdate ); // список Товаров
                                                              // debugfile( $ProductsToUpdate );
                                                              // debugfile( $ProductsToUpdate_list );

    // $ProductsToUpdate_list = implode( ",", $pluck_ProductsToUpdate ); // список совпадающих ID Group из XML
    $sql_is_processed      = <<<SQL
UPDATE PRICE_Products__nixru
SET
 is_processed = 1,
 is_published = 1
WHERE product_code IN ({$ProductsToUpdate_list})
SQL;
    $queryIs_processed = db_query( $sql_is_processed );


} //if

?>