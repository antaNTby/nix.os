<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

function catname( $id ) {
// global $antcats;
    // $res= $antcats[(int)$id];
    global $fc;
    $res = $fc[(int)$id]["name"];
    return $res;
}

// Перенес из ajaxfilter.php
function recursiveCatFilter( $catID, $arrayID = array() ) {
    global $fc;
    foreach ( $fc as $val ) {
        if ( $val['parent'] == $catID ) {
            $arrayID = recursiveCatFilter( $val['categoryID'], $arrayID );
        }
    }
    $arrayID[] = $catID;
    return $arrayID;
}

//построение фильтра CONF_AJAX_FILTER_ENABLE
# BEGIN автоопределение характеристик для фильтра
function recursiveCat( $catID, $arrayID = array() ) {
    global $mc;
    foreach ( $mc as $key => $val ) {
        if ( $val == $catID ) {
            $arrayID = recursiveCat( $key, $arrayID );
        }
    }

    $arrayID[] = $catID;
    return $arrayID;
}
# BEGIN автоопределение характеристик для фильтра

// function FilterShowPrice( $price, $rval = 2, $dec = '.', $term = ' ' ) {
//     $x = (int)$price;
//     if ( $x > 1000000 ) {
//         $rounder = -4;
//     } elseif ( $x > 100000 ) {
//         $rounder = -3;
//     } elseif ( $x > 10000 ) {
//         $rounder = -2;
//     } elseif ( $x > 1000 ) {
//         $rounder = -1;
//     } elseif ( $x > 100 ) {
//         $rounder = 0;
//     } elseif ( $x < 10 ) {
//         $rounder = 2;
//     }

//     $priceF  = _formatPrice( $price, $rounder, '.', '#' );
//     $priceFF = str_replace( '#', $term, $priceF );
//     if ( $priceFF == '0.00' ) {
//         $priceFF = 0;
//     }

//     return $priceFF;
// }

function get_antcats( $addonCat = "categoryID>=1" ) {

    $where_clause = "";
    if ( $addonCat != "" ) {
        $where_clause = " WHERE {$addonCat}";
    }
    $q = db_query( "SELECT `categoryID`, `name` FROM `" .
        CATEGORIES_TABLE . "` {$where_clause}" . " ORDER BY categoryID" );
    $res        = array();
    $antcatsKEY = array();
    $antcatsVAL = array();
    while ( $row = db_fetch_row( $q ) ) {
        $res["{$row[0]}"] = $row[1];
    }
    return $res;
}

function catInstall() {
    db_query( "INSERT INTO " . CATEGORIES_TABLE . " ( name, parent, categoryID, sort_order ) values ( '" . ADMIN_CATEGORY_ROOT . "', NULL, 1,0 )" );
}

function getcontentcat( $categoryID ) {
    $out = array();
    $cnt = 0;
    $q   = db_query( "SELECT Owner FROM " . RELATED_CONTENT_CAT_TABLE . " WHERE categoryID=" . (int)$categoryID ) . "";
    while ( $row = db_fetch_row( $q ) ) {
        $qd           = db_query( "SELECT aux_page_name from " . AUX_PAGES_TABLE . " WHERE aux_page_ID=" . (int)$row["Owner"] );
        $rowd         = db_fetch_row( $qd );
        $out[$cnt][0] = $row["Owner"];
        $out[$cnt][1] = $rowd["aux_page_name"];
        $cnt++;
    }
    return $out;
}

function processCategories( $level, $path, $sel ) {
    //returns an array of categories, that will be presented by the category_navigation.tpl template

    //$categories[] - categories array
    //$level - current level: 0 for main categories, 1 for it's subcategories, etc.
    //$path - path from root to the selected category (calculated by calculatePath())
    //$sel -- categoryID of a selected category

    //returns an array of (categoryID, name, level)

    //category tree is being rolled out "by the path", not fully

    $out = array();
    $cnt = 0;

    $parent = $path[$level]["parent"];
    if ( $parent == "" || $parent == null ) {
        $parent = "NULL";
    }

    $q = db_query( "SELECT categoryID, name FROM " . CATEGORIES_TABLE .
        " WHERE parent=" . (int)$path[$level]["parent"] . " ORDER BY sort_order, name" );
    $c_path = count( $path );
    while ( $row = db_fetch_row( $q ) ) {
        $out[$cnt][0] = $row["categoryID"];
        $out[$cnt][1] = $row["name"];
        $out[$cnt][2] = $level;
        $cnt++;

        //process subcategories?
        if ( $level + 1 < $c_path && $row["categoryID"] == $path[$level + 1] ) {
            $sub_out = processCategories( $level + 1, $path, $sel );
            //add $sub_out to the end of $out
            for ( $j = 0; $j < count( $sub_out ); $j++ ) {
                $out[] = $sub_out[$j];
                $cnt++;
            }
        }
    }
    return $out;
} //processCategories

function fillTheCList( $parent, $level ) //completely expand category tree
{

    $q = db_query( "SELECT categoryID, name, products_count, products_count_admin, parent FROM " .
        CATEGORIES_TABLE . " WHERE parent=" . (int)$parent . " ORDER BY sort_order, name" );
    $a = array(); //parents
    while ( $row = db_fetch_row( $q ) ) {
        $row["level"] = $level;
        $a[]          = $row;
        //process subcategories
        $b = fillTheCList( $row[0], $level + 1 );
        //add $b[] to the end of $a[]
        $cc_b = count( $b );
        for ( $j = 0; $j < $cc_b; $j++ ) {
            $a[] = $b[$j];
        }

    }
    return $a;

} //fillTheCList

