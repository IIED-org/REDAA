<?php

namespace Drupal\tamper\Plugin\Tamper;

use Drupal\tamper\Exception\TamperException;
use Drupal\tamper\TamperBase;
use Drupal\tamper\TamperableItemInterface;

/**
 * Plugin implementation for html entity decode.
 *
 * @Tamper(
 *   id = "html_entity_decode",
 *   label = @Translation("HTML entity decode"),
 *   description = @Translation("Convert all HTML entities such as &amp;amp; and &amp;quot; to &amp; and &quot;."),
 *   category = "Text"
 * )
 */
class HtmlEntityDecode extends TamperBase {

  /**
   * {@inheritdoc}
   */
  public function tamper($data, ?TamperableItemInterface $item = NULL) {
    // Don't process empty or null values.
    if (is_null($data) || $data === '') {
      return $data;
    }

    if (!is_string($data)) {
      throw new TamperException('Input should be a string.');
    }

    return html_entity_decode($data, ENT_QUOTES, 'UTF-8');
  }

}
