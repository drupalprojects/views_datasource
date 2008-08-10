<?php
//$Id$
/**
 * @file views_rdf.views.inc 
 * Views style plugin to render nodes in the RDF data format.
 * @see views-view-rdf.tpl.php
 * @ingroup views_plugins
 */

/**
 * Implementation of hook_views_plugin
 *
 */
function views_rdf_views_plugins() {
  return array(
    'style' => array( //declare the views_rdf style plugin
      'views_rdf' => array(
        'title' => t('RDF data document'),
        'theme' => 'views_view_rdf',
        'help' => t('Displays nodes using the RDF data format.'),
        'handler' => 'views_plugin_style_rdf',
        'uses row plugin' => TRUE,
        'uses fields' => TRUE,
        'uses options' => TRUE,
        'type' => 'normal',       
      ),
    ),
  );
}

class views_plugin_style_rdf extends views_plugin_style {
  
  /**
   * Set default options
   */
  function options(&$options) {
    $options['vocabulary'] = 'FOAF';
  }
  
  /**
   * Provide a form for setting options.
   *
   * @param array $form
   * @param array $form_state
   */  
  function options_form(&$form, &$form_state) {
    $form['vocabulary'] = array(
      '#type' => 'radios',
      '#title' => t('RDF vocabulary'),
      '#options' => array('FOAF' => t('FOAF'),'SIOC' => t('SIOC') ),
      '#default_value' => $this->options['vocabulary'],
    );
  }

}

/**
 * Theme preprocess function for views-view-rdf.tpl.php
 *
 * @param array $vars
 */
function template_preprocess_views_view_rdf(&$vars) {
  $view     = &$vars['view'];
  $options  = $view->style_handler->options;
  $handler  = $view->style_handler;
}