<?php

/**
 * @file
 * Provide views data for decoupled_auth.module.
 */

/**
 * Implements hook_views_data_alter().
 */
function decoupled_auth_views_data_alter(&$data) {
  $data['users']['decoupled'] = array(
    'title' => t('Decoupled'),
    'help' => t('Whether this user is decoupled or not.'),
    'field' => array(
      'id' => 'user_decoupled',
    ),
    'filter' => array(
      'id' => 'user_decoupled',
    ),
  );
}
