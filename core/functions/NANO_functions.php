<?php

function maxLimit( $d ) {
    $number     = abs( $d );
    $digitCount = floor( log( $number ) / log( 10 ) );
    $digPow     = pow( 10, $digitCount );

    $res = ( $digPow != 0 ) ? ceil( $d / $digPow ) * $digPow : $d;
    return $res;
}
function minLimit( $d ) {
    $number     = abs( $d );
    $digitCount = floor( log( $number ) / log( 10 ) );
    $digPow     = pow( 10, $digitCount );

    $res = ( $digPow != 0 ) ? floor( $d / $digPow ) * $digPow : $d;
    return $res;
}

function NANO_filter_instock( $option_value_selected = 0, $dbf = "`in_stock`" ) {
    $res = "";

    $html = '
<option value="0" selected>Без разницы</option>
<option value="1">Есть на складе</option>
<option value="2">Доступно для заказа</option>
<option value="3">Только Предзаказ</option>
<option value="4">Ожидаем/В резерве</option>
<option value="5">Нет на складе</option>
<option value="6">Поставка прекращена</option>
<option value="7">Много - больше 10шт</option>
<option value="8">Мало - меньше 5шт</option>
<option value="9">Под заказ менее 2-х недель</option>
<option value="10">Под заказ более 2-х недель</option>
';

    switch ( $option_value_selected ) {
        case 0:
            $res = "";
            break;
        case 1:
            $res = "{$dbf} > 0";
            break;
        case 2:
            // $res = " ( ({$dbf} > 0) OR ({$dbf} <=-1) AND ({$dbf} !=-1000)) ";
            $res = "{$dbf}!=0 and {$dbf}!=-1000";
            break;
        case 3:
            $res = "{$dbf}<=-2 and {$dbf}!=-1000";
            break;
        case 4:
            $res = "{$dbf}=-1";
            break;
        case 5:
            $res = "{$dbf}=0";
            break;
        case 6:
            $res = "{$dbf}=-1000";
            break;
        case 7:
            $res = "{$dbf}>10";
            break;
        case 8:
            $res = "{$dbf}<=5 and {$dbf}>0";
            break;
        case 9:
            $res = "{$dbf}>=-14 and {$dbf}<-1";
            break;
        case 10:
            $res = "{$dbf}>-1000 and {$dbf}<-14 and {$dbf}<-1";
            break;
        default:
            $res = "";
            break;
            /* endswitch; */
    }

    return $res;
}

