<?php

namespace Drupal\willdodone\Service;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\taxonomy\TermInterface;

class TermUserSetter {
  protected $currentUser;

  public function __construct(AccountProxyInterface $current_user) {
    $this->currentUser = $current_user;
  }

  public function setUserForTerm(TermInterface $term) {
    if ($term->hasField('field_user') && $term->get('field_user')->isEmpty()) {
      $term->set('field_user', $this->currentUser->id());
    }
  }
}
