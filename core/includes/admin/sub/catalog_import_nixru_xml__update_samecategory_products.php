<?php
# проходим таблицу Групп, ищем Полностью совпадающие по названию Категории,
# и суваем туда продукты из одноименных Групп, в котрых есть товары

### Переносим товары из PRICE_Groups в постоянные категории в случае полного совпадения ИМЕН ВРЕМЕННОЙ и ПОСТОЯННОЙ КАТЕГОРИИ
$CategoriesTable      = CATEGORIES_TABLE;
$ProductsTable        = PRODUCTS_TABLE;
$GroupsTable          = GROUPS_TABLE__NIXRU;
$GoodsTable = GOODS_TABLE__NIXRU;

$res = null;
$ii  = 0;

# выбираем Группы товары их которых переонсим
$FullMatchesGroups = array();
$sql_transfer      = <<<SQL
    SELECT temp.GroupName as temp_Name, permanent.name as permanent_Name,
    temp.GroupID as temp_GroupID, permanent.categoryID as permanent_CatID,
    temp.GroupParentID as temp_Parent, permanent.parent as permanent_Parent
    FROM PRICE_Groups AS temp
    LEFT JOIN UTF_categories AS permanent
    ON LOWER(TRIM(temp.GroupName)) LIKE LOWER(TRIM(permanent.name))
    WHERE temp.GroupParentID = 0
    AND temp.vendorID = 1
    AND temp.products_count > 0
    AND temp.enabled = 1
    AND temp.subcount = 0
    AND permanent.subcount = 0
    AND permanent.name IS NOT NULL
    AND permanent.parent IS NOT NULL
    ORDER BY permanent.products_count DESC, permanent.categoryID DESC
SQL;
$q_FullMatchesGroups = db_query( $sql_transfer );

while ( $row = db_fetch_assoc( $q_FullMatchesGroups ) ) {
    $FullMatchesGroups[] = $row;

    {
        # обновляем PRICE_Groups в поле categoriesID_AssocLink добавляем permanent_CatID
        // $params["id"]=_GroupID_2_id($row["temp_GroupID"]);
        $params["GroupID"]  = ( $row["temp_GroupID"] );
        $params["vendorID"] = 1;
        $newCategoryID      = (int)$row["permanent_CatID"];
        groupUpdateAssocLink( $params, $newCategoryID );
    }

}

$count_FullMatchesGroups = count( $FullMatchesGroups );
$smarty->assign( "count_FullMatchesGroups", $count_FullMatchesGroups );

// debugfile( $count_FullMatchesGroups, "catalog_import_nixru_xml__update_samecategory_products.php:57 count_FullMatchesGroups" );

