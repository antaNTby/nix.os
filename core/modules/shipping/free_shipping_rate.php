<?php
/*
sample shipping module
 */

/**
 * @connect_module_class_name CShippingModuleFree
 *
 */
class CShippingModuleFree extends ShippingRateCalculator {

    public function initVars() {

        $this->title       = "Бесплатная доставка или самовывоз";
        $this->description = "Стоимость доставки равна нулю. Для самовывоза покупателем с пункта выдачи. Не зависит от географии, так как покупатель приезжает сам";
        $this->sort_order  = 0;

        $this->Settings[] = 'CONF_SHIPPING_MODULE_FREESHIPPING';
    }

    public function calculate_shipping_rate( $order, $address, $_shServiceID = 0 ) {
        $res = 0;
        return $res;
    }

    public function install() {

        $this->SettingsFields['CONF_SHIPPING_MODULE_FREESHIPPING'] = array(
            'settings_value'         => '0',
            'settings_title'         => CSHIPPINGMODULEFIXED_CONF_SHIPPINGRATE_TITLE,
            'settings_description'   => "Стоимость доставки равна НУЛЮ",
            'settings_html_function' => "",
            'sort_order'             => 0,
        );

        ShippingRateCalculator::install();
    }
}

?>
