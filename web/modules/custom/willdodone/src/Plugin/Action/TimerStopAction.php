<?php

namespace Drupal\willdodone\Plugin\Action;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\eca\Plugin\Action\ActionBase;
use Drupal\eca\Plugin\Action\ConfigurableActionTrait;
use Drupal\eca\Plugin\ECA\PluginFormTrait;
use Drupal\eca\TypedData\PropertyPathTrait;
use Drupal\eca_content\Plugin\EntitySaveTrait;
/**
 * Provides a custom action for timer stop functionality.
 *
 * @Action(
 *   id = "wdd_timer_stop_action",
 *   label = @Translation("Timer Stop Action"),
 *   description = @Translation("Add record for time used upan stoping."),
 *   type = "entity"
 * )
 */
class TimerStopAction extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute( $entity = NULL) {
    // Flag is being unflagged (timer stop)
    // Get the current date and time.
    $current_datetime = new DrupalDateTime('now');
    $formatted_datetime = $current_datetime->format('Y-m-d\TH:i:s');

    // Timer add end time to time used field
    if ($entity->hasField('field_time_used'))  {
      // Find the most recently added value in field_time_used
      $field_values = $entity->get('field_time_used')->getValue();
      $last_item_index = count($field_values) - 1;

      // Update its end value with the current date/time
      $field_values[$last_item_index]['end_value'] = $formatted_datetime;
      $entity->set('field_time_used', $field_values);
    }

    // TODO change swinline status accordly

    ///**** DEPRECATED */
    // Stop timer put the status in paused
    if ($entity->hasField('field_custom_progress'))  {
      $entity_field_progress = $entity->get('field_custom_progress')->getValue();
      if ($entity_field_progress[0]['status'] == 'do') {
          $entity_field_progress[0]['status'] =  'paused';
          $entity->set('field_custom_progress', $entity_field_progress);
      }
    }

//    try {
//      // Get the flag object from the event (assuming $this->event->getFlagging() returns the flag object)
//      $flag = $this->event->getFlaggings();
//
//      // Get the current values of the field_time field
//       $flag_id = key($this->event->getFlaggings());
//
//      $field_time = $flag[$flag_id]->get('field_time');
//
//        $last_item_index = count($field_time) - 1;
//
//        // Update its end value with the current date/time
//        $field_time[$last_item_index]['end_value'] = $formatted_datetime;
//      // Set the updated values back to the field
////         $flag[$flag_id]->set('field_time', $field_time);
//
//      // Save the flag
//      $flag[$flag_id]->save();
//    } catch (\Exception $e) {}




      // Save the entity
    $entity->save();
  }

}
