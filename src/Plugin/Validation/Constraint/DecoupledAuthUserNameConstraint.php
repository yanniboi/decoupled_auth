<?php

/**
 * @file
 * Contains \Drupal\decoupled_auth\Plugin\Validation\Constraint\UserNameConstraint.
 */

namespace Drupal\decoupled_auth\Plugin\Validation\Constraint;

use Drupal\user\Plugin\Validation\Constraint\UserNameConstraint;

/**
 * Checks if a value is a valid user name allowing it to be empty.
 *
 * @Constraint(
 *   id = "DecoupledAuthUserName",
 *   label = @Translation("User name (Decoupled auth)", context = "Validation"),
 * )
 */
class DecoupledAuthUserNameConstraint extends UserNameConstraint {

  public $decoupledNotEmptyMessage = 'Decoupled users cannot have a username.';

}