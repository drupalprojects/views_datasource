<?php
// $Id$
/**
 * @file views-view-xml.tpl.php
 * View template to render view fields as XML. Supports raw XML, OPML and Atoma schema.
 *
 * - $view: The view in use.
 * - $rows: The raw result objects from the query, with all data it fetched.
 * - $options: The options for the style passed in from the UI.
 *
 * @ingroup views_templates
 * @see views_xml.views.inc
 */

/*
if (get_class($view->style_plugin->row_plugin) !== 'views_plugin_row_unformatted') {
  print ('<b style="color:red">The row plugin is not of type Unformatted.</b>');
  return;
}
else if (($view->style_plugin->row_plugin->options['separator']) !== '|') {
  print ('<b style="color:red">The row plugin separator is not "<span style="color:blue">|</span>" (you can set this in the options for the row style plugin.)</b>');
  return;
}

*/
if ($options['schema'] == 'raw') xml_raw_render($view);
if ($options['schema'] == 'opml') xml_opml_render($view);
if ($options['schema'] == 'atom') xml_atom_render($view);

function xml_raw_render($view) {
	$xml .= '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
  $xml .= '<!-- generator="Drupal Views_Datasource.Module" -->'."\n";
  $xml .='<nodes>'."\n";  
  
  foreach ($view->result as $node) {
    $xml .= '  <node>'."\n";   
    foreach($node as $label => $value) {
      $label = views_xml_strip_illegal_chars($label);
      $value = views_xml_strip_illegal_chars(views_xml_is_date($value));
      if (strtotime($value))
        $value = date(DATE_ISO8601, strtotime($value));
      $label = str_replace('_value', '', str_replace("profile_values_profile_", '', $label)); //strip out Profile: from profile fields
      if (is_null($value) || ($value === '')) continue;
      $xml .= "    <$label>$value</$label>\n";
    }
  $xml .= '  </node>'."\n";
  }
  $xml .='</nodes>'."\n";
  if ($view->override_path) //inside live preview 
    print htmlspecialchars($xml);
  else {  
   drupal_set_header('Content-Type: text/xml');
   print $xml;
   module_invoke_all('exit');
   exit;
  }
}

function xml_opml_render($view) {
	//var_dump($view);
	//return;
  global $user;
	$xml .= '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
  $xml .= '<!-- generator="Drupal Views_Datasource.Module" -->'."\n";
  $xml .='<opml version="1.0">'."\n";  
	$xml .='<head>'."\n";
	$xml .='  <title>'.variable_get('site_name', 'drupal').'-'.$view->name.'</title>'."\n";
	$xml .='  <ownerName>'.$user->name.'</ownerName>'."\n";
	$xml .='  <ownerEmail>'.$user->mail.'</ownerEmail>'."\n";
	$xml .='  <dateCreated>'.date(DATE_ISO8601, time()).'</dateCreated>'."\n";
	$xml .='</head>'."\n";
	$xml .='<body>'."\n";
  foreach ($view->result as $node) {
    $xml .= '  <outline ';
    $fieldcount = 0;   
    foreach($node as $label => $value) {
    	$fieldcount++;
      $label = views_xml_strip_illegal_chars($label);
      $value = views_xml_strip_illegal_chars(views_xml_is_date($value));
      $label = str_replace('_value', '', str_replace("profile_values_profile_", '', $label)); //strip out Profile: from profile fields
      if (is_null($value) || ($value === '')) continue;
      if ((strtolower($label) == 'text') || (strtolower($label) == 'node_revisions_body'))
        $label = "text";
      if ((strtolower($label) == 'type') || (strtolower($label) == 'node_type'))  
        $label = "type";
      $xml .= $label. '="'.preg_replace('/[^A-Za-z0-9 ]/','',$value).'" ';
    }
  $xml .= '/>'."\n";
  }
	$xml .='</body>'."\n";
  $xml .='</opml>'."\n";
	if ($view->override_path) //inside live preview 
	  print htmlspecialchars($xml);
	else {  
   drupal_set_header('Content-Type: text/xml');
   print $xml;
   module_invoke_all('exit');
   exit;
	}
}

