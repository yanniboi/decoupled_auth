<?php

/**
 * @file
 * Contains \Drupal\Tests\decoupled_auth\Kernel\AcquisitionApiTest.
 */

namespace Drupal\Tests\decoupled_auth\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\decoupled_auth\Entity\DecoupledAuthUser;
use Drupal\decoupled_auth\AcquisitionServiceInterface;

/**
 * Tests the Migration entity.
 *
 * @coversDefaultClass \Drupal\decoupled_auth\AcquisitionService
 * @group decoupled_auth
 */
class AcquisitionApiTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['decoupled_auth', 'user', 'system'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installSchema('system', 'sequences');
  }

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

  /**
   * Test the standard basic acquisition process of acquiring a user via a
   * unique email.
   *
   * @covers ::acquire
   */
  public function testAcquireBasic() {
    /** @var \Drupal\decoupled_auth\AcquisitionServiceInterface $acquisition */
    $acquisition = \Drupal::service('decoupled_auth.acquisition');

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
    $acquisition = \Drupal::service('decoupled_auth.acquisition');

    // Create our users.
    $user_1 = $this->createUser();
    $this->createUser($user_1->original_name, TRUE);

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
   * Test the behavior when there are blocked users.
   *
   * @covers ::acquire
   */
  public function testAcquireBlocked() {
    // @todo: Write this.
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
   * Test event subscribers.
   *
   * @covers ::acquire
   */
  public function testAcquireEventSubscribers() {
    // @todo: Write this.
  }

}
