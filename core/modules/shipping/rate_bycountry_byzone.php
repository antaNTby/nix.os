<?php
/*
shipping rates definition by countries/zones
 */
define( 'SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE', DB_PRFX . '_module_shipping_bycountries_byzones_rates' );

function _create_JS_zones_handlers( $zones, $value ) //zones handlers
{
    $zones_handlers = "";
    foreach ( $zones as $zone ) {
        $zoneID = (int)$zone["zoneID"];
        $zones_handlers .= "document.shipping_method_form.shipping_module_bycountry_byzone_zone_rate_" . $zoneID . ".disabled = " . $value . ";\n";
    }
    return $zones_handlers;
}

function settingCONF_BYCOUNTRY_BYZONE_FORM() {
    $curr_country = isset( $_POST["shipping_module_bycountry_byzone_country"] ) ? $_POST["shipping_module_bycountry_byzone_country"] : 0;
    $curr_country = (int)$curr_country;
    if ( !$curr_country ) {

        $curr_country = xGetData( 'SXshipping_module_bycountry_byzone_country' );
    }
    xSaveData( 'SXshipping_module_bycountry_byzone_country', $curr_country );
    $module_id    = isset( $_GET['setting_up'] ) ? $_GET['setting_up'] : 0;
    $ModuleConfig = modGetModuleConfig( $module_id );
    if ( !$ModuleConfig['ModuleClassName'] ) {
        $module_id = 0;
    }

    if ( isset( $_POST["save"] ) ) {
        $radiotoggle = isset( $_POST["shipping_module_bycountry_byzone_radiotoggle"] ) ? $_POST["shipping_module_bycountry_byzone_radiotoggle"] : 0;
        if ( $radiotoggle == 0 ) //fixed rate for this country
        {
            $rate = (float)$_POST["shipping_module_bycountry_byzone_zone_rate_0"];
            $sql  = '
                                DELETE FROM ' . SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE . '
                                WHERE countryID=' . $curr_country . ' AND zoneID=0 AND module_id=' . $module_id . '
                        ';
            db_query( $sql );
            $sql = '
                                INSERT INTO ' . SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE . '
                                (countryID, zoneID, shipping_rate, module_id)
                                VALUES (' . $curr_country . ', 0, ' . $rate . ',' . $module_id . ')
                        ';
            db_query( $sql );
        } else //by zone definition
        {
            $sql = '
                                DELETE FROM ' . SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE . '
                                WHERE countryID=' . $curr_country . ' AND module_id=' . $module_id . '
                        ';
            db_query( $sql );
            foreach ( $_POST as $key => $val ) {
                if ( strstr( $key, "shipping_module_bycountry_byzone_zone_rate_" ) ) {
                    $zone = (int)str_replace( "shipping_module_bycountry_byzone_zone_rate_", "", $key );
                    $rate = (float)$val;
                    if ( $zone > 0 && $rate != 0 ) //add rate
                    {
                        $sql = '
                                                        INSERT INTO ' . SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE . '
                                                        (countryID, zoneID, shipping_rate, module_id)
                                                        VALUES (' . $curr_country . ', ' . $zone . ', ' . $rate . ', ' . $module_id . ')
                                                ';
                        db_query( $sql );
                    }
                }
            }
        }
    }

    $res             = "<table border=0>\n<tr>\n<td>" . CSHIPPINGMODULEBYCOUNTRYBYZONE_TXT_SELECT_COUNTRY . "</td>\n";
    $countries_count = 0;
    $countries       = cnGetCountries( null, $countries_count );
    $res .= "<td><select class='form-control input-sm' name='shipping_module_bycountry_byzone_country'>\n";
    $res .= "<option value='0'>" . ADMIN_NOT_DEFINED . "</option>\n";

    foreach ( $countries as $country ) {
        $res .= "<option value='" . $country["countryID"] . "' ";
        if ( $curr_country == $country["countryID"] ) {
            $res .= " selected ";
        }

        $res .= ">";
        $res .= $country["country_name"];
        $res .= "</option>\n";
    }
    $res .= "</select></td>";
    $res .= "<td><input type='submit' name='shipping_module_bycountry_byzone_change_country' value=\"" . CSHIPPINGMODULEBYCOUNTRYBYZONE_TXT_5 . "\"></td>\n";
    $res .= "</tr>\n";

    //show toggle + zones
    if ( $curr_country > 0 ) {
        $zones = znGetZonesById( $curr_country );

        //toggle

        $res .= "<tr><td colspan=3><hr size=1></td></tr>\n";
        $res .= "<tr><td colspan=3>\n";

        $sql = '
                        SELECT shipping_rate FROM ' . SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE . '
                        WHERE countryID=' . $curr_country . ' and zoneID=0 AND module_id="' . $module_id . '"
                ';
        $q   = db_query( $sql );
        $row = db_fetch_row( $q );
        if ( $row || count( $zones ) == 0 ) {
            $radiotoggle = 0; //fixed for this country
            $rate        = (float)$row[0];
        } else {
            $radiotoggle = 1; //by zone definition
            $rate        = 0;
        }

        $res .= "<table border=0><tr><td valign=top>\n<input type='radio' name='shipping_module_bycountry_byzone_radiotoggle' value=0";
        if ( $radiotoggle == 0 ) {
            $res .= " checked";
        }

        $res .= " onclick='JavaScript:shipping_module_bycountry_byzone_toogleClickHandler();'></td>\n";
        $res .= "<td valign=top>" . CSHIPPINGMODULEBYCOUNTRYBYZONE_TXT_1 . "</td>\n";
        $res .= "<td><input type='text' class='form-control input-sm' name='shipping_module_bycountry_byzone_zone_rate_0' value=\"$rate\"><br>" . CSHIPPINGMODULEBYCOUNTRYBYZONE_TXT_2 . "</td></tr>\n";
        $res .= "<tr><td valign=top>\n<input type='radio' name='shipping_module_bycountry_byzone_radiotoggle' value=1";
        if ( $radiotoggle == 1 ) {
            $res .= " checked";
        }

        $res .= " onclick='JavaScript:shipping_module_bycountry_byzone_toogleClickHandler();'></td>\n";
        $res .= "<td colspan=2>" . CSHIPPINGMODULEBYCOUNTRYBYZONE_TXT_3 . "\n";

        //zones

        if ( count( $zones ) > 0 ) {
            $res .= "<p><table>\n";
            foreach ( $zones as $zone ) {
                $zoneID = (int)$zone["zoneID"];

                $sql = '
                                        SELECT shipping_rate FROM ' . SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE . '
                                        WHERE countryID=' . $curr_country . ' and zoneID=' . $zoneID . ' AND module_id=' . $module_id . '
                                ';
                $q   = db_query( $sql );
                $row = db_fetch_row( $q );

                $zone_shipping_rate = (float)$row[0];

                $res .= "<tr><td>" . $zone["zone_name"] . ":</td>";
                $res .= "<td><input type='text' class='form-control input-sm' name='shipping_module_bycountry_byzone_zone_rate_" . $zoneID . "' value='" . $zone_shipping_rate . "'></td></tr>";
            }
            $res .= "</table>\n";
        } else {
            $res .= "<p>&lt; " . CSHIPPINGMODULEBYCOUNTRYBYZONE_TXT_4 . " &gt;";
        }

        $res .= "</td></tr></table>\n";

        $res .= "</td></tr>\n";
    }

    $res .= "</table>";

    //javascript code
    if ( $curr_country > 0 ) {

        $res .= "
                                <script language='JavaScript'>
                                function shipping_module_bycountry_byzone_toogleClickHandler()
                                {

                                        if ( document.shipping_method_form.shipping_module_bycountry_byzone_radiotoggle[0].checked )
                                        {
                                                document.shipping_method_form.shipping_module_bycountry_byzone_zone_rate_0.disabled = false;
                                                " . _create_JS_zones_handlers( $zones, "true" ) . "
                                        }
                                        else if ( document.shipping_method_form.shipping_module_bycountry_byzone_radiotoggle[1].checked )
                                        {
                                                document.shipping_method_form.shipping_module_bycountry_byzone_zone_rate_0.disabled = true;
                                                " . _create_JS_zones_handlers( $zones, "false" ) . "
                                        }
                                }

                                shipping_module_bycountry_byzone_toogleClickHandler();
                                </script>
                ";
    }

    return $res;
}

