<?php
namespace Drupal\willdodone\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TimerController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new TimerController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Toggles the timer status.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $entity_id
   *   The entity ID.
   * @param string $field_name
   *   The field name.
   * @param int $delta
   *   The field delta.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function toggleTimer($entity_type, $entity_id, $field_name, $delta) {
    $entity = $this->entityTypeManager->getStorage($entity_type)->load($entity_id);
    $timer_field = $entity->get($field_name);
    $timer_item = $timer_field->get($delta);

    $new_status = !$timer_item->status;
    $timer_item->status = $new_status;

    if ($new_status) {
      $timer_item->start = date('Y-m-d\TH:i:s');
      $timer_item->end = NULL;
    } else {
      $timer_item->end = date('Y-m-d\TH:i:s');
    }

    $entity->save();

    return new JsonResponse([
      'status' => $new_status ? 'running' : 'stopped',
    ]);
  }

}
