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

/**
 * Main theme function for the style plugin - just acts as a switch to pass control to the other themes
 * @param $view
 * @param $rows
 * @param $attachment
 * @param $options
 * @return unknown_type
 */
function theme_views_views_xml_style(&$view, $rows = array(), $attachment = NULL, $options = array()) {
	if ($options['schema'] == 'raw') return theme('views_views_xml_style_raw', $view, $rows, $attachment, $options);
	if ($options['schema'] == 'opml') return theme('views_views_xml_style_opml', $view, $rows, $attachment, $options);
	if ($options['schema'] == 'atom') return theme('views_views_xml_style_atom', $view, $rows, $attachment, $options);
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
  $author = _views_xml_format_author($options["author"]);
  if (!$vars["header"]) {
  	$vars["title"] = ($view->get_title() ? $view->get_title(): $view->name);
  	$vars["dateCreated"] = format_date(time(), 'custom', DATE_RFC822);
  	$vars["ownerName"] = $author["name"];
  	$vars["ownerEmail"] = $author["email"];
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

/**
 * Template preprocess for the Atom format
 * @param $vars
 * @return unknown_type
 */
function template_preprocess_views_views_xml_style_atom(&$vars) {
	global $user;
	global $base_url; 
  $view = &$vars["view"];
  $rows = &$vars["rows"];
  $options = &$vars["options"];
  $link = $view->display_handler->get_path();
  $url_options = array('absolute' => TRUE);
  if (!empty($view->exposed_raw_input)) {
    $url_options['query'] = $view->exposed_raw_input;
  }
	$base = $view->base_table;
  $root = "feed";
  $plaintext_output = $options["plaintext_output"];
  $vars["content_type"] = ($options['content_type'] == 'default') ? 'application/atom+xml' : $options['content_type'];
  $header  = $options["header"];
  if (!$vars["header"]) {
  	$vars["title"] = check_plain(($view->get_title() ? $view->get_title(): $view->name));
  	$vars["id"] = check_url($view->get_url());
  	$vars["updated"] = format_date(time(), 'custom', DATE_ATOM);
  	$vars["author"] = _views_xml_format_author($options["author"]);
    $vars['link'] = check_url(url($view->display_handler->get_path(), $url_options));
  } else $vars["header"] = $header;
	$entries = array();
  foreach($rows as $row) {
  	$entry = array();
  	foreach ($row as $field) {
  	  if ($options["field_output"] == "normal") {
  	  	$label = $plaintext_output ? check_plain(strip_tags($field->label)) : $field->label;
  	  	$content = $plaintext_output ? check_plain(strip_tags($field->content)) : $field->content;
  	  }
  	  elseif ($options["field_output"] == "raw") {
  	  	$label = $plaintext_output ? check_plain(strip_tags($field->id)) : $field->id;
  	  	$content = $plaintext_output ? check_plain(strip_tags($field->raw)) : $field->raw;
  	  }
  	  $label = _views_xml_strip_illegal_xml_name_chars(check_plain(strip_tags($label)));
  	  //if (($options["skip_empty_fields"] == TRUE) && (is_null($content) || $content === "")) continue;
  	  
  	  /* Atom id attribute */			
  	  if (!array_key_exists("id", $entry)) {
			  if (drupal_strtolower($label) == "id") {
	  	    $entry["id"] = $content;
	  	    continue;
	      }
	  	}

  	  /* Atom title attribute */			
  	  if (!array_key_exists("title", $entry)) {
			  if (drupal_strtolower($label) == "title") {
	  	    $entry["title"] = $content;
	  	    continue;
	      }
	  	}

	  	/* Atom updated attribute */
	  	if (!array_key_exists("updated", $entry)) {
	  		$value = NULL;
	      if (drupal_strtolower($label) == "updated") {
	      	$value = $content;
	      }
	      if ($value) {
	        $value = $field->raw; //@todo Hmmm
	      	if (intval($value))  // timestamp
            $value = format_date(intval($value), 'custom', DATE_ATOM);
          elseif (getdate($value))      // string date
            $value = format_date(strtotime($value), 'custom', DATE_ATOM);
	        //otherwise just pass the string as is
          $entry["updated"] = $value;
	  	    continue;
	      }
	  	}
  	  
  	  /* Atom author attribute */			
  	  if (!array_key_exists("author", $entry)) {
			  if (drupal_strtolower($label) == "author") {
	  	    $entry["author"] = _views_xml_format_author($content);
	  	    continue;
	      }
	      elseif (drupal_strtolower($label) == "uid") {
	  	    $entry["author"] = _views_xml_format_author($content);
	  		  continue;
	  	  }
	  	}
	  	
	  	/* Atom link attribute */
  	  if (!array_key_exists("link", $entry)) {
			  if (drupal_strtolower($label) == "link") {
	  	    $entry["link"] = $content;
	  	    continue;
	      }
	  	}
	  	
  		/* Atom content attribute */
  	  if (!array_key_exists("content", $entry)) {
			  if (drupal_strtolower($label) == "content") {
	  	    $entry["content"] = $content;
	  	    continue;
	      }
	  	}
	  	
  	  /* Atom summary attribute */
  	  if (!array_key_exists("summary", $entry)) {
			  if (drupal_strtolower($label) == "summary") {
	  	    $entry["summary"] = $content;
	  	    continue;
	      }
	  	}	  	
	  		  	
	  	
      /* Otherwise just use the $label and $content as element name and value */
	    $entry[strtolower($label)] = $content;	  		  	   	
  	} //for
	  
	  /* Check if id attribute exists and if not fall back to nid and try to use that */
  	if (!array_key_exists("id", $entry)) {
      if (array_key_exists("nid", $entry)) {
      	$nid = $entry["nid"];
	  	  $entry["id"] = "tag:$base_url,node-$nid";
	    }
  	}
  	
	  /* Check if title attribute exists and if not fall back to node_title */
  	if (!array_key_exists("title", $entry)) {
      if (array_key_exists("node_title", $entry)) {
	  	  $entry["title"] = $entry["node_title"];
	    }
  	}
  	
  	/* Check if link attribute exists and if not fall back to constructing a link from the nid */
  	if (!array_key_exists("link", $entry)) {
      if (array_key_exists("nid", $entry)) {
      	$nid = $entry["nid"];
	  	  $entry["link"] = url("node/$nid", array('absolute' => TRUE));
	    }
  	}
  	
  	/* Check if updated attribute exists and if not fall back to other possible fields */
	  if (!array_key_exists("updated", $entry)) {
	    $value = NULL;
	  	if (array_key_exists("last_updated", $entry)) {
	      $value = $entry["last_updated"];
	    }
	    elseif (array_key_exists("updateddate", $entry)) {
	      $value = $entry["updateddate"];
	    }
	  	elseif (array_key_exists("changed", $entry)) {
	      $value = $entry["changed"];
	    }
	    elseif (array_key_exists("updatedcommenteddate", $entry)) {
	      $value = $entry["updatedcommenteddate"];
	    }
	    elseif (array_key_exists("lastcommenttime", $entry)) {
	      $value = $entry["lastcommenttime"];
	    }
	  	elseif (array_key_exists("last_comment_timestamp", $entry)) {
	      $value = $entry["last_comment_timestamp"];
	    }
	    if ($value) {
	      if (is_numeric($value))  { // timestamp
          //_views_xml_debug_stop($value);  
	        $value = format_date(intval($value), 'custom', DATE_ATOM);
	      }
        else  {    // string date
          $timestamp = strtotime($value);
        	if ($timestamp) $value = format_date($timestamp, 'custom', DATE_ATOM);
          //_views_xml_debug_stop($timestamp);
        }
        $entry["updated"] = $value;
        //_views_xml_debug_stop($entry["updated"]);
	    }
  	}
  	
  	/* Check if content attribute exists and if not fall back to using node body */
  	if (!array_key_exists("content", $entry)) {
      if (array_key_exists("body", $entry)) {
	  	  $entry["content"] = $entry["body"];
	    }
  	  if (array_key_exists("node_body", $entry)) {
	  	  $entry["content"] = $entry["node_body"];
	    }
  	  if (array_key_exists("node_revisions_body", $entry)) {
	  	  $entry["content"] = $entry["node_revisions_body"];
	    }	    
  	}
  	
    /* Check if summary attribute exists and if not fall back to using node teader */
  	if (!array_key_exists("summary", $entry)) {
      if (array_key_exists("node_teaser", $entry)) {
	  	  $entry["summary"] = $entry["node_teaser"];
	    }
  	  if (array_key_exists("node_revisions_teaser", $entry)) {
	  	  $entry["summary"] = $entry["node_revisions_teaser"];
	    }
  	}  	
  	
	  $entry["id"] = check_plain($entry["id"]); $entry["title"] = check_plain($entry["title"]); $entry["updated"] = check_plain($entry["updated"]);
	  //_views_xml_debug_stop($entry);
	  $entries[] = $entry;
	  
  }//for
  //_views_xml_debug_stop($entries);
  $vars["entries"] = $entries;  
}
