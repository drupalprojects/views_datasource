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

json_render($view->result, $options);


/**
 * Render JSON.
 *
 * The function will directly output a JSON string instead of returning it.
 *
 * @param $items
 *   The collection of items to encode into JSON.
 * @param $options
 *   Render options.
 */
function json_render($items, $options) {
  $obj = new stdClass();

  // do some preprocessing if necessary

  if ($options['format'] === 'exhibit') {
    // we'll be rendering MIT Simile/Exhibit JSON

    // add required fields
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
    // we'll be rendering regular JSON
    $obj->nodes = $items;
  }

  // render the output
  if ($options['displaying_output']) {
    // we're inside a live preview where the JSON is pretty-printed
    print '<code>'. _json_preview_render($obj) .'</code>';
  }
  else {
    if (function_exists('json_encode')) {
      $json = json_encode($obj);
    }
    else {
      $json = _json_render($obj);
    }

    if ($options['using_coder']) {
      // we're in "coder" mode
      print $json;
    }
    else {
      // we want to send the JSON as a server response so switch the content type
      // and stop further processing of the page.
      drupal_set_header('Content-Type: application/json');
      print $json;
      module_invoke_all('exit');
      exit;
    }
  }
}


/**
 * Helper function that builds the JSON.
 */
function _json_render($var) {
//  define('EXHIBIT_DATE_FORMAT', '%Y-%m-%d %H:%M:%S');

  // hack of the elegant drupal_to_js() function
  switch (gettype($var)) {
    case 'boolean':
      // lowercase is necessary!
      return $var ? 'true' : 'false';
    case 'integer':
    case 'double':
      return $var;
    case 'resource':
    case 'string':
//      if (preg_match('/\d/', $var)) {
//        if (strtotime($var)) {
//          $var = gmstrftime(EXHIBIT_DATE_FORMAT, strtotime($value));
//        }
//      }

      $value = views_json_encode_special_chars(trim(views_json_is_date($var)));
//      $value = str_replace(array("\r", "\n", "<", ">", "&"),
//                           array('\r', '\n', '\x3c', '\x3e', '\x26'),
//                           addslashes($value));
      return '"'. $value .'"';
    case 'array':
      // Arrays in JSON can't be associative.  If the array is empty or if it
      // has sequential whole number keys starting with 0, it's not associative
      // so we can go ahead and convert it as an array.
      if (empty($var) || array_keys($var) === range(0, sizeof($var) - 1)) {
        $output = array();
        foreach ($var as $v) {
          $output[] = _json_render($v);
        }
        return '['. implode(',', $output) .']';
      }
      // Otherwise, fall through to convert the array as an object.
    case 'object':
      $output = array();
      foreach ($var as $k => $v) {
        $output[] = trim(views_json_check_label(_json_render(strval($k)))) .':'. _json_render($v);
      }
      return '{'. implode(',', $output) .'}';
    default:
      return 'null';
  }
}


/**
 * Helper function that builds pretty-printed JSON.
 */
function _json_preview_render($var, $depth = 0) {
//  define('EXHIBIT_DATE_FORMAT', '%Y-%m-%d %H:%M:%S');

  $base_indent  = '&nbsp;&nbsp;';
  $eol          = '<br />';
  $indent       = str_repeat($base_indent, $depth);

  // hack of the elegant drupal_to_js() function
  switch (gettype($var)) {
    case 'boolean':
      // lowercase is necessary!
      return $var ? 'true' : 'false';
    case 'integer':
    case 'double':
      return $var;
    case 'resource':
    case 'string':
//      if (preg_match('/\d/', $var)) {
//        if (strtotime($var)) {
//          $var = gmstrftime(EXHIBIT_DATE_FORMAT, strtotime($value));
//        }
//      }

      $value = views_json_encode_special_chars(trim(views_json_is_date($var)));
//      $value = str_replace(array("\r", "\n", "<", ">", "&"),
//                           array('\r', '\n', '\x3c', '\x3e', '\x26'),
//                           addslashes($value));
      return '"'. check_plain($value) .'"';
    case 'array':
      // Arrays in JSON can't be associative.  If the array is empty or if it
      // has sequential whole number keys starting with 0, it's not associative
      // so we can go ahead and convert it as an array.
      if (empty($var) || array_keys($var) === range(0, sizeof($var) - 1)) {
        $output = array();
        foreach ($var as $v) {
          $output[] = $indent . $base_indent . _json_preview_render($v, $depth + 1);
        }
        return '['. (!empty($output) ? $eol . implode(','. $eol, $output) . $eol . $indent : '') .']';
      }
      // Otherwise, fall through to convert the array as an object.
    case 'object':
      $output = array();
      foreach ($var as $k => $v) {
        $output[] = $indent . $base_indent . trim(views_json_check_label(_json_preview_render(strval($k)))) .' : '. _json_preview_render($v, $depth + 1);
      }
      return '{'. (!empty($output) ? $eol . implode(','. $eol, $output) . $eol . $indent : '') .'}';
    default:
      return 'null';
  }
}
