<?php

/**
 * Implements hook_views_data_alter().
 */
function restme_foods_views_data_alter(array &$data) {
  $data['node']['discount_sale_offers'] = [
    'title' => t('Discount sale offers'),
    'filter' => [
      'title' => t('Discount sale offers'),
      'help' => 'Filters sale offers with discount',
      'id' => 'discount_sale_offers',
    ],
  ];
}

