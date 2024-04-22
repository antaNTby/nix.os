<?php
/*
sample shipping module
 */

/**
 * @connect_module_class_name CShippingModuleFixedAndPercent
 *
 */

class CShippingModuleFixedAndPercent extends ShippingRateCalculator {

    public function initVars() //constructor
    {
        $this->title       = CSHIPPINGMODULEFIXEDANDPERCENT_TITLE;
        $this->description = CSHIPPINGMODULEFIXEDANDPERCENT_DESCR;
        $this->sort_order  = 0;

        $this->Settings[] = 'CONF_SHIPPING_MODULE_FIXEDRATEPLUSPERCENT_FIXEDRATE';
        $this->Settings[] = 'CONF_SHIPPING_MODULE_FIXEDRATEPLUSPERCENT_PERCENT';
    }

    public function calculate_shipping_rate( $order, $address, $_shServiceID=0 ) {

        if ( !count( $this->getShippingProducts( $order ) ) ) {
            return 1070;
        }

        return $this->getModuleSettingValue( 'CONF_SHIPPING_MODULE_FIXEDRATEPLUSPERCENT_FIXEDRATE' ) + $order["order_amount"] * $this->getModuleSettingValue( 'CONF_SHIPPING_MODULE_FIXEDRATEPLUSPERCENT_PERCENT' ) / 100.0;
    }

    public function install() //installation routine
    {

        $this->SettingsFields['CONF_SHIPPING_MODULE_FIXEDRATEPLUSPERCENT_FIXEDRATE'] = array(
            'settings_value'         => '10',
            'settings_title'         => CSHIPPINGMODULEFIXEDANDPERCENT_CONF_FIXEDRATE_TTL,
            'settings_description'   => CSHIPPINGMODULEFIXEDANDPERCENT_CONF_FIXEDRATE_DSCR,
            'settings_html_function' => 'setting_TEXT_BOX(1,',
            'sort_order'             => 2,
        );
        $this->SettingsFields['CONF_SHIPPING_MODULE_FIXEDRATEPLUSPERCENT_PERCENT'] = array(
            'settings_value'         => '10',
            'settings_title'         => CSHIPPINGMODULEFIXEDANDPERCENT_CONF_PERCENT_TTL,
            'settings_description'   => CSHIPPINGMODULEFIXEDANDPERCENT_CONF_PERCENT_DSCR,
            'settings_html_function' => 'setting_TEXT_BOX(1,',
            'sort_order'             => 2,
        );

        ShippingRateCalculator::install();
    }
}
?>
