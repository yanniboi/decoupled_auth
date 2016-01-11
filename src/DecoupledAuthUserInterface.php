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
   * Set this user to the decoupled state.
   *
   * @return DecoupledAuthUserInterface
   */
  public function decouple();

  /**
   * Check whether this user is decoupled.
   *
   * @return bool
   */
  public function isCoupled();

  /**
   * Set this user to the coupled state.
   *
   * @return DecoupledAuthUserInterface
   */
  public function couple();

  /**
   * Calculate the decoupled state of this user.
   *
   * @return bool
   */
  public function calculateDecoupled();

}
