<?php

/**
 * @file
 * Contains \Drupal\decoupled_auth\UserInterface.
 */

namespace Drupal\decoupled_auth;

use Drupal\user\UserInterface;

/**
 * Provides an interface defining a user entity.
 *
 * @ingroup user_api
 */
interface DecoupledAuthUserInterface extends UserInterface {

  /**
   * Check whether this user is decoupled.
   *
   * @return bool
   */
  public function isDecoupled();

  /**
   * Set the decoupled state of this user.
   *
   * @param bool|NULL $decoupled
   *   Whether the user is decoupled. If NULL, we work it out from whether name
   *   is set. If FALSE, we clear out name/pass.
   */
  public function setDecoupled($decoupled = NULL);

}
