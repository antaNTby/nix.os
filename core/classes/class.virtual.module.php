<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

define( 'SHIPPING_RATE_MODULE', 1 );
define( 'PAYMENT_MODULE', 2 );
define( 'SMSMAIL_MODULE', 3 );

define( 'MODULE_DEBUG', 0 ); //ant64
define( 'MODULE_LOG_CURL', 1 );
define( 'MODULE_LOG_FEDEX', 2 );

@ini_set( 'max_execution_time', 0 );

class virtualModule {

    public $id;
    public $title;
    public $description;
    public $myClassName;
    public $sort_order;
    public $ModuleType;
    public $ModuleConfigID;
    public $MethodsTable;
    public $DebugMode;
    public $ModuleVersion = 1;

    public $Settings       = array();
    public $SettingsFields = array();
    public $LanguageDir;
    public $TemplatesDir;
    public $SingleInstall = false;
    public $LogFile       = 'core/temp/general_msg.log';

    /**
     * Constructor for modules
     *
     * @param integer $_ModuleConfigID - if more then zero work with given config id
     * @return virtualModule
     */
    public function __construct( $_ModuleConfigID = 0 ) {

        $this->initDebugMode();

        $this->connectLanguageFile();

        $this->initVars();

        $this->myClassName = get_class( $this );

        $this->ModuleConfigID = $_ModuleConfigID;

        if ( $_ModuleConfigID ) {

            $this->title .= ' (' . $_ModuleConfigID . ')';
            $_TC = count( $this->Settings ) - 1;
            for ( ; $_TC >= 0; $_TC-- ) {

                $this->Settings[$_TC] .= '_' . $_ModuleConfigID;
            }
        }
    }

    public function getModuleConfigID() {

        return $this->ModuleConfigID;
    }

    /**
     * Returns module settings list
     *
     * @return array
     */
    public function settings_list(): array{

        return $this->Settings;
    }

    /**
     * Return module id
     *
     * @return integer
     */
    public function get_id() {
        $res = 0;

        if ( $this->ModuleConfigID ) {
            $res = $this->ModuleConfigID;
            return (int)$res;
        }

        $sql = "SELECT module_id FROM " . MODULES_TABLE .
        " WHERE module_name='" . $this->title . "' ";
        $q   = db_query( $sql ); //DEBUD
        $row = db_fetch_row( $q );

        $res = (int)$row["module_id"];
        return $res;
    }

