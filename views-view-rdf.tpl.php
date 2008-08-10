<?php
// $Id$
/**
 * @file views-view-rdf.tpl.php
 * View template to render views as RDF. Supports FOAF and SIOC vocabulary.
 *
 * - $view: The view in use.
 * - $rows: The raw result objects from the query, with all data it fetched.
 * - $options: The options for the style passed in from the UI.
 *
 * @ingroup views_templates
 * @see views_rdf.views.inc
 */

if ($options['vocabulary'] == 'FOAF') rdf_foaf_xml_render($view);
if ($options['vocabulary'] == 'SIOC') rdf_sioc_xml_render($view);

/**
 * Render nodes as FOAF in XML
 *
 * @param array $nodes
 * @return none
 */
function rdf_foaf_xml_render($view) {
	global $base_url;
  $xml .= '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
  $xml .= '<!-- generator="Drupal Views_Datasource.Module" -->'."\n";
  $xml .= '<rdf:RDF xmlns="http://xmlns.com/foaf/0.1"'."\n";
  $xml .= '  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"'."\n";
  $xml .= '  xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"'."\n";
  $xml .= '  xmlns:dc="http://purl.org/dc/elements/1.1/"'."\n";
  $xml .= '  xmlns:foaf="http://xmlns.com/foaf/0.1/">'."\n";
  foreach ($view->result as $node) {
    $xml.="<foaf:Person>";
    foreach($node as $field_label => $field_value) {
      $label = views_rdf_strip_illegal_chars($field_label);
      $value = views_xml_strip_illegal_chars(views_xml_is_date($field_value));
      if (is_null($value) || ($value === '')) continue;
      if (strtotime($value))
        $value = date(DATE_ISO8601, strtotime($value));
      if (stripos($label, 'firstname') !== false) {
        $xml.="  <foaf:firstName>$value</foaf:firstName>\n";
        continue;
      }      
      if (stripos($label, 'surname') !== false) {
        $xml.="  <foaf:surName>$value</foaf:surName>\n";
        continue;
      }      
      if ((stripos($label, 'name') == true) && ((stripos($label, 'surname') === false) && (stripos($label, 'firstname') === false))) {
        //if (stripos($xml, "<foaf:name>") === false)
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
      if ((stripos($label, 'mbox') !== false) && !(stripos($label, 'mbox_sha1sum') !== false)) {
        $xml.="  <foaf:mbox>$value</foaf:mbox>\n";
        continue;
      }
      if ((stripos($label, 'mail') == true) && (stripos($xml, '<foaf:mbox>') == false)) {
          $xml.="  <foaf:mbox>$value</foaf:mbox>\n";
          $xml.="  <foaf:mbox_sha1sum>".md5("mailto:".$value)."</foaf:mbox_sha1sum>\n";
        continue;
      }
      if (stripos($label, 'mbox_sha1sum') !== false) {
        $xml.="  <foaf:mbox_sha1sum>$value</foaf:mbox_sha1sum>\n";
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
    drupal_set_header('Content-Type: application/rdf+xml');
    print $xml;
    module_invoke_all('exit');
    exit;
  }  
  
}

/**
 * Render users, blog and forum posts and comments, as SIOC in XML
 *
 * @param object $view
 * @return none
 */
function rdf_sioc_xml_render($view) {
	global $base_url;
	$users = array();
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	$xml .= '<!-- generator="Drupal Views_Datasource.Module" -->'."\n";
  $xml .= "<rdf:RDF\r\n";
  $xml .= "  xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\r\n";
  $xml .= "  xmlns:rdfs=\"http://www.w3.org/2000/01/rdf-schema#\"\r\n";
  $xml .= "  xmlns:sioc=\"http://rdfs.org/sioc/ns#\"\r\n";
  $xml .= "  xmlns:sioct=\"http://rdfs.org/sioc/terms#\"\r\n";
  $xml .= "  xmlns:dc=\"http://purl.org/dc/elements/1.1/\"\r\n";
  $xml .= "  xmlns:dcterms=\"http://purl.org/dc/terms/\"\r\n";
  $xml .= "  xmlns:admin=\"http://webns.net/mvcb/\"\r\n";
  $xml .= "  xmlns:foaf=\"http://xmlns.com/foaf/0.1/\">\r\n";
  if ($view->base_table == 'users') {
  	$has_uid = false;
  	$has_name = false;
  	$has_email = false;
    foreach($view->field as $field) {
    	//if (($field->field_alias == 'uid') && ($field['options']['field'] ==  'uid'))
    	if ($field->options['field'] ==  'uid') 
    	  $has_uid = true;
    	if ($field->options['field'] ==  'name') 
        $has_name = true;
      if ($field->options['field'] ==  'mail') 
        $has_email = true;   
    }
  	if (!$has_uid) {
  		if ($view->override_path)
  		  print ('<b style="color:red">The uid field must be present.</b>');
      else
  	    drupal_set_message('The uid field must be present.', 'error');
  	  return;
  	}
    if (!$has_name) {
      if ($view->override_path)
        print ('<b style="color:red">The name field must be present.</b>');
      else
        drupal_set_message('The name field must be present.', 'error');
      return;
    }
    if (!$has_email) {
      if ($view->override_path)
        print ('<b style="color:red">The email field must be present.</b>');
      else
        drupal_set_message('The email field must be present.', 'error');
      return;
    }
        
  }
  if ($view->base_table == 'users') {
    $xml .= "<foaf:Document rdf:about=\"".url($view->name, array('absolute'=>true))."\">\n";
    $xml .= "  <dc:title>SIOC profiles for: ".variable_get('site_name', 'drupal')."</dc:title>\n";
    $xml .= "  <dc:description>\n";
    $xml .= "    A User is an online account of a member of an online community. 
     It is connected to Items and Posts that a User creates or edits, 
     to Containers and Forums that it is subscribed to or moderates and 
     to Sites that it administers. Users can be grouped for purposes of 
     allowing access to certain Forums or enhanced community site features (weblogs, webmail, etc.).
     A foaf:Person will normally hold a registered User account on a Site 
     (through the property foaf:holdsAccount), and will use this account 
     to create content and interact with the community. sioc:User describes 
     properties of an online account, and is used in combination with a 
     foaf:Person (using the property sioc:account_of) which describes 
     information about the individual itself.\n";
    $xml .= "  </dc:description>\n";
    //$xml .= "<foaf:primaryTopic rdf:resource=\"####user_name###\"/>\n";
    $xml .= "  <admin:generatorAgent rdf:resource=\"http://drupal.org/project/views_datasource\"/>\n";
    $xml .= "</foaf:Document>\n";
  }
  foreach($view->result as $node) {	
  	foreach($node as $field_label=>$field_value) {
      $label = views_rdf_strip_illegal_chars($field_label);
      $value = views_xml_strip_illegal_chars(views_xml_is_date($field_value));
      if (is_null($value) || ($value === '')) continue;
      if (strtotime($value))
        $value = date(DATE_ISO8601, strtotime($value));
      if ((strtolower($label) == 'id') || (strtolower($label) == 'uid')) {
      	$uid = $value;    	
      }
  	  if ((strtolower($label) == 'name') || (strtolower($label) == 'users_name')) {
        $user_name = $value;      
      }
  	  if ((strtolower($label) == 'email') || (strtolower($label) == 'users_mail')) {
        $user_email = $value;      
      }            
  	}
  	if (empty($user_name)) continue;
    $xml .="<foaf:Person rdf:ID=\"$user_name\" rdf:about=\"".url('user/'.$uid, array('absolute'=>true))."\">\n";
    $xml .="  <foaf:name>$user_name</foaf:name>\n";
    $xml .="  <foaf:mbox_sha1sum>".md5('mailto:'.$user_email)."</foaf:mbox_sha1sum>\n";
    $xml .="  <foaf:holdsAccount>\n";
    $xml .="    <sioc:User rdf:about=\"".url('user/'.$uid, array('absolute'=>true))."\">\n";
    $xml .="      <sioc:name>$user_name</sioc:name>\n";
    $xml .="      <sioc:email rdf:resource=\"$user_email\"/>\n";
    $xml .="      <sioc:email_sha1>".md5('mailto:'.$user_email)."</sioc:email_sha1>\n";
    $roles = array();
    $roles_query = db_query("SELECT r.name AS name, r.rid AS rid FROM {users_roles} ur, {role} r WHERE ur.uid = %d AND ur.rid = r.rid", $uid);
    while ($role = db_fetch_object($roles_query))
      $roles[$role->rid] = $role->name;
    if (count($roles) > 0) {
      $xml .="      <sioc:has_function>\n";
      foreach($roles as $rid=>$name)
        $xml .="        <sioc:Role><rdfs:label><![CDATA[$name]]></rdfs:label></sioc:Role>\n";
      $xml .="      </sioc:has_function>\n"; 	  
    }             
    $xml .="    </sioc:User>\n";
    $xml .="  </foaf:holdsAccount>\n";
    $xml .="</foaf:Person>\n";	
  }
  $xml.="</rdf:RDF>\n";
  if ($view->override_path) //inside live preview 
    print htmlspecialchars($xml);
  else {  
    drupal_set_header('Content-Type: application/rdf+xml');
    print $xml;
    //var_dump($view);
    module_invoke_all('exit');
    exit;
  }	
  
}
  