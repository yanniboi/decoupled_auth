<?php

/**
 * @file
 * Contains Drupal\Tests\decoupled\Unit\DecoupledAuthExampleTest
 */

namespace Drupal\Tests\decoupled_auth\Unit;

use Drupal\Tests\UnitTestCase;

/**
 * AddClass units tests.
 *
 * @ingroup decoupled_auth
 * @group decoupled_auth
 */
class DecoupledAuthExampleTest extends UnitTestCase {

  /**
   * Very simple test
   */
  public function testSimple() {
    $a = 5;
    $b = 5;
    $this->assertEquals($a, $b);
  }
}