# Копируем Товары из  PRICE_Groups в Каталог
if ( is_array( $FullMatchesGroups ) && ( $count_FullMatchesGroups > 0 ) ) {
    # для кажной совпадающей группы отбираем из таблицы продуктов никсру товары этой группы
    # и вставлем или обновляем товары в Каталоге добаляя их в Категорию совпадающую с categoriesID_AssocLink
    # (потом сделаю что и в product_categories)
    foreach ( $FullMatchesGroups as $_Group ) {

        $new_categoryID = $_Group["permanent_CatID"];

        $ProductsToInsertUpdate = array();

        $sql_ProductsToInsertUpdate = <<<SQL
        SELECT product_code
        FROM {$GoodsTable}
        WHERE GroupID = {$_Group["temp_GroupID"]}
        AND vendorID = 1
        AND is_published = 0 -- пропускаем Опубликованные Продукты
        AND enabled = 1
SQL;
        $q_ProductsToInsertUpdate = db_query( $sql_ProductsToInsertUpdate );

        while ( $r = db_fetch_row( $q_ProductsToInsertUpdate ) ) {
            $ProductsToInsertUpdate[] = $r[0];
        }

        $count_ProductsToInsertUpdate = count( $ProductsToInsertUpdate );
        $smarty->assign( "count_ProductsToInsertUpdate", $count_ProductsToInsertUpdate );

        // debugfile($count_ProductsToInsertUpdate,"catalog_import_nixru_xml__update_samecategory_products.php:83 count_ProductsToInsertUpdate");

        if ( is_array( $ProductsToInsertUpdate ) && $count_ProductsToInsertUpdate >= 1 ) {
            $sql_update = "";
            $iii        = 0;
            foreach ( $ProductsToInsertUpdate as $_update_product_code ) {

                $dataNew      = array();
                $_sql_dataNew = <<<SQL
                SELECT
                Prefix,
                Name as name,
                Col1 as Price,
                Col6 as list_price,
                ApproxAmount,
                href,
                vendorID,
                enabled
                FROM {$GoodsTable}
                WHERE product_code = {$_update_product_code}
                ORDER BY product_code;
SQL;

                $dataNew = db_fetch_assoc( db_query( $_sql_dataNew ) );

                if ( is_array( $dataNew ) && ( count( $dataNew ) > 0 ) ) {
                    $new_enabled = (int)( $dataNew["enabled"] );

                    $new_name       = xEscSQL( $dataNew["Prefix"] . " [same_category_name] " . $dataNew["name"] ); // "[XXX] [XXX] " .
                    $new_Price      = (float)( $dataNew["Price"] );
                    $new_list_price = (float)( $dataNew["list_price"] );

                    $new_in_stock    = Encode_ApproxAmount( $dataNew["ApproxAmount"] );
                    $new_description = ( "<a data-vendor=" . $dataNew["vendorID"] . " class=\"btn btn-default btn-sm btn-block\" target=\"_blank\" href=\"" . $dataNew["href"] . "\">Информация nix.ru</a>" );

                    $sql_insert_update = <<<SQL
                    INSERT INTO {$ProductsTable} (
                    categoryID,
                    product_code,
                    name,
                    Price,
                    list_price,
                    in_stock,
                    date_added,
                    date_modified,
                    description,
                    vendorID,
                    enabled
                    )
                    VALUES (
                    '{$new_categoryID}',
                    '{$_update_product_code}',
                    '{$new_name}',
                    '{$new_Price}',
                    '{$new_list_price}',
                    '{$new_in_stock}',
                    NOW(),
                    NOW(),
                    '{$new_description}',
                    1,
                    '{$new_enabled}'
                    )
                    ON DUPLICATE KEY UPDATE
                    categoryID = '{$new_categoryID}',
                    product_code = '{$_update_product_code}',
                    name ='{$new_name}',
                    Price ='{$new_Price}',
                    list_price ='{$new_list_price}',
                    in_stock ='{$new_in_stock}',
                    date_modified = NOW(),
                    description = '{$new_description}',
                    vendorID = 1,
                    enabled ={$new_enabled}
SQL;

                    #date_modified = '{$_start_time}',
                    #date_modified = NOW()
                    $ii++;
                    $iii++;

                    $queryUpdate = db_query( $sql_insert_update );

                    // debugfile( $_update_product_code, "$ii -- $iii" );

                    $sql_is_processed = <<<SQL
                    UPDATE {$GoodsTable}
                    SET
                    is_processed = 1,
                    is_published = 1
                    WHERE product_code = '{$_update_product_code}';
SQL;
                    $queryIs_processed = db_query( $sql_is_processed );
                }
                  // if ( $ii >= 5 ) {break; }
            } //foreach
        } //if

    } //  foreach ( $FullMatchesGroups as $_Group )

    $pluck_FullMatchesGroups = pluck( $FullMatchesGroups, "temp_GroupID" );

    debugfile( $FullMatchesGroups, "\$FullMatchesGroups catalog_import_nixru_xml__update_samecategory_products.php:153" );
    debugfile( $pluck_FullMatchesGroups, "\$pluck_FullMatchesGroups catalog_import_nixru_xml__update_samecategory_products.php:154" );
// UPDATE PRICE_Groups SET
    // is_new_group =0,
    // is_published = 1,
    // is_processed = 1,
    // date_modified = NOW()
    // WHERE GroupID IN ()
    // AND vendorID = 1
    // AND enabled = 1

    $pluck_FullMatchesCategories = pluck( $FullMatchesGroups, "permanent_CatID" );
// debugfile( $pluck );

    $fullMatchesGroups_list     = implode( ",", $pluck_FullMatchesGroups );     // список совпадающих ID Group из XML
    $fullMatchesCategories_list = implode( ",", $pluck_FullMatchesCategories ); // список совпадающих categoriesID из каталога

// debugfile( $pluck_list );
    // debugfile( $FullMatchesGroups );
    # отмечаем Группы как не новые, как обработанные и как опубликованные
    $sql_addMatchesCategoryID_is_published = <<<SQL
    UPDATE {$GroupsTable} SET
    is_new_group = 0,
    is_published = 1,
    is_processed = 1,
    date_modified = NOW()
    WHERE GroupID IN ({$fullMatchesGroups_list})
    AND vendorID = 1
    AND enabled = 1
SQL;

    $q_addMatchesCategoryID_is_published = db_query( $sql_addMatchesCategoryID_is_published, 1 );

} //if ( is_array( $FullMatchesGroups )
  // $smarty->assign( "fullMatchesGroups_list", $fullMatchesGroups_list );


$test = <<<SQL
    SELECT
    temp.id as temp_id, permanent.categoryID as permanent_id,
    temp.GroupID as temp_GroupID, permanent.categoryID as permanent_CatID,
    temp.GroupName as temp_Name, permanent.name as permanent_Name,
    temp.GroupParentID as temp_Parent, permanent.parent as permanent_Parent
    FROM PRICE_Groups AS temp
    LEFT JOIN UTF_categories AS permanent
    ON LOWER(TRIM(temp.GroupName)) LIKE LOWER(TRIM(permanent.name))
    WHERE
    -- temp.GroupParentID = 0
    temp.vendorID = 1
    -- AND temp.products_count > 0
    AND temp.enabled = 1
    -- AND temp.subcount = 0
    -- AND permanent.subcount = 0
    AND permanent.name IS NOT NULL
    AND permanent.parent IS NOT NULL
    ORDER BY permanent.products_count DESC, permanent.categoryID DESC
SQL;










?>