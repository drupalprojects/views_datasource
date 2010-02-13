<?php
// $Id$

/**
 * @file
 * Implementation of views_plugin_style for views_xml
 */


/**
 * Implementation of views_plugin_style
 */
class views_plugin_style_xml extends views_plugin_style {

  /** 
   * Implementation of views_plugin_style::option_definition 
   */ 
	function option_definition() {
    $options = parent::option_definition();
    $options['schema'] = array('default' => 'raw', 'translatable' => TRUE);
    $options['root_element'] = array('default' => 'node', 'translatable' => TRUE);
    $options['field_output'] = array('default' => 'normal', 'translatable' => TRUE);
    $options['plaintext_output'] = array('default' => TRUE, 'translatable' => TRUE);
    $options['skip_empty_fields'] = array('default' => TRUE, 'translatable' => TRUE);
    $options['element_output'] = array('default' => 'nested', 'translatable' => TRUE);
    $options['escape_as_CDATA'] = array('default' => 'yes', 'translatable' => TRUE);
    $options['content_type'] = array('default' => 'text/xml', 'translatable' => FALSE);
    $options['header'] = array('default' => NULL, 'translatable' => FALSE);
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
  	$form['schema'] = array(
      '#type' => 'radios',
      '#title' => t('XML schema'),
      '#description' => t('The schema or format of the XML data document.'),    
      '#options' => array('raw' => t('Raw XML'), 'opml' => t('OPML'), 'atom' => t('Atom')),
      '#default_value' => $this->options['schema'],
    );
    $form['root_element'] = array(
      '#type' => 'textfield',
      '#title' => t('Root element name'),
      '#default_value' => $this->options['root_element'],
      '#description' => t('The name of the root element in the XML document.'),
      //'#process' => array('views_process_dependency'),
      //'#dependency' => array('edit-style-options-schema' => array('raw')),  
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
    $form['skip_empty_fields'] = array(
      '#type'           => 'checkbox',
      '#title'          => t('Skip adding fields that are empty.'),
      '#default_value'  => $this->options['skip_empty_fields'],
      '#description'    => t('For each row in the view, skip adding a field if the field value is NULL or blank.'),
    );    
    $form['element_output']  = array(
      '#type' => 'radios',
      '#title' => 'Element output',
      '#description' => 'For each row in the view, fields can be output as either nested XML child elements, or attributes of the XML element.',
      '#options' => array('nested' => t('Nested'), 'attributes' => t('Attributes')),
      '#default_value' => $this->options['element_output'],
    );
    $form['escape_as_CDATA']  = array(
      '#type' => 'radios',
      '#title' => 'Escape row content as CDATA',
      '#description' => 'For each row in the view, escape the row field content as XML CDATA sections.',
      '#options' => array('yes' => t('Yes'), 'no' => t('No')),
      '#default_value' => $this->options['escape_as_CDATA'],
      //'#process' => array('views_process_dependency'),
      //'#dependency' => array('edit-style-options-element-output-options' => array('nested')),
    );
    $form['header'] = array (
      '#type' => 'textarea',
      '#title' => 'XML document header',
      '#description' => 'You can use this to place any additional lines you want after the document declaration (like !DOCTYPE.)',
      '#default_value' => $this->options['header'], 
      );
    $form['content_type'] = array(
      '#type'           => 'radios',
      '#title'          => t('Content-Type'),
      '#options'        => array(
         'default'      => t("Default: text/xml for raw documents, text/html for OPML, application/atom+xml for Atom."),  
         'text/xml'     => 'text/xml',
         'text/html'    => 'text/html',
         'text/x-opml'  => 'text/x-opml'
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
      $rows[] = _views_xml_render_fields($view, $row);
    }
    
 	  return theme($this->theme_functions(), $this->view, $rows, $this->attachment, $this->options); 	  
 	}
 
}