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
   * Set default options
   */
  function options(&$options) {
    $options['format'] = 'Simple';
  }

  /**
   * Provide a form for setting options.
   *
   * @param $form
   * @param $form_state
   */
  function options_form(&$form, &$form_state) {
    $form['format'] = array(
      '#type'           => 'radios',
      '#title'          => t('JSON data format'),
      '#options'        => array(
        'simple'        => t('Simple'),
        'simple-coder'  => t('Simple (Coder)'),
        'exhibit'       => t('MIT Simile/Exhibit'),
        'exhibit-coder' => t('MIT Simile/Exhibit (Coder)'),
      ),
      '#default_value'  => $this->options['format'],
    );
  }
}
