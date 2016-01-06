<?php

/**
 * @file
 * Contains \Drupal\Tests\decoupled_auth\Kernel\DecoupledAuthUserEntityTest.
 */

namespace Drupal\Tests\decoupled_auth\Kernel;

use Drupal\KernelTests\KernelTestBase;

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
  public static $modules = ['decoupled_auth', 'user', 'system'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['decoupled_auth']);
    $this->installEntitySchema('user');
    $this->installSchema('system', 'sequences');
  }

  /**
   * Tests some of the Classes that replace core User classes.
   */
  public function testUserClasses() {
    // Get hold of our user type definition.
    $manager = $this->container->get('entity_type.manager');
    $user_type = $manager->getDefinition('user');

    $this->assertEquals('Drupal\decoupled_auth\Entity\DecoupledAuthUser', $user_type->getClass(), 'User class is decoupled_auth class.');
    $this->assertEquals('Drupal\decoupled_auth\DecoupledAuthUserStorageSchema', $user_type->getHandlerClass('storage_schema'), 'User storage schema class is decoupled_auth class.');

    // Uninstall decoupled auth to check module removal.
    $this->disableModules(array('decoupled_auth'));

    $manager = $this->container->get('entity_type.manager');
    $user_type = $manager->getDefinition('user');

    $this->assertEquals('Drupal\user\Entity\User', $user_type->getClass(), 'User class is decoupled_auth class.');
    $this->assertEquals('Drupal\user\UserStorageSchema', $user_type->getHandlerClass('storage_schema'), 'User storage schema class is decoupled_auth class.');
  }

}
