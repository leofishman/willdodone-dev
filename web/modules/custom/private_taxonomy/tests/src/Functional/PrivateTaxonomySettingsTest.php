<?php

namespace Drupal\Tests\private_taxonomy\Functional;

/**
 * Test Private Taxonomy functionality.
 *
 * @group private_taxonomy
 */
class PrivateTaxonomySettingsTest extends PrivateTaxonomyTestBase {

  use \Drupal\Core\StringTranslation\StringTranslationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['private_taxonomy'];

  /**
   * Settings tests.
   */
  public function testPrivateTaxonomySettings() {
    $admin_user = $this->drupalCreateUser(['administer taxonomy']);
    $user = $this->drupalCreateUser([]);
    $this->drupalLogin($admin_user);

    $admin_name = $admin_user->getDisplayName();
    $edit = [
      'cloning_user_name' => $admin_name,
      'enable_new_users' => TRUE,
    ];
    $this->drupalPostForm('admin/config/people/taxonomy', $edit,
      $this->t('Save configuration'));
    $this->assertRaw($admin_name, $this->t('Cloning user name visible'));
    $this->assertRaw('name="enable_new_users" value="1" checked="checked"',
      $this->t('New users enabled'));

    $private = TRUE;
    $private_vocabulary = $this->createVocabulary($private);
    $private_term = $this->createTerm($private_vocabulary);
    $this->drupalGet('admin/structure/taxonomy/manage/' .
      $private_vocabulary->id() . '/overview');
    $this->assertText($private_term->getName(),
      $this->t('Admin private term visible.'));
    $this->assertText($admin_name, $this->t('Admin private term visible.'));
    $edit = [
      'existing_users_cloning' => TRUE,
    ];
    $this->drupalPostForm('admin/config/people/taxonomy', $edit,
      $this->t('Save configuration'));
    $this->assertText('Terms cloned for 2 users', $this->t('Cloning message appears'));

    $this->drupalGet('admin/structure/taxonomy/manage/' .
      $private_vocabulary->id() . '/overview');
    $this->assertText('admin', $this->t('Term cloned for admin'));
    $this->assertText($user->getDisplayName(), $this->t('Term cloned for user'));
    $edit = [
      'cloning_user_name' => '',
      'enable_new_users' => FALSE,
    ];
    $this->drupalPostForm('admin/config/people/taxonomy', $edit,
      $this->t('Save configuration'));
    $this->assertText('The configuration options have been saved',
      $this->t('Remove user name'));
  }

  /**
   * Validation tests.
   */
  public function testPrivateTaxonomyValidation() {
    $admin_user = $this->drupalCreateUser(['administer taxonomy']);
    $this->drupalLogin($admin_user);

    $edit = [
      'enable_new_users' => TRUE,
    ];
    $this->drupalPostForm('admin/config/people/taxonomy', $edit,
      $this->t('Save configuration'));
    $this->assertText('Missing user name',
      $this->t('User name required to clone terms'));
    $edit = [
      'existing_users_cloning' => TRUE,
    ];
    $this->drupalPostForm('admin/config/people/taxonomy', $edit,
      $this->t('Save configuration'));
    $this->assertText('Missing user name',
      $this->t('User name required to clone terms'));
  }

  /**
   * Clone hierarcy test.
   */
  public function testPrivateTaxonomyHierarchy() {
    $admin_user = $this->drupalCreateUser(['administer taxonomy']);
    $user = $this->drupalCreateUser([]);
    $this->drupalLogin($admin_user);

    $admin_name = $admin_user->getDisplayName();
    $edit = [
      'cloning_user_name' => $admin_name,
      'enable_new_users' => TRUE,
    ];
    $this->drupalPostForm('admin/config/people/taxonomy', $edit,
      $this->t('Save configuration'));
    $this->assertRaw($admin_name, $this->t('Cloning user name visible'));
    $this->assertRaw('name="enable_new_users" value="1" checked="checked"',
      $this->t('New users enabled'));

    $private = TRUE;
    $private_vocabulary = $this->createVocabulary($private);
    $private_term1 = $this->createTerm($private_vocabulary);
    $private_term2 = $this->createTerm($private_vocabulary);
    $this->drupalGet('admin/structure/taxonomy/manage/' .
      $private_vocabulary->id() . '/overview');
    $this->assertText($private_term1->getName(),
      $this->t('Admin private term visible.'));
    $this->assertText($private_term2->getName(),
      $this->t('Admin private term visible.'));
    $this->drupalGet('taxonomy/term/' . $private_term2->id() . '/edit');
    $edit = [
      'parent[]' => $private_term1->id(),
    ];
    $this->drupalPostForm('taxonomy/term/' . $private_term2->id() . '/edit',
      $edit, $this->t('Save'));
    $this->drupalGet('admin/structure/taxonomy/manage/' .
      $private_vocabulary->id() . '/overview');
    $edit = [
      'existing_users_cloning' => TRUE,
    ];
    $this->drupalPostForm('admin/config/people/taxonomy', $edit,
      $this->t('Save configuration'));
    $this->assertText('Terms cloned for 2 users', $this->t('Cloning message appears'));

    $this->drupalGet('admin/structure/taxonomy/manage/' .
      $private_vocabulary->id() . '/overview');
    $this->assertRaw('terms[tid:4:0][term][parent]', $this->t('Hierarchy cloned'));
    $this->assertRaw('terms[tid:6:0][term][parent]', $this->t('Hierarchy cloned'));
  }

}
