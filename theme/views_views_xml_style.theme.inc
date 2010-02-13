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
	if ($options['schema'] == 'opml') return theme('views_views_xml_style_opml', $view, $rows, $attachment, $options);
}


function template_preprocess_views_views_xml_style_opml(&$vars) {
	global $user;
	global $base_url; 
	$view = &$vars["view"];
  $rows = &$vars["rows"];
  $options = &$vars["options"];
	$base = $view->base_table;
  $root = "opml";;
  $plaintext_output = $options["plaintext_output"];
  $vars["content_type"] = ($options['content_type'] == 'default') ? 'text/html' : $options['content_type'];
  $header  = $options["header"];
  if (!$vars["header"]) {
  	$vars["title"] = ($view->get_title() ? $view->get_title(): $view->name);
  	$vars["dateCreated"] = format_date(time(), 'custom', DATE_RFC822);
  	$vars["ownerName"] = $user->name;
  	$vars["ownerEmail"] = $user->mail;
  	$vars["ownerId"] = url("user/".$user->uid, array('absolute' => TRUE));
  	$vars["docs"] = url($view->get_url(), array('absolute' => TRUE)); //$base_url;
  } else $vars["header"] = $header;
	$outlines = array();
  foreach($rows as $row) {
  	$outline = array();
  	foreach ($row as $field) {
  	  if ($options["field_output"] == "normal") {
  	  	$label = $plaintext_output ? check_plain(strip_tags($field->label)) : $field->label;
  	  	$content = $plaintext_output ? check_plain(strip_tags($field->content)) : $field->content;
  	  }
  	  elseif ($options["field_output"] == "raw") {
  	  	$label = $plaintext_output ? check_plain(strip_tags($field->id)) : $field->id;
  	  	$content = $plaintext_output ? check_plain(strip_tags($field->raw)) : $field->raw;
  	  }
  	  $content = _views_xml_strip_illegal_xml_attribute_value_chars($content);
  	  $label = _views_xml_strip_illegal_xml_name_chars(check_plain(strip_tags($label)));
  	  if (($options["skip_empty_fields"] == TRUE) && (is_null($content) || $content == "")) continue;
  	  	  
		  /* OPML text attribute */
			if (!array_key_exists("text", $outline)) {
			  if (drupal_strtolower($label) == "text") {
	  	    $outline["text"] = $content;
	  	    continue;
	      }
	      elseif (drupal_strtolower($label) == "body") {
	        $outline["text"] = $content;
	  		  continue;
	  	  }
	  	  elseif ((drupal_strtolower($label) == "node_revisions_body")) {
	  		  $outline["text"] = $content;
	  		  continue;
	  	  }
	  	}
		  
	  	/* OPML type attribute */
	  	if (!array_key_exists("type", $outline)) {
	  	  if (drupal_strtolower($label) == "type") {
	  	    $outline["type"] = $content;
	  	    continue;
	      }
	      elseif (drupal_strtolower($label) == "node_type") {
	        $outline["type"] = $content;
	  		  continue;
	  	  }
	  	}
	  	
	  	/* OPML isComment attribute */
	  	if (!array_key_exists("isComment", $outline)) {
	      if (drupal_strtolower($label) == "iscomment") {
	  	    $outline["isComment"] = $content;
	  	    continue;
	      }	  		
	    }
	    
			/* OPML isBreakpoint attribute */
	  	if (!array_key_exists("isBreakpoint", $outline)) {
	      if (drupal_strtolower($label) == "isbreakpoint") {
	  	    $outline["isBreakpoint"] = $content;
	  	    continue;
	      }	  		
	    }

	    /* OPML created attribute */
	  	if (!array_key_exists("created", $outline)) {
	  		$value = NULL;
	      if (drupal_strtolower($label) == "created") {
	      	$value = $content;
	      }
	      elseif (drupal_strtolower($label) == "published") {
	      	$value = $content;
	      }
	      elseif (drupal_strtolower($label) == "node_created") {
	      	$value = $content;
	      }
	  		elseif (drupal_strtolower($label) == "postdate") {
	      	$value = $content;
	      }	      
	      if ($value) {
	        $value = $field->raw;
	      	if (intval($value))  // timestamp
            $value = format_date(intval($value), 'custom', DATE_RFC822);
          elseif (getdate($value))      // string date
            $value = format_date(strtotime($value), 'custom', DATE_RFC822);
	        //otherwise just pass the string as is
          $outline["created"] = $value;
	  	    continue;
	      }
	  	} 
      //Otherwise just use the $label and $content as attribute nam and value
	    $outline[$label] = $content; 	    		
		}
    $outlines[] = $outline;
  }	
  $vars["outlines"] = $outlines;   
}
