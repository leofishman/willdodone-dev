<?php

namespace Drupal\Tests\private_taxonomy\Functional;

/**
 * Test Private Taxonomy functionality.
 *
 * @group private_taxonomy
 */
class PrivateTaxonomyViewsTest extends PrivateTaxonomyTestBase {

  use \Drupal\Core\StringTranslation\StringTranslationTrait;

  /**
   * Config schema.
   *
   * @var bool
   */
  protected $strictConfigSchema = FALSE;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['private_taxonomy', 'views_ui'];

  /**
   * Tests for Views integration.
   */
  public function testPrivateTaxonomyViewsOwner() {
    $admin_user = $this->drupalCreateUser([
      'access content',
      'administer taxonomy',
      'administer views',
    ]);
    $user = $this->drupalCreateUser([
      'access content',
      'administer own taxonomy',
      'view private taxonomies',
    ]);
    $this->drupalLogin($admin_user);

    // Create a private vocabulary.
    $private = TRUE;
    $private_vocabulary = $this->createVocabulary($private);

    // Add terms to vocabularies.
    $this->drupalLogin($user);
    $private_term = $this->createTerm($private_vocabulary);
    $this->drupalLogin($admin_user);
    $admin_term = $this->createTerm($private_vocabulary);

    $this->drupalGet('admin/structure/views');
    $this->drupalGet('admin/structure/views/add');
    $edit = [
      'label' => 'Private',
      'id' => 'private',
      'show[wizard_key]' => 'taxonomy_term',
      'show[sort]' => 'none',
      'page[create]' => TRUE,
      'page[title]' => 'Private',
      'page[path]' => 'private-view',
    ];
    $this->drupalPostForm('admin/structure/views/add', $edit,
      $this->t('Save and edit'));
    $this->drupalGet('private-view');
    // All terms should be visible.
    $this->assertText($admin_term->getName(), $this->t('Admin term visisble'));
    $this->assertText($private_term->getName(), $this->t('User term visisble'));

    // Add current user filter.
    $this->drupalGet('admin/structure/views/view/private');
    $url = 'admin/structure/views/nojs/add-handler/private/page_1/filter';
    $edit = [
      'name[user_term.uid]' => TRUE,
    ];
    $this->drupalPostForm($url, $edit, $this->t('Add and configure filter criteria'));
    $url = 'admin/structure/views/nojs/handler/private/page_1/filter/uid';
    $edit = [
      'options[value]' => TRUE,
    ];
    $this->drupalPostForm($url, $edit, $this->t('Apply'));
    $this->assertText('Private Taxonomy term: Current user (= True)',
      $this->t('Filter set'));
    $url = 'admin/structure/views/view/private/edit/page_1';
    $this->drupalPostForm($url, [], $this->t('Save'));
    $this->drupalGet('private-view');
    // Just the owner's terms should be visible.
    $this->assertText($admin_term->getName(), $this->t('Admin term visisble'));
    $this->assertNoText($private_term->getName(),
      $this->t('User term is not visisble'));

    $this->drupalLogin($user);
    $this->drupalGet('private-view');
    $this->assertNoText($admin_term->getName(),
      $this->t('Admin term is not visisble'));
    $this->assertText($private_term->getName(), $this->t('User term visisble'));
  }

  /**
   * Tests for Views integration.
   */
  public function testPrivateTaxonomyViewsRelationship() {
    $admin_user = $this->drupalCreateUser([
      'administer taxonomy',
      'administer views',
      'administer content types',
      'administer node fields',
    ]);
    $this->drupalLogin($admin_user);

    // Create a private vocabulary.
    $private = TRUE;
    $private_vocabulary = $this->createVocabulary($private);

    $this->drupalGet('admin/structure/types/manage/page/fields/add-field');
    $url = 'admin/structure/types/manage/page/fields/add-field';
    $edit = [
      'new_storage_type' => 'private_taxonomy_term_reference',
      'label' => 'Private',
      'field_name' => 'private',
    ];
    $this->drupalPostForm($url, $edit, $this->t('Save and continue'));
    $url = 'admin/structure/types/manage/page/fields/node.page.field_private/storage';
    $this->drupalPostForm($url, [], $this->t('Save field settings'));

    $url = 'admin/structure/views/nojs/add-handler/content/page_1/relationship';
    $this->drupalGet($url);
    $this->assertText('A bridge to the term that is referenced via field_private', $this->t('Relationship found'));
    $edit = [
      'name[node__field_private.field_private_target_id]' => TRUE,
    ];
    $this->drupalPostForm($url, $edit, $this->t('Add and configure relationships'));
    $url = 'admin/structure/views/nojs/handler/content/page_1/relationship/field_private_target_id';
    $this->drupalPostForm($url, [], $this->t('Apply'));
    $this->assertText('field_private');
  }

}
