<?php

/**
 * @file
 * Contains \Drupal\decoupled_auth\DecoupledAuthUserCreationTrait.
 */

namespace Drupal\decoupled_auth\Tests;

use Drupal\decoupled_auth\Entity\DecoupledAuthUser;
use Drupal\Component\Utility\SafeMarkup;

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
   * @var string $email_prefix
   *   This is suffixed with '@example.com' for the mail and, if not decoupled,
   *   is used for the name of the user. If not given, a random name will be
   *   generated
   *
   * @return \Drupal\decoupled_auth\Entity\DecoupledAuthUser
   *   The created user.
   */
  protected function createDecoupledUser($email_prefix = NULL) {
    // Generate a random name if we don't have one.
    if (!$email_prefix) {
      $email_prefix = $this->randomMachineName();
    }

    // Create and save our user.
    $user = DecoupledAuthUser::create([
      'mail' => $email_prefix . '@example.com',
      'name' => NULL,
      'status' => 1,
    ]);
    $user->save();

    // Set the given name as a property so it can be accessed when the user is
    // decoupled.
    $user->email_prefix = $email_prefix;

    $this->assertTrue($user, SafeMarkup::format('Decoupled user successfully created with the email %email.', ['%mail' => $user->getEmail()]));

    return $user;
  }

}
