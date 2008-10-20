<?php
//$Id$
/**
 * @file views_json.views.inc 
 * Views style plugin to render nodes in the JSON data format.
 * @see views-view-json.tpl.php views-view-row-unformatted.tpl.php
 * @ingroup views_plugins
 */

/**
 * Implementation of hook_views_plugin
 *
 */
function views_json_views_plugins() {
  return array(
    'style' => array( //declare the views_json style plugin
      'views_json' => array(
        'title' => t('JSON data document'),
        'theme' => 'views_view_json',
        'help' => t('Displays nodes in the JSON data format.'),
        'handler' => 'views_plugin_style_json',
        'uses row plugin' => TRUE,
        'uses fields' => TRUE,
        'uses options' => TRUE,
        'type' => 'normal',       
      ),
    ),
  );
}

/**
 * Implementation of views_plugin_style
 *
 */

class views_plugin_style_json extends views_plugin_style {
  
  /**
   * Set default options
   */
  function options(&$options) {
    $options['format'] = 'Simple';
  }
  
  /**
   * Provide a form for setting options.
   *
   * @param array $form
   * @param array $form_state
   */  
  function options_form(&$form, &$form_state) {
    $form['format'] = array(
      '#type' => 'radios',
      '#title' => t('JSON data format'),
      '#options' => array('Simple' => t('Simple'), 'Exhibit' => t('MIT Simile/Exhibit')),
      '#default_value' => $this->options['format'],
    );
  }

}

/**
 * Theme preprocess function for views-view-json.tpl.php
 *
 * @param array $vars
 */
function template_preprocess_views_view_json(&$vars) {
  $view     = &$vars['view'];
  $options  = $view->style_handler->options;
  $handler  = $view->style_handler;
}
