<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

###################### antaNT64 func ###########################################################
###################### antaNT64 func ###########################################################
###################### antaNT64 func ###########################################################
function __filter_order( $option_value_selected, $pt = "pt." ) {
    $res = "";

    $res = $option_value_selected;
    return $res;
}

function __filter_instock( $option_value_selected = 0, $dbf = "`in_stock`" ) {
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
            $res = " {$dbf} > 0 ";
            break;
        case 2:
            // $res = " ( ({$dbf} > 0) OR ({$dbf} <=-1) AND ({$dbf} !=-1000)) ";
            $res = " ( ({$dbf} !=0) AND ({$dbf} !=-1000)) ";
            break;
        case 3:
            $res = " ( ({$dbf} <=-2) AND ({$dbf} !=-1000) )";
            break;
        case 4:
            $res = " ({$dbf} =-1) ";
            break;
        case 5:
            $res = " ({$dbf} = 0) ";
            break;
        case 6:
            $res = " ({$dbf} = -1000) ";
            break;
        case 7:
            $res = " ({$dbf} > 10) ";
            break;
        case 8:
            $res = " ({$dbf} <= 5) AND ({$dbf} > 0) ";
            break;
        case 9:
            $res = " ({$dbf} >= -14) AND ({$dbf} < -1) ";
            break;
        case 10:
            $res = " ({$dbf} > -1000) AND ({$dbf} < -14) AND ({$dbf} < -1) ";
            break;
        default:
            $res = "";
            break;
            /* endswitch; */
    }

    return $res;
}

function prdAddProductFromData( $data, $updateGCV = 0, $AS_SQL = 0 ) {

    // $categoryID
    // $name
    // $Price
    // $description
    // $in_stock
    // $brief_description
    // $list_price
    // $product_code
    // $sort_order
    // $ProductIsProgram
    // $eproduct_filename
    // $eproduct_available_days
    // $eproduct_download_times
    // $weight
    // $meta_description
    // $meta_keywords
    // $free_shipping
    // $min_order_amount
    // $shipping_freight
    // $classID
    // $title
    // $updateGCV
    {
        $categoryID              = (int)$data["categoryID"];
        $name                    = xHtmlSpecialCharsDecode( $data["name"] );
        $Price                   = (double)$data["Price"];
        $description             = $data["description"];
        $in_stock                = (int)$data["in_stock"];
        $brief_description       = $data["brief_description"];
        $list_price              = (double)$data["list_price"];
        $product_code            = xHtmlSpecialCharsDecode( $data["product_code"] );
        $sort_order              = (int)$data["sort_order"];
        $ProductIsProgram        = $data["ProductIsProgram"];
        $eproduct_filename       = $data["eproduct_filename"];
        $eproduct_available_days = (int)$data["eproduct_available_days"];
        $eproduct_download_times = (int)$data["eproduct_download_times"];
        $weight                  = (float)$data["weight"];
        $meta_description        = xHtmlSpecialCharsDecode( $data["meta_description"] );
        $meta_keywords           = xHtmlSpecialCharsDecode( $data["meta_keywords"] );
        $free_shipping           = $data["free_shipping"];
        $min_order_amount        = (int)$data["min_order_amount"];
        $shipping_freight        = (double)$data["shipping_freight"];
        $classID                 = $data["classID"];
        $title                   = xHtmlSpecialCharsDecode( $data["title"] );
    }
    $updateGCV = 0;
    $vendorID  = $data["vendorID"];

//core/functions/product_functions.php:592 @AddProduct
    $res = AddProduct(
        $categoryID,
        $name,
        $Price,
        $description,
        $in_stock,
        $brief_description,
        $list_price,
        $product_code,
        $sort_order,
        $ProductIsProgram,
        $eproduct_filename,
        $eproduct_available_days,
        $eproduct_download_times,
        $weight,
        $meta_description,
        $meta_keywords,
        $free_shipping,
        $min_order_amount,
        $shipping_freight,
        $classID,
        $title,
        $updateGCV,
        $vendorID,
        $AS_SQL
    );
    return $res;
}

function prdUpdateProductFromData( $data, $updateGCV = 0 ) {
    $currentID = $data["currentID"];
    if ( is_null( $currentID ) || is_null( $data["categoryID"] ) ) {
        return false; // нехер обновлять если нет кода продукта]
    }

    {
        $new_categoryID          = (int)$data["categoryID"];
        $name                    = xHtmlSpecialCharsDecode( $data["name"] );
        $Price                   = (double)$data["Price"];
        $description             = $data["description"];
        $in_stock                = (int)$data["in_stock"];
        $customers_rating        = $data["customers_rating"];
        $brief_description       = $data["brief_description"];
        $list_price              = (double)$data["list_price"];
        $product_code            = xHtmlSpecialCharsDecode( $data["product_code"] );
        $sort_order              = (int)$data["sort_order"];
        $ProductIsProgram        = $data["ProductIsProgram"]; //
        $eproduct_filename       = $data["eproduct_filename"];
        $eproduct_available_days = (int)$data["eproduct_available_days"];
        $eproduct_download_times = (int)$data["eproduct_download_times"];
        $weight                  = (float)$data["weight"];
        $meta_description        = xHtmlSpecialCharsDecode( $data["meta_description"] );
        $meta_keywords           = xHtmlSpecialCharsDecode( $data["meta_keywords"] );
        $free_shipping           = $data["free_shipping"];
        $min_order_amount        = (int)$data["min_order_amount"];
        $shipping_freight        = (double)$data["shipping_freight"];
        $classID                 = $data["classID"];
        $title                   = xHtmlSpecialCharsDecode( $data["title"] );
    }

    $updateGCV = 0;

    #core/functions/product_functions.php:463 @UpdateProduct
    $result = UpdateProduct(
        $currentID,
        $categoryID,
        $name,
        $Price,
        $description,
        $in_stock,
        $customers_rating,
        $brief_description,
        $list_price,
        $product_code,
        $sort_order,
        $ProductIsProgram,
        $eproduct_filename,
        $eproduct_available_days,
        $eproduct_download_times,
        $weight,
        $meta_description,
        $meta_keywords,
        $free_shipping,
        $min_order_amount,
        $shipping_freight,
        $classID,
        $title,
        $updateGCV
    );

    $result = $currentID;
    return $result;
}

# $data - должен быть array(строка продукта)
function prdConvertFromPriceToCatalog( $data, $permanent_categoryID = 1, $tag = "" ) {
// consolelog( $permanent_categoryID );
    // consolelog($data[0]);

    $out = array();

    $ii = 0;
    foreach ( $data as $key => $row ) {

        $LABEL_IS_NEW    = ( $row["is_newproduct"] ) ? "NEW!" : ""; //html_spchars
        $prod_code_debug = trim( $data[$key]["product_code"] );

        if ( strlen( $prod_code_debug ) >= 1 ) {
            $new__product_code = $prod_code_debug;
        } else {
            $new__product_code = "#{$ii}#_"+xToText( $_SERVER["REQUEST_TIME"] );
        }

        $new__name              = ( $row["Prefix"] . " {$tag} " . $row["Name"] ); // "[XXX] [XXX] " .
        $new__Price             = (double)( $row["Col1"] );
        $new__list_price        = (double)( $row["Col3"] );
        $new__in_stock          = Encode_ApproxAmount( $row["ApproxAmount"] );
        $new__brief_description = $LABEL_IS_NEW . ( $row["OriginalName"] );

        $new__description = "";
        if ( (int)$row["vendorID"] == 1 && strlen( $row["href"] ) ) {
            $new__description .= ( "<a row-vendor=" . $row["vendorID"] . " class=\"btn btn-default btn-sm btn-block\" target=\"_blank\" href=\"" . $row["href"] . "\">Информация nix.ru</a>" );
        } else {
            $new__description = "";
        }

        $new__description .= "гарантия - {$row["Warranty"]} ";
        $new__description .= "\r\n";
        $new__description .= "вес {$row["Weight"]} кг ";
        $new__description .= "\r\n";
        $new__description .= "объем {$row["Volume"]} ";
        $new__description .= "\r\n";
        $new__description .= $row["GroupName"];
        $new__description .= "\r\n";

        $new__default_picture = null;

        $new__title            = $LABEL_IS_NEW . ( $row["Prefix"] . " " . $row["Name"] ) . " " . "Беларусь Минск купить заказать";
        $new__meta_description = $new__name . " " . "Беларусь Минск купить заказать НДС nix nix.by никс тшч";

        # $out[$ii]["productID"]               = "INSERT";
        $out[$ii]["categoryID"]              = ( !isset( $row["AssocLink"] ) ) ? $permanent_categoryID : (int)$row["AssocLink"]; //(int)$categoryID
        $out[$ii]["name"]                    = $new__name;                                                                    //xToText( trim( $name ) )
        $out[$ii]["description"]             = "";                                                                            //$new__description;                          //xEscSQL( $description )
        $out[$ii]["customers_rating"]        = 0;                                                                             //0
        $out[$ii]["Price"]                   = $new__Price;                                                                   //(double)$Price
        $out[$ii]["in_stock"]                = $new__in_stock;                                                                //(int)$in_stock
        $out[$ii]["customer_votes"]          = 0;                                                                             //0
        $out[$ii]["items_sold"]              = 0;                                                                             //0
        $out[$ii]["enabled"]                 = (int)$row["enabled"];                                                         //1
        $out[$ii]["brief_description"]       = $new__brief_description;                                                       //xEscSQL( $brief_description )
        $out[$ii]["list_price"]              = $new__list_price;                                                              //(double)$list_price
        $out[$ii]["product_code"]            = $new__product_code;                                                            //xToText( trim( $product_code ) )
        $out[$ii]["sort_order"]              = $ii * 10;                                                                      //(int)$sort_order
        $out[$ii]["default_picture"]         = $new__default_picture;                                                         //
        $out[$ii]["date_added"]              = $row["date_added"];                                                            //xEscSQL( get_current_time() )
        $out[$ii]["date_modified"]           = $row["date_from_price"];                                                       //
        $out[$ii]["viewed_times"]            = 1;                                                                             //
        $out[$ii]["eproduct_filename"]       = "";                                                                            //xEscSQL( $eproduct_filename )
        $out[$ii]["eproduct_available_days"] = 7;                                                                             //(int)$eproduct_available_days
        $out[$ii]["eproduct_download_times"] = 5;                                                                             //(int)$eproduct_download_times
        $out[$ii]["weight"]                  = $row["Weight"];                                                                //(float)$weight
        $out[$ii]["meta_description"]        = "";                                                                            //$new__meta_description;                     //xToText( trim( $meta_description ) )
        $out[$ii]["meta_keywords"]           = "";                                                                            //xToText( trim( $meta_keywords ) )
        $out[$ii]["free_shipping"]           = 0;                                                                             //(int)$free_shipping
        $out[$ii]["min_order_amount"]        = 1;                                                                             //(int)$min_order_amount
        $out[$ii]["shipping_freight"]        = 0;                                                                             //(double)$shipping_freight
        $out[$ii]["classID"]                 = CONF_DEFAULT_TAX_CLASS;                                                        //(int)$classID
        $out[$ii]["title"]                   = "";                                                                            //$new__title;                                //xToText( trim( $title ) )
        $out[$ii]["vendorID"]                = $row["vendorID"];                                                              //(int)$vendorID
        $out[$ii]["ProductIsProgram"]        = trim( $out[$ii]["eproduct_filename"] ) != "";                                    //

        $ii++;
    }

    return $out;
}

