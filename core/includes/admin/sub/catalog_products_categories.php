<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2019-09-05       #
#          http://nixby.pro              #
##########################################

//products and categories tree view

if ( !strcmp( $sub, "products_categories" ) ) {
    if ( CONF_BACKEND_SAFEMODE != 1 && ( !isset( $_SESSION["log"] ) || !in_array( 1, $relaccess ) ) ) //unauthorized
    {
        $smarty->assign( "admin_sub_dpt", "error_forbidden.tpl.html" );
    } else {

        function _getUrlToSubmit() {
            $res = ADMIN_FILE . "?dpt=catalog&sub=products_categories";
            if ( isset( $_GET["categoryID"] ) ) {
                $res .= "&categoryID=" . $_GET["categoryID"];
            }

            if ( isset( $_GET["offset"] ) ) {
                $res .= "&offset=" . $_GET["offset"];
            }

            if ( isset( $_GET["sort"] ) ) {
                $res .= "&sort=" . $_GET["sort"];
            }

            if ( isset( $_GET["sort_dir"] ) ) {
                $res .= "&sort_dir=" . $_GET["sort_dir"];
            }

            if ( isset( $_GET["search_criteria"] ) ) {
                $res .= "&search_criteria=" . $_GET["search_criteria"];
            }

            if ( isset( $_GET["search_value"] ) ) {
                $res .= "&search_value=" . $_GET["search_value"];
            }

            if ( isset( $_POST["search_criteria"] ) ) {
                $res .= "&search_criteria=" . $_POST["search_criteria"];
            }

            if ( isset( $_POST["search_value"] ) ) {
                $res .= "&search_value=" . $_POST["search_value"];
            }

            if ( isset( $_GET["search"] ) ) {
                $res .= "&search=" . $_GET["search"];
            }

            if ( isset( $_GET["show_all"] ) ) {
                $res .= "&show_all=" . $_GET["show_all"];
            }

            return $res;
        }

        function _getUrlToDelete() {
            return _getUrlToSubmit();
        }

        function _getUrlToCategoryTreeExpand() {
            return _getUrlToSubmit();
        }

        function _getUrlToNavigate() {
            $res = ADMIN_FILE . "?dpt=catalog&sub=products_categories";
            if ( isset( $_GET["categoryID"] ) ) {
                $res .= "&categoryID=" . $_GET["categoryID"];
            }

            if ( isset( $_GET["offset"] ) ) {
                $res .= "&offset=" . $_GET["offset"];
            }

            if ( isset( $_GET["sort"] ) ) {
                $res .= "&sort=" . $_GET["sort"];
            }

            if ( isset( $_GET["sort_dir"] ) ) {
                $res .= "&sort_dir=" . $_GET["sort_dir"];
            }

            if ( isset( $_GET["search_criteria"] ) ) {
                $res .= "&search_criteria=" . $_GET["search_criteria"];
            }

            if ( isset( $_GET["search_value"] ) ) {
                $res .= "&search_value=" . $_GET["search_value"];
            }

            if ( isset( $_POST["search_criteria"] ) ) {
                $res .= "&search_criteria=" . $_POST["search_criteria"];
            }

            if ( isset( $_POST["search_value"] ) ) {
                $res .= "&search_value=" . $_POST["search_value"];
            }

            if ( isset( $_GET["search"] ) ) {
                $res .= "&search=" . $_GET["search"];
            }

            return $res;
        }

        function _getUrlToSort() {
            return _getUrlToSubmit();

            /*$res = ADMIN_FILE."?dpt=catalog&sub=products_categories";
        if ( isset($_GET["categoryID"]) )
        $res .= "&categoryID=".$_GET["categoryID"];
        return $res;*/
        }

        $callBackParam = array();
// BEGIN Percentable-change
        if ( isset( $_GET["change_price"] ) ) {
            if ( strlen( $_POST["new_price"] ) > 0 and preg_match( ";^[+-]*[0-9]+[.][0-9]+|[+-]*[0-9]+$;", $_POST["new_price"] ) ) {

                $new_price = trim(  ( $_POST["new_price"] ) );

                $catid    = $_GET["categoryID"];
                $func     = 'ROUND';
                $cc       = currGetCurrencyByID( $_SESSION["current_currency"] );
                $roundval = isset( $cc["roundval"] ) ? $cc["roundval"] : 2;
                if ( (int)$roundval == 0 ) {
                    $func = 'TRUNCATE';
                }

                //if((int)$roundval == -1) $func = 'TRUNCATE';
                if ( $_POST["ed"] == 2 ) {
                    $expression = "(Price*$new_price/100)";
                } elseif ( $_POST["ed"] == 3 ) {
                    $expression = "($new_price)";
                }

                if ( isset( $_POST['change_subcat'] ) ) {
                    // BEGIN модификация для апдейта и дочерних категорий тоже
                    function change_recursiveCat( $catID, $arrayID = array() ) {
                        global $fc;
                        foreach ( $fc as $val ) {
                            if ( $val['parent'] == $catID ) {
                                $arrayID = change_recursiveCat( $val['categoryID'], $arrayID );
                            }
                        }
                        $arrayID[] = $catID;
                        return $arrayID;
                    }

                    db_query( "UPDATE " . PRODUCTS_TABLE . " SET Price=" . $func . "(Price+" . $expression . ", $roundval) WHERE categoryID IN (" . implode( ",", change_recursiveCat( $catid ) ) . ")", 1 );
                    // END модификация для апдейта и дочерних категорий тоже
                } else {
                    db_query( "UPDATE " . PRODUCTS_TABLE . " SET Price=" . $func . "(Price+" . $expression . ", $roundval) WHERE categoryID=" . (int)$catid, 1 );
                }
            }
        }
// END Percentable-change

// BEGIN RoundPrice
        //Округление действует согласно указанной позиции. Например: Цена товара 3029.64 рубля. Если в округлении указано 2, то цена товара не изменится, так как округление идет до двух знаков в дробной части. Если будет указано 0, то цена товара будет выводиться как 3029 рублей. Если указано -1, то цена товара будет выводиться как 3030 поскольку округление задано в один знак до дробной части.

        $round_str = "UPDATE " . PRODUCTS_TABLE . " SET Price=IF(MOD(Price,100)>60,TRUNCATE(Price,-2)+90,TRUNCATE(Price,-2)+50) ";

        if ( isset( $_GET["round_price"] ) && isset( $_GET['categoryID'] ) ) {

            if ( isset( $_POST['round_subcat'] ) ) {
                function round_recursiveCat( $catID, $arrayID = array() ) {
                    global $fc;
                    foreach ( $fc as $val ) {
                        if ( $val['parent'] == $catID ) {
                            $arrayID = round_recursiveCat( $val['categoryID'], $arrayID );
                        }
                    }

                    $arrayID[] = $catID;
                    return $arrayID;}
                db_query( $round_str . "WHERE categoryID IN (" . implode( ",", round_recursiveCat( $_GET['categoryID'] ) ) . ")" );
            } else {
                db_query( $round_str . "WHERE categoryID=" . $_GET['categoryID'] );
            }

        }
// END RoundPrice

        if ( isset( $_GET["search"] ) ) {
            if ( isset( $_POST["search_value"] ) ) //"Find" button pressed
            {
                $search_value = $_POST["search_value"];
            } elseif ( isset( $_GET["search_value"] ) ) //after search is made customer pushed 'delete' button, changed sort order, etc.
            {
                $search_value = $_GET["search_value"];
            }

            $array              = explode( " ", $search_value );
            $search_value_array = array();
            foreach ( $array as $val ) {
                if ( $val != "" ) {
                    $search_value_array[] = $val;
                }

            }

            if ( isset( $_POST["search_criteria"] ) ) {
                $search_criteria = $_POST["search_criteria"];
            } elseif ( isset( $_GET["search_criteria"] ) ) {
                $search_criteria = $_GET["search_criteria"];
            }

            if ( $search_criteria == "name" ) {
                $callBackParam["name"] = $search_value_array;
            }

            if ( $search_criteria == "product_code" ) {
                $callBackParam["product_code"] = $search_value_array;
            }

// BEGIN Search-in-description
            if ( $search_criteria == "description" ) {
                $callBackParam["description"] = $search_value_array;
            }
// END Search-in-description

            $smarty->assign( "search_criteria", $search_criteria );
            $smarty->assign( "search_value", $search_value );
            $smarty->assign( "searched_done", 1 );
        }

        if ( !isset( $_SESSION["expcat"] ) ) {
            $_SESSION["expcat"] = array( 1 );
        }

        if ( isset( $_GET["expandCat"] ) ) {
            catExpandCategory( $_GET["expandCat"], "expcat" );
        }

        if ( isset( $_GET["shrinkCat"] ) ) {
            catShrinkCategory( $_GET["shrinkCat"], "expcat" );
        }

        if ( isset( $_GET["shrinkCatm"] ) ) {
            catShrinkCategorym( "expcat" );
        }

        if ( isset( $_GET["expandCatp"] ) ) {
            catExpandCategoryp( "expcat" );
        }

        $c = catGetCategoryCList( $_SESSION["expcat"] );
        $smarty->assign( "categories", $c );

        if ( isset( $_POST["ADD_COMMAND"] ) && ( $_POST["ADD_COMMAND"] == "prod_off" || $_POST["ADD_COMMAND"] == "prod_on" || $_POST["ADD_COMMAND"] == "prod_dell" || $_POST["ADD_COMMAND"] == "prod_move" || $_POST["ADD_COMMAND"] == "change_price_selected" || $_POST["ADD_COMMAND"] == "roundpriceselected" ) ) {
            ## ОБРАБОТКА КОММАНД С ВЫБРАННЫМИ ПРОДУКТАМИ
            ## ОБРАБОТКА КОММАНД С ВЫБРАННЫМИ ПРОДУКТАМИ
            include_once "core/includes/admin/sub/catalog_products_categories__selectedProductsCommand.php";
            ## ОБРАБОТКА КОММАНД С ВЫБРАННЫМИ ПРОДУКТАМИ КОНЕЦ
            ## ОБРАБОТКА КОММАНД С ВЫБРАННЫМИ ПРОДУКТАМИ КОНЕЦ

        } elseif ( isset( $_GET["delete_all_products"] ) ) {
            if ( CONF_BACKEND_SAFEMODE ) //this action is forbidden when SAFE MODE is ON
            {
                Redirect( _getUrlToSubmit() . "&safemode=yes" );
            }

            if ( DeleteAllProductsOfThisCategory( (int)$_GET["categoryID"] ) ) {
                Redirect( ADMIN_FILE . "?dpt=catalog&sub=products_categories&categoryID=" .
                    $_GET["categoryID"] );
            } else {
                Redirect( ADMIN_FILE . "?dpt=catalog&sub=products_categories&categoryID=" .
                    $_GET["categoryID"] .
                    "&couldntToDeleteThisProducts=1" );
            }

        } elseif ( isset( $_POST["products_update"] ) ) {
            ## ОБНОВЛЕНИЕ ПРОДУКТОВ
            ## ОБНОВЛЕНИЕ ПРОДУКТОВ
            include_once "core/includes/admin/sub/catalog_products_categories__updateProductsData.php";
            ## ОБНОВЛЕНИЕ ПРОДУКТОВ КОНЕЦ
            ## ОБНОВЛЕНИЕ ПРОДУКТОВ КОНЕЦ
        } elseif ( isset( $_GET["terminate"] ) ) //delete product
        {
            if ( CONF_BACKEND_SAFEMODE ) //this action is forbidden when SAFE MODE is ON
            {
                Redirect( _getUrlToSubmit() . "&safemode=yes" );
            }

            if ( DeleteProduct( $_GET["terminate"] ) ) {
                Redirect( _getUrlToSubmit() );
            } else {
                Redirect( _getUrlToSubmit() . "&couldntToDelete=1" );
            }

        }

        if ( isset( $_POST["update_gc_value"] ) ) {
            @set_time_limit( 60 * 4 );
            update_psCount( 1 );
            Redirect( ADMIN_FILE . "?dpt=catalog&sub=products_categories&categoryID=" . (int)$_POST["categoryID"] );
        }

        // update button pressed -- НАДО СДЕЛАТЬ Кнопку
        if ( isset( $_GET["force_update_gc_value"] ) ) {
            @set_time_limit( 60 * 10 );
            update_psCount( 1 );
            arrangeSortOrderByDefault( 1);
            Redirect( ADMIN_FILE . "?dpt=catalog&sub=products_categories&toastr_message_onload=ok&categoryID=" . (int)$_GET["categoryID"] . "&expandCat=" . (int)$_GET["categoryID"] );
        }

        //calculate how many products are there in root category
        $q   = db_query( "SELECT count(*) FROM " . PRODUCTS_TABLE . " WHERE categoryID=1" );
        $cnt = db_fetch_row( $q );
        $smarty->assign( "products_in_root_category", $cnt[0] );

        //show category name as a title
        $row = array();
        if ( !isset( $_GET["categoryID"] ) && !isset( $_POST["categoryID"] ) ) {
            $categoryID = 1;
            $row[0]     = ADMIN_CATEGORY_ROOT;
        } else {
            //go to the root if category doesn't exist
            $categoryID = isset( $_GET["categoryID"] ) ? $_GET["categoryID"] : $_POST["categoryID"];
            $q          = db_query( "SELECT name FROM " . CATEGORIES_TABLE . " WHERE categoryID=" . (int)$categoryID );
            $row        = db_fetch_row( $q );
            if ( !$row ) {
                $categoryID = 0;
                $row[0]     = ADMIN_CATEGORY_ROOT;
            }
        }

        if ( !isset( $_GET["search"] ) ) {
            $smarty->assign( "products_count_category", catGetCategoryProductCount( $categoryID, false ) );
        }

        $smarty->assign( "categoryID", $categoryID );
        $smarty->assign( "category_name", $row[0] );

        $count_row = 0;
        $offset    = 0;
        $products  = null;

        if ( isset( $_GET["sort"] ) ) {
            $callBackParam["sort"] = $_GET["sort"];
            if ( isset( $_GET["sort_dir"] ) ) {
                $callBackParam["direction"] = $_GET["sort_dir"];
            }

        }
        if ( !isset( $_GET["search"] ) ) {
            $callBackParam["categoryID"] = $categoryID;
        }
        $callBackParam["searchInSubcategories"] = false;

        $count = 0;

        $navigatorHtml = GetNavigatorHtmlNEW(
            _getUrlToNavigate(),
            20,
            'prdSearchProductByTemplateAdmin',
            $callBackParam,
            $products,
            $offset,
            $count
        );

        for ( $i = 0; $i < count( $products ); $i++ ) {
            $products[$i]["picture_count"]   = GetPictureCount( $products[$i]["productID"] );
            $products[$i]["thumbnail_count"] = GetThumbnailCount( $products[$i]["productID"] );
            $products[$i]["enlarged_count"]  = GetEnlargedPictureCount( $products[$i]["productID"] );
        }

        $smarty->assign( "navigatorHtml", $navigatorHtml );

        $smarty->hassign( "urlToSort", _getUrlToSort() );
        $smarty->hassign( "urlToSubmit", _getUrlToSubmit() );
        $smarty->hassign( "urlToDelete", _getUrlToDelete() );
        $smarty->hassign( "urlToCategoryTreeExpand", _getUrlToCategoryTreeExpand() );

        $smarty->assign( "searched_count",
            str_replace( "{N}",
                count( $products ), ADMIN_N_RECORD_IS_SEARCHED ) );

// BEGIN изменение характеристик прямо в таблице товаров
        if ( db_fetch_row( db_query( "SHOW COLUMNS FROM " . CATEGORIES_TABLE . " LIKE 'allowed_parameters'" ) ) ) {
            $row   = db_fetch_assoc( db_query( "SELECT allowed_parameters FROM " . CATEGORIES_TABLE . " WHERE categoryID=" . $_GET['categoryID'] ) );
            $where = $row['allowed_parameters'] ? " WHERE optionID IN (" . $row['allowed_parameters'] . ")" : "";
        } else {
            $where = "";
        }

        $data = db_query( "SELECT optionID,name FROM " . PRODUCT_OPTIONS_TABLE . $where . " ORDER BY sort_order,name" );
        while ( $row = db_fetch_assoc( $data ) ) {
            $options[] = $row;
        }

        $smarty->assign( "options", $options );

        if ( isset( $_SESSION['optionID'] ) ) {
            $_GET['optionID'] = $_SESSION['optionID'];
            unset( $_SESSION['optionID'] );
        }

        if ( isset( $_GET['optionID'] ) && ( $optionID = (int)$_GET['optionID'] ) > 0 && isset( $products ) && count( $products ) > 0 ) {
            $data = db_query( "SELECT variantID,option_value FROM " . PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE . " WHERE optionID=$optionID ORDER BY sort_order,option_value" );
            while ( $row = db_fetch_assoc( $data ) ) {
                $variants[] = $row;
            }

            $smarty->assign( "variants", $variants );

            foreach ( $products as $key => $product ) {
                if ( $row = db_fetch_assoc( db_query( "SELECT option_value, option_type FROM " . PRODUCT_OPTIONS_VALUES_TABLE . " WHERE optionID=$optionID AND productID=" . $product['productID'] . " LIMIT 1" ) ) ) {
                    if ( $row['option_type'] == 0 ) {
                        if ( empty( $row['option_value'] ) ) {
                            $products[$key]['type'] = 0;
                            continue;}
                        $products[$key]['type']  = 1;
                        $products[$key]['value'] = $row['option_value'];
                    } else {
                        $count                   = 0;
                        $products[$key]['value'] = $variants;
                        if ( is_array( $products[$key]['product_extra'] ) ) {
                            $product_extra_key = -1;
                            foreach ( $products[$key]['product_extra'] as $key1 => $val1 ) {
                                if ( $val1['optionID'] == $optionID ) // в характеристиках товара нашлась заданная характеристика
                                {
                                    $product_extra_key = $key1;
                                }
                            }

                            // запомним номер этого элемента массива

                            $sets = array();
                            if ( $product_extra_key > -1 ) {
                                foreach ( $products[$key]['product_extra'][$product_extra_key]['values_to_select'] as $key1 => $val1 ) {
                                    $sets[] = $val1['variantID'];
                                }
                            }

                            // составим список вариантов этой хар-ки у этого товара

                            foreach ( $products[$key]['value'] as $key1 => $val1 ) {
                                if ( in_array( $val1['variantID'], $sets ) ) {
                                    $products[$key]['value'][$key1]['select'] = ' selected';
                                    $count++;
                                }
                            }

                        }
                        $products[$key]['type'] = $count > 1 ? 3 : 2;
                    }
                } else {
                    $products[$key]['type'] = 0;
                }

            }
        }
// END изменение характеристик прямо в таблице товаров

        $product_category_path = array();
        $product_category_path = catCalculatePathToCategory( $categoryID );
        $currentCategoryParent = (int)$product_category_path[count( $product_category_path ) - 1][1];

// $adminerHREF="
        // http://nixminsk.by/adminer.php?username=nixby_dbadmin&db=nixby_UTF8&select=UTF_categories
        // &columns[0][fun]=
        // &columns[0][col]=
        // &where[0][col]=categoryID
        // &where[0][op]=%3D
        // &where[0][val]=134010+
        // &where[01][col]=
        // &where[01][op]=%3D
        // &where[01][val]=
        // &order[0]=
        // &limit=50
        // &text_length=100";

        //products list
        $smarty->assign( "products", $products );
        //set main template
        $smarty->assign( "admin_sub_dpt", "catalog_products_categories.tpl.html" );
        //calculate a path to the category
        $smarty->assign( "product_category_path", $product_category_path );
        $smarty->assign( "currentCategoryParent", $currentCategoryParent );

        $cats     = catGetCategoryCListMin();
        $smarty->assign( "cats", $cats );

    }
}
?>