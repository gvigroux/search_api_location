<?php

namespace Drupal\search_api_location\LocationInput;

use Drupal\Core\Form\FormStateInterface;
use Drupal\search_api\Plugin\ConfigurablePluginBase;

/**
 * Defines a base class from which other data type classes may extend.
 */
abstract class LocationInputPluginBase extends ConfigurablePluginBase implements LocationInputInterface {

  /**
   * {@inheritdoc}
   */
  public function hasInput($input, array $options) {
    $input['value'] = trim($input['value']);
    if (!$input['value'] || !($options['operator'] || is_numeric($options['distance']['from']))) {
      return FALSE;
    }

    return TRUE;
  }
  
  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
  	return array(
  			'radius_type' => 'select',
  			'radius_options' => '- -\n5 5 km\n10 10 km\n16.09 10 mi',
  			'radius_units'	=> 1,
  	);
  }
  
  /**

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

  	//$configuration = $this->getConfiguration();
  	
    $form['radius_type'] = array(
      '#type' => 'select',
      '#title' => $this->t('Type of distance input'),
      '#description' => $this->t('Select the type of input element for the distance option.'),
      '#options' => array(
        'select' => $this->t('Select'),
        'textfield' => $this->t('Text field'),
      ),
      '#default_value' => $this->configuration['radius_type'],
    );
    

    $form['radius_options'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Distance options'),
      '#description' => $this->t('Add one line per option for Range you want to provide. The first part of each line is the distance in kilometres, everything after the first space is the label. "-" as the distance ignores the location for filtering, but will still use it for facets, sorts and distance calculation. Skipping the distance altogether (i.e., starting the line with a space) will provide an option for ignoring the entered location completely.'),
    		'#default_value' => $this->configuration['radius_options'],
      '#states' => [
        'visible' => [
          'select[name="options[plugin_' . $this->pluginId . '][radius_type]"]' => ['value' => 'select'],
        ],
      ],
    );

    $form['radius_units'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Distance conversion factor'),
      '#description' => $this->t('Enter the conversion factor from the expected unit of the user input to kilometers. E.g., miles would have a factor of 1.60935.'),
    		'#default_value' => $this->configuration['radius_units'],
      '#states' => [
        'visible' => [
          'select[name="options[plugin_' . $this->pluginId . '][radius_type]"]' => ['value' => 'textfield'],
        ],
      ],
    );
    

    return $form;
  }
  
  
  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the plugin form as built
   *   by static::buildConfigurationForm().
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the complete form.
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  	// TODO: Implement submitConfigurationForm() method.
  	//dpm($form_state);
  	//dpm($form_state->getValue(array('options', 'plugin_raw', 'radius_options')));
  	//$configuration = $this->getConfiguration();
  	$this->configuration['radius_type'] 		= $form_state->getValue(array('options', 'plugin_raw', 'radius_type'));
  	$this->configuration['radius_options'] 		= $form_state->getValue(array('options', 'plugin_raw', 'radius_options'));
  	$this->configuration['radius_units'] 		= $form_state->getValue(array('options', 'plugin_raw', 'radius_units'));
  	
  }
  
  
  /**
   * {@inheritdoc}
   */
  public function getForm(array $form, FormStateInterface $form_state, $options) {
  
  	//$configuration = $this->getConfiguration();
  	
    $distance_options = [];
    $lines = array_filter(array_map('rtrim', explode('\n', $this->configuration['radius_options'])));
    foreach ($lines as $line) {
      $pos = strpos($line, ' ');
      $range = substr($line, 0, $pos);
      $distance_options[$range] = trim(substr($line, $pos + 1));
    }

    $form['value']['#tree'] = TRUE;

    $value_prefix = 'of&nbsp;';
    $distance_prefix = '';

    if (!$options['expose']['use_operator']) {
      $distance_prefix = 'within&nbsp;';
    }
  
    $form['value']['distance']['from'] = [
      '#title' => '&nbsp;',
      '#type' => 'select',
      '#options' => $distance_options,
      '#field_prefix' => $distance_prefix,
    ];

    if ($options['expose']['use_operator'] || (!$options['expose']['use_operator'] && $options['operator'] == '<>')) {
      $form['value']['distance']['to'] = [
        '#title' => '&nbsp;',
        '#type' => 'select',
        '#options' => $distance_options,
        '#field_prefix' => 'and&nbsp;',
      ];
    }
    if ($options['expose']['use_operator']) {
      $form['value']['distance']['to']['#states'] = [
        'visible' => [
          'select[name="latlon_op"]' => ['value' => '<>'],
        ],
      ];
    }

   
    	$form['value']['value'] = [
    		'#type' => 'textfield',
    		'#title' => '&nbsp;',
    		'#field_prefix' => $value_prefix,
    		'#size' => 20,
    	];

    return $form;
  }

}
