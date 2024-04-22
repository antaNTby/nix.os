<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

if ( !strcmp( $sub, "contracts" ) ) {
    if ( CONF_BACKEND_SAFEMODE != 1 && ( !isset( $_SESSION["log"] ) || !in_array( 16, $relaccess ) ) ) //unauthorized
    {
        $smarty->assign( "admin_sub_dpt", "error_forbidden.tpl.html" );
    } else {

        // редактирвание контракта
        if ( isset( $_GET["edit_mode"] ) ) {

            $editID = (int)$_GET["edit_mode"];

            if ( isset( $_POST["save_all"] ) ) {

                if ( isset( $_POST["area_text0"] ) ) {
                    contrUpdateField( $editID, "dogovor_body", $_POST["area_text0"] );
                }
                if ( isset( $_POST["area_text1"] ) ) {
                    contrUpdateField( $editID, "schet_body", $_POST["area_text1"] );
                }
                if ( isset( $_POST["area_text2"] ) ) {
                    contrUpdateField( $editID, "warranty_body", $_POST["area_text2"] );
                }

                if ( isset( $_POST["contract_title_" . $editID] ) ) {
                    contrUpdateField( $editID, "contract_title", $_POST["contract_title_" . $editID] );
                }

                // if ( isset( $_POST["read_only_".$editID] ) ) {
                //     contrUpdateField( $editID, "read_only_", $_POST["read_only_".$editID] );
                // }
            }

            if ( $editID > 0 ) {

                // debug($editID);

                $edit_contract = contrGetContract( $editID );

                if ( file_exists( "core/temp/mpdf_legend.json" ) ) {

                    $fp         = fopen( "core/temp/mpdf_legend.json", 'r' );
                    $mpdf_legend = fgets( $fp );
                    $smarty->assign( "mpdf_legend", json_decode( $mpdf_legend ) );
                    fclose( $fp );
                }

                if ( $edit_contract["contractID"] >= 1 ) {
                    $smarty->assign( "edit_mode", 1 );
                    $smarty->assign( "contractID", $editID );
                    $smarty->assign( "edit_contract", $edit_contract );

                    // $arrayRES = array();
                    // $arrayRES = array( 'ID' => $editID, 'edit_contract' => $edit_contract );

                    // debug($edit_contract);

                    // echo json_encode( $arrayRES );
                    // die();

                } else {
                    $smarty->assign( "edit_mode", 0 );
                    $contracts = contrGetAllContracts();
                    $smarty->assign( "contracts", $contracts );
                }
            }

        }

        // Действия с записями таблицы
        elseif ( isset( $_GET["delete"] ) ) {

            contrDeleteContract( $_GET["delete"] );
            Redirect( ADMIN_FILE . "?dpt=custord&sub=contracts" );
        } elseif ( isset( $_GET["duplicate"] ) ) {

            contrDuplicateContract( $_GET["duplicate"] );
            Redirect( ADMIN_FILE . "?dpt=custord&sub=contracts" );
        } elseif ( isset( $_GET["preview"] ) ) {

            $ID    = (int)$_GET["preview"];
            $texts = contrLoadTexts( $ID );

            $arrayRES = array();
            $arrayRES = array( 'ID' => $ID, 'texts' => $texts );

            echo json_encode( $arrayRES );
            die();
        } elseif ( isset( $_GET["update"] ) ) {

            // $contract   = contrGetContract( $_GET["update"] );
            $ID         = (int)$_POST["ID"];
            $ajax_field = trim( $_POST["ajax_field"] );
            $new_value  = trim( $_POST["new_value"] );

            if ( (int)$_GET["update"] == ( contrUpdateField( $ID, $ajax_field, $new_value ) ) ) {

                $new_contract = contrGetContract( $ID );
                $smarty->assign( "contract", $new_contract );
                // $smarty->assign( "edit", 1 );
                // debug($new_contract);
                $arrayRES = array();
                $arrayRES = array( 'ID' => $ID, 'ajax_field' => $ajax_field, 'new_value' => $new_value, 'new_contract' => $new_contract );

                echo json_encode( $arrayRES );
                die();
            }

        } else {
            $smarty->assign( "edit_mode", 0 );
            $contracts = contrGetAllContracts();
            $smarty->assign( "contracts", $contracts );
        }

        //set sub-department template
        $smarty->assign( "admin_sub_dpt", "custord_contracts.tpl.html" );
    }
}
?>