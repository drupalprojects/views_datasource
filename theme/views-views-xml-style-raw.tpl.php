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
  $base = $view->base_table;
  $root = $options['root_element'];
  $plaintext_output = $options["plaintext_output"];
  $content_type = ($options['content_type'] == 'default') ? 'text/xml' : $options['content_type'];
  $header = $options['header'];
  $xml =  "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
  if ($header) $xml.= $header."\n"; 
	$xml .= "<$root>\n";
  foreach($rows as $row) {
		$xml .= ($options['element_output'] == 'nested') ? "  <$base>\n": "  <$base\n";
	  foreach($row as $id => $object) {
		  if ($options['field_output'] == 'normal')  {
		    $label = _views_xml_strip_illegal_xml_name_chars(check_plain(strip_tags($object->label)));
		    $content = ($plaintext_output ? check_plain(strip_tags($object->content)) : $object->content);
		  }
		  elseif ($options['field_output'] == 'raw') {
		    $label = _views_xml_strip_illegal_xml_name_chars(check_plain(strip_tags($id)));
		    $content = ($plaintext_output ? check_plain(strip_tags($object->raw)) : $object->raw);
		  }
		  if ($options['element_output'] == 'nested') {
			  //$xml .= ">\n";
			  $xml .= "    <$label>\n";
			  $xml .= "    ".(($options['escape_as_CDATA'] == 'yes') ? "      <![CDATA[$content]]>\n": "      ".$content."\n");
			  $xml .= "    </$label>\n";
		  }
		  elseif($options['element_output'] == 'attributes') {
        $content = _views_xml_strip_illegal_xml_attribute_value_chars($content);
		    $xml .= " $label=\"$content\" ";
		  }
	  }
	  $xml .= ($options['element_output'] == 'nested') ? "  </$base>\n": "/>\n";
	}
	$xml .= "</$root>\n";	
	if ($view->override_path) {       // inside live preview
    print htmlspecialchars($xml);
  }
  else {
    drupal_set_header("Content-Type: $content_type");
    print $xml;
    exit;
  }