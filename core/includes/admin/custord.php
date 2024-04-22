<?php

//define a new admin department
$admin_dpt = array(
    "id"              => "custord",                  //department ID
     "sort_order"      => 10,                         //sort order (less `sort_order`s appear first)
     "name"            => ADMIN_CUSTOMERS_AND_ORDERS, //department name
     "sub_departments" => array
    (
        array( "id" => "companies", "name" => ADMIN_STRING_COMPANIES ),
        // array( "id" => "new_orders", "name" => ADMIN_NEW_ORDERS ),
        array( "id" => "orders", "name" => ADMIN_NEW_ORDERS ),
        array( "id" => "invoices", "name" => ADMIN_STRING_INVOICES ),
        array( "id" => "contracts", "name" => ADMIN_STRING_CONTRACTS ),
        array( "id" => "order_statuses", "name" => ADMIN_ORDER_STATUES ),
        array( "id" => "custlist", "name" => ADMIN_CUSTOMERS ),
        array( "id" => "custgroup", "name" => ADMIN_CUSTGROUP ),
        array( "id" => "discounts", "name" => ADMIN_DISCOUNTS ),
        // array( "id" => "subscribers", "name" => ADMIN_NEWS_SUBSCRIBERS ),
        // array( "id" => "reg_fields", "name" => ADMIN_CUSTOMER_REG_FIELDS ),
        // array( "id" => "aux_pages", "name" => ADMIN_AUX_PAGES ),
        // array( "id" => "affiliate", "name" => STRING_AFFILIATE_PROGRAM ),
    ),
);
add_department( $admin_dpt );

//show department if it is being selected
if ( $dpt == "custord" ) {
    //set default sub department if required
    if ( !isset( $sub ) ) {
        // $sub = "new_orders";
        $sub = "orders";
    }

    //sub-department file exists
    if ( file_exists( "core/includes/admin/sub/" . $admin_dpt["id"] . "_$sub.php" ) ) {
        //assign admin main department template
        $smarty->assign( "admin_main_content_template", $admin_dpt["id"] . ".tpl.html" );
        //assign subdepts
        $smarty->assign( "admin_sub_departments", $admin_dpt["sub_departments"] );
        $smarty->assign( "antSUB", $sub);
        //include selected sub-department
        include_once "core/includes/admin/sub/" . $admin_dpt["id"] . "_$sub.php";
    }
    //no sub department found
    else {
        $smarty->assign( "admin_main_content_template", "notfound.tpl.html" );
    }

}
