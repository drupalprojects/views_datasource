<?php
// $Id$
/**
 * @file views-view-xhtml.tpl.php
 * View template to render views as XHTML microformats. Supports hCard format 
 *
 * - $view: The view in use.
 * - $rows: The raw result objects from the query, with all data it fetched.
 * - $options: The options for the style passed in from the UI.
 *
 * @ingroup views_templates
 * @see views_xhtml.views.inc
 */

$nodes = array();
foreach($rows as $row) {
  $nodes[] = explode("|", trim($row));
  
}
if (count($nodes) != count($rows))
  return ("Did not get all rows (is the field separator '|' ?)");
  
if ($options['format'] == 'hcard') xhtml_hcard_render($nodes, $view);

function xhtml_hcard_render($nodes, $view) {
  $xhtml .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n";
  $xhtml .= '<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr"'.">\r\n";
  $xhtml .= '<head>'."\r\n";
  $xhtml .= '  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'."\r\n";
  $xhtml .= '  <meta name="KEYWORDS" content="hCards" />'."\r\n";
  $xhtml .= '  <title>hCards</title>'."\r\n";
  $xhtml .= '</head>'."\r\n";
  $xhtml .= '<body>'."\r\n";
  foreach ($nodes as $node) {
    $hcard = array('adr'=> array(
                           'type' => '', 
                           'post-office-box' => '',
                           'street-address' => array(),
                           'extended-address' => '',
                           'region' => '',
                           'locality' => '',
                           'postal-code' => '',
                           'country-name' => ''    
                           ),
                   'agent' => array(),
                   'bday' => '',
                   'class' => '',
                   'category' => array(),
                   'email' => array(),
                   'fn' => '',
                   'n' => array(
                          'honorific-prefix' => '',
                          'given-name' => '',
                          'additional-name' => '',
                          'family-name' => '',
                          'honorific-suffix' => ''    
                           ),
                   'nickname' => '',                
                   'org' => array (
                           'organization-name' => '',
                           'organization-unit' => array()
                           ),                        
                   'photo' => '',
                   'tel'=> array()         
                  );
    foreach($node as $nodefield) {
      $nodefieldarray = explode(":", $nodefield);

      /*replace escaped colons with actual colon*/
      $nodefieldarray[0] = str_replace('#colon#', ':', $nodefieldarray[0]);
      $nodefieldarray[1] = str_replace('#colon#', ':', $nodefieldarray[1]);

      $label = views_xhtml_strip_illegal_chars($nodefieldarray[0]);
      $value = views_xhtml_strip_illegal_chars(views_xhtml_is_date($nodefieldarray[1]));
      $label = str_replace('_value', '', str_replace("profile_values_profile_", '', $label)); //strip out Profile: from profile fields
      if (is_null($value) || ($value === '')) continue;
      
      if (stripos($label, 'address-type') !== FALSE) {
        $hcard['adr']['type'] = $value; 
      }
      if (stripos($label, 'post-office-box') !== FALSE) { 
        $hcard['adr']['post-office-box'] = $value;  
      }
      if (stripos($label, 'street-address') !== FALSE) {
        $hcard['adr']['street-address'][] = $value;  
      }
      if (stripos($label, 'extended-address') !== FALSE) {
        $hcard['adr']['extended-address'] = $value;  
      }
      if (stripos($label, 'region') !== FALSE) {
        $hcard['adr']['region'] = $value;  
      }
      if (stripos($label, 'locality') !== FALSE) {
        $hcard['adr']['locality'] = $value;  
      }
      if (stripos($label, 'postal-code') !== FALSE) {
        $hcard['adr']['postal-code'] = $value;  
      }
      if (stripos($label, 'country-name') !== FALSE) {
        $hcard['adr']['country-name'] = $value;  
      }
      if (stripos($label, 'agent') !== FALSE) {
        $hcard['agent'][] = $value;  
      }
      if (stripos($label, 'bday') !== FALSE) {
        $hcard['bday'] = $value;  
      }
      if (stripos($label, 'class') !== FALSE) {
        $hcard['class'] = $value;  
      }
      if (stripos($label, 'category') !== FALSE) {
        $hcard['category'][] = $value;  
      }
      if (stripos($label, 'email') !== FALSE) {
        $hcard['email'][$label] = $value;  
      }
      if (stripos($label, 'honorific-prefix') !== FALSE) {
        $hcard['n']['honorific-prefix'] = $value;  
      }
      if (stripos($label, 'given-name') !== FALSE) {
        $hcard['n']['given-name'] = $value;  
      }
      if (stripos($label, 'additional-name') !== FALSE) {
        $hcard['n']['additional-name'] = $value;  
      }
      if (stripos($label, 'family-name') !== FALSE) {
        $hcard['n']['family-name'] = $value;  
      }
      if (stripos($label, 'honorific-suffix') !== FALSE) {
        $hcard['n']['honorific-suffix'] = $value;  
      }        
      if (stripos($label, 'fn') !== FALSE) {
        $hcard['fn'] = $value;  
      }
      if (stripos($label, 'nickname') !== FALSE) {
        $hcard['nickname'] = $value;  
      }
      if (stripos($label, 'organization-name') !== FALSE) {
        $hcard['org']['organization-name'] = $value;  
      }
      if (stripos($label, 'organization-unit') !== FALSE) {
        $hcard['org']['organization-unit'][] = $value;  
      }
      if (stripos($label, 'photo') !== FALSE) {
        $hcard['photo'] = $value;  
      }
      if (stripos($label, 'tel') === 0) {
        $hcard['tel'][$label] = $value;  
      }                                
    } 
    $xhtml .= '<div class = "vcard">'."\r\n";
    if ($hcard['photo'] != '')
      $xhtml .='  <img class="photo" alt="photo" title="photo" style="height:96px;width:96px" src="'.$hcard['photo'].'"/>'."<br/>\r\n";      
    if ($hcard['fn'])
      $xhtml .='  <span class="fn">'.$hcard['fn'].'</span>'."<br/>\r\n";
    if ($hcard['nickname'])
      $xhtml .='  <span class="nickname">'.$hcard['nickname'].'</span>'."<br/>\r\n";
    $name = $hcard['n'];
    if ($hcard['fn']) 
      $xhtml .= '  <span class = "n">'."\r\n";
    else
      $xhtml .= '  <span class = "fn n">'."\r\n";
    if ($name['honorific-prefix'] !== '')
      $xhtml .='    <span class="honorific-prefix">'.$name['honorific-prefix'].'</span>'."\r\n";
    if ($name['given-name'] !== '')
      $xhtml .='    <span class="given-name">'.$name['given-name'].'</span>'."\r\n";
    if ($name['additional-name'] !== '')
      $xhtml .='    <span class="additional-name">'.$name['additional-name'].'</span>'."\r\n";
    if ($name['family-name'] !== '')
      $xhtml .='    <span class="family-name">'.$name['family-name'].'</span>'."\r\n";
    if ($name['honorific-suffix'] !== '')
      $xhtml .='    <span class="honorific-suffix">'.$name['honorific-suffix'].'</span>'."\r\n";
    $xhtml .= '  </span><br/>'."\r\n";
    if ($hcard['nickname'] !== '')
      $xhtml .= '    <span class="nickname">'.$hcard['nickname'].'</span><br/>'."\r\n";                  
    $org = $hcard['org'];
    $xhtml .= '  <span class="org">'."\r\n";
    if ($org['organization-name'] !== '')
      $xhtml.= '    <span class="organization name">'.$org['organization-name'].'</span><br/>'."\r\n";
    $org_units = $org['organization-unit'];  
    foreach ($org_units as $org_unit) 
      $xhtml .='    <span class="organization-unit">'.$org_unit.'</span>'."<br/>\r\n";
    $xhtml .= '  </span>'."\r\n";  
    $address = $hcard['adr'];
    $xhtml .= '  <span class = "adr">'."\r\n";
    if ($address['type'] !== '')
      $xhtml .='    <span class="type">'.$address['type'].'</span>'."<br/>\r\n";
    if ($address['post-office-box'] !== '')
      $xhtml .='    <span class="post-office-box">'.$address['post-office-box'].'</span>'."<br/>\r\n";
    $street_addresses = $address['street-address'];  
    foreach ($street_addresses as $street_address) 
      $xhtml .='    <span class="street-address">'.$street_address.'</span>'."<br/>\r\n";
    if ($address['extended-address'] !== '')
      $xhtml .='    <span class="extended-address">'.$address['extended-address'].'</span>'."<br/>\r\n";
    if ($address['region'] !== '')
      $xhtml .='    <span class="region">'.$address['region'].'</span>'."<br/>\r\n";
    if ($address['locality'] !== '')
      $xhtml .='    <span class="locality">'.$address['locality'].'</span>'."<br/>\r\n";
    if ($address['postal-code'] !== '')
      $xhtml .='    <span class="postal-code">'.$address['postal-code'].'</span>'."<br/>\r\n";
    if ($address['country-name'] !== '')
      $xhtml .='    <span class="country-name">'.$address['country-name'].'</span>'."\r\n";
    $xhtml .= '  </span><br/>'."\r\n";       
    $agents = $hcard['agent'];
    foreach ($agents as $agent) 
      $xhtml .='  <span class="agent">'.$agent.'</span>'."<br/>\r\n";
    $birthday =  $hcard['bday'];
    if ($birthday !== '') 
      $xhtml .='  <span class="bday">'.$birthday.'</span>'."<br/>\r\n";      
    $class = $hcard['class'];
    if ($class !== '')
      $xhtml .='  <span class="class">'.$class.'</span>'."<br/>\r\n";
    $categories = $hcard['category'];  
    foreach ($categories as $category) 
      $xhtml .='  <span class="category">'.$category.'</span>'."<br/>\r\n";
    if ($hcard['email']) {
      $email_addrs = $hcard['email']; 
      foreach ($email_addrs as $email_type => $email_addr) 
        $xhtml .='  <span class="email">'."\r\n".
                  '    <span class="type">'.$email_type.': </span>'."\r\n".
                  '    <a class="value" href="mailto:'.$email_addr.'">'.$email_addr.'</a>'."\r\n".
                  '  </span>'."<br/>\r\n";    
    
    }
    if ($hcard['tel']) {
      $tel_nos = $hcard['tel'];
      foreach ($tel_nos as $tel_no_type => $tel_no) 
        $xhtml .='  <span class="tel">'.
                    '<span class="type">'.$tel_no_type.': </span>'.
                    '<span class="value">'.$tel_no.'</span>'.
                    '</span>'."<br/>\r\n";    
    }    
    $xhtml .= '</div>'."\r\n";
  }

  $xhtml.='</body>'."\r\n";
  $xhtml.='</html>'."\r\n";
  if ($view->override_path) //inside live preview 
    print htmlspecialchars($xhtml);
  else {  
   print $xhtml;
   module_invoke_all('exit');
   exit;
  }  
}