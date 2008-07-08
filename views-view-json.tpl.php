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
 
$items = array();
foreach($rows as $row) {
//  print_r($row).EOL;
  $items[] = explode("|", trim($row));
  
}
if (count($items) != count($rows))
  return ("Did not get all rows (is the field separator '|' ?)"); 
//print_r($items);
//foreach ($items as $item) { 
//  print_r($item).PHP_EOL;
//	foreach($item as $itemfield) {
//		print($itemfield);
//		$itemfieldarray = explode(":", $itemfield);
//		print_r($itemfieldarray).PHP_EOL;
//		$label = $itemfieldarray[0]; $value=$itemfieldarray[1];
//		print $label." : ".$value;
//	}
//}

if ($options['format'] == 'Simple') json_simple_render($items, $view);
if ($options['format'] == 'Exhibit') json_exhibit_render($items, $view);

function json_simple_render($items, $view) {
  define('EXHIBIT_DATE_FORMAT', '%Y-%m-%d %H:%M:%S');	
	$json = '"nodes":'.str_repeat(" ", 4)."[\n";
	foreach($items as $item) {
		$json.= str_repeat(" ", 6)."{\n";
		foreach($item as $itemfield) {
			$itemfieldarray = explode(":", $itemfield);

			/*replace escaped colons with actual colon*/
			$itemfieldarray[0] = str_replace('#colon#', ':', $itemfieldarray[0]);
			$itemfieldarray[1] = str_replace('#colon#', ':', $itemfieldarray[1]);

			$label = trim(views_json_strip_illegal_chars(views_json_encode_special_chars($itemfieldarray[0])));
      $value = views_json_encode_special_chars(trim(views_json_is_date($itemfieldarray[1])));
      if (strtotime($value))
        $value = gmstrftime(EXHIBIT_DATE_FORMAT, strtotime($value));
      $label = str_replace('_value', '', str_replace("profile_values_profile_", '', $label)); //strip out Profile: from profile fields
      $json.=str_repeat(" ", 8).'"'.$label.'"'. " ".": ".'"'.$value.'"'.",\n";
		}
		$json.=str_repeat(" ", 6)."},\n\n";	
	}
	$json.=str_repeat(" ", 4)."]";
  /*
   * The following will cause an error in a live view preview - comment out if
   * debugging in there.
   */
  if ($view->override_path) { //inside a live preview so just output the text (not working yet)
    print $json; 
  }
  else { //real deal so switch the content type and stop further processing of the page
    drupal_set_header('Content-Type: text/javascript');
    print $json;
    module_invoke_all('exit');
    exit;
 }
	
}

function json_exhibit_render($items, $view) {
  define('EXHIBIT_DATE_FORMAT', '%Y-%m-%d %H:%M:%S');
  $json = "{\n".str_repeat(" ", 4).'"items"'." : ". "[\n";
  foreach ($items as $item) { 
  	$json.=str_repeat(" ", 8)."{\n";
  	$json.=str_repeat(" ", 12).'"type" '. " ".": ".'"'.'##type##'.'",'."\n";
    $json.=str_repeat(" ", 12).'"label" '. " "." : ".'"'.'##label##'.'",'."\n"; 
  	foreach ($item as $itemfield) {
  		$itemfieldarray = explode(":", $itemfield);
  		
      /*replace escaped colons with actual colon*/
      $itemfieldarray[0] = str_replace('#colon#', ':', $itemfieldarray[0]);
      $itemfieldarray[1] = str_replace('#colon#', ':', $itemfieldarray[1]);
  		
  		$label = trim(views_json_strip_illegal_chars(views_json_encode_special_chars($itemfieldarray[0]))); 
  		$value=views_json_encode_special_chars(trim(views_json_is_date($itemfieldarray[1])));
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
