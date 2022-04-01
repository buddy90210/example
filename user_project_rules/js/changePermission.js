/**
 *
 *
 */

(function ($, Drupal, settings) {

  "use strict";
    
  Drupal.behaviors.changeUserProjectPermission = {
    attach: function (context) {
    
      $(context).find(".changePermission").once('changeUserProjectPermission').click(function(){
        $('body').after(Drupal.theme.ajaxProgressThrobber());
        let elemId = $(this).data("id");
        var ajax = new Drupal.Ajax(false, false, {
            url: '/project_services/change_user_project_permissions/'+elemId,
        });
        ajax.execute().done(function () {
            $("div").remove(".fullscreen-throbber");
            $('#changePermission').modal();
        });
      });

      $(context).find(".deletePermission").once('changeUserProjectPermission').click(function(){
        let elemId = $(this).data("id");
        var ajax = new Drupal.Ajax(false, false, {
            url: '/project_services/delete_user_project_permissions/'+elemId,
        });
        ajax.execute().done(function () {
            $('#deletePermission').modal();
        });
      });
      
    }
  }

})(jQuery, Drupal, drupalSettings);