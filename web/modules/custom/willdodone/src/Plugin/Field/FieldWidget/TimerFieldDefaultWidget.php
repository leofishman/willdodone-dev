<?php

namespace Drupal\willdodone\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Plugin implementation of the 'timerField_default' widget.
 *
 * @FieldWidget(
 *   id = "timerField_default",
 *   label = @Translation("Timer Field default"),
 *   field_types = {
 *     "timerField"
 *   },
 *   multiple_values = TRUE
 * )
 */
class TimerFieldDefaultWidget extends WidgetBase {

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

    $start_time = isset($items[$delta]) ? $items[$delta]->start_time : NULL;
    $end_time = isset($items[$delta]) ? $items[$delta]->end_time : NULL;
    $status = isset($items[$delta]) ? $items[$delta]->status : NULL;

    $element['details']['start_time'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Start time'),
      '#default_value' => $start_time ? DrupalDateTime::createFromFormat(DateTimeItemInterface::DATETIME_STORAGE_FORMAT, $start_time) : NULL,
    ];

    $element['details']['end_time'] = [
      '#type' => 'datetime',
      '#title' => $this->t('End time'),
      '#default_value' => $end_time ? DrupalDateTime::createFromFormat(DateTimeItemInterface::DATETIME_STORAGE_FORMAT, $end_time) : NULL,
    ];

    $element['details']['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Running'),
      '#default_value' => $status,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $new_values = [];

    foreach ($values['details'] as $delta => $item) {
//      $new_values[$delta]['start_time'] = $item['start_time'] ? $item['start_time']->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT) : NULL;
//      $new_values[$delta]['end_time'] = $item['end_time'] ? $item['end_time']->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT) : NULL;
      $new_values[$delta]['status'] = $item['status'];
    }

    return $new_values;
  }
}
