<?php

// File: migrate_project_references.php

// Mapping of old project node IDs to new taxonomy term IDs
$projectTermMappings = [
  1 => 27,
  9 => 28,
];
$defaultTermId = 29; // Default term for tasks without project reference or unmatched project ID
$entityTypeId = 'node'; // Entity type ID for your task nodes (adjust if different)

// Get all tasks with a reference to a project node
$query = \Drupal::entityQuery($entityTypeId)
  ->exists('field_project')
  ->accessCheck(TRUE); // Explicitly enable access checks
$taskIds = $query->execute();
\Drupal::logger('willdodone')->notice("Task {$taskIds[0]}");
echo "starting...";
// Load the tasks
$tasks = \Drupal::entityTypeManager()->getStorage($entityTypeId)->loadMultiple($taskIds);

foreach ($tasks as $task) {
  echo "Migrating task " . $task->id();
  $oldProjectId = $task->field_project->target_id; // Assuming a single-value reference field
  $newTermId = isset($projectTermMappings[$oldProjectId]) ? $projectTermMappings[$oldProjectId] : $defaultTermId;

  // Update the taxonomy term reference field (assuming field name is field_taxonomy_project)
  $task->field_taxonomy_project = $newTermId;

  // Save the task
  $task->save();

  // Optional: Log the update (replace with your preferred logging method)
  \Drupal::logger('willdodone')->notice("Task {$task->id()} updated: Project {$oldProjectId} -> Term {$newTermId}");
}

// Done
echo "Task references successfully migrated to taxonomy terms.\n";
