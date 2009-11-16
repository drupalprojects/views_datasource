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
    $form['using_coder'] = array(
      '#type'           => 'checkbox',
      '#title'          => t('Use coder mode'),
      '#default_value'  => $this->options['using_coder'],
      '#description'    => t('Not using coder mode means the JSON gets output directly and the server ceases normal page processing.  Using coder mode means the server does not cease processing after outputting the JSON.  This allows for the Views API to be used with the view without having to prematurely terminate page processing.'),
    );
  }
}
