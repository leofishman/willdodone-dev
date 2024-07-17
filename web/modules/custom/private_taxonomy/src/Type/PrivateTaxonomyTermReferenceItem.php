<?php

namespace Drupal\private_taxonomy\Type;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the 'private_taxonomy_term_reference' entity field item.
 */
class PrivateTaxonomyTermReferenceItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $settings = $field_definition->getSettings();

    $target_id_definition = DataReferenceTargetDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('@label ID', ['@label' => $target_type_info->getLabel()]))
      ->setSetting('unsigned', TRUE);
    $target_id_definition->setRequired(TRUE);
    $properties['target_id'] = $target_id_definition;

    $properties['entity'] = DataReferenceDefinition::create('entity')
      ->setLabel($target_type_info->getLabel())
      ->setDescription(new TranslatableMarkup('The referenced entity'))
      // The entity object is computed out of the entity ID.
      ->setComputed(TRUE)
      ->setReadOnly(FALSE)
      ->setTargetDefinition(EntityDataDefinition::create($settings['target_type']))
      // We can add a constraint for the target entity type. The list of
      // referenceable bundles is a field setting, so the corresponding
      // constraint is added dynamically in ::getConstraints().
      ->addConstraint('EntityType', $settings['target_type']);

    return $properties;
  }

  /**
   * Overrides \Drupal\Core\Entity\Field\FieldItemBase::setValue().
   */
  public function setValue($values, $notify = TRUE) {
    // Treat the values as property value of the entity field, if no array
    // is given.
    if (!is_array($values)) {
      $values = ['entity' => $values];
    }

    // Entity is computed out of the ID, so we only need to update the ID. Only
    // set the entity field if no ID is given.
    if (isset($values['tid'])) {
      $this->properties['tid']->setValue($values['tid']);
    }
    elseif (isset($values['entity'])) {
      $this->properties['entity']->setValue($values['entity']);
    }
    else {
      $this->properties['entity']->setValue(NULL);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $target_type = $field_definition->getSetting('target_type');
    $target_type_info = \Drupal::entityTypeManager()->getDefinition($target_type);
    $properties = static::propertyDefinitions($field_definition)['target_id'];
    if ($target_type_info->entityClassImplements(FieldableEntityInterface::class) && $properties->getDataType() === 'integer') {
      $columns = [
        'target_id' => [
          'description' => 'The ID of the target entity.',
          'type' => 'int',
          'unsigned' => TRUE,
        ],
      ];
    }
    else {
      $columns = [
        'target_id' => [
          'description' => 'The ID of the target entity.',
          'type' => 'varchar_ascii',
          // If the target entities act as bundles for another entity type,
          // their IDs should not exceed the maximum length for bundles.
          'length' => $target_type_info->getBundleOf() ? EntityTypeInterface::BUNDLE_MAX_LENGTH : 255,
        ],
      ];
    }

    $schema = [
      'columns' => $columns,
      'indexes' => [
        'target_id' => ['target_id'],
      ],
    ];

    return $schema;
  }

}
