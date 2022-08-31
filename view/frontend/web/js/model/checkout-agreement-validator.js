define(
    [
        'jquery',
        'mage/translate',
        'Magento_Ui/js/model/messageList'
    ],
    function ($, $t, messageList) {
        'use strict';

        return {

            /**
             * Validate checkout agreements
             *
             * @returns {Boolean}
             */
            validate: function () {
                if ($('#axytos_kauf_auf_rechnung').is(':not(:checked)')) {
                    return true;
                }

                const isChecked = $('#axytos_kauf_auf_rechnung_agreement').is(':checked');

                if (!isChecked) {
                    const text = $.mage.__('This is a required field.');
                    $('#axytos_kauf_auf_rechnung_agreement_error').text(text).show();
                } else {
                    $('#axytos_kauf_auf_rechnung_agreement_error').hide();
                }

                return isChecked;
            }
        };
    }
);