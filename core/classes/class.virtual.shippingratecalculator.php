<?php
require_once 'core/classes/class.virtual.module.php';

/**
 * Parent for all rate calculators modules
 *
 */
class ShippingRateCalculator extends virtualModule {

    public function __construct( $_ModuleConfigID = 0 ) {

        $this->LanguageDir  = 'core/modules/shipping/languages/';
        $this->ModuleType   = SHIPPING_RATE_MODULE;
        $this->MethodsTable = SHIPPING_METHODS_TABLE;
        virtualModule::__construct( $_ModuleConfigID );
    }

    public function getServiceType( $_ServiceID ) {

        $ShippingTypes = $this->getShippingTypes();
        foreach ( $ShippingTypes as $_Type => $_Services ) {
            if ( in_array( $_ServiceID, $_Services ) ) {
                return $_Type;
            }
        }

        return '';
    }

    public function convertDecLBStoPoundsOunces( $_Dec ) {

        return array(
            'lbs' => floor( $_Dec ),
            'oz'  => ceil( 16 * ( $_Dec - floor( $_Dec ) ) ),
        );
    }

    /**
     * Return list of rates for services
     *
     * @param array $_Services
     * @param array $order
     * @param array $address
     */
    public function getRates( $_Services,  $order,  $address ) {

        $Query        = $this->prepareQuery( $_Services, $order,  $address );
        $Answer       = $this->sendQuery( $Query );
        $parsedAnswer = $this->parseAnswer( $Answer );
        $newServices  = array();

        $_TC = count( $_Services );

        for ( $_ind = 0; $_ind < $_TC; $_ind++ ) {

            $_Service = &$_Services[$_ind];
            if ( isset( $parsedAnswer[$_Service['id']] ) ) {
                foreach ( $parsedAnswer[$_Service['id']] as $_indV => $_Variant ) {

                    $newServices[] = array(
                        'id'   => sprintf( "%02d%02d", $_Service['id'], $_indV ),
                        'name' => $_Variant['name'],
                        'rate' => $_Variant['rate'],
                    );
                }
            }

        }
        $_Services = $newServices;
    }

    /**
     * Return information by available shipping services
     * The same for all shipping modules
     *
     * @param array $order
     * @param array $address
     * @param integer $_shServiceID
     * @return array 'name'=>'<Service name>', 'id'=><Service ID>, 'rate'=>'<Service Rate>'
     */
    public function calculate_shipping_rate(  $order,  $address, $_shServiceID = 0 ) {

        $_shServiceID = (int)$_shServiceID;
        if ( $_shServiceID > 99 ) {

            if ( strlen( $_shServiceID ) < 4 ) {
                $_shServiceID = sprintf( "%04d", $_shServiceID );
            }

            $_orinServiceID                      = $_shServiceID;
            list( $_shServiceID, $_serviceOffset ) = sscanf( $_shServiceID, "%02d%02d" );
        }
        $Rates = array();
        if ( $_shServiceID ) {

            $AvailableServices = $this->getShippingServices();
            $Rates[]           = array(
                'name' => ( isset( $AvailableServices[$_shServiceID]['name'] ) ? $AvailableServices[$_shServiceID]['name'] : '' ),
                'code' => ( isset( $AvailableServices[$_shServiceID]['code'] ) ? $AvailableServices[$_shServiceID]['code'] : '' ),
                'id'   => $_shServiceID,
                'rate' => 0,
            );
        } else {

            $AvailableServices = $this->getServicesByCountry(  $address['countryID'] );
            foreach ( $AvailableServices as $_Service ) {

                $_Service['rate'] = 0;
                $Rates[]          = $_Service;
            }
        }

        $this->getRates( $Rates,  $order,  $address );

        if ( isset( $_orinServiceID ) ) {

            if ( isset( $Rates[$_serviceOffset] ) ) {
                $Rates = array( $Rates[$_serviceOffset] );
            } else {
                $Rates = array( array(
                    'name' => '',
                    'id'   => 0,
                    'rate' => 0,
                ) );
            }
        }
        if ( is_array( $Rates ) && !count( $Rates ) ) {
            $Rates = array( array(
                'name' => '',
                'id'   => 0,
                'rate' => 0,
            ) );
        }
        return $Rates;
    }

