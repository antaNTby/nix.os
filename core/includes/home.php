<?php
//special offers for home.tpl.html

// debug( $_GET );


# тяжелые запросы и нужны только на стартовой странице
if ( $_GET === array() ) {
// echo "SUPERMETKA";

#####################################special offers###########################################################
    $result = array();
//INNER JOIN `" . PRODUCT_PICTURES . "` AS t ON (products.default_picture=t.photoID
    // AND products.productID=t.productID)
    // t.filename
    $q = db_query( "
SELECT products.productID,
       products.categoryID,
       products.name,
       products.Price,
       products.brief_description,
       products.product_code,
       products.default_picture,
       products.enabled,
       special_offers.productID
FROM `UTF_special_offers` AS special_offers
INNER JOIN `UTF_products` AS products ON (special_offers.productID=products.productID)
 ORDER BY special_offers.sort_order
 LIMIT 12" );

    while ( $row = db_fetch_row( $q ) ) {
        $nixru_picture = dbGetFieldData( "PRICE_Pictures", "img_url", "`offer_id`='" . $row["product_code"] . "' LIMIT 1" );
        if ( !strlen( $nixru_picture ) ) {
            // $row["image_path"] = "data/images/empty.gif";
            $row["image_path"] = "";
        } else {
            $row["USE_NIX_PICTURE"] = 1;
            $row["image_path"]      = $nixru_picture;
        }
        $row["cena"]  = $row["Price"];
        $row["Price"] = show_price( $row["Price"] );
        $result[]     = $row;
    }
    $smarty->assign( "special_offers", $result );
#####################################special offers###########################################################
    #####################################special offers###########################################################
    #####################################special offers###########################################################

#####################################rand_products###########################################################
    $cifra      = 12; //количество rand_products товаров для выбора
    $result_ids = array();
    $q          = db_query( "SELECT products.productID
FROM " . PRODUCTS_TABLE . " AS products
RIGHT JOIN `PRICE_Pictures` AS nix ON ( products.product_code = nix.offer_id)
WHERE  products.enabled=1 AND products.in_stock<>0  AND products.in_stock<>-1000 AND products.in_stock<>0 AND products.categoryID > 1 AND nix.img_url LIKE '%_2254_%'
  AND products.Price>=1" );

    while ( $row = db_fetch_row( $q ) ) {
        $result_ids[] = $row[0];
    }

    $rand_products = array();

    for ( $i = 0; $i < $cifra; $i++ ) {
        $random_id = $result_ids[rand( 1, count( $result_ids ) - 1 )];
        $q         = db_query( "SELECT products.productID,
       products.product_code,
       products.name,
       products.Price,
       products.enabled,
       t.filename AS image_path,
       products.categoryID
FROM `" . PRODUCTS_TABLE . "` AS products
LEFT JOIN `" . PRODUCT_PICTURES . "` AS t ON (products.default_picture=t.photoID AND products.productID=t.productID)
WHERE products.productID='" . $random_id . "' LIMIT 1" );

        $result1      = array();
        $row          = db_fetch_row( $q );
        $row['Price'] = CatDiscount( $row['Price'], $row['categoryID'] );

        if ( strlen( $row["image_path"] ) > 0 && file_exists( "data/small/" . $row["image_path"] ) ) {
            $row["name"]       = htmlspecialchars_decode( $row["name"] );
            $row["image_path"] = "small/" . $row["image_path"];
            $row["cena"]       = $row["Price"];
            $row["Price"]      = show_price( $row["Price"] );
            $result1           = $row;

        } else {
            $nixru_picture = dbGetFieldData( "PRICE_Pictures", "img_url", "`img_url` LIKE '%_2254_%' AND `offer_id`='" . $row["product_code"] . "' LIMIT 1" );
            if ( !strlen( $nixru_picture ) ) {
                // $row["image_path"] = "data/images/empty.gif";
                $row["image_path"] = "";
            } else {
                $row["USE_NIX_PICTURE"] = 1;
                $row["image_path"]      = $nixru_picture;
            }
            $row["cena"]  = $row["Price"];
            $row["Price"] = show_price( $row["Price"] );
            $result1      = $row;
        }
        $rand_products[] = $result1;

    }

// $smarty->assign( "rand_product", $rand_products[rand( 1, count( $cifra ) - 1 )] );
    $smarty->assign( "rand_products", $rand_products );
#####################################rand_products###########################################################
    #####################################rand_products###########################################################
    #####################################rand_products###########################################################

#####################################new_products###########################################################
    // $cifra  = 3; //количество new_products товаров для выбора
    // $result = array();

// // LEFT JOIN `" . PRODUCT_PICTURES . "` AS t ON (products.default_picture=t.photoID AND products.productID=t.productID)
    // // t.filename,
    // $q = db_query( "
    // SELECT products.productID,
    // products.product_code,
    // products.name,
    // products.Price,
    // products.enabled,
    // products.categoryID,
    // nix.photoID
    // FROM `" . PRODUCTS_TABLE . "` AS products
    // LEFT JOIN `PRICE_Pictures` AS nix ON ( products.product_code = nix.offer_id)
    // WHERE products.categoryID >1 AND products.enabled='1' AND nix.img_url LIKE '%_2254_%'
    // ORDER BY products.date_added DESC, nix.photoID DESC LIMIT 1," . $cifra . ";" , 1   );

// while ( $row = db_fetch_row( $q ) ) {
    //     $row["name"]  = htmlspecialchars_decode( $row["name"] );
    //     $row['Price'] = CatDiscount( $row['Price'], $row['categoryID'] );
    //     if ( strlen( $row["image_path"] ) > 0 && file_exists( "data/small/" . $row["image_path"] ) ) {
    //         $row["image_path"] = "small/" . $row["image_path"];
    //         $row["cena"]     = $row["Price"];
    //         $row["Price"]    = show_price( $row["Price"] );
    //         $result[]        = $row;

//     } else {
    //         $nixru_picture = dbGetFieldData( "PRICE_Pictures", "img_url", "`offer_id`='" . $row["product_code"] . "' LIMIT 1" );
    //         if ( !strlen( $nixru_picture ) ) {
    //             $row["image_path"] = "/data/images/empty.gif";
    //         } else {
    //             $row["USE_NIX_PICTURE"] = 1;
    //             $row["image_path"]        = $nixru_picture;
    //         }
    //         $row["cena"]  = $row["Price"];
    //         $row["Price"] = show_price( $row["Price"] );
    //         $result[]     = $row;
    //     }
    // }
    // $smarty->assign( "new_products", $result );
    #####################################new_products###########################################################
    #####################################new_products###########################################################
    #####################################new_products###########################################################

#####################################popular_products###########################################################
    // $cifra  = 3; //количество popular_products товаров для выбора
    // $result = array();

// // LEFT JOIN `" . PRODUCT_PICTURES . "` AS t ON (products.default_picture=t.photoID AND products.productID=t.productID)
    // // t.filename,
    // $q = db_query( "
    // SELECT products.productID,
    // products.product_code,
    // products.name,
    // products.Price,
    // products.enabled,
    // products.categoryID,
    //  nix.offer_id
    // FROM `" . PRODUCTS_TABLE . "` AS products
    // LEFT JOIN `PRICE_Pictures` AS nix ON ( products.product_code = nix.offer_id)
    // WHERE products.categoryID >1 AND products.in_stock<>-1000 AND products.in_stock<>0 AND products.enabled='1' AND nix.img_url LIKE '%_2254_%'
    // ORDER BY products.viewed_times DESC LIMIT 1, " . $cifra . ";"   );

// while ( $row = db_fetch_row( $q ) ) {
    //     $row['Price'] = CatDiscount( $row['Price'], $row['categoryID'] );
    //     if ( strlen( $row["image_path"] ) > 0 && file_exists( "data/small/" . $row["image_path"] ) ) {
    //         $row["name"]     = htmlspecialchars_decode( $row["name"] );
    //         $row["image_path"] = "small/" . $row["image_path"];
    //         $row["cena"]     = $row["Price"];
    //         $row["Price"]    = show_price( $row["Price"] );
    //         $result[]        = $row;

//     } else {
    //         $nixru_picture = dbGetFieldData( "PRICE_Pictures", "img_url", "`offer_id`='" . $row["product_code"] . "' LIMIT 1" );
    //         if ( !strlen( $nixru_picture ) ) {
    //             $row["image_path"] = "/data/images/empty.gif";
    //         } else {
    //             $row["USE_NIX_PICTURE"] = 1;
    //             $row["image_path"]        = $nixru_picture;
    //         }

//         $row["cena"]  = $row["Price"];
    //         $row["Price"] = show_price( $row["Price"] );
    //         $result[]     = $row;
    //     }
    // }
    // $smarty->assign( "popular_products", $result );
    #####################################popular_products###########################################################
    #####################################popular_products###########################################################
    #####################################popular_products###########################################################

// $smarty->assign( "PageH1", "Приветствуем вас на сайте www.nixminsk.by" );

}

?>