<?php
// $Id$
/**
 * @file views-view-json.tpl.php
 * View template to render view fields as JSON. Supports simple JSON and the Exhibit format.
 *
 * - $view: The view in use.
 * - $rows: The raw result objects from the query, with all data it fetched.
 * - $options: The options for the style passed in from the UI.
 *
 * @ingroup views_templates
 * @see views_json.views.inc
 */
 
if ($options['format'] == 'Simple') json_simple_render($view);
if ($options['format'] == 'Exhibit') json_exhibit_render($view);

function json_simple_render($view) {
  define('EXHIBIT_DATE_FORMAT', '%Y-%m-%d %H:%M:%S');	
	$json = '"nodes":'.str_repeat(" ", 4)."[\n";
	foreach ($view->result as $node) {
		$json.= str_repeat(" ", 6)."{\n";
		foreach($node as $field_label => $field_value) {
		  /*replace escaped colons with actual colon*/
			$label = str_replace('#colon#', ':', $field_label);
			$value = str_replace('#colon#', ':', $field_value);
			$label = trim(views_json_strip_illegal_chars(views_json_encode_special_chars($label)));
      $value = views_json_encode_special_chars(trim(views_json_is_date($value)));
      if (strtotime($value))
        $value = gmstrftime(EXHIBIT_DATE_FORMAT, strtotime($value));
      $label = str_replace('_value', '', str_replace("profile_values_profile_", '', $label)); //strip out Profile: from profile fields
      $json.=str_repeat(" ", 8).'"'.$label.'"'. " ".": ".'"'.$value.'"'.",\n";
		}
		$json.=str_repeat(" ", 6)."},\n\n";	
	}
	$json.=str_repeat(" ", 4)."]";
  
  if ($view->override_path) { //inside a live preview so just output the text
    print $json; 
  }
  else { //real deal so switch the content type and stop further processing of the page
    drupal_set_header('Content-Type: text/javascript');
    print $json;
    module_invoke_all('exit');
    exit;
 }
	
}

function json_exhibit_render($view) {
  define('EXHIBIT_DATE_FORMAT', '%Y-%m-%d %H:%M:%S');
  $json = "{\n".str_repeat(" ", 4).'"items"'." : ". "[\n";
  foreach ($view->result as $node) { 
  	$json.=str_repeat(" ", 8)."{\n";
  	$json.=str_repeat(" ", 12).'"type" '. " ".": ".'"'.'##type##'.'",'."\n";
    $json.=str_repeat(" ", 12).'"label" '. " "." : ".'"'.'##label##'.'",'."\n"; 
    foreach($node as $field_label => $field_value) {
      /*replace escaped colons with actual colon*/
      $label = str_replace('#colon#', ':', $field_label);
      $value = str_replace('#colon#', ':', $field_value);
      $label = trim(views_json_strip_illegal_chars(views_json_encode_special_chars($label)));
      $value = views_json_encode_special_chars(trim(views_json_is_date($value)));
  		//if (empty($value)) continue;        
      if (strtotime($value))
        $value = gmstrftime(EXHIBIT_DATE_FORMAT, strtotime($value));
      $label = str_replace('_value', '', str_replace("profile_values_profile_", '', $label)); //strip out Profile: from profile fields
      if ($label == 'type') $json = str_replace('##type##', $value, $json);
      elseif ($label == 'label') $json = str_replace('##label##', $value, $json);
      else $json.=str_repeat(" ", 12).'"'.$label.'"'. " ".": ".'"'.$value.'"'.",\n";
  	}
   if (strpos($json, '##type##') !== false) 
    $json = str_replace('##type##', 'Item', $json);
   if (strpos($json, '##label##') !== false) 
    $json = str_replace('##label##',  'none', $json);    	
   $json = rtrim($json, ",\n");
   $json.="\n";
   $json.=str_repeat(" ", 8)."},\n";
  }
  $json = rtrim($json, ",\n");
  $json.="\n";
  $json.=str_repeat(" ", 4)."]\n}";
  /*
   * The following will cause an error in a live view preview - comment out if
   * debugging in there.
   */
  if ($view->override_path) { //inside a live preview so just output the text
  	print $json; 
  }
  else { //real deal so switch the content type and stop further processing of the page
    drupal_set_header('Content-Type: text/javascript');
    print $json;
    module_invoke_all('exit');
    exit;
 }

}
