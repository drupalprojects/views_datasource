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
        'simple'  => t('Simple'),
        'exhibit' => t('MIT Simile/Exhibit'),
      ),
      '#default_value'  => $this->options['format'],
    );
    $form['using_views_api_mode'] = array(
      '#type'           => 'checkbox',
      '#title'          => t('Views API mode'),
      '#default_value'  => $this->options['using_views_api_mode'],
      '#description'    => t('Not using View API mode means the JSON gets output directly and the server ceases normal page processing.  Using it means the server does not cease processing after outputting the JSON.  This allows the Views API to be used with the view without having to prematurely terminate page processing.'),
    );
  }
}
