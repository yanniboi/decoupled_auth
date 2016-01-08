<?php

/**
 * @file
 * Contains \Drupal\Tests\decoupled_auth\Kernel\AcquisitionApiTest.
 */

namespace Drupal\Tests\decoupled_auth\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\decoupled_auth\Tests\DecoupledAuthUserCreationTrait;
use Drupal\simpletest\UserCreationTrait;

/**
 * Tests the Migration entity.
 *
 * @coversDefaultClass \Drupal\decoupled_auth\Plugin\Validation\Constraint\DecoupledAuthUserMailUniqueValidator
 * @group decoupled_auth
 */
class UniqueEmailTest extends KernelTestBase {

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
   * Set the configuration for unique emails.
   *
   * @param string $mode
   *   The mode. Can be one of 'all', 'none', 'include' or 'exclude'.
   * @param array $roles
   *   The array of role IDs if in 'include' or 'exclude' mode.
   */
  protected function setUniqueEmailsConfig($mode, $roles = []) {
    $this->config('decoupled_auth.settings')
      ->set('unique_emails.mode', $mode)
      ->set('unique_emails.roles', $roles)
      ->save();
  }

  /**
   * Test the unique email validator in 'all' mode with no existing users.
   *
   * @covers ::validate
   */
  public function testModeAllNone() {
    $this->setUniqueEmailsConfig('all');

    // Test validating a decoupled user.
    // Expected result: No violations
    $user = $this->createUnsavedUser();
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Decoupled user passes validation.');

    // Test validating a coupled user.
    // Expected result: No violations
    $user = $this->createUnsavedUser(FALSE);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Coupled user passes validation.');
  }

  /**
   * Test the unique email validator in 'all' mode with an existing decoupled
   * user.
   *
   * @covers ::validate
   */
  public function testModeAllDecoupled() {
    $this->setUniqueEmailsConfig('all');

    $existing_user = $this->createDecoupledUser();

    // Test validating a decoupled user.
    // Expected result: Violations
    $user = $this->createUnsavedUser(TRUE, $existing_user->email_prefix);
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Decoupled user fails validation.');

    // Test validating a coupled user.
    // Expected result: Violations
    $user = $this->createUnsavedUser(FALSE, $existing_user->email_prefix);
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Coupled user fails validation.');
  }

  /**
   * Test the unique email validator in 'all' mode with an existing coupled
   * user.
   *
   * @covers ::validate
   */
  public function testModeAllCoupled() {
    $this->setUniqueEmailsConfig('all');

    $existing_user = $this->createUser();

    // Test validating a decoupled user.
    // Expected result: Violations
    $user = $this->createUnsavedUser(TRUE, $existing_user->getAccountName());
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Decoupled user fails validation.');

    // Test validating a coupled user.
    // Expected result: Violations
    $user = $this->createUnsavedUser(FALSE, $existing_user->getAccountName());
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Coupled user fails validation.');
  }

  /**
   * Test the unique email validator in 'none' mode with no existing users.
   *
   * @covers ::validate
   */
  public function testModeNoneNone() {
    $this->setUniqueEmailsConfig('none');

    // Test validating a decoupled user.
    // Expected result: No violations
    $user = $this->createUnsavedUser();
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Decoupled user passes validation.');

    // Test validating a coupled user.
    // Expected result: No violations
    $user = $this->createUnsavedUser(FALSE);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Coupled user passes validation.');
  }

  /**
   * Test the unique email validator in 'none' mode with an existing decoupled
   * user.
   *
   * @covers ::validate
   */
  public function testModeNoneDecoupled() {
    $this->setUniqueEmailsConfig('none');

    $existing_user = $this->createDecoupledUser();

    // Test validating a decoupled user.
    // Expected result: No violations
    $user = $this->createUnsavedUser(TRUE, $existing_user->email_prefix);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Decoupled user passes validation.');

    // Test validating a coupled user.
    // Expected result: No violations
    $user = $this->createUnsavedUser(FALSE, $existing_user->email_prefix);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Coupled user passes validation.');
  }

  /**
   * Test the unique email validator in 'none' mode with an existing coupled
   * user.
   *
   * @covers ::validate
   */
  public function testModeNoneCoupled() {
    $this->setUniqueEmailsConfig('none');

    $existing_user = $this->createUser();

    // Test validating a decoupled user.
    // Expected result: Violations
    $user = $this->createUnsavedUser(TRUE, $existing_user->getAccountName());
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Decoupled user fails validation.');

    // Test validating a coupled user.
    // Expected result: Violations
    $user = $this->createUnsavedUser(FALSE, $existing_user->getAccountName());
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Coupled user fails validation.');
  }

  /**
   * Test the unique email validator in 'include' mode with no existing users.
   *
   * @covers ::validate
   */
  public function testModeIncludeNone() {
    $role = $this->createRole([]);
    $this->setUniqueEmailsConfig('include', [$role]);

    // Test validating a decoupled user without the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser();
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Decoupled user passes validation.');

    // Test validating a decoupled user with the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser();
    $user->addRole($role);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Decoupled user passes validation.');

    // Test validating a coupled user without the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser(FALSE);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Coupled user passes validation.');

    // Test validating a coupled user with the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser(FALSE);
    $user->addRole($role);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Coupled user passes validation.');
  }