function NANO_generateClauses( $params ) {

    $res = array(
        "DEDICATED"   => "",
        "PRICELIMITS" => "",
        "OVERALL"     => "",
        "SUMMARY"     => "",
    );

    if ( is_array( $params ) && count( $params ) ) {

        // objectlog( $params );

        _deletePercentSymbol( $params ); // как? это же массив

        $searchstring_clause = "";
        if ( isset( $params["searchstring"] ) and $params["searchstring"] != "" ) {
            $ccode              = array();
            $cname              = array();
            $cbrief_description = array();
            foreach ( explode( " ", trim( $params["searchstring"] ) ) as $word ) {

                if ( strlen( $word ) >= 1 ) {
                    $ccode[]              = "lower(product_code) LIKE '%" . xToText( strtolower( $word ) ) . "%'";
                    $cname[]              = "lower(name) LIKE '%" . xToText( strtolower( $word ) ) . "%'";
                    $cbrief_description[] = "lower(brief_description) LIKE '%" . xToText( strtolower( $word ) ) . "%'";
                }
            }
            $searchstring_clause = "(" . implode( " and ", $ccode ) . ")" . " OR " . "(" . implode( " and ", $cname ) . ")" . " OR " . "(" . implode( " and ", $cbrief_description ) . ")";
        }

        $product_code_clause = "";
        if ( isset( $params["product_code"] ) and $params["product_code"] != "" ) {
            $product_code_clause = "lower(product_code) LIKE '%" . xToText( strtolower( $params["product_code"] ) ) . "%'";
            if ( $product_code_clause != "" ) {
                if ( $searchstring_clause != "" ) {
                    $searchstring_clause .= " AND ({$product_code_clause})";
                } else {
                    $searchstring_clause .= "{$product_code_clause}";
                }
            }
        }
        $product_name_clause = "";
        if ( isset( $params["product_name"] ) and $params["product_name"] != "" ) {
            $product_name_clause = "lower(name) LIKE '%" . xToText( strtolower( $params["product_name"] ) ) . "%'";
            if ( $product_name_clause != "" ) {
                if ( $searchstring_clause != "" ) {
                    $searchstring_clause .= " AND ({$product_name_clause})";
                } else {
                    $searchstring_clause .= "{$product_name_clause}";
                }
            }
        }
        $brief_description_clause = "";
        if ( isset( $params["brief_description"] ) and $params["brief_description"] != "" ) {
            $brief_description_clause = "lower(brief_description) LIKE '%" . xToText( strtolower( $params["brief_description"] ) ) . "%'";
            if ( $brief_description_clause != "" ) {
                if ( $searchstring_clause != "" ) {
                    $searchstring_clause .= " AND ({$brief_description_clause})";
                } else {
                    $searchstring_clause .= "{$brief_description_clause}";
                }
            }
        }

        $stopwords_clause = "";
        if ( isset( $params["stopwords"] ) and $params["stopwords"] != "" ) {
            $cname = array();
            foreach ( explode( " ", trim( $params["stopwords"] ) ) as $stopword ) {

                if ( strlen( $stopword ) >= 1 ) {
                    $cname[] = "lower(name) NOT LIKE '%" . xToText( strtolower( $stopword ) ) . "%'";
                }
            }
            if ( is_array( $cname ) && count( $cname ) ) {
                $stopwords_clause = '' . implode( ' and ', $cname ) . '';
            }
        }
        $crosswords_clause = "";
        if ( isset( $params["crosswords"] ) and $params["crosswords"] != "" ) {
            $cname = array();
            foreach ( explode( " ", trim( $params["crosswords"] ) ) as $stopword ) {

                if ( strlen( $stopword ) >= 1 ) {
                    $cname[] = "lower(name) LIKE '%" . xToText( strtolower( $stopword ) ) . "%'";
                }
            }
            if ( is_array( $cname ) && count( $cname ) ) {
                $crosswords_clause = '' . implode( ' or ', $cname ) . '';
            }
        }
        if ( $searchstring_clause && ( $crosswords_clause || $stopwords_clause ) ) {
            if ( $crosswords_clause ) {
                $searchstring_clause = "(  {$searchstring_clause}  OR  {$crosswords_clause}  )";
            }

            if ( $stopwords_clause ) {
                $searchstring_clause = "(( {$searchstring_clause} )  AND  ( {$stopwords_clause} ))";
            }
        } else {
            if ( $crosswords_clause ) {
                $searchstring_clause = "{$crosswords_clause}";
            }

            if ( $stopwords_clause ) {
                if ( $crosswords_clause ) {
                    $searchstring_clause = "(( {$searchstring_clause} )  AND  ( {$stopwords_clause} ))";
                } else {
                    $searchstring_clause = "{$stopwords_clause}";
                }
            }
        }

        $categories_clause = "";
        if ( isset( $params["categoryID"] ) && ( (int)$params["categoryID"] > 1 ) ) {
            $categories_clause = "`categoryID` = '{$params['categoryID']}'";

            $category_array        = array();
            $searchInSubcategories = ( isset( $params["searchInSubcategories"] ) && $params["searchInSubcategories"] == "true" );
            $category_array        = ( $searchInSubcategories ) ? recursiveCat( $params["categoryID"] ) : null;
            if (  ( count( $category_array ) > 1 ) && is_array( $category_array ) && !empty( $category_array ) ) {
                $category_list     = implode( ",", $category_array );
                $categories_clause = "`categoryID` IN ({$category_list})";
            }
        }

        $cats_clause = "";
        if ( isset( $params["cats_include"] ) && ( strlen( $params["cats_include"] ) > 0 ) ) {
            $cats_array    = array();
            $category_list = $params['cats_include'];
            $cats_array    = explode( ",", $category_list );

            if (  ( count( $cats_array ) > 1 ) && is_array( $cats_array ) ) {
                $cats_clause = "categoryID IN ({$category_list})";
            } else {
                $cats_clause = "categoryID   = {$params['cats_include']}";
            }
        }

        // if ( $categories_clause && $cats_clause !="") {
        //     $categories_clause = "$categories_clause OR {$cats_clause}";
        // }

        // $cats_clause = "";
        // if ( isset( $params["cats_exclude"] ) && ( strlen( $params["cats_exclude"] ) ) ) {
        //     $cats_array    = array();
        //     $category_list = $params['cats_exclude'];
        //     $cats_array    = explode( ",", $category_list );
        //     if ( is_array( $cats_array ) ) {
        //         $cats_clause = "`categoryID` NOT IN ({$category_list})";
        //     } else {
        //         $cats_clause = "`categoryID`!={$category_list}";
        //     }
        // }
        // if (  isset( $params["cats_exclude"] && $categories_clause && $cats_clause ) {
        //     $categories_clause = "{$categories_clause} AND {$cats_clause}";
        // } elseif ( $params["cats_exclude"] && $cats_clause ) {
        //     $categories_clause = "{$cats_clause}";
        // }

        $in_stock_clause = "";
        if ( isset( $params['in_stock'] ) && ( (int)$params["in_stock"] != 0 ) ) {
            $dbf             = "`in_stock`";
            $in_stock_clause = NANO_filter_instock( (int)$params["in_stock"], $dbf );
        }

        $Price_clause = "";
        if (
            ( isset( $params['price_from'] ) || isset( $params['price_to'] ) ) AND
            (  ( $params['price_from'] >= 0 ) AND ( $params['price_to'] >= 0 ) )
        ) {
            $dbf = "Price";

            if ( $params['price_from'] > $params['price_to'] ) {
                $price_from_e         = $params['price_from'];
                $price_to_e           = $params['price_to'];
                $params['price_from'] = $price_to_e;
                $params['price_to']   = $price_from_e;
            }

            $price_from   = ( $params['price_from'] ) ? floor( $params['price_from'] / $params["tpl_currency_value"] ) : 0;
            $price_to     = ( $params['price_to'] ) ? ceil( $params['price_to'] / $params["tpl_currency_value"] ) : 100000;
            $Price_clause = "{$dbf} >= {$price_from} and {$dbf} <= {$price_to}";
        }

        $enable_clause = "";
        if ( isset( $params['show_disabled'] ) ) {
            $enable_clause = "";
        } elseif ( isset( $params['show_only_disabled'] ) ) {
            $enable_clause = "`enabled` = 0";
        } else {
            $enable_clause = "`enabled` = 1";
        }

    }

    $OVERALL      = "";
    $where_clause = "";
    if ( $enable_clause ) {
        $where_clause .= ( $where_clause == "" ) ? "    ( {$enable_clause} )" : "  AND  ( {$enable_clause} )";
    }
    if ( $categories_clause ) {
        $where_clause .= ( $where_clause == "" ) ? "    ( {$categories_clause} )" : "  AND  ( {$categories_clause} )";
    }
    // if ( $in_stock_clause ) {
    //     $where_clause .= ( $where_clause == "" ) ? "    ( {$in_stock_clause} )" : "  AND  ( {$in_stock_clause} )";
    // }
    $OVERALL = $where_clause;

    $DEDICATED    = "";
    $where_clause = "";
    if ( $searchstring_clause ) {
        $where_clause .= ( $where_clause == "" ) ? "    ( {$searchstring_clause} )" : "  AND  ( {$searchstring_clause} )";
    }
    if ( $in_stock_clause ) {
        $where_clause .= ( $where_clause == "" ) ? "    ( {$in_stock_clause} )" : "  AND  ( {$in_stock_clause} )";
    }
    // if ( $categories_clause ) {
    //     $where_clause .= ( $where_clause == "" ) ? "    ( {$categories_clause} )" : "  AND  ( {$categories_clause} )";
    // }

    if ( $cats_clause ) {
        $where_clause .= ( $where_clause == "" ) ? "    ( {$cats_clause} )" : "  AND  ( {$cats_clause} )";
    }
    $DEDICATED = $where_clause;

    $SUMMARY      = "";
    $where_clause = "";
    if ( $enable_clause ) {
        $where_clause .= ( $where_clause == "" ) ? "    ( {$enable_clause} )" : "  AND  ( {$enable_clause} )";
    }
    if ( $searchstring_clause ) {
        $where_clause .= ( $where_clause == "" ) ? "    ( {$searchstring_clause} )" : "  AND  ( {$searchstring_clause} )";
    }
    if ( $in_stock_clause ) {
        $where_clause .= ( $where_clause == "" ) ? "    ( {$in_stock_clause} )" : "  AND  ( {$in_stock_clause} )";
    }
    if ( $categories_clause ) {
        $where_clause .= ( $where_clause == "" ) ? "    ( {$categories_clause} )" : "  AND  ( {$categories_clause} )";
    }

    if ( $cats_clause ) {
        $where_clause .= ( $where_clause == "" ) ? "    ( {$cats_clause} )" : "  AND  ( {$cats_clause} )";
    }
    $SUMMARY = $where_clause;

    $PRICELIMITS  = "";
    $where_clause = "";
    if ( $Price_clause ) {
        $where_clause .= ( $where_clause == "" ) ? "    ( {$Price_clause} )" : "  AND  ( {$Price_clause} )";
    }
    $PRICELIMITS = $where_clause;

    $res = array(
        "DEDICATED"   => $DEDICATED,
        "PRICELIMITS" => $PRICELIMITS,
        "OVERALL"     => $OVERALL,
        "SUMMARY"     => $SUMMARY,
    );

    // console( $res["DEDICATED"] );
    // console( $res["SUMMARY"] );
    return $res;
}

