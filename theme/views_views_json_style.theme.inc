<?php
// $Id$

/**
 * @file
 * Views theme to render view fields as JSON.
 *
 * - $view: The view in use.
 * - $rows: Array of row objects as rendered by _views_json_render_fields 
 * - $attachment: Not used currently
 * - $options: The options for the style passed in from the UI.
 *
 * @ingroup views_templates
 * @see views_json.views.inc
 */ 

function theme_views_views_json_style(&$view, $rows = array(), $attachment = NULL, $options = array()) {
	//_views_json_debug_stop($options, "theme");
if ($options['format'] == 'simple') return theme('views_views_json_style_simple', $view, $rows, $attachment, $options);
if ($options['format'] == 'exhibit') return theme('views_views_json_style_exhibit', $view, $rows, $attachment, $options);
}

function template_preprocess_views_views_json_style_simple(&$vars) {	
  $view = &$vars["view"];
  $rows = &$vars["rows"];
  $options = $vars["options"];
  $base = $view->base_table;
  $root_object = $options["root_object"];
  $plaintext_output = $options["plaintext_output"];	
	$objects = array();
  foreach($rows as $row) {
  	$object = array($base => array());
  	/*Convert the $rows into a hierachial key=>value array*/
  	foreach ($row as $field) {
  	  if ($options["field_output"] == "normal") {
  	  	if ($field->label)
  	  	  $label = $plaintext_output ? strip_tags($field->label) : $field->label;
  	  	else $label = $plaintext_output ? strip_tags($field->id) : $field->id;  
  	  	$content = $plaintext_output ? strip_tags($field->content) : $field->content;
  	  }
  	  elseif ($options["field_output"] == "raw") {
  	  	$label = $plaintext_output ? strip_tags($field->id) : $field->id;
  	  	$content = $plaintext_output ? strip_tags($field->raw) : $field->raw;
  	  }	
  	$object[$base][$label] = $content;
  	}
  	$objects[] = $object;
  }	 
  $vars["rows"] = array($root_object => $objects);
  //_views_json_debug_stop($vars["rows"], "template preprocess simple");
}

function template_preprocess_views_views_json_style_exhibit(&$vars) {	
  $view = &$vars["view"];
  $rows = &$vars["rows"];
  $options = $vars["options"];
  $base = $view->base_table;
  $root_object = "Items";
  $plaintext_output = $options["plaintext_output"];	
	$objects = array();
  foreach($rows as $row) {
  	$object = array();
  	/*Convert the $rows into a hierachial key=>value array*/
  	foreach ($row as $field) {
  	  if ($options["field_output"] == "normal") {
  	  	if ($field->label)
  	  	  $label = $plaintext_output ? check_plain(strip_tags($field->label)) : $field->label;
  	  	else $label = $plaintext_output ?  check_plain(strip_tags($field->id)) : $field->id;
  	  	$content = $plaintext_output ?  check_plain(strip_tags($field->content)) : $field->content;
  	  }
  	  elseif ($options["field_output"] == "raw") {
  	  	$label = $plaintext_output ?  check_plain(strip_tags($field->id)) : $field->id;
  	  	$content = $plaintext_output ?  check_plain(strip_tags($field->raw)) : $field->raw;
  	  }
  	  $object[$label] = $content;	
  	}
  	if (!array_key_exists("label", $object)) $object["label"] = "Item";
    if (!array_key_exists("type", $object)) $object["type"] = $base;
    $objects[] = $object;
  }	
  $vars["rows"] = array($root_object => $objects); 
}

