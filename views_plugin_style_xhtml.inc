<?php
// $Id$

/**
 * @file
 * Implementation of views_plugin_style for views_xhtml
 */


/**
 * Implementation of views_plugin_style
 */
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
   * @param $form
   * @param $form_state
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
