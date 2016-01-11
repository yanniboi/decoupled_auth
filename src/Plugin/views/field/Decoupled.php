<?php

/**
 * @file
 * Contains \Drupal\decoupled_auth\Plugin\views\field\Decoupled.
 */

namespace Drupal\decoupled_auth\Plugin\views\field;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to whether user is decoupled.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("user_decoupled")
 */
class Decoupled extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    $this->additional_fields['uid'] = 'uid';
  }

  public function query() {
    $this->ensureMyTable();
    $this->addAdditionalFields();
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $uid = $this->getValue($values, 'uid');
    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);

    return $user->isDecoupled() ? 'Decoupled' : 'Coupled';
  }

}
