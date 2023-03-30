<?php

namespace Drupal\Tests\layout_builder_boolean\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Testing for layout_builder_boolean module.
 *
 * This tests that known configuration gets transformed into the
 * expected FE.
 *
 * @group layout_builder_boolean
 */
class LayoutBuilderBooleanTest extends BrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['layout_builder_boolean_tests'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests html output of layout_bg sections.
   */
  public function testLayoutBuilderBoolean() {

    $session = $this->assertSession();

    // @todo This should use a provider so errors are clearer.
    $fields = [
      'promote',
      'field_link',
      'body',
      'field_image',
      'field_boolean',
    ];
    $display = \Drupal::service('entity_display.repository')->getViewDisplay('node', 'lbb_test', 'default');
    $tps = $display->get('third_party_settings');
    foreach ($fields as $field) {
      // Update field being used as the switch. We want to test different fields
      // types that have different main properties to be on the safe side.
      foreach ($tps['layout_builder']['sections'] as $section) {
        $settings = $section->getLayoutSettings();
        $settings['switch_field'] = $field;
        $section->setLayoutSettings($settings);
      }
      $display->set('third_party_settings', $tps)->save();

      // Node one has no true fields other than status.
      $this->drupalGet('/node/1');
      $session->elementTextContains('css', '.layout--twocol-section--67-33 .layout__region--first', 'LBB Test');
      $session->elementTextNotContains('css', '.layout--twocol-section--67-33 .layout__region--first', 'Mostly Blank Node');
      $session->elementTextNotContains('css', '.layout--twocol-section--67-33 .layout__region--second', 'LBB Test');
      $session->elementTextContains('css', '.layout--twocol-section--67-33 .layout__region--second', 'Mostly Blank Node');
      $session->elementTextContains('css', '.layout--onecol .layout__region--content', 'LBB Test');
      $session->elementTextNotContains('css', '.layout--onecol .layout__region--content', 'Mostly Blank Node');

      // Node 2 should be backwards.
      $this->drupalGet('/node/2');
      $session->elementTextNotContains('css', '.layout--twocol-section--67-33 .layout__region--first', 'LBB Test');
      $session->elementTextContains('css', '.layout--twocol-section--67-33 .layout__region--first', 'Node With Values');
      $session->elementTextContains('css', '.layout--twocol-section--67-33 .layout__region--second', 'LBB Test');
      $session->elementTextNotContains('css', '.layout--twocol-section--67-33 .layout__region--second', 'Node With Values');
      $session->elementTextNotContains('css', '.layout--onecol .layout__region--content', 'LBB Test');
      $session->elementTextContains('css', '.layout--onecol .layout__region--content', 'Node With Values');
    }

    // Try with a bogus field name to see if it is read as false.
    foreach ($tps['layout_builder']['sections'] as $section) {
      $settings = $section->getLayoutSettings();
      $settings['switch_field'] = 'totally_bogus_field';
      $section->setLayoutSettings($settings);
    }
    $display->set('third_party_settings', $tps)->save();
    $this->drupalGet('/node/1');
    $session->elementTextContains('css', '.layout--twocol-section--67-33 .layout__region--first', 'LBB Test');
    $session->elementTextNotContains('css', '.layout--twocol-section--67-33 .layout__region--first', 'Mostly Blank Node');
    $session->elementTextNotContains('css', '.layout--twocol-section--67-33 .layout__region--second', 'LBB Test');
    $session->elementTextContains('css', '.layout--twocol-section--67-33 .layout__region--second', 'Mostly Blank Node');
    $session->elementTextContains('css', '.layout--onecol .layout__region--content', 'LBB Test');
    $session->elementTextNotContains('css', '.layout--onecol .layout__region--content', 'Mostly Blank Node');
    $this->drupalGet('/node/2');
    $session->elementTextContains('css', '.layout--twocol-section--67-33 .layout__region--first', 'LBB Test');
    $session->elementTextNotContains('css', '.layout--twocol-section--67-33 .layout__region--first', 'Node With Values');
    $session->elementTextNotContains('css', '.layout--twocol-section--67-33 .layout__region--second', 'LBB Test');
    $session->elementTextContains('css', '.layout--twocol-section--67-33 .layout__region--second', 'Node With Values');
    $session->elementTextContains('css', '.layout--onecol .layout__region--content', 'LBB Test');
    $session->elementTextNotContains('css', '.layout--onecol .layout__region--content', 'Node With Values');
  }

}
