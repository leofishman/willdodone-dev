<?php

namespace Drupal\private_taxonomy\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Permit access to admin/structure/taxonomy. The list of terms displayed
    // is handled by private_taxonomy_taxonomy_vocabulary_access().
    if ($route = $collection->get('entity.taxonomy_vocabulary.collection')) {
      $permissions = $route->getRequirement('_permission');
      if ($permissions !== NULL) {
        // We need a '+' prefix if other permissions already exist.
        $permissions .= "+";
      }
      $permissions .= "administer own taxonomy";
      $route->setRequirement('_permission', $permissions);
    }
  }

}
