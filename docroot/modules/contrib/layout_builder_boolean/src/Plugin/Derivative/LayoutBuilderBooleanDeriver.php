<?php

namespace Drupal\layout_builder_boolean\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Layout\LayoutDefinition;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Layout\LayoutPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides LB Boolean version of all Layouts.
 *
 * @internal
 *   Plugin derivers are internal.
 */
class LayoutBuilderBooleanDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The layout manager.
   *
   * @var \Drupal\Core\Layout\LayoutPluginManager
   */
  protected $layoutPluginManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Constructs a new LayoutBuilderBooleanDeriver.
   */
  public function __construct(LayoutPluginManager $layout_plugin_manager, ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler) {
    $this->layoutPluginManager = $layout_plugin_manager;
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('plugin.manager.core.layout'),
      $container->get('module_handler'),
      $container->get('theme_handler'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    assert($base_plugin_definition instanceof LayoutDefinition);

    $layouts = $this->ymlLayoutDefinitions();
    foreach ($layouts as $layout_id => $definition) {
      $derivative = clone $base_plugin_definition;
      $derivative->setLabel($definition['label'] . ' (Boolean)');
      $derivative->setIconMap($definition['icon_map'] ?? NULL);
      $derivative->setCategory($definition['category'] ?? NULL);
      $derivative->setConfigDependencies(['module' => [$definition['provider']]]);

      $regions = [];
      foreach ($definition['regions'] as $key => $label) {
        $regions["true:$key"] = $label;
        $regions["false:$key"] = $label;
      }
      $derivative->setRegions($regions);

      $derivative->addContextDefinition('entity', ContextDefinition::create('entity')->setRequired(FALSE));
      $this->derivatives[$layout_id] = $derivative;
    }
    return $this->derivatives;
  }

  /**
   * Discover all layou plugins defined through yml.
   *
   * This is special discovery so that this derivative does not call
   * LayoutPluginManager::getDefinitions(). That would result in an
   * infinitely recursive nightmare.
   *
   * @return array
   *   An array of layout definition arrays, not objects.
   */
  protected function ymlLayoutDefinitions() {
    $discovery = new YamlDiscovery('layouts', $this->moduleHandler->getModuleDirectories() + $this->themeHandler->getThemeDirectories());
    $discovery
      ->addTranslatableProperty('label')
      ->addTranslatableProperty('description')
      ->addTranslatableProperty('category');
    return $discovery->getDefinitions();
  }

}
