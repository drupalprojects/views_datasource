<?php
// $Id$
/**
 * @file views-view-rdf.tpl.php
 * View template to render views as RDF. Supports FOAF vocabulary.
 *
 * - $view: The view in use.
 * - $rows: The raw result objects from the query, with all data it fetched.
 * - $options: The options for the style passed in from the UI.
 *
 * @ingroup views_templates
 * @see views_rdf.views.inc
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

if ($options['vocabulary'] == 'FOAF') rdf_foaf_xml_render($nodes, $view);

/**
 * Render nodes as FOAF in XML
 *
 * @param array $nodes
 * @return none
 */
function rdf_foaf_xml_render($nodes, $view) {
  $xml .= '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
  $xml .= '<!-- generator="Drupal Views_Datasource.Module" -->'."\n";
  $xml .= '<rdf:RDF xmlns="http://xmlns.com/foaf/0.1"'."\n";
  $xml .= '  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"'."\n";
  $xml .= '  xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"'."\n";
  $xml .= '  xmlns:dc="http://purl.org/dc/elements/1.1/"'."\n";
  $xml .= '  xmlns:foaf="http://xmlns.com/foaf/0.1/">'."\n";
  foreach ($nodes as $node) {
    $xml.="<foaf:Person>\n";
    foreach($node as $nodefield) {
      $nodefieldarray = explode(":", $nodefield);

      /*replace escaped colons with actual colon*/
      $nodefieldarray[0] = str_replace('#colon#', ':', $nodefieldarray[0]);
      $nodefieldarray[1] = str_replace('#colon#', ':', $nodefieldarray[1]);

      $label = views_rdf_strip_illegal_chars($nodefieldarray[0]);
      $value = views_rdf_strip_illegal_chars(views_rdf_is_date($nodefieldarray[1]));
      if (strtotime($value))
        $value = date(DATE_ISO8601, strtotime($value));
      if (is_null($value) || ($value === '')) continue;
      if (stripos($label, 'firstname') !== false) {
        $xml.="  <foaf:firstName>$value</foaf:firstName>\n";
        continue;
      }      
      if (stripos($label, 'surname') !== false) {
        $xml.="  <foaf:surName>$value</foaf:surName>\n";
        continue;
      }      
      if ((stripos($label, 'name') !== false) && !((stripos($label, 'surname') !== false) || (stripos($label, 'firstname') !== false))) {
        if (stripos($xml, "<foaf:name>") == false)
          $xml.="  <foaf:name>$value</foaf:name>\n";
        continue;
      }
      if (stripos($label, 'title') !== false) {
        $xml.="  <foaf:title>$value</foaf:title>\n";
        continue;
      }
      if (stripos($label, 'nick') !== false) {
        $xml.="  <foaf:nick>$value</foaf:nick>\n";
        continue;
      }
      if (stripos($label, 'mbox_sha1sum') !== false) {
        $xml.="  <foaf:mbox_sha1sum>$value</foaf:mbox_sha1sum>\n";
        continue;
      }
      if ((stripos($label, 'mbox') !== false) && !(stripos($label, 'mbox_sha1sum') !== false)) {
        $xml.="  <foaf:mbox>$value</foaf:mbox>\n";
        continue;
      }
      if (stripos($label, 'openid') !== false) {
        $xml.="  <foaf:openid>$value</foaf:openid>\n";
        continue;
      }
      if (strpos($label, 'workplaceHomepage') !== false) {
        $xml.='  <foaf:workplaceHomepage rdf:resource="'.$value.'"/>'."\n";
        continue;
      }
      if (strpos($label, 'homepage') !== false) {
        $xml.='  <foaf:homepage rdf:resource="'.$value.'"/>'."\n";
        continue;
      } 
      if (stripos($label, 'weblog') !== false) {
        $xml.='  <foaf:weblog rdf:resource="'.$value.'"/>'."\n";
        continue;
      }
      if (strpos($label, 'img') !== false) {
        $xml.='  <foaf:img rdf:resource="'.$value.'"/>'."\n";
        $xml.='  <foaf:depiction rdf:resource="'.$value.'"/>'."\n";
        continue;
      }
      if (stripos($label, 'member') !== false) {
        $xml.="  <foaf:member>$value</foaf:member>\n";
        continue;
      }      
      if (stripos($label, 'phone') !== false) {
        $xml.="  <foaf:phone>$value</foaf:phone>\n";
        continue;
      }
      if (stripos($label, 'jabberID') !== false) {
        $xml.="  <foaf:jabberID>$value</foaf:jabberID>\n";
        continue;
      }
      if (stripos($label, 'msnChatID') !== false) {
        $xml.="  <foaf:msnChatID>$value</foaf:msnChatID>\n";
        continue;
      }
      if (stripos($label, 'aimChatID') !== false) {
        $xml.="  <foaf:aimChatID>$value</foaf:aimChatID>\n";
        continue;
      }
      if (stripos($label, 'yahooChatID') !== false) {
        $xml.="  <foaf:yahooChatID>$value</foaf:yahooChatID>\n";
        continue;
      }            
    }
    $xml.="</foaf:Person>\n";
  }
  $xml.="</rdf:RDF>\n";
  if ($view->override_path) //inside live preview 
    print htmlspecialchars($xml);
  else {  
  drupal_set_header('Content-Type: text/xml');
   print $xml;
   module_invoke_all('exit');
   exit;
  }  
  
}
  