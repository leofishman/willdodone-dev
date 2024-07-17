<?php

namespace Drupal\willdodone\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines the 'timer_widget' field widget.
 *
 * @FieldWidget(
 *   id = "timer_widget",
 *   label = @Translation("Timer widget"),
 *   field_types = {
 *     "timer"
 *   }
 * )
 */
class TimerWidget extends WidgetBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
//  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
//    $element['status'] = [
//      '#type' => 'checkbox',
//      '#title' => $this->t('Running'),
//      '#default_value' => isset($items[$delta]->status) ? $items[$delta]->status : FALSE,
//    ];
//
//    $element['start_time'] = [
//      '#type' => 'datetime',
//      '#title' => $this->t('Start Time'),
//      '#default_value' => isset($items[$delta]->start_time) ? $items[$delta]->start_time : NULL,
//    ];
//
//    $element['end_time'] = [
//      '#type' => 'datetime',
//      '#title' => $this->t('End Time'),
//      '#default_value' => isset($items[$delta]->end_time) ? $items[$delta]->end_time : NULL,
//    ];
//
//    return $element;
//  }
//
//

  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['details'] = [
        '#type' => 'details',
        '#title' => $element['#title'],
        '#open' => $this->getSetting('fieldset_state') == 'open' ? TRUE : FALSE,
        '#description' => $element['#description'],
      ] + $element;


    $element['details']['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Running'),
      '#default_value' => isset($items[$delta]->status) ? $items[$delta]->status : 0,
      '#description' => 'Active',
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $new_values = [];

    foreach ($values as $delta => $item ) {
      //      $new_values[$delta]['start_time'] = $item['start_time'] ? $item['start_time']->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT) : NULL;
      //      $new_values[$delta]['end_time'] = $item['end_time'] ? $item['end_time']->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT) : NULL;
      $new_values[$delta]['status'] = $item['details']['status'];
    }

    return $new_values;
  }
}
