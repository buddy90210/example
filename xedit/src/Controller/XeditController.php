<?php

namespace Drupal\xedit\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Controller\ControllerBase;

class XeditController extends ControllerBase
{

  public function editFieldInline($field, $id, $value, $selector, $entity_type)
  {
    if ($id && $field && isset($value)) {
      $node = \Drupal::entityTypeManager()->getStorage($entity_type)->load($id);
      $node->set($field, $value);
      $check = $node->save();

      if ($check) {
        $response = new AjaxResponse();
        $response->addCommand(new HtmlCommand('[data-xselector="' . $selector . '"]', $value, $settings = NULL));
        return $response;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  public function editFieldInlineEmpty($field, $id, $selector, $entity_type)
  {
    if ($id && $field) {
      $value = '';
      $node = \Drupal::entityTypeManager()->getStorage($entity_type)->load($id);
      $node->set($field, $value);
      $check = $node->save();

      if ($check) {
        $response = new AjaxResponse();
        $response->addCommand(new HtmlCommand('[data-xselector="' . $selector . '"]', $value, $settings = NULL));
        return $response;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }
}
