<?php
namespace Drupal\user_project_rules\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\user_info\Controller\UserInfoLoader;

class ChangeUserPermissionController extends ControllerBase{

  public function changeUserPermission($id) {

      if ($id) {
        $current_user_id = \Drupal::currentUser()->id();
        //query
        $query = \Drupal::database()->select('user_project_rules', 'upr');
        $query->fields('upr', array('id', 'uid', 'pid', 'pid_uid', 'job', 'mat', 'ppr', 'media', 'name', 'post', 'email', 'comment', 'timestamp'));
        $query->condition('upr.id', $id);
        $query->range(0,1);
        $result = $query->execute()->fetchAll();
        $query_user = $result[0];

        if ($query_user && $current_user_id === $query_user->pid_uid) {
            if ($query_user->uid > 0) {
              $user_obj = UserInfoLoader::UserInfoLoadShort($query_user->uid);
            } else {
              $user_obj = null;
            }
            $params = array(
                'id' => $query_user->id,
                'uid' => $query_user->uid,
                'pid' => $query_user->pid,
                'pid_uid' => $query_user->pid_uid,
                'job' => $query_user->job,
                'mat' => $query_user->mat,
                'ppr' => $query_user->ppr,
                'media' => $query_user->media,
                'name' => $query_user->name,
                'post' => $query_user->post,
                'email' => $query_user->email,
                'comment' => $query_user->comment,
                'timestamp' => $query_user->timestamp,
                'user_obj' => $user_obj,
            );
            //form
            $form = \Drupal::formBuilder()->getForm('Drupal\user_project_rules\Form\ChangeUserProjectPermission', $params);
            //ajax response
            $response = new AjaxResponse();
            $response->addCommand(new HtmlCommand('#changePermissionForm', $form, $settings = NULL));

            return $response;
        } else {
          $message = 'Вы не можете управлять правами!';
          $response = new AjaxResponse();
          $response->addCommand(new HtmlCommand('#changePermissionForm', $message, $settings = NULL));
          return $response;
        }

      } else {
        return;
      }
  }

  public function deleteUserPermission($id) {
    if ($id) {
      $current_user_id = \Drupal::currentUser()->id();
      //query
      $query = \Drupal::database()->select('user_project_rules', 'upr');
      $query->fields('upr', array('id','pid_uid'));
      $query->condition('upr.id', $id);
      $query->range(0,1);
      $result = $query->execute()->fetchAll();
      $query_user = $result[0];

      if ($query_user && $current_user_id === $query_user->pid_uid) {
        $params = array(
          'id' => $id,
        );
        //form
        $form = \Drupal::formBuilder()->getForm('Drupal\user_project_rules\Form\DeleteUserProjectPermission', $params);
        //ajax response
        $response = new AjaxResponse();
        $response->addCommand(new HtmlCommand('#deletePermissionForm', $form, $settings = NULL));
        return $response;
      } else {
        $message = 'Вы не можете управлять правами!';
        $response = new AjaxResponse();
        $response->addCommand(new HtmlCommand('#deletePermissionForm', $message, $settings = NULL));
        return $response;
      }
    } else {
      return;
    }
  }

}