function prdUpdateProductFieldsBySelectedData(
    $identity_column = "productID",
    $currentID,
    $data,
    $fieldList = "Price,in_stock,list_price,date_modified",
    $updateGCV = 0,
    $AS_SQL = 0
) {

    $result = false;
    // $currentID = $data["currentID"];

    if ( !isset( $data ) || is_null( $currentID ) || is_null( $currentID ) ) {
        return false; // нехер обновлять если нет кода продукта]
    }

    global $CATEGORIES_FIELDS_ARRAY;
    global $PRODUCTS_FIELDS_ARRAY;
    global $PRICE_PRODUCTS__NIXRU_FIELDS_ARRAY;

    $fieldset = array();

    if ( $fieldList != "" ) {
        $temp_fielset = explode( ",", $fieldList );
    }

    if ( count( $temp_fielset ) >= 1 ) {
        foreach ( $temp_fielset as $key => $value ) {
            # code...
            if ( in_array( trim( $value ), $PRODUCTS_FIELDS_ARRAY ) ) {
                $fieldset[$key] = $value;
            }

        }
    }

    {
        # раскладываем data на переменные

        $vendorID                = (int)$data["vendorID"];
        $categoryID              = (int)$data["categoryID"];
        $name                    = xHtmlSpecialCharsDecode( $data["name"] );
        $Price                   = (double)$data["Price"];
        $description             = $data["description"];
        $in_stock                = (int)$data["in_stock"];
        $customers_rating        = $data["customers_rating"];
        $brief_description       = $data["brief_description"];
        $list_price              = (double)$data["list_price"];
        $product_code            = xHtmlSpecialCharsDecode( $data["product_code"] );
        $sort_order              = (int)$data["sort_order"];
        $ProductIsProgram        = $data["ProductIsProgram"]; //
        $eproduct_filename       = $data["eproduct_filename"];
        $eproduct_available_days = (int)$data["eproduct_available_days"];
        $eproduct_download_times = (int)$data["eproduct_download_times"];
        $weight                  = (float)$data["weight"];
        $meta_description        = xHtmlSpecialCharsDecode( $data["meta_description"] );
        $meta_keywords           = xHtmlSpecialCharsDecode( $data["meta_keywords"] );
        $free_shipping           = $data["free_shipping"];
        $min_order_amount        = (int)$data["min_order_amount"];
        $shipping_freight        = (double)$data["shipping_freight"];
        $classID                 = $data["classID"];
        $title                   = xHtmlSpecialCharsDecode( $data["title"] );
    }

    {
        # модифицируем переменные min_order_amount free_shipping
        # и работаем с программными товарами

        if ( $min_order_amount == 0 ) {
            $min_order_amount = 1;
        }

        if ( !$ProductIsProgram ) {
            $eproduct_filename = "";
        }

        if ( !$free_shipping ) {
            $free_shipping = 0;
        } else {
            $free_shipping = 1;
        }

        $q             = db_query( "SELECT eproduct_filename FROM " . PRODUCTS_TABLE . " WHERE `{$identity_column}`='" . (int)$currentID . "'" );
        $old_file_name = db_fetch_row( $q );
        $old_file_name = $old_file_name[0];

        if ( $classID == null ) {
            $classID = "NULL";
        }

        if ( $eproduct_filename != "" && $ProductIsProgram ) {
            if ( trim( $_FILES[$eproduct_filename]["name"] ) != "" ) {
                if ( trim( $old_file_name ) != "" && file_exists( "core/files/" . $old_file_name ) ) {
                    unlink( "core/files/$old_file_name" );
                }

                if ( $_FILES[$eproduct_filename]["size"] != 0 ) {
                    $r = move_uploaded_file( $_FILES[$eproduct_filename]["tmp_name"],
                        "core/files/" . $_FILES[$eproduct_filename]["name"] );
                }

                $eproduct_filename = trim( $_FILES[$eproduct_filename]["name"] );
                SetRightsToUploadedFile( "core/files/" . $eproduct_filename );
            } else {
                $eproduct_filename = $old_file_name;
            }

        } elseif ( $old_file_name != "" ) {
            unlink( "core/files/" . $old_file_name );
        }
    }

    {
        # Формируем и выполняем запрос в БД

        if ( count( $fieldset ) >= 1 ) {

            $sql_update = "UPDATE " . PRODUCTS_TABLE . " SET ";
            foreach ( $fieldset as $key => $value ) {
                // debugfile( $value, "VALUE  prdUpdateProductFieldsBySelectedData " );
                # code...

                $sql_update .= ( $value == "vendorID" ) ? "vendorID=" . (int)$vendorID . ", " : "";
                $sql_update .= ( $value == "categoryID" ) ? "categoryID=" . (int)$categoryID . ", " : "";
                $sql_update .= ( $value == "name" ) ? "name='" . xToText( trim( $name ) ) . "', " : "";
                $sql_update .= ( $value == "Price" ) ? "Price=" . (double)$Price . ", " : "";

                $sql_update .= ( $value == "description" ) ? "description='" . xEscSQL( $description ) . "', " : "";
                $sql_update .= ( $value == "in_stock" ) ? "in_stock=" . (int)$in_stock . ", " : "";
                $sql_update .= ( $value == "customers_rating" ) ? "customers_rating=" . (float)$customers_rating . ", " : "";
                $sql_update .= ( $value == "brief_description" ) ? "brief_description='" . xEscSQL( $brief_description ) . "', " : "";

                $sql_update .= ( $value == "list_price" ) ? "list_price=" . (double)$list_price . ", " : "";
                $sql_update .= ( $value == "product_code" ) ? "product_code='" . xToText( trim( $product_code ) ) . "', " : "";
                $sql_update .= ( $value == "sort_order" ) ? "sort_order=" . (int)$sort_order . ", " : "";
                $sql_update .= ( $value == "date_modified" ) ? "date_modified='" . xEscSQL( get_current_time() ) . "', " : "";

                $sql_update .= ( $value == "eproduct_filename" ) ? "eproduct_filename='" . xEscSQL( $eproduct_filename ) . "', " : "";
                $sql_update .= ( $value == "eproduct_available_days" ) ? "eproduct_available_days=" . (int)$eproduct_available_days . ", " : "";
                $sql_update .= ( $value == "eproduct_download_times" ) ? "eproduct_download_times=" . (int)$eproduct_download_times . ",  " : "";
                $sql_update .= ( $value == "weight" ) ? "weight=" . (float)$weight . ", " : "";

                $sql_update .= ( $value == "meta_description" ) ? "meta_description='" . xToText( trim( $meta_description ) ) . "', " : "";
                $sql_update .= ( $value == "meta_keywords" ) ? "meta_keywords='" . xToText( trim( $meta_keywords ) ) . "', " : "";
                $sql_update .= ( $value == "free_shipping" ) ? "free_shipping=" . (int)$free_shipping . ", " : "";
                $sql_update .= ( $value == "min_order_amount" ) ? "min_order_amount = " . (int)$min_order_amount . ", " : "";

                $sql_update .= ( $value == "shipping_freight" ) ? "shipping_freight = " . (double)$shipping_freight . ", " : "";
                $sql_update .= ( $value == "categoryID" ) ? "title = '" . xToText( trim( $title ) ) . "' " : "";
                $sql_update .= ( $value == "classID" && $classID != null ) ? "classID=" . (int)$classID . ", " : "";

            }

            $sql_update .= " WHERE `{$identity_column}`=" . (int)$currentID;
            $sql_update = str_replace( ",  WHERE", " WHERE", $sql_update );

            if ( !$AS_SQL ) {
                db_query( $sql_update );
                $result = $currentID;
            } else {
                // die($sql_update);
                $result = $sql_update;
            }

        } //end if
    }

    // consolelog( $result );

    #update goods count values for categories in case of regular file editing. do not update during import from excel
    if ( $updateGCV == 1 && CONF_UPDATE_GCV == "1" ) {
        update_psCount( 1 );
    }

    return $result;
}

function prdGetShortProductDATA( $productID ) {
    $q                   = db_query( "SELECT productID,categoryID,name,Price,in_stock,product_code from " . PRODUCTS_TABLE . " where productID=" . (int)$productID );
    $row                 = db_fetch_assoc( $q );
    $row["name_cropped"] = "[" . $row["product_code"] . "] " . mb_substr( $row["name"], 0, 30 ) . " ..";
    $row["name"]         = "[" . $row["product_code"] . "] " . $row["name"];
    if ( $row["in_stock"] < 0 ) {
        $Lag        = Decode_row_in_stock( $row["in_stock"] );
        $row["lag"] = $Lag["in_stock_string"];
    }
    return ( $row );
}

# фальсифицируем in_stock  для  отруцательных значений
# возвращаем фальшивое количество товара на складе - это  НЕ ЗАДЕРЖКА СРОКа ПОСТАВКИ !!!!  а гадание на кофейной гуще
# in_stock из таблицы товаров
# положительное число - реальное количество
# отрицательное - прогнозируем количество
# -1 - неизвестно ни количество ни срок поставкуи, но товар есть в парйсе поставщика
# -2....-999 - количесво дней на поставку
# 0 - нет на складе - ноль это ноль
# -1000 - нет на складе и НИКОГДА НЕ ПОЯВИТСЯ - ноль это ноль
function FakeProductInStockCount( $productID ) {
    $q  = db_query( "SELECT in_stock FROM " . PRODUCTS_TABLE . " WHERE productID=" . (int)$productID );
    $is = db_fetch_row( $q );

    $fake_instock = generateFakedInStockCount( $is[0] );
    return $fake_instock;
# END Добавление ТРАНЗИТНЫХ ТОВАРОВ
}

function generateFakedInStockCount( $number ) {
    $real_instock = $number;
    $fake_instock = $number;
    if ( $real_instock > 0 ) {
        $fake_instock = $real_instock;
    } elseif ( $real_instock == 9999 ) {
        $fake_instock = 1000;
    } elseif ( $real_instock == 0 ) {
        $fake_instock = 0;
    } elseif ( $real_instock == -1 ) {
        $fake_instock = floor( rand( 6, 10 ) );
    } elseif ( $real_instock == -1000 ) {
        $fake_instock = 0;
    } else {
        $fake_instock = floor( rand( 100, 500 ) );
    }
    return $fake_instock;
}

//определяем максимальное время доставки для  товаров в корзине
function GetMaxDeliveryDelay() {
    $res = array();

    $cartContent    = cartGetCartContent();
    $real_is        = 0;
    $ShippingTermin = 0;
    foreach ( $cartContent["cart_content"] as $cartItem ) {
        $productID = isset( $_SESSION["log"] ) ? GetProductIdByItemId( $cartItem["id"] ) : $cartItem["id"];
        $real_is   = GetProductInStockCount( $productID );

        if ( $real_is <= 0 ) {
            if ( $ShippingTermin < abs( $real_is ) ) {
                $ShippingTermin = abs( $real_is );
                $res            = Decode_row_in_stock( $real_is );
            }

        }

    }
    return $res;
}
# кодируем ApproxAmount с nix.ru
function Encode_ApproxAmount( $s = "", $shipping_lag = 1 ) {
    $res      = 0;
    $patterns = array( '/много/', '/\D/' );
    $replace  = array( 9999, '' );
    // $num = is_numeric( $s ) ? (int)$s : ( -1 * preg_replace( $patterns, $replace, $s ) );
    if ( !is_numeric( $s ) ) {
        if ( $s == "много" ) {
            $res = 9999;
        } else {
            $res = ( -1 * ( preg_replace( $patterns, $replace, $s ) + $shipping_lag ) );
        }
    } else {
        $res = (int)$s;
    }
    return $res;
}

#возвращаем массив расшифровки in_stock
function Decode_row_in_stock( $row_in_stock ) {
    $res = array();

    if ( $row_in_stock <= 0 ) {

        switch ( $row_in_stock ) {

            case ( 0 ):
                $in_stock         = 0;
                $in_stock_request = 0;
                $in_stock_string  = STRING_INSTOCK_NO_STOCK;
                $in_stock_symbol  = SYMBOL_INSTOCK_NO;
                $delay            = 365;
                break;

            case ( -1 ):
                $in_stock         = 0;
                $in_stock_request = 1;
                $in_stock_string  = STRING_INSTOCK_WAIT;
                $in_stock_symbol  = SYMBOL_INSTOCK_WAIT;
                $delay            = 1;
                break;

            case ( -1000 ):
                $in_stock         = 0;
                $in_stock_request = 0;
                $in_stock_string  = STRING_INSTOCK_DIE;
                $in_stock_symbol  = SYMBOL_INSTOCK_DIE;
                $delay            = 1000000;
                break;

            case ( $row_in_stock <= -2 ):

                $in_stock         = 0;
                $in_stock_request = 1;
                // $in_stock_string= DaysToStringRussian(abs($row_in_stock."%b"));
                $cnt = abs( $row_in_stock );

                if ( $cnt <= 14 ) {
                    $in_stock_string = "" . $cnt . "&nbsp;" . say_to_russian( $cnt, "дня", "дня", "дней" );
                    $in_stock_symbol = SYMBOL_INSTOCK_TRAIN_GO_2;
                } elseif (  ( $cnt > 14 ) && ( $cnt < 60 ) ) {
                    $cnt             = ceil( $cnt / 7 );
                    $in_stock_string = "" . $cnt . "&nbsp;" . say_to_russian( $cnt, "неделя", "недели", "недель" );
                    $in_stock_symbol = SYMBOL_INSTOCK_TRAIN_GO_4;
                } elseif (  ( $cnt >= 60 ) && ( $cnt < 1000 ) ) {
                    $cnt             = ceil( $cnt / 30 );
                    $in_stock_string = "" . $cnt . "&nbsp;" . say_to_russian( $cnt, "месяца", "месяца", "месяцев" );
                    $in_stock_symbol = SYMBOL_INSTOCK_TRAIN_GO_6;
                }

                $delay = abs( $row_in_stock );
                break;
        }

    } elseif ( $row_in_stock == 9999 ) {
        $in_stock         = 1000;
        $in_stock_request = 0;
        $in_stock_string  = "очень много";
        $in_stock_symbol  = SYMBOL_INSTOCK_MUCH;
        $delay            = 0;
    } elseif (  ( $row_in_stock >= 1 ) && ( $row_in_stock != 9999 ) ) {
        $delay            = 0;
        $in_stock         = $row_in_stock;
        $in_stock_request = 0;
        $in_stock_string  = "" . $in_stock . STRING_INSTOCK_COUNT . " " . STRING_INSTOCK_YES_STOCK . "";
        $in_stock_symbol  = SYMBOL_INSTOCK_MUCH;
    }

    $res["in_stock"]         = (int)$in_stock;
    $res["in_stock_request"] = $in_stock_request;
    $res["in_stock_string"]  = (string)$in_stock_string;
    $res["in_stock_symbol"]  = $in_stock_symbol;
    $res["in_stock_qnt"]     = $row_in_stock;
    $res["Value"]            = (int)$delay;

    return $res;
}

