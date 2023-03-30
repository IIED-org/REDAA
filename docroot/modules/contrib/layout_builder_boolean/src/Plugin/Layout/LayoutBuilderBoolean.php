<?php

namespace Drupal\layout_builder_boolean\Plugin\Layout;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Layout\LayoutPluginManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a section that renders differently depending on a field value.
 *
 * @Layout(
 *   id = "layout_builder_boolean",
 *   path = "templates",
 *   template = "layout--layout-builder-boolean",
 *   library = "layout_builder_boolean/layout_builder_boolean",
 *   deriver = "\Drupal\layout_builder_boolean\Plugin\Derivative\LayoutBuilderBooleanDeriver",
 * )
 *
 * @internal
 *   Plugin classes are internal.
 */
class LayoutBuilderBoolean extends LayoutDefault implements ContainerFactoryPluginInterface {

  /**
   * Instance of base layout plugin.
   *
   * @var \Drupal\Core\Layout\LayoutInterface
   */
  protected $baseLayoutInstance;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LayoutPluginManagerInterface $layout_plugin_manager, RouteMatchInterface $route_match, EntityFieldManagerInterface $entity_field_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    if (strpos($plugin_id, static::DERIVATIVE_SEPARATOR)) {
      list(, $derivative_id) = explode(static::DERIVATIVE_SEPARATOR, $plugin_id, 2);
    }
    $this->baseLayoutInstance = $layout_plugin_manager->createInstance($derivative_id, $configuration);
    $this->routeMatch = $route_match;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.core.layout'),
      $container->get('current_route_match'),
      $container->get('entity_field.manager')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = parent::defaultConfiguration();
    return $configuration + [
      'switch_field' => '',
      'base_layout' => $this->getDerivativeId(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form += $this->baseLayoutInstance->buildConfigurationForm($form, $form_state);
    $form['switch_field'] = [
      '#type' => 'select',
      '#options' => $this->getFieldOptions(),
      '#empty_option' => '-Select-',
      '#title' => $this->t('"Switch" Field'),
      '#default_value' => $this->configuration['switch_field'],
      '#description' => $this->t('This is the field that is checked for true/false (or populated/empty) status. It acts like a switch.'),
      '#required' => TRUE,
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->baseLayoutInstance->submitConfigurationForm($form, $form_state);
    $this->configuration = $this->baseLayoutInstance->getConfiguration();
    $this->configuration['switch_field'] = $form_state->getValue('switch_field');
    $this->configuration['base_layout'] = $this->getDerivativeId();
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $build = parent::build($regions);
    $organized_regions = $this->organizeRegions($regions);
    $build['#true'] = $this->baseLayoutInstance->build($organized_regions['true']);
    $build['#false'] = $this->baseLayoutInstance->build($organized_regions['false']);
    $build['#entity'] = $this->getContextValue('entity');
    return $build;
  }

  /**
   * Put the regions into a useful structure.
   *
   * @param array $regions
   *   The regions passed to the build function.
   *
   * @return array|array[]
   *   The regions in a nice structure.
   */
  protected function organizeRegions(array $regions) {
    $organized = [
      'true' => [],
      'false' => [],
    ];
    $base_regions = $this->baseLayoutInstance->getPluginDefinition()->getRegionNames();
    foreach ($base_regions as $key => $label) {
      $organized['true'][$key] = $regions["true:$key"] ?? [];
      $organized['false'][$key] = $regions["false:$key"] ?? [];
    }
    return $organized;
  }

  /**
   * Get fields for options.
   *
   * @return array
   *   Field options that can be used as the swicth field.
   */
  protected function getFieldOptions() {
    $display = $this->routeMatch->getParameter('section_storage')->getContextValue('display');
    $entity_type_id = $display->getTargetEntityTypeId();
    $bundle = $display->getTargetBundle();
    $fields = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);
    $field_options = [];
    // Some base fields aren't suitable for this plugin.
    $fields_we_do_not_want = [
      'changed',
      'created',
      'default_langcode',
      'langcode',
      'nid',
      'revision_default',
      'revision_log',
      'revision_timestamp',
      'revision_translation_affected',
      'revision_uid',
      'title',
      'type',
      'uid',
      'uuid',
      'vid',
    ];
    foreach ($fields as $key => $definition) {
      if (!in_array($key, $fields_we_do_not_want, TRUE)) {
        $field_options[$key] = $definition->getLabel();
      }
    }
    // Sort the list alphabetically to be nice.
    ksort($field_options);
    return $field_options;
  }

}