function _recursiveGetCategoryCompactCList( $path, $level ) {
    $q = db_query( "SELECT categoryID, parent, name, products_count, title FROM " . CATEGORIES_TABLE .
        " WHERE parent=" . (int)$path[$level - 1]["categoryID"] . " ORDER BY sort_order, name " );
    $res                = array();
    $selectedCategoryID = null;
    $c_path             = count( $path );
    while ( $row = db_fetch_row( $q ) ) {

        $row["level"] = $level;
        $res[]        = $row;
        if ( $level <= $c_path - 1 ) {
            if ( (int)$row["categoryID"] == (int)$path[$level]["categoryID"] ) {
                $selectedCategoryID = $row["categoryID"];
                $arres              = _recursiveGetCategoryCompactCList( $path, $level + 1 );

                $c_arres = count( $arres );
                for ( $i = 0; $i < $c_arres; $i++ ) {
                    $res[] = $arres[$i];
                }

            }
        }
    }

    return $res;
}

function getcontentcatresc( $catID ) {
    $q = db_query( "select categoryID, name, products_count, description, picture  from " . CATEGORIES_TABLE .
        " where parent=" . (int)$catID . " order by sort_order, name " );
    $res = array();
    while ( $row = db_fetch_row( $q ) ) {
        $res[] = $row;
    }
// debug($res);
    return $res;
}

function getsisters( $rootID, $CategoriesTable = CATEGORIES_TABLE ) {

    $sql_sisters = <<<SQL
SELECT `categoryID`,
       `name`,
       `products_count`
       -- ,
       -- `description`,
       -- `picture`
FROM `{$CategoriesTable}`
WHERE (`parent` >1 AND `parent` = '{$rootID}' AND `products_count` >0) OR (`parent` >1 AND `parent` = '{$rootID}' AND `subcount` > 0)
ORDER BY `sort_order`, `name`
SQL;
    $sisters = array();
    $q       = db_query( $sql_sisters );
    while ( $row = db_fetch_assoc( $q ) ) {
        $sisters[] = $row;
    }
    return $sisters;
}

function catExpandCategory( $categoryID, $sessionArrayName ) {
    $existFlag = false;
    foreach ( $_SESSION[$sessionArrayName] as $key => $value ) {
        if ( $value == $categoryID ) {
            $existFlag = true;
            break;
        }
    }

    if ( !$existFlag ) {
        $_SESSION[$sessionArrayName][] = $categoryID;
    }

}

function catShrinkCategory( $categoryID, $sessionArrayName ) {
    foreach ( $_SESSION[$sessionArrayName] as $key => $value ) {
        if ( $value == $categoryID ) {
            unset( $_SESSION[$sessionArrayName][$key] );
        }

    }
}

function catExpandCategoryp( $sessionArrayName ) {
    $categoryID = 0;
    $cats       = array();
    $q          = db_query( "select categoryID FROM " . CATEGORIES_TABLE . " ORDER BY sort_order, name" );
    while ( $row = db_fetch_row( $q ) ) {
        $_SESSION[$sessionArrayName][] = $row[0];
    }

}

function catShrinkCategorym( $sessionArrayName ) {
    unset( $_SESSION[$sessionArrayName] );
    $_SESSION["expcat"] = array( 1 );
}