# для товаров на странице магазина
function prdInStockDeliveryDelay( $row_in_stock, $exact_product_balance = 1, $stock_replace_by_symbol = 0, $for_admin = 0 ) {
    // debug( $isadmin );
    // echo "<br>";
    // debug( $for_admin );
    // echo "<hr>";

    $res = array();

    $in_stock         = $row_in_stock;
    $in_stock_request = "";
    $in_stock_string  = "";

    if ( $exact_product_balance == 0 ) {
        if ( $row_in_stock >= 1 ) {
            $in_stock         = 1;
            $in_stock_request = 0;
            $in_stock_string  = STRING_INSTOCK_YES;
            $in_stock_symbol  = SYMBOL_INSTOCK_YES;
        } elseif (  ( $row_in_stock == 0 ) or ( $row_in_stock == -1000 ) ) {
            $in_stock         = 0;
            $in_stock_request = 0;
            $in_stock_string  = STRING_INSTOCK_NO;
            $in_stock_symbol  = SYMBOL_INSTOCK_NO;
        } elseif ( $row_in_stock == -1 ) {
            $in_stock         = 0;
            $in_stock_request = 1;
            $in_stock_string  = STRING_INSTOCK_WAIT;
            $in_stock_symbol  = SYMBOL_INSTOCK_WAIT;
        } else {
            $in_stock         = 0;
            $in_stock_request = 1;
            $in_stock_string  = STRING_INSTOCK_TRAIN_GO;
            $in_stock_symbol  = SYMBOL_INSTOCK_TRAIN_GO;
        }

    } else {

        switch ( $row_in_stock ) {
            case ( -1000 ):
                $in_stock         = 0;
                $in_stock_request = 0;
                $in_stock_string  = STRING_INSTOCK_DIE;
                $in_stock_symbol  = SYMBOL_INSTOCK_DIE;
                break;
            case ( 0 ):
                $in_stock         = 0;
                $in_stock_request = 0;
                $in_stock_string  = STRING_INSTOCK_NO_STOCK;
                $in_stock_symbol  = SYMBOL_INSTOCK_NO;
                break;
            case ( -1 ):
                $in_stock         = 0;
                $in_stock_request = 1;
                $in_stock_string  = STRING_INSTOCK_WAIT;
                $in_stock_symbol  = SYMBOL_INSTOCK_WAIT;
                break;
            case ( $row_in_stock <= -2 ):
                $in_stock         = 0;
                $in_stock_request = 1;
                // $in_stock_string= DaysToStringRussian(abs($row_in_stock."%b"));
                $cnt = abs( $row_in_stock );
                if ( $cnt <= 14 ) {
                    $in_stock_string = "+" . $cnt . "&nbsp;" . say_to_russian( $cnt, "дня", "дня", "дней" );
                    $in_stock_symbol = SYMBOL_INSTOCK_TRAIN_GO_2;
                } elseif (  ( $cnt > 14 ) && ( $cnt < 60 ) ) {
                    $cnt             = ceil( $cnt / 7 );
                    $in_stock_string = "+" . $cnt . "&nbsp;" . say_to_russian( $cnt, "неделя", "недели", "недель" );
                    $in_stock_symbol = SYMBOL_INSTOCK_TRAIN_GO_4;
                } elseif (  ( $cnt >= 60 ) && ( $cnt < 1000 ) ) {
                    $cnt             = ceil( $cnt / 30 );
                    $in_stock_string = "+" . $cnt . "&nbsp;" . say_to_russian( $cnt, "месяца", "месяца", "месяцев" );
                    $in_stock_symbol = SYMBOL_INSTOCK_TRAIN_GO_6;
                }
                break;

            default:
                $in_stock         = abs( $in_stock );
                $in_stock_request = 0;
                $in_stock_string  = "" . $in_stock . STRING_INSTOCK_COUNT . "";
                $in_stock_symbol  = SYMBOL_INSTOCK_MUCH;
                break;
        }

        if ( $stock_replace_by_symbol == 1 ) {

            if ( $row_in_stock >= 1000 ) {
                $in_stock_symbol = str_repeat( $in_stock_symbol, 5 );

            } elseif ( $row_in_stock >= 100 ) {
                $in_stock_symbol = str_repeat( $in_stock_symbol, 4 );

            } elseif ( $row_in_stock > 50 ) {
                $in_stock_symbol = str_repeat( $in_stock_symbol, 3 );

            } elseif ( $row_in_stock > 10 ) {
                $in_stock_symbol = str_repeat( $in_stock_symbol, 2 );

            } elseif ( $row_in_stock > 5 ) {
                $in_stock_symbol = $in_stock_symbol . SYMBOL_INSTOCK_LITTLE;

            } elseif ( $row_in_stock > 1 ) {
                $in_stock_symbol = $in_stock_symbol;

            } elseif ( $row_in_stock == 1 ) {
                $in_stock_symbol = SYMBOL_INSTOCK_LITTLE;

            } elseif ( $row_in_stock == 0 ) {
                $in_stock_symbol = SYMBOL_INSTOCK_NO;
            }
        }
    }

    $res["in_stock"]         = $in_stock;
    $res["qnt"]              = $in_stock;
    $res["in_stock_qnt"]     = $row_in_stock;
    $res["in_stock_request"] = $in_stock_request;
    $res["in_stock_string"]  = $in_stock_string;
    $res["in_stock_symbol"]  = $in_stock_symbol;
    if ( $for_admin ) {
        $res["in_stock_admin"] = $row_in_stock;
    }

    return $res;
}

###################### antaNT64 func ###########################################################
###################### antaNT64 func ###########################################################
###################### antaNT64 func ###########################################################

function prdProductExists( $productID ) {
    $q   = db_query( "SELECT COUNT(*) FROM " . PRODUCTS_TABLE . " WHERE productID=" . (int)$productID ) . "";
    $row = db_fetch_row( $q );
    return ( $row[0] != 0 );
}

function getcontentprod( $productID ) {
    $out = array();
    $cnt = 0;
    $q   = db_query( "SELECT Owner FROM " . RELATED_CONTENT_TABLE . " WHERE productID=" . (int)$productID ) . "";
    while ( $row = db_fetch_row( $q ) ) {
        $outpre       = $row["Owner"];
        $qh           = db_query( "SELECT aux_page_name FROM " . AUX_PAGES_TABLE . " WHERE aux_page_ID=" . (int)$outpre );
        $rowh         = db_fetch_row( $qh );
        $out[$cnt][0] = $outpre;
        $out[$cnt][1] = $rowh["aux_page_name"];
        $cnt++;
    }
    return $out;
}

// *****************************************************************************
// Purpose        gets product
// Inputs   $productID - product ID
// Remarks
// Returns        array of fieled value
//                        "name"                                - product name
//                        "product_code"                - product code
//                        "description"                - description
//                        "brief_description"        - short description
//                        "customers_rating"        - product rating
//                        "in_stock"                        - in stock (this parametr is persist if CONF_CHECKSTOCK == 1 )
//                        "option_values"                - array of
//                                        "optionID"                - option ID
//                                        "name"                        - name
//                                        "value"        - option value
//                                        "option_type" - option type
//                        "ProductIsProgram"                - 1 if product is program, 0 otherwise
//                        "eproduct_filename"                - program filename
//                        "eproduct_available_days"        - program is available days to download
//                        "eproduct_download_times"        - attempt count download file
//                        "weight"                        - product weigth
//                        "meta_description"                - meta tag description
//                        "meta_keywords"                        - meta tag keywords
//                        "free_shipping"                        - 1 product has free shipping,
//                                                        0 - otherwise
//                        "min_order_amount"                - minimum order amount
//                        "classID"                        - tax class ID

function GetProduct( $productID, $for_admin = 0 ) {
    $q = db_query( "SELECT * FROM " . PRODUCTS_TABLE . " WHERE productID=" . (int)$productID . "" );

    if ( $product = db_fetch_row( $q ) ) {

        $product["ProductIsProgram"] = ( trim( $product["eproduct_filename"] ) != "" );
        $sql                         = 'SELECT pot.optionID,pot.name,povt.option_value,povt.option_value AS value,povt.option_type FROM ' . PRODUCT_OPTIONS_VALUES_TABLE . ' as povt
                        LEFT JOIN ' . PRODUCT_OPTIONS_TABLE . ' AS pot ON pot.optionID=povt.optionID
                        WHERE productID=' . (int)$productID . '';
        $Result                   = db_query( $sql );
        $product['option_values'] = array();

        while ( $_Row = db_fetch_row( $Result ) ) {

            $product['option_values'][] = $_Row;
        }
# [ДОПОЛНЕНИЕ] РАЗНАЯ СКИДКА ДЛЯ РАЗНЫХ КАТЕГОРИЙ

        $product['Price'] = CatDiscount( $product['Price'], $product['categoryID'] );
# /[ДОПОЛНЕНИЕ] РАЗНАЯ СКИДКА ДЛЯ РАЗНЫХ КАТЕГОРИЙ
        $product['date_added']    = format_datetime( $product['date_added'] );
        $product['date_modified'] = format_datetime( $product['date_modified'] );

        $date_modified = new DateTime( $product['date_modified'] ); //from database
        $expireDate    = new DateTime( '' );
        $expireDate->modify( '-2 month' );

        if ( $date_modified->format( "Y-m-d" ) < $expireDate->format( "Y-m-d" ) ) {
            $product['date_old'] = $expireDate->format( "Y-m-d" );
        } else {
            $product['date_old'] = 0;
        }

        # Расширенный ВЫВОД ПРОДУКТОВ
        $in_stock_res                = prdInStockDeliveryDelay( $product["in_stock"], CONF_EXACT_PRODUCT_BALANCE, CONF_INSTOCK_REPLACE_BY_SYMBOLS, $for_admin );
        $product["in_stock"]         = $in_stock_res["in_stock"];
        $product["in_stock_request"] = $in_stock_res["in_stock_request"];
        $product["in_stock_string"]  = $in_stock_res["in_stock_string"];
        $product["in_stock_symbol"]  = $in_stock_res["in_stock_symbol"];
        $product["in_stock_qnt"]     = $in_stock_res["in_stock_qnt"];
        if ( $isadmin ) {
            $product["in_stock_admin"] = $in_stock_res["in_stock_admin"];
        }

        $product['name'] = $product['name'];
        $product['Name'] = htmlspecialchars_decode( trim( $product['name'] ) );
        # Расширенный ВЫВОД ПРОДУКТОВ

        return $product;
    }
    return false;
}

