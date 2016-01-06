<?php

/**
 * @file
 * Contains \Drupal\decoupled_auth\DecoupledAuthUserCreationTrait.
 */

namespace Drupal\decoupled_auth\Tests;

use Drupal\decoupled_auth\Entity\DecoupledAuthUser;

/**
 * Provides methods to create additional test users for decoupled auth tests
 *
 * This trait is meant to be used only by test classes extending
 * \Drupal\simpletest\TestBase or Drupal\KernelTests\KernelTestBase.
 */
trait DecoupledAuthUserCreationTrait {

  /**
   * Create a user with the given email and name.
   *
   * @var string $name
   *   This is suffixed with '@example.com' for the mail and, if not decoupled,
   *   is used for the name of the user. If not given, a random name will be
   *   generated.
   * @var bool $decoupled
   *   Whether this should be a decoupled user. Defaults to FALSE.
   *
   * @return \Drupal\decoupled_auth\Entity\DecoupledAuthUser
   *   The created user.
   */
  protected function createUser($name = NULL, $decoupled = FALSE) {
    // Generate a random name if we don't have one.
    if (!$name) {
      $name = $this->randomMachineName();
    }

    // Create and save our user.
    $user = DecoupledAuthUser::create([
      'mail' => $name . '@example.com',
      'name' => $decoupled ? NULL : $name,
      'status' => 1,
    ]);
    $user->save();

    // Set the given name as a property so it can be accessed when the user is
    // decoupled.
    $user->original_name = $name;

    $this->assertTrue($user, 'User successfully created.');

    return $user;
  }

}
