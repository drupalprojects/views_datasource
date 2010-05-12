<?php
// $Id$

/**
 * @file
 * View template to render view fields as rdf.
 *
 * - $view: The view in use.
 * - $rows: Array of row objects as rendered by _views_json_render_fields 
 * - $attachment: not used currently
 * - $options: The options for the style passed in from the UI.
 *
 * @ingroup views_templates
 * @see views_rdf.views.inc
 */

/**
 * Main theme function for the style plugin - just acts as a switch to pass control to the other themes
 * @param $view
 * @param $rows
 * @param $attachment
 * @param $options
 * @return unknown_type
 */
function theme_views_views_rdf_style(&$view, $rows = array(), $attachment = NULL, $options = array()) {
	if ($options['vocabulary'] == 'foaf') return theme('views_views_rdf_style_foaf', $view, $rows, $attachment, $options);
	if ($options['vocabulary'] == 'sioc') return theme('views_views_rdf_style_sioc', $view, $rows, $attachment, $options);
}

/**
 * Template preprocess for the FOAF vocabulary
 * @param $vars
 * @return unknown_type
 */
function template_preprocess_views_views_rdf_style_foaf(&$vars) {
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
  $vars["content_type"] = ($options['content_type'] == 'default') ? 'application/atom+rdf' : $options['content_type'];
  $header  = $options["header"];
  if (!$vars["header"]) {
  	$vars["title"] = check_plain(($view->get_title() ? $view->get_title(): $view->name));
  	$vars["id"] = check_url($view->get_url());
  	$vars["updated"] = format_date(time(), 'custom', DATE_ATOM);
  	$vars["author"] = _views_rdf_format_author($options["author"]);
    $vars['link'] = check_url(url($view->display_handler->get_path(), $url_options));
  } else $vars["header"] = $header;
	$persons = array();
  foreach($rows as $row) {
  	$person = array();
  	foreach ($row as $field) {
  		//_views_rdf_debug_stop($row);
  	  if ($options["field_output"] == "normal") {
  	  	if ($field->label)
  	  	  $label = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->label))) : $field->label;
  	  	else  $label = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->id))) : $field->id;
  	  	$content = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->content))) : $field->content;
  	  }
  	  elseif ($options["field_output"] == "raw") {
  	  	$label = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->id))) : $field->id;
  	  	$content = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->raw))) : $field->raw;
  	  }
  	  //if (($options["skip_empty_fields"] == TRUE) && (is_null($content) || $content === "")) continue;  	  
  	  if (stripos($label, 'firstname') !== FALSE) {
        $person["firstName"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'surname') !== FALSE) {
        $person["surName"] = check_plain($content);
        continue;
      }
      if ((stripos($label, 'name') !== FALSE) && ((!stripos($label, 'firstname')) && (!stripos($label, 'surname')))) {
      	$person["name"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'title') !== FALSE) {
        $person["title"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'nick') !== FALSE) {
        $person["nick"] = check_plain($content);
        continue;
      }
      if ((stripos($label, 'mbox') !== FALSE) && !(stripos($label, 'mbox_sha1sum') !== FALSE)) {
        $person["mbox"] = check_plain($content);
        continue;
      }
      if (((stripos($label, 'mail') !== FALSE) || (stripos($label, 'email') !== FALSE)) && (!array_key_exists('mbox', $person))) {
      	//_views_rdf_debug_stop($label);
        $person["mbox"] = check_plain($content);
        $person["mbox_sha1sum"] = md5("mailto:". check_plain($content));
        continue;
      }
      if (stripos($label, 'mbox_sha1sum') !== FALSE) {
        $person["mbox_sha1sum"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'openid') !== FALSE) {
        $person["openid"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'workplaceHomepage') !== FALSE) {
        $person["workplaceHomepage"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'homepage') !== FALSE) {
        //$rdf .= '  <foaf:homepage rdf:resource="'. $value .'"/>'."\n";
        $person["homepage"] = _views_rdf_strip_illegal_xml_attribute_value_chars(check_plain($content));
        continue;
      }
      if (stripos($label, 'weblog') !== FALSE) {
        //$rdf .= '  <foaf:weblog rdf:resource="'. $value .'"/>'."\n";
        $person["weblog"] = _views_rdf_strip_illegal_xml_attribute_value_chars(check_plain($content));
        continue;
      }
      if (stripos($label, 'img') !== FALSE) {
        //$rdf .= '  <foaf:img rdf:resource="'. $value .'"/>'."\n";
        //$rdf .= '  <foaf:depiction rdf:resource="'. $value .'"/>'."\n";
        $person["img"] = _views_rdf_strip_illegal_xml_attribute_value_chars(check_plain($content));
        continue;
      }
      if (stripos($label, 'member') !== FALSE) {
        //$rdf .= "  <foaf:member>$value</foaf:member>\n";
        $person["member"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'phone') !== FALSE) {
        //$rdf .= "  <foaf:phone>$value</foaf:phone>\n";
        $person["phone"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'jabberID') !== FALSE) {
        $person["jabberID"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'msnChatID') !== FALSE) {
        $person["msnChatID"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'aimChatID') !== FALSE) {
        $person["aimChatID"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'yahooChatID') !== FALSE) {
        $person["yahooChatID"] = check_plain($content);
        continue;
      }
    }  	
	  $persons[] = $person; 
  }//for
  $vars["persons"] = $persons;  
}
