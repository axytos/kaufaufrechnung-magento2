/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'axytos_kauf_auf_rechnung',
                component: 'Axytos_KaufAufRechnung/js/view/payment/method-renderer/axytos_kauf_auf_rechnung'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
