<?php
 
/**
 * @file
 * Contains \Drupal\user_project_rules\Form\DeleteUserProjectPermission.
 */
 
namespace Drupal\user_project_rules\Form;
 
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\custom_ajax_commands\Ajax\CloseModalForm;
 
class DeleteUserProjectPermission extends FormBase
{
 
  protected $step = 1;
 
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames()
  {
  }
 
  /**
   * {@inheritdoc}
   */
  public function getFormID()
  {
    return 'delete_user_project_permission';
  }
 
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $params = null)
  {
 
    $form['#prefix'] = '<div id="ajax_form_user_delete_permission">';
    $form['#suffix'] = '</div>';
   if ($this->step === 1) {
    if ($params) {
      //static values
      $id = $form_state->set('id',$params['id']);
      
      //submit button
      $form['buttons']['success'] = array(
        '#type' => 'submit',
        '#value' => 'Удалить',
        '#ajax' => array(
          'wrapper' => 'ajax_form_user_delete_permission',
          'callback' => '::ajax_form_user_delete_permission_callback',
          'event' => 'click',
        ),
      );

    }
   }

   if ($this->step === 2) {
    $id = $form_state->get('id');
    //Ajax Respone
    $modalSelector = array(
        'hideSelector' => '#deletePermission',
    );
    $selector = '#trEmploye-'.$id;
    $response = new AjaxResponse();
    $response->addCommand(new RemoveCommand($selector));
    $response->addCommand(new CloseModalForm($modalSelector));
    return $response;
   }
   
    return $form;
    
  }
 
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    return parent::validateForm($form, $form_state);
  }
 
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    
    if ($this->step === 1) {
      $this->step = 2;
      $id = $form_state->get('id');
      //delete from DB
      $query = \Drupal::database()->delete('user_project_rules');
      $query->condition('id', $id);
      $query->execute();
    }
    
    $form_state->setRebuild();
    
  }
 
  public function ajax_form_user_delete_permission_callback(array &$form, FormStateInterface $form_state) {
    return $form;
  }
 
}