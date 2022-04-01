(function ($, Drupal, drupalSettings) {

    "use strict";

    Drupal.behaviors.callLogin = {
        attach: function (context, settings) {
            $(context).find('#phoneNumber').once('callLogin').on('focus', function () {
                $(this).inputmask("+7(999) 999-99-99");
            });
            $(context).find('#secretNumber').once('callLogin').on('focus', function () {
                $(this).inputmask("9999");
            });
            $(context).find('#changeNumber').once('callLogin').click(function () {
                var ajax = new Drupal.Ajax(false, false, {
                    url: `/call_service/get_call_login_form/from_else`,
                });
                ajax.execute().done();
            });
        }
    }

})(jQuery, Drupal, drupalSettings);