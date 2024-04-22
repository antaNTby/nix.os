<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

if ( isset( $address_book ) && isset( $_SESSION["log"] ) ) {
    if ( isset( $_GET["delete"] ) ) {
        $aID = (int)$_GET["delete"];
        if ( regGetAddressByLogin( $aID, $_SESSION["log"] ) ) // delete address only if belongs to customer
        {
            redDeleteAddress( $aID );
        }
    }

    if ( isset( $_POST["save"] ) ) {
        $aID = (int)$_POST["DefaultAddress"];
        if ( regGetAddressByLogin( $aID, $_SESSION["log"] ) ) // update default address only if belongs to customer
        {
            regSetDefaultAddressIDByLogin( $_SESSION["log"], $aID );
        }
    }

    $addresses = regGetAllAddressesByLogin( $_SESSION["log"] );
    for ( $i = 0; $i < count( $addresses ); $i++ ) {
        $addresses[$i]["addressStr"] = regGetAddressStr( $addresses[$i]["addressID"] );
    }

    $defaultAddressID = regGetDefaultAddressIDByLogin( $_SESSION["log"] );

    $smarty->assign( "defaultAddressID", $defaultAddressID );
    $smarty->assign( "addresses", $addresses );
    $smarty->assign( "PageH1", STRING_ADDRESS_BOOK );
    $smarty->assign( "main_content_template", "address_book.tpl.html" );
}

?>