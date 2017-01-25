<?php

namespace Drupal\Tests\decoupled_auth\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\decoupled_auth\Entity\DecoupledAuthUser;
use Drupal\decoupled_auth\Tests\DecoupledAuthUserCreationTrait;
use Drupal\simpletest\UserCreationTrait;

/**
 * Tests the user entity class and modifications made by decoupled auth.
 *
 * @group decoupled_auth
 * @see \Drupal\decoupled_auth\Entity\User
 */
class UserEntityTest extends KernelTestBase {

  use DecoupledAuthUserCreationTrait;
  use UserCreationTrait;

  /**
   * Create an unsaved decoupled user.
   *
   * @var bool
   */
  const UNSAVED_USER_DECOUPLED = TRUE;

  /**
   * Create an unsaved coupled user.
   *
   * @var bool
   */
  const UNSAVED_USER_COUPLED = FALSE;

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
   * Get validation messages from unsaved user object.
   *
   * @param \Drupal\decoupled_auth\Entity\DecoupledAuthUser $user
   *   User entity being validated.
   *
   * @return array
   *   Array of message template strings.
   */
  protected function getUserValidation(DecoupledAuthUser $user) {
    $violations = $user->validate();
    $messages = [];
    foreach ($violations as $violation) {
      $messages[] = $violation->getMessageTemplate();
    }
    return $messages;
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

  /**
   * Tests username constraints for decoupled users.
   */
  public function testUserNameValidationDecoupled() {
    // Test username empty validation.
    // Expected: no errors.
    $user_1 = $this->createUnsavedUser(self::UNSAVED_USER_DECOUPLED);
    $messages = $this->getUserValidation($user_1);
    $this->assertEmpty($messages, 'No violation errors.');

    // Test username nonempty validation.
    // Expected: validation errors.
    $user_2 = $this->createUnsavedUser(self::UNSAVED_USER_COUPLED);
    $user_2->decouple();
    $username = $user_2->email_prefix;
    $user_2->setUsername($username);
    $messages = $this->getUserValidation($user_2);
    $this->assertNotEmpty($messages, 'Some violation errors.');
    $this->assertTrue(in_array('Decoupled users cannot have a username.', $messages));
  }

  /**
   * Tests username constraints for coupled users.
   */
  public function testUserNameValidationCoupled() {
    // Test username empty validation.
    // Expected: no errors.
    $user_1 = $this->createUnsavedUser(self::UNSAVED_USER_COUPLED);
    $user_1->setUsername(NULL);
    $messages = $this->getUserValidation($user_1);
    $this->assertNotEmpty($messages, 'No violation errors.');
    $this->assertTrue(in_array('You must enter a username.', $messages));

    // Test username nonempty validation.
    // Expected: validation errors.
    $user_2 = $this->createUnsavedUser(self::UNSAVED_USER_COUPLED);
    $username = $user_2->email_prefix;
    $messages = $this->getUserValidation($user_2);
    $this->assertEmpty($messages, 'No violation errors.');

    // Save user for unique constraint in next test.
    $user_2->save();

    // Test username non unique validation.
    // Expected: validation errors.
    $user_3 = $this->createUnsavedUser(self::UNSAVED_USER_COUPLED, $username);
    $messages = $this->getUserValidation($user_3);
    $this->assertNotEmpty($messages, 'No violation errors.');
    $this->assertTrue(in_array('The username %value is already taken.', $messages));
  }

  /**
   * Tests email constraints for decoupled users.
   */
  public function testUserEmailValidationDecoupled() {
    // Test email empty validation.
    // Expected: no errors.
    $user_1 = $this->createUnsavedUser(self::UNSAVED_USER_DECOUPLED);
    $user_1->setEmail(NULL);
    $messages = $this->getUserValidation($user_1);
    $this->assertEmpty($messages, 'No violation errors.');

    // Test email nonempty validation.
    // Expected: no errors.
    $user_2 = $this->createUnsavedUser(self::UNSAVED_USER_DECOUPLED);
    $messages = $this->getUserValidation($user_2);
    $this->assertEmpty($messages, 'No violation errors.');
  }

  /**
   * Tests email constraints for coupled users.
   */
  public function testUserEmailValidationCoupled() {
    // Test email empty validation.
    // Expected: validation errors.
    $user_1 = $this->createUnsavedUser(self::UNSAVED_USER_COUPLED);
    $user_1->setEmail(NULL);
    $messages = $this->getUserValidation($user_1);
    $this->assertNotEmpty($messages, 'No violation errors.');
    $this->assertTrue(in_array('@name field is required.', $messages));

    // Test username nonempty validation.
    // Expected: no errors.
    $user_2 = $this->createUnsavedUser(self::UNSAVED_USER_COUPLED);
    $email_prefix = $user_2->email_prefix;
    $messages = $this->getUserValidation($user_2);
    $this->assertEmpty($messages, 'No violation errors.');

    // Save user for unique constraint in next test.
    $user_2->save();

    // Test username non unique validation.
    // Expected: validation errors.
    $user_3 = $this->createUnsavedUser(self::UNSAVED_USER_COUPLED, $email_prefix);
    $messages = $this->getUserValidation($user_3);
    $this->assertNotEmpty($messages, 'No violation errors.');
    $this->assertTrue(in_array('The email address %value is already taken.', $messages));
  }

}
