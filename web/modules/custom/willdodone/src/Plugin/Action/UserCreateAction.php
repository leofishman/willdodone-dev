<?php

namespace Drupal\willdodone\Plugin\Action;

//use Drupal\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\eca\Plugin\Action\ActionBase;
use Drupal\eca\Plugin\Action\ConfigurableActionTrait;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupRole;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
class UserCreateAction extends ActionBase implements ContainerFactoryPluginInterface, DependentPluginInterface {

    use ConfigurableActionTrait;
    use StringTranslationTrait;

    /**
     * The entity type manager.
     *
     * @var \Drupal\Core\Entity\EntityTypeManagerInterface
     */
    protected EntityTypeManagerInterface $entityTypeManager;

    /**
     * The logger factory.
     *
     * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
     */
    protected $loggerFactory;

    /**
     * Constructs a new UserCreateAction object.
     *
     * @param array $configuration
     *   A configuration array containing information about the plugin instance.
     * @param string $plugin_id
     *   The plugin_id for the plugin instance.
     * @param mixed $plugin_definition
     *   The plugin implementation definition.
     * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
     *   The entity type manager.
     * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
     *   The logger factory.
     */
    public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger_factory) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->entityTypeManager = $entity_type_manager;
        $this->loggerFactory = $logger_factory;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static
    {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('entity_type.manager'),
            $container->get('logger.factory')
        );
    }

    /**
     * Check if a group with the given name exists.
     *
     * @param string $groupName
     *   The name of the group to check.
     *
     * @return bool
     *   TRUE if the group exists, FALSE otherwise.
     */
    protected function groupExists($groupName) {
        $query = $this->entityTypeManager->getStorage('group')->getQuery()
            ->condition('label', $groupName)
            ->range(0, 1)
            ->accessCheck(TRUE)
            ->execute();
        return !empty($query);
    }

    /**
     * {@inheritdoc}
     */
    public function execute($entity = NULL) {
        if ($entity->getEntityTypeId() !== 'user') {
            $this->loggerFactory->get('willdodone')->warning('Entity is not a user.');
            return;
        }

        $userName = $entity->getDisplayName();
        $groupName = $userName . ' space';

        if (!$this->groupExists($groupName)) {
            try {
                // Create a new group of type 'space'.
                $group = Group::create([
                    'type' => 'space',
                    'label' => $groupName,
                ]);
                $group->save();

                // Create default roles for the space group.
                $this->createDefaultRoles($group);

                // Add the user to the group with the admin role.
                $group->addMember($entity, ['group_roles' => ['space-admin']]);

                $this->loggerFactory->get('willdodone')->info('Group "%group_name" created and user added.', ['%group_name' => $groupName]);
            }
            catch (\Exception $e) {
                $this->loggerFactory->get('willdodone')->error('Failed to create group or add user: %error', ['%error' => $e->getMessage()]);
            }
        }
        else {
            $this->loggerFactory->get('willdodone')->warning('Group "%group_name" already exists.', ['%group_name' => $groupName]);
        }
    }

    /**
     * Create default roles for the group.
     *
     * @param \Drupal\group\Entity\Group $group
     *   The group entity.
     */
    protected function createDefaultRoles(Group $group) {
        $roles = [
            'space-admin' => $this->t('Space Admin'),
            'space-member' => $this->t('Space Member'),
        ];

        foreach ($roles as $role_id => $label) {
            if (!$group->getGroupType()->hasRole($role_id)) {
                $group_role = GroupRole::create([
                    'id' => $role_id,
                    'label' => $label,
                    'group_type' => $group->getGroupType()->id(),
                ]);
                $group_role->save();
            }
        }
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