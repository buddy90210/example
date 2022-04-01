<?php
 
/**
 * @file
 * Contains \Drupal\user_project_rules\Form\Multistep\AddUserforProject.
 */
 
namespace Drupal\user_project_rules\Form\Multistep;
 
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\custom_ajax_commands\Ajax\CloseModalForm;
use Drupal\user_info\Controller\UserInfoLoader;
 
class AddUserforProject extends FormBase
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
    return 'add_user_for_project';
  }
 
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $params = null)
  {
 
    $form['#prefix'] = '<div id="ajax_form_user_add_form">';
    $form['#suffix'] = '</div>';
    if ($params) {
      $pid = $params['pid'];
      $project_title = $params['project_title'];
      $project_url = $params['project_url'];
      $form_state->set('pid',$params['pid']);
      $form_state->set('project_title',$params['project_title']);
      $form_state->set('project_url',$params['project_url']);
      $form_state->set('pid_uid', $params['pid_uid']);

      if ($this->step === 1) {
        $form['email'] = array(
          '#type' => 'email',
          '#title' => 'E-mail',
          '#title_display' => 'invisible',
          '#required' => true,
          '#attributes' => array(
            'placeholder' => '@',
          ),
        );
        $form['check_send'] = array(
          '#type' => 'checkbox',
          '#title' => 'Отправить уведомление на e-mail',
        );
        $form['buttons']['forward'] = array(
          '#type' => 'submit',
          '#value' => 'Добавить',
          '#ajax' => array(
            'wrapper' => 'ajax_form_user_add_form',
            'callback' => '::ajax_form_multistep_form_ajax_callback',
            'event' => 'click',
          ),
        );
      }

      if ($this->step === 2) { // ajax response user is register
        
        $this->step = 1;

        $uid = $form_state->get('uid');
        $data_id = $form_state->get('data_id');
        $user = UserInfoLoader::UserInfoLoadOnlyVars($uid);
        //user table string for paste
        $html_for_paste = '<tr id="trEmploye-'.$data_id.'">
							<td><img src="'.$user['avatar'].'" width="50" height="50" loading="lazy" typeof="foaf:Image" class="table-avatar"></td>
							<td>'.$user['name'].'</td>
							<td id="userPost-'.$data_id.'"></td>
							<td>'.$user['phone'].'</td>
							<td>'.$user['email'].'</td>
							<td>
								<a role="button" class="changePermission btn btn-link" data-id="'.$data_id.'">
									<i class="fas fa-cog"></i>
								</a>
								<a role="button" class="deletePermission btn btn-link" data-id="'.$data_id.'">
									<i class="fas fa-trash-alt"></i>
								</a>
							</td>
						</tr>';
        //
        $selector = '#employes';
        $modalSelector = array(
          'hideSelector' => '#addEmployes',
          'cleanVal' => array(
            'input[name ="email"]',
          ),
        );
        $response = new AjaxResponse();
        $response->addCommand(new AppendCommand($selector, $html_for_paste, $settings = NULL));
        $response->addCommand(new CloseModalForm($modalSelector));
        return $response;

      }

      if ($this->step === 3) {// ajax response user is register and send mail

        $this->step = 1;
        $email = $form_state->getValue('email');
        $uid = $form_state->get('uid');
        $data_id = $form_state->get('data_id');
        $user = UserInfoLoader::UserInfoLoadOnlyVars($uid);
        //user table string for paste
        $html_for_paste = '<tr id="trEmploye-'.$data_id.'">
							<td><img src="'.$user['avatar'].'" width="50" height="50" loading="lazy" typeof="foaf:Image" class="table-avatar"></td>
							<td>'.$user['name'].'</td>
							<td id="userPost-'.$data_id.'"></td>
							<td>'.$user['phone'].'</td>
							<td>'.$user['email'].'</td>
							<td>
								<a role="button" class="changePermission btn btn-link" data-id="'.$data_id.'">
									<i class="fas fa-cog"></i>
								</a>
								<a role="button" class="deletePermission btn btn-link" data-id="'.$data_id.'">
									<i class="fas fa-trash-alt"></i>
								</a>
							</td>
						</tr>';
        // send mail section
        $mailManager = \Drupal::service('plugin.manager.mail');
        $module = 'mail_sender';
        $key = 'user_is_not_in_db_project';
        $to = $email;
        $params['message'] = '
          <p>Вас добавили в качестве сотрудника к проекту: '.$project_title.'</p>
          <p>Для регистрации в системе перейдите по ссылке - <a href="'.$project_url.'">Регистрация.</a></p>
        ';
        $langcode = \Drupal::currentUser()->getPreferredLangcode();
        $send = true;

        $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
        if ($result['result'] !== true) {
          $mail_status_obj = '
            <div class="alert-wrapper" data-drupal-messages="">
              <div aria-label="Сообщение об ошибке" class="alert alert-dismissible fade show col-12 alert-danger" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
                Письмо не отправлено, проверьте корекктность e-mail адреса.
              </div>
            </div>
          ';
        }
        else {
          $mail_status_obj = '
            <div class="alert-wrapper" data-drupal-messages="">
              <div aria-label="Статус" class="alert alert-dismissible fade show col-12 alert-success" role="status">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
                Ссылка на регистрацию в системе - успешно отправлена.
              </div>
            </div>
          ';
        }
        // end send mail section

        $mail_selector = 'div.highlighted > aside';
        $selector = '#employes';
        $modalSelector = array(
          'hideSelector' => '#addEmployes',
          'cleanVal' => array(
            'input[name ="email"]',
          ),
        );
        $response = new AjaxResponse();
        $response->addCommand(new AppendCommand($selector, $html_for_paste, $settings = NULL));
        $response->addCommand(new AppendCommand($mail_selector, $mail_status_obj, $settings = NULL));
        $response->addCommand(new CloseModalForm($modalSelector));
        return $response;
      }

      if ($this->step === 4) { // ajax response user is NOT register
        
        $this->step = 1;
        $email = $form_state->getValue('email');
        $data_id = $form_state->get('data_id');

        $html_for_paste = '<tr id="trEmploye-'.$data_id.'">
							<td><img src="/sites/default/files/default_images/no-avatar_0.png" width="50" height="50" loading="lazy" typeof="foaf:Image" class="table-avatar"></td>
							<td>Не зарегистрирован</td>
							<td id="userPost-'.$data_id.'"></td>
							<td>Не указан</td>
							<td>'.$email.'</td>
							<td>
								<a role="button" class="changePermission btn btn-link" data-id="'.$data_id.'">
									<i class="fas fa-cog"></i>
								</a>
								<a role="button" class="deletePermission btn btn-link" data-id="'.$data_id.'">
									<i class="fas fa-trash-alt"></i>
								</a>
							</td>
						</tr>';

        $selector = '#employes';
        $modalSelector = array(
          'hideSelector' => '#addEmployes',
          'cleanVal' => array(
            'input[name ="email"]',
          ),
        );
        $response = new AjaxResponse();
        $response->addCommand(new AppendCommand($selector, $html_for_paste, $settings = NULL));
        $response->addCommand(new CloseModalForm($modalSelector));
        return $response;

      }

      if ($this->step === 5) {// ajax response user is NOT register and send mail

        $this->step = 1;

        $email = $form_state->getValue('email');

        // send mail section
        $mailManager = \Drupal::service('plugin.manager.mail');
        $module = 'mail_sender';
        $key = 'user_is_not_in_db_project';
        $to = $email;
        $params['message'] = '
          <p>Вас добавили в качестве сотрудника к проекту: '.$project_title.'</p>
          <p>Для регистрации в системе перейдите по ссылке - <a href="'.$project_url.'">Регистрация.</a></p>
        ';
        $langcode = \Drupal::currentUser()->getPreferredLangcode();
        $send = true;

        $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
        if ($result['result'] !== true) {
          $mail_status_obj = '
            <div class="alert-wrapper" data-drupal-messages="">
              <div aria-label="Сообщение об ошибке" class="alert alert-dismissible fade show col-12 alert-danger" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
                Письмо не отправлено, проверьте корекктность e-mail адреса.
              </div>
            </div>
          ';
        }
        else {
          $mail_status_obj = '
            <div class="alert-wrapper" data-drupal-messages="">
              <div aria-label="Статус" class="alert alert-dismissible fade show col-12 alert-success" role="status">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
                Ссылка на регистрацию в системе - успешно отправлена.
              </div>
            </div>
          ';
        }
        // end send mail section

        $data_id = $form_state->get('data_id');

        $html_for_paste = '<tr id="trEmploye-'.$data_id.'">
							<td><img src="/sites/default/files/default_images/no-avatar_0.png" width="50" height="50" loading="lazy" typeof="foaf:Image" class="table-avatar"></td>
							<td>Не зарегистрирован</td>
							<td id="userPost-'.$data_id.'"></td>
							<td>Не указан</td>
							<td>'.$email.'</td>
							<td>
								<a role="button" class="changePermission btn btn-link" data-id="'.$data_id.'">
									<i class="fas fa-cog"></i>
								</a>
								<a role="button" class="deletePermission btn btn-link" data-id="'.$data_id.'">
									<i class="fas fa-trash-alt"></i>
								</a>
							</td>
						</tr>';

        $mail_selector = 'div.highlighted > aside';
        $selector = '#employees';
        $modalSelector = array(
          'hideSelector' => '#addEmployes',
          'cleanVal' => array(
            'input[name ="email"]',
          ),
        );
        $response = new AjaxResponse();
        $response->addCommand(new AppendCommand($selector, $html_for_paste, $settings = NULL));
        $response->addCommand(new AppendCommand($mail_selector, $mail_status_obj, $settings = NULL));
        $response->addCommand(new CloseModalForm($modalSelector));
        return $response;
      }

      if ($this->step === 6) { // user is alredy member of project

        $this->step = 1;

        $status_message = '
            <div class="alert-wrapper" data-drupal-messages="">
              <div aria-label="Статус" class="alert alert-dismissible fade show col-12 alert-success" role="status">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
                Пользователь с данным E-mail уже прикреплен к проекту!
              </div>
            </div>
        ';

        $status_selector = 'div.highlighted > aside';
        $modalSelector = array(
          'hideSelector' => '#addEmployes',
          'cleanVal' => array(
            'input[name ="email"]',
          ),
        );
        $response = new AjaxResponse();
        $response->addCommand(new AppendCommand($status_selector, $status_message, $settings = NULL));
        $response->addCommand(new CloseModalForm($modalSelector));
        return $response;

      }

      if ($this->step === 7) { // user is Author

        $this->step = 1;

        $status_message = '
            <div class="alert-wrapper" data-drupal-messages="">
              <div aria-label="Статус" class="alert alert-dismissible fade show col-12 alert-success" role="status">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
                Вы автор данного проекта и не можете прикрепить себя в качестве сотрудника!
              </div>
            </div>
        ';

        $status_selector = 'div.highlighted > aside';
        $modalSelector = array(
          'hideSelector' => '#addEmployes',
          'cleanVal' => array(
            'input[name ="email"]',
          ),
        );
        $response = new AjaxResponse();
        $response->addCommand(new AppendCommand($status_selector, $status_message, $settings = NULL));
        $response->addCommand(new CloseModalForm($modalSelector));
        return $response;

      }

    } else {
      $form['markup'] = array(
        '#markup' => 'Что то пошло не так! Попробуйте еще раз.',
      );
    }

    $form['#attached']['library'][] = 'custom_ajax_commands/closeModalForm';
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
      $email = $form_state->getValue('email');
      $pid = $form_state->get('pid');
      $pid_uid = $form_state->get('pid_uid');
      $check_send = $form_state->getValue('check_send');

      // query if user is already a member of project
      $query = \Drupal::database()->select('user_project_rules', 'upr');
      $query->addField('upr', 'email');
      $query->condition('upr.email', $email);
      $query->condition('upr.pid', $pid);
      $query->range(0, 1);
      $query_mail = $query->execute()->fetchField();
      if ($query_mail) {// User already is a member of this project
          $this->step = 6;
      } else {
          // query if user is register
          $query = \Drupal::entityQuery('user');
          $query->condition('mail', $email);
          $query->range(0, 1);
          $result = $query->execute();
  
          if ($result) {
              $uid = array_shift($result);
              if ($uid === $pid_uid) {
                $this->step = 7;
              } else {
                $form_state->set('uid',$uid);
                //insert user ID in DB
                $query = \Drupal::database()->insert('user_project_rules');
                $query->fields(array(
                  'uid',
                  'pid',
                  'pid_uid',
                  'email',
                  'timestamp',
                ));
                $query->values(array(
                  $uid,
                  $pid,
                  $pid_uid,
                  $email,
                  time(),
                ));
                $data_id = $query->execute();
                $form_state->set('data_id', $data_id);
              
                if ($check_send === 1) {
                  $this->step = 3;
                } else {
                  $this->step = 2;
                }
            }
              
          } else { //User is NOT registered in SITE
            //insert user mail in DB
            $query = \Drupal::database()->insert('user_project_rules');
            $query->fields(array(
              'pid',
              'pid_uid',
              'email',
              'timestamp',
            ));
            $query->values(array(
              $pid,
              $pid_uid,
              $email,
              time(),
            ));
            $data_id = $query->execute();
            $form_state->set('data_id', $data_id);

            if ($check_send === 1) {
              $this->step = 5;
            } else {
              $this->step = 4;
            }
          }
      }
  
    }
 
    $form_state->setRebuild();
  }
 
  public function ajax_form_multistep_form_ajax_callback(array &$form, FormStateInterface $form_state) {
    return $form;
  }
 
}