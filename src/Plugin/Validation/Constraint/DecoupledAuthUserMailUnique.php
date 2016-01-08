<?php

/**
 * @file
 * Contains \Drupal\decoupled_auth\Plugin\Validation\Constraint\DecoupledAuthUserMailUnique.
 */

namespace Drupal\decoupled_auth\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if a user's email address is unique on the site within coupled users
 * and decoupled users of specific roles (if configured).
 *
 * @Constraint(
 *   id = "DecoupledAuthUserMailUnique",
 *   label = @Translation("User email unique (decoupled authentication)", context = "Validation")
 * )
 */
class DecoupledAuthUserMailUnique extends Constraint {

  public $message = 'The email address %value is already taken.';

}
