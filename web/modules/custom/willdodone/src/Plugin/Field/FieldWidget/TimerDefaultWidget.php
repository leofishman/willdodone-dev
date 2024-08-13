<?php

namespace Drupal\willdodone\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Plugin implementation of the 'timer_default' widget.
 *
 * @FieldWidget(
 *   id = "timer_default",
 *   label = @Translation("Timer default"),
 *   field_types = {
 *     "timer"
 *   },
 *   multiple_values = TRUE
 * )
 */
class TimerDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Ensure field storage configuration exists.
    $field_definition = $this->fieldDefinition;
    $field_name = $field_definition->getName();
    $entity_type = $field_definition->getTargetEntityTypeId();

    $field_storage = \Drupal::entityTypeManager()->getStorage('field_storage_config');
    $field_storage_exists = $field_storage->load($entity_type . '.' . $field_name);

    if (!$field_storage_exists) {
      $field_storage_config = [
        'field_name' => $field_name,
        'entity_type' => $entity_type,
        'type' => 'datetime',
        'settings' => [],
        'module' => 'datetime',
        'locked' => FALSE,
        'cardinality' => 1,
        'translatable' => TRUE,
        'indexes' => [],
        'persist_with_no_fields' => FALSE,
        'custom_storage' => FALSE,
      ];
      $field_storage->create($field_storage_config)->save();
    }

    $element['#type'] = 'details';
    $element['#title'] = $this->t('Timer entries');
    $element['#open'] = TRUE;

    $element['details'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Start time'),
        $this->t('End time'),
        $this->t('Status'),
        $this->t('Operations'),
      ],
      '#empty' => $this->t('No time entries recorded.'),
    ];

     $element['details']['start'] = [
       '#type' => 'datetime',
       '#title' => $this->t('Start time'),
  //        '#title_display' => 'invisible',
       '#default_value' => $items->start ? DrupalDateTime::createFromFormat(DateTimeItemInterface::DATETIME_STORAGE_FORMAT, $items->start) : NULL,
     ];

     $element['details']['end'] = [
       '#type' => 'datetime',
       '#title' => $this->t('End time'),
       '#title_display' => 'invisible',
       '#default_value' => $items->end ? DrupalDateTime::createFromFormat(DateTimeItemInterface::DATETIME_STORAGE_FORMAT, $items->end) : NULL,
     ];

     $element['details']['status'] = [
       '#type' => 'checkbox',
       '#title' => $this->t('Running'),
  //        '#title_display' => 'invisible',
       '#default_value' => $items->status,
     ];
//    foreach ($items as $item_delta => $item) {
//      $default_value = isset($items[$delta]->value) ? DrupalDateTime::createFromTimestamp($items[$delta]->value) : '';
//
//      $element['details'][$item_delta]['start'] = [
//        '#type' => 'datetime',
//        '#title' => $this->t('Start time'),
////        '#title_display' => 'invisible',
//        '#default_value' => $item->start ? DrupalDateTime::createFromFormat(DateTimeItemInterface::DATETIME_STORAGE_FORMAT, $item->start) : NULL,
//      ];
//
//      $element['details'][$item_delta]['end'] = [
//        '#type' => 'datetime',
//        '#title' => $this->t('End time'),
//        '#title_display' => 'invisible',
//        '#default_value' => $item->end ? DrupalDateTime::createFromFormat(DateTimeItemInterface::DATETIME_STORAGE_FORMAT, $item->end) : NULL,
//      ];
//
//      $element['details'][$item_delta]['status'] = [
//        '#type' => 'checkbox',
//        '#title' => $this->t('Running'),
////        '#title_display' => 'invisible',
//        '#default_value' => $item->status,
//      ];
//
////      $element['details'][$item_delta]['remove'] = [
////        '#type' => 'submit',
////        '#value' => $this->t('Remove'),
////        '#submit' => [[$this, 'removeItem']],
////        '#ajax' => [
////          'callback' => [$this, 'removeItemAjax'],
////          'wrapper' => 'timer-entries-wrapper',
////        ],
////        '#name' => 'remove_' . $delta . '_' . $item_delta,
////      ];
//    }

//    $element['add_more'] = [
//      '#type' => 'submit',
//      '#value' => $this->t('Add another item'),
//      '#submit' => [[$this, 'addMoreSubmit']],
//      '#ajax' => [
//        'callback' => [$this, 'addMoreAjax'],
//        'wrapper' => 'timer-entries-wrapper',
//      ],
//    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $new_values = [];

    foreach ($values['details'] as $delta => $item) {
      if ($item['start']) {
        $new_values[$delta]['start'] = $item['start'] ? $item['start']->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT) : NULL;
        $new_values[$delta]['end'] = $item['end'] ? $item['end']->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT) : NULL;
        $new_values[$delta]['status'] = $item['status'];
      }
    }
    return $new_values;
  }
}
