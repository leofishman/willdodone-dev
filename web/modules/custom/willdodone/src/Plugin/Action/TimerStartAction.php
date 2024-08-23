<?php

namespace Drupal\willdodone\Plugin\Action;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\eca\Plugin\Action\ActionBase;
use Drupal\eca\Plugin\Action\ConfigurableActionTrait;
use Drupal\eca\Plugin\ECA\PluginFormTrait;
use Drupal\eca\TypedData\PropertyPathTrait;
use Drupal\eca_content\Plugin\EntitySaveTrait;
/**
 * Provides a custom action for timer functionality.
 *
 * @Action(
 *   id = "wdd_timer_start_action",
 *   label = @Translation("Timer Start Action"),
 *   description = @Translation("Add record for time used upan starting."),
 *   type = "entity"
 * )
 */
class TimerStartAction extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute( $entity = NULL) {
    // Flag is being flagged (timer start)
    // Get the current date and time.
    $current_datetime = new DrupalDateTime('now');
    $formatted_datetime = $current_datetime->format('Y-m-d\TH:i:s');

    if ($entity->bundle() === 'task' && $entity->hasField('field_time_used'))  {
      // Add a new value to field_time_used with the current date/time as the start value
      $new_value = [
        'value' => $formatted_datetime,
        'end_value' => NULL,
      ];
      $entity->get('field_time_used')->appendItem($new_value);

//    } else {
//      // Flag is being unflagged (timer stop)
//
//      // Find the most recently added value in field_time_used
//      $field_values = $entity->get('field_time_used')->getValue();
//      $last_item_index = count($field_values) - 1;
//
//      // Update its end value with the current date/time
//      $field_values[$last_item_index]['end_value'] = $formatted_datetime;
//      $entity->set('field_time_used', $field_values);

    }

    // Save the entity
    $entity->save();
  }

}
