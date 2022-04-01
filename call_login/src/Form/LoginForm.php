<?php

/**
 * @file
 * Contains \Drupal\call_login\Form\LoginForm.
 *
 *
 */

namespace Drupal\call_login\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\call_login\Controller\CallLoginController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\user\Entity\User;

/**
 * 
 */
class LoginForm extends FormBase
{

    protected $step = 1;
    protected $input = [
        'secret' => null,
        'status' => null,
        'val' => null,
        'text' => null,
        'try' => 1,
    ];
    protected $retry_code = false;
    protected $from = null;

    /**
     *
     * {@inheritdoc}.
     */
    public function getFormId()
    {
        return 'call_login_form';
    }

    /**
     *
     * {@inheritdoc}.
     */
    public function buildForm(array $form, FormStateInterface $form_state, $params = NULL)
    {

        $form['#prefix'] = '<div id="ajax_call_login_form">';
        $form['#suffix'] = '</div>';
        $form['#attached']['library'][] = 'call_login/call_login';

        if ($params && $params['from'] === 'from_order') {
            $this->from = $params['from'];
        }

        if ($this->step === 1) {

            $form['name'] = [
                '#type' => 'textfield',
                '#title' => 'Номер телефона',
                '#id' => 'phoneNumber',
                '#required' => true,
                '#attributes' => [
                    'placeholder' => '7(999) 999-99-99'
                ],
            ];
            $form['markup'] = [
                '#markup' => '<p class="login-desc">Вам будет совершен звонок со случайного номера, отвечать на звонок не нужно.<br /><strong>Введите последние 4 цифры номера.</strong></p>',
            ];
            if ($this->input['status'] === 'phone_format_error') {
                $form['markup_error'] = [
                    '#markup' => '<p class="login-error">Проверьте правильность ввода номера! Пример +7(999) 999-99-99</p>',
                ];
            }
            if ($this->input['status'] === 'sms_ru_error') {
                $form['markup_error'] = [
                    '#markup' => '<p class="login-error">Ошибка сервера авторизации! Пожалуйста позвоните нам по номеру +7 (391) 250-19-50<br />Текст ошибки - ' . $this->input['text'] . '</p>',
                ];
            }
        }

        if ($this->step === 2) {

            $phone_number = $this->input['val'];
            $decsription = 'Введите 4 последних цифры номера, который вам звонил! Это может занять несколько минут.';
            if ($this->input['status'] === 'phone_code_error') {
                $code_error = true;
            } else {
                $code_error = false;
            }

            if ($this->input['secret']) {
                $this->step = 'phone_submit';
                if ($code_error) {
                    $form['secret_number'] = [
                        '#type' => 'textfield',
                        '#id' => 'secretNumber',
                        '#title' => 'Код',
                        '#description' => $decsription,
                        '#attributes' => [
                            'placeholder' => '0 0 0 0',
                            'class' => ['error'],
                        ],
                    ];
                    $form['markup'] = [
                        '#markup' => '<p class="login-error">Неверный код! Проверьте правильность номера - ' . $phone_number . '</p>',
                    ];
                    $form['markup_link'] = [
                        '#markup' => '<p><a class="change-number" id="changeNumber" role="button">Ввести другой номер</a></p>',
                    ];
                } else {
                    $form['secret_number'] = [
                        '#type' => 'textfield',
                        '#id' => 'secretNumber',
                        '#title' => 'Код',
                        '#description' => $decsription,
                        '#attributes' => [
                            'placeholder' => '0 0 0 0',
                        ],
                    ];
                }
            }
        }

        if ($this->step === 'login_success') {
            $redirect_url = '/user';
            $response = new AjaxResponse();
            $response->addCommand(new RedirectCommand($redirect_url));
            return $response;
        }

        if ($this->step === 'login_success_for_order') {
            $redirect_url = '/cart-step-2';
            $response = new AjaxResponse();
            $response->addCommand(new RedirectCommand($redirect_url));
            return $response;
        }

        $form['captcha_desc'] = [
            '#markup' => '<p class="google-desc">This site is protected by reCAPTCHA and the Google
            <a href="https://policies.google.com/privacy">Privacy Policy</a> and 
            <a href="https://policies.google.com/terms">Terms of Service</a>
            apply.
        </p>'
        ];

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => 'Войти',
            '#id' => 'loginBtn',
            '#ajax' => [
                'wrapper' => 'ajax_call_login_form',
                'callback' => '::ajax_call_login_form_callback',
                'event' => 'click',
            ],
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        return parent::validateForm($form, $form_state);
    }

    /**
     *
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        if ($this->step === 1) {

            $name = $form_state->getValue('name');

            if ($name) {
                if (preg_match('/[\+]?([7|8])[-|\s]?\([-|\s]?(\d{3})[-|\s]?\)[-|\s]?(\d{3})[-|\s]?(\d{2})[-|\s]?(\d{2})/', $name)) {
                    // $json = CallLoginController::getAuth($name);
                    // if ($json && $json->status == 'ERROR') {
                    //     $this->input = [
                    //         'status' => 'sms_ru_error',
                    //         'text' => $json->status_text,
                    //         'val' => $name
                    //     ];
                    //     CallLoginController::sendMailLoginError($json->status_text);
                    // }
                    // if ($json && $json->status == 'OK') {
                    //     $this->input = [
                    //         'status' => 'ok',
                    //         'secret' => $json->code,
                    //         'val' => $name,
                    //     ];
                    //     if ($json->balance < 50) {
                    //         CallLoginController::sendMailSmallBalance($json->balance);
                    //     }
                    //     $this->step = 2;
                    // }
                    $this->input = [
                        'status' => 'ok',
                        'secret' => '0000',
                        'val' => $name,
                    ];
                    $this->step = 2;
                } else {
                    $this->input = [
                        'status' => 'phone_format_error',
                    ];
                }
                $form_state->setRebuild();
            } else {
                return;
            }
        }

        if ($this->step === 'phone_submit') {
            $input_number = $form_state->getValue('secret_number');
            $secret_number = $this->input['secret'];
            if ($input_number && $secret_number) {
                if ($input_number == $secret_number) {
                    $user = \Drupal::entityTypeManager()->getStorage('user')->loadByProperties(['field_user_phone' => $this->input['val']]);
                    $user = array_shift($user);
                    if ($user) {
                        user_login_finalize($user);
                        $this->step = 'login_success';
                        $form_state->setRebuild();
                    } else {
                        $user = User::create([
                            'name' => 'user-'.time(),
                            'pass' => '!@bubVYE88g*GY@G2',
                            'field_user_phone' => $this->input['val'],
                            'field_user_name' => 'Ваше имя',
                            'status' => 1
                        ]);
                        $user->save();
                        user_login_finalize($user);
                        if ($this->from === 'from_order') {
                            $this->step = 'login_success_for_order';
                        } else {
                            $this->step = 'login_success';
                        }
                        $form_state->setRebuild();
                    }
                } else {
                    $this->input['status'] = 'phone_code_error';
                    $this->step = 2;
                    $form_state->setRebuild();
                }
            }
        }
    }

    /**
     * Ajax Callback
     * {@inheritdoc}
     */
    public function ajax_call_login_form_callback(array &$form, FormStateInterface $form_state)
    {
        return $form;
    }
}
