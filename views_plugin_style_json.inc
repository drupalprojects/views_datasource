<?php
// $Id$

/**
 * @file
 * Implementation of views_plugin_style for views_json
 */


/**
 * Implementation of views_plugin_style
 */
class views_plugin_style_json extends views_plugin_style {
  /** 
   * Implementation of views_plugin_style::option_definition 
   */ 
  	function option_definition() {
      $options = parent::option_definition();
      $options['root_object'] = array('default' => 'nodes', 'translatable' => TRUE);
      $options['top_child_object'] = array('default' => 'node', 'translatable' => TRUE);
      $options['field_output'] = array('default' => 'normal', 'translatable' => TRUE);
      $options['plaintext_output'] = array('default' => TRUE, 'translatable' => TRUE);
      $options['format'] = array('default' => 'simple', 'translatable' => TRUE);
      $options['content_type'] = array('default' => 'application/json', 'translatable' => FALSE);
      $options['using_views_api_mode'] = array('default' => FALSE, 'translatable' => TRUE);
      return $options;
  	}
  	
  /**
   * Provide a form for setting options.
   *
   * @param $form
   * @param $form_state
   */
  function options_form(&$form, &$form_state) {
  	$form['root_object'] = array(
    '#type' => 'textfield',
    '#title' => t('Root object name'),
    '#default_value' => $this->options['root_object'],
    '#description' => t('The name of the root object in the JSON document. e.g nodes or users or forum_posts'),
      //'#process' => array('views_process_dependency'),
      //'#dependency' => array('radios:schema' => array('raw')),      
    );
  	$form['top_child_object'] = array(
    '#type' => 'textfield',
    '#title' => t('Top-level child object'),
    '#default_value' => $this->options['top_child_object'],
    '#description' => t('The name of each top-level child object in the JSON document. e.g node or user or forum_post'),
    );    
  	$form['field_output']  = array(
      '#type' => 'radios',
      '#title' => 'Field output',
      '#description' => 'For each row in the view, fields can be output as either the field rendered by Views, or by the raw content of the field.',  
      '#options' => array('normal' => t('Normal'), 'raw' => t('Raw')),
      '#default_value' => $this->options['field_output'],
    );
    $form['plaintext_output'] = array(
      '#type'           => 'checkbox',
      '#title'          => t('Plaintext output'),
      '#default_value'  => $this->options['plaintext_output'],
      '#description'    => t('For each row in the view, strip all markup from the field output.'),
    );
    $form['format'] = array(
      '#type'           => 'radios',
      '#title'          => t('JSON data format'),
      '#options'        => array(
        'simple'  => t('Simple'),
        'exhibit' => t('MIT Simile/Exhibit'),
      ),
      '#default_value'  => $this->options['format'],
      '#description'    => t('What object format will be used for JSON output.')
    );
    $form['content_type'] = array(
      '#type'           => 'radios',
      '#title'          => t('Content-Type'),
      '#options'        => array(
        'default'       => t("Default: application/json"),
        'text/json'     => t('text/json'),
       ),
      '#default_value'  => $this->options['content_type'],
      '#description'    => t('The Content-Type header that will be sent with the JSON output.')
    );
    $form['using_views_api_mode'] = array(
      '#type'           => 'checkbox',
      '#title'          => t('Views API mode'),
      '#default_value'  => $this->options['using_views_api_mode'],
      '#description'    => t('Not using View API mode means the JSON gets output directly and the server ceases normal page processing.  Using it means the server does not cease processing after outputting the JSON.  This allows the Views API to be used with the view without having to prematurely terminate page processing.'),
    );
  }
  
  /**
   * Implementation of view_style_plugin::render()
   */
  function render() {
  	$view = &$this->view;
  	$options = $this->options;
  	$field = $view->field;

 	  $rows = array();

    foreach ($view->result as $row) {
      $rows[] = _views_json_render_fields($view, $row);
    }
 	  
    return theme($this->theme_functions(), $this->view, $rows, $this->attachment, $this->options); 	  
 	}
 
}  