// *****************************************************************************
// Purpose        updates product
// Inputs   $productID - product ID
//                                $categoryID                        - category ID ( see CATEGORIES_TABLE )
//                                $name                                - name of product
//                                $Price                                - price of product
//                                $description                - product description
//                                $in_stock                        - stock counter
//                                $customers_rating        - rating
//                                $brief_description  - short product description
//                                $list_price                        - old price
//                                $product_code                - product code
//                                $sort_order                        - sort order
//                                $ProductIsProgram                - 1 if product is program, 0 otherwise
//                                $eproduct_filename                - program filename
//                                $eproduct_available_days        - program is available days to download
//                                $eproduct_download_times        - attempt count download file
//                                $weight                        - product weigth
//                                $meta_description        - meta tag description
//                                $meta_keywords                - meta tag keywords
//                                $free_shipping                - 1 product has free shipping,
//                                                        0 - otherwise
//                                $min_order_amount        - minimum order amount
//                                $classID                - tax class ID
// Remarks
// Returns
function UpdateProduct(
    $productID,
    $categoryID,
    $name,
    $Price,
    $description,
    $in_stock,
    $customers_rating,
    $brief_description,
    $list_price,
    $product_code,
    $sort_order,
    $ProductIsProgram,
    $eproduct_filename,
    $eproduct_available_days,
    $eproduct_download_times,
    $weight,
    $meta_description,
    $meta_keywords,
    $free_shipping,
    $min_order_amount,
    $shipping_freight,
    $classID,
    $title,
    $updateGCV = 1,
    $vendorID = 1
) {
    if ( $min_order_amount == 0 ) {
        $min_order_amount = 1;
    }

    if ( !$ProductIsProgram ) {
        $eproduct_filename = "";
    }

    if ( !$free_shipping ) {
        $free_shipping = 0;
    } else {
        $free_shipping = 1;
    }

    $q             = db_query( "SELECT eproduct_filename FROM " . PRODUCTS_TABLE . " WHERE productID=" . (int)$productID );
    $old_file_name = db_fetch_row( $q );
    $old_file_name = $old_file_name[0];

    if ( $classID == null ) {
        $classID = "NULL";
    }

    if ( $eproduct_filename != "" && $ProductIsProgram ) {
        if ( trim( $_FILES[$eproduct_filename]["name"] ) != "" ) {
            if ( trim( $old_file_name ) != "" && file_exists( "core/files/" . $old_file_name ) ) {
                unlink( "core/files/$old_file_name" );
            }

            if ( $_FILES[$eproduct_filename]["size"] != 0 ) {
                $r = move_uploaded_file( $_FILES[$eproduct_filename]["tmp_name"],
                    "core/files/" . $_FILES[$eproduct_filename]["name"] );
            }

            $eproduct_filename = trim( $_FILES[$eproduct_filename]["name"] );
            SetRightsToUploadedFile( "core/files/" . $eproduct_filename );
        } else {
            $eproduct_filename = $old_file_name;
        }

    } elseif ( $old_file_name != "" ) {
        unlink( "core/files/" . $old_file_name );
    }

    $sql_update = "UPDATE " . PRODUCTS_TABLE . " SET " .
    "categoryID=" . (int)$categoryID . ", " .
    "name='" . xToText( trim( $name ) ) . "', " .
    "Price=" . (double)$Price . ", " .
    "description='" . xEscSQL( $description ) . "', " .
    "in_stock=" . (int)$in_stock . ", " .
    "customers_rating=" . (float)$customers_rating . ", " .
    "brief_description='" . xEscSQL( $brief_description ) . "', " .
    "list_price=" . (double)$list_price . ", " .
    "product_code='" . xToText( trim( $product_code ) ) . "', " .
    "sort_order=" . (int)$sort_order . ", " .
    "date_modified='" . xEscSQL( get_current_time() ) . "', " .
    "eproduct_filename='" . xEscSQL( $eproduct_filename ) . "', " .
    "eproduct_available_days=" . (int)$eproduct_available_days . ", " .
    "eproduct_download_times=" . (int)$eproduct_download_times . ",  " .
    "weight=" . (float)$weight . ", meta_description='" . xToText( trim( $meta_description ) ) . "', " .
    "meta_keywords='" . xToText( trim( $meta_keywords ) ) . "', free_shipping=" . (int)$free_shipping . ", " .
    "min_order_amount = " . (int)$min_order_amount . ", " .
    "shipping_freight = " . (double)$shipping_freight . ", " .
    "title = '" . xToText( trim( $title ) ) . "' ";

    if ( $classID != null ) {
        $sql_update .= ", classID = " . (int)$classID;
    }
    $sql_update .= ", vendorID = " . (int)$vendorID; //ant

    $sql_update .= " WHERE productID=" . (int)$productID;
    db_query( $sql_update );
    // db_query( $sql_update );

    db_query( "delete from " . CATEGORIY_PRODUCT_TABLE . " where productID = " . (int)$productID . " and categoryID = " . (int)$categoryID ); //ЗАЧЕМ???

    if ( $updateGCV == 1 && CONF_UPDATE_GCV == '1' ) //update goods count values for categories in case of regular file editing. do not update during import from excel
    {
        update_psCount( 1 );
    }

}

// *****************************************************************************
// Purpose        sets product file
// Inputs
// Remarks
// Returns
function SetProductFile( $productID, $eproduct_filename ) {
    db_query( "update " . PRODUCTS_TABLE . " set eproduct_filename='" . xEscSQL( $eproduct_filename ) . "' " .
        " where productID=" . (int)$productID );

}

// *****************************************************************************
// Purpose        adds product
// Inputs
//                                $categoryID                        - category ID ( see CATEGORIES_TABLE )
//                                $name                                - name of product
//                                $Price                                - price of product
//                                $description                - product description
//                                $in_stock                        - stock counter
//                                $brief_description  - short product description
//                                $list_price                        - old price
//                                $product_code                - product code
//                                $sort_order                        - sort order
//                                $ProductIsProgram                - 1 if product is program,
//                                                                        0 otherwise
//                                $eproduct_filename                - program filename ( it is index of $_FILE variable )
//                                $eproduct_available_days        - program is available days
//                                                                        to download
//                                $eproduct_download_times        - attempt count download file
//                                $weight                        - product weigth
//                                $meta_description        - meta tag description
//                                $meta_keywords                - meta tag keywords
//                                $free_shipping                - 1 product has free shipping,
//                                                        0 - otherwise
//                                $min_order_amount        - minimum order amount
//                                $classID                - tax class ID
// Remarks
// Returns
function AddProduct(
    $categoryID,
    $name,
    $Price,
    $description,
    $in_stock,
    $brief_description,
    $list_price,
    $product_code,
    $sort_order,
    $ProductIsProgram,
    $eproduct_filename,
    $eproduct_available_days,
    $eproduct_download_times,
    $weight,
    $meta_description,
    $meta_keywords,
    $free_shipping,
    $min_order_amount,
    $shipping_freight,
    $classID,
    $title,
    $updateGCV = 1,
    $vendorID = 1,
    $AS_SQL = 0
) {
    // special symbol prepare
    if ( $free_shipping ) {
        $free_shipping = 1;
    } else {
        $free_shipping = 0;
    }

    if ( $classID == null ) {
        $classID = "NULL";
    }

    if ( $min_order_amount == 0 ) {
        $min_order_amount = 1;
    }

    if ( !$ProductIsProgram ) {
        $eproduct_filename = "";
    }

    if ( $eproduct_filename != "" ) {
        if ( trim( $_FILES[$eproduct_filename]["name"] ) != "" ) {
            if ( $_FILES[$eproduct_filename]["size"] != 0 ) {
                $r = move_uploaded_file( $_FILES[$eproduct_filename]["tmp_name"],
                    "core/files/" . $_FILES[$eproduct_filename]["name"] );
            }

            $eproduct_filename = trim( $_FILES[$eproduct_filename]["name"] );
            SetRightsToUploadedFile( "core/files/" . $eproduct_filename );
        }
    }

    if ( trim( $name ) == "" ) {
        $name = "?";
    }

    $sql_add_product = "INSERT IGNORE INTO " . PRODUCTS_TABLE .
    " ( categoryID, name, description," .
    "        customers_rating, Price, in_stock, " .
    "        customer_votes, items_sold, enabled, " .
    "        brief_description, list_price, " .
    "        product_code, sort_order, date_added, " .
    "         eproduct_filename, eproduct_available_days, " .
    "         eproduct_download_times, " .
    "        weight, meta_description, meta_keywords, " .
    "        free_shipping, min_order_amount, shipping_freight, classID, title, vendorID " .
    " ) " .
    " VALUES (" .
    (int)$categoryID . ", '" .
    xToText( trim( $name ) ) . "', '" .
    xEscSQL( $description ) . "', " .
    "0, '" .
    (double)$Price . "', " .
    (int)$in_stock . ", " .
    " 0, 0, 1, '" .
    xEscSQL( $brief_description ) . "', '" .
    (double)$list_price . "', '" .
    xToText( trim( $product_code ) ) . "', " .
    (int)$sort_order . ", '" .
    xEscSQL( get_current_time() ) . "',  '" .
    xEscSQL( $eproduct_filename ) . "', " .
    (int)$eproduct_available_days . ", " .
    (int)$eproduct_download_times . ",  " .
    (float)$weight . ", " . "'" .
    xToText( trim( $meta_description ) ) . "', " . "'" .
    xToText( trim( $meta_keywords ) ) . "', " .
    (int)$free_shipping . ", " .
    (int)$min_order_amount . ", " .
    (double)$shipping_freight . ", " .
    (int)$classID . ", '" .
    xToText( trim( $title ) ) . "', " .
    (int)$vendorID . " " .
        ") ";

    if ( !$AS_SQL ) {
        $q         = db_query( $sql_add_product );
        $insert_id = db_insert_id();
        if ( $updateGCV == 1 && CONF_UPDATE_GCV == '1' ) {
            update_psCount( 1 );
        }
        return $insert_id;
    } else {
        return $sql_add_product;
    }

}

// *****************************************************************************
// Purpose        deletes product
// Inputs   $productID - product ID
// Remarks
// Returns        true if success, else false otherwise
function DeleteProduct( $productID, $updateGCV = 1 ) {
    $whereClause = " where productID=" . (int)$productID;

    $q = db_query( "select itemID from " . SHOPPING_CART_ITEMS_TABLE . " " . $whereClause );
    while ( $row = db_fetch_row( $q ) );
    db_query( "delete from " . SHOPPING_CARTS_TABLE . " where itemID=" . (int)$row["itemID"] );

    // delete all items for this product
    db_query( "update " . SHOPPING_CART_ITEMS_TABLE .
        " set productID=NULL " . $whereClause );

    // delete all product option values
    db_query( "delete from " . PRODUCTS_OPTIONS_SET_TABLE . $whereClause );
    db_query( "delete from " . PRODUCT_OPTIONS_VALUES_TABLE . $whereClause );

    // delete pictures
    DeleteThreePictures2( $productID );

    // delete additional categories records
    db_query( "delete from " . CATEGORIY_PRODUCT_TABLE . $whereClause );

    // delete discussions
    db_query( "delete from " . DISCUSSIONS_TABLE . $whereClause );

    // delete special offer
    db_query( "delete from " . SPECIAL_OFFERS_TABLE . $whereClause );

    // delete related items
    db_query( "delete from " . RELATED_PRODUCTS_TABLE . $whereClause );
    db_query( "delete from " . RELATED_PRODUCTS_TABLE . " where Owner=" . (int)$productID );

    // delete product
    db_query( "delete from " . PRODUCTS_TABLE . $whereClause );

    if ( $updateGCV == 1 && CONF_UPDATE_GCV == '1' ) {
        update_psCount( 1 );
    }

    return true;
}

// *****************************************************************************
// Purpose        deletes all products of category
// Inputs   $categoryID - category ID
// Remarks
// Returns        true if success, else false otherwise
function DeleteAllProductsOfThisCategory( $categoryID ) {
    $q = db_query( "SELECT `productID` FROM `" . PRODUCTS_TABLE .
        "` WHERE `categoryID`=" . (int)$categoryID );
    $res = true;
    while ( $r = db_fetch_row( $q ) ) {
        if ( !DeleteProduct( $r["productID"], 0 ) ) {
            $res = false;
        }

    }

    if ( CONF_UPDATE_GCV == '1' ) {
        update_psCount( 1 );
    }

    return $res;
}

