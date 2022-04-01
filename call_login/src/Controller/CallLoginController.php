<?php

namespace Drupal\call_login\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;

class CallLoginController extends ControllerBase
{
    public function getLoginForm($from)
    {
        $current_user_id = \Drupal::currentUser()->id();
        if ($current_user_id > 0) {
            $form = 'Вы уже авторизованны!';
        } else {
            $params = [
                'from' => $from
            ];
            $form = \Drupal::formBuilder()->getForm('Drupal\call_login\Form\LoginForm', $params);
        }

        $selector = '#mainModalBody';

        $response = new AjaxResponse();
        $response->addCommand(new HtmlCommand($selector, $form));
        return $response;
    }

    public static function getAuth($phone_number)
    {
        //init sms.ru service
        $smsru_key = 'KEY_HERE';

        $ch = curl_init("https://sms.ru/code/call");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            "phone" => $phone_number, // номер телефона пользователя
            "ip" => $_SERVER["REMOTE_ADDR"], // ip адрес пользователя
            "api_id" => $smsru_key
        )));
        $body = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($body);

        return $json;
    }

    public static function sendMailSmallBalance($balance)
    {
        $message = '
            <p>Низкий баланс в сервисе SMS.RU - ' . $balance . '</p>
            <p>Если его не пополнить вход на Ваш сайт ' . \Drupal::config('system.site')->get('name') . ' будет невозможен! <a href="https://sms.ru/?panel=login">Перейти на sms.ru</a></p>
        ';
        $mailManager = \Drupal::service('plugin.manager.mail');
        $module = 'call_login';
        $key = 'small_balance';
        $to = \Drupal::config('system.site')->get('mail');
        $params['message'] = $message;
        $langcode = \Drupal::currentUser()->getPreferredLangcode();
        $send = true;
        $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
        return $result;
    }

    public static function sendMailLoginError($error_message)
    {
        $message = '
            <p>Не удалось войти на сайт ' . \Drupal::config('system.site')->get('name') . '</p>
            <p>Текст ошибки - ' . $error_message . '</p>
            <p><a href="https://sms.ru/?panel=login">Перейти на sms.ru</a></p>
        ';
        $mailManager = \Drupal::service('plugin.manager.mail');
        $module = 'call_login';
        $key = 'login_error';
        $to = \Drupal::config('system.site')->get('mail');
        $params['message'] = $message;
        $langcode = \Drupal::currentUser()->getPreferredLangcode();
        $send = true;
        $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
        return $result;
    }
}
