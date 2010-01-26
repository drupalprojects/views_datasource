<?php
// $Id$

/**
 * @file
 * View template to render view fields as JSON.  Supports simple JSON
 * and the Exhibit format.
 *
 * - $view: The view in use.
 * - $rows: The raw result objects from the query, with all data it fetched.
 * - $options: The options for the style passed in from the UI.
 *
 * @ingroup views_templates
 * @see views_json.views.inc
 */


$options['displaying_output'] = $view->override_path;

views_json_render($view->result, $options);


/**
 * Render a view's output as JSON.
 *
 * The function will directly output a JSON string instead of returning it.
 *
 * @param $items
 *   The collection of items to encode into JSON.
 * @param $options
 *   Render options.
 */
function views_json_render($items, $options) {
//  define('EXHIBIT_DATE_FORMAT', '%Y-%m-%d %H:%M:%S');

  $obj = new stdClass();

  // Do some preprocessing if necessary.

/* *
  // TO-DO:  Future stuff like date handling should go here.

  // trim(views_json_is_date($v))
  //      if (preg_match('/\d/', $var)) {
  //        if (strtotime($var)) {
  //          $var = gmstrftime(EXHIBIT_DATE_FORMAT, strtotime($value));
  //        }
  //      }

  // If input is a serialized date array, return a date string
  $num_items = count($items);
  for ($i = 0; $i < $num_items; ++$i) {
    if (!isset($items[$i]->type)) {
      if (is_string($input) && strpos($input, 'a:3:{s:5:"month"') === 0) {
        // serialized date array
        $date = unserialize($input);
        return format_date(mktime(0, 0, 0, $date['month'], $date['day'], $date['year']), 'custom', DATE_ISO8601);
      }
      return $input;
    }
  }
/* */

  if ($options['format'] === 'exhibit') {
    // We'll be rendering MIT Simile/Exhibit JSON.

    // Add required fields.
    $num_items = count($items);
    for ($i = 0; $i < $num_items; ++$i) {
      if (!isset($items[$i]->type)) {
        $items[$i]->type = (isset($node->type) ? $node->type : 'Item');
      }
      if (!isset($items[$i]->label)) {
        $items[$i]->label = (isset($node->type) ? $node->title : 'none');
      }
    }

    $obj->items = $items;
  }
  else {
    // We'll be rendering regular JSON.
    $obj->nodes = $items;
  }

  // Render the output.
  if ($options['displaying_output']) {
    // We're inside a live preview where the JSON is pretty-printed.
    print '<code>'. json_encode_formatted($obj) .'</code>';
  }
  else {
    $json = json_encode($obj);

    if ($options['using_views_api_mode']) {
      // We're in Views API mode.
      print $json;
    }
    else {
      // We want to send the JSON as a server response so switch the content
      // type and stop further processing of the page.
      drupal_set_header('Content-Type: application/json; charset=utf-8');
      print $json;
      module_invoke_all('exit');
      exit;
    }
  }
}


/**
 * Encodes JSON in a pretty-printed fashion.
 */
function json_encode_formatted($v, $depth = 0) {
  $base_indent  = '&nbsp;&nbsp;';
  $eol          = '<br />';
  $indent       = str_repeat($base_indent, $depth);

  // This is based on the drupal_to_js() function.
  switch (gettype($v)) {
    case 'boolean':
      // Lowercase is necessary!
      return $v ? 'true' : 'false';

    case 'integer':
    case 'double':
      return $v;

    case 'resource':
    case 'string':
      $search   = array('"', chr(92), chr(47), chr(8), chr(12), chr(13) . chr(10), chr(10), chr(13), chr(9));
      $replace  = array('\"', '\\', '\/', '\b', '\f', '\n', '\n', '\r', '\t');
      $output   = str_replace($search, $replace, $v);
/* *
      $output = str_replace(array("\r", "\n", "<", ">", "&"),
                           array('\r', '\n', '\x3c', '\x3e', '\x26'),
                           addslashes($output));
/* */
      return '"'. check_plain($output) .'"';

    case 'array':
      // Arrays in JSON can't be associative.  If the array is empty or if it
      // has sequential whole number keys starting with 0, it's not associative
      // so we can go ahead and convert it as an array.
      if (empty($v) || array_keys($v) === range(0, sizeof($v) - 1)) {
        $output = array();
        foreach ($v as $val) {
          $output[] = $indent . $base_indent . json_encode_formatted($val, $depth + 1);
        }
        return '['. (!empty($output) ? $eol . implode(','. $eol, $output) . $eol . $indent : '') .']';
      }
      // Otherwise, fall through to convert the array as an object.

    case 'object':
      $output = array();
      foreach ($v as $key => $val) {
        $output[] = $indent . $base_indent . json_encode_formatted(strval($key)) .' : '. json_encode_formatted($val, $depth + 1);
      }
      return '{'. (!empty($output) ? $eol . implode(','. $eol, $output) . $eol . $indent : '') .'}';

    default:
      return 'null';
  }
}