function catGetCategoryCompactCList( $selectedCategoryID ) {
    $path  = catCalculatePathToCategory( $selectedCategoryID );
    $res   = array();
    $res[] = array( "categoryID" => 1, "parent"                  => null,
        "name"                      => ADMIN_CATEGORY_ROOT, "level" => 0 );
    $q = db_query( "SELECT categoryID,
       parent,
       name,
       products_count,
       title
FROM " . CATEGORIES_TABLE . " where parent=1 " . "
ORDER BY sort_order,
         name" );
    $c_path = count( $path );
    while ( $row = db_fetch_row( $q ) ) {
        $row["level"] = 1;
        $res[]        = $row;
        if ( $c_path > 1 ) {
            if ( $row["categoryID"] == $path[1]["categoryID"] ) {
                $arres   = _recursiveGetCategoryCompactCList( $path, 2 );
                $c_arres = count( $arres );
                for ( $i = 0; $i < $c_arres; $i++ ) {
                    $res[] = $arres[$i];
                }

            }
        }
    }
    // debugfile($res,"catGetCategoryCompactCList:263");
    return $res;
}

// *****************************************************************************
// Purpose        gets category tree to render it on HTML page
// Inputs
//                        $parent - must be 0
//                        $level        - must be 0
//                        $expcat - array of category ID that expanded
// Remarks
//                        array of item
//                                for each item
//                                        "products_count"                        -                count product in category including
//                                                                                                                        subcategories excluding enabled product
//                                        "products_count_admin"                -                count product in category
//                                                                                                                        without count product subcategory
//                                        "products_count_category"        -
// Returns        nothing
function _recursiveGetCategoryCListOLD( $parent, $level, $expcat, $_indexType = 'NUM', $cprod = false, $ccat = true ) {
    global $fc, $mc;

    $rcat   = array_keys( $mc, (int)$parent );
    $result = array(); //parents

    $crcat = count( $rcat );
    for ( $i = 0; $i < $crcat; $i++ ) {

        $row = $fc[(int)$rcat[$i]];
        if ( !file_exists( "data/category/" . $row["picture"] ) ) {
            $row["picture"] = "";
        }

        $row["level"]            = $level;
        $row["ExpandedCategory"] = false;
        if ( $expcat != null ) {
            foreach ( $expcat as $categoryID ) {
                if ( (int)$categoryID == (int)$row["categoryID"] ) {
                    $row["ExpandedCategory"] = true;
                    break;
                }
            }
        } else {
            $row["ExpandedCategory"] = true;
        }

        if ( $ccat ) {$row["products_count_category"] = catGetCategoryProductCount( $row["categoryID"], $cprod );}

        $row["ExistSubCategories"] = ( $row["subcount"] != 0 );

        if ( $_indexType == 'NUM' ) {
            $result[] = $row;
        } elseif ( $_indexType == 'ASSOC' ) {
            $result[$row['categoryID']] = $row;
        }

        if ( $row["ExpandedCategory"] ) {
            //process subcategories
            $subcategories = _recursiveGetCategoryCList( $row["categoryID"],
                $level + 1, $expcat, $_indexType, $cprod, $ccat );

            if ( $_indexType == 'NUM' ) {

                //add $subcategories[] to the end of $result[]
                for ( $j = 0; $j < count( $subcategories ); $j++ ) {
                    $result[] = $subcategories[$j];
                }

            } elseif ( $_indexType == 'ASSOC' ) {

                //add $subcategories[] to the end of $result[]
                foreach ( $subcategories as $_sub ) {

                    $result[$_sub['categoryID']] = $_sub;
                }
            }

        }
    }
    return $result;
}

function _recursiveGetCategoryCList( $parent, $level, $expcat, $_indexType = 'NUM', $cprod = false, $ccat = true ) {
# BEGIN упростим функцию _recursiveGetCategoryCList
    /*
    функция вызывается только из catGetCategoryCList() и catGetCategoryCListMin()
    $parent - categoryID родительской категории, от которого получают список дочерних. Обычно равно 0 (Главная категория)
    $level - по логике это level категории $parent (т.е. обычно 0), но по идее может быть любым, при рекурсии он +1.
    $expcat - массив из categoryID, для которых надо в свою очередь построить дерево дочерних категорий (вызвать эту же func рекурсивно)
    (если не массив, а null, то для всех).
    $_indexType - 'NUM' - категории располагаются в массиве c последовательным индексом (0,1,2,3,4..), если 'ASSOC', то индекс элемента массива равен categoryID
    $cprod - считать только enabled=1 товары (true) или все (false)
    $ccat - считать количество товаров в категории (true)
     */
    global $fc;
    global $mc;

    $rcats = array_keys( $mc, (int)$parent );
                       // debug($rcats);
    $result = array(); //parents

    foreach ( $rcats as $rcat ) {
        $row                       = $fc[(int)$rcat];
        $row['level']              = $level;
        $row['ExistSubCategories'] = ( $row['subcount'] != 0 );
        $row['ExpandedCategory']   = ( $expcat == null || in_array( (int)$row['categoryID'], $expcat ) );
        if ( !file_exists( 'data/category/' . $row['picture'] ) ) {
            $row['picture'] = '';
        }

# Новый вариант расчета products_count_category (без SQL-запросов, используя $fc)
        $in_childs  = 0;
        $count_name = $cprod ? 'products_count' : 'products_count_admin';
        foreach ( array_keys( $mc, $row['categoryID'] ) as $childID ) {
            $in_childs += $fc[$childID][$count_name];
        }

        if ( $ccat ) {
            $row['products_count_category'] = $row[$count_name] - $in_childs;
        }

# старый вариант (с SQL-запросами)
        #               if ($ccat) $row['products_count_category'] = catGetCategoryProductCount( $row['categoryID'], $cprod );

        if ( $_indexType == 'NUM' ) {
            $result[] = $row;
        } elseif ( $_indexType == 'ASSOC' ) {
            $result[$row['categoryID']] = $row;
        }

        if ( $row['ExpandedCategory'] && $row['ExistSubCategories'] ) {
            $subcategories = _recursiveGetCategoryCList( $row['categoryID'], $level + 1, $expcat, $_indexType, $cprod, $ccat );
            foreach ( $subcategories as $_sub ) {
                if ( $_indexType == 'NUM' ) {
                    $result[] = $_sub;
                } elseif ( $_indexType == 'ASSOC' ) {
                    $result[$_sub['categoryID']] = $_sub;
                }

            }
        }
    }
    return $result;
# END упростим функцию _recursiveGetCategoryCList
}

// *****************************************************************************
// Purpose        gets category tree to render it on HTML page
// Inputs
// Remarks
// Returns        nothing
function catGetCategoryCList( $expcat = null, $_indexType = 'NUM', $cprod = false, $ccat = true ) {
    return _recursiveGetCategoryCList( 1, 0, $expcat, $_indexType, $cprod, $ccat );
}

function catGetCategoryCListMin() {
    # BEGIN кэшируем в файл клиентский список категорий
    #return _recursiveGetCategoryCList( 1, 0, null, 'NUM', false, false);
    if ( file_exists( "core/temp/cats_tree_cache.txt" ) && ( $cache = file_get_contents( "core/temp/cats_tree_cache.txt" ) ) && $cats = unserialize( $cache ) ) {
        return $cats;
    } else {
        $cats = _recursiveGetCategoryCList( 1, 0, null, 'NUM', false, false );
        file_put_contents( "core/temp/cats_tree_cache.txt", serialize( $cats ) );

        //  $cats_json = convert_Categories_to_jstree( $cats );
        // file_put_contents( "catcache_json.json", $cats_json );
        return $cats;
    }
    # END кэшируем в файл клиентский список категорий

/*
file_put_contents — Пишет данные в файл
file_put_contents ( string $filename , mixed $data [, int $flags = 0 [, resource $context ]] ) : int
Функция идентична последовательным успешным вызовам функций fopen(), fwrite() и fclose().
Если filename не существует, файл будет создан. Иначе, существующий файл будет перезаписан, за исключением случая, если указан флаг FILE_APPEND.
 */
}

// *****************************************************************************
// Purpose        gets product count in category
// Inputs
// Remarks  this function does not keep in mind subcategories
// Returns        nothing
function catGetCategoryProductCount( $categoryID, $cprod = false ) {
    if ( !$categoryID ) {
        return 0;
    }

    $res = 0;
    $sql = "
                select count(*) FROM " . PRODUCTS_TABLE . "
                WHERE categoryID=" . (int)$categoryID . "" . ( $cprod ? " AND enabled>0" : "" );
    $q = db_query( $sql );
    $t = db_fetch_row( $q );
    $res += $t[0];
    if ( $cprod ) {
        $sql = "
                        select COUNT(*) FROM " . PRODUCTS_TABLE . " AS prot
                        LEFT JOIN " . CATEGORIY_PRODUCT_TABLE . " AS catprot
                        ON prot.productID=catprot.productID
                        WHERE catprot.categoryID=" . (int)$categoryID . " AND prot.enabled>0
                ";
    } else {
        $sql = "
                        select count(*) from " . CATEGORIY_PRODUCT_TABLE .
        " where categoryID=" . (int)$categoryID
        ;
    }

    $q1  = db_query( $sql );
    $row = db_fetch_row( $q1 );
    $res += $row[0];
    return $res;
}

function update_sCount( $parent ) {
    global $fc, $mc;

    $rcat  = array_keys( $mc, (int)$parent );
    $crcat = count( $rcat );
    for ( $i = 0; $i < $crcat; $i++ ) {

        $rowsub   = $fc[(int)$rcat[$i]];
        $countsub = count( array_keys( $mc, (int)$rowsub["categoryID"] ) );

        db_query( "UPDATE " . CATEGORIES_TABLE .
            " SET subcount=" . (int)$countsub . " " .
            " WHERE categoryID=" . (int)$rcat[$i] );

        $rowsubExist = ( $countsub != 0 );
        if ( $rowsubExist ) {
            update_sCount( $rowsub["categoryID"] );
        }

    }
}

function update_pCount( $parent ) {
    update_sCount( $parent );

    $q = db_query( "select categoryID FROM " . CATEGORIES_TABLE .
        " WHERE categoryID>1 AND parent=" . (int)$parent );

    $cnt                   = array();
    $cnt["admin_count"]    = 0;
    $cnt["customer_count"] = 0;

    // process subcategories
    while ( $row = db_fetch_row( $q ) ) {
        $t = update_pCount( $row["categoryID"] );
        $cnt["admin_count"] += $t["admin_count"];
        $cnt["customer_count"] += $t["customer_count"];
    }

    // to administrator
    $q = db_query( "select count(*) FROM " . PRODUCTS_TABLE .
        " WHERE categoryID=" . (int)$parent );
    $t = db_fetch_row( $q );
    $cnt["admin_count"] += $t[0];
    $q1 = db_query( "select count(*) from " . CATEGORIY_PRODUCT_TABLE .
        " where categoryID=" . (int)$parent );
    $row = db_fetch_row( $q1 );
    $cnt["admin_count"] += $row[0];

    // to customer
    $q = db_query( "select count(*) FROM " . PRODUCTS_TABLE .
        " WHERE enabled=1 AND categoryID=" . (int)$parent );
    $t = db_fetch_row( $q );
    $cnt["customer_count"] += $t[0];
    $q1 = db_query( "select productID, categoryID from " . CATEGORIY_PRODUCT_TABLE .
        " where categoryID=" . (int)$parent );
    while ( $row = db_fetch_row( $q1 ) ) {
        $q2 = db_query( "select productID from " . PRODUCTS_TABLE .
            " where enabled=1 AND productID=" . (int)$row["productID"] );
        if ( db_fetch_row( $q2 ) ) {
            $cnt["customer_count"]++;
        }

    }

    db_query( "UPDATE " . CATEGORIES_TABLE .
        " SET products_count=" . (int)$cnt["customer_count"] . ", products_count_admin=" .
        (int)$cnt["admin_count"] . " WHERE categoryID=" . (int)$parent );
    return $cnt;
}

function update_psCount( $parent ) {
    # BEGIN кэшируем в файл клиентский список категорий
    if ( file_exists( "core/temp/cats_tree_cache.txt" ) ) {
        unlink( "core/temp/cats_tree_cache.txt" );
    }

    if ( file_exists( "core/temp/categories_index_cache.txt" ) ) {
        unlink( "core/temp/categories_index_cache.txt" );
    }

    # END кэшируем в файл клиентский список категорий
    global $fc, $mc;

    $q = db_query( "SELECT categoryID, name, products_count, products_count_admin, parent, picture, subcount FROM " . CATEGORIES_TABLE ." ORDER BY sort_order, name" );
    $fc = array(); //parents
    $mc = array(); //parents
    while ( $row = db_fetch_row( $q ) ) {
        $fc[(int)$row["categoryID"]] = $row;
        $mc[(int)$row["categoryID"]] = (int)$row["parent"]; // categiryID => parentID
    }
    #update_pCount($parent);
    update_pCount_new( $parent );
}

function update_sCount_new( $parent, $counts = array( 1 => array( 'subcats' => 0, 'admin_count' => 0, 'customer_count' => 0 ) ) ) {
    global $mc;
    # array_keys — Возвращает все или некоторое подмножество ключей массива
    #Описание
    #array_keys ( array $array ) : array
    #array_keys ( array $array , mixed $search_value [, bool $strict = FALSE ] ) : array
    #Функция array_keys() возвращает числовые и строковые ключи, содержащиеся в массиве array.
    #Если указан параметр search_value, функция возвращает только ключи, совпадающие с этим параметром. В обратном случае, функция возвращает все ключи массива array.

    foreach ( array_keys( $mc, (int)$parent ) as $val ) {
        $counts[$val]['subcats']        = (int)count( array_keys( $mc, $val ) );
        $counts[$val]['admin_count']    = 0;
        $counts[$val]['customer_count'] = 0;
        if ( $counts[$val]['subcats'] ) {
            $counts = update_sCount_new( $val, $counts );
        }

    }
    return $counts;
}

function update_pCount_new( $parent ) {
    global $fc;
    $qstr = array(
        "SELECT categoryID, COUNT(*) AS admcount, COUNT(NULLIF(enabled,0)) AS count FROM " . PRODUCTS_TABLE . " GROUP BY categoryID",
        "SELECT pc.categoryID, COUNT(*) AS admcount, COUNT(NULLIF(enabled,0)) AS count FROM " . CATEGORIY_PRODUCT_TABLE . " AS pc JOIN " . PRODUCTS_TABLE . " USING (productID) GROUP BY pc.categoryID" );
    $counts = update_sCount_new( $parent );
    foreach ( $qstr as $str ) {
        $data = db_query( $str );
        while ( $row = db_fetch_assoc( $data ) ) {
            $categoryID = (int)$row['categoryID'];
            while ( $categoryID ) {
                $counts[$categoryID]['admin_count'] += (int)$row['admcount'];
                $counts[$categoryID]['customer_count'] += (int)$row['count'];
                $categoryID = $fc[$categoryID]['parent'];
            }
        }
    }
    $data = db_query( "SELECT categoryID, subcount, products_count, products_count_admin FROM " . CATEGORIES_TABLE );

    // ЗДЕСЬ ОШИБКА при наличии левых Категорий WHERE products_count=0 AND products_count_admin=0 AND subcount=0;
    while ( $row = db_fetch_assoc( $data ) ) {
        $categoryID = (int)$row['categoryID'];
        if ( (int)$row['subcount'] != $counts[$categoryID]['subcats'] || (int)$row['products_count'] != $counts[$categoryID]['customer_count'] || (int)$row['products_count_admin'] != $counts[$categoryID]['admin_count'] ) {
            db_query( "UpdatE " . CATEGORIES_TABLE . " SET subcount=" . (int)$counts[$categoryID]['subcats'] . ", products_count=" . (int)$counts[$categoryID]['customer_count'] . ", products_count_admin=" . (int)$counts[$categoryID]['admin_count'] . " WHERE categoryID=$categoryID" );
        }

    }
}

// *****************************************************************************
// Purpose        get subcategories by category id
// Inputs   $categoryID
//                                parent category ID
// Remarks  get current category's subcategories IDs (of all levels!)
// Returns        array of category ID

function catGetSubCategories( $categoryID ) {
# BEGIN оптимизируем SQL-запросы
    global $cats;
    $sub = array();
    foreach ( $cats as $key => $val ) {
        if ( $val['categoryID'] == $categoryID ) {
            $i    = $key;
            $last = count( $cats ) - 1;
            while ( ++$i <= $last && $cats[$i]['level'] > $val['level'] ) {
                $sub[] = $cats[$i]['categoryID'];
            }

            break;
        }
    }
    return $sub;
# END оптимизируем SQL-запросы
}

// function catGetSubCategoriesOLD($categoryID) {
//     $q = db_query("select categoryID from " . CATEGORIES_TABLE . " where parent=" . (int) $categoryID);
//     $r = array();
//     while ($row = db_fetch_row($q)) {
//         $a   = catGetSubCategories($row[0]);
//         $c_a = count($a);
//         for ($i = 0; $i < $c_a; $i++) {
//             $r[] = $a[$i];
//         }

//         $r[] = $row[0];
//     }
//     return $r;
// }

// *****************************************************************************
// Purpose        get subcategories by category id
// Inputs           $categoryID
//                                parent category ID
// Remarks          get current category's subcategories IDs (of all levels!)
// Returns        array of category ID
function catGetSubCategoriesSingleLayer( $categoryID ) {
    $q = db_query( "SELECT categoryID, name, products_count FROM " .
        CATEGORIES_TABLE . " WHERE parent=" . (int)$categoryID . " ORDER BY sort_order, name" );
    $result = array();
    while ( $row = db_fetch_row( $q ) ) {
        $result[] = $row;
    }
    return $result;
}

// *****************************************************************************
// Purpose        get category by id
// Inputs   $categoryID
//                                - category ID
// Remarks
// Returns
function catGetCategoryById( $categoryID ) {
    $q = db_query( "SELECT categoryID, name, parent, products_count, description, picture, " .
        " products_count_admin, sort_order, viewed_times, allow_products_comparison, allow_products_search, " .
        " show_subcategories_products, meta_description, meta_keywords, title " .
        " FROM " . CATEGORIES_TABLE . " WHERE categoryID=" . (int)$categoryID );
    $catrow = db_fetch_row( $q );
    return $catrow;
}

// *****************************************************************************
// Purpose        gets category META information in HTML form
// Inputs   $categoryID
//                                - category ID
// Remarks
// Returns
function catGetMetaTags( $categoryID ) {
    $q = db_query( "SELECT meta_description, meta_keywords from " .
        CATEGORIES_TABLE . " WHERE categoryID=" . (int)$categoryID );
    $row = db_fetch_row( $q );

    $res = "";

    if ( $row["meta_description"] != "" ) {
        $res .= "<meta name=\"Description\" content=\"" . $row["meta_description"] . "\">\n";
    }

    if ( $row["meta_keywords"] != "" ) {
        $res .= "<meta name=\"KeyWords\" content=\"" . $row["meta_keywords"] . "\" >\n";
    }

    return $res;
}

// *****************************************************************************
// Purpose        adds product to appended category
// Inputs
// Remarks      this function uses CATEGORIY_PRODUCT_TABLE table in data base instead of
//                        PRODUCTS_TABLE.categoryID. In CATEGORIY_PRODUCT_TABLE saves appended
//                        categories
// Returns        array of item
//                        "categoryID"
//                        "category_name"
function catGetAppendedCategoriesToProduct( $productID ) {
    $q = db_query( "SELECT " . CATEGORIES_TABLE . ".categoryID as categoryID, name AS category_name " .
        " FROM " . CATEGORIY_PRODUCT_TABLE . ", " . CATEGORIES_TABLE . " " .
        " WHERE " . CATEGORIY_PRODUCT_TABLE . ".categoryID = " . CATEGORIES_TABLE . ".categoryID " .
        " AND productID = " . (int)$productID );
    $data = array();
    while ( $row = db_fetch_row( $q ) ) {
        $wayadd = '';
        $way    = catCalculatePathToCategoryA( $row["categoryID"] );
        $cway   = count( $way );
        for ( $i = $cway - 1; $i >= 0; $i-- ) {
            if ( $way[$i]['categoryID'] != 1 ) {
                $wayadd .= $way[$i]['name'] . ' / ';
            }
        }
        $row["category_way"] = $wayadd . "<b>" . $row["category_name"] . "</b>";
        $data[]              = $row;
    }
    return $data;
}

// *****************************************************************************
// Purpose        adds product to appended category
// Inputs
// Remarks      this function uses CATEGORIY_PRODUCT_TABLE table in data base instead of
//                        PRODUCTS_TABLE.categoryID. In CATEGORIY_PRODUCT_TABLE saves appended
//                        categories
// Returns        true if success, false otherwise
function catAddProductIntoAppendedCategory( $productID, $categoryID ) {
    $q = db_query( "select count(*) from " . CATEGORIY_PRODUCT_TABLE .
        " where productID=" . (int)$productID . " AND categoryID=" . (int)$categoryID );
    $row = db_fetch_row( $q );

    $qh = db_query( "select categoryID from " . PRODUCTS_TABLE .
        " where productID=" . (int)$productID );
    $rowh             = db_fetch_row( $qh );
    $basic_categoryID = $rowh["categoryID"];

    if ( !$row[0] && $basic_categoryID != $categoryID ) {
        db_query( "insert into " . CATEGORIY_PRODUCT_TABLE .
            "( productID, categoryID ) " .
            "values( " . (int)$productID . ", " . (int)$categoryID . " )" );
        return true;
    } else {
        return false;
    }

}

// *****************************************************************************
// Purpose        removes product to appended category
// Inputs
// Remarks      this function uses CATEGORIY_PRODUCT_TABLE table in data base instead of
//                        PRODUCTS_TABLE.categoryID. In CATEGORIY_PRODUCT_TABLE saves appended
//                        categories
// Returns        nothing
function catRemoveProductFromAppendedCategory( $productID, $categoryID ) {
    db_query( "delete from " . CATEGORIY_PRODUCT_TABLE .
        " where productID = " . (int)$productID . " AND categoryID = " . (int)$categoryID );

}

// *****************************************************************************
// Purpose        calculate a path to the category ( $categoryID )
// Inputs
// Remarks
// Returns        path to category
function catCalculatePathToCategoryOLD( $categoryID ) {
    if ( !$categoryID ) {
        return NULL;
    }

    $path = array();

    $q = db_query( "select count(*) from " . CATEGORIES_TABLE .
        " where categoryID=" . (int)$categoryID );
    $row = db_fetch_row( $q );
    if ( $row[0] == 0 ) {
        return $path;
    }

    do {
        $q = db_query( "select categoryID, parent, name FROM " .
            CATEGORIES_TABLE . " WHERE categoryID=" . (int)$categoryID );
        $row    = db_fetch_row( $q );
        $path[] = $row;

        if ( $categoryID == 1 ) {
            break;
        }

        $categoryID = $row["parent"];
    } while ( 1 );
    //now reverse $path
    $path = array_reverse( $path );
    return $path;
}

// *****************************************************************************
// Purpose        calculate a path to the category ( $categoryID )
// Inputs
// Remarks
// Returns        path to category
function catCalculatePathToCategoryAOLD( $categoryID ) {
    if ( !$categoryID ) {
        return NULL;
    }

    $path = array();

    $q = db_query( "select count(*) from " . CATEGORIES_TABLE .
        " where categoryID=" . (int)$categoryID );
    $row = db_fetch_row( $q );
    if ( $row[0] == 0 ) {
        return $path;
    }

    $curr = $categoryID;
    do {
        $q = db_query( "select categoryID, parent, name FROM " .
            CATEGORIES_TABLE . " WHERE categoryID=" . (int)$categoryID );
        $row = db_fetch_row( $q );
        if ( $categoryID != $curr ) {
            $path[] = $row;
        }

        if ( $categoryID == 1 ) {
            break;
        }

        $categoryID = $row["parent"];
    } while ( 1 );
    //now reverse $path
    $path = array_reverse( $path );
    return $path;
}

function catCalculatePathToCategory( $categoryID ) {
# BEGIN оптимизируем SQL-запросы
    global $cats;
    if ( isset( $cats ) ) {
        if ( !$categoryID ) {
            return NULL;
        }

        $i = -1;
        foreach ( $cats as $key => $cat ) {
            if ( $cat['categoryID'] == $categoryID ) {
                $i = $key;
                break;}
        }

        $path = array();
        if ( $i < 0 ) {
            return $path;
        }

        while ( true ) {
            $level  = $cats[$i]['level'];
            $path[] = array( 'categoryID' => $cats[$i]['categoryID'], 'parent' => $cats[$i]['parent'], 'name' => $cats[$i]['name'] );
            if ( !$level ) {
                break;
            }
             while ( $level <= $cats[--$i]['level'] );
        }
        return array_reverse( $path );
    }
# END оптимизируем SQL-запросы
    if ( !$categoryID ) {
        return NULL;
    }

    $path = array();

    $q = db_query( "select count(*) from " . CATEGORIES_TABLE .
        " where categoryID=" . (int)$categoryID );
    $row = db_fetch_row( $q );
    if ( $row[0] == 0 ) {
        return $path;
    }

    do {
        $q = db_query( "select categoryID, parent, name FROM " .
            CATEGORIES_TABLE . " WHERE categoryID=" . (int)$categoryID );
        $row    = db_fetch_row( $q );
        $path[] = $row;

        if ( $categoryID == 1 ) {
            break;
        }

        $categoryID = $row["parent"];
    } while ( 1 );
    //now reverse $path
    $path = array_reverse( $path );
    return $path;
}

function catCalculatePathToCategoryA( $categoryID ) {
# BEGIN оптимизируем SQL-запросы
    global $cats;
    if ( isset( $cats ) ) {
        if ( !$categoryID ) {
            return NULL;
        }

        $i = -1;
        foreach ( $cats as $key => $cat ) {
            if ( $cat['categoryID'] == $categoryID ) {
                $i = $key;
                break;}
        }

        $path = array();
        if ( $i < 0 ) {
            return $path;
        }

        while ( true ) {
            $level = $cats[$i]['level'];
            if ( $categoryID != $cats[$i]['categoryID'] ) {
                $path[] = array( 'categoryID' => $cats[$i]['categoryID'], 'parent' => $cats[$i]['parent'], 'name' => $cats[$i]['name'] );
            }

            if ( !$level ) {
                break;
            }
             while ( $level <= $cats[--$i]['level'] );
        }
        return array_reverse( $path );
    }
# END оптимизируем SQL-запросы
    if ( !$categoryID ) {
        return NULL;
    }

    $path = array();

    $q = db_query( "select count(*) from " . CATEGORIES_TABLE .
        " where categoryID=" . (int)$categoryID );
    $row = db_fetch_row( $q );
    if ( $row[0] == 0 ) {
        return $path;
    }

    $curr = $categoryID;
    do {
        $q = db_query( "select categoryID, parent, name FROM " .
            CATEGORIES_TABLE . " WHERE categoryID=" . (int)$categoryID );
        $row = db_fetch_row( $q );
        if ( $categoryID != $curr ) {
            $path[] = $row;
        }

        if ( $categoryID == 1 ) {
            break;
        }

        $categoryID = $row["parent"];
    } while ( 1 );
    //now reverse $path
    $path = array_reverse( $path );
    return $path;
}

function _deleteSubCategories( $parent ) {

    $q1 = db_query( "SELECT picture FROM " . CATEGORIES_TABLE . " WHERE categoryID=" . (int)$parent );
    $r  = db_fetch_row( $q1 );
    if ( $r["picture"] && file_exists( "data/category/" . $r["picture"] ) ) {
        unlink( "data/category/" . $r["picture"] );
    }

    $q = db_query( "SELECT categoryID FROM " . CATEGORIES_TABLE . " WHERE parent=" . (int)$parent );
    while ( $row = db_fetch_row( $q ) ) {
        $qp = db_query( "SELECT productID FROM " . PRODUCTS_TABLE . " WHERE categoryID=" . (int)$row["categoryID"] );
        while ( $picture = db_fetch_row( $qp ) ) {
            DeleteThreePictures2( $picture["productID"] );
        }
        db_query( "DELETE FROM " . PRODUCTS_TABLE . " WHERE categoryID=" . (int)$row["categoryID"] );
        _deleteSubCategories( $row["categoryID"] );
    }
    db_query( "DELETE FROM " . CATEGORIES_TABLE . " WHERE parent=" . (int)$parent );

}

// *****************************************************************************
// Purpose        deletes category
// Inputs
//                 $categoryID - ID of category to be deleted
// Remarks      delete also all subcategories, all prodoctes remove into root
// Returns        nothing
function catDeleteCategory( $categoryID ) {
    $result = false;
    if ( $categoryID ) {
        _deleteSubCategories( $categoryID );

        $q = db_query( "SELECT productID FROM " . PRODUCTS_TABLE . " WHERE categoryID=" . (int)$categoryID );
        if ( $picture = db_fetch_row( $q ) ) {
            DeleteThreePictures2( $picture["productID"] );
        }

        db_query( "DELETE FROM " . PRODUCTS_TABLE . " WHERE categoryID=" . (int)$categoryID );

        db_query( "DELETE FROM " . CATEGORIES_TABLE . " WHERE parent=" . (int)$categoryID );
        $q = db_query( "SELECT picture FROM " . CATEGORIES_TABLE . " WHERE categoryID=" . (int)$categoryID );
        $r = db_fetch_row( $q );
        if ( $r["picture"] && file_exists( "data/category/" . $r["picture"] ) ) {
            unlink( "data/category/" . $r["picture"] );}

        $res    = db_query( "DELETE FROM " . CATEGORIES_TABLE . " WHERE categoryID=" . (int)$categoryID );
        $result = ( !$res["resource"] ) ? false : true;
    }
    return $result;

}

function catGetFullMatchesCategory( $FindString ) {

    $res = null;

    $q = db_query( "SELECT count(*) FROM " . CATEGORIES_TABLE .
        " WHERE `name` LIKE '%" . (string)$FindString . "%';" );

    $row = db_fetch_row( $q );
    if ( $row[0] == 1 ) {
        // debugfile($FindString,"\$FindString=$FindString  category_functions.php:1039");
        $q1 = db_query( "SELECT categoryID,name,parent FROM " . CATEGORIES_TABLE .
            " WHERE `name` LIKE '%" . (string)$FindString . "%';" );
        $row1 = db_fetch_row( $q1 );
        $res  = $row1;
    }

    // if ( $res ) {
    //     debugfile( $res, "category_functions.php:1045 RESULTAT" );
    // }

    return $res;
}

### Переносим товары из категорий в НОВИНКАХ в постоянные категории в случае полного совпадения ИМЕН ВРЕМЕННОЙ и ПОСТОЯННОЙ КАТЕГОРИИ
function catMergeCategoriesWithSameName( $tempRootTiile = "Новинки НИКС", $killThemAll = true ) {

    $CategoriesTable = CATEGORIES_TABLE;
    $ProductsTable   = PRODUCTS_TABLE;

    $res = null;

    $sql = <<<SQL
SELECT
temp.name as temp_Name, permanent.name as permanent_Name,
temp.categoryID as temp_CatID,  permanent.categoryID as permanent_CatID,
temp.parent as temp_Parent, permanent.parent as permanent_Parent
FROM {$CategoriesTable} AS temp
LEFT JOIN {$CategoriesTable} AS permanent
ON temp.name LIKE permanent.name
WHERE ( (temp.parent = (SELECT categoryID FROM {$CategoriesTable} WHERE name ='{$tempRootTiile}')) AND (permanent.parent!=(SELECT categoryID FROM {$CategoriesTable} WHERE name ='{$tempRootTiile}')));
SQL;

    $q = db_query( $sql );

    while ( $row = db_fetch_assoc( $q ) ) {
        $res[] = $row;

        $sql1 = <<<SQL
UPDATE {$ProductsTable} SET
categoryID = {$row["permanent_CatID"]}
WHERE categoryID = {$row["temp_CatID"]}
SQL;
        $q1 = db_query( $sql1 );

        if ( $killThemAll ) {
            $sql2 = <<<SQL
DELETE FROM {$CategoriesTable}
WHERE categoryID = {$row["temp_CatID"]}
SQL;
            $q2 = db_query( $sql2 );
        }

    }

    // debug( $res );

    return count( $res );
}

/*
####
SELECT categoryID,name,parent FROM {$CategoriesTable}
WHERE name LIKE CONCAT('%',`name`,'%') AND parent = 28920;

SELECT categoryID,name,parent FROM {$CategoriesTable}
WHERE name LIKE CONCAT('%',`name`,'%') AND parent != 28920;

SELECT count(*) FROM UTF_categories
WHERE name LIKE CONCAT('%',`name`,'%') AND parent != 28920;

SELECT count(*) FROM UTF_categories
WHERE name LIKE CONCAT('%',`name`,'%') AND parent = 28920;

SELECT count(*) FROM UTF_categories
WHERE parent;
####
 */

// function catGetFullMatchesCategory_lowercase( $FindString ) {

//     $res = null;
//     $q   = db_query( "SELECT count(*) FROM " . CATEGORIES_TABLE .
//         " WHERE LOWER(`name`) LIKE '%" . (trim( strtolower( (string)$FindString ) )) . "%';" );

//     $row = db_fetch_row( $q );
//     if ( $row[0] == 1 ) {
//         // debugfile($FindString,"\$FindString=$FindString  category_functions.php:1039");
//         $q1 = db_query( "SELECT categoryID,name,parent FROM " . CATEGORIES_TABLE .
//             " WHERE LOWER(`name`) LIKE '%" . (trim( strtolower( (string)$FindString ) )) . "%';" );
//         $row1 = db_fetch_row( $q1 );
//         // $res  = $row1["categoryID"];
//         $res=$row;
//     }
//     if ( $res ) {
//         debugfile( $res, "cart_functions.php:1045 RESULTAT" );
//     }

//     return $res;
// }

?>