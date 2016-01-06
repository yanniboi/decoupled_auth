<?php

/**
 * @file
 * Contains \Drupal\Tests\decoupled_auth\Kernel\AcquisitionApiTest.
 */

namespace Drupal\Tests\decoupled_auth\Kernel;

use Drupal\simpletest\WebTestBase;
use Drupal\decoupled_auth\Tests\DecoupledAuthUserCreationTrait;

/**
 * Tests the Migration entity.
 *
 * @group decoupled_auth
 */
class RegistrationTest extends WebTestBase {

  use DecoupledAuthUserCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['decoupled_auth', 'user', 'system'];

  /**
   * Test the standard registration process when there is no existing user.
   */
  public function testNormalNone() {
    // @todo: Change the site config to disable acquisitions.

    // @todo: Test when there is no user (should create).
  }

  /**
   * Test the acquisition registration process when there is no existing user.
   */
  public function testAcquisitionNone() {
    // @todo: Test when there is no user (should create).
  }

  /**
   * Test the normal registration process when there is a single existing
   * user.
   */
  public function testNormalSingle() {
    // @todo: Change the site config to disable acquisitions.

    // @todo: Test when the user is decoupled (should create).
    // This will actually fail validation until
    // https://www.drupal.org/node/2630366 is in.

    // @todo: Test when the user is coupled (should fail validation).
  }

  /**
   * Test the acquisition registration process when there is a single existing
   * user.
   */
  public function testAcquisitionSingle() {
    // @todo: Test when the user is decoupled (should acquire).

    // @todo: Test when the user is coupled (should fail validation).
  }

  /**
   * Test the normal registration process when there is are multiple existing
   * users.
   */
  public function testNormalMultiple() {
    // @todo: Change the site config to disable acquisitions.

    // @todo: Test when the users are all decoupled (should create).
    // This will actually fail validation until
    // https://www.drupal.org/node/2630366 is in.

    // @todo: Test when the user one user is coupled (should fail validation).
  }

  /**
   * Test the acquisition registration process when there is are multiple
   * existing users.
   */
  public function testAcquisitionMultiple() {
    // @todo: Test when the users are all decoupled (should create).
    // This will actually fail validation until
    // https://www.drupal.org/node/2630366 is in.

    // @todo: Test when the user one user is coupled (should fail validation).
  }

  /**
   * Test the acquisition registration process when there is are multiple
   * existing users and we are acquiring the first.
   */
  public function testAcquisitionMultipleFirst() {
    // @todo: Change the site config to acquire the first.

    // @todo: Test when the users are all decoupled (should acquire).

    // @todo: Test when the user one user is coupled (should fail validation).
  }

}
