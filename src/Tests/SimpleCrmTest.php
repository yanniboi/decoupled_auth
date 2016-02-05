<?php

/**
 * @file
 * Contains \Drupal\decoupled_auth\Tests\SimpleCrmTest.
 */

namespace Drupal\decoupled_auth\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\profile\Tests\ProfileTestTrait;
use Drupal\decoupled_auth\Tests\DecoupledAuthUserCreationTrait;
/**
 * Tests CRM fields and views.
 *
 * @group decoupled_auth
 */
class SimpleCrmTest extends WebTestBase {

  use ProfileTestTrait;
  use DecoupledAuthUserCreationTrait;

  public static $modules = ['user', 'decoupled_auth_crm', 'views'];

  /**
   * Testing admin user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $indiv_user;

  /**
   * Testing admin user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $admin_user;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Check that the required permissions exist.
    $this->checkPermissions([
      "view own crm_indiv profile",
      "add own crm_indiv profile",
      "edit own crm_indiv profile",
    ]);

    // Create a user with permissions for the crm_indiv profile type.
    $this->indiv_user = $this->createUser();
    $this->indiv_user->addRole('crm_indiv');
    $this->indiv_user->save();
    $this->admin_user = $this->createUser([], NULL, TRUE);

  }

  /**
   * Function to get information about users from the Admin People view.
   *
   * @return mixed
   *   An associative array of user information or FALSE if not users are found.
   */
  protected function getCoupledUsersFromView() {
    $this->drupalLogin($this->admin_user);

    $this->drupalGet('admin/people');
    $this->assertResponse(200);

    $result = $this->xpath('//table[contains(@class, "responsive-enabled")]/tbody/tr');
    $result_accounts = array();
    foreach ($result as $account) {
      $name = (string) $account->td[1]->a;
      $roles = array();
      if (isset($account->td[3]->div->ul)) {
        foreach ($account->td[3]->div->ul->li as $element) {
          $roles[] = (string) $element;
        }
      }

      foreach($account->td[1]->a->attributes() as $label => $attribute) {
        if ($label = 'href') {
          $href = explode('/', $attribute);
          $uid = array_pop($href);
        }
      }

      $result_accounts[$name] = array(
        'name' => $name,
        'status' => (string) $account->td[2],
        'roles' => $roles,
        'member_for' => (string) $account->td[4],
        'last_access' => (string) $account->td[5],
      );

      if (isset($uid)) {
        $result_accounts[$name]['uid'] = $uid;
      }
    }

    return !empty($result_accounts) ? $result_accounts : FALSE;
  }

  /**
   * Function to get information about users from the Contacts view.
   *
   * @return mixed
   *   An associative array of user information or FALSE if not users are found.
   */
  protected function getDecoupledUsersFromView() {
    $this->drupalLogin($this->admin_user);

    $this->drupalGet('admin/people/contacts');
    $this->assertResponse(200);

    $result = $this->xpath('//table[contains(@class, "views-table")]/tbody/tr');
    $result_accounts = array();

    foreach ($result as $account) {

      $this->verbose(json_encode($account));

      $email = (string) $account->td[1];
      $email_components = explode('@', $email);
      $name = $email_components[0];

      $roles = array();
      if (isset($account->td[3]->div->ul)) {
        foreach ($account->td[3]->div->ul->li as $element) {
          $roles[] = (string) $element;
        }
      }

      $result_accounts[$name] = array(
        'name' => $name,
        'email' => $email,
        'roles' => $roles,
      );

      if (isset($uid)) {
        $result_accounts[$name]['uid'] = $uid;
      }
    }
    return !empty($result_accounts) ? $result_accounts : FALSE;
  }

  /**
   * Test People and Contact views
   */
  public function testContactViews() {
    // Create a decoupled user to test views.
    $user_1 = $this->createDecoupledUser();
    $email_prefix = $user_1->email_prefix;

    // Fetch the user accounts from both views.
    $coupled_accounts = $this->getCoupledUsersFromView();
    $decoupled_accounts = $this->getDecoupledUsersFromView();

    // Check decoupled user is in contacts view and not people view.
    $this->assertFalse(in_array($email_prefix, array_keys($coupled_accounts)));
    $this->assertTrue(in_array($email_prefix, array_keys($decoupled_accounts)));

    // Check coupled users are in people view and not contacts view.
    $this->assertTrue(in_array($this->indiv_user->label(), array_keys($coupled_accounts)));
    $this->assertTrue(in_array($this->admin_user->label(), array_keys($coupled_accounts)));
    $this->assertFalse(in_array($this->indiv_user->label(), array_keys($decoupled_accounts)));
    $this->assertFalse(in_array($this->admin_user->label(), array_keys($decoupled_accounts)));
  }

  /**
   * Test Simple Crm fields.
   */
  public function testContactFields() {
    $this->drupalLogin($this->indiv_user);

    // Submit the profile add form for an crm_indiv profile.
    $edit = [
      'crm_name[0][value]' => $this->indiv_user->getDisplayName(),
      'crm_email[0][value]' => $this->indiv_user->getEmail(),
      'crm_dob[0][value][year]' => '1990',
      'crm_dob[0][value][month]' => '1',
      'crm_dob[0][value][day]' => '1',
      'crm_gender' => 'female',
    ];
    $this->drupalPostForm('user/' . $this->indiv_user->id() . '/crm_indiv', $edit, t('Save'));

    // Check that the fields have been saved.
    $this->drupalGet('user/' . $this->indiv_user->id() . '/crm_indiv');
    $this->assertResponse(200);

    $this->assertFieldByName('crm_name[0][value]', $this->indiv_user->getDisplayName());
    $this->assertFieldByName('crm_email[0][value]', $this->indiv_user->getEmail());
    $this->assertFieldByName('crm_dob[0][value][year]', '1990');
    $this->assertFieldByName('crm_dob[0][value][month]', '1');
    $this->assertFieldByName('crm_dob[0][value][day]', '1');
    $this->assertFieldByName('crm_gender', 'female');
  }

}