function phpmakeBuyButtonBS5( $data ) {
    $out_html       = "";
    $form_html      = "";
    $is_fake_clause = "";
    $doLoadLink     = "";
    $btn_class      = "";

    if ( $data['productID'] ) {
        // $instock = $data['in_stock']['qnt'];
        $instock = $data["in_stock_qnt"];

        if ( $instock <= -1 ) {
            $is_fake_clause = "&is_fake=yes";
        }

        if ( $instock > 0 ) {
            $btn_class = "btn btn-success btn-lg";
        } elseif ( $instock == -1 ) {
            $btn_class = "btn btn-secondary btn-lg";
        } elseif ( $instock < -1 ) {
            $btn_class = "btn btn-outline-success btn-lg";
        }

        if (  ( $instock != 0 ) && ( $instock != -1000 ) ) {
            $action = ( CONF_MOD_REWRITE )
            ? "product_{$data['productID']}.html"
            : "index.php?productID={$data['productID']}";

            $form_html = <<<HTML
<form
action="{$action}"
class="row g-1 align-items-center"
name="HiddenFieldsForm_{$data['productID']}"
id="HiddenFieldsForm_{$data['productID']}"
method="post"
role="form"
>
        <label for="multyaddcount" class="col col-form-label col-form-label-lg"><span class="h3">Заказать</span></label>
        <div class="col">
            <div class="input-group input-group-lg">
                <input type="number" min = "1" max = "9999" step="1" name="multyaddcount" class="form-control text-end" value="1" style="min-width: 2rem!important;max-width: 6rem!important;">
                <button class="{$btn_class}" type="button" name="buttonAddToCart" data-is_fake_clause="{$is_fake_clause}" data-productID="{$data['productID']}" data-categoryID="{$data['categoryID']}">
                    <i class="bi bi-cart3"></i>
                </button>
            </div>
        </div>
        <div class="col" name="for-future-use">&nbsp;</div>

</form>
HTML;
        } elseif ( $instock == 0 ) {
            $form_html = '<div class="row g-3 align-items-center"><div class="col"><a href="mailto:2842895@gmail.com?subject=Заказ&body=Запрос поставки товара: ' . $data['name'] . '" class="btn btn-link text-decoration-none"><span class="h3"><i class="bi bi-envelope-check"></i> Запросить поставку товара e-mail: ' . '2842895@gmail.com' . ' </span></a></div></div>';
        } else {
            $form_html = '<div class="row g-3 align-items-center"><div class="col"><a href="mailto:2842895@gmail.com?subject=Заказ&body=Запрос поставки товара: ' . $data['name'] . '" class="btn btn-link text-decoration-none"><span class="h3"><i class="bi bi-envelope-check"></i> Снят с продажи </span></a></div></div>';
        }

    }

    $out_html = $form_html;
    return $out_html;
}

function NANO_catGetCategoryById( $categoryID ) {
    $q = db_query( "SELECT categoryID, name, parent, products_count, description, picture, " .
        " products_count_admin, sort_order, viewed_times, allow_products_comparison, allow_products_search, " .
        " show_subcategories_products, meta_description, meta_keywords, title " .
        " FROM " . CATEGORIES_TABLE . " WHERE categoryID=" . (int)$categoryID );
    $catrow = db_fetch_row( $q );
    return $catrow;
}

