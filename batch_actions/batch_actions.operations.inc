<?php
use Drupal\node\Entity\Node;

/**
 * Action 1
 * @inheritdoc
 */
function batch_actions_action_1($nid, &$context){
  $node = Node::load($nid);
  $node->save();

  $context['results'][] = $node->id();
  $context['message'] = 'Processed node: ' . $node->getTitle();
}

/**
 * Action 2
 * @inheritdoc
 */
function batch_actions_action_2($ids, &$context){
  $collection_storage = \Drupal::entityTypeManager()->getStorage('field_collection_item');
  $collections = $collection_storage->loadMultiple($ids);
  $collection_storage->delete($collections);
  $collection_storage->resetCache();

  foreach ($ids as $id){
    $context['results'][] = $id;
  }
  $context['message'] = 'Processed delete';
}

/**
 * Action 3
 * @inheritdoc
 */
function batch_actions_action_3($fid, $file_path, &$context){
  if(!file_exists($file_path)) {
    drupal_set_message($fid . ';' . $file_path);
    $context['results'][] = $fid;
  }
  $context['message'] = 'Processed delete';
}

/**
 * Action 4
 * @inheritdoc
 */
function batch_actions_action_4($nid, &$context){
  $offer = Node::load($nid);
  $offer_manager = \Drupal::service('restme.offer_manager');
  $result = $offer_manager->checkFoodsImage($offer);

  if(!$result) {
    $query = \Drupal::database()->update('node_field_data');
    $query->condition('nid', $nid);
    $query->fields([
      'status' => 0,
    ]);
    $query->execute();
    $context['results'][] = $offer->id();
  }
  $context['message'] = 'Processed node: ' . $offer->getTitle();
}

/**
 * Finished
 * @inheritdoc
 */
function batch_actions_finished($success, $results, $operations) {
  if ($success) {
    $message = 'Success '. count($results);
  }
  else {
    $message = t('Finished with an error.');
  }
  drupal_set_message($message);
}
