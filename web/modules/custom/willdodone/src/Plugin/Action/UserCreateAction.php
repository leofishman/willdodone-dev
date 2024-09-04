<?php

namespace Drupal\willdodone\Plugin\Action;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Core\Entity\DependencyTrait;
use Drupal\eca\Plugin\Action\ConfigurableActionTrait;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupRole;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

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
    use DependencyTrait;
    use ConfigurableActionTrait;
    use StringTranslationTrait;
    public function __construct(array $configuration, $plugin_id, $plugin_definition, MessengerInterface $messenger, LoggerChannelFactoryInterface $logger_factory, EntityTypeManagerInterface $entity_type_manager) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->setMessenger($messenger);
        $this->loggerFactory = $logger_factory;
        $this->entityTypeManager = $entity_type_manager;
    }

    /**
     * {@inheritDoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('messenger'),
            $container->get('logger.factory'),
            $container->get('entity_type.manager')
        );
    }

  /**
   * {@inheritDoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $result = AccessResult::allowed();
    return $return_as_object ? $result : $result->isAllowed();
  }

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
                // Add the user to the group with the admin role.
                $group->save();
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
     * {@inheritdoc}
     */
    public function calculateDependencies() {
        if ($this->flag) {
            $this->addDependency('config', $this->flag->getConfigDependencyName());
        }
        return $this->dependencies;
    }

}