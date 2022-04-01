<?php
 
/**
 * @file
 * Contains \Drupal\user_project_rules\Form\ChangeUserProjectPermission.
 */
 
namespace Drupal\user_project_rules\Form;
 
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\custom_ajax_commands\Ajax\CloseModalForm;
 
class ChangeUserProjectPermission extends FormBase
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
    return 'change_user_project_permission';
  }
 
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $params = null)
  {
 
    $form['#prefix'] = '<div id="ajax_form_user_change_permission">';
    $form['#suffix'] = '</div>';
   if ($this->step === 1) {
    if ($params) {
      //static values
      $id = $form_state->set('id',$params['id']);
      $uid = $form_state->set('uid',$params['uid']);
      $pid = $form_state->set('pid',$params['pid']);
      $pid_uid = $form_state->set('pid_uid',$params['pid_uid']);
      $email = $params['email'];
      //user object
      if ($params['user_obj']) {
        $avatar = $params['user_obj']['vars']['avatar'];
        $name = $params['user_obj']['vars']['name'];
        $email = $params['user_obj']['vars']['email'];
        $phone = $params['user_obj']['vars']['phone'];
        $form['user_avatar'] = array(
          '#markup' => $avatar,
        );
        $form['user_name'] = array(
          '#markup' => $name,
        );
        $form['user_email'] = array(
          '#markup' => $email,
        );
        $form['user_phone'] = array(
          '#markup' => $phone,
        );
      } else {
        $form['user_avatar'] = array(
          '#markup' => '/sites/default/files/default_images/no-avatar_0.png',
        );
        $form['user_name'] = array(
          '#markup' => 'Не зарегистрирован',
        );
        $form['user_email'] = array(
          '#markup' => $email,
        );
        $form['user_phone'] = array(
          '#markup' => 'Не указан',
        );
      }
      //user object

      //text fields
      $form['post'] = array(
        '#type' => 'textfield',
        '#title' => 'Должность',
        '#default_value' => $params['post'],
      );
      $form['comment'] = array(
        '#type' => 'textarea',
        '#title' => 'Комментарий',
        '#rows' => 3,
        '#default_value' => $params['comment'],
      );
      //select fields
      $form['job'] = array(
        '#title' => 'Управление работами',
        '#type' => 'select',
        '#options' => array(
          '0' => 'Запрещено все',
          '1' => 'Только просматривать',
          '2' => 'Только редактировать',
          '3' => 'Создавать и редактировать - только свои',
          '4' => 'Создавать, редактировать, удалять - только свои',
          '5' => 'Создавать и редактировать - все',
          '6' => 'Создавать, редактировать, удалять - все',
        ),
        '#required' => true,
        '#default_value' => $params['job'],
      );
      $form['mat'] = array(
        '#title' => 'Управление материалами',
        '#type' => 'select',
        '#options' => array(
          '0' => 'Запрещено все',
          '1' => 'Только просматривать',
          '2' => 'Только редактировать',
          '3' => 'Создавать и редактировать - только свои',
          '4' => 'Создавать, редактировать, удалять - только свои',
          '5' => 'Создавать и редактировать - все',
          '6' => 'Создавать, редактировать, удалять - все',
        ),
        '#required' => true,
        '#default_value' => $params['mat'],
      );
      $form['ppr'] = array(
        '#title' => 'Управление правилами проведения работ',
        '#type' => 'select',
        '#options' => array(
          '0' => 'Запрещено все',
          '1' => 'Только просматривать',
          '2' => 'Только редактировать',
          '3' => 'Создавать и редактировать - только свои',
          '4' => 'Создавать, редактировать, удалять - только свои',
          '5' => 'Создавать и редактировать - все',
          '6' => 'Создавать, редактировать, удалять - все',
        ),
        '#required' => true,
        '#default_value' => $params['ppr'],
      );
      $form['media'] = array(
        '#title' => 'Работа с изображениями',
        '#type' => 'select',
        '#options' => array(
          '0' => 'Запрещено все',
          '1' => 'Только просматривать',
          '2' => 'Только редактировать',
          '3' => 'Создавать и редактировать - только свои',
          '4' => 'Создавать, редактировать, удалять - только свои',
          '5' => 'Создавать и редактировать - все',
          '6' => 'Создавать, редактировать, удалять - все',
        ),
        '#required' => true,
        '#default_value' => $params['media'],
      );

      //submit button
      $form['action'] = array(
        '#type' => 'submit',
        '#value' => 'Сохранить',
        '#ajax' => array(
          'wrapper' => 'ajax_form_user_change_permission',
          'callback' => '::ajax_form_user_change_permission_callback',
          'event' => 'click',
        ),
      );

    }
   }

   if ($this->step === 2) {
    //Ajax Respone
    $modalSelector = array(
      'hideSelector' => '#changePermission',
    );
    $id = $form_state->get('id');
    $selector = '#userPost-'.$id;
    $message = $form_state->getValue('post');
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand($selector, $message, $settings = NULL));
    $response->addCommand(new CloseModalForm($modalSelector));
    return $response;
   }
   
    $form['#theme'] = 'change_user_project_permission';
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
      $post = $form_state->getValue('post');
      $comment = $form_state->getValue('comment');
      $job = $form_state->getValue('job');
      $mat = $form_state->getValue('mat');
      $ppr = $form_state->getValue('ppr');
      $media = $form_state->getValue('media');
      //update DB
      $query = \Drupal::database()->update('user_project_rules');
      $query->fields(array(
        'post' => $post,
        'comment' => $comment,
        'job' => $job,
        'mat' => $mat,
        'ppr' => $ppr,
        'media' => $media,
      ));
      $query->condition('id', $id);
      $query->execute();
    }
    
    $form_state->setRebuild();
    
  }
 
  public function ajax_form_user_change_permission_callback(array &$form, FormStateInterface $form_state) {
    return $form;
  }
 
}