function xml_atom_render($view) {
	global $base_url;
	
  $xml .= '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
  $xml .= '<!-- generator="Drupal Views_Datasource.Module" -->'."\n";
  $xml .='<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="en">'."\n";  
  $xml .='  <title>'.$view->name.'</title>'."\n";
  $xml .='  <link rel="alternate" type="text/html" href="'.$base_url.'"/>'."\n";
  $xml .='  <link rel="self" type="application/atom+xml" href="'.$base_url.'/'.$view->display_handler->options['path'].'"/>'."\n";
  $xml .='  <id>'.$base_url.'/'.$view->display_handler->options['path'].'</id>'."\n";//use path as id
  $xml .='  <updated>###feed_updated###</updated>'."\n"; //will set later 
  $xml .='  <generator>Views Datasource module</generator>'."\n"; 
  $feed_last_updated = 0;
  foreach ($view->result as $node) {
  	$entry = array();   
    foreach($node as $label=>$value) {
      $label = views_xml_strip_illegal_chars($label);
      $value = views_xml_strip_illegal_chars(views_xml_is_date($value));
      if (strtotime($value)) {//string date
        $value = date(DATE_ISO8601, strtotime($value));
      }
      $label = str_replace('_value', '', str_replace("profile_values_profile_", '', $label)); //strip out Profile: from profile fields
      if (is_null($value) || ($value === '')) continue;
      
      if (strtolower($label) == 'nid') $entry['nid'] = $value;
      if ((strtolower($label) == 'updated') || (strtolower($label) == 'updated date') || (strtolower($label) == 'node_changed')) {
      	if (intval($value)) //timestamp
      	  $entry['updated'] =  intval($value) ;
      	else if(getdate($value)) { //string date
      		$entry['updated'] = strtotime($value);
      	}
      } 
      if ((strtolower($label) == 'title') || (strtolower($label) == 'node_title')) $entry['title'] = $value;
      if (strtolower($label) == 'link') $entry['link'] = $value;
      if ((strtolower($label) == 'published') || (strtolower($label) == 'node_created')) {
        if (intval($value)) //timestamp
          $entry['published'] =  intval($value) ;
        else if(getdate($value)) { //string date
          $entry['published'] = strtotime($value);
        }
      } 
      if ((strtolower($label) == 'author') || (strtolower($label) == 'users_name')) $entry['author'] = $value;
      if ((strtolower($label) == 'content') || (strtolower($label) == 'node_revisions_body')) $entry['content'] = $value;
      //if ((strtolower($label) == 'summary') || (strtolower($label) == 'node_teaser') || (strtolower($label) == 'node_revisions_teaser')) $entry['summary'] = $value;
    }
    if (isset($entry['nid']) && (isset($entry['updated'])) && (isset($entry['link'])) && (isset($entry['title'])) && (isset($entry['published']))) {
   	  if (parse_url($entry['link'])) 
   	    $link = $entry['link'];	
   	  else {
        print ('<b style="color:red">The link URL is not valid.</b>');
        return;
   	  }
    }
    elseif (isset($entry['nid']) && (isset($entry['updated'])) && (isset($entry['title'])) && (isset($entry['title']))) { //make the entry path with base_url + nid {
   	  $entry['link'] = $base_url.'/index.php?q='.$entry['nid'];
    }
    else {
      print ('<b style="color:red">The fields "nid", "title" "published" and "updated" must exist.</b>');
      return;  
    }
    $link = $entry['link'];
    $link_url = parse_url($link);
    $nid = $entry['nid'];
    $updated = $entry['updated'];
    if ($updated > $feed_last_updated) $feed_last_updated = $updated; //Overall feed updated is the most recent node updated timestamp
    $title = $entry['title'];
    $published = $entry['published'];
    $author = $entry['author'];
    $content = $entry['content'];
    $summary = $entry['summary'];
    
    //Create an id for the entry using tag URIs
    $id = 'tag:'.$link_url['host'].','.date('Y-m-d', $updated).':'.$link_url['path'].'?'.$link_url['query'];
    $xml .= '  <entry>'."\n";
    $xml .= '    <id>'.$id.'</id>'."\n"; 
    $xml .= '    <updated>'.date(DATE_ATOM, $updated).'</updated>'."\n";
    $xml .= '    <title>'.$title.'</title>'."\n";
    $xml .= '    <link rel="self" alternate="'.$link.'"/>'."\n";
    $xml .= '    <published>'.date(DATE_ATOM, $published).'</published>'."\n";
    if ($author) $xml .= '    <author><name>'.$author.'</name></author>'."\n";
    if ($content)$xml .= '    <content type="html" xml:base="'.$base_url.'"><![CDATA['.$content.']]></content>'."\n";
    if ($summary)$xml .= '    <summary type="html" xml:base="'.$base_url.'"><![CDATA['.$summary.']]></summary>'."\n";
    $xml .= '  </entry>'."\n";
  }
  $xml .='</feed>'."\n";
  $xml = str_replace('###feed_updated###', date(DATE_ATOM, $feed_last_updated), $xml);
  if ($view->override_path) //inside live preview 
    print htmlspecialchars($xml);
  else {  
   drupal_set_header('Content-Type: text/xml');
   print $xml;
   //var_dump($view);   
   module_invoke_all('exit');
   exit;
  }
}
