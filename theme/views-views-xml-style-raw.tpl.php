<?php
// $Id $
/**
 * @file views-views-xml-style-raw.tpl.php
 * Default template for the Views XML style plugin using the raw schema
 *
 * - $view: The View object.
 * - $rows: Array of row objects as rendered by _views_xml_render_fields 
 *
 * @ingroup views_templates
 */
  //views_xml_test_debug_stop($view->base_table);
  $base = $view->base_table;
  $root = $options['root_element'];
  print "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"; 
	$xml .= "<$root>\n";
  foreach($rows as $row) {
		$xml .= ($options['element_output'] == 'nested') ? "  <$base>\n": "  <$base\n";
	  foreach($row as $id => $object) {
		  if ($options['field_output'] == 'normal')  {
		    $label = _views_xml_strip_illegal_xml_name_chars(check_plain(strip_tags($object->label)));
		    $content = $object->content;
		  }
		  elseif ($options['field_output'] == 'raw') {
		    $label = _views_xml_strip_illegal_xml_name_chars(check_plain(strip_tags($id)));
		    $content = $object->raw;
		  }
		  if ($options['element_output'] == 'nested') {
			  //$xml .= ">\n";
			  $xml .= "    <$label>\n";
			  $xml .= "    ".($options['escape_as_CDATA'] == 'yes') ? "      <![CDATA[$content]]>\n": "      ".$content."\n";
			  $xml .= "    </$label>\n";
		  }
		  elseif($options['element_output'] == 'attributes') {
		    $content = _views_xml_strip_illegal_xml_name_chars(check_plain(strip_tags($content)));
			  $xml .= " $label='$content' ";
		  }
	  }
	  $xml .= ($options['element_output'] == 'nested') ? "  </$base>\n": "/>\n";
	}
	$xml .= "</$root>\n";	
	if ($view->override_path) {       // inside live preview
    print htmlspecialchars($xml);
  }
  else {
    drupal_set_header('Content-Type: text/xml');
    print $xml;
    module_invoke_all('exit');
    exit;
  }