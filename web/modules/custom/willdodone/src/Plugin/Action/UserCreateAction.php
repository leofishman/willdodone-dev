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
use Drupal\group\Entity\Group;

/**
 * Provides a custom action for timer functionality.
 *
 * @Action(
 *   id = "wdd_user_create_action",
 *   label = @Translation("User Create Action"),
 *   description = @Translation("Add space group to the new user."),
 *   type = "entity"
 * )
 */
class UserCreateAction extends ActionBase {
/**
 * Check if a group with the given name exists.
 *
 * @param string $groupName
 *   The name of the group to check.
 *
 * @return bool
 *   TRUE if the group exists, FALSE otherwise.
 */
    function group_exists($groupName) {
      $query = \Drupal::entityQuery('group')
        ->condition('label', $groupName)
        ->range(0, 1)
        ->accessCheck(TRUE)
        ->execute();

      return !empty($query);
    }
  /**
   * {@inheritdoc}
   */
  public function execute( $entity = NULL) {

    if ($entity->getEntityTypeId() == 'user') {
        $userName = $entity->getDisplayName();
        $groupName = $userName . ' space';

        if (!$this->group_exists($groupName)) {
            // Create a new group of type 'space'.
            $group = Group::create([
                'type' => 'space',
                'label' => $groupName,
            ]);
            $group->save();

            // Add the user to the group.
            $group->addMember($entity,['group_roles' => 'space-admin']);
            // create default roles for space and project
            // add member as admin

        }
        // add project as user space content group

    }
    $entity_type = $entity->getEntityTypeId();

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