/**
 * @connect_module_class_name CShippingModuleByCountryByZone
 *
 */

class CShippingModuleByCountryByZone extends ShippingRateCalculator {

    public $SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE;

    public function initVars() {

        $this->title       = CSHIPPINGMODULEBYCOUNTRYBYZONE_TTL;
        $this->description = CSHIPPINGMODULEBYCOUNTRYBYZONE_DSCR;
        $this->sort_order  = 1;

        $this->Settings[] = 'CONF_SHIPPING_MODULE_BYCOUNTRYBYZONE_IS_INSTALLED';
    }

    public function allow_shipping_to_address( $address ) //defines is shipping allowed to the specified address
    {
        $country = (int)$address["countryID"];
        $zone    = $address["zoneID"];

        $sql = '
                        SELECT COUNT(*) FROM ' . SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE . '
                        WHERE countryID=' . $country . ' AND module_id="' . $this->ModuleConfigID . '"
                ';
        $q   = db_query( $sql );
        $row = db_fetch_row( $q );
        if ( $row[0] > 0 ) //there are some rates defined
        {
            $sql = '
                                SELECT shipping_rate FROM ' . SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE . '
                                WHERE countryID=' . $country . ' AND zoneID=0 AND module_id="' . $this->ModuleConfigID . '"
                        ';
            $q   = db_query( $sql );
            $row = db_fetch_row( $q );
            if ( $row ) //fixed rate for this country
            {
                return ( $row[0] < 0 ) ? 0 : 1;
            } else {
                if ( $zone != NULL ) {
                    $sql = '
                                                SELECT shipping_rate FROM ' . SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE . '
                                                WHERE countryID=' . $country . ' AND zoneID=' . $zone . ' AND module_id="' . $this->ModuleConfigID . '"
                                        ';
                    $q   = db_query( $sql );
                    $row = db_fetch_row( $q );
                    if ( $row ) {
                        return ( $row[0] < 0 ) ? 0 : 1;
                    }
                }
            }
        }

        return 1;
    }

