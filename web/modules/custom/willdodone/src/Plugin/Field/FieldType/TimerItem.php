<?php

namespace Drupal\willdodone\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Defines the 'timer' field type.
 *
 * @FieldType(
 *   id = "timer",
 *   label = @Translation("Simple Timer"),
 *   description = @Translation("A field to store timer data."),
 *   default_widget = "timer_widget",
 *   default_formatter = "timer_formatter"
 * )
 */
class TimerItem extends FieldItemBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['status'] = DataDefinition::create('boolean')
      ->setLabel(t('Status'));
    //    $properties['start_time'] = DataDefinition::create('datetime_iso8601')
    //      ->setLabel(t('Start Time'));
    //    $properties['end_time'] = DataDefinition::create('datetime_iso8601')
    //      ->setLabel(t('End Time'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'status' => [
          'type' => 'boolean',
        ],
        //        'start_time' => [
        //          'type' => 'datetime',
        //          'length' => 20,
        //        ],
        //        'end_time' => [
        //          'type' => 'datetime',
        //          'length' => 20,
        //        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $start_time = 1;//$this->get('start_time')->getValue();
    $end_time = 1;//$this->get('end_time')->getValue();
    $status = $this->get('status')->getValue();
    return  empty($status);//empty($start_time) && empty($end_time) &&
  }
  //    $status = $this->get('status')->getValue();
  //    $start_time = $this->get('start_time')->getValue();
  //    $end_time = $this->get('end_time')->getValue();
  //    return empty($status) && empty($start_time) && empty($end_time);
  //  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $values['status'] = 1;
    return $values;
  }

}