// *****************************************************************************
// Purpose        gets extra parametrs
// Inputs   $productID - product ID
// Remarks
// Returns        array of value extraparametrs
//                                each item of this array has next struture
//                                        first type "option_type" = 0
//                                                "name"                                        - parametr name
//                                                "option_value"                        - value
//                                                "option_type"                        - 0
//                                        second type "option_type" = 1
//                                                "name"                                        - parametr name
//                                                "option_show_times"                - how times does show in client side this
//                                                                                                parametr to select
//                                                "variantID_default"                - variant ID by default
//                                                "values_to_select"                - array of variant value to select
//                                                        each item of "values_to_select" array has next structure
//                                                                "variantID"                        - variant ID
//                                                                "price_surplus"                - to added to price
//                                                                "option_value"                - value
function GetExtraParametrs( $productID ) {

    if ( !is_array( $productID ) ) {

        $ProductIDs = array( $productID );
        $IsProducts = false;
    } elseif ( count( $productID ) ) {

        $ProductIDs =& $productID;
        $IsProducts = true;
    } else {

        return array();
    }

    $ProductsExtras = array();
    $sql            = 'select povt.productID,pot.optionID,pot.name,povt.option_value,povt.option_type,povt.option_show_times, povt.variantID, povt.optionID
                FROM ?#PRODUCT_OPTIONS_VALUES_TABLE as povt LEFT JOIN  ?#PRODUCT_OPTIONS_TABLE as pot ON pot.optionID=povt.optionID
                WHERE povt.productID IN (?@) ORDER BY pot.sort_order, pot.name
        ';
    $Result = db_phquery( $sql, $ProductIDs );

    while ( $_Row = db_fetch_assoc( $Result ) ) {

        $_Row;
        $b = null;
        if (  ( $_Row['option_type'] == 0 || $_Row['option_type'] == NULL ) && strlen( trim( $_Row['option_value'] ) ) > 0 ) {

            $ProductsExtras[$_Row['productID']][] = array(
                'option_type'  => $_Row['option_type'],
                'name'         => $_Row['name'],
                'option_value' => $_Row['option_value'],
            );
        }
/**
 * @features "Extra options values"
 * @state begin
 */
        elseif ( $_Row['option_type'] == 1 ) {

            //fetch all option values variants
            $sql = 'select povvt.option_value, povvt.variantID, post.price_surplus
                                FROM ' . PRODUCTS_OPTIONS_SET_TABLE . ' as post
                                LEFT JOIN ' . PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE . ' as povvt
                                ON povvt.variantID=post.variantID
                                WHERE povvt.optionID=' . $_Row['optionID'] . ' AND post.productID=' . $_Row['productID'] . '
                                ORDER BY povvt.sort_order, povvt.option_value
                        ';
            $q2                       = db_query( $sql );
            $_Row['values_to_select'] = array();
            $i                        = 0;
# [ДОПОЛНЕНИЕ] РАЗНАЯ СКИДКА ДЛЯ РАЗНЫХ КАТЕГОРИЙ
            if ( CONF_DISCOUNT_TYPE == 2 && isset( $_SESSION["log"] ) && !is_bool( $customerID = regGetIdByLogin( $_SESSION["log"] ) ) && $customer_group = GetCustomerGroupByCustomerId( $customerID ) ) {
                $change = ( $row = db_fetch_assoc( db_query( "SELECT discount FROM " . PRODUCTS_TABLE .
                    " JOIN " . DB_PRFX . "custgroups_category USING (categoryID)
                                                WHERE productID=" . $_Row['productID'] . " AND custgroupID=" . $customer_group['custgroupID'] .
                    " LIMIT 1" ) ) ) ? ( 1 - $row['discount'] / 100 ) : 1;
            } else {
                $change = 1;
            }

# EMD [ДОПОЛНЕНИЕ] РАЗНАЯ СКИДКА ДЛЯ РАЗНЫХ КАТЕГОРИЙ
            while ( $_Rowue = db_fetch_assoc( $q2 ) ) {

                $_Row['values_to_select'][$i]                 = array();
                $_Row['values_to_select'][$i]['option_value'] = $_Rowue['option_value'];
                // if ( $_Rowue['price_surplus'] > 0 )$_Row['values_to_select'][$i]['option_value'] .= ' (+ '.show_price($_Rowue['price_surplus']).')';
                // elseif($_Rowue['price_surplus'] < 0 )$_Row['values_to_select'][$i]['option_value'] .= ' (- '.show_price(-$_Rowue['price_surplus']).')';

                $_Row['values_to_select'][$i]['option_valueWithOutPrice'] = $_Rowue['option_value'];
# [ДОПОЛНЕНИЕ] РАЗНАЯ СКИДКА ДЛЯ РАЗНЫХ КАТЕГОРИЙ
                // $_Row['values_to_select'][$i]['price_surplus']            = show_priceWithOutUnit($_Rowue['price_surplus']);
                $_Row['values_to_select'][$i]['price_surplus'] = show_priceWithOutUnit( (float)$_Rowue['price_surplus'] * $change );
# [ДОПОЛНЕНИЕ] РАЗНАЯ СКИДКА ДЛЯ РАЗНЫХ КАТЕГОРИЙ
                $_Row['values_to_select'][$i]['variantID'] = $_Rowue['variantID'];
                $i++;
            }
            $_Row['values_to_select_count']       = count( $_Row['values_to_select'] );
            $ProductsExtras[$_Row['productID']][] = $_Row;
        }
        /**
         * @features "Extra options values"
         * @state end
         */
    }
    if ( !$IsProducts ) {

        if ( !count( $ProductsExtras ) ) {
            return array();
        } else {
            return $ProductsExtras[$productID];
        }
    }
    return $ProductsExtras;
}

function _setPictures( &$product ) {

    if ( isset( $product['default_picture'] ) && !is_null( $product['default_picture'] ) && isset( $product['productID'] ) ) {
        $picture = db_query( "select filename, thumbnail, enlarged from " .
            PRODUCT_PICTURES . " where photoID=" . (int)$product["default_picture"] );
        $picture_row            = db_fetch_row( $picture );
        $product['picture']     = file_exists( 'data/small/' . $picture_row['filename'] ) ? $picture_row['filename'] : 0;
        $product['thumbnail']   = file_exists( 'data/medium/' . $picture_row['thumbnail'] ) ? $picture_row['thumbnail'] : 0;
        $product['big_picture'] = file_exists( 'data/big/' . $picture_row['enlarged'] ) ? $picture_row['enlarged'] : 0;

    } else {

        $product["USE_NIX_PICTURES"] = 1;
        $product["picture"]          = $nix_pictures[0]["filename"];
        $product["thumbnail"]        = $nix_pictures[0]["thumbnail"];
        $product["big_picture"]      = $nix_pictures[0]["enlarged"];


        $s = "SELECT `photoID`, `img_url` as `filename`, `img_url` as `thumbnail`, `img_url` as `enlarged`
               FROM `PRICE_Pictures`
               WHERE `offer_id`='" . $product["product_code"] . "'
               AND INSTR( `img_url`, '_2254_') > 0
               LIMIT 1;";

        $q = db_query( $s );
        // ant

        $nix_pictures = array();
        while ( $nix_picture_row = db_fetch_assoc( $q ) ) {
            // consolelog( $nix_picture_row["img_url"] );
            $nix_pictures[] = $nix_picture_row;
        }

        ## если нет основного вида _2254
        if ( count( $nix_pictures ) < 1 ) {
            $nix_pictures = array();
            $s            = "SELECT `photoID`, `img_url` as `filename`, `img_url` as `thumbnail`, `img_url` as `enlarged`
                   FROM `PRICE_Pictures`
                   WHERE `offer_id`='" . $product["product_code"] . "'
                   LIMIT 1;";

            $q = db_query( $s );
            // ant

            $nix_pictures = array();
            while ( $nix_picture_row = db_fetch_assoc( $q ) ) {
                // consolelog( $nix_picture_row["img_url"] );
                $nix_pictures[] = $nix_picture_row;
            }
        }

        $product["USE_NIX_PICTURES"] = 1;
        $product["picture"]          = $nix_pictures[0]["filename"];
        $product["thumbnail"]        = $nix_pictures[0]["thumbnail"];
        $product["big_picture"]      = $nix_pictures[0]["enlarged"];

    }

}

function _setNixParams( &$product ) {
    if ( isset( $product['product_code'] ) && !is_null( $product['product_code'] ) && isset( $product['productID'] ) ) {
        $s = "SELECT * FROM `PRICE_Params` WHERE `offer_id`='{$product["product_code"]}';";

        $s = <<<SQL
        SELECT DISTINCT `title`,
                        `value`,
                        `offer_id`,
                        `index`,
                        `paramID`
        FROM `PRICE_Params`
        WHERE `offer_id`='{$product["product_code"]}'
        ORDER BY `index`, `paramID` DESC
SQL;

        $q = db_query( $s );

        while ( $row = db_fetch_assoc( $q ) ) {
            // consolelog( $nix_picture_row["img_url"] );
            foreach ( $row as $key => $value ) {
                # code...
                $product["nixru_params"][$key][] = $value;
            }
        }

    } else {
        $product["nixru_params"] = null;
    }

}

function _setNixOptions( &$product ) {
    if ( isset( $product['product_code'] ) && !is_null( $product['product_code'] ) && isset( $product['productID'] ) ) {
        $fields1_array = array(
            // "id",
             // "parent_id",
             // "Name",
             // "Col1",
             // "Col2",
             // "Col3",
             // "Col4",
             // "Col5",
             // "Col6",
             "`GroupName` AS `Группа`",
            "`Warranty` AS `Гарантия (мес)`",
            // "Prefix",
             "ROUND(`Weight`,3) AS `Вес (кг)`",
            "ROUND(`Volume`,3) AS `Объем (л)`",
            "`date_modified` AS `Обновлено`",
            // "ApproxAmount",
             "href",
            "vendorID",
            "`is_newproduct` AS `НОВИНКА!!!`",
            // "product_code",
            // "GroupID",
            // "date_from_price AS ",
            // "date_added",
            // "is_processed",
            // "is_published",
            // "enabled",
            // "OriginalName",
        );

        $fields2_array = array(
            // "id",
             // "offer_id",
             // "name",
             "`brand` AS `Марка`",
            "`model` AS  `Модель`",
            "`typePrefix` AS `Тип оборудования`",
            "url",
            // "last_modify",
            // "date_added",
            // "date_modified",
            // "productID",
        );

        $price_params = array();
        $YML_params   = array();

        $product["nixru_options"] = array();

        $s_YML_params = "
        SELECT " . implode( ", ", $fields2_array ) . " FROM `PRICE_Offers` WHERE `offer_id`='" . $product['product_code'] . "';";
        $q_YML_params = db_query( $s_YML_params );
        while ( $row = db_fetch_assoc( $q_YML_params ) ) {
            // consolelog( $nix_picture_row["img_url"] );
            foreach ( $row as $key => $value ) {
                # code...
                $product["nixru_options"][$key] = $value;
            }
        }

        $s_price_params = "
        SELECT " . implode( ", ", $fields1_array ) . " FROM `PRICE_Products__nixru` WHERE `product_code`='" . $product['product_code'] . "';";
        $q_price_params = db_query( $s_price_params );
        while ( $row = db_fetch_assoc( $q_price_params ) ) {
            // consolelog( $nix_picture_row["img_url"] );
            foreach ( $row as $key => $value ) {
                # code...
                $product["nixru_options"][$key] = $value;
            }
        }

    } else {
        $product["nixru_options"] = null;
    }

}

function GetProductInSubCategories( $callBackParam, &$count_row, $navigatorParams = null, $for_admin = 0 ) {

    if ( $navigatorParams != null ) {
        $offset         = $navigatorParams["offset"];
        $CountRowOnPage = $navigatorParams["CountRowOnPage"];
    } else {
        $offset         = 0;
        $CountRowOnPage = 0;
    }

    $categoryID         = $callBackParam["categoryID"];
    $subCategoryIDArray = catGetSubCategories( $categoryID );
    $cond               = "";
    foreach ( $subCategoryIDArray as $subCategoryID ) {
        if ( $cond != "" ) {
            $cond .= " OR categoryID=" . (int)$subCategoryID;
        } else {
            $cond .= " categoryID=" . (int)$subCategoryID . " ";
        }

    }
    $whereClause = "";
    if ( $cond != "" ) {
        $whereClause = " where " . $cond;
    }

    $result = array();
    if ( $whereClause == "" ) {
        $count_row = 0;
        return $result;
    }

    $q = db_query( "select categoryID, name, brief_description, " .
        " customers_rating, Price, in_stock, " .
        " customer_votes, list_price, " .
        " productID, default_picture, sort_order from " . PRODUCTS_TABLE .
        " " . $whereClause . " order by " . CONF_DEFAULT_SORT_ORDER );
    $i = 0;
    while ( $row = db_fetch_row( $q ) ) {
        if (  ( $i >= $offset && $i < $offset + $CountRowOnPage ) ||
            $navigatorParams == null ) {
            $row["PriceWithUnit"]      = show_price( $row["Price"] );
            $row["list_priceWithUnit"] = show_price( $row["list_price"] );
            // you save (value)
            $row["SavePrice"] = show_price( $row["list_price"] - $row["Price"] );

            // you save (%)
            if ( $row["list_price"] ) {
                $row["SavePricePercent"] = ceil(  (  (  ( $row["list_price"] - $row["Price"] ) / $row["list_price"] ) * 100 ) );
            }

            _setPictures( $row );

            $row["product_extra"]          = GetExtraParametrs( $row["productID"] );
            $row["PriceWithOutUnit"]       = show_priceWithOutUnit( $row["Price"] );
            $row["list_priceWithOutUnit"]  = show_priceWithOutUnit( $row["list_price"] );
            $row["PriceWithOutUnitF"]      = show_priceWithOutUnitF( $row["Price"] );
            $row["list_priceWithOutUnitF"] = show_priceWithOutUnitF( $row["list_price"] );
# Расширенный ВЫВОД ПРОДУКТОВ
            $in_stock_res            = prdInStockDeliveryDelay( $row["in_stock"], CONF_EXACT_PRODUCT_BALANCE, CONF_INSTOCK_REPLACE_BY_SYMBOLS, $for_admin );
            $row["in_stock"]         = $in_stock_res["in_stock"];
            $row["qnt"]              = $in_stock_res["in_stock"];
            $row["in_stock_request"] = $in_stock_res["in_stock_request"];
            $row["in_stock_string"]  = $in_stock_res["in_stock_string"];
            $row["in_stock_symbol"]  = $in_stock_res["in_stock_symbol"];
            if ( $isadmin ) {
                $row["in_stock_admin"] = $in_stock_res["in_stock_admin"];
            }
# Расширенный ВЫВОД ПРОДУКТОВ
            $result[] = $row;
        }
        $i++;
    }
    $count_row = $i;
    return $result;
}