function getHtmlSuccessOrder( $ModuleObj = null, $orderID, $isadmin ) {
    $smarty                = new Smarty();
    $template              = isset( $_SESSION["CUSTOM_DESIGN"] ) ? $_SESSION["CUSTOM_DESIGN"] : CONF_DEFAULT_TEMPLATE;
    $smarty->template_dir  = "core/tpl/user/" . $template;
    $smarty->compile_id    = $template;
    $smarty->force_compile = true;

    $html = "";

    $Order     = ordGetOrder( $orderID );
    $companyID = (int)$Order["companyID"];

    $AdvancedOrderContent = ordAdvancedOrderContent(
        $orderID = $orderID,
        $Currency_rate = $Order["currency_value"],
        $shipping_cost = $Order["shipping_cost"],
        $order_discount = $Order["order_discount"],
        $nds_included = 0,
        $VAT_rate = DEFAULT_VAT_RATE,
        $Multiplier = 1,
        $dec = $Order["currency_round"],
        $currency_iso_3 = "BYN",
        $invoice_subdiscount = 0
    );

    if ( !$ModuleObj->ModuleConfigID ) {
        $sql                              = 'SELECT `module_id` FROM ' . MODULES_TABLE . ' WHERE `module_name`="' . xEscSQL( $ModuleObj->title ) . '" ';
        @list( $ModuleObj->ModuleConfigID ) = db_fetch_row( db_query( $sql ) );
    }

    // debugfile( $ModuleObj->title );

    $smarty->assign( "data", $AdvancedOrderContent );
    $smarty->assign( "Order", $Order );
    $html .= $smarty->fetch( "quick_cart__after_processing.tpl.html" );

    // directlog($html);
    return $html;
}

// company_title_clause
// company_name_clause
// company_unp_clause
// company_okpo_clause
// company_adress_clause
// company_bank_clause
// company_contacts_clause
// company_director_clause
// company_data_clause

function NANO_AddCompany( $data ) {

    if ( !$data ) {
        return false;
    }

    if ( isset( $data["company_title"] ) ) {
        $company_title = xToText( trim( $data["company_title"] ) );
    } else {
        $company_title = "Новая компания";
    }

    if ( isset( $data["company_name"] ) ) {
        $company_name = xToText( trim( $data["company_name"] ) );
    } else {
        $company_name = "Новая компания РЕКВИЗИТЫ ОДНОЙ СТРОКОЙ";
    }
    if ( isset( $data["company_unp"] ) ) {
        $company_unp = xToText( trim( preg_replace( '/\D/', "", $data["company_unp"] ) ) );
    } else {
        $company_unp = "404000404";
    }
    if ( isset( $data["company_okpo"] ) ) {
        $company_okpo = xToText( trim( preg_replace( '/\D/', "", $data["company_okpo"] ) ) );
    } else {
        $company_okpo = "";
    }
    if ( isset( $data["company_adress"] ) ) {
        $company_adress = xToText( trim( $data["company_adress"] ) );
    } else {
        $company_adress = "-";
    }
    if ( isset( $data["company_bank"] ) ) {
        $company_bank = xToText( trim( $data["company_bank"] ) );
    } else {
        $company_bank = "-";
    }
    if ( isset( $data["company_contacts"] ) ) {
        $company_contacts = xToText( trim( $data["company_contacts"] ) );
    } else {
        $company_contacts = "-";
    }
    if ( isset( $data["company_director"] ) ) {
        $company_director = xEscSQL( trim( $data["company_director"] ) );
    } else {
        $company_director = "";
    }

    if ( isset( $data["company_userdata"] ) ) {
        $company_data = xEscSQL( trim( $data["company_userdata"] ) );
    } else {
        $company_data = "";
    }

    if ( isset( $data["parsedUNP"] ) ) {
        $company_unp = xToText( trim( $data["parsedUNP"] ) );
    }

    $sql_field_list = "`read_only`,`company_title`, `company_name`, `company_unp`, `company_okpo`, `company_adress`, `company_bank`, `company_contacts`, `company_director`, `company_data`, `update_time`";

    $sql = "INSERT INTO " . COMPANIES_TABLE . " ( " . $sql_field_list . " ) VALUES (0, '{$company_title}', '{$company_name}', '{$company_unp}', '{$company_okpo}', '{$company_adress}', '{$company_bank}', '{$company_contacts}', '{$company_director}', '{$company_data}',  NOW() );";
    $res = array();
    $q   = db_query( $sql );

    $res["companyID"] = (int)db_insert_id();
    // $res["operation"] = "INSERT";

    if ( $q["mysql_info"] ) {
        $res["mysql_info"] = $q["mysql_info"];
    }
    return $res;
}

function NANO_UpdateCompany( $data, $companyID ) {
// objectlog($data);

    if ( !$companyID ) {
        return false;
    }
    if ( !$data ) {
        return false;
    }
    $res = array();
    if ( is_array( $data ) && count( $data ) ) {

        $company_title_clause = isset( $data["company_title"] )
        ? "`company_title` = '" . xToText( trim( $data["company_title"] ) ) . "', "
        : "";

        $company_name_clause = isset( $data["company_name"] )
        ? "`company_name` = '" . xToText( trim( $data["company_name"] ) ) . "', "
        : "";
        $company_unp_clause = isset( $data["company_unp"] )
        ? "`company_unp` = '" . xToText(  ( preg_replace( '/\D/', "", $data["company_unp"] ) ) ) . "', "
        : "";
        $company_okpo_clause = isset( $data["company_okpo"] )
        ? "`company_okpo` = '" . xToText(  ( preg_replace( '/\D/', "", $data["company_okpo"] ) ) ) . "', "
        : "";
        $company_adress_clause = isset( $data["company_adress"] )
        ? "`company_adress` = '" . xToText( trim( $data["company_adress"] ) ) . "', "
        : "";
        $company_bank_clause = isset( $data["company_bank"] )
        ? "`company_bank` = '" . xToText( trim( $data["company_bank"] ) ) . "', "
        : "";
        $company_contacts_clause = isset( $data["company_contacts"] )
        ? "`company_contacts` = '" . xToText( trim( $data["company_contacts"] ) ) . "', "
        : "";

        $company_director_clause = isset( $data["company_director"] )
        ? "`company_director` = '" . xEscSQL(  ( $data["company_director"] ) ) . "', "
        : "";

        $company_data_clause = isset( $data["company_userdata"] )
        ? "`company_data` = '" . xEscSQL( trim( $data["company_userdata"] ) ) . "', "
        : "";

        if ( isset( $data["parsedUNP"] ) ) {
            $company_unp_clause = "`company_unp` = '" . xToText(  ( $data["parsedUNP"] ) ) . "', ";
        }

        $read_only_clause = "`read_only` = 0, ";

        $sql = "UPDATE `" . COMPANIES_TABLE . "` SET " .
        "{$read_only_clause}" .
        "{$company_title_clause}" .
        "{$company_name_clause}" .
        "{$company_unp_clause}" .
        "{$company_okpo_clause}" .
        "{$company_adress_clause}" .
        "{$company_bank_clause}" .
        "{$company_contacts_clause}" .
        "{$company_director_clause}" .
        "{$company_data_clause}" .
        "`update_time` = NOW() WHERE `companyID` = " . (int)$companyID . ";";
        $q = db_query( $sql );
        if ( $q["mysql_info"] ) {
            $res["mysql_info"] = $q["mysql_info"];
        }
        if ( $companyID ) {
            $res["companyID"] = $companyID;
        }
        $res["operation"] = "UPDATE";

        return $res;
    }
}

