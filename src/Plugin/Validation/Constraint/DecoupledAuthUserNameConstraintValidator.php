<?php

/**
 * @file
 * Contains \Drupal\user\Plugin\Validation\Constraint\UserNameConstraintValidator.
 */

namespace Drupal\decoupled_auth\Plugin\Validation\Constraint;

use Drupal\user\Plugin\Validation\Constraint\UserNameConstraintValidator;
use Drupal\Core\Field\FieldItemListInterface;
use Symfony\Component\Validator\Constraint;

/**
 * Validates the DecoupledAuthUserName constraint.
 */
class DecoupledAuthUserNameConstraintValidator extends UserNameConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    $is_decoupled = $items instanceof FieldItemListInterface && $items->getEntity()->isDecoupled();
    if ($is_decoupled && (!isset($items) || !$items->value)) {
      return;
    }
    parent::validate($items, $constraint);
  }
}
