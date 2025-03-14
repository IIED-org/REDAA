<?php

namespace Drupal\varnish_purger\Entity;

use Drupal\purge\Plugin\Purge\Purger\PurgerSettingsBase;
use Drupal\purge\Plugin\Purge\Purger\PurgerSettingsInterface;

// phpcs:disable Drupal.NamingConventions.ValidVariableName.LowerCamelName

/**
 * Defines the HTTP purger settings entity.
 *
 * @ConfigEntityType(
 *   id = "varnishpurgersettings",
 *   label = @Translation("Varnish purger settings"),
 *   config_prefix = "settings",
 *   static_cache = TRUE,
 *   entity_keys = {"id" = "id"},
 *   config_export = {
 *     "id",
 *     "name",
 *     "invalidationtype",
 *     "hostname",
 *     "port",
 *     "path",
 *     "request_method",
 *     "scheme",
 *     "verify",
 *     "headers",
 *     "body",
 *     "body_content_type",
 *     "runtime_measurement",
 *     "timeout",
 *     "connect_timeout",
 *     "cooldown_time",
 *     "max_requests",
 *     "http_errors",
 *   }
 * )
 */
class VarnishPurgerSettings extends PurgerSettingsBase implements PurgerSettingsInterface {

  //
  // Instance metadata:
  //
  /**
   * The readable name of this purger.
   *
   * @var string
   */
  public $name = '';

  /**
   * The invalidation plugin ID that this purger invalidates.
   *
   * @var string
   */
  public $invalidationtype = 'tag';

  //
  // Primary request information:
  //
  /**
   * The host or IP-address to connect to.
   *
   * @var string
   */
  public $hostname = 'localhost';

  /**
   * The port to connect to.
   *
   * @var int
   */
  public $port = 80;

  /**
   * The HTTP path.
   *
   * @var string
   */
  public $path = '/';

  /**
   * The HTTP request method.
   *
   * @var string
   */
  public $request_method = 'BAN';

  /**
   * The HTTP scheme.
   *
   * @var string
   */
  public $scheme = 'http';

  /**
   * Whether to verify SSL certificates or not.
   *
   * @var bool
   *
   * @see http://docs.guzzlephp.org/en/latest/request-options.html#verify
   */
  public $verify = TRUE;

  //
  // Request headers (outbound):
  //
  /**
   * Configured outgoing HTTP headers.
   *
   * @var array[]
   */
  public $headers = [];

  /**
   * When FALSE, dynamic capacity calculation will be disabled.
   *
   * Based upon the connect_timeout and timeout settings.
   *
   * @var bool
   */
  public $runtime_measurement = TRUE;

  /**
   * The timeout of the request in seconds.
   *
   * @var float
   */
  public $timeout = 1.0;

  /**
   * The number of seconds to wait while trying to connect to a server.
   *
   * @var float
   */
  public $connect_timeout = 1.0;

  /**
   * Number of seconds to wait after one or more invalidations took place.
   *
   * (so that other purgers get fresh content).
   *
   * @var float
   */
  public $cooldown_time = 0.0;

  /**
   * Defines the maximum number of HTTP requests.
   *
   * The maximum number of HTTP requests that can be made during Drupal's
   * execution lifetime. Usually, PHP resource restraints lower this
   * value dynamically, but it can be met at the CLI.
   *
   * @var int
   */
  public $max_requests = 100;

  //
  // Success resolution:
  //
  /**
   * Whether 4xx and 5xx responses need to be treated as failures or not.
   *
   * @var bool
   *
   * @see http://docs.guzzlephp.org/en/latest/request-options.html#http-errors
   */
  public $http_errors = TRUE;


  /**
   * The form settings.
   *
   * @var array
   */
  public $settings = '';

}
