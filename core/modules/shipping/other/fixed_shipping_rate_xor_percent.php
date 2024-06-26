<?php

/**
 * @connect_module_class_name CShippingModuleFixedXorPercent
 *
 */

class CShippingModuleFixedXorPercent extends ShippingRateCalculator {

    public function initVars() //constructor
    {
        //$this->id = "shipping_fixed";
        $this->title       = CSHIPPINGMODULEFIXEDXORPERCENT_TTL;
        $this->description = CSHIPPINGMODULEFIXEDXORPERCENT_DSCR;
        $this->sort_order  = 0;

        $this->Settings[] = 'CONF_SHIPPING_MODULE_FIXEDRATEXORPERCENT_FIXEDRATE';
        $this->Settings[] = 'CONF_SHIPPING_MODULE_FIXEDRATEXORPERCENT_PERCENT';
    }

    public function calculate_shipping_rate( $order, $address, $_shServiceID=0 ) //core shipping rate calculation routine
                                                       //returns float value in case of correct calculation, and error string in case of error
    {
        if ( !count( $this->getShippingProducts( $order ) ) ) {
            return 0;
        }

        return
        max(
            $this->getModuleSettingValue( 'CONF_SHIPPING_MODULE_FIXEDRATEXORPERCENT_FIXEDRATE' ),
            $order["order_amount"] * $this->getModuleSettingValue( 'CONF_SHIPPING_MODULE_FIXEDRATEXORPERCENT_PERCENT' ) / 100.0
        );
    }

    public function install() //installation routine
    {

        $this->SettingsFields['CONF_SHIPPING_MODULE_FIXEDRATEXORPERCENT_FIXEDRATE'] = array(
            'settings_value'         => '0',
            'settings_title'         => CSHIPPINGMODULEFIXEDXORPERCENT_CONF_FIXEDRATE_TTL,
            'settings_description'   => CSHIPPINGMODULEFIXEDXORPERCENT_CONF_FIXEDRATE_DSCR,
            'settings_html_function' => 'setting_TEXT_BOX(1,',
            'sort_order'             => 2,
        );
        $this->SettingsFields['CONF_SHIPPING_MODULE_FIXEDRATEXORPERCENT_PERCENT'] = array(
            'settings_value'         => '0',
            'settings_title'         => CSHIPPINGMODULEFIXEDXORPERCENT_CONF_PERCENT_TTL,
            'settings_description'   => CSHIPPINGMODULEFIXEDXORPERCENT_CONF_PERCENT_DSCR,
            'settings_html_function' => 'setting_TEXT_BOX(1,',
            'sort_order'             => 2,
        );

        ShippingRateCalculator::install();
    }
}
?>
