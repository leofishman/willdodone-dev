<?php

namespace Drupal\Tests\private_taxonomy\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test Private Taxonomy functionality.
 *
 * @group private_taxonomy
 */
class PrivateTaxonomyTokensTest extends BrowserTestBase {

  use \Drupal\Core\StringTranslation\StringTranslationTrait;

  /**
   * Default theme.
   *
   * @var string
   */
  protected $defaultTheme = 'classy';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['private_taxonomy', 'pathauto'];

  /**
   * Settings tests.
   */
  public function testPrivateTaxonomyTokens() {
    $admin_user = $this->drupalCreateUser(['administer pathauto']);
    $this->drupalLogin($admin_user);

    $this->drupalGet('admin/config/search/path/patterns/add');
    $edit = [
      'type' => 'canonical_entities:taxonomy_term',
      'label' => 'Name',
      'id' => 'name',
    ];
    $this->drupalPostForm('admin/config/search/path/patterns/add', $edit, $this->t('Save'));
    $edit = [
      'pattern' => '[term:vocabulary]/[term:term_owner_name]/[term:name]',
    ];
    $this->drupalPostForm('admin/config/search/path/patterns/name', $edit,
      $this->t('Save'));
    $this->assertNoText($this->t('invalid tokens'), 'Token found');
    $edit = [
      'type' => 'canonical_entities:taxonomy_term',
      'label' => 'User ID',
      'id' => 'user_id',
    ];
    $this->drupalPostForm('admin/config/search/path/patterns/add', $edit, $this->t('Save'));
    $edit = [
      'pattern' => '[term:vocabulary]/[term:term_owner_uid]/[term:name]',
    ];
    $this->drupalPostForm('admin/config/search/path/patterns/user_id', $edit,
      $this->t('Save'));
    $this->assertNoText($this->t('invalid tokens'), 'Token found');
  }

}
