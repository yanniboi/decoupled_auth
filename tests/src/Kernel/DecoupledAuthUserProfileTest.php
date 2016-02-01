<?php

/**
 * @file
 * Contains \Drupal\Tests\decoupled_auth\Kernel\DecoupledAuthUserProfileTest.
 */

namespace Drupal\Tests\decoupled_auth\Kernel;

use Drupal\decoupled_auth\Tests\DecoupledAuthUserCreationTrait;
use Drupal\simpletest\UserCreationTrait;
use Drupal\profile\Tests\ProfileTestTrait;
use Drupal\profile\Entity\Profile;
use Drupal\simpletest\WebTestBase;
use Drupal\user\Entity\User;

/**
 * Tests Profile integration.
 *
 * @group decoupled_auth
 * @see \Drupal\decoupled_auth\Entity\User
 */
class DecoupledAuthUserProfileTest extends WebTestBase {
  use DecoupledAuthUserCreationTrait;
  use UserCreationTrait;
  use ProfileTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['decoupled_auth', 'user', 'system', 'profile'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
  }

  /**
   * Test profile field on users.
   */
  public function testUserNameValidationDecoupled() {
    $profile_type = $this->createProfileType('test_defaults', 'test_defaults');
    $type_id = $profile_type->id();

    $user = $this->createUser([]);

    // Create new profiles.
    $profile1 = Profile::create([
      'type' => $type_id,
      'uid' => $user->id(),
    ]);
    $profile1->setActive(TRUE);
    $profile1->save();

    // Reload user values.
    $user = User::load($user->id());

    $this->assertTrue(in_array('profile_' . $type_id, array_keys($user->getFields())));
    $this->assertTrue($user->profile_test_defaults->count());
  }

}
