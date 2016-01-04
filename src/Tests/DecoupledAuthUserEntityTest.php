<?php

/**
 * @file
 * Contains \Drupal\user\Tests\UserEntityTest.
 */

namespace Drupal\decoupled_auth\Tests;

use Drupal\simpletest\KernelTestBase;

/**
 * Tests the user entity class and modifications made by decoupled auth.
 *
 * @group decoupled_auth
 * @see \Drupal\decoupled_auth\Entity\User
 */
class DecoupledAuthUserEntityTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('system', 'user', 'field', 'decoupled_auth');


  /**
   * Tests some of the Classes that replace core User classes.
   */
  public function testUserClasses() {
    // Get hold of our user type definition.
    $manager = \Drupal::entityTypeManager();
    $user_type = $manager->getDefinition('user');

    $this->assertTrue($user_type->getClass() === 'Drupal\decoupled_auth\Entity\User', 'User class is decoupled_auth class.');
    $this->assertTrue($user_type->getHandlerClass('storage_schema') === 'Drupal\decoupled_auth\UserStorageSchema', 'User storage schema class is decoupled_auth class.');

    // Uninstall decoupled auth to check module removal.
    $this->disableModules(array('decoupled_auth'));

    $manager = \Drupal::entityTypeManager();
    $user_type = $manager->getDefinition('user');

    $this->assertTrue($user_type->getClass() === 'Drupal\user\Entity\User', 'User class is not decoupled_auth class.');
    $this->assertTrue($user_type->getHandlerClass('storage_schema') === 'Drupal\user\UserStorageSchema', 'User storage schema class is not decoupled_auth class.');
  }

}