function NANO_payment_process( $order ) {
//  Здесь можно следать запрос в налоговую на проверку компании
    /*
    $companyID          = $_POST["companyID"];
    $_SESSION["companyID"] = $companyID;
    // $smarty->assign( "last_order_companyID", $companyID );
    return ($companyID>0)?1:0;
     */
    $companyID             = $_POST["companyID"];
    $_SESSION["companyID"] = $companyID;
    return 1;
}

function NANO_sameUnpCompanies( $company_data ) {
    $COMPANIES_TABLE = COMPANIES_TABLE;
    $unp             = (int)$company_data["company_unp"];
    $companyID       = (int)$company_data["companyID"];
    if ( !$unp ) {
        return false;
    }
    if ( !$unp ) {
        return false;
    }

    $sql = <<<SQL
  SELECT DISTINCT
  `company_title`,
  `companyID`,
  `update_time`,
  `company_unp`
  -- `company_name`,
  -- `company_adress`,
  -- `company_bank`,
  -- `company_director`,
  -- `company_contacts`
  FROM `{$COMPANIES_TABLE}`
  WHERE
   `companyID`> 0
  -- AND  `companyID` != {$companyID}
  AND  `company_unp` = {$unp}
  ORDER BY update_time DESC
SQL;
    $CompanyVariants = array();
    $q               = db_query( $sql );
    while ( $res = db_fetch_assoc( $q ) ) {
        if ( (int)$res["companyID"] == $companyID ) {
            $res["selected"] = "selected";
        }
        $CompanyVariants[] = $res;
    }
    return $CompanyVariants;
}

function getInvoiceAddonHtml( $Adress ) {
    // header( "Content-type: text/html; charset=" . DEFAULT_CHARSET_HTML );
    $smarty                = new Smarty();
    $template              = isset( $_SESSION["CUSTOM_DESIGN"] ) ? $_SESSION["CUSTOM_DESIGN"] : CONF_DEFAULT_TEMPLATE;
    $smarty->template_dir  = "core/tpl/user/" . $template;
    $smarty->compile_id    = $template;
    $smarty->force_compile = true;

    if ( in_array( 100, checklogin() ) ) {
        $isadmin = true;
        $smarty->assign( "isadmin", "yes" );
    } else {
        $isadmin = false;
    }

    if ( $Adress["customerID"] ) {
        $customerID = $Adress["customerID"];
    } else {
        $customerID = 0;
    }

    $where_clause = "";
    $AllowedID    = array();
    $sql          = <<<SQL
SELECT DISTINCT `companyID` FROM `UTF_orders` WHERE
    -- `customerID` > '1' AND
    `companyID` > '0' AND
    `customerID` = {$customerID}
    ORDER BY `orderID` DESC
SQL;

    $q = db_query( $sql );
    while ( $row = db_fetch_assoc( $q ) ) {
        $AllowedID[] = $row["companyID"];
    }

    if ( $AllowedID === array() ) {
        $smarty->assign( "AllowedID", false );

    } else {

        $allowedCompaniesList = implode( ",", $AllowedID );

        if ( !$isadmin && strlen( $allowedCompaniesList ) > 1 ) {
            $where_clause = " WHERE `companyID` != 0 AND `companyID` in ( {$allowedCompaniesList} ) ";
        }

        $Companies = getOptionsForCompanies( $where_clause );

        if ( $Companies === array() or !$customerID ) {
            $smarty->assign( "AllowedCompanies", false );
        } else {

            $AllowedCompanies = array();

            // $AllowedCompanies[0]['title']    = "Добавить новые реквизиты";
            // $AllowedCompanies[0]['value']    = "0";
            // $AllowedCompanies[0]['selected'] = null;

            $ii                              = 0;
            foreach ( $Companies as $_custom ) {
                $ArrayDir = array();

                if ( !empty( $_custom["company_director"] ) ) {
                    $ArrayDir = unserialize( $_custom["company_director"] );
                } else {
                    // consolelog($_custom["companyID"]." *** ".$_custom["company_director"]);
                    $ArrayDir = array();
                }

                if ( $ArrayDir === array() ) {
                    $directorString = "-";
                } else {
                    $directorString = implode( " :: ", $ArrayDir );
                }


                $__is_selected= ( $_custom["companyID"] == $_SESSION["companyID"] ) ? 1 : 0;

                $AllowedCompanies[$ii] = array(
                    "title"    => "{$_custom["company_name"]}",
                    "value"    => $_custom["companyID"],
                    "selected" =>$__is_selected,
                    "subtext"  => "{$_custom["company_unp"]}  {$_custom["update_time"]} ",
                    "tokens"   => "{$_custom["companyID"]} {$_custom["company_name"]} {$_custom["company_unp"]} {$_custom["company_adress"]} {$_custom["company_bank"]}",

                    "json"     => array(
                        "id"                        => $_custom["companyID"],
                        "Дата последнего изменения" => $_custom["update_time"],
                        "Название"                  => $_custom["company_name"],
                        "УНП"                       => $_custom["company_unp"],
                        "Адрес"                     => $_custom["company_adress"],
                        "Банк"                      => $_custom["company_bank"],
                        "Руководитель"              => $directorString,
                    ),

                );

                $ii++;
            }

            $smarty->assign( "AllowedCompanies", $AllowedCompanies );
            $smarty->assign( "AllowedID", $AllowedID );

            if ( !$customerID ) {
                $smarty->assign( "AllowedCompanies", false );
                $smarty->assign( "AllowedID", false );
            }
        }
    }

    exit( $smarty->fetch( "quick_cart__invoice_addon.tpl.html" ) );

}

