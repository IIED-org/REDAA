<?php

namespace Acquia\DrupalEnvironmentDetector;

/**
 * Maps Acquia Hosting environment names to standard names (prod/stage/dev).
 *
 * @package Acquia\DrupalEnvironmentDetector
 */
class EnvironmentNames {

  /**
   * Is this a prod environment on Acquia hosting.
   *
   * @param string $ah_env
   *   Environment machine name.
   *
   * @return bool
   *   TRUE if prod, FALSE otherwise.
   */
  public static function isAhProdEnv(string $ah_env): bool {
    // ACE prod is 'prod'; ACSF can be '01live', '02live', ...
    return $ah_env === 'prod' || preg_match('/^\d*live$/', $ah_env);
  }

  /**
   * Is this a stage environment on Acquia hosting.
   *
   * Legacy stage environments are typically named 'stg'. More recently they are
   * named 'test'. Some applications may have non-standard environment names,
   * these are not supported.
   *
   * @param string $ah_env
   *   Environment machine name.
   *
   * @return bool
   *   TRUE if 'stage', FALSE otherwise.
   */
  public static function isAhStageEnv(string $ah_env): bool {
    // ACE staging is 'test', 'stg', or 'stage'; ACSF is '01test', '02test', ...
    return preg_match('/^\d*test$/', $ah_env) || $ah_env === 'stg' || $ah_env === 'stage';
  }

  /**
   * Is this a dev environment on Acquia hosting.
   *
   * @param string $ah_env
   *   Environment machine name.
   *
   * @return bool
   *   TRUE if dev, FALSE otherwise.
   */
  public static function isAhDevEnv(string $ah_env): bool {
    // ACE dev is 'dev', 'dev1', ...; ACSF dev is '01dev', '02dev', ...
    return (bool) preg_match('/^\d*dev\d*$/', $ah_env);
  }

  /**
   * Is AH ODE.
   *
   * @param string $ah_env
   *   Environment machine name.
   *
   * @return bool
   *   TRUE if ODE, FALSE otherwise.
   */
  public static function isAhOdeEnv(string $ah_env): bool {
    // CDEs (formerly 'ODEs') can be 'ode1', 'ode2', ...
    return (bool) preg_match('/^ode\d*$/', $ah_env);
  }

  /**
   * Is AH IDE.
   *
   * @param string $ah_env
   *   Environment machine name.
   *
   * @return bool
   *   TRUE if IDE, FALSE otherwise.
   */
  public static function isAhIdeEnv(string $ah_env): bool {
    return strtolower($ah_env) === 'ide';
  }

}
