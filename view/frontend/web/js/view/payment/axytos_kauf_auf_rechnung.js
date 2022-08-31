/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Axytos_KaufAufRechnung/js/model/checkout-agreement-validator'
    ],
    function (
        Component,
        rendererList,
        additionalValidtaotrs,
        axytosCheckoutAgreementValidator
    ) {
        'use strict';

        rendererList.push(
            {
                type: 'axytos_kauf_auf_rechnung',
                component: 'Axytos_KaufAufRechnung/js/view/payment/method-renderer/axytos_kauf_auf_rechnung'
            }
        );

        additionalValidtaotrs.registerValidator(axytosCheckoutAgreementValidator);

        return Component.extend({});
    }
);
