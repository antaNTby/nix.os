<?php

if ( !strcmp( $sub, "order_statuses" ) ) {
    if ( CONF_BACKEND_SAFEMODE != 1 && ( !isset( $_SESSION["log"] ) || !in_array( 9, $relaccess ) ) ) //unauthorized
    {
        $smarty->assign( "admin_sub_dpt", "error_forbidden.tpl.html" );
    } else {

        if ( isset( $_GET["delete"] ) ) {
            //this action is forbidden when SAFE MODE is ON
            if ( CONF_BACKEND_SAFEMODE ) {
                Redirect( ADMIN_FILE . "?dpt=custord&sub=order_statuses&safemode=yes" );
            }

            if ( !ostDeleteOrderStatus( $_GET["delete"] ) ) {
                $smarty->assign( "prompt", ADMIN_COULDNT_DELETE_ORDER_STATUS );
            }

        }

        if ( isset( $_POST["save"] ) ) {
            //this action is forbidden when SAFE MODE is ON
            if ( CONF_BACKEND_SAFEMODE ) {
                Redirect( ADMIN_FILE . "?dpt=custord&sub=order_statuses&safemode=yes" );
            }

            if ( trim( $_POST["new_status_name"] ) != "" ) {
                $sort_order = (int)$_POST["new_sort_order"];
                ostAddOrderStatus( trim( $_POST["new_status_name"] ), $sort_order );
            }
            $updateData = ScanPostVariableWithId( array( "status_name", "sort_order" ) );
            foreach ( $updateData as $key => $value ) {
                ostUpdateOrderStatus( $key, $value["status_name"], $value["sort_order"] );
            }

            Redirect( ADMIN_FILE . "?dpt=custord&sub=order_statuses&save_successful=yes" );

        }

        if ( isset( $_GET["save_successful"] ) ) //show successful save confirmation message
        {
            $smarty->assign( "configuration_saved", 1 );
        }

        $order_statues = ostGetOrderStatuses( false, 'html' );

        $smarty->assign( "order_statues", $order_statues );

        //set sub-department template
        $smarty->assign( "admin_sub_dpt", "custord_order_statuses.tpl.html" );
    }
}
?>