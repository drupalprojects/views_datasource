<?php
//$Id$
/**
 * @file views_xhtml.views.inc 
 * Views style plugin to render nodes as XHTML microformats.
 * @see views-view-xhtml.tpl.php
 * @ingroup views_plugins
 */

/**
 * Implementation of hook_views_plugin
 *
 */
function views_xhtml_views_plugins() {
  return array(
    'style' => array( //declare the views_xhtml style plugin
      'views_xhtml' => array(
        'title' => t('XHTML data document'),
        'theme' => 'views_view_xhtml',
        'help' => t('Displays nodes as XHTML microformats.'),
        'handler' => 'views_plugin_style_xhtml',
        'uses row plugin' => TRUE,
        'uses fields' => TRUE,
        'uses options' => TRUE,
        'type' => 'normal',       
      ),
    ),
  );
}

class views_plugin_style_xhtml extends views_plugin_style {
  
  /**
   * Set default options
   */
  function options(&$options) {
    $options['format'] = 'hcard';
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
      '#title' => t('Microformat'),
      '#options' => array('hcard' => t('hCard'), 'hcalendar' => t('hCalendar')),
      '#default_value' => $this->options['format'],
    );
  }

}

/**
 * Theme preprocess function for views-view-xhtml.tpl.php
 *
 * @param array $vars
 */
function template_preprocess_views_view_xhtml(&$vars) {
  $view     = &$vars['view'];
  $options  = $view->style_handler->options;
  $handler  = $view->style_handler;
}