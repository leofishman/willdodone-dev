<?php

namespace Drupal\willdodone\Plugin\Action;

use Drupal\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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

    //TODO: Stop all other timers running and add end time on each

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

    }

    // Save the entity
    $entity->save();
  }
  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    if ($this->flag) {
      $this->addDependency('config', $this->flag->getConfigDependencyName());
    }
    return $this->dependencies;
  }

}