function getHtmlPaymentSelector( $shippingID ) {
    $html = "";
    if ( $shippingID == 0 ) {
        $shippingID = 3;
    }
    // САМОВЫВОЗ
    if ( $shippingID == 0 ) {
        $html = <<<HTML
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Внимание!</strong> Не выбран способ доставки
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <input type="hidden" id="payment_error" name="payment_error" value="1">
HTML;
    } else {
        $html = <<<HTML
        <input type="hidden" id="payment_error" name="payment_error" value="0">
HTML;

        $moduleFiles = GetFilesInDirectory( "core/modules/payment", "php" );
        if ( !$moduleFiles ) {

            return array( 'payment_methods' => array(), 'html' => "no payment module file" );
        }

        foreach ( $moduleFiles as $fileName ) {
            include $fileName;
        }
        $payment_methods = array();

        foreach ( payGetAllPaymentMethods( true ) as $payment_method ) {
            if ( $shippingID == -1 ) {
                $payment_methods[] = $payment_method;
            } else {
                foreach ( $payment_method["ShippingMethodsToAllow"] as $ShippingMethod ) {
                    if ( $shippingID == $ShippingMethod["SID"] && $ShippingMethod["allow"] ) {
                        $payment_methods[] = $payment_method;
                        break;
                    }
                }
            }
        }

        $count = count( $payment_methods );
        if ( $count ) {
            $html_container = <<<HTML
<div id="payment_methodHelp" class="form-text">Способ оплаты не влияет на стоимость заказа</div>
<div class="list-group list-group-flush my-2" id="payment_method_select">
HTML;
            $html_body = "";
            foreach ( $payment_methods as $index => $payment_method ) {

                $_is_checked = (  ( $count == 1 ) ) ? " checked " : ""; // $_is_checked = (  ( $count == 1 )  || ( $index == 0 )) ? " checked" : "";   /*   || ( $index == 0)*/   || ( $index == 0 )
                /*if($_is_checked){
                $loadAddon=<<<HTML
                <script async defer>
                document.getElementById('payment_method_0').onclick();
                </script>
                HTML;
                }*/
                $html_body .= <<<HTML
<li class="list-group-item mb-0">
<label>
<input class="form-check-input me-1 no-style-validated"
type="radio"
{$_is_checked}
value="{$payment_method['PID']}"
id="payment_method_{$index}"
name="payment_method"
onmouseup="button_checked=this.checked;"
onclick="payment_changed()"
>
<span class="fw-bold">{$payment_method["Name"]}<sub class="opacity-25">{$payment_method['PID']}</sub></span>
<p class="mb-0 text-break text-end">
<em class="text-end">{$payment_method["description"]}</em></p>
  </label>
</li>
HTML;
            }
            $html_container_end = <<<HTML
</div>
{$loadAddon}
HTML;
            $html = $html_container . $html_body . $html_container_end;
        } else {

            $html = <<<HTML
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Внимание!</strong> Нет вариантов оплаты...
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
HTML;
        }
    }
    return array( "payment_methods" => $payment_methods, "html" => $html );
}

