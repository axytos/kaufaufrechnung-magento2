/*jshint browser:true jquery:true*/
/*global alert*/

define(
    [
        'jquery',
        'mage/url',
        'mage/utils/wrapper',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/payment/method-list'
    ],
    function ($, url, wrapper, globalMessageList, methodList) {
        'use strict';

        return function (targetModule) {

            targetModule.disablePaymentMethod = function (paymentMethod) {
                $('INPUT#' + paymentMethod).parents('.payment-method').find('.action.checkout').prop("disabled", true);
                $('INPUT#' + paymentMethod).parents('.payment-method').delay(5000).fadeOut(2000, function () {
                    $('INPUT#' + paymentMethod).parents('.payment-method').remove();
                });
            };

            targetModule.process = wrapper.wrap(targetModule.process, function (originalAction, response, messageContainer) {
                const origReturn = originalAction(response, messageContainer);
                const responseJSON = response.responseJSON;
                if (responseJSON && responseJSON.hasOwnProperty('errors')) {
                    const paymentMethodError = responseJSON.errors.find(error => error.parameters.hasOwnProperty('paymentMethod'));
                    if (paymentMethodError) {
                        const paymentMethod = paymentMethodError.parameters.paymentMethod;

                        $.each(methodList(), function ( key, value ) {
                            if (value.method == paymentMethod) {
                                targetModule.disablePaymentMethod(value.method);
                            }
                        });
                    }
                }

                return origReturn;
            });
            return targetModule;
        };
    }
);
