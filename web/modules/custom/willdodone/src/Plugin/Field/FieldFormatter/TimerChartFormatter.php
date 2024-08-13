<?php

namespace Drupal\willdodone\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Datetime\DrupalDateTime;


/**
 * Plugin implementation of the 'timer_chart' formatter.
 *
 * @FieldFormatter(
 *   id = "timer_chart",
 *   label = @Translation("Timer Chart"),
 *   field_types = {
 *     "timer"
 *   }
 * )
 */
class TimerChartFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $data = [];
    foreach ($items as $delta => $item) {
      $start = new DrupalDateTime($item->start);
      $end = $item->end ? new DrupalDateTime($item->end) : new DrupalDateTime();

      $data[] = [
        'start' => $start->getTimestamp(),
        'end' => $end->getTimestamp(),
        'duration' => $end->getTimestamp() - $start->getTimestamp(),
      ];
    }

    $elements[0] = [
      '#theme' => 'timer_chart',
      '#data' => $data,
      '#attached' => [
        'library' => ['willdodone/timer-chart'],
      ],
    ];

    return $elements;
  }

}