function NANO_GetShippingSelector( $zoneID ) {
    $html = "";
    if ( $zoneID == 0 ) {
        $zoneID = 100;
    }
    // МИНСК
    if ( $zoneID || CONF_ADDRESSFORM_STATE > 0 ) {
        $resCart     = cartGetCartContent();
        $resDiscount = dscCalculateDiscount( $resCart["total_price"], isset( $_SESSION["log"] ) ? $_SESSION["log"] : "" );
        $order       = array( "orderContent" => $resCart["cart_content"], "order_amount" => $resCart["total_price"] - $resDiscount["discount_standart_unit"] );
        $sh_address  = $zoneID ? znGetSingleZoneById( $zoneID ) : array( "address" => "", "zoneID" => 0, "addressID" => 0 );
        $addresses   = array( $sh_address, $sh_address );
        $moduleFiles = GetFilesInDirectory( "core/modules/shipping", "php" );

        if ( !$moduleFiles ) {

            return array( 'shipping_methods' => array(), 'html' => "no shipping module file" );
        }

        foreach ( $moduleFiles as $fileName ) {
            include $fileName;
        }

        $shipping_methods = array();
        foreach ( shGetAllShippingMethods( $enabledOnly = true ) as $key => $shipping_method ) {
            $_ShippingModule = modGetModuleObj( $shipping_method["module_id"], SHIPPING_RATE_MODULE ); # вернет обьект модуля доставки либо NULL
            if ( !$_ShippingModule || $_ShippingModule->allow_shipping_to_address( $sh_address ) ) {
                $shipping_cost = oaGetShippingCostTakingIntoTax( $resCart, $shipping_method["SID"], $addresses, $order );
                foreach ( $shipping_cost as $subkey => $subcost ) {
                    $flag = false;
                    if ( $subcost["rate"] > 0 ) // если стоимость доставки больше нуля (sign - от модуля "Самовывоз")
                    {
                        $flag                           = true;
                        $shipping_cost[$subkey]["rate"] = show_price( $subcost["rate"] );
                    } elseif ( isset( $subcost["sign"] ) ) {
                        $flag                           = true;
                        $shipping_cost[$subkey]["rate"] = show_price( $subcost["rate"] );
                    } elseif ( count( $shipping_cost ) > 1 || $subcost["rate"] == 0 ) // если стоимость доставки не больше нуля, но есть еще варианты или доставка равна нулю (бесплатна)
                    {
                        $flag                           = true;
                        $shipping_cost[$subkey]["rate"] = "";
                    }
                }
                if ( $flag ) {
                    $shipping_method["shipping_costs"] = $shipping_cost;
                    $shipping_methods[]                = $shipping_method;
                }
            }
        }

        $count = count( $shipping_methods );
        if ( $count ) {
            $html_container = <<<HTML
            <div id="shipping_methodHelp" class="form-text">Стоимость доставки будет равномерно разнесена по стоимости заказа</div>
<div class="list-group list-group-flush my-2" id="shipping_method_select">
HTML;
            $html_body = "";
            foreach ( $shipping_methods as $index => $shipping_method ) {
                // $_is_checked = (  ( $count == 1 ) ) ? " checked" : "";
                $_is_checked = (  ( $index == 0 ) || ( $count == 1 ) ) ? " checked" : ""; /*предустанавливаем самомвывоз первым пунктом ( $index == 0 ) || */

                $html_body_costs = "";
                if ( count( $shipping_method['shipping_costs'] ) == 1 ) {
                    if ( !empty( $shipping_method['shipping_costs'][0]['name'] ) ) {
                        $html_body_costs .= $shipping_method['shipping_costs'][0]['name'] . '<br>';
                    }
                    $html_body_costs .= empty( $shipping_method['shipping_costs'][0]['rate'] ) ? STRING_ZERO_SHIPPING : $shipping_method['shipping_costs'][0]['rate'];
                } else {
                    $html_body_costs .= '<select class="form-control input-sm" name="shServiceID[' . $shipping_method['SID'] . ']" >';
                    foreach ( $shipping_method['shipping_costs'] as $key => $shipping_cost ) {
                        $html_body_costs .= '<option value="' . ( $key + 1 ) . '">' . $shipping_cost['name'] . ' - ' . $shipping_cost['rate'] . '</option>';
                    }
                    $html_body_costs .= '</select>';
                }
                $html_body .= <<<HTML
<li class="list-group-item mb-0">
<label>
<input class="form-check-input me-1 no-style-validated"
type="radio"
{$_is_checked}
value="{$shipping_method['SID']}"
name="shipping_method"
onmouseup="button_checked=this.checked;"
onclick="shipping_changed()"
                                        >
<span class="fw-bold">{$shipping_method['Name']}<sub class="opacity-25">{$shipping_method['SID']}</sub></span><span class="badge bg-danger float-end">{$html_body_costs}</span>
<p class="text-break text-end mb-0">
<em class="text-end">{$shipping_method['description']}</em></p>
  </label>
</li>
HTML;
            }
            $html_container_end = <<<HTML
</div>
HTML;
            $html = $html_container . $html_body . $html_container_end;
        } else {
            $html = <<<HTML
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Внимание!</strong> Нет вариантов доставки в ваш регион
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
HTML;
        }
    } else {
        // $html = "Внимание! Не выбран регион доставки!";
        $html = <<<HTML
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <strong>Внимание!</strong> Не выбран регион доставки!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
HTML;
    }
    return array( 'shipping_methods' => $shipping_methods, 'html' => $html );
}

