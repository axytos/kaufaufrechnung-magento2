/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Axytos_KaufAufRechnung/payment/form'
            },

            initObservable: function () {
                this._super();
                return this;
            },

            getCode: function () {
                return 'axytos_kauf_auf_rechnung';
            },

            getData: function () {
                return {
                    'method': this.item.method
                };
            },

            getCreditCheckInfoText: function () {
                return window.checkoutConfig.creditCheckInfo.axytos_kauf_auf_rechnung.infoText;
            }
        });
    }
);