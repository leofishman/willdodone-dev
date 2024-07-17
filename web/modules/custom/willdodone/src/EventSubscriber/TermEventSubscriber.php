<?php

namespace Drupal\willdodone\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\Event;

class TermEventSubscriber implements EventSubscriberInterface {
  protected $termUserSetter;

  public function __construct(\Drupal\willdodone\Service\TermUserSetter $term_user_setter) {
    $this->termUserSetter = $term_user_setter;
  }

  public static function getSubscribedEvents() {
    return [
      'entity.update' => 'onEntityUpdate',
      'entity.insert' => 'onEntityCreate',
    ];
  }

  public function onEntityCreate(Event $event) {
    dump($event);
    $this->setUserForTerm($event->getEntity());
  }

  public function onEntityUpdate(Event $event) {
    dump($event);die;
    $this->setUserForTerm($event->getEntity());
  }

  protected function setUserForTerm($entity) {
    if ($entity instanceof \Drupal\taxonomy\TermInterface) {
      $this->termUserSetter->setUserForTerm($entity);
    }
  }
}
