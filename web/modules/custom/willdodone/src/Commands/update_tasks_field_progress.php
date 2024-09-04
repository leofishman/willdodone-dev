<?php

//ddev drush php-script modules/custom/willdodone/src/Commands/update_tasks_field_progress.php


// Load Drupal bootstrap to access Drupal functionality.
//use Drupal\node\Entity\Node;
// Mapping of old project node IDs to new taxonomy term IDs
//$projectTermMappings = [
//  1 => 27,
//  9 => 28,
//];
$target_field = 'field_progress';
$defaultTermId = 29; // Default term for tasks without project reference or unmatched project ID
$entityTypeId = 'node'; // Entity type ID for your task nodes (adjust if different)

// Get all tasks with a reference to a project node
$query = \Drupal::entityQuery($entityTypeId)
    ->condition('type', 'task')
  ->accessCheck(FALSE); // Explicitly enable access checks
$taskIds = $query->execute();
\Drupal::logger('willdodone')->notice("Task {$taskIds[0]}");
echo "starting...";
// Load the tasks
$tasks = \Drupal::entityTypeManager()->getStorage($entityTypeId)->loadMultiple($taskIds);

foreach ($tasks as $task) {
  echo "Updating task " . $task->id();
  $field_progress = $task->$target_field->getValue()[0];
  if ($field_progress['status'] == 'completed') { $field_progress['status'] = 'done'; }
  if ($field_progress['status'] == 'new') { $field_progress['status'] = 'will'; }
  if ($field_progress['status'] == 'doing') { $field_progress['status'] = 'do'; }

 $task->field_custom_progress = $field_progress;
  // Save the task
  $task->save();

  // Optional: Log the update (replace with your preferred logging method)
  \Drupal::logger('willdodone')->notice("Task {$task->id()} updated: Project {$field_progress}");
}

// Done
echo "Task references successfully migrated to taxonomy terms.\n";
