<?php

namespace Drupal\Tests\layout_builder_boolean\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Tests the deriver that makes the magic happen.
 *
 * @group layout_builder_boolean
 */
class LayoutBuilderBooleanDeriverTest extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'layout_builder',
    'layout_discovery',
    'layout_builder_boolean',
  ];

  /**
   * Tests derivatives of a core layout.
   */
  public function testDerivative() {
    $layout = \Drupal::service('plugin.manager.core.layout')->createInstance('layout_builder_boolean:layout_twocol_section');
    $this->assertSame('layout_builder_boolean', $layout->getBaseId());
    $this->assertSame('layout_twocol_section', $layout->getDerivativeId());

    $definition = $layout->getPluginDefinition();
    $this->assertSame('Two column (Boolean)', $definition->getLabel());
    $this->assertSame('layout--layout-builder-boolean', $definition->getTemplate());
    $this->assertSame('layout__layout_builder_boolean', $definition->getThemeHook());
    $this->assertSame([['first', 'second']], $definition->getIconMap());
    $this->assertSame(['true:first', 'false:first', 'true:second', 'false:second'], $definition->getRegionNames());
    $this->assertSame('layout_builder_boolean', $definition->getProvider());
    $this->assertSame(['module' => ['layout_builder', 'layout_builder_boolean']], $definition->getConfigDependencies());
    $this->assertNotNull($definition->getContextDefinitions()['entity']);
  }

}
