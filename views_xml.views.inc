<?php
//$Id$
/**
 * @file views_xml.views.inc 
 * Views style plugins to render nodes in the XML data format.
 * @see views-view-xml.tpl.php
 * @ingroup views_plugins
 */

/**
 * Implementation of hook_views_plugin
 *
 */
function views_xml_views_plugins() {
  return array(
    'style' => array( //declare the views_xml_* style plugins
      'views_xml' => array(
        'title' => t('XML data document'),
        'theme' => 'views_view_xml',
        'help' => t('Displays nodes as XML.'),
        'handler' => 'views_plugin_style_xml',
        'uses row plugin' => TRUE,
        'uses fields' => TRUE,
        'uses options' => TRUE,
        'type' => 'normal',       
      ),
    ),
  );
}

class views_plugin_style_xml extends views_plugin_style {
  
  /**
   * Set default options
   */
  function options(&$options) {
    $options['schema'] = 'raw';
  }
  
  /**
   * Provide a form for setting options.
   *
   * @param array $form
   * @param array $form_state
   */  
  function options_form(&$form, &$form_state) {
    $form['schema'] = array(
      '#type' => 'radios',
      '#title' => t('XML schema'),
      '#options' => array('raw' => t('Raw XML'), 'opml' => t('OPML')),
      '#default_value' => $this->options['format'],
    );
  }

}

/**
 * Theme preprocess function for views-view-xml.tpl.php
 *
 * @param array $vars
 */
function template_preprocess_views_view_xml(&$vars) {
  $view     = &$vars['view'];
  $options  = $view->style_handler->options;
  $handler  = $view->style_handler;
}

