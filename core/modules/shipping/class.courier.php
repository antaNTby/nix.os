<?php
/**
 * @connect_module_class_name CourierShippingModule
 *
 */

class CourierShippingModule extends ShippingRateCalculator {

    public $DB_TABLE;

    /*
    Parent methods redifinition
     */

    public function allow_shipping_to_address( $_Address ) {

        if ( $this->getModuleSettingValue( 'CONF_COURIER_COUNTRY' ) > 0 ) {

            if ( $_Address['countryID'] != $this->getModuleSettingValue( 'CONF_COURIER_COUNTRY' ) ) {
                return false;
            }

            if ( $this->getModuleSettingValue( 'CONF_COURIER_ZONE' ) > 0 && $_Address['zoneID'] != $this->getModuleSettingValue( 'CONF_COURIER_ZONE' ) ) {
                return false;
            }

        }
        return true;
    }

    public function calculate_shipping_rate( $_Order, $_Address, $_ServID = 0 ) {

        $Rate = $this->getOrderpSumm( $_Order );
        if ( !$Rate ) {
            return 0;
        }

        $Rate = $this->getRate( $Rate );
        if ( !isset( $Rate['isPercent'] ) ) {
            return 0;
        }

        $Rate = $Rate['isPercent'] ? ( $_Order['order_amount'] * ( $Rate['rate'] / 100 ) ) : $Rate['rate'];
        return $Rate;
    }

    public function uninstall( $_ModuleConfigID = 0 ) {

        ShippingRateCalculator::uninstall( $_ModuleConfigID );

        if ( !count( modGetModuleConfigs( get_class( $this ) ) ) ) {

            //drop shipping rates table
            $sql = '
                                DROP TABLE IF EXISTS ' . CourierShippingModule::getDBName() . '
                        ';
        } else {

            $sql = '
                                DELETE FROM ' . CourierShippingModule::getDBName() . ' WHERE module_id="' . $_ModuleConfigID . '"
                        ';
        }
        db_query( $sql );
    }
    /*
    Abstract methods redifinition
     */
    public function InitVars() {

        $this->TemplatesDir = getcwd() . '/core/modules/shipping/templates/'; // getcwd(): string|false  Возвращает имя текущего рабочего каталога.
        $this->DB_TABLE     = $this->getDBName();
        $this->title        = COURIER_TTL;
        $this->description  = COURIER_DSCR;
        $this->Settings     = array(
            'CONF_COURIER_COUNTRY',
            'CONF_COURIER_ZONE',
            'CONF_COURIER_RATES',
        );
    }

    public function initSettingFields() {

        $this->SettingsFields['CONF_COURIER_COUNTRY'] = array(
            'settings_value'         => CONF_COUNTRY,
            'settings_title'         => COURIER_CFG_COUNTRY_TTL,
            'settings_description'   => COURIER_CFG_COUNTRY_DSCR,
            'settings_html_function' => 'CourierShippingModule::setting_COUNTRY_SELECT(true,',
            'sort_order'             => 20,
        );
        $this->SettingsFields['CONF_COURIER_ZONE'] = array(
            'settings_value'         => '',
            'settings_title'         => COURIER_CFG_ZONE_TTL,
            'settings_description'   => COURIER_CFG_ZONE_DSCR,
            'settings_html_function' => 'CourierShippingModule::setting_ZONE_SELECT(' . $this->getModuleSettingRealName( 'CONF_COURIER_COUNTRY' ) . ',',
            'sort_order'             => 30,
        );
        $this->SettingsFields['CONF_COURIER_RATES'] = array(
            'settings_value'         => '',
            'settings_title'         => COURIER_CFG_RATES_TTL,
            'settings_description'   => COURIER_CFG_RATES_DSCR,
            'settings_html_function' => 'CourierShippingModule::setting_RATES(' . $this->ModuleConfigID . ',',
            'sort_order'             => 40,
        );

        if ( !in_array( strtolower( $this->DB_TABLE ), db_get_all_tables() ) ) {

            $sql = '
                                CREATE TABLE ' . $this->DB_TABLE . '
                                (module_id INT UNSIGNED NOT NULL, orderAmount DOUBLE, rate FLOAT, isPercent BOOL, KEY (module_id))
                        ';
            db_query( $sql );
        }

    }

    /*
    current object methods
     */
    public function getDBName() {

        return 'SS__courier_rates';
        // return DB_PRFX . 'SS__courier_rates';
    }