// *****************************************************************************
// Purpose        gets all products by categoryID
// Inputs             $callBackParam item
//                        "categoryID"
//                        "fullFlag"
// Remarks
// Returns
function prdGetProductByCategory( $callBackParam, &$count_row, $navigatorParams = null, $for_admin = 0 ) {

    if ( $navigatorParams != null ) {
        $offset         = $navigatorParams["offset"];
        $CountRowOnPage = $navigatorParams["CountRowOnPage"];
    } else {
        $offset         = 0;
        $CountRowOnPage = 0;
    }

    $result = array();

    $categoryID = $callBackParam["categoryID"];
    $fullFlag   = $callBackParam["fullFlag"];
    if ( $fullFlag ) {
        $conditions = array( " categoryID=" . (int)$categoryID . " " );
        $q          = db_query( "select productID from " .
            CATEGORIY_PRODUCT_TABLE . " where  categoryID=" . (int)$categoryID );
        while ( $products = db_fetch_row( $q ) );
        $conditions[] = " productID=" . (int)$products[0];

        $data = array();
        foreach ( $conditions as $cond ) {
            $q = db_query( "select categoryID, name, brief_description, " .
                " customers_rating, Price, in_stock, " .
                " customer_votes, list_price, " .
                " productID, default_picture, sort_order, items_sold, enabled, product_code from " . PRODUCTS_TABLE .
                " where " . $cond . " order by " . CONF_DEFAULT_SORT_ORDER );
            while ( $row = db_fetch_row( $q ) ) {
                $row["PriceWithUnit"]      = show_price( $row["Price"] );
                $row["list_priceWithUnit"] = show_price( $row["list_price"] );
                // you save (value)
                $row["SavePrice"] = show_price( $row["list_price"] - $row["Price"] );

                // you save (%)
                if ( $row["list_price"] ) {
                    $row["SavePricePercent"] = ceil(  (  (  ( $row["list_price"] - $row["Price"] ) / $row["list_price"] ) * 100 ) );
                }

                _setPictures( $row );
                $row["product_extra"]          = GetExtraParametrs( $row["productID"] );
                $row["product_extra_count"]    = count( $row["product_extra"] );
                $row["PriceWithOutUnit"]       = show_priceWithOutUnit( $row["Price"] );
                $row["list_priceWithOutUnit"]  = show_priceWithOutUnit( $row["list_price"] );
                $row["PriceWithOutUnitF"]      = show_priceWithOutUnitF( $row["Price"] );
                $row["list_priceWithOutUnitF"] = show_priceWithOutUnitF( $row["list_price"] );
                # Расширенный ВЫВОД ПРОДУКТОВ
                $in_stock_res            = prdInStockDeliveryDelay( $row["in_stock"], CONF_EXACT_PRODUCT_BALANCE, CONF_INSTOCK_REPLACE_BY_SYMBOLS, $for_admin );
                $row["in_stock"]         = $in_stock_res["in_stock"];
                $row["qnt"]              = $in_stock_res["in_stock"];
                $row["in_stock_request"] = $in_stock_res["in_stock_request"];
                $row["in_stock_string"]  = $in_stock_res["in_stock_string"];
                $row["in_stock_symbol"]  = $in_stock_res["in_stock_symbol"];
                if ( $isadmin ) {
                    $row["in_stock_admin"] = $in_stock_res["in_stock_admin"];
                }
                # Расширенный ВЫВОД ПРОДУКТОВ
                $data[] = $row;
            }
        }

        function _compare( $row1, $row2 ) {
            if ( (int)$row1["sort_order"] == (int)$row2["sort_order"] ) {
                return 0;
            }

            return ( (int)$row1["sort_order"] < (int)$row2["sort_order"] ) ? -1 : 1;
        }

        usort( $data, "_compare" );

        $result = array();
        $i      = 0;
        $ccdata = count( $data );
        for ( $s = 0; $s < $ccdata; $s++ ) {
            if (  ( $i >= $offset && $i < $offset + $CountRowOnPage ) ||
                $navigatorParams == null ) {
                $result[] = $data[$s];
            }

            $i++;
        }
        $count_row = $i;
        return $result;
    } else {
        $q = db_query( "select categoryID, name, brief_description, " .
            " customers_rating, Price, in_stock, " .
            " customer_votes, list_price, " .
            " productID, default_picture, sort_order, items_sold, enabled, product_code from " . PRODUCTS_TABLE .
            " where categoryID=" . (int)$categoryID . " order by " . CONF_DEFAULT_SORT_ORDER );
        $i = 0;
        while ( $row = db_fetch_row( $q ) ) {
            if (  ( $i >= $offset && $i < $offset + $CountRowOnPage ) ||
                $navigatorParams == null ) {
                $result[] = $row;
            }

            $i++;
        }
        $count_row = $i;
        return $result;
    }
}

function _getConditionWithCategoryConjWithSubCategoriesOLD( $condition, $categoryID ) //fetch products from current category and all its subcategories
{
    $new_condition = "";
    $tempcond      = "";

    $categoryID_Array   = catGetSubCategories( $categoryID );
    $categoryID_Array[] = (int)$categoryID;

    foreach ( $categoryID_Array as $catID ) {
        if ( $new_condition != "" ) {
            $new_condition .= " OR ";
        }

        $new_condition .= _getConditionWithCategoryConj( $tempcond, $catID );

    }
    if ( $condition == "" ) {
        return $new_condition;
    } else {
        return $condition . " AND (" . $new_condition . ")";
    }

}

function _getConditionWithCategoryConjWithSubCategories( $condition, $categoryID ) //fetch products from current category and all its subcategories
{
    $new_condition = "";
    $tempcond      = "";

    $categoryID_Array   = catGetSubCategories( $categoryID );
    $categoryID_Array[] = (int)$categoryID;

# BEGIN оптимизируем SQL-запросы

    foreach ( $categoryID_Array as $catID ) {
        if ( $new_condition != "" ) {
            $new_condition .= " OR ";
        }

        $new_condition .= _getConditionWithCategoryConj( $tempcond, $catID );

    }

    $incat = implode( ',', $categoryID_Array );
    $data  = db_query( "SELECT productID FROM " . CATEGORIY_PRODUCT_TABLE . " WHERE categoryID IN ($incat)" );
    $inprd = array();
    while ( $row = db_fetch_assoc( $data ) ) {
        $inprd[] = $row['productID'];
    }

    $new_condition = ( count( $inprd ) ? ( 'productID IN(' . implode( ',', $inprd ) . ') OR ' ) : '' ) . "categoryID IN ($incat)";
#       if ($condition != "") $new_condition = "$condition AND ($new_condition)";
    # END оптимизируем SQL-запросы
    if ( $condition == "" ) {
        return $new_condition;
    } else {
        return $condition . " AND (" . $new_condition . ")";
    }

}

function _getConditionWithCategoryConj( $condition, $categoryID ) //fetch products from current category
{
    $category_condition = "";
    $q                  = db_query( "select productID from " .
        CATEGORIY_PRODUCT_TABLE . " where categoryID=" . (int)$categoryID );
    $icounter = 0;
    while ( $product = db_fetch_row( $q ) ) {
        if ( $icounter == 0 ) {
            $category_condition .= " productID IN (";
        }

        if ( $icounter > 0 ) {
            $category_condition .= ",";
        }

        $category_condition .= (int)$product[0];
        $icounter++;
    }
    if ( $icounter > 0 ) {
        $category_condition .= ")";
    }

    if ( $condition == "" ) {
        if ( $category_condition == "" ) {
            return "categoryID=" . (int)$categoryID;
        } else {
            return "(" . $category_condition . " OR categoryID=" . (int)$categoryID . ")";
        }

    } else {
        if ( $category_condition == "" ) {
            return $condition . " AND categoryID=" . (int)$categoryID;
        } else {
            return "(" . $condition . " AND (" . $category_condition . " OR categoryID=" . (int)$categoryID . "))";
        }

    }
}

// *****************************************************************************
// Purpose
// Inputs
//                                $productID - product ID
//                                $template  - array of item
//                                        "optionID"        - option ID
//                                        "value"                - value or variant ID
// Remarks
// Returns        returns true if product matches to extra parametr template
//                        false otherwise
function _testExtraParametrsTemplate( $productID, &$template ) {

    // get category ID
    $categoryID = $template["categoryID"];

    foreach ( $template as $key => $item ) {

        if ( !isset( $item["optionID"] ) ) {
            continue;
        }

        if ( (string)$key == "categoryID" ) {
            continue;
        }

        // get value to search
        if ( $item['set_arbitrarily'] == 1 ) {

            $valueFromForm = $item["value"];
        } else {

            if ( (int)$item["value"] == 0 ) {
                continue;
            }

            if ( !isset( $template[$key]['__option_value_from_db'] ) ) {

                $SQL = 'select option_value FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE variantID=?
                                ';
                $option_value                             = db_fetch_assoc( db_phquery( $SQL, (int)$item['value'] ) );
                $template[$key]['__option_value_from_db'] = $option_value['option_value'];
            }
            $valueFromForm = $template[$key]['__option_value_from_db'];
        }

        // get option value
        $SQL = 'select option_value, option_type FROM ?#PRODUCT_OPTIONS_VALUES_TABLE WHERE optionID=? AND productID=?
                ';
        $q = db_phquery( $SQL, (int)$item['optionID'], (int)$productID );

        if ( !( $row = db_fetch_row( $q ) ) ) {

            if ( trim( $valueFromForm ) == '' ) {
                continue;
            } else {
                return false;
            }

        }

        $option_value      = $row['option_value'];
        $option_type       = $row['option_type'];
        $valueFromDataBase = array();

        if ( $option_type == 0 ) {

            $valueFromDataBase[] = $option_value;
        } else {

            $SQL = 'select povv.option_value FROM ?#PRODUCTS_OPTIONS_SET_TABLE as pos
                                LEFT JOIN ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE as povv ON pos.variantID=povv.variantID WHERE pos.optionID=? AND pos.productID=?
                        ';
            $Result = db_phquery( $SQL, (int)$item["optionID"], (int)$productID );
            while ( $Row = db_fetch_assoc( $Result ) ) {

                $valueFromDataBase[] = $Row['option_value'];
            }
        }

        if ( trim( $valueFromForm ) != '' ) {

            $existFlag = false;
            $vcount    = count( $valueFromDataBase );
            for ( $v = 0; $v < $vcount; $v++ ) {
                if ( strstr( strtolower( (string)trim( $valueFromDataBase[$v] ) ), strtolower( (string)trim( $valueFromForm ) ) ) ) {
                    $existFlag = true;
                    break;
                }
            }
            if ( !$existFlag ) {
                return false;
            }

        }
    }
    return true;
}

function _deletePercentSymbol( &$str ) {
    $str = str_replace( "%", "", $str );
    $str = str_replace( ">", "", $str );
    $str = str_replace( "<", "", $str );
    return $str;
}

