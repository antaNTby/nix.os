<?php
/*
sample shipping module
 */

/**
 * @connect_module_class_name CShippingModuleFixed
 *
 */
class CShippingModuleFixed extends ShippingRateCalculator {

    public function initVars() {

        $this->title       = CSHIPPINGMODULEFIXED_TITLE;
        $this->description = CSHIPPINGMODULEFIXED_DESCRIPTION;
        $this->sort_order  = 0;

        $this->Settings[] = 'CONF_SHIPPING_MODULE_FIXEDRATE_SHIPPINGRATE';
    }

    public function calculate_shipping_rate( $order, $address, $_shServiceID=0 ) {

        if ( !count( $this->getShippingProducts( $order ) ) ) {
            return 0;
        }

        $res=$this->getModuleSettingValue( 'CONF_SHIPPING_MODULE_FIXEDRATE_SHIPPINGRATE' );
        return  $res;
    }

    public function install() {

        $this->SettingsFields['CONF_SHIPPING_MODULE_FIXEDRATE_SHIPPINGRATE'] = array(
            'settings_value'         => '10',
            'settings_title'         => CSHIPPINGMODULEFIXED_CONF_SHIPPINGRATE_TITLE,
            'settings_description'   => CSHIPPINGMODULEFIXED_CONF_SHIPPINGRATE_DESCR,
            'settings_html_function' => 'setting_TEXT_BOX(1,',
            'sort_order'             => 2,
        );

        ShippingRateCalculator::install();
    }
}

?>
