
(function ($, Drupal) {

  "use strict";

  Drupal.behaviors.fieldXedit = {
    attach: function (context) {


      $.fn.editable.defaults.mode = 'inline';
      $('[data-edit="xEdit"]').each(function () {

        let id = this.dataset.xid;
        let field = this.dataset.xfield;
        let selector = this.dataset.xselector;
        let type = this.dataset.xtype;
        let entity_type = this.dataset.xentity;

        $(this).editable({
          type: type,
          showbuttons: false,
          onblur: 'submit',
          success: function (response, newValue) {
            if (newValue) {
              var ajax = new Drupal.Ajax(false, false, {
                url: `/services/inline_field_edit/${field}/${id}/${newValue}/${selector}/${entity_type}`,
              });
              ajax.execute().done(function () {
                $('[data-xselector="' + selector + '"]').removeClass('editable-unsaved');
              });
            } else {
              var ajax = new Drupal.Ajax(false, false, {
                url: `/services/inline_field_edit_empty/${field}/${id}/${selector}/${entity_type}`,
              });
              ajax.execute().done(function () {
                $('[data-xselector="' + selector + '"]').removeClass('editable-unsaved');
                $('[data-xselector="' + selector + '"]').removeClass('editable-empty');
              });
            }
          }
        });
      });

    }
  }

})(jQuery, Drupal);