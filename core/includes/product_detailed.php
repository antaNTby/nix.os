<?php
##########################################
#        antaNT64 ShopCMS_UTF8           #
##########################################
// 6583031!!!6583031!!!6583031!!!

// echo 'product_detailed.php';

if ( isset( $_POST["cart_x"] ) ) //add product to cart
{
    $variants = array();
    foreach ( $_POST as $key => $val ) {
        if ( strstr( $key, "option_select_hidden_" ) ) {
            $variants[] = $val;
        }

    }
    unset( $_SESSION["variants"] );
    $_SESSION["variants"] = $variants;
    Redirect( "index.php?shopping_cart=yes&add2cart=" . (int)$_GET['productID'] . "&multyaddcount=" . (int)$_POST['multyaddcount'] );
}

//show product information
if ( isset( $productID ) && $productID > 0 && !isset( $_POST["add_topic"] ) && !isset( $_POST["discuss"] ) ) {
    $product = GetProduct( $productID );

    if ( !$product || $product["enabled"] == 0 ) {

        header( "HTTP/1.0 404 Not Found" );
        header( "HTTP/1.1 404 Not Found" );
        header( "Status: 404 Not Found" );
        die( ERROR_404_HTML );

    } else {

        if ( !$isadmin && !isset( $_GET["vote"] ) ) {
            IncrementProductViewedTimes( $productID );
        }

        $dontshowcategory = 1;

        $smarty->assign( "main_content_template", "product_detailed.tpl.html" );
        $smarty->assign( "PageH1", $product["name"] );

        $a = $product;

        $a["PriceWithUnit"]      = show_price( $a["Price"] );
        $a["list_priceWithUnit"] = show_price( $a["list_price"] );

        if (  ( (float)$a["shipping_freight"] ) > 0 ) {
            $a["shipping_freightUC"] = show_price( $a["shipping_freight"] );
        }

/*  КАРТИНКИ ПОКА ТОЛЬКО С НИКСА
if ( isset( $_GET["picture_id"] ) ) {
$picture = db_query( "select filename, thumbnail, enlarged from " .
PRODUCT_PICTURES . " where photoID=" . (int)$_GET["picture_id"] );
$picture_row = db_fetch_row( $picture );
} elseif ( !is_null( $a["default_picture"] ) ) {
$picture = db_query( "select filename, thumbnail, enlarged from " .
PRODUCT_PICTURES . " where photoID=" . (int)$a["default_picture"] );
$picture_row = db_fetch_row( $picture );
} else {
$picture = db_query(
"select filename, thumbnail, enlarged, photoID from " . PRODUCT_PICTURES .
" where productID=" . $productID );
if ( $picture_row = db_fetch_row( $picture ) ) {
$a["default_picture"] = $picture_row["photoID"];
} else {
$picture_row = null;
}

}

if ( $picture_row ) {
$a["picture"]     = $picture_row[0];
$a["thumbnail"]   = $picture_row[1];
$a["big_picture"] = $picture_row[2];
} else {
$a["picture"]     = "";
$a["thumbnail"]   = "";
$a["big_picture"] = "";
}
 */

##################################
        if ( $a["vendorID"] == "1" ) {
            _setPictures( $a );
            _setNixParams( $a );
            _setNixOptions( $a );
        }
##################################
        if ( $a ) //product found
        {

            if ( !isset( $categoryID ) ) {
                $categoryID = $a["categoryID"];
            }

/*
//get selected category info
$q   = db_query( "select categoryID, name, description, picture, allow_products_comparison FROM " . CATEGORIES_TABLE . " WHERE categoryID=" . (int)$categoryID );
$row = db_fetch_row( $q );
if ( $row ) {
if ( !file_exists( "data/category/" . $row[3] ) ) {
$row[3] = "";
}

$smarty->assign( "selected_category", $row );
$a["allow_products_comparison"] = $row[4];
} else {
$smarty->assign( "selected_category", NULL );
$a["allow_products_comparison"] = NULL;
}

 */
            //calculate a path to the category
            $product_category_path = catCalculatePathToCategory( (int)$categoryID );
            $Parent                = end( $product_category_path );
            $smarty->assign( "product_category_path", $product_category_path );

/*
if ( CONF_SHOW_PARENCAT ) {
$catrescur       = array();
$catrescur       = getcontentcatresc( $categoryID );
$catrescur_count = count( $catrescur );
$catrescur_half  = ceil( $catrescur_count / 2 );
$smarty->assign( "catrescur", $catrescur );
$smarty->assign( "catrescur_count", $catrescur_count );
$smarty->assign( "catrescur_half", $catrescur_half );

$sisters       = array();
$sisters       = getsisters( (int)$Parent["parent"] );
$sisters_count = count( $sisters );
$sisters_half  = ceil( $sisters_count / 2 );
$smarty->assign( "sisters", $sisters );
$smarty->assign( "sisters_count", $sisters_count );
$smarty->assign( "sisters_half", $sisters_half );
}

 */

/*
//reviews number   число обсуждений товара
$q = db_query( "select count(*) FROM " . DISCUSSIONS_TABLE . " WHERE productID=" . $productID );
$k = db_fetch_row( $q );
$k = $k[0];
 */

/*
//extra parameters
$extra      = GetExtraParametrs( (int)$productID );
$extracount = count( $extra );

 */

/*

//related items  - желаемые продукты
$related = array();
$q       = db_query( "select count(*) FROM " . RELATED_PRODUCTS_TABLE . " WHERE Owner=" . $productID );
$cnt     = db_fetch_row( $q );
$smarty->assign( "product_related_number", $cnt[0] );

if ( $cnt[0] > 0 ) {
$q = db_query( "select productID FROM " . RELATED_PRODUCTS_TABLE . " WHERE Owner=" . $productID );

while ( $row = db_fetch_row( $q ) ) {
$p = db_query( "select s.productID, s.name, s.Price, t.filename FROM " . PRODUCTS_TABLE . " AS s LEFT JOIN " . PRODUCT_PICTURES . "
AS t on (s.default_picture=t.photoID AND s.productID=t.productID) WHERE s.productID='" . $row[0] . "' and s.enabled=1" );
if ( $r = db_fetch_row( $p ) ) {
$r["Price"] = show_price( $r["Price"] );
if ( strlen( $r["filename"] ) > 0 && file_exists( "data/small/" . $r["filename"] ) ) {
$r["filename"] = "small/" . $r["filename"];
} else {
$r["filename"] = "empty.gif";
}
$related[] = $r;
}
}
}

 */

/*

// связанные продукты
$smarty->assign( "productslinkscat", getcontentprod( $productID ) );

 */

            // Выдает размеры картинок
            //update several product fields
            if ( !file_exists( "data/small/" . $a["picture"] ) ) {
                $a["picture"] = 0;
            }

            if ( !file_exists( "data/medium/" . $a["thumbnail"] ) ) {
                $a["thumbnail"] = 0;
            }

            if ( !file_exists( "data/big/" . $a["big_picture"] ) ) {
                $a["big_picture"] = 0;
            } elseif ( $a["big_picture"] ) {
                $size  = getimagesize( "data/big/" . $a["big_picture"] );
                $a[16] = $size[0] + 40;
                $a[17] = $size[1] + 30;
            }

            $a[12]                 = show_price( $a["Price"] );
            $a[13]                 = show_price( $a["list_price"] );
            $a[14]                 = show_price( $a["list_price"] - $a["Price"] ); //you save (value)
            $a["PriceWithOutUnit"] = show_priceWithOutUnit( $a["Price"] );
            if ( $a["list_price"] ) {
                $a[15] =
                    ceil(  (  (  ( $a["list_price"] - $a["Price"] ) / $a["list_price"] ) * 100 ) ); //you save (%)
            }

################################################################
            # если нет кошерных картинок то пробуем найти их на нихру
            $all_product_pictures    = array();
            $all_product_pictures_id = array();

            // запроса GET picture_id   нигде нету
            if ( isset( $_GET["picture_id"] ) ) {
                $pictures = db_query( "select photoID, filename, thumbnail, enlarged from " .
                    PRODUCT_PICTURES . " where photoID!=" . (int)$_GET["picture_id"] .
                    " AND productID=" . $productID );
            } elseif ( !is_null( $a["default_picture"] ) ) {
                $pictures = db_query( "select photoID, filename, thumbnail, enlarged from " .
                    PRODUCT_PICTURES . " where photoID!=" . $a["default_picture"] .
                    " AND productID=" . $productID );
            } else {
                $pictures = db_query( "select photoID, filename, thumbnail, enlarged from " .
                    PRODUCT_PICTURES . " where productID=" . $productID );
            }

            while ( $picture = db_fetch_row( $pictures ) ) {
                if ( $picture["filename"] != "" ) {
                    if ( file_exists( "data/small/" . $picture["filename"] ) ) {
                        if ( !file_exists( "data/medium/" . $picture["thumbnail"] ) ) {
                            $picture["thumbnail"] = 0;
                        }
                        if ( !file_exists( "data/big/" . $picture["enlarged"] ) ) {
                            $picture["enlarged"] = 0;
                        }
                        $all_product_pictures[]    = $picture;
                        $all_product_pictures_id[] = $picture[0];
                    }
                }
            }

            if (  ( !is_array( $all_product_pictures ) || !count( $all_product_pictures ) ) && ( $product["vendorID"] == 1 ) ) {
                $s = "SELECT photoID, img_url as filename, img_url as thumbnail, img_url as enlarged FROM PRICE_Pictures WHERE `offer_id`='" . $product["product_code"] . "' ORDER BY photoID ASC;";
                $q = db_query( $s );

                while ( $nix_picture_row = db_fetch_assoc( $q ) ) {

                    // Все  картинки с нихру
                    $all_product_pictures[]    = $nix_picture_row;
                    $all_product_pictures_id[] = $nix_picture_row["photoID"];

                    // главная картинка с нихру
                    _setPictures( $a );
                }
            }
################################################################

/*
//eproduct
if ( strlen( $a["eproduct_filename"] ) > 0 && file_exists( "core/files/" . $a["eproduct_filename"] ) ) {
#$size = filesize("core/files/".$a["eproduct_filename"]);
#if ($size > 1000) $size = round ($size / 1000);
#$a["eproduct_filesize"] = $size." Kb";
$a["eproduct_filesize"] = ceil( filesize( "core/files/" . $a["eproduct_filename"] ) / 1024.0 ) . 'кб';

} else {
$a["eproduct_filename"] = "";
}

 */

/*
//ПИСЬМО С ЗАПРОСМ ПО ПРОДУКТУ
//initialize product "request information" form in case it has not been already submitted
if ( !isset( $_POST["request_information"] ) ) {
if ( !isset( $_SESSION["log"] ) ) {
$customer_name  = "";
$customer_email = "";
} else {
$custinfo       = regGetCustomerInfo2( $_SESSION["log"] );
$customer_name  = $custinfo["first_name"] . " " . $custinfo["last_name"];
$customer_email = $custinfo["Email"];
}

$message_text = "";
}

$smarty->hassign( "customer_name", $customer_name );
$smarty->hassign( "customer_email", $customer_email );
$smarty->hassign( "message_text", $message_text );

if ( isset( $_GET["sent"] ) ) {
$smarty->assign( "sent", 1 );
}

 */

################################################################

################################################################

            $smarty->assign( "all_product_pictures_id", $all_product_pictures_id );
            $smarty->assign( "all_product_pictures", $all_product_pictures );
            $smarty->assign( "product_info", $a );
            $smarty->assign( "product_reviews_count", $k );
            $smarty->assign( "product_extra", $extra );
            $smarty->assign( "product_extra_count", $extracount );
            $smarty->assign( "product_related", $related );
        } else {
            //product not found
            header( "HTTP/1.0 404 Not Found" );
            header( "HTTP/1.1 404 Not Found" );
            header( "Status: 404 Not Found" );
            die( ERROR_404_HTML );
        }
    }
}

?>

