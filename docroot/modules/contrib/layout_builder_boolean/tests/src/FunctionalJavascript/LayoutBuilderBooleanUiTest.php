<?php

namespace Drupal\Tests\layout_builder_boolean\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\layout_builder\FunctionalJavascript\LayoutBuilderSortTrait;

/**
 * Tests the Layout Builder UI with Layout Builder Boolean sections.
 *
 * This is very closely modeled on LayoutBuilderTest.php from layout_builder.
 * This tests that the UI correctly updates configuration.
 *
 * @group layout_builder_boolean
 */
class LayoutBuilderBooleanUiTest extends WebDriverTestBase {

  use LayoutBuilderSortTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'layout_builder_boolean_tests',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalLogin($this->drupalCreateUser([
      'access contextual links',
      'configure any layout',
      'administer node display',
    ], 'foobar'));
  }

  /**
   * Tests the Layout Builder UI.
   */
  public function testLayoutBuilderUi() {
    $layout_url = 'admin/structure/types/manage/lbb_test/display/default/layout';
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    // Go to manage layout.
    $this->drupalGet($layout_url);
    // Turn off preview.
    $page->uncheckField('layout-builder-content-preview');

    // Two-column: Check that blocks start out in the right place.
    $assert_session->elementTextContains('css', '[data-region="true:first"]', 'Title');
    $assert_session->elementTextNotContains('css', '[data-region="true:first"]', 'Content type');
    $assert_session->elementTextContains('css', '[data-region="true:second"]', 'Content type');
    $assert_session->elementTextNotContains('css', '[data-region="true:second"]', 'Title');
    $assert_session->elementTextContains('css', '[data-region="false:first"]', 'Content type');
    $assert_session->elementTextNotContains('css', '[data-region="false:first"]', 'Title');
    $assert_session->elementTextContains('css', '[data-region="false:second"]', 'Title');
    $assert_session->elementTextNotContains('css', '[data-region="false:second"]', 'Content type');

    // One-column: Check that blocks start out in the right place.
    $assert_session->elementTextContains('css', '[data-region="true:content"]', 'Title');
    $assert_session->elementTextNotContains('css', '[data-region="true:content"]', 'Content type');
    $assert_session->elementTextNotContains('css', '[data-region="false:content"]', 'Title');
    $assert_session->elementTextContains('css', '[data-region="false:content"]', 'Content type');

    // Two-column: Drag the title block from true:first to false:first.
    $this->sortableTo('[data-layout-content-preview-placeholder-label="\"Title\" field"]', '[data-region="true:first"]', '[data-region="false:first"]');
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->elementTextContains('css', '[data-region="false:first"]', 'Title');
    $assert_session->elementTextNotContains('css', '[data-region="true:first"]', 'Title');

    // Save and make sure the config sticks.
    $page->pressButton('Save layout');
    $this->drupalGet($layout_url);

    // Two-column: Check that blocks start out in the new right place.
    $assert_session->elementTextNotContains('css', '[data-region="true:first"]', 'Title');
    $assert_session->elementTextNotContains('css', '[data-region="true:first"]', 'Content type');
    $assert_session->elementTextContains('css', '[data-region="true:second"]', 'Content type');
    $assert_session->elementTextNotContains('css', '[data-region="true:second"]', 'Title');
    $assert_session->elementTextContains('css', '[data-region="false:first"]', 'Content type');
    $assert_session->elementTextContains('css', '[data-region="false:first"]', 'Title');
    $assert_session->elementTextContains('css', '[data-region="false:second"]', 'Title');
    $assert_session->elementTextNotContains('css', '[data-region="false:second"]', 'Content type');

    // Check that the configuration form works.
    $assert_session->linkExists('Configure TwoCol');
    $this->clickLink('Configure TwoCol');
    $this->assertOffCanvasFormAfterWait('layout_builder_configure_section');
    $page->fillField('layout_settings[column_widths]', '25-75');
    $page->fillField('layout_settings[switch_field]', 'status');
    $page->pressButton('Update');
    $assert_session->assertNoElementAfterWait('css', '#drupal-off-canvas');
    // Check that new switch field looks good.
    $assert_session->pageTextContains('Section conditioned on: status');
    // Check the column widths changed.
    $assert_session->elementExists('css', '.layout--twocol-section--25-75');
    $assert_session->elementNotExists('css', '.layout--twocol-section--67-33');

    // Save and confirm configuration updated correctly.
    $page->pressButton('Save layout');
    $display = \Drupal::service('entity_display.repository')->getViewDisplay('node', 'lbb_test', 'default');
    $tps = $display->get('third_party_settings');
    $section = $tps['layout_builder']['sections'][0];
    $settings = $section->getLayoutSettings();
    $this->assertSame('status', $settings['switch_field']);
    $this->assertSame('25-75', $settings['column_widths']);
  }

  /**
   * Waits for the specified form and returns it when available and visible.
   *
   * Taken directly from layout_builder.
   *
   * @param string $expected_form_id
   *   The expected form ID.
   */
  private function assertOffCanvasFormAfterWait($expected_form_id) {
    $this->assertSession()->assertWaitOnAjaxRequest();
    $off_canvas = $this->assertSession()->waitForElementVisible('css', '#drupal-off-canvas');
    $this->assertNotNull($off_canvas);
    $form_id_element = $off_canvas->find('hidden_field_selector', [
      'hidden_field',
      'form_id',
    ]);
    // Ensure the form ID has the correct value and that the form is visible.
    $this->assertNotEmpty($form_id_element);
    $this->assertSame($expected_form_id, $form_id_element->getValue());
    $this->assertTrue($form_id_element->getParent()->isVisible());
  }

}
