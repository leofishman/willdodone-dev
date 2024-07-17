<?php

namespace Drupal\Tests\private_taxonomy\Functional;

/**
 * Test Private Taxonomy functionality.
 *
 * @group private_taxonomy
 */
class PrivateTaxonomyPermissionsTest extends PrivateTaxonomyTestBase {

  use \Drupal\Core\StringTranslation\StringTranslationTrait;

  /**
   * Test for user with 'administer own taxonomy' permission.
   */
  public function testUserPrivateTaxonomy() {
    $admin_user = $this->drupalCreateUser(['administer taxonomy']);
    $user = $this->drupalCreateUser(['administer own taxonomy']);

    // Create a private vocabulary.
    $private = TRUE;
    $private_vocabulary = $this->createVocabulary($private);
    $private = FALSE;
    $public_vocabulary = $this->createVocabulary($private);

    // Add terms to vocabularies.
    $this->drupalLogin($user);
    $private_term = $this->createTerm($private_vocabulary);
    $this->drupalLogin($admin_user);
    $admin_term = $this->createTerm($private_vocabulary);

    $this->drupalLogin($user);

    // Test to see what vocabularies are visible.
    $this->drupalGet('admin/structure/taxonomy');
    $this->assertNoText($public_vocabulary->label(),
      $this->t('Public vocabulary not visible.'));
    $this->assertText($private_vocabulary->label(),
      $this->t('Private vocabulary visible.'));

    // Test to see what terms are visible.
    $this->drupalGet('admin/structure/taxonomy/manage/' .
      $private_vocabulary->id() . '/overview');
    $this->assertText($private_term->getName(), $this->t('Private term visible.'));
    $this->assertNoText($admin_term->getName(), $this->t('Admin term not visible.'));

    // Test to verify what vocabularies we can add terms to.
    $this->drupalGet('admin/structure/taxonomy/manage/' . $private_vocabulary->id() . '/add');
    $this->assertSession()->statusCodeEquals(200);

    $this->drupalGet('admin/structure/taxonomy/manage/' . $public_vocabulary->id() . '/add');
    $this->assertSession()->statusCodeEquals(403);

  }

  /**
   * Test for user with 'view private taxonomies' permission.
   *
   * Uses 'all' for widget.
   */
  public function testViewPrivateTaxonomyAll() {
    $admin_user = $this->drupalCreateUser([
      'administer taxonomy',
      'administer content types',
      'administer node fields',
      'administer node display',
      'administer node form display',
      'bypass node access',
      'administer nodes',
      'access content overview',
    ]);
    $user = $this->drupalCreateUser([
      'view private taxonomies',
      'create page content',
      'edit own page content',
      'access content',
    ]);

    // Create a private vocabulary.
    $private = TRUE;
    $private_vocabulary = $this->createVocabulary($private);
    $private = FALSE;
    $public_vocabulary = $this->createVocabulary($private);

    // Add terms to vocabularies.
    $this->drupalLogin($user);
    $private_term = $this->createTerm($private_vocabulary);
    $this->drupalLogin($admin_user);
    $admin_term = $this->createTerm($private_vocabulary);

    $this->drupalLogin($user);

    // Test to see what vocabularies are visible.
    $this->drupalGet('admin/structure/taxonomy');
    $this->assertNoText($public_vocabulary->label(),
      $this->t('Public vocabulary not visible.'));
    $this->assertText($private_vocabulary->label(),
      $this->t('Private vocabulary visible.'));

    // Test to see what terms are visible.
    $this->drupalGet('admin/structure/taxonomy/manage/' .
      $private_vocabulary->id() . '/overview');
    $this->assertText($private_term->getName(), $this->t('Private term visible.'));
    $this->assertText($admin_term->getName(), $this->t('Admin term visible.'));

    $this->drupalLogin($admin_user);
    $edit = [
      'new_storage_type' => 'private_taxonomy_term_reference',
      'label' => 'Private',
      'field_name' => 'private',
    ];
    $this->drupalPostForm('admin/structure/types/manage/page/fields/add-field',
      $edit, $this->t('Save and continue'));
    $edit = [
      'fields[field_private][type]' => 'options_select',
      'fields[field_private][region]' => 'content',
    ];
    $this->drupalPostForm('admin/structure/types/manage/page/form-display',
      $edit, $this->t('Save'));
    $edit = [
      'fields[field_private][type]' => 'private_taxonomy_term_reference_link',
      'fields[field_private][region]' => 'content',
    ];
    $this->drupalPostForm('admin/structure/types/manage/page/display',
      $edit, $this->t('Save'));
    $edit = [
      'settings[allowed_values][0][vocabulary]' => $private_vocabulary->id(),
      'settings[allowed_values][0][users]' => 'all',
    ];
    $this->drupalPostForm('admin/structure/types/manage/page/fields/node.page.field_private/storage', $edit, $this->t('Save field settings'));

    $this->drupalGet('node/add/page');
    $this->assertText($admin_term->getName(), $this->t('Found term'));
    $this->assertText($private_term->getName(), $this->t('Found term'));

    $edit = [
      'title[0][value]' => $this->randomMachineName(),
      'field_private' => $admin_term->id(),
    ];
    $this->drupalPostForm('node/add/page', $edit, $this->t('Save'));
    // Should find the owner's term and use it.
    $this->assertRaw('taxonomy/term/' . $admin_term->id(), $this->t('Found term'));
    // Check taxonomy index.
    $this->drupalGet('taxonomy/term/' . $admin_term->id());
    $this->assertRaw($admin_term->getName(), $this->t('Found term'));
  }