    function calculate_shipping_rate( $order, $address, $_shServiceID=0 )
    //core shipping rate calculation routine
    //returns float value in case of correct calculation, and error string in case of error
    {
        $result = array();
        if ( !count( $this->getShippingProducts( $order ) ) ) {
            $result = 0;
// debugfile( $result, "rate_bycountry_byzone:255 NO PRODUCTS" );
            return $result;
        }

        $country = (int)$address["countryID"];
        $zone    = $address["zoneID"];

// debugfile($address,"calculate_shipping_rate => \$address rate_bycountry_byzone:262");

        $sql = 'SELECT COUNT(*) FROM ' . SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE . ' WHERE countryID=' . $country . ' AND module_id="' . $this->ModuleConfigID . '"';
        $q   = db_query( $sql,0);
        $row = db_fetch_row( $q );
        if ( $row[0] > 0 ) //there are some rates defined
        {
            $sql = 'SELECT shipping_rate FROM ' . SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE . ' WHERE countryID=' . $country . ' AND zoneID=0 AND module_id="' . $this->ModuleConfigID . '"';
            $q   = db_query( $sql,0);
            $row = db_fetch_row( $q );
            if ( $row ) //fixed rate for this country
            {
                $result = $row[0];
                return $result;
            } else {
                if ( $zone == NULL ) //undefined zone => return 0
                {
                    $result = 0;
                    return $result;
                } else {
                    $sql = 'SELECT shipping_rate FROM ' . SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE . ' WHERE countryID=' . $country . ' AND zoneID=' . $zone . ' AND module_id="' . $this->ModuleConfigID . '"';
                    $q   = db_query( $sql,0);
                    $row = db_fetch_row( $q );
                    if ( $row ) {
                        $result = $row[0];
                        return $result;
                    } else {
                        $result = 0;
                        return $result;
                    }
                }
            }
        }

        //otherwise return 0 (free shipping)
        $result = 0;
        return $result;
    }





