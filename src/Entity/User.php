<?php

/**
 * @file
 * Contains \Drupal\decoupled_auth\Entity\User.
 */

namespace Drupal\decoupled_auth\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\Entity\User as CoreUser;

/**
 * Defines the decoupled user authentication user entity class.
 */
class User extends CoreUser {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Make name not required at a database level and swap the constraint.
    $constraints = $fields['name']->getConstraints();
    unset($constraints['UserName']);
    unset($constraints['NotNull']);
    $constraints['DecoupledAuthUserName'] = array();

    $fields['name']
      ->setRequired(FALSE)
      ->setConstraints($constraints);

    // Swap to our own required constraint for mail.
    // @todo: Do this.

    return $fields;
  }

}
