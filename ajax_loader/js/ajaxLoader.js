(function ($, Drupal, settings) {

  "use strict";
    
  Drupal.behaviors.ajaxLoader = {
    attach: function (context) {
    
      $(context).find(".ajaxView").once('ajaxLoader').click(function(){
        $('#ajaxModalBody').html('<div id="loader" class="lds-dual-ring"></div>');
        $('#ajaxModal').modal('show');
        let elemId = $(this).data("id");
        var ajax = new Drupal.Ajax(false, false, {
            url: '/project_services/ajax_loader/'+elemId,
        });
        ajax.execute().done(function () {
            
        });
      });

      $(context).find(".ajaxEdit").once('ajaxLoader').click(function(){
        $('#ajaxModalBody').html('<div id="loader" class="lds-dual-ring"></div>');
        $('#ajaxModal').modal('show');
        let elemId = $(this).data("id");
        var ajax = new Drupal.Ajax(false, false, {
            url: '/project_services/ajax_loader/edit/'+elemId,
        });
        ajax.execute().done(function () {
            
        });
      });

      $(context).find(".ajaxDelete").once('ajaxLoader').click(function(){
        $('#ajaxModalBody').html('<div id="loader" class="lds-dual-ring"></div>');
        $('#ajaxModal').modal('show');
        let elemId = $(this).data("id");
        var ajax = new Drupal.Ajax(false, false, {
            url: '/project_services/ajax_loader/delete/'+elemId,
        });
        ajax.execute().done(function () {
            
        });
      });

      $(context).find(".ajaxProjectEdit").once('ajaxLoader').click(function(){
        let label = $(this).data("label");
        $('#ajaxModalLabel').html(label);
        $('#ajaxModalBody').html('<div id="loader" class="lds-dual-ring"></div>');
        $('#ajaxModal').modal('show');
        let elemId = $(this).data("id");
        var ajax = new Drupal.Ajax(false, false, {
            url: '/project_services/ajax_loader/project/edit/'+elemId,
        });
        ajax.execute().done(function () {
            
        });
      });

      $(context).find(".ajaxProjectDelete").once('ajaxLoader').click(function(){
        let label = $(this).data("label");
        $('#ajaxModalLabel').html(label);
        $('#ajaxModalBody').html('<div id="loader" class="lds-dual-ring"></div>');
        $('#ajaxModal').modal('show');
        let elemId = $(this).data("id");
        var ajax = new Drupal.Ajax(false, false, {
            url: '/project_services/ajax_loader/project/delete/'+elemId,
        });
        ajax.execute().done(function () {
            
        });
      });

      $(context).find('[data-target="addStageForm"]').once('ajaxLoader').click(function(){
        $('#ajaxModalLabel').html('Добавить этап');
        $('#ajaxModalBody').html('<div id="loader" class="lds-dual-ring"></div>');
        $('#ajaxModal').modal('show');
        let objectId = $(this).data("object-id");
        let projectId = $(this).data("project-id");
        var ajax = new Drupal.Ajax(false, false, {
            url: '/project_services/ajax_loader/stage/add/'+objectId+'/'+projectId,
        });
        ajax.execute().done(function () {
            
        });
      });

      $(context).find(".ajaxStageEdit").once('ajaxLoader').click(function(){
        let label = $(this).data("label");
        $('#ajaxModalLabel').html(label);
        $('#ajaxModalBody').html('<div id="loader" class="lds-dual-ring"></div>');
        $('#ajaxModal').modal('show');
        let elemId = $(this).data("id");
        var ajax = new Drupal.Ajax(false, false, {
            url: '/project_services/ajax_loader/stage/edit/'+elemId,
        });
        ajax.execute().done(function () {
            
        });
      });

      $(context).find(".ajaxStageDelete").once('ajaxLoader').click(function(){
        let label = $(this).data("label");
        $('#ajaxModalLabel').html(label);
        $('#ajaxModalBody').html('<div id="loader" class="lds-dual-ring"></div>');
        $('#ajaxModal').modal('show');
        let elemId = $(this).data("id");
        var ajax = new Drupal.Ajax(false, false, {
            url: '/project_services/ajax_loader/stage/delete/'+elemId,
        });
        ajax.execute().done(function () {
            
        });
      });

      $(context).find("#closeAjaxViewer").once('ajaxLoader').click(function(){
        $('#ajaxViewer').removeClass('ajaxViewerOpen');
        $('#ajaxViewerContent').html('<div id="loader" class="lds-dual-ring"></div>');
      });
      
    }
  }

})(jQuery, Drupal, drupalSettings);