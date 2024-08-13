<?php

namespace Drupal\willdodone\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Datetime\DrupalDateTime;


/**
 * Plugin implementation of the 'timer_table' formatter.
 *
 * @FieldFormatter(
 *   id = "timer_table",
 *   label = @Translation("Timer Table"),
 *   field_types = {
 *     "timer"
 *   }
 * )
 */
class TimerTableFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $rows = [];
    foreach ($items as $delta => $item) {
      $start = new DrupalDateTime($item->start);
      $end = $item->end ? new DrupalDateTime($item->end) : NULL;

      $rows[] = [
        $start->format('Y-m-d H:i:s'),
        $end ? $end->format('Y-m-d H:i:s') : $this->t('Running'),
        $end ? $end->diff($start)->format('%H:%I:%S') : $this->t('N/A'),
      ];
    }

    $elements[0] = [
      '#theme' => 'table',
      '#header' => [$this->t('Start'), $this->t('End'), $this->t('Duration')],
      '#rows' => $rows,
      '#empty' => $this->t('No time entries recorded.'),
    ];

    return $elements;
  }

}
