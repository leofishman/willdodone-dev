<?php

namespace Drupal\willdodone\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'timer_status' formatter.
 *
 * @FieldFormatter(
 *   id = "timer_status",
 *   label = @Translation("Timer Status"),
 *   field_types = {
 *     "timer"
 *   }
 * )
 */
class TimerStatusFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'show_elapsed_time' => TRUE,
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['show_elapsed_time'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show elapsed time'),
      '#default_value' => $this->getSetting('show_elapsed_time'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Show elapsed time: @value', ['@value' => (bool) $this->getSetting('show_elapsed_time') ? $this->t('Yes') : $this->t('No')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $start_time = new DrupalDateTime($item->start);
      $elapsed_time = '';

      if ($item->status) {
        $now = new DrupalDateTime();
        $interval = $start_time->diff($now);
        $elapsed_time = $interval->format('%H:%I:%S');
      } elseif ($item->end) {
        $end_time = new DrupalDateTime($item->end);
        $interval = $start_time->diff($end_time);
        $elapsed_time = $interval->format('%H:%I:%S');
      }

      $elements[$delta] = [
        '#theme' => 'timer_status',
        '#status' => $item->status,
        '#start' => $item->start,
        '#end' => $item->end,
        '#show_elapsed_time' => $this->getSetting('show_elapsed_time'),
        '#elapsed_time' => $elapsed_time,
        '#attached' => [
          'library' => ['willdodone/timer'],
          'drupalSettings' => [
            'willdodone' => [
              'timerStatus' => [
                $delta => [
                  'startTime' => $start_time->getTimestamp() * 1000,
                  'toggleUrl' => Url::fromRoute('willdodone.toggle_timer', [
                    'entity_type' => $items->getEntity()->getEntityTypeId(),
                    'entity_id' => $items->getEntity()->id(),
                    'field_name' => $items->getFieldDefinition()->getName(),
                    'delta' => $delta,
                  ])->toString(),
                ],
              ],
            ],
          ],
        ],
      ];
    }


    return $elements;
  }

}
