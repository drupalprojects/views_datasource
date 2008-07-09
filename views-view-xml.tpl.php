<?php
// $Id$
/**
 * @file views-view-xml.tpl.php
 * View template to render view fields as XML. Supports raw XML and OPML schema.
 *
 * - $view: The view in use.
 * - $rows: The raw result objects from the query, with all data it fetched.
 * - $options: The options for the style passed in from the UI.
 *
 * @ingroup views_templates
 * @see views_xml.views.inc
 */

if (get_class($view->style_plugin->row_plugin) !== 'views_plugin_row_unformatted') {
  print ('<b style="color:red">The row plugin is not of type Unformatted.</b>');
  return;
}
else if (($view->style_plugin->row_plugin->options['separator']) !== '|') {
  print ('<b style="color:red">The row plugin separator is not "<span style="color:blue">|</span>" (you can set this in the options for the row style plugin.)</b>');
  return;
}

$nodes = array();
foreach($rows as $row) {
  $nodes[] = explode("|", trim($row));
  
}

if ($options['schema'] == 'raw') xml_raw_render($nodes, $view);
if ($options['schema'] == 'opml') xml_opml_render($nodes, $view);

function xml_raw_render($nodes, $view) {
	$xml .= '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
  $xml .= '<!-- generator="Drupal Views_Datasource.Module" -->'."\n";
  $xml .='<nodes>'."\n";  

  foreach ($nodes as $node) {
    $xml .= '  <node>'."\n";   
    foreach($node as $nodefield) {
      $nodefieldarray = explode(":", $nodefield);

      /*replace escaped colons with actual colon*/
      $nodefieldarray[0] = str_replace('#colon#', ':', $nodefieldarray[0]);
      $nodefieldarray[1] = str_replace('#colon#', ':', $nodefieldarray[1]);

      $label = views_xml_strip_illegal_chars($nodefieldarray[0]);
      $value = views_xml_strip_illegal_chars(views_xml_is_date($nodefieldarray[1]));
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

function xml_opml_render($nodes, $view) {
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
  foreach ($nodes as $node) {
    $xml .= '  <outline ';
    $fieldcount = 0;   
    foreach($node as $nodefield) {
    	$fieldcount++;
      $nodefieldarray = explode(":", $nodefield);
      /*replace escaped colons with actual colon*/
      $nodefieldarray[0] = str_replace('#colon#', ':', $nodefieldarray[0]);
      $nodefieldarray[1] = str_replace('#colon#', ':', $nodefieldarray[1]);

      $label = views_xml_strip_illegal_chars($nodefieldarray[0]);
      $value = views_xml_strip_illegal_chars(views_xml_is_date($nodefieldarray[1]));
      $label = str_replace('_value', '', str_replace("profile_values_profile_", '', $label)); //strip out Profile: from profile fields
      if (is_null($value) || ($value === '')) continue;
      $xml .= $label. '="'.$value.'" ';
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