    /**
     * returns TRUE if module is installed (if number of settings in the database equals number of settings from settings_list()), and FALSE if not
     *
     * @return bool
     */
    public function is_installed(): bool{

        $constants = "'" . implode( "', '", $this->settings_list() ) . "'";
        $q         = db_query( "select COUNT(*) FROM " . SETTINGS_TABLE . "
                        WHERE settings_constant_name IN (" . $constants . ")" );
        list( $cnt ) = db_fetch_row( $q );

        return ( $cnt != 0 );
    }

    /**
     * Uninstall module
     *
     */
    public function uninstall( $_ConfigID = 0 ) {

        $_ConfigID = (int)$_ConfigID ? (int)$_ConfigID : $this->ModuleConfigID;

        $constants = "'" . implode(  ( $_ConfigID ? '_' . $_ConfigID : '' ) . "', '", $this->settings_list() ) . ( $_ConfigID ? '_' . $_ConfigID : '' ) . "'";

        if ( $this->MethodsTable ) {

            $sql = '
                                UPDATE ' . $this->MethodsTable . '
                                SET module_id=NULL WHERE module_id=' . $_ConfigID . '
                        ';
            db_query( $sql );
        }

        $sql = '
                        DELETE FROM ' . SETTINGS_TABLE . '
                        WHERE settings_constant_name IN (' . $constants . ')
                ';
        db_query( $sql );

        $sql = '
                        DELETE FROM ' . MODULES_TABLE . '
                        WHERE module_id=' . $_ConfigID . '
                ';
        db_query( $sql );
    }

    /**
     * Install module
     * Should be redefined
     * In redefinition before call to parent method should be init SettingsFields
     *
     */
    public function install() {

        db_query( "INSERT INTO " . MODULES_TABLE .
            " ( module_name, ModuleClassName ) " .
            " VALUES( '" . $this->title . "', '" . get_class( $this ) . "' ) " );

        $NewModuleConfigID = db_insert_id();

        $this->ModuleConfigID = $NewModuleConfigID;

        $sql = "
                        UPDATE " . MODULES_TABLE . "
                        SET module_name='" . $this->title . ( $this->SingleInstall ? '' : ' (' . $NewModuleConfigID . ")" ) . "'
                        WHERE module_id=" . $NewModuleConfigID . "
                ";
        db_query( $sql );

        $this->initSettingFields();

        $this->SettingsFields = xEscapeSQLstring( $this->SettingsFields );

        foreach ( $this->Settings as $_SettingName ) {

            $sql = "
                                INSERT INTO " . SETTINGS_TABLE . "
                                (
                                        settings_groupID, settings_constant_name,
                                        settings_value,
                                        settings_title,
                                        settings_description,
                                        settings_html_function,
                                        sort_order
                                )
                                VALUES (
                                        " . settingGetFreeGroupId() . ", '" . $_SettingName . ( $this->SingleInstall ? '' : '_' . $NewModuleConfigID ) . "',
                                        '" . ( isset( $this->SettingsFields[$_SettingName]['settings_value'] ) ? $this->SettingsFields[$_SettingName]['settings_value'] : '' ) . "',
                                        '" . ( isset( $this->SettingsFields[$_SettingName]['settings_title'] ) ? $this->SettingsFields[$_SettingName]['settings_title'] : '' ) . "',
                                        '" . ( isset( $this->SettingsFields[$_SettingName]['settings_description'] ) ? $this->SettingsFields[$_SettingName]['settings_description'] : '' ) . "',
                                        '" . ( isset( $this->SettingsFields[$_SettingName]['settings_html_function'] ) ? $this->SettingsFields[$_SettingName]['settings_html_function'] : '' ) . "',
                                        '" . ( isset( $this->SettingsFields[$_SettingName]['sort_order'] ) ? $this->SettingsFields[$_SettingName]['sort_order'] : '' ) . "'
                                )";
            db_query( $sql );
        }
    }

    /**
     * Return value for setting constant
     *
     * @param string $_SettingName - setting constant name
     * @return unknown
     */
    public function getModuleSettingValue( $_SettingName ) {

        return constant( $_SettingName . (  ( $this->ModuleConfigID && !$this->SingleInstall ) ? '_' . $this->ModuleConfigID : '' ) );
    }

    public function getModuleSettingRealName( $_SettingName ) {

        return $_SettingName . (  ( $this->ModuleConfigID && !$this->SingleInstall ) ? '_' . $this->ModuleConfigID : '' );
    }

    /**
     * Check defined constant
     *
     */
    public function definedModuleSetting( $_SettingName ) {

        return defined( $_SettingName . (  ( $this->ModuleConfigID && !$this->SingleInstall ) ? '_' . $this->ModuleConfigID : '' ) );
    }
    /**
     * Return module type. Such as PAYMENT_MODULE,SHIPPING_RATE_MODULE
     *
     * @return integer
     */
    public function getModuleType() {

        return $this->ModuleType;
    }

    /**
     * Connect language file for module
     *
     */
    public function connectLanguageFile() {

        global $lang_list;
        $LanguageFile = $this->LanguageDir . $lang_list[$_SESSION["current_language"]]->iso2 . '.' . strtolower( get_class( $this ) ) . '.php';

        if ( file_exists( $LanguageFile ) ) {
            require_once $LanguageFile;
        }

    }

    /**
     * Convert from one currency type to another type
     * @param float $_Value - currency value
     * @param mixed $_FromType - could be currency ID or currency ISO3
     * @param mixed $_ToType
     */
    public function convertCurrency(
        $_Value,
        $_FromType,
        $_ToType
    ) {

        if ( !intval( $_FromType ) ) {

            if ( strlen( $_FromType ) == 3 ) {

                $FromCurrency = currGetCurrencyByISO3( $_FromType );
            } else {

                $FromCurrency = array( 'currency_value' => 1 );
            }
        } else {

            $FromCurrency = currGetCurrencyByID( $_FromType );
        }

        if ( !intval( $_ToType ) ) {

            if ( strlen( $_ToType ) == 3 ) {

                $ToCurrency = currGetCurrencyByISO3( $_ToType );
            } else {

                $ToCurrency = array( 'currency_value' => 1 );
            }
        } else {

            $ToCurrency = currGetCurrencyByID( $_ToType );
        }

        return ( $_Value / $FromCurrency['currency_value'] * $ToCurrency['currency_value'] );
    }

    /**
     * For redifinition in child classes. Called in constructor
     *
     */
    public function initVars() {

        ;
    }

    /**
     * For redifinition in child classes. Called in function 'install'
     *
     */
    public function initSettingFields() {
        ;
    }

    public function getTitle() {

        return $this->title;
    }

    public function getMyClass() {

        return $this->myClassName;
    }

    public function writeLogMessage(
        $_LogType,
        $_Message
    ) {

        switch ( $_LogType ) {
            case MODULE_LOG_CURL:
                $this->LogFile = 'core/temp/curl_msg.log';
                break;
            case MODULE_LOG_FEDEX:
                $this->LogFile = 'core/temp/fedex_msg.log';
                break;
            case MODULE_DEBUG:
                $this->LogFile = 'core/temp/module_debug.log';
                break;
        }
        if ( $this->LogFile ) {

            $fp = fopen( $this->LogFile, 'a' );
            fwrite( $fp, "\r\n" . date( "Y-m-d H:i:s " ) . "\r\n" . $_Message . "\r\n" );
            fclose( $fp );
        }
    }

    public function initDebugMode() {

        global $DebugMode;
        $this->DebugMode = $DebugMode;
    }

    public function debugMessage(
        $_Title,
        $_Msg
    ) {

        if ( $this->DebugMode ) {

            print '<br /><b>' . $_Title . '</b><br />' . $_Msg;
        }
    }
}
?>