  /**
   * Test the unique email validator in 'include' mode with an existing decoupled
   * user with no matching roles.
   *
   * @covers ::validate
   */
  public function testModeIncludeDecoupledWithoutRole() {
    $role = $this->createRole([]);
    $this->setUniqueEmailsConfig('include', [$role]);

    $existing_user = $this->createDecoupledUser();

    // Test validating a decoupled user without the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser(TRUE, $existing_user->email_prefix);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Decoupled user without role passes validation.');

    // Test validating a decoupled user with the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser(TRUE, $existing_user->email_prefix);
    $user->addRole($role);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Decoupled user with role passes validation.');

    // Test validating a coupled user without the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser(FALSE, $existing_user->email_prefix);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Coupled user without the role passes validation.');

    // Test validating a coupled user with the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser(FALSE, $existing_user->email_prefix);
    $user->addRole($role);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Coupled user with the role passes validation.');
  }

  /**
   * Test the unique email validator in 'include' mode with an existing decoupled
   * user with a matching roles.
   *
   * @covers ::validate
   */
  public function testModeIncludeDecoupledWithRole() {
    $role = $this->createRole([]);
    $this->setUniqueEmailsConfig('include', [$role]);

    $existing_user = $this->createDecoupledUser();
    $existing_user->addRole($role);
    $existing_user->save();

    // Test validating a decoupled user without the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser(TRUE, $existing_user->email_prefix);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Decoupled user without role passes validation.');

    // Test validating a decoupled user with the role.
    // Expected result: Violations
    $user = $this->createUnsavedUser(TRUE, $existing_user->email_prefix);
    $user->addRole($role);
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Decoupled user with role fails validation.');

    // Test validating a coupled user without the role.
    // Expected result: Violations
    $user = $this->createUnsavedUser(FALSE, $existing_user->email_prefix);
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Coupled user without the role fails validation.');

    // Test validating a coupled user with the role.
    // Expected result: Violations
    $user = $this->createUnsavedUser(FALSE, $existing_user->email_prefix);
    $user->addRole($role);
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Coupled user with the role fails validation.');
  }

  /**
   * Test the unique email validator in 'include' mode with an existing coupled
   * user with no matching roles.
   *
   * @covers ::validate
   */
  public function testModeIncludeCoupledWithoutRole() {
    $role = $this->createRole([]);
    $this->setUniqueEmailsConfig('include', [$role]);

    $existing_user = $this->createUser();

    // Test validating a coupled user without the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser(TRUE, $existing_user->getAccountName());
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Decoupled user without role passes validation.');

    // Test validating a coupled user with the role.
    // Expected result: Violations
    $user = $this->createUnsavedUser(TRUE, $existing_user->getAccountName());
    $user->addRole($role);
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Decoupled user with role fails validation.');

    // Test validating a coupled user without the role.
    // Expected result: Violations
    $user = $this->createUnsavedUser(FALSE, $existing_user->getAccountName());
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Coupled user without the role fails validation.');

    // Test validating a coupled user with the role.
    // Expected result: Violations
    $user = $this->createUnsavedUser(FALSE, $existing_user->getAccountName());
    $user->addRole($role);
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Coupled user with the role fails validation.');
  }

  /**
   * Test the unique email validator in 'include' mode with an existing coupled
   * user with a matching roles.
   *
   * @covers ::validate
   */
  public function testModeIncludeCoupledWithRole() {
    $role = $this->createRole([]);
    $this->setUniqueEmailsConfig('include', [$role]);

    $existing_user = $this->createUser();
    $existing_user->addRole($role);
    $existing_user->save();

    // Test validating a coupled user without the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser(TRUE, $existing_user->getAccountName());
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Coupled user without role passes validation.');

    // Test validating a coupled user with the role.
    // Expected result: Violations
    $user = $this->createUnsavedUser(TRUE, $existing_user->getAccountName());
    $user->addRole($role);
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Coupled user with role fails validation.');

    // Test validating a coupled user without the role.
    // Expected result: Violations
    $user = $this->createUnsavedUser(FALSE, $existing_user->getAccountName());
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Coupled user without the role fails validation.');

    // Test validating a coupled user with the role.
    // Expected result: Violations
    $user = $this->createUnsavedUser(FALSE, $existing_user->getAccountName());
    $user->addRole($role);
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Coupled user with the role fails validation.');
  }

