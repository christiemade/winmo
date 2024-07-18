<?php

// Save the individual contact as a transient, if it doesn't exist yet
function set_contact_transient($contact_id, $data = "")
{

  global $wpdb;
  // Pull company info from database
  $sql = "SELECT * FROM `winmo` WHERE `type` = 'contacts' AND `api_id` = '" . $contact_id . "' LIMIT 1";
  $result = $wpdb->get_results($sql);
  if ($result) $result = json_decode($result[0]->data);

  // Contact info doesn't exist in the database, or we're here to change it
  if (!empty($data)) {
    // store the contact's data into the DB table
    if ($result) {
      $sql = "UPDATE `winmo` SET `data` = CAST('" . addslashes($data) . "' AS JSON) WHERE id = '" . $result->id . "'";
    } else {
      $sql = "INSERT INTO `winmo` (`type`, `api_id`, `data`)
        VALUES('contacts', '" . $contact_id . "', CAST('" . addslashes($data) . "' AS JSON))";
    }
    $result = $wpdb->query($sql);
    if ($result) $result = $data;
  }

  return $result;
}

// Put all contacts into a transient
function set_contacts_transient($results = array(), $page = false, $last = false)
{
  $contacts = get_transient('winmo_contacts');

  // if we're rebuilding (page 1) then lets reset the array
  if ($page == 1) { // Rebuild transient
    $contacts = array();
  }

  // Dont change transient until all data is uploaded
  if ($page > 1) {
    $contacts = get_transient('winmo_contacts_temp');

    // Set current page - this is where we'll START from next time
    if ($contacts) {
      set_transient('contacts_last_page', $page, 0);
    }
    // Something went wrong - our temporary contacts are missing - start over
    else {
      $contacts = array(); // start over
      $page = 0; // so that it loops to page 1 next time
      error_log("Need to start over on contacts :(");
      $results = array();  // dont use the results because theyre not the first set
    }
  }

  $rework = array();
  $contact_links = array();

  foreach ($results as $contact) :
    $permalink = strtolower(str_replace(" ", '-', $contact['fname'] . ' ' . $contact['lname']));

    if (isset($contact_links[$permalink])) {
      $contact_links[$permalink][] = $permalink;
      $permalink .= "-" . ceil(sizeof($contact_links[$permalink]) + 1);
    } else {
      $contact_links[$permalink] = array();
    }

    //$rework[$contact['id']] = array($contact['fname'], $contact['lname'], $contact['title'], $contact['entity_id'], $permalink, $contact['type']);
    $rework[$contact['id']] = array($contact['fname'], $contact['lname'], $permalink);

    // Save permalink into database as well
    $contact['permalink'] = $permalink;

    // Set individual data into database
    set_contact_transient($contact['id'], json_encode($contact));
  endforeach;
  //error_log(gettype($contacts) . " " . gettype($rework));
  $contacts = $contacts + $rework;

  // store the companies array and set it to never expire
  // This doesnt need to expire, we can manually refresh the transient when we get a new CSV
  $transient_name = 'winmo_contacts_temp';
  if ($last) {
    error_log("Warning: we are deleting a transient..." . $transient_name);
    delete_transient('contacts_last_page'); // Remove last page check
    delete_transient($transient_name); // Remove temporary transient
    $transient_name = 'winmo_contacts';  // Last page, now update officialdelete_transient($transient_name); // Remove temporary transient
    delete_transient($transient_name); // Remove previous transient
  }
  set_transient($transient_name, $contacts, 0);
  //set_transient("winmo_contacts", $contacts, 0);
  return array('data' => true, 'page' => $page);
}

// Show unlock button in header of contact pages
add_filter('avf_main_menu_nav', function ($stuff) {
  $contact = get_query_var('pid');
  if ($contact && is_page('contacts')) {
    $stuff .= '<div class="button"><a class="modal" href="https://www.winmo.com/profile-1/"><img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/unlock.png"></a></div>';
  }
  return $stuff;
});

// Create AJAX Call for Contacts Pager
add_action("wp_ajax_winmo_contacts_list", "winmo_contacts_list");
add_action("wp_ajax_nopriv_winmo_contacts_list", "winmo_contacts_list");

function winmo_contacts_list()
{
  $data = $_POST["data"];
  if (!wp_verify_nonce($_POST['nonce'], "winmo_filter_nonce")) {
    exit("There has been an error.");
  }

  $contacts = get_transient('winmo_contacts');

  // Turn filter values into an array
  $data = explode('&', $data);
  $filter = array();
  foreach ($data as $string) :
    $keyval = explode('=', $string);
    $filter[$keyval[0]] = $keyval[1];
  endforeach;

  // Filter query
  $search_filter = isset($filter['search']) ? $filter['search'] : '';

  // Pull out all entries by alpha
  $alpha = $filter['alpha'];

  // If an alpha sort is provided, do that first
  if ($alpha) {

    $filtered = array_filter($contacts, function ($contact) use ($alpha) {

      $letter = substr($contact[1], 0, 1);

      if (strtolower($letter) == strtolower($alpha)) {
        return true;
      }
      // In non-alpha sort
      else {
        return false;
      }
    });
  } else {
    error_log(gettype($contacts));
    $filtered = $contacts;
  }

  // filter companies array based on query
  if (!empty($search_filter)) {
    foreach ($filtered as $key => $contact) {
      if ((stripos($contact[0], urldecode($search_filter)) !== false) || (stripos($contact[1], urldecode($search_filter)) !== false)) {
      } else {
        unset($filtered[$key]);
      }
    }
  }

  // Define total products
  $total_items = sizeof($filtered);

  // Go through filtered items and acquire additional data (permalink)
  foreach ($filtered as $key => $val) :
    $result = set_contact_transient($key);
    if (isset($val[2])) $filtered[$key]['permalink'] = $val[2];
    if (!isset($val['permalink']) && isset($result->permalink)) $filtered[$key]['permalink'] = $result->permalink;
  endforeach;

  // Sort our filtered items
  usort($filtered, "last_name_sort");

  /*********************
    The Template
   ********************/
  $html = "<div class=\"row container\"></div><div class=\"row container\">";
  $mod = round($total_items / 3) + 1; // 3 columns
  $counter = 0;
  if ($total_items) {
    foreach ($filtered as $key => $contact) {
      // Column shift
      if ($counter % $mod == 0) {
        if ($counter > 1) $html .= '</div><!-- /col -->';
        $html .= '<div class="col">';
      }
      $html .= '<a href="/decision_makers/' . $contact['permalink'] . '/">' . $contact[0] . ' ' . $contact[1] . '</a>';
      $counter++;
    }
    $html .= '</div><!-- /col --></div><!-- /row -->';
  } else {
    $html .= '<p class="alert alert-warning" >No results found.</p>';
  }

  if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $html = json_encode($html);
    echo $html;
  } else {
    header("Location: " . $_SERVER["HTTP_REFERER"]);
  }

  die();
}

function last_name_sort($a, $b)
{
  return $a[1] > $b[1];
}