    public function setting_RATES( $_ModuleConfigID ) {

        $smarty  = new Smarty();
        $Courier = new CourierShippingModule( $_ModuleConfigID );
        $Rates   = array();

        if ( isset( $_GET['delete_rate'] ) ) {

            $Courier->deleteRate( $_GET['delete_rate'] );
            Redirect( set_query( 'delete_rate=' ) );
        }

        if ( isset( $_POST['save'] ) ) {

            $_Rates   = array();
            $_Amounts = array();
            foreach ( $_POST['fORDER_AMOUNTS'] as $_Ind => $_Amount ) {

                if ( (float)$_Amount <= 0 || (float)$_POST['fRATES'][$_Ind] <= 0 || in_array( $_Amount, $_Amounts ) ) {
                    continue;
                }

                $_Rate         = array();
                $_Rate['rate'] = preg_replace( '/([0-9]+)\%/', '$1', $_POST['fRATES'][$_Ind] );

                if ( $_Rate['rate'] != $_POST['fRATES'][$_Ind] ) {

                    $_Rate['isPercent'] = 1;
                } else {

                    $_Rate['isPercent'] = 0;
                }
                $_Rate['orderAmount'] = $_Amount;
                $_Amounts[]           = $_Amount;
                $_Rates[]             = $_Rate;
            }
            $Courier->saveRates( $_Rates );
        }

        if ( !count( $Rates ) && $Courier ) {

            $Rates = $Courier->getRates( array(), array(), array() );
            // $Rates = $Courier->getRates();
        }

        $smarty->assign( 'Rates', $Rates );
        return $smarty->fetch( $Courier->TemplatesDir . 'courier.tpl.html' );
    }

    public function saveRates( $_Rates ) {

        $sql = '
                        DELETE FROM `' . $this->DB_TABLE . '`
                        WHERE module_id = ' . (int)$this->ModuleConfigID . '
                ';

        db_query( RN( $sql ) );

        foreach ( $_Rates as $_Rate ) {

            $sql = '
                                INSERT `' . $this->DB_TABLE . '`
                                (module_id, `' . implode( '`, `', xEscapeSQLstring( array_keys( $_Rate ) ) ) . '`)
                                VALUES(' . (int)$this->ModuleConfigID . ', "' . implode( '", "', xEscapeSQLstring( $_Rate ) ) . '")
                        ';
            db_query( RN( $sql ) );
        }
    }

    public function getRates( $_Services, $order, $address ) {
        // public function getRates() {

        $Rates = array();
        $sql   = '
                        SELECT orderAmount, rate, isPercent
                        FROM ' . $this->DB_TABLE . '
                        WHERE module_id=' . (int)$this->ModuleConfigID . '
                        ORDER BY orderAmount ASC
                ';

        $Result = db_query( RN( $sql ) );
        while ( $_Row = db_fetch_row( $Result ) ) {

            $Rates[] = $_Row;
        }
        return $Rates;
    }

    public function deleteRate( $_Amount ) {

        $sql = '
                        DELETE FROM ' . $this->DB_TABLE . '
                        WHERE  module_id=' . (int)$this->ModuleConfigID . ' AND orderAmount="' . xEscapeSQLstring( $_Amount ) . '"
                ';
        db_query( $sql );
    }

    public function getRate( $_Amount ) {

        $sql = '
                        SELECT  rate, isPercent FROM ' . $this->DB_TABLE . '
                        WHERE  module_id=' . (int)$this->ModuleConfigID . '
                        AND orderAmount>' . floor( $_Amount ) . '
                        ORDER BY orderAmount ASC
                        LIMIT 1
                ';
        return db_fetch_row( db_query( $sql ) );
    }

    public function setting_COUNTRY_SELECT( $_ShowButton, $_SettingID = null ) {

        if ( !isset( $_SettingID ) ) {
            $_SettingID  = $_ShowButton;
            $_ShowButton = false;
        }

        $Options = array(
            array( "title" => STR_ANY_COUNTRY, "value" => 0 ),
        );
        $CountriesNum = 0;
        $Countries    = cnGetCountries( array( 'raw data' => true ), $CountriesNum );
        foreach ( $Countries as $_Country ) {
            $Options[] = array( "title" => $_Country['country_name'], "value" => $_Country['countryID'] );
        }

        $html_select = setting_SELECT_BOX( $Options, $_SettingID );
        $html_button = "";
        if ( $_ShowButton ) {
            $html_button = "<button type=\"submit\" class=\"btn btn-secondary\" onclick=\"document.getElementById('save').name='save';document.getElementById('formmodule').submit(); return false\"><i class=\"bi bi-check-square\"></i></button>";
        }

        $html = "";
        $html .="
                    <div class=\"input-group\">
                    {$html_select}
                    {$html_button}
                    </div>";

        return $html;
    }

    public function setting_ZONE_SELECT( $_CountryID, $_SettingID ) {

        $Zones = znGetZones( $_CountryID );

        $Options = array(
            array( "title" => STR_ANY_ZONE, "value" => 0 ),
        );

        if ( !count( $Zones ) && $_CountryID ) {
            setting_SELECT_BOX( $Options, $_SettingID );
            return STR_ZONES_NOTDEFINED . '<input type="hidden" name="setting_' . settingGetConstNameByID( $_SettingID ) . '" value="0" />';
        }

        foreach ( $Zones as $_Zone ) {
            $Options[] = array(
                "title" => $_Zone['zone_name'],
                "value" => $_Zone['zoneID'],
            );
        }
        return setting_SELECT_BOX( $Options, $_SettingID );
    }

}
?>