  /**
   * Test the unique email validator in 'exclude' mode with no existing users.
   *
   * @covers ::validate
   */
  public function testModeExcludeNone() {
    $role = $this->createRole([]);
    $this->setUniqueEmailsConfig('exclude', [$role]);

    // Test validating a decoupled user without the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser();
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Decoupled user passes validation.');

    // Test validating a decoupled user with the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser();
    $user->addRole($role);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Decoupled user passes validation.');

    // Test validating a coupled user without the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser(FALSE);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Coupled user passes validation.');

    // Test validating a coupled user with the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser(FALSE);
    $user->addRole($role);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Coupled user passes validation.');
  }

  /**
   * Test the unique email validator in 'exclude' mode with an existing decoupled
   * user with no matching roles.
   *
   * @covers ::validate
   */
  public function testModeExcludeDecoupledWithoutRole() {
    $role = $this->createRole([]);
    $this->setUniqueEmailsConfig('exclude', [$role]);

    $existing_user = $this->createDecoupledUser();

    // Test validating a decoupled user without the role.
    // Expected result: Violations
    $user = $this->createUnsavedUser(TRUE, $existing_user->email_prefix);
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Decoupled user without role fails validation.');

    // Test validating a decoupled user with the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser(TRUE, $existing_user->email_prefix);
    $user->addRole($role);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Decoupled user with role passes validation.');

    // Test validating a coupled user without the role.
    // Expected result: Violations
    $user = $this->createUnsavedUser(FALSE, $existing_user->email_prefix);
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Coupled user without the role fails validation.');

    // Test validating a coupled user with the role.
    // Expected result: Violations
    $user = $this->createUnsavedUser(FALSE, $existing_user->email_prefix);
    $user->addRole($role);
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Coupled user with the role fails validation.');
  }

  /**
   * Test the unique email validator in 'exclude' mode with an existing decoupled
   * user with a matching roles.
   *
   * @covers ::validate
   */
  public function testModeExcludeDecoupledWithRole() {
    $role = $this->createRole([]);
    $this->setUniqueEmailsConfig('exclude', [$role]);

    $existing_user = $this->createDecoupledUser();
    $existing_user->addRole($role);
    $existing_user->save();

    // Test validating a decoupled user without the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser(TRUE, $existing_user->email_prefix);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Decoupled user without role passes validation.');

    // Test validating a decoupled user with the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser(TRUE, $existing_user->email_prefix);
    $user->addRole($role);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Decoupled user with role passes validation.');

    // Test validating a coupled user without the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser(FALSE, $existing_user->email_prefix);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Coupled user without the role passes validation.');

    // Test validating a coupled user with the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser(FALSE, $existing_user->email_prefix);
    $user->addRole($role);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Coupled user with the role passes validation.');
  }

  /**
   * Test the unique email validator in 'exclude' mode with an existing coupled
   * user with no matching roles.
   *
   * @covers ::validate
   */
  public function testModeExcludeCoupledWithoutRole() {
    $role = $this->createRole([]);
    $this->setUniqueEmailsConfig('exclude', [$role]);

    $existing_user = $this->createUser();

    // Test validating a coupled user without the role.
    // Expected result: Violations
    $user = $this->createUnsavedUser(TRUE, $existing_user->getAccountName());
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Decoupled user without role fails validation.');

    // Test validating a coupled user with the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser(TRUE, $existing_user->getAccountName());
    $user->addRole($role);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Decoupled user with role passes validation.');

    // Test validating a coupled user without the role.
    // Expected result: Violations
    $user = $this->createUnsavedUser(FALSE, $existing_user->getAccountName());
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Coupled user without the role fails validation.');

    // Test validating a coupled user with the role.
    // Expected result: Violations
    $user = $this->createUnsavedUser(FALSE, $existing_user->getAccountName());
    $user->addRole($role);
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Coupled user with the role fails validation.');
  }

  /**
   * Test the unique email validator in 'exclude' mode with an existing coupled
   * user with a matching roles.
   *
   * @covers ::validate
   */
  public function testModeExcludeCoupledWithRole() {
    $role = $this->createRole([]);
    $this->setUniqueEmailsConfig('exclude', [$role]);

    $existing_user = $this->createUser();
    $existing_user->addRole($role);
    $existing_user->save();

    // Test validating a coupled user without the role.
    // Expected result: Violations
    $user = $this->createUnsavedUser(TRUE, $existing_user->getAccountName());
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Decoupled user without role passes validation.');

    // Test validating a coupled user with the role.
    // Expected result: No violations
    $user = $this->createUnsavedUser(TRUE, $existing_user->getAccountName());
    $user->addRole($role);
    $violations = $user->validate();
    $this->assertEmpty($violations, 'Decoupled user with role passes validation.');

    // Test validating a coupled user without the role.
    // Expected result: Violations
    $user = $this->createUnsavedUser(FALSE, $existing_user->getAccountName());
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Coupled user without the role fails validation.');

    // Test validating a coupled user with the role.
    // Expected result: Violations
    $user = $this->createUnsavedUser(FALSE, $existing_user->getAccountName());
    $user->addRole($role);
    $violations = $user->validate();
    $this->assertNotEmpty($violations, 'Coupled user with the role fails validation.');
  }

}
