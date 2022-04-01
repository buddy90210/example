<?php
/**
 * @file
 * Contains \Drupal\batch_actions\Form\ActionsForm.
 */

namespace Drupal\batch_actions\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\restme\RestMe;

class ActionsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];

    // Обновить общий выход блюд предложений (Action 1)
    $form['action_1'] = [
      '#type' => 'submit',
      '#value' => 'Обновить общий выход блюд предложений',
    ];

    // Удалить неиспользуемые сущности коллекций (Action 2)
    $form['action_2'] = [
      '#type' => 'submit',
      '#value' => 'Удалить неиспользуемые сущности коллекций',
    ];

    // Поиск несуществующих файлов (Action 3)
    $form['action_3'] = [
      '#type' => 'submit',
      '#value' => 'Поиск несуществующих файлов',
    ];

    // Снять с публикации предложения где нет фото блюд
    $form['action_4'] = [
      '#type' => 'submit',
      '#value' => 'Снять с публикации предложения где нет фото блюд',
    ];

    $form['action_5'] = [
      '#type' => 'submit',
      '#value' => 'Удалить все коллекции блюд и услуг',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $id = $form_state->getTriggeringElement()['#id'];

    // Action 1
    if($id == 'edit-action-1'){
      // Query
      $query = \Drupal::entityQuery('node');
      $query->condition('type', 'offer');
      $query->condition('field_offer_template', 1, '!=');
      $query->condition('field_offer_for_order', '', '!=');
      $query->notExists('field_total_output');
      $nodes = $query->execute();

      // Operations
      $operations = [];
      foreach ($nodes as $nid){
        $operations[] = ['batch_actions_action_1', [$nid]];
      }

      // Batch
      $batch = array(
        'title' => 'Обновить общий выход блюд предложений',
        'operations' => $operations,
        'finished' => 'batch_actions_finished',
        'file' => drupal_get_path('module', 'batch_actions') . '/batch_actions.operations.inc',
      );
      //batch_set($batch);
    }

    // Action 2
    if($id == 'edit-action-2'){
      // Query
      $fields = [
        'field_foods',
        'field_collection_services',
        'field_order_utm',
        'field_photo_gallery',
      ];
      $delete_ids = [];
      foreach ($fields as $field){
        $entity = 'node';
        if($field == 'field_photo_gallery') $entity = 'user';
        $query = \Drupal::database()->select('field_collection_item', 'c');
        $query->leftJoin($entity . '__' . $field, 'f', 'c.item_id = f.' . $field . '_target_id');
        $query->fields('c', ['item_id']);
        $query->condition('c.field_name', $field);
        $query->isNull('f.entity_id');
        $result = $query->execute()->fetchCol();
        if(!empty($result)) $delete_ids = array_merge($result, $delete_ids);
      }

      // Operations
      $operations = [];
      $ids = [];
      $limit = 20;
      $i = 0;
      $last_id = end($delete_ids);
      foreach ($delete_ids as $id){
        $ids[] = $id;
        $i++;
        if($i < $limit and $id != $last_id) continue;
        $operations[] = ['batch_actions_action_2', [$ids]];
        $ids = [];
        $i = 0;
      }

      // Batch
      $batch = array(
        'title' => 'Удалить неиспользуемые сущности коллекций',
        'operations' => $operations,
        'finished' => 'batch_actions_finished',
        'file' => drupal_get_path('module', 'batch_actions') . '/batch_actions.operations.inc',
      );
      //batch_set($batch);
    }

    // Action 3
    if($id == 'edit-action-3'){
      // Query
      $query = \Drupal::database()->select('file_managed', 'f');
      $query->fields('f', ['fid', 'uri']);
      $files = $query->execute()->fetchAllKeyed();

      // Operations
      $operations = [];
      $i = 0;
      $limit = 20;
      foreach ($files as $fid => $uri){
        //if($i > $limit) break;
        $file_path = str_replace('public://', DRUPAL_ROOT . '/sites/default/files/', $uri);
        $operations[] = ['batch_actions_action_3', [$fid, $file_path]];
        $i++;
      }

      // Batch
      $batch = array(
        'title' => 'Поиск несуществующих файлов',
        'operations' => $operations,
        'finished' => 'batch_actions_finished',
        'file' => drupal_get_path('module', 'batch_actions') . '/batch_actions.operations.inc',
      );
      //batch_set($batch);
    }

    // Action 4
    if($id == 'edit-action-4'){
      $exclude_offer_status = [
        RestMe::OFFER_REJECTED,
        RestMe::OFFER_CANCELED,
        RestMe::OFFER_DRAFT,
      ];

      // Query
      $query = \Drupal::entityQuery('node');
      $query->condition('type', 'offer');
      $query->condition('status', 1);
      $query->condition('field_offer_status', $exclude_offer_status, 'NOT IN');
      $nodes = $query->execute();

      // Operations
      $operations = [];
      foreach ($nodes as $nid){
        $operations[] = ['batch_actions_action_4', [$nid]];
      }

      // Batch
      $batch = array(
        'title' => 'Снять с публикации предложения где нет фото блюд',
        'operations' => $operations,
        'finished' => 'batch_actions_finished',
        'file' => drupal_get_path('module', 'batch_actions') . '/batch_actions.operations.inc',
      );
      batch_set($batch);
    }
    if($id == 'edit-action-5'){
      $q = \Drupal::entityTypeManager()->getStorage('field_collection_item')->getQuery();
      $q->condition('field_name', ['field_collection_services', 'field_foods'], 'IN');
      $delete_ids = $q->execute();

      // Operations
      $operations = [];
      $ids = [];
      $limit = 1000;
      $i = 0;
      foreach ($delete_ids as $id){
        $ids[] = $id;
        $i++;
        if($i < $limit) continue;
        $operations[] = ['batch_actions_action_2', [$ids]];
        $ids = [];
        $i = 0;
      }

      if (!empty($ids)) {
        $operations[] = ['batch_actions_action_2', [$ids]];
      }

      // Batch
      $batch = [
        'title' => 'Удалить все коллекции блюд и услуг',
        'operations' => $operations,
        'finished' => 'batch_actions_finished',
        'file' => drupal_get_path('module', 'batch_actions') . '/batch_actions.operations.inc',
      ];

      batch_set($batch);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'batch_actions_admin';
  }
}