    #заглушка
    public function allow_shipping_to_address($address) {

        return true;
    }

    /**
     * Convert from one Measurement to another Measurement
     *
     * @param unknown_type $_Units
     * @param unknown_type $_From
     * @param unknown_type $_To
     */
    public function convertMeasurement( $_Units, $_From, $_To ) {

        switch ( strtolower( $_From ) . '_' . strtolower( $_To ) ) {

            case 'lb_kg':
            case 'lbs_kgs':
            case 'lbs_kg':
            case 'lb_kgs':
                $_Units = $_Units / 2.2046;
                break;
            case 'kg_lb':
            case 'kg_lbs':
            case 'kgs_lb':
            case 'kgs_lbs':
                $_Units = $_Units * 2.2046;
                break;
            case 'g_lb':
            case 'g_lbs':
                $_Units = $_Units / 1000 * 2.2046;
                break;
            case 'lb_g':
            case 'lbs_g':
                $_Units = $_Units / 2.2046 * 1000;
                break;
            case 'g_kg':
            case 'g_kgs':
                $_Units = $_Units / 1000;
        }

        return $_Units;
    }

    public function getOrderWeight( &$Order ) {

        $TC               = count( $Order['orderContent']['cart_content'] );
        $OrderWeight      = 0;
        $ShippingProducts = 0;

        for ( $i = 0; $i < $TC; $i++ ) {

            $Product = GetProduct( $Order['orderContent']['cart_content'][$i]['productID'] );
            if ( $Product['free_shipping'] ) {
                continue;
            }

            $ShippingProducts++;
            if ( !isset( $Product['weight'] ) ) {
                continue;
            }

            if ( !$Product['weight'] ) {
                continue;
            }

            $OrderWeight += $Order['orderContent']['cart_content'][$i]['quantity'] * $Product['weight'];
        }
        if ( $OrderWeight <= 0 && $ShippingProducts ) {
            $OrderWeight = 0.1;
        }

        return $OrderWeight;
    }

    public function getOrderpSumm( &$Order ) {

        $TC               = count( $Order['orderContent']['cart_content'] );
        $OrderpSumm       = 0;
        $ShippingProducts = 0;

        for ( $i = 0; $i < $TC; $i++ ) {

            $Product = GetProduct( $Order['orderContent']['cart_content'][$i]['productID'] );
            if ( $Product['free_shipping'] ) {
                continue;
            }

            $ShippingProducts++;
            $OrderpSumm += $Order['orderContent']['cart_content'][$i]['quantity'] * $Order['orderContent']['cart_content'][$i]['costUC'];
        }

        return $OrderpSumm;
    }

    public function getShippingProducts( $_Order ) {

        $Products = array();
        $_TC      = count( $_Order['orderContent']['cart_content'] ) - 1;
        for ( ; $_TC >= 0; $_TC-- ) {

            if ( $_Order['orderContent']['cart_content'][$_TC]['free_shipping'] ) {
                continue;
            }

            $Products[] = $_Order['orderContent']['cart_content'][$_TC];
        }
        return $Products;
    }

    /*
    abstract methods
     */

    /**
     * Return array of shipping types
     */
    public function getShippingTypes() {

        return array();
    }

    /**
     * Return services for country
     *
     * @param integer $_CountryID - country id
     */
    public function getServicesByCountry() {

        return $this->getShippingServices();
    }

    /**
     * Return list of shipping services
     *
     * @param string $_Type shipping type (Domestic, Inrenational)
     * @return array
     */
    public function getShippingServices() {return array();}

    public function prepareQuery( &$_Services,  $order,  $address ) {

        return $this->prepareXMLQuery( $_Services,  $order,  $address );
    }

    public function sendQuery( $_Query ) {

        return $this->sendXMLQuery( $_Query );
    }

    public function parseAnswer( $_Answer ) {

        return $this->parseXMLAnswer( $_Answer );
    }

    public function sendXMLQuery() {

    }

    public function prepareXMLQuery() {
    }

    public function parseXMLAnswer() {;}
}
?>