<?php

namespace Drupal\Tests\conditional_fields\FunctionalJavascript;

use Drupal\field\Tests\EntityReference\EntityReferenceTestTrait;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Tests\conditional_fields\FunctionalJavascript\TestCases\ConditionalFieldCheckedUncheckedInterface;
use Drupal\Tests\conditional_fields\FunctionalJavascript\TestCases\ConditionalFieldValueInterface;

/**
 * Test Conditional Fields States.
 *
 * @group conditional_fields
 */
class ConditionalFieldRadiosTest extends ConditionalFieldTestBase implements
  ConditionalFieldValueInterface,
  ConditionalFieldCheckedUncheckedInterface {

  use EntityReferenceTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $screenshotPath = 'sites/simpletest/conditional_fields/radios/';

  /**
   * The name and vid of vocabulary, created for testing.
   *
   * @var string
   */
  protected $taxonomyName;

  /**
   * The amount of generated terms in created vocabulary.
   *
   * @var int
   */
  protected $termsCount;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Create a vocabulary with random name.
    $this->taxonomyName = $this->getRandomGenerator()->word(8);
    $vocabulary = Vocabulary::create([
      'name' => $this->taxonomyName,
      'vid' => $this->taxonomyName,
    ]);
    $vocabulary->save();
    // Create a random taxonomy terms for vocabulary.
    $this->termsCount = mt_rand(3, 5);
    for ($i = 1; $i <= $this->termsCount; $i++) {
      $termName = $this->getRandomGenerator()->word(8);
      Term::create([
        'parent' => [],
        'name' => $termName,
        'vid' => $this->taxonomyName,
      ])->save();
    }
    // Add a custom field with taxonomy terms to 'Article'.
    // The field label is a machine name of created vocabulary.
    $handler_settings = [
      'target_bundles' => [
        $vocabulary->id() => $vocabulary->id(),
      ],
    ];
    $this->createEntityReferenceField('node', 'article', 'field_' . $this->taxonomyName, $this->taxonomyName, 'taxonomy_term', 'default', $handler_settings);
    EntityFormDisplay::load('node.article.default')
      ->setComponent('field_' . $this->taxonomyName, ['type' => 'options_buttons'])
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  public function testVisibleValueWidget() {
    // TODO: Implement testVisibleValueWidget() method.
    $this->markTestIncomplete();
  }

  /**
   * {@inheritdoc}
   */
  public function testVisibleValueRegExp() {
    // TODO: Implement testVisibleValueRegExp() method.
    $this->markTestIncomplete();
  }

  /**
   * {@inheritdoc}
   */
  public function testVisibleValueAnd() {
    $this->baseTestSteps();

    // Visit a ConditionalFields configuration page for `Article` Content type.
    $this->createCondition('body', 'field_' . $this->taxonomyName, 'visible', 'value');
    // Change a condition's values set and the value.
    $this->changeField('#edit-values-set', CONDITIONAL_FIELDS_DEPENDENCY_VALUES_AND);
    // Random term id to check necessary value.
    $term_id = mt_rand(1, $this->termsCount);
    $this->changeField('#edit-values', $term_id);
    // Submit the form.
    $this->getSession()
      ->executeScript("jQuery('#conditional-field-edit-form').submit();");
    $this->assertSession()->statusCodeEquals(200);

    // Check if that configuration is saved.
    $this->drupalGet('admin/structure/conditional_fields/node/article');
    $this->assertSession()
      ->pageTextContains('body field_' . $this->taxonomyName . ' visible value');

    // Visit Article Add form to check that conditions are applied.
    $this->drupalGet('node/add/article');
    $this->assertSession()->statusCodeEquals(200);

    // Check that the field Body is not visible.
    $this->waitUntilHidden('.field--name-body', 0, 'Article Body field is visible');
    // Change a select value set to show the body.
    $this->changeSelect('#edit-field-' . $this->taxonomyName . '-' . $term_id, $term_id);
    $this->waitUntilVisible('.field--name-body', 50, 'Article Body field is not visible');
//    $this->createScreenshot('sites/simpletest/scr1BodyVisTerm.jpg');
    // Change a select value set to hide the body again.
    $this->changeSelect('#edit-field-' . $this->taxonomyName . '-' . $term_id);
    $this->waitUntilHidden('.field--name-body', 50, 'Article Body field is visible');
//    $this->createScreenshot('sites/simpletest/scr2BodyHid.jpg');
  }

  /**
   * {@inheritdoc}
   */
  public function testVisibleValueOr() {
    $this->baseTestSteps();

    // Visit a ConditionalFields configuration page for `Article` Content type.
    $this->createCondition('body', 'field_' . $this->taxonomyName, 'visible', 'value');
    // Change a condition's values set and the value.
    $this->changeField('#edit-values-set', CONDITIONAL_FIELDS_DEPENDENCY_VALUES_OR);
    // Random term id to check necessary value.
    $term_id_1 = mt_rand(1, $this->termsCount);
    do {
      $term_id_2 = mt_rand(1, $this->termsCount);
    } while ($term_id_2 == $term_id_1);
    $values = $term_id_1 . '\r\n' . $term_id_2;
    $this->changeField('#edit-values', $values);
    // Submit the form.
    $this->getSession()
      ->executeScript("jQuery('#conditional-field-edit-form').submit();");
    $this->assertSession()->statusCodeEquals(200);

    // Check if that configuration is saved.
    $this->drupalGet('admin/structure/conditional_fields/node/article');
    $this->assertSession()
      ->pageTextContains('body field_' . $this->taxonomyName . ' visible value');

    // Visit Article Add form to check that conditions are applied.
    $this->drupalGet('node/add/article');
    $this->assertSession()->statusCodeEquals(200);

    // Check that the field Body is not visible.
    $this->waitUntilHidden('.field--name-body', 0, 'Article Body field is visible');
    // Change a select value set to show the body.
    $this->changeSelect('#edit-field-' . $this->taxonomyName . '-' . $term_id_1, $term_id_1);
    $this->waitUntilVisible('.field--name-body', 50, 'Article Body field is not visible');
    // Change a select value set to hide the body again.
    $this->changeSelect('#edit-field-' . $this->taxonomyName . '-' . $term_id_1);
    $this->waitUntilHidden('.field--name-body', 50, 'Article Body field is visible');
    $this->changeSelect('#edit-field-' . $this->taxonomyName . '-' . $term_id_2, $term_id_2);
    $this->waitUntilVisible('.field--name-body', 50, 'Article Body field is not visible');
    // Change a select value set to hide the body again.
    $this->changeSelect('#edit-field-' . $this->taxonomyName . '-' . $term_id_2);
    $this->waitUntilHidden('.field--name-body', 50, 'Article Body field is visible');
  }

  /**
   * {@inheritdoc}
   */
  public function testVisibleValueNot() {
    // TODO: Implement testVisibleValueNot() method.
    $this->markTestIncomplete();
  }

  /**
   * {@inheritdoc}
   */
  public function testVisibleValueXor() {
    // TODO: Implement testVisibleValueXor() method.
    $this->markTestIncomplete();
  }

  /**
   * {@inheritdoc}
   */
  public function testVisibleChecked() {
    $this->baseTestSteps();

    // Visit a ConditionalFields configuration page for `Article` Content type.
    $this->createCondition('body', 'field_' . $this->taxonomyName, 'visible', 'checked');

    // Check if that configuration is saved.
    $this->drupalGet('admin/structure/conditional_fields/node/article');
    $this->assertSession()
      ->pageTextContains('body field_' . $this->taxonomyName . ' visible checked');

    // Visit Article Add form to check that conditions are applied.
    $this->drupalGet('node/add/article');
    $this->assertSession()->statusCodeEquals(200);

    // Check that the field Body is not visible.
    $this->waitUntilHidden('.field--name-body', 0, 'Article Body field is visible');
    for ($term_id = 1; $term_id < $this->termsCount; $term_id++) {
      // Change a select value set to show the body.
      $this->changeSelect('#edit-field-' . $this->taxonomyName . '-' . $term_id, $term_id);
      $this->waitUntilVisible('.field--name-body', 50, 'Article Body field is not visible');
//      $this->createScreenshot('sites/simpletest/scr1BodyVisTerm'.$term_id.'.jpg');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function testVisibleUnchecked() {
    $this->baseTestSteps();

    // Visit a ConditionalFields configuration page for `Article` Content type.
    $this->createCondition('body', 'field_' . $this->taxonomyName, 'visible', '!checked');

    // Check if that configuration is saved.
    $this->drupalGet('admin/structure/conditional_fields/node/article');
    $this->assertSession()
      ->pageTextContains('body field_' . $this->taxonomyName . ' visible !checked');

    // Visit Article Add form to check that conditions are applied.
    $this->drupalGet('node/add/article');
    $this->assertSession()->statusCodeEquals(200);

    // Check that the field Body is visible.
    $this->waitUntilVisible('.field--name-body', 50, 'Article Body field is not visible');
    for ($term_id = 1; $term_id < $this->termsCount; $term_id++) {
      // Change a select value set to hide the body.
      $this->changeSelect('#edit-field-' . $this->taxonomyName . '-' . $term_id, $term_id);
      $this->waitUntilHidden('.field--name-body', 50, 'Article Body field is visible');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function testInvisibleUnchecked() {
    // TODO: Implement testInvisibleUnchecked() method.
    $this->markTestIncomplete();
  }

}
