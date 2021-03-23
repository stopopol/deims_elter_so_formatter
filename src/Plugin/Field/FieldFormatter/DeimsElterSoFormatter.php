<?php

namespace Drupal\deims_elter_so_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'DeimsElterSoFormatter' formatter.
 *
 * @FieldFormatter(
 *   id = "deims_elter_so_formatter",
 *   label = @Translation("DEIMS eLTER SO Formatter"),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   quickedit = {
 *     "editor" = "disabled"
 *   }
 * )
 */
 
class DeimsElterSoFormatter extends FormatterBase {

  /**
   * https://www.drupal.org/docs/creating-custom-modules/adding-stylesheets-css-and-javascript-js-to-a-drupal-module
   * https://plotly.com/javascript/sunburst-charts/
   * 	always show all categories
   *		and only print covered compartments and variables
   * {@inheritdoc}
   */
 
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Formats the elter SO field of Drupal.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

	// check if compartments are covered - make chart sectors grey if not covered
	$abiotic_check = false;
	$biotic_heterogeneity_check = false;
	$socio_ecology_check = false;
	$energy_budget_check = false;
	$water_balance_check = false;

    foreach ($items as $delta => $item) {
      $item_value = $item->getValue();
      $term_id = $item_value['target_id'];
	  $term_label = \Drupal\taxonomy\Entity\Term::load($term_id)->get('name')->value;
	  	  
	  $compartment = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($term_id);
	  $compartment_item = reset($compartment);
	  $compartment_term_id = $compartment_item->id();
	  $compartment_term_label = \Drupal\taxonomy\Entity\Term::load($compartment_term_id)->get('name')->value;
	  
	  $category = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($compartment_term_id);
	  $category_item = reset($category);
	  $category_term_id = $category_item->id();
	  $category_term_label = \Drupal\taxonomy\Entity\Term::load($category_term_id)->get('name')->value;
	  
	  $output = $category_term_label . ' - ' . $compartment_term_label . ' - ' . $term_label;
	  

      $elements[$delta] = [
        '#markup' => '<div id="my_elter_so_test"></div>',
		'#variablenametobepassed' => 20, // any other preprocessing		
		'#attached' => array('library'=> array('deims_elter_so_formatter/deims-elter-so-formatter')),
      ];
	  
    }

    return $elements;
  }
}
