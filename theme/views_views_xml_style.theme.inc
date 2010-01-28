<?php
// $Id$

/**
 * @file
 * View template to render view fields as XML.
 *
 * - $view: The view in use.
 * - $rows: Array of row objects as rendered by _views_json_render_fields 
 * - $attachment: not used currently
 * - $options: The options for the style passed in from the UI.
 *
 * @ingroup views_templates
 * @see views_xml.views.inc
 */

function theme_views_views_xml_style(&$view, $rows = array(), $attachment = NULL, $options = array()) {
	if ($options['schema'] == 'raw') return theme('views_views_xml_style_raw', $view, $rows, $attachment, $options);
	  
}

function template_preprocess_views_views_xml_style_raw(&$vars) {	
  //drupal_set_header('Content-Type: text/xml; charset=utf-8');	 
}