function prdSearchProductByTemplateAdmin( $callBackParam, &$count_row, $navigatorParams = null, $for_admin = 1 ) {
    // navigator params
    if ( $navigatorParams != null ) {
        $offset         = xEscSQL( $navigatorParams["offset"] );
        $CountRowOnPage = xEscSQL( $navigatorParams["CountRowOnPage"] );
    } else {
        $offset         = 0;
        $CountRowOnPage = 0;
    }

    if ( isset( $callBackParam["extraParametrsTemplate"] ) ) {

        $replicantExtraParametersTpl = $callBackParam["extraParametrsTemplate"];
    }
    // special symbol prepare
    if ( isset( $callBackParam["search_simple"] ) ) {
/*                for( $i=0; $i<count($callBackParam["search_simple"]); $i++ )
{
$callBackParam["search_simple"][$i] = $callBackParam["search_simple"][$i];
}*/
        _deletePercentSymbol( $callBackParam["search_simple"] );
    }
    if ( isset( $callBackParam["name"] ) ) {
        for ( $i = 0; $i < count( $callBackParam["name"] ); $i++ ) {
            $callBackParam["name"][$i] = xToText( trim( $callBackParam["name"][$i] ) );
        }

        _deletePercentSymbol( $callBackParam["name"][$i] );
    }
    if ( isset( $callBackParam["product_code"] ) ) {
        for ( $i = 0; $i < count( $callBackParam["product_code"] ); $i++ ) {
            $callBackParam["product_code"][$i] = xToText( trim( $callBackParam["product_code"][$i] ) );
        }
        _deletePercentSymbol( $callBackParam["product_code"] );
    }
# BEGIN Search-in-description
    if ( isset( $callBackParam["description"] ) ) {
        for ( $i = 0; $i < count( $callBackParam["description"] ); $i++ ) {
            $callBackParam["description"][$i] = xToText( trim( $callBackParam["description"][$i] ) );
        }
        _deletePercentSymbol( $callBackParam["description"] );
    }
# END Search-in-description

    if ( isset( $callBackParam["extraParametrsTemplate"] ) ) {
        foreach ( $callBackParam["extraParametrsTemplate"] as $key => $value ) {
            if ( is_int( $key ) ) {
                $callBackParam["extraParametrsTemplate"][$key] = xEscSQL( trim( $callBackParam["extraParametrsTemplate"][$key] ) );
                _deletePercentSymbol( $callBackParam["extraParametrsTemplate"][$key] );
            }
        }
    }

    $where_clause = "";

    if ( isset( $callBackParam["search_simple"] ) ) {
        if ( !count( $callBackParam["search_simple"] ) ) //empty array
        {
            $where_clause = " where 0";
        } else //search array is not empty
        {
            $sscount = count( $callBackParam["search_simple"] );
            for ( $n = 0; $n < $sscount; $n++ ) {
                if ( $where_clause != "" ) {
                    $where_clause .= " AND ";
                }

                $where_clause .= " ( LOWER(name) LIKE '%" . xToText( trim( strtolower( $callBackParam["search_simple"][$n] ) ) ) . "%' OR " .
                "   LOWER(description) LIKE '%" . xEscSQL( trim( strtolower( $callBackParam["search_simple"][$n] ) ) ) . "%' OR " .
                "   LOWER(product_code) LIKE '%" . xEscSQL( trim( strtolower( $callBackParam["search_simple"][$n] ) ) ) . "%' OR " .
                "   LOWER(brief_description) LIKE '%" . xEscSQL( trim( strtolower( $callBackParam["search_simple"][$n] ) ) ) . "%' ) ";
            }

            if ( $where_clause != "" ) {
                $where_clause = " where categoryID>1 and enabled=1 and " . $where_clause;
            } else {
                $where_clause = " where categoryID>1 and enabled=1";
            }

        }

    } else {

        // "enabled" parameter
        if ( isset( $callBackParam["enabled"] ) ) {
            if ( $where_clause != "" ) {
                $where_clause .= " AND ";
            }

            $where_clause .= " enabled=" . (int)$callBackParam["enabled"];
        }

        // take into "name" parameter
        if ( isset( $callBackParam["name"] ) ) {
            foreach ( $callBackParam["name"] as $name ) {
                if ( strlen( $name ) > 0 ) {
                    if ( $where_clause != "" ) {
                        $where_clause .= " AND ";
                    }

                    $where_clause .= " LOWER(name) LIKE '%" . xToText( trim( strtolower( $name ) ) ) . "%' ";
                }
            }

        }

        // take into "product_code" parameter
        if ( isset( $callBackParam["product_code"] ) ) {
            foreach ( $callBackParam["product_code"] as $product_code ) {
                if ( $where_clause != "" ) {
                    $where_clause .= " AND ";
                }

                $where_clause .= " LOWER(product_code) LIKE '%" . xToText( trim( strtolower( $product_code ) ) ) . "%' ";
            }
        }
# BEGIN Search-in-description
        // take into "description" parameter
        if ( isset( $callBackParam["description"] ) ) {
            foreach ( $callBackParam["description"] as $description ) {
                if ( $where_clause != "" ) {
                    $where_clause .= " AND ";
                }

                $where_clause .= " LOWER(description) LIKE '%" . xToText( trim( strtolower( $description ) ) ) . "%' ";
            }
        }
# END Search-in-description

        // take into "price" parameter
        if ( isset( $callBackParam["price"] ) ) {
            $price = $callBackParam["price"];

            if ( trim( $price["from"] ) != "" && $price["from"] != null ) {
                if ( $where_clause != "" ) {
                    $where_clause .= " AND ";
                }

                $from = ConvertPriceToUniversalUnit( $price["from"] );
                $where_clause .= " Price>=" . (double)$from . " ";
            }
            if ( trim( $price["to"] ) != "" && $price["to"] != null ) {
                if ( $where_clause != "" ) {
                    $where_clause .= " AND ";
                }

                $to = ConvertPriceToUniversalUnit( $price["to"] );
                $where_clause .= " Price<=" . (double)$to . " ";
            }
        }

        // categoryID
        if ( isset( $callBackParam["categoryID"] ) ) {
            $searchInSubcategories = false;
            if ( isset( $callBackParam["searchInSubcategories"] ) ) {
                if ( $callBackParam["searchInSubcategories"] ) {
                    $searchInSubcategories = true;
                } else {
                    $searchInSubcategories = false;
                }

            }

            if ( $searchInSubcategories ) {
                $where_clause = _getConditionWithCategoryConjWithSubCategories( $where_clause,
                    $callBackParam["categoryID"] );
            } else {
                $where_clause = _getConditionWithCategoryConj( $where_clause,
                    $callBackParam["categoryID"] );
            }
        }

        if ( $where_clause != "" ) {
            $where_clause = "where " . $where_clause;
        }

    }

    $order_by_clause = "order by " . CONF_DEFAULT_SORT_ORDER;

    if ( isset( $callBackParam["sort"] ) ) {
        if ( $callBackParam["sort"] == "categoryID" ||
            $callBackParam["sort"] == "name" ||
            $callBackParam["sort"] == "brief_description" ||
            $callBackParam["sort"] == "in_stock" ||
            $callBackParam["sort"] == "Price" ||
            $callBackParam["sort"] == "customer_votes" ||
            $callBackParam["sort"] == "customers_rating" ||
            $callBackParam["sort"] == "list_price" ||
            $callBackParam["sort"] == "sort_order" ||
            $callBackParam["sort"] == "items_sold" ||
            $callBackParam["sort"] == "product_code" ||
            $callBackParam["sort"] == "shipping_freight" ||
            $callBackParam["sort"] == "viewed_times" ) {
            $order_by_clause = " order by " . xEscSQL( $callBackParam["sort"] ) . " ASC ";
            if ( isset( $callBackParam["direction"] ) ) {
                if ( $callBackParam["direction"] == "DESC" ) {
                    $order_by_clause = " order by " . xEscSQL( $callBackParam["sort"] ) . " DESC ";
                }
            }

        }
    }

    $sqlQueryCount  = "select count(*) from " . PRODUCTS_TABLE . " " . $where_clause;
    $q              = db_query( $sqlQueryCount );
    $products_count = db_fetch_row( $q );
    $products_count = $products_count[0];
    $limit_clause   = ( isset( $callBackParam["extraParametrsTemplate"] ) || !$CountRowOnPage ) ? "" : " LIMIT " . $offset . "," . $CountRowOnPage;
    $sqlQuery       = "select categoryID, name, brief_description, " .
        " customers_rating, Price, in_stock, " .
        " customer_votes, list_price, " .
        " productID, default_picture, sort_order, items_sold, enabled, " .
        " product_code, description, shipping_freight, viewed_times, min_order_amount from " . PRODUCTS_TABLE . " " .
        $where_clause . " " . $order_by_clause . $limit_clause;

    $q      = db_query( $sqlQuery );
    $result = array();
    $i      = 0;

    if ( $offset >= 0 && $offset <= $products_count ) {
        while ( $row = db_fetch_row( $q ) ) {

            if ( isset( $callBackParam["extraParametrsTemplate"] ) ) {

                // take into "extra" parametrs
                $testResult = _testExtraParametrsTemplate( $row["productID"], $replicantExtraParametersTpl );
                if ( !$testResult ) {
                    continue;
                }

            }

            if (  (  ( $i >= $offset || !isset( $callBackParam["extraParametrsTemplate"] ) ) && $i < $offset + $CountRowOnPage ) ||
                $navigatorParams == null ) {
                $row["PriceWithUnit"]      = show_price( $row["Price"] );
                $row["list_priceWithUnit"] = show_price( $row["list_price"] );
                // you save (value)
                $row["SavePrice"] = show_price( $row["list_price"] - $row["Price"] );

                // you save (%)
                if ( $row["list_price"] ) {
                    $row["SavePricePercent"] = ceil(  (  (  ( $row["list_price"] - $row["Price"] ) / $row["list_price"] ) * 100 ) );
                }

                _setPictures( $row );
                $row["product_extra"]          = GetExtraParametrs( $row["productID"] );
                $row["product_extra_count"]    = count( $row["product_extra"] );
                $row["PriceWithOutUnit"]       = show_priceWithOutUnit( $row["Price"] );
                $row["list_priceWithOutUnit"]  = show_priceWithOutUnit( $row["list_price"] );
                $row["PriceWithOutUnitF"]      = show_priceWithOutUnitF( $row["Price"] );
                $row["list_priceWithOutUnitF"] = show_priceWithOutUnitF( $row["list_price"] );
# Расширенный ВЫВОД ПРОДУКТОВ
                $in_stock_res            = prdInStockDeliveryDelay( $row["in_stock"], CONF_EXACT_PRODUCT_BALANCE, CONF_INSTOCK_REPLACE_BY_SYMBOLS, $for_admin );
                $row["in_stock"]         = $in_stock_res["in_stock"];
                $row["qnt"]              = $in_stock_res["in_stock"];
                $row["in_stock_request"] = $in_stock_res["in_stock_request"];
                $row["in_stock_string"]  = $in_stock_res["in_stock_string"];
                $row["in_stock_symbol"]  = $in_stock_res["in_stock_symbol"];
                if ( $isadmin ) {
                    $row["in_stock_admin"] = $in_stock_res["in_stock_admin"];
                }
# Расширенный ВЫВОД ПРОДУКТОВ
                if (  ( (double)$row["shipping_freight"] ) > 0 ) {
                    $row["shipping_freightUC"] = show_price( $row["shipping_freight"] );
                }

                $row["name"]              = $row["name"];
                $row["description"]       = $row["description"];
                $row["brief_description"] = $row["brief_description"];
                $row["product_code"]      = $row["product_code"];
                $row["viewed_times"]      = $row["viewed_times"];
                $row["items_sold"]        = $row["items_sold"];
                $result[]                 = $row;
            }
            $i++;
        }
    }
    $count_row = isset( $callBackParam["extraParametrsTemplate"] ) ? $i : $products_count;
    return $result;
}

