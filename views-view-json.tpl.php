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


switch ($options['format']) {
  // Simple (Coder)
  case 'simple-coder':
    json_simple_render($view, TRUE);
    break;

  // MIT Simile/Exhibit
  case 'exhibit':
    json_exhibit_render($view);
    break;

  // MIT Simile/Exhibit (Coder)
  case 'exhibit-coder':
    json_exhibit_render($view, TRUE);
    break;

  // Simple
  default:
    json_simple_render($view);
}


function json_simple_render($view, $coder_mode = FALSE) {
  define('EXHIBIT_DATE_FORMAT', '%Y-%m-%d %H:%M:%S');

  $eol      = '';
  $spaces1  = '';
  $spaces2  = '';
  $spaces4  = '';

  if ($view->override_path) {
    // inside a live preview so use HTML line breaks and space accordingly
    $eol      = '<br />';
    $spaces1  = ' ';
    $spaces2  = str_repeat('&nbsp;', 2);
    $spaces4  = str_repeat('&nbsp;', 4);
  }

  $json = '{' . $spaces1 .'"nodes"'. $spaces1 .':'. $spaces1 .'['. $eol;

  $more_view_results = FALSE;
  foreach ($view->result as $node) {
    $json .= ($more_view_results ? ','. $eol : '') . $spaces2 .'{'. $eol;

    $more_fields = FALSE;
    foreach ($node as $field_label => $field_value) {
      $label = trim(views_json_strip_illegal_chars(views_json_encode_special_chars($field_label)));
      $value = views_json_encode_special_chars(trim(views_json_is_date($field_value)));
      if ((is_null($value)) || ($value == '')) continue;

//    if (preg_match('/\d/', $value)) {
//      if (strtotime($value)) {
//        $value = gmstrftime(EXHIBIT_DATE_FORMAT, strtotime($value));
//      }
//    }

      // strip out Profile: from profile fields
      $label = str_replace('_value', '', str_replace('profile_values_profile_', '', $label));

      if ($view->override_path) {
        $value = check_plain($value);
      }

      $json .= ($more_fields ? ','. $eol : '') . $spaces4 .'"'. $label .'"'. $spaces1 .':'. $spaces1 .'"'. $value .'"';

      $more_fields = TRUE;
    }

    $json .= $eol . $spaces2 .'}';

    $more_view_results = TRUE;
  }

  $json .= ']'. $spaces1 .'}';

  if ($view->override_path) {
    // we're inside a live preview so pretty-print the JSON
    print '<code>'. $json .'</code>';
  }
  elseif ($coder_mode) {
    // we're in "coder" mode so just output the JSON
    print $json;
  }
  else {
    // we're in callback mode so switch the content type and stop further processing of the page
    drupal_set_header('Content-Type: text/javascript');
    print $json;
    module_invoke_all('exit');
    exit;
  }
}


function json_exhibit_render($view, $coder_mode = FALSE) {
  define('EXHIBIT_DATE_FORMAT', '%Y-%m-%d %H:%M:%S');

  $eol      = '';
  $spaces1  = '';
  $spaces2  = '';
  $spaces4  = '';

  if ($view->override_path) {
    // inside a live preview so use HTML line breaks and space accordingly
    $eol      = '<br />';
    $spaces1  = ' ';
    $spaces2  = str_repeat('&nbsp;', 2);
    $spaces4  = str_repeat('&nbsp;', 4);
  }

  $json = '{' . $spaces1 .'"items"'. $spaces1 .':'. $spaces1 .'['. $eol;

  $more_view_results = FALSE;
  foreach ($view->result as $node) {
    $json .= ($more_view_results ? ','. $eol : '') . $spaces2 .'{'. $eol;
    $json .= $spaces4 .'"type"'. $spaces1 .':'. $spaces1 .'"'.'##type##'.'",'. $eol;
    $json .= $spaces4 .'"label"'. $spaces1 .':'. $spaces1 .'"'.'##label##'.'",'. $eol;

    $more_fields = FALSE;
    foreach ($node as $field_label => $field_value) {
      $label = trim(views_json_strip_illegal_chars(views_json_encode_special_chars($field_label)));
      $value = views_json_encode_special_chars(trim(views_json_is_date($field_value)));
      if ((is_null($value)) || ($value == '')) continue;

//    if (preg_match('/\d/', $value)) {
//      if (strtotime($value)) {
//        $value = gmstrftime(EXHIBIT_DATE_FORMAT, strtotime($value));
//      }
//    }

      // strip out Profile: from profile fields
      $label = str_replace('_value', '', str_replace('profile_values_profile_', '', $label));

      if ($view->override_path) {
        $value = check_plain($value);
      }

      if ($label == 'type') {
        $json = str_replace('##type##', $value, $json);
      }
      elseif ($label == 'label') {
        $json = str_replace('##label##', $value, $json);
      }
      else {
        $json .= ($more_fields ? ','. $eol : '') . $spaces4 .'"'. $label .'"'. $spaces1 .':'. $spaces1 .'"'. $value .'"';
      }
    }

    if (strpos($json, '##type##') !== FALSE) {
      $json = str_replace('##type##', (isset($node->type) ? $node->type : 'Item'), $json);
    }
    if (strpos($json, '##label##') !== FALSE) {
      $json = str_replace('##label##', (isset($node->title) ? $node->title : 'none'), $json);
    }

    $json .= $eol . $spaces2 .'}';

    $more_view_results = TRUE;
  }

  $json .= ']'. $spaces1 .'}';

  if ($view->override_path) {
    // we're inside a live preview so pretty-print the JSON
    print '<code>'. $json .'</code>';
  }
  elseif ($coder_mode) {
    // we're in "coder" mode so just output the JSON
    print $json;
  }
  else {
    // we're in callback mode so switch the content type and stop further processing of the page
    drupal_set_header('Content-Type: text/javascript');
    print $json;
    module_invoke_all('exit');
    exit;
  }
}
