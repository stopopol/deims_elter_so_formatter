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
	
	$parents_object = array();
	$ids_object = array();
	$labels_object = array();
	
	// manually adding all high-level terms aka categories	
	$labels = array('Abiotic','Biotic<br>heterogeneity','Energy<br>budget','Matter<br>budget','Socio<br>Ecology','Water<br>Balance');
	$ids = array(54356,54380,54410,54431,54481,54533);
	$variable_for_looping_all_children = $ids;
	$parents = array('','','','','','');
	
	
	// we take the above listed parents and loop through each parent to get its children and add them to the array for the charts
	$children_tids = array();
	$children_labels = array();
	foreach($variable_for_looping_all_children as $parent_id) {
		$children = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadChildren($parent_id);
		foreach($children as $child) {
			$childTermId = $child->get('tid')->value;
			$child_label = \Drupal\taxonomy\Entity\Term::load($childTermId)->get('name')->value;
			array_push($labels,$child_label);
			array_push($ids,$childTermId);
			array_push($parents,$parent_id);
		}
	}
		
	// looping through all ticked terms
    foreach ($items as $delta => $item) {
      $item_value = $item->getValue();
      $term_id = $item_value['target_id'];
	  $term_label = \Drupal\taxonomy\Entity\Term::load($term_id)->get('name')->value;
	  	  
	  $compartment = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($term_id);
	  $compartment_item = reset($compartment);
	  $compartment_term_id = $compartment_item->id();
	  $compartment_term_label = \Drupal\taxonomy\Entity\Term::load($compartment_term_id)->get('name')->value;
	  
	  /*
	  $category = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($compartment_term_id);
	  $category_item = reset($category);
	  $category_term_id = $category_item->id();
	  $category_term_label = \Drupal\taxonomy\Entity\Term::load($category_term_id)->get('name')->value; */
	  
	  array_push($parents,$compartment_term_id);
	  array_push($ids,$term_id);
	  array_push($labels,$term_label);
	  
      $elements[$delta] = [
        '#markup' => '<div id="my_elter_so_test"></div>',
		'#attached' => array(
						'library'=> array('deims_elter_so_formatter/deims-elter-so-formatter'),
						'drupalSettings' => array(
							'deims_elter_so_formatter' => array(
								'data_object' => array(
									'parents' => $parents,
									'ids' => $ids,
									'labels' => $labels,
								),
							)
						),
		),
      ];
	  
    }

    return $elements;
  }
}
