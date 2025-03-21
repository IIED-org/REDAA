<?php

namespace Drupal\varnish_purger\Plugin\Purge\DiagnosticCheck;

use Drupal\purge\Plugin\Purge\DiagnosticCheck\DiagnosticCheckBase;
use Drupal\purge\Plugin\Purge\DiagnosticCheck\DiagnosticCheckInterface;
use Drupal\purge\Plugin\Purge\Purger\PurgersServiceInterface;
use Drupal\varnish_purger\Entity\VarnishPurgerSettings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Verifies that only fully configured Varnish purgers load.
 *
 * @PurgeDiagnosticCheck(
 *   id = "varnishconfiguration",
 *   title = @Translation("Varnish"),
 *   description = @Translation("Verifies that only fully configured Varnish purgers load."),
 *   dependent_queue_plugins = {},
 *   dependent_purger_plugins = {"varnish"}
 * )
 */
class ConfigurationCheck extends DiagnosticCheckBase implements DiagnosticCheckInterface {

  /**
   * The purge service.
   *
   * @var \Drupal\purge\Plugin\Purge\Purger\PurgersServiceInterface
   */
  protected $purgePurgers;

  /**
   * Constructs the PurgerAvailableCheck object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\purge\Plugin\Purge\Purger\PurgersServiceInterface $purge_purgers
   *   The purge executive service, which wipes content from external caches.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PurgersServiceInterface $purge_purgers) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->purgePurgers = $purge_purgers;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('purge.purgers')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function run() {

    // Load configuration objects for all enabled HTTP purgers.
    $plugins = [];
    foreach ($this->purgePurgers->getPluginsEnabled() as $id => $plugin_id) {
      if (in_array($plugin_id, ['varnish', 'varnishbundled'])) {
        $plugins[$id] = VarnishPurgerSettings::load($id);
      }
    }

    // Perform checks against configuration.
    $labels = $this->purgePurgers->getLabels();
    foreach ($plugins as $id => $settings) {
      $t = ['@purger' => $labels[$id]];
      foreach (['name', 'hostname', 'port', 'request_method', 'scheme'] as $f) {
        if (empty($settings->$f)) {
          $this->recommendation = $this->t("@purger not configured.", $t);
          return self::SEVERITY_ERROR;
        }
      }
      if (($settings->scheme === 'https') && ($settings->port != 443)) {
        $this->recommendation = $this->t("@purger uses https:// but its port is not 443!", $t);
        return self::SEVERITY_WARNING;
      }
      if (($settings->scheme === 'http') && ($settings->port == 443)) {
        $this->recommendation = $this->t("@purger uses http:// but its port is 443!", $t);
        return self::SEVERITY_WARNING;
      }
    }

    $this->recommendation = $this->t("All purgers configured.");
    return self::SEVERITY_OK;
  }

}