    // public function calculate_shipping_rate( $order, $address ) //core shipping rate calculation routine
    //                                                    //returns float value in case of correct calculation, and error string in case of error
    // {
    //     if ( !count( $this->getShippingProducts( $order ) ) ) {
    //         return 0;
    //     }

    //     $country = (int)$address["countryID"];
    //     $zone    = $address["zoneID"];

    //     $sql = '
    //                     SELECT COUNT(*) FROM ' . SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE . '
    //                     WHERE countryID=' . $country . ' AND module_id="' . $this->ModuleConfigID . '"
    //             ';
    //     $q   = db_query( $sql );
    //     $row = db_fetch_row( $q );
    //     if ( $row[0] > 0 ) //there are some rates defined
    //     {
    //         $sql = '
    //                             SELECT shipping_rate FROM ' . SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE . '
    //                             WHERE countryID=' . $country . ' AND zoneID=0 AND module_id="' . $this->ModuleConfigID . '"
    //                     ';
    //         $q   = db_query( $sql );
    //         $row = db_fetch_row( $q );
    //         if ( $row ) //fixed rate for this country
    //         {
    //             return $row[0];
    //         } else {
    //             if ( $zone == NULL ) //undefined zone => return 0
    //             {
    //                 return 0;
    //             } else {
    //                 $sql = '
    //                                             SELECT shipping_rate FROM ' . SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE . '
    //                                             WHERE countryID=' . $country . ' AND zoneID=' . $zone . ' AND module_id="' . $this->ModuleConfigID . '"
    //                                     ';
    //                 $q   = db_query( $sql );
    //                 $row = db_fetch_row( $q );
    //                 if ( $row ) {
    //                     return $row[0];
    //                 } else {
    //                     return 0;
    //                 }
    //             }
    //         }
    //     }

    //     //otherwise return 0 (free shipping)
    //     return 0;
    // }

    public function install() {

        //create table to store shipping rate values

        //create new empty database

        if ( !in_array( strtolower( SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE ), db_get_all_tables() ) ) {

            $sql = '
                                CREATE TABLE ' . SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE . '
                                (module_id INT UNSIGNED NOT NULL, countryID INT, zoneID INT, shipping_rate FLOAT DEFAULT 0)
                        ';
            db_query( $sql );
        }

        $this->SettingsFields['CONF_SHIPPING_MODULE_BYCOUNTRYBYZONE_IS_INSTALLED'] = array(
            'settings_value'         => '1',
            'settings_title'         => CSHIPPINGMODULEBYCOUNTRYBYZONE_CNF_IS_INSTALLED_TTL,
            'settings_description'   => CSHIPPINGMODULEBYCOUNTRYBYZONE_CNF_IS_INSTALLED_DSCR,
            'settings_html_function' => 'settingCONF_BYCOUNTRY_BYZONE_FORM()',
            'sort_order'             => 1,
        );

        ShippingRateCalculator::install();
    }

    public function uninstall( $_ModuleConfigID = 0 ) {

        ShippingRateCalculator::uninstall( $_ModuleConfigID );

        if ( !count( modGetModuleConfigs( get_class( $this ) ) ) ) {

            //drop shipping rates table
            db_query( 'DROP TABLE IF EXISTS ' . SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE );
        } else {

            $sql = '
                                DELETE FROM ' . SHIPPING_BYCOUNTRIES_BYZONES_RATES_TABLE . ' WHERE module_id="' . $this->ModuleConfigID . '"
                        ';
        }
    }
}

?>