  /**
   * Test for user with 'view private taxonomies' permission.
   *
   * Uses 'owner' for widget.
   */
  public function testViewPrivateTaxonomyOwner() {
    $admin_user = $this->drupalCreateUser([
      'administer taxonomy',
      'administer content types',
      'administer node fields',
      'administer node display',
      'administer node form display',
    ]);
    $user = $this->drupalCreateUser([
      'view private taxonomies',
      'create page content',
      'edit own page content',
    ]);

    // Create a private vocabulary.
    $private = TRUE;
    $private_vocabulary = $this->createVocabulary($private);
    $private = FALSE;
    $public_vocabulary = $this->createVocabulary($private);

    // Add terms to vocabularies.
    $this->drupalLogin($user);
    $private_term = $this->createTerm($private_vocabulary);
    $this->drupalLogin($admin_user);
    $admin_term = $this->createTerm($private_vocabulary);

    $this->drupalLogin($user);

    // Test to see what vocabularies are visible.
    $this->drupalGet('admin/structure/taxonomy');
    $this->assertNoText($public_vocabulary->label(),
      $this->t('Public vocabulary not visible.'));
    $this->assertText($private_vocabulary->label(),
      $this->t('Private vocabulary visible.'));

    // Test to see what terms are visible.
    $this->drupalGet('admin/structure/taxonomy/manage/' .
      $private_vocabulary->id() . '/overview');
    $this->assertText($private_term->getName(), $this->t('Private term visible.'));
    $this->assertText($admin_term->getName(), $this->t('Admin term visible.'));

    $this->drupalLogin($admin_user);
    $edit = [
      'new_storage_type' => 'private_taxonomy_term_reference',
      'label' => 'Private',
      'field_name' => 'private',
    ];
    $this->drupalPostForm('admin/structure/types/manage/page/fields/add-field',
      $edit, $this->t('Save and continue'));
    $edit = [
      'fields[field_private][type]' => 'options_select',
      'fields[field_private][region]' => 'content',
    ];
    $this->drupalPostForm('admin/structure/types/manage/page/form-display',
      $edit, $this->t('Save'));
    $edit = [
      'fields[field_private][type]' => 'private_taxonomy_term_reference_plain',
      'fields[field_private][region]' => 'content',
    ];
    $this->drupalPostForm('admin/structure/types/manage/page/display',
      $edit, $this->t('Save'));
    $edit = [
      'settings[allowed_values][0][vocabulary]' => $private_vocabulary->id(),
      'settings[allowed_values][0][users]' => 'owner',
    ];
    $this->drupalPostForm('admin/structure/types/manage/page/fields/node.page.field_private/storage', $edit, $this->t('Save field settings'));

    $this->drupalLogin($user);
    $this->drupalGet('node/add/page');
    $this->assertNoText($admin_term->getName(), $this->t('Term not found'));
    $this->assertText($private_term->getName(), $this->t('Found term'));

    $edit = [
      'title[0][value]' => $this->randomMachineName(),
      'field_private' => $private_term->id(),
    ];
    $this->drupalPostForm('node/add/page', $edit, $this->t('Save'));

    // Should find the owner's term and use it.
    $this->assertText($private_term->getName(), $this->t('Found term'));
    // Check taxonomy index.
    $this->drupalGet('taxonomy/term/' . $private_term->id());
    $this->assertRaw($private_term->getName(), $this->t('Found term'));
  }

  /**
   * Test for user with both permissions.
   */
  public function testBothPrivateTaxonomy() {
    $admin_user = $this->drupalCreateUser(['administer taxonomy']);
    $user = $this->drupalCreateUser([
      'administer own taxonomy',
      'view private taxonomies',
    ]);

    // Create a private vocabulary.
    $private = TRUE;
    $private_vocabulary = $this->createVocabulary($private);
    $private = FALSE;
    $public_vocabulary = $this->createVocabulary($private);

    // Add terms to vocabularies.
    $this->drupalLogin($user);
    $private_term = $this->createTerm($private_vocabulary);
    $this->drupalLogin($admin_user);
    $admin_term = $this->createTerm($private_vocabulary);

    $this->drupalLogin($user);

    // Test to see what vocabularies are visible.
    $this->drupalGet('admin/structure/taxonomy');
    $this->assertNoText($public_vocabulary->label(),
      $this->t('Public vocabulary not visible.'));
    $this->assertText($private_vocabulary->label(),
      $this->t('Private vocabulary visible.'));

    // Test to see what terms are visible.
    $this->drupalGet('admin/structure/taxonomy/manage/' .
      $private_vocabulary->id() . '/overview');
    $this->assertText($private_term->getName(), $this->t('Private term visible.'));
    $this->assertText($admin_term->getName(), $this->t('Admin term visible.'));
  }

  /**
   * Test that private vocabulary terms are actually private.
   */
  public function testNoPermissions() {
    $admin_user = $this->drupalCreateUser(['administer taxonomy']);
    $user = $this->drupalCreateUser(['administer own taxonomy']);
    $restricted_user = $this->drupalCreateUser();

    // Create a private vocabulary.
    $private = TRUE;
    $private_vocabulary = $this->createVocabulary($private);
    $private = FALSE;
    $public_vocabulary = $this->createVocabulary($private);

    // Add terms to vocabularies.
    $this->drupalLogin($user);
    $private_term = $this->createTerm($private_vocabulary);
    $public_term = $this->createTerm($public_vocabulary);

    $this->drupalLogin($restricted_user);

    // Test to see what terms are visible.
    $this->drupalGet('taxonomy/term/' . $public_term->id());
    $this->assertSession()->statusCodeEquals(200);

    $this->drupalGet('taxonomy/term/' . $private_term->id());
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Test 'edit own terms in' Taxonomy permissions.
   */
  public function testEditOwnTerms() {
    $admin_user = $this->drupalCreateUser(['administer own taxonomy']);

    // Create a private vocabulary.
    $private = TRUE;
    $private_vocabulary = $this->createVocabulary($private);
    $private_restricted_vocabulary = $this->createVocabulary($private);

    $user = $this->drupalCreateUser([
      'edit own terms in ' . $private_vocabulary->id(),
    ]);

    // Add terms to vocabularies.
    $this->drupalLogin($admin_user);
    $admin_term = $this->createTerm($private_vocabulary);

    $this->drupalLogin($user);
    $private_term = $this->createTerm($private_vocabulary);
    $private_restricted_term = $this->createTerm($private_restricted_vocabulary);

    // Test to see what terms we can edit.
    $this->drupalGet('taxonomy/term/' . $private_term->id() . '/edit');
    $this->assertSession()->statusCodeEquals(200);

    $this->drupalGet('taxonomy/term/' . $private_restricted_term->id() . '/edit');
    $this->assertSession()->statusCodeEquals(403);

    $this->drupalGet('taxonomy/term/' . $admin_term->id() . '/edit');
    $this->assertSession()->statusCodeEquals(403);

  }

  /**
   * Test 'delete own terms in' Taxonomy permissions.
   */
  public function testDeleteOwnTerms() {
    $admin_user = $this->drupalCreateUser(['administer own taxonomy']);

    // Create a private vocabulary.
    $private = TRUE;
    $private_vocabulary = $this->createVocabulary($private);
    $private_restricted_vocabulary = $this->createVocabulary($private);

    $user = $this->drupalCreateUser([
      'delete own terms in ' . $private_vocabulary->id(),
    ]);

    // Add terms to vocabularies.
    $this->drupalLogin($admin_user);
    $admin_term = $this->createTerm($private_vocabulary);

    // Add terms to vocabularies.
    $this->drupalLogin($admin_user);
    $admin_term = $this->createTerm($private_vocabulary);

    $this->drupalLogin($user);
    $private_term = $this->createTerm($private_vocabulary);
    $private_restricted_term = $this->createTerm($private_restricted_vocabulary);

    // Test to see what terms we can edit.
    $this->drupalGet('taxonomy/term/' . $private_term->id() . '/delete');
    $this->assertSession()->statusCodeEquals(200);

    $this->drupalGet('taxonomy/term/' . $private_restricted_term->id() . '/delete');
    $this->assertSession()->statusCodeEquals(403);

    $this->drupalGet('taxonomy/term/' . $admin_term->id() . '/delete');
    $this->assertSession()->statusCodeEquals(403);

  }

  /**
   * Test 'edit terms in' Taxonomy permissions.
   */
  public function testEditTaxonomyTerms() {
    $user = $this->drupalCreateUser(['administer own taxonomy']);

    // Create a private vocabulary.
    $private = TRUE;
    $private_vocabulary = $this->createVocabulary($private);
    $private_restricted_vocabulary = $this->createVocabulary($private);

    $admin_user = $this->drupalCreateUser([
      'edit terms in ' . $private_vocabulary->id(),
    ]);

    // Add terms to vocabularies.
    $this->drupalLogin($user);
    $private_term = $this->createTerm($private_vocabulary);
    $private_restricted_term = $this->createTerm($private_restricted_vocabulary);

    $this->drupalLogin($admin_user);

    // Test to see what terms we can edit.
    $this->drupalGet('taxonomy/term/' . $private_term->id() . '/edit');
    $this->assertSession()->statusCodeEquals(200);

    $this->drupalGet('taxonomy/term/' . $private_restricted_term->id() . '/edit');
    $this->assertSession()->statusCodeEquals(403);

  }

  /**
   * Test 'create terms in' Taxonomy permissions.
   */
  public function testCreateTaxonomyTerms() {
    $user = $this->drupalCreateUser(['administer own taxonomy']);

    // Create a private vocabulary.
    $private = TRUE;
    $private_vocabulary = $this->createVocabulary($private);
    $private_restricted_vocabulary = $this->createVocabulary($private);

    $admin_user = $this->drupalCreateUser([
      'create terms in ' . $private_vocabulary->id(),
    ]);

    $this->drupalLogin($admin_user);

    // Test to see what vocabularies we can add to.
    $this->drupalGet('admin/structure/taxonomy/manage/' . $private_vocabulary->id() . '/add');
    $this->assertSession()->statusCodeEquals(200);

    $this->drupalGet('admin/structure/taxonomy/manage/' . $private_restricted_vocabulary->id() . '/add');
    $this->assertSession()->statusCodeEquals(403);

  }

  /**
   * Test 'delete terms in' Taxonomy permissions.
   */
  public function testDeleteTaxonomyTerms() {
    $user = $this->drupalCreateUser(['administer own taxonomy']);

    // Create a private vocabulary.
    $private = TRUE;
    $private_vocabulary = $this->createVocabulary($private);
    $private_restricted_vocabulary = $this->createVocabulary($private);

    $admin_user = $this->drupalCreateUser([
      'delete terms in ' . $private_vocabulary->id(),
    ]);

    // Add terms to vocabularies.
    $this->drupalLogin($user);
    $private_term = $this->createTerm($private_vocabulary);
    $private_restricted_term = $this->createTerm($private_restricted_vocabulary);

    $this->drupalLogin($admin_user);

    // Test to see what terms we can delete.
    $this->drupalGet('taxonomy/term/' . $private_term->id() . '/delete');
    $this->assertSession()->statusCodeEquals(200);

    $this->drupalGet('taxonomy/term/' . $private_restricted_term->id() . '/delete');
    $this->assertSession()->statusCodeEquals(403);

  }

}
