<?php

/**
 * @file
 * Contains \Drupal\decoupled_auth\Tests\RegistrationTest.
 */

namespace Drupal\decoupled_auth\Tests;

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
    $config = $this->config('user.settings');
    // Don't require email verification and allow registration by site visitors
    // without administrator approval.
    $config
      ->set('verify_mail', FALSE)
      ->set('register', USER_REGISTER_VISITORS)
      ->save();

    // Disable acquisition on registration.
    $acquisition_config = $this->config('decoupled_auth.settings');
    $acquisition_config->set('acquisitions.registration', 0)->save();

    $edit = array();
    $edit['name'] = $name = $this->randomMachineName();
    $edit['mail'] = $mail = $edit['name'] . '@example.com';
    $edit['pass[pass1]'] = $pass = $this->randomMachineName();
    $edit['pass[pass2]'] = $pass;
    $this->drupalPostForm('user/register', $edit, t('Create new account'));
    $this->assertText(t('Registration successful. You are now logged in.'));

    $user_storage = $this->container->get('entity_type.manager')->getStorage('user');
    $accounts = $user_storage->loadByProperties( array(
      'name' => $name,
    ));

    if (!empty($accounts)) {
      $account = reset($accounts);
      $this->assertTrue($account->isActive());
      $this->assertEqual($name, $account->getUsername());
    }
  }

  /**
   * Test the acquisition registration process when there is no existing user.
   */
  public function testAcquisitionNone() {
    // @todo: Test when there is no user (should create).
    $edit = array();
    $edit['name'] = $name = $this->randomMachineName();
    $edit['mail'] = $mail = $edit['name'] . '@example.com';
    $this->drupalPostForm('user/register', $edit, t('Create new account'));

    $user_storage = $this->container->get('entity_type.manager')->getStorage('user');
    $accounts = $user_storage->loadByProperties( array(
      'name' => $name,
    ));

    if (!empty($accounts)) {
      $account = reset($accounts);
      $this->assertTrue($account->isActive());
      $this->assertEqual($name, $account->getUsername());
    }
  }

  /**
   * Test the normal registration process when there is a single existing
   * user.
   */
  public function testNormalSingle() {
    $config = $this->config('user.settings');
    // Don't require email verification and allow registration by site visitors
    // without administrator approval.
    $config
      ->set('verify_mail', FALSE)
      ->set('register', USER_REGISTER_VISITORS)
      ->save();

    // Change the site config to disable acquisitions.
    $acquisition_config = $this->config('decoupled_auth.settings');
    $acquisition_config->set('acquisitions.registration', 0)->save();

    $user_1 = $this->createDecoupledUser();
    $email = $user_1->email_prefix . '@example.com';

    $edit = array();
    $edit['name'] = $this->randomMachineName();
    $edit['mail'] = $email;
    $edit['pass[pass1]'] = $pass = $this->randomMachineName();
    $edit['pass[pass2]'] = $pass;
    $this->drupalPostForm('user/register', $edit, t('Create new account'));
    $this->assertText(t('Registration successful. You are now logged in.'));

    $this->drupalLogout();

    $user_storage = $this->container->get('entity_type.manager')->getStorage('user');
    $accounts = $user_storage->loadByProperties( array(
      'mail' => $email,
    ));

    if (!empty($accounts)) {
      $account = reset($accounts);
      $this->assertTrue($account->isActive());
      $this->assertEqual($email, $account->getEmail());
      $this->assertNotEqual($user_1->id(), $account->id());
    }

    $name = $this->randomMachineName();
    $email = $name . '@example.com';
    $this->drupalCreateUser(array(), $name);

    $edit = array();
    $edit['name'] = $this->randomMachineName();
    $edit['mail'] = $email;
    $edit['pass[pass1]'] = $pass = $this->randomMachineName();
    $edit['pass[pass2]'] = $pass;
    $this->drupalPostForm('user/register', $edit, t('Create new account'));
    $this->assertText('The email address ' . $name . '@example.com is already taken.');
  }

  /**
   * Test the acquisition registration process when there is a single existing
   * user.
   */
  public function testAcquisitionSingle() {
    $config = $this->config('user.settings');
    // Don't require email verification and allow registration by site visitors
    // without administrator approval.
    $config
      ->set('verify_mail', FALSE)
      ->set('register', USER_REGISTER_VISITORS)
      ->save();

    $user_1 = $this->createDecoupledUser();
    $email = $user_1->email_prefix . '@example.com';

    $edit = array();
    $edit['name'] = $this->randomMachineName();
    $edit['mail'] = $email;
    $edit['pass[pass1]'] = $pass = $this->randomMachineName();
    $edit['pass[pass2]'] = $pass;
    $this->drupalPostForm('user/register', $edit, t('Create new account'));
    $this->assertText(t('Registration successful. You are now logged in.'));

    $this->drupalLogout();

    $user_storage = $this->container->get('entity_type.manager')->getStorage('user');
    $accounts = $user_storage->loadByProperties( array(
      'mail' => $email,
    ));

    if (!empty($accounts)) {
      $account = reset($accounts);
      $this->assertTrue($account->isActive());
      $this->assertEqual($email, $account->getEmail());
      $this->assertEqual($user_1->id(), $account->id());
    }

    $name = $this->randomMachineName();
    $email = $name . '@example.com';
    $this->drupalCreateUser(array(), $name);

    $edit = array();
    $edit['name'] = $this->randomMachineName();
    $edit['mail'] = $email;
    $edit['pass[pass1]'] = $pass = $this->randomMachineName();
    $edit['pass[pass2]'] = $pass;
    $this->drupalPostForm('user/register', $edit, t('Create new account'));
    $this->assertText('The email address ' . $name . '@example.com is already taken.');
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