// *****************************************************************************
// Purpose        gets all products by categoryID
// Inputs             $callBackParam item
//                                        "search_simple"                                - string search simple
//                                        "sort"                                        - column name to sort
//                                        "direction"                                - sort direction DESC - by descending,
//                                                                                                by ascending otherwise
//                                        "searchInSubcategories" - if true function searches
//                                                product in subcategories, otherwise it does not
//                                        "searchInEnabledSubcategories"        - this parametr is actual when
//                                                                                        "searchInSubcategories" parametr is specified
//                                                                                        if true this function take in mind enabled categories only
//                                        "categoryID"        - is not set or category ID to be searched
//                                        "name"                        - array of name template
//                                        "product_code"                - array of product code template
//                                        "price"                        -
//                                                                array of item
//                                                                        "from"        - down price range
//                                                                        "to"        - up price range
//                                        "enabled"                - value of column "enabled"
//                                                                        in database
//                                        "extraParametrsTemplate"
// Remarks
// Returns
function prdSearchProductByTemplate( $callBackParam, &$count_row, $navigatorParams = null, $for_admin = 0 ) {
    // navigator params
    if ( $navigatorParams != null ) {
        $offset         = xEscSQL( $navigatorParams["offset"] );
        $CountRowOnPage = xEscSQL( $navigatorParams["CountRowOnPage"] );
    } else {
        $offset         = 0;
        $CountRowOnPage = 0;
    }

    if ( isset( $callBackParam["extraParametrsTemplate"] ) ) {

        $replicantExtraParametersTpl = $callBackParam["extraParametrsTemplate"];
    }
    // special symbol prepare
    if ( isset( $callBackParam["search_simple"] ) ) {
/*                for( $i=0; $i<count($callBackParam["search_simple"]); $i++ )
{
$callBackParam["search_simple"][$i] = $callBackParam["search_simple"][$i];
}*/
        _deletePercentSymbol( $callBackParam["search_simple"] );
    }

    if ( isset( $callBackParam["name"] ) ) {
        for ( $i = 0; $i < count( $callBackParam["name"] ); $i++ ) {
            $callBackParam["name"][$i] = xToText( trim( $callBackParam["name"][$i] ) );
        }

        _deletePercentSymbol( $callBackParam["name"][$i] );
    }
    if ( isset( $callBackParam["product_code"] ) ) {
        for ( $i = 0; $i < count( $callBackParam["product_code"] ); $i++ ) {
            $callBackParam["product_code"][$i] = xToText( trim( $callBackParam["product_code"][$i] ) );
        }
        _deletePercentSymbol( $callBackParam["product_code"] );
    }

    if ( isset( $callBackParam["extraParametrsTemplate"] ) ) {
        foreach ( $callBackParam["extraParametrsTemplate"] as $key => $value ) {
            if ( is_int( $key ) ) {
                $callBackParam["extraParametrsTemplate"][$key] = xEscSQL( trim( $callBackParam["extraParametrsTemplate"][$key] ) );
                _deletePercentSymbol( $callBackParam["extraParametrsTemplate"][$key] );
            }
        }
    }

    $where_clause = "";

    if ( isset( $callBackParam["search_simple"] ) ) {
        if ( !count( $callBackParam["search_simple"] ) ) //empty array
        {
            $where_clause = " where 0";
        } else //search array is not empty
        {
            $sscount = count( $callBackParam["search_simple"] );
            for ( $n = 0; $n < $sscount; $n++ ) {
                if ( $where_clause != "" ) {
                    $where_clause .= " AND ";
                }

                $where_clause .= " ( LOWER(name) LIKE '%" . xToText( trim( strtolower( $callBackParam["search_simple"][$n] ) ) ) . "%' OR " .
                "   LOWER(description) LIKE '%" . xEscSQL( trim( strtolower( $callBackParam["search_simple"][$n] ) ) ) . "%' OR " .
                "   LOWER(product_code) LIKE '%" . xEscSQL( trim( strtolower( $callBackParam["search_simple"][$n] ) ) ) . "%' OR " .
                "   LOWER(brief_description) LIKE '%" . xEscSQL( trim( strtolower( $callBackParam["search_simple"][$n] ) ) ) . "%' ) ";
            }

            if ( $where_clause != "" ) {
                $where_clause = " where categoryID>1 and enabled=1 and " . $where_clause;
            } else {
                $where_clause = " where categoryID>1 and enabled=1";
            }

            if ( CONF_CHECKSTOCK && CONF_SHOW_NULL_STOCK ) {
                if ( $where_clause != "" ) {
                    $where_clause .= " AND in_stock>0 ";
                } else {
                    $where_clause = "where in_stock>0 ";
                }

            }
        }

    } else {

        // "enabled" parameter
        if ( isset( $callBackParam["enabled"] ) ) {
            if ( $where_clause != "" ) {
                $where_clause .= " AND ";
            }

            $where_clause .= " enabled=" . (int)$callBackParam["enabled"];
        }

        // take into "name" parameter
        if ( isset( $callBackParam["name"] ) ) {
            foreach ( $callBackParam["name"] as $name ) {
                if ( strlen( $name ) > 0 ) {
                    if ( $where_clause != "" ) {
                        $where_clause .= " AND ";
                    }

                    $where_clause .= " LOWER(name) LIKE '%" . xToText( trim( strtolower( $name ) ) ) . "%' ";
                }
            }

        }

        // take into "product_code" parameter
        if ( isset( $callBackParam["product_code"] ) ) {
            foreach ( $callBackParam["product_code"] as $product_code ) {
                if ( $where_clause != "" ) {
                    $where_clause .= " AND ";
                }

                $where_clause .= " LOWER(product_code) LIKE '%" . xToText( trim( strtolower( $product_code ) ) ) . "%' ";
            }
        }

        // take into "price" parameter
        if ( isset( $callBackParam["price"] ) ) {
            $price = $callBackParam["price"];

            if ( trim( $price["from"] ) != "" && $price["from"] != null ) {
                if ( $where_clause != "" ) {
                    $where_clause .= " AND ";
                }

                $from = ConvertPriceToUniversalUnit( $price["from"] );
                $where_clause .= " Price>=" . (double)$from . " ";
            }
            if ( trim( $price["to"] ) != "" && $price["to"] != null ) {
                if ( $where_clause != "" ) {
                    $where_clause .= " AND ";
                }

                $to = ConvertPriceToUniversalUnit( $price["to"] );
                $where_clause .= " Price<=" . (double)$to . " ";
            }
        }
# ant фильтровка по наличию

        $filterStockExtended = ( isset( $_SESSION["log"] ) and isset( $_SESSION["FILTER_STOCK"] ) ) ? (int)$_SESSION["FILTER_STOCK"] : 0;
        switch ( $filterStockExtended ) {
            case 0:
                $addonStockExtended = "";
                break;
            case 1:
                $addonStockExtended = " AND in_stock > 0 ";
                break;
            case 2:
                $addonStockExtended = " AND ( (in_stock > 0)  OR (in_stock =-2) OR (in_stock =-3) OR (in_stock =-4) OR (in_stock =-5) OR (in_stock =-6) OR (in_stock =-7) OR (in_stock =-8) OR (in_stock =-9) ) ";
                break;
            case 3:
                $addonStockExtended = " AND ( (in_stock =-2) OR (in_stock =-3) OR (in_stock =-4) OR (in_stock =-5) OR (in_stock =-6) OR (in_stock =-7) OR (in_stock =-8) OR (in_stock =-9) ) ";
                break;
            case 4:
                $addonStockExtended = " AND (in_stock =-1) ";
                break;
            case 5:
                $addonStockExtended = " AND (in_stock = 0) ";
                break;
            case 6:
                $addonStockExtended = " AND in_stock = -1000 ";
                break;
            case 7:
                $addonStockExtended = " AND in_stock > 10 ";
                break;
            case 8:
                $addonStockExtended = " AND in_stock <= 5 AND in_stock > 0 ";
                break;
            default:
                $addonStockExtended = "";
                break;
                /* endswitch; */
        }

# ant фильтровка по наличию

        if ( CONF_CHECKSTOCK && CONF_SHOW_NULL_STOCK ) {
            if ( $where_clause != "" ) {
                $where_clause .= " AND in_stock>0 ";
            } else {
                $where_clause = "WHERE in_stock>0 ";
            }

        }
# ant фильтровка по наличию
        else {
            if ( $where_clause != "" ) {
                $where_clause .= $addonStockExtended;
            } else {
                $where_clause = $addonStockExtended;
            }
        }
# ant фильтровка по наличию
        // categoryID
        if ( isset( $callBackParam["categoryID"] ) ) {
            $searchInSubcategories = false;
            if ( isset( $callBackParam["searchInSubcategories"] ) ) {
                if ( $callBackParam["searchInSubcategories"] ) {
                    $searchInSubcategories = true;
                } else {
                    $searchInSubcategories = false;
                }

            }

            if ( $searchInSubcategories ) {
                $where_clause = _getConditionWithCategoryConjWithSubCategories( $where_clause,
                    $callBackParam["categoryID"] );
            } else {
                $where_clause = _getConditionWithCategoryConj( $where_clause,
                    $callBackParam["categoryID"] );
            }
        }

        if ( $where_clause != "" ) {
            $where_clause = "where " . $where_clause;
        }

    }

    $order_by_clause = "order by " . CONF_DEFAULT_SORT_ORDER . "";

    if ( isset( $callBackParam["sort"] ) ) {
        if ( $callBackParam["sort"] == "categoryID" ||
            $callBackParam["sort"] == "name" ||
            $callBackParam["sort"] == "brief_description" ||
            $callBackParam["sort"] == "in_stock" ||
            $callBackParam["sort"] == "Price" ||
            $callBackParam["sort"] == "customer_votes" ||
            $callBackParam["sort"] == "customers_rating" ||
            $callBackParam["sort"] == "list_price" ||
            $callBackParam["sort"] == "sort_order" ||
            $callBackParam["sort"] == "items_sold" ||
            $callBackParam["sort"] == "product_code" ||
            $callBackParam["sort"] == "shipping_freight" ||
            $callBackParam["sort"] == "viewed_times" ) {
            $order_by_clause = " order by " . xEscSQL( $callBackParam["sort"] ) . " ASC ";
            if ( isset( $callBackParam["direction"] ) ) {
                if ( $callBackParam["direction"] == "DESC" ) {
                    $order_by_clause = " order by " . xEscSQL( $callBackParam["sort"] ) . " DESC ";
                }
            }

        }
    }

    $sqlQueryCount  = "SELECT count(*) from " . PRODUCTS_TABLE . " " . $where_clause;
    $q              = db_query( $sqlQueryCount );
    $products_count = db_fetch_row( $q );
    $products_count = $products_count[0];
    $limit_clause   = ( isset( $callBackParam["extraParametrsTemplate"] ) || !$CountRowOnPage ) ? "" : " LIMIT " . $offset . "," . $CountRowOnPage;
    $sqlQuery       = "SELECT categoryID, name, brief_description, " .
        " customers_rating, Price, in_stock, " .
        " customer_votes, list_price, " .
        " productID, default_picture, sort_order, items_sold, enabled, " .
        " product_code, description, shipping_freight, viewed_times, min_order_amount FROM " . PRODUCTS_TABLE . " " .
        $where_clause . " " . $order_by_clause . $limit_clause;

    $q      = db_query( $sqlQuery );
    $result = array();
    $i      = 0;

    if ( $offset >= 0 && $offset <= $products_count ) {
        while ( $row = db_fetch_row( $q ) ) {

# [ДОПОЛНЕНИЕ] РАЗНАЯ СКИДКА ДЛЯ РАЗНЫХ КАТЕГОРИЙ
            $row["Price"] = CatDiscount( $row["Price"], $row["categoryID"] );
# END[ДОПОЛНЕНИЕ] РАЗНАЯ СКИДКА ДЛЯ РАЗНЫХ КАТЕГОРИЙ

            if ( isset( $callBackParam["extraParametrsTemplate"] ) ) {

                // take into "extra" parametrs
                $testResult = _testExtraParametrsTemplate( $row["productID"], $replicantExtraParametersTpl );
                if ( !$testResult ) {
                    continue;
                }

            }

            if (  (  ( $i >= $offset || !isset( $callBackParam["extraParametrsTemplate"] ) ) && $i < $offset + $CountRowOnPage ) ||
                $navigatorParams == null ) {
                $row["PriceWithUnit"]      = show_price( $row["Price"] );
                $row["list_priceWithUnit"] = show_price( $row["list_price"] );
                // you save (value)
                $row["SavePrice"] = show_price( $row["list_price"] - $row["Price"] );

                // you save (%)
                if ( $row["list_price"] ) {
                    $row["SavePricePercent"] = ceil(  (  (  ( $row["list_price"] - $row["Price"] ) / $row["list_price"] ) * 100 ) );
                }

                _setPictures( $row );
                $row["product_extra"]          = GetExtraParametrs( $row["productID"] );
                $row["product_extra_count"]    = count( $row["product_extra"] );
                $row["PriceWithOutUnit"]       = show_priceWithOutUnit( $row["Price"] );
                $row["list_priceWithOutUnit"]  = show_priceWithOutUnit( $row["list_price"] );
                $row["PriceWithOutUnitF"]      = show_priceWithOutUnitF( $row["Price"] );
                $row["list_priceWithOutUnitF"] = show_priceWithOutUnitF( $row["list_price"] );
                # Расширенный ВЫВОД ПРОДУКТОВ
                $in_stock_res            = prdInStockDeliveryDelay( $row["in_stock"], CONF_EXACT_PRODUCT_BALANCE, CONF_INSTOCK_REPLACE_BY_SYMBOLS, $for_admin );
                $row["in_stock"]         = $in_stock_res["in_stock"];
                $row["qnt"]              = $in_stock_res["in_stock"];
                $row["in_stock_request"] = $in_stock_res["in_stock_request"];
                $row["in_stock_string"]  = $in_stock_res["in_stock_string"];
                $row["in_stock_symbol"]  = $in_stock_res["in_stock_symbol"];
                if ( $isadmin ) {
                    $row["in_stock_admin"] = $in_stock_res["in_stock_admin"];
                }
                # Расширенный ВЫВОД ПРОДУКТОВ
                if (  ( (double)$row["shipping_freight"] ) > 0 ) {
                    $row["shipping_freightUC"] = show_price( $row["shipping_freight"] );
                }

                $row["name"]              = $row["name"];
                $row["description"]       = $row["description"];
                $row["brief_description"] = $row["brief_description"];
                $row["product_code"]      = $row["product_code"];
                $row["viewed_times"]      = $row["viewed_times"];
                $row["items_sold"]        = $row["items_sold"];
                $result[]                 = $row;
            }
            $i++;
        }
    }
    $count_row = isset( $callBackParam["extraParametrsTemplate"] ) ? $i : $products_count;
    return $result;
}

function prdGetMetaKeywordTag( $productID ) {
    $q = db_query( "select meta_description from " . PRODUCTS_TABLE . " where productID=" . (int)$productID );
    if ( $row = db_fetch_row( $q ) ) {
        return $row["meta_description"];
    } else {
        return "";
    }

}

function prdGetMetaTags( $productID ) //gets META keywords and description - an HTML code to insert into <head> section
{
    $q = db_query( "select meta_description, meta_keywords from " .
        PRODUCTS_TABLE . " where productID=" . (int)$productID );
    $row              = db_fetch_row( $q );
    $meta_description = $row["meta_description"];
    $meta_keywords    = $row["meta_keywords"];

    $res = "";

    if ( $meta_description != "" ) {
        $res .= "<meta name=\"Description\" content=\"" . $meta_description . "\">\n";
    }

    if ( $meta_keywords != "" ) {
        $res .= "<meta name=\"KeyWords\" content=\"" . $meta_keywords . "\" >\n";
    }

    return $res;
}

?>