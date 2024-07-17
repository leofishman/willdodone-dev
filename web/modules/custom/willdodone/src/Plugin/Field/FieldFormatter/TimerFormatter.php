<?php

namespace Drupal\willdodone\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'timer_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "timer_formatter",
 *   label = @Translation("Timer formatter"),
 *   field_types = {
 *     "timer"
 *   }
 * )
 */
class TimerFormatter extends FormatterBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#theme' => 'timer_formatter',
        '#status' => $item->status, //? $this->t('Running') : $this->t('Stopped'),
//        '#start_time' => $item->start_time ? $item->start_time->format('Y-m-d H:i:s') : '',
//        '#end_time' => $item->end_time ? $item->end_time->format('Y-m-d H:i:s') : '',
      ];
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return array
   *   The value render array.
   */
  protected function viewValue(FieldItemInterface $item) {

    return [
      '#theme' => 'timer_formatter',
      '#status' => $item->status,
    ];
  }

}
