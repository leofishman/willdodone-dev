<?php
//////////////////////////////
/// DEPRECATED. NOT IN USE
namespace Drupal\willdodone\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'timerField' field type.
 *
 * @FieldType(
 *   id = "timerField",
 *   label = @Translation("Timer Field"),
 *   description = @Translation("Stores a start time, end time, and status."),
 *   default_widget = "timerField_default",
 *   default_formatter = "timerField_default"
 * )
 */
class TimerFieldItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['start_time'] = DataDefinition::create('datetime_iso8601')
      ->setLabel(t('Start time'));

    $properties['end_time'] = DataDefinition::create('datetime_iso8601')
      ->setLabel(t('End time'));

    $properties['status'] = DataDefinition::create('boolean')
      ->setLabel(t('Status'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'start_time' => [
          'type' => 'datetime',
          'mysql_type' => 'datetime',
        ],
        'end_time' => [
          'type' => 'datetime',
          'mysql_type' => 'datetime',
        ],
        'status' => [
          'type' => 'boolean',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $start_time = $this->get('start_time')->getValue();
    $end_time = $this->get('end_time')->getValue();
    $status = $this->get('status')->getValue();
    return empty($start_time) && empty($end_time) && empty($status);
  }
}