function getHtmlNavigator( $a, $offset, $q, $path ) {
    //shows navigator [prev] 1 2 3 4 … [next]
    //$a - count of elements in the array, which is being navigated
    //$offset - current offset in array (showing elements [$offset ... $offset+$q])
    //$q - quantity of items per page
    //$path - link to the page (f.e: "index.php?categoryID=1&")

    if ( $q == 0 ) {
        $q = 16;
    }

    //if all elements couldn't be placed on the page
    if ( $a > $q ) {
        //[prev]
        if ( $offset > 0 ) {
            $html .= "<li class='page-item'><a class='page-link link-ajax' role='button' data-offset='" . ( $offset - $q ) . "' data-href=\"" . $path . "offset_" . ( $offset - $q ) . ".html\">&lsaquo;</a></li>";
        }

        //digital links
        $k = (int)$offset / $q;

        //not more than 4 links to the left
        $min = $k - 4;
        if ( $min < 0 ) {
            $min = 0;
        } else {
            if ( $min >= 1 ) {
                //link on the 1st page
                $html .= "<li class='page-item'><a class='page-link link-ajax' role='button' data-offset='" . ( $i * $q ) . "' data-href=\"" . $path . "offset_0.html\">1</a></li>";
                if ( $min != 1 ) {
                    $html .= "<li class='page-item'><span class='page-link link-ajax pe-none' role='button' data-offset='" . ( $i * $q ) . "'>...</span></li>";
                }
            }
        }

        for ( $i = $min; $i < $k; $i++ ) {
            $m = $i * $q + $q;
            if ( $m > $a ) {
                $m = $a;
            }

            $html .= "<li class='page-item'><a class='page-link link-ajax' role='button' data-offset='" . ( $i * $q ) . "' data-href=\"" . $path . "offset_" . ( $i * $q ) . ".html\">" . ( $i + 1 ) . "</a></li>";
        }

        //# of current page
        if ( strcmp( $offset, "show_all" ) ) {
            $min = $offset + $q;
            if ( $min > $a ) {
                $min = $a;
            }

            $html .= "<li class='page-item active' aria-current='page'><span class='page-link link-ajax pe-none' role='button' data-offset='" . ( $i * $q ) . "'>" . ( $k + 1 ) . "</span></li>";
        } else {
            $min = $q;
            if ( $min > $a ) {
                $min = $a;
            }

            $html .= "<li class='page-item'><a class='page-link link-ajax' role='button' data-offset='" . ( $i * $q ) . "' data-href=\"" . $path . "offset_0.html\" data-offset='" . ( $i * $q ) . "'>1</a></li>";
        }

        //not more than 5 links to the right
        $min = $k + 5;
        if ( $min > $a / $q ) {
            $min = $a / $q;
        }
        ;
        for ( $i = $k + 1; $i < $min; $i++ ) {
            $m = $i * $q + $q;
            if ( $m > $a ) {
                $m = $a;
            }

            $html .= "<li class='page-item'><a class='page-link link-ajax' role='button' data-offset='" . ( $i * $q ) . "' data-href=\"" . $path . "offset_" . ( $i * $q ) . ".html\">" . ( $i + 1 ) . "</a></li>";
        }

        if ( ceil( $min * $q ) < $a ) {
            //the last link
            if ( $min * $q < $a - $q ) {
                $html .= "<li class='page-item'><span class='page-link link-ajax text-muted pe-none' role='button'>...</span></li>";
            }

            // $html .= "<li class='page-item'><a class='page-link link-ajax' role='button' data-offset='" . ( $a - $a % $q ) . "' data-href=\"" . $path . "offset_" . ( $a - $a % $q ) . ".html\">" . ( floor( $a / $q ) + 1 ) . "</a></li>";

            if (  ( $a % $q ) != 0 ) {
                $html .= "<li class='page-item'><a class='page-link link-ajax' role='button' data-offset='" . ( $a - $a % $q ) . "' data-href=\"" . $path . "offset_" . ( $a - $a % $q ) . ".html\">" . ( ceil( $a / $q ) ) . "</a></li>";
            } else {
                $html .= "<li class='page-item'><a class='page-link link-ajax' role='button' data-offset='" . ( $a - $q ) . "' data-href=\"" . $path . "offset_" . ( $a - $a % $q ) . ".html\">" . ( ceil( $a / $q ) ) . "</a></li>";
            }

        }

        //[next]
        if ( strcmp( $offset, "show_all" ) ) {
            if ( $offset < ( $a - $q ) ) {
                $html .= "<li class='page-item'><a class='page-link link-ajax' role='button' data-offset='" . ( $offset + $q ) . "' data-href=\"" . $path . "offset_" . ( $offset + $q ) . ".html\">&rsaquo;</a></li>";
            }
        }

        //[show all]
        // if ( $a <= CONF_PRODUCTS_SHOW_ALL * CONF_PRODUCTS_PER_PAGE ) {
        if ( $a <= 512 ) {

            if ( strcmp( $offset, "show_all" ) ) {
                $html .= "<li class='page-item'><a class='page-link link-ajax' role='button' data-offset='show_all' data-href=\"" . $path . "show_all.html\" rel=\"nofollow\">" . STRING_SHOWALL . "</a></li>";
            } else {
                $html .= "<li class='page-item active' aria-current='page'><span class='page-link link-ajax pe-none' role='button' data-offset='show_all'>" . STRING_SHOWALL . "</span></li>";
            }

        }
    }

    return $html;
}

function phpmakeBuyButtonSm( $data ) {
    $out_html       = "";
    $form_html      = "";
    $is_fake_clause = "";
    $doLoadLink     = "";
    $btn_class      = "";

    if ( $data['productID'] ) {
        // $instock = $data['in_stock']['qnt'];
        $instock = $data["in_stock_qnt"];

        if ( $instock <= -1 ) {
            $is_fake_clause = "&is_fake=yes";
        }

        if ( $instock > 0 ) {
            $btn_class = "btn btn-success btn-sm";
        } elseif ( $instock == -1 ) {
            $btn_class = "btn btn-secondary btn-sm";
        } elseif ( $instock < -1 ) {
            $btn_class = "btn btn-outline-success btn-sm";
        }

        if (  ( $instock != 0 ) && ( $instock != -1000 ) ) {
            $action = ( CONF_MOD_REWRITE )
            ? "product_{$data['productID']}.html"
            : "index.php?categoryID={$data["categoryID"]}&prdID={$data["productID"]}";

            $form_html = <<<HTML

<form
action="index.php?categoryID={$data["categoryID"]}&prdID={$data["productID"]}"
class="d-flex flex-row justify-content-center align-items-center"
name="HiddenFieldsForm_{$data["productID"]}"
id="HiddenFieldsForm_{$data["productID"]}"
method="post"
role="form"
>

            <div class="input-group input-group-sm justify-content-center">
                <input type="number" min = "1" max = "9999" step="1" name="multyaddcount" class="form-control text-end" value="1" style="min-width: 2rem!important;max-width: 6rem!important;">
                <button class="{$btn_class}" type="button" name="buttonAddToCart" data-is_fake_clause="{$is_fake_clause}" data-productID="{$data['productID']}" data-categoryID="{$data['categoryID']}">
                    <i class="bi bi-cart3"></i>
                </button>
            </div>

</form>
HTML;
        } elseif ( $instock == 0 ) {
            $form_html = "<div class=\"d-flex flex-row justify-content-center align-items-center\"><a href=\"mailto:2842895@gmail.com?subject=Заказ&body=Запрос поставки товара:{$data["Name"]}\" class=\"btn btn-link text-decoration-none\" title=\"Запросить поставку e-mail:2842895@gmail.com\"><i class=\"bi bi-envelope-check\"></i>e-mail</a></div>";
        } else {
            $form_html = "<div class=\"d-flex flex-row justify-content-center align-items-center\">Поставка прекращена</div>";
        }

    }

    $out_html = $form_html;
    return $out_html;
}

?>