<?php

namespace Drupal\willdodone\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Service for handling timer operations.
 */
class TimerService {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new TimerService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountProxyInterface $current_user) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * Starts a timer for a given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to start the timer for.
   * @param string $field_name
   *   The name of the timer field.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function startTimer($entity, $field_name) {
    $this->stopRunningTimers($entity, $field_name);

    $timer_field = $entity->get($field_name);
    $timer_field->add([
      'start' => (new DrupalDateTime())->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'status' => TRUE,
    ]);
    $entity->save();
  }

  /**
   * Stops a timer for a given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to stop the timer for.
   * @param string $field_name
   *   The name of the timer field.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function stopTimer($entity, $field_name) {
    $timer_field = $entity->get($field_name);
    $value = unserialize($timer_field->value);
    $last_entry = end($value);
    if (isset($last_entry['start']) && !isset($last_entry['end'])) {
      $last_entry['end'] = time();
      $value[key($value)] = $last_entry;
    }
    $timer_field->value = serialize($value);
    $timer_field->status = FALSE;
    $entity->save();
  }

  /**
   * Stops all running timers for a given project and user.
   *
   * @param \Drupal\Core\Entity\EntityInterface $project
   *   The project entity.
   * @param string $field_name
   *   The name of the timer field.
   */
  protected function stopRunningTimers($project, $field_name) {
    $task_storage = $this->entityTypeManager->getStorage('node');
    $query = $task_storage->getQuery()
      ->condition('type', 'task')
      ->condition('field_project', $project->id())
      ->condition($field_name . '.status', TRUE)
      ->condition('uid', $this->currentUser->id());
    $task_ids = $query->execute();

    foreach ($task_ids as $task_id) {
      $task = $task_storage->load($task_id);
      $this->stopTimer($task, $field_name);
    }
  }
}
