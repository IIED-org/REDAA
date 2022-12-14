<?php

namespace tests\Helpers;

use AcquiaCMS\Cli\Helpers\Utility;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class UtilityTest extends TestCase {
  use ProphecyTrait;

  /**
   * Tests the method: normalizePath() of class Utility.
   *
   * @param string $expected
   *   An expected normalized directory path.
   * @param string $actual
   *   An actual directory path to normalize.
   *
   * @dataProvider dataProviderDirectory
   */
  public function testNormalizeDirectoryPath(string $expected, string $actual) :void {
    $this->assertEquals($expected, Utility::normalizePath($actual));
  }

  /**
   * Provides an array of actual & expected directory path.
   *
   * @return array
   *   Returns an array of directories path.
   */
  public function dataProviderDirectory() :array {
    return [
      [
        "/acquia/acquia-cms-project",
        "/acquia/acquia-cms-project/src/tests/../../",
      ],
      [
        "/acquia/acquia-cms-project/vendor/bin",
        "/acquia/acquia-cms-project/src/tests/unit/Helpers/../../../../../acquia-cms-project/vendor/bin",
      ],
      [
        "/acquia/acquia-cms-project/vendor/bin",
        "/acquia/acquia-cms-project/src/tests/unit/Helpers/../../../../../acquia-cms-project/vendor/bin/../bin",
      ],
      [
        "/acquia/acquia-cms-project/bin/acms.php",
        "/acquia/acquia-cms-project/src/tests/../../var/logs/../../bin/acms.php",
      ],
    ];
  }

  /**
   * Tests the method generateRandomPassword().
   */
  public function testGeneratedPassword() :void {
    $password = Utility::generateRandomPassword(12);
    $this->assertMatchesRegularExpression("/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}/", $password);
  }

}
