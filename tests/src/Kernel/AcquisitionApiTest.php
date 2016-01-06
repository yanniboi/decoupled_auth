<?php

/**
 * @file
 * Contains \Drupal\Tests\decoupled_auth\Kernel\AcquisitionApiTest.
 */

namespace Drupal\Tests\decoupled_auth\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\decoupled_auth\Tests\DecoupledAuthUserCreationTrait;
use Drupal\simpletest\UserCreationTrait;
use Drupal\decoupled_auth\AcquisitionServiceInterface;

/**
 * Tests the Migration entity.
 *
 * @coversDefaultClass \Drupal\decoupled_auth\AcquisitionService
 * @group decoupled_auth
 */
class AcquisitionApiTest extends KernelTestBase {

  use DecoupledAuthUserCreationTrait;
  use UserCreationTrait;

  /**
   * {@inheritdoc}
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
   * Test the standard basic acquisition process of acquiring a user via a
   * unique email.
   *
   * @covers ::acquire
   */
  public function testAcquireBasic() {
    /** @var \Drupal\decoupled_auth\AcquisitionServiceInterface $acquisition */
    $acquisition = $this->container->get('decoupled_auth.acquisition');

    // Create the user we will attempt to acquire.
    $user = $this->createUser();

    // Run our acquisition.
    $values = ['mail' => $user->getEmail()];
    $acquired_user = $acquisition->acquire($values, ['name' => 'decoupled_auth_AcquisitionTest'], $method);

    // Check the result.
    if (!$acquired_user) {
      $this->fail('Failed to acquire user.');
    }
    else {
      $this->assertEquals('acquire', $method, 'Successfully acquired user.');
      $this->assertEquals($user->id(), $acquired_user->id(), 'Acquired correct user.');
    }
  }

  /**
   * Test the behaviour when there are multiple users with the same email.
   *
   * @covers ::acquire
   */
  public function testAcquireMultiple() {
    /** @var \Drupal\decoupled_auth\AcquisitionServiceInterface $acquisition */
    $acquisition = $this->container->get('decoupled_auth.acquisition');

    // Create our users.
    $user_1 = $this->createUser();
    $this->createDecoupledUser($user_1->getAccountName());

    // Set up our values for acquiring.
    $values = ['mail' => $user_1->getEmail()];

    // First try without the default behaviors - we expect $user_1 to be
    // acquired as it is the only coupled user.
    $acquired_user = $acquisition->acquire($values, ['name' => 'decoupled_auth_AcquisitionTest'], $method);
    if (!$acquired_user) {
      $this->fail('Failed to acquire user.');
    }
    else {
      $this->assertEquals('acquire', $method, 'Successfully acquired user.');
      $this->assertEquals($user_1->id(), $acquired_user->id(), 'Acquired correct user.');
    }

    // Next try with no behaviors. We expect no user.
    $acquired_user = $acquisition->acquire($values, ['behavior' => NULL], $method);
    $this->assertNull($acquired_user, 'Unable to acquire a user.');

    // Finally try with first match behavior. We expect either to be acquired.
    $acquired_user = $acquisition->acquire($values, ['behavior' => AcquisitionServiceInterface::BEHAVIOR_FIRST], $method);
    if (!$acquired_user) {
      $this->fail('Failed to acquire user.');
    }
    else {
      $this->assertEquals('acquire', $method, 'Successfully acquired user.');
      $this->assertEquals($user_1->getEmail(), $acquired_user->getEmail(), 'Acquired correct user.');
    }
  }

  /**
   * Test the behavior when using the
   * \Drupal\decoupled_auth\AcquisitionServiceInterface::BEHAVIOR_CREATE
   * behavior.
   *
   * @covers ::acquire
   */
  public function testAcquireCreate() {
    // @todo: Write this.
  }

  /**
   * Test the behavior when status conditions.
   *
   * @covers ::acquire
   */
  public function testAcquireStatusCondition() {
    // @todo: Write this.
  }

  /**
   * Test acquisitions with role conditions.
   *
   * @covers ::acquire
   */
  public function testAcquireRoleConditions() {
    // @todo: Write this.
  }

  /**
   * Test acquisitions with other additional conditions.
   *
   * @covers ::acquire
   */
  public function testAcquireConditions() {
    // @todo: Write this.
  }

  /**
   * Test that configuration defaults and changes set the correct default
   * context.
   *
   * @covers ::acquire
   */
  public function testAcquireConfig() {
    // Check the default configuration.
    /** @var \Drupal\decoupled_auth\AcquisitionServiceInterface $acquisition */
    $acquisition = \Drupal::service('decoupled_auth.acquisition');
    $context = $acquisition->getContext();
    $expected = AcquisitionServiceInterface::BEHAVIOR_CREATE | AcquisitionServiceInterface::BEHAVIOR_PREFER_COUPLED;
    $this->assertEquals($expected, $context['behavior'], 'Default configuration sets the correct default behavior');

    // Change the configuration.
    $this->config('decoupled_auth.settings')
      ->set('acquisitions.behavior_first', 1)
      ->save();

    // Check our updated configuration.
    /** @var \Drupal\decoupled_auth\AcquisitionServiceInterface $acquisition */
    $acquisition = \Drupal::service('decoupled_auth.acquisition');
    $context = $acquisition->getContext();
    $expected = $expected | AcquisitionServiceInterface::BEHAVIOR_FIRST;
    $this->assertEquals($expected, $context['behavior'], 'Enabling first match configuration sets the correct default behavior');
  }

  /**
   * Test event subscribers.
   *
   * @covers ::acquire
   */
  public function testAcquireEventSubscribers() {
    // @todo: Write this.
  }

}
