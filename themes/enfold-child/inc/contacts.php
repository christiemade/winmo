<?php

// Obtain the individual contact permalink by api_id
function get_contact_permalink($contact_id)
{

  global $wpdb;
  $result = null;

  $sql = "SELECT permalink FROM `winmo_contacts` WHERE `api_id` = '" . $contact_id . "' LIMIT 1";
  $result = $wpdb->get_var($sql);
  return $result;
}


// Grab or update individual contact from the database
function set_contact_information($contact_id, $data = "")
{

  global $wpdb;

  $wpdb->show_errors();

  // No data provided, so we're fishing
  if (empty($data)) {

    // Pull company info from database
    $sql = "SELECT data FROM `winmo` WHERE `type` = 'contacts' AND `api_id` = '" . $contact_id . "' LIMIT 1";

    $result = $wpdb->get_var($sql);
    if ($result !== null) $result = json_decode($result);
  }
  // Data provided, so we're adding or updating
  else {

    // Checking if contacts exist
    $sql = "SELECT id FROM `winmo` WHERE `type` = 'contacts' AND `api_id` = '" . $contact_id . "' LIMIT 1";

    $result = $wpdb->get_var($sql);

    // store the contact's data into the DB table
    if ($result !== null) {
      if (in_array(gettype($result), array("string", "integer"))) {
        $sql = "UPDATE `winmo` SET `data` = CAST('" . addslashes($data) . "' AS JSON) WHERE id = '" . $result . "'";
      } else {
        error_log("We couldn't run the update because the result is " . gettype($result));
      }
    } else {
      $sql = "INSERT INTO `winmo` (`type`, `api_id`, `data`)
        VALUES('contacts', '" . $contact_id . "', CAST('" . addslashes($data) . "' AS JSON))";
    }

    $result = $wpdb->query($sql);

    if ($result) $result = $data;
  }
  $wpdb->hide_errors();

  return $result;
}

// Put all contacts into a transient (custom db table, because of how much data it is)
function set_contacts_transient($results = array(), $atts = array())
{
  global $wpdb;
  extract($atts);

  error_log($type . " page " . $page . " " . json_encode($atts));
  // if we're rebuilding (page 1) then lets reset the array
  if (($page == 1) && ($type == "company_contacts")) { // Rebuild transient
    error_log("Trying to delete all of the temporary contacts");
    error_log($page . " && " . $type);
    //$wpdb->delete('winmo_contacts', array('status' => 'temp'));
  }

  // Prevent switch to agencies from breaking the pager
  //error_log($page . " (" . gettype($page) . ") + " . $first_total . "(" . gettype($first_total) . ")");
  if ($type == "agency_contacts") {
    $page = (int)$page + (int)$first_total;
    error_log("Inside " . $type . " so page is now: " . $page);
  }

  // Dont change official contact list until all data is uploaded
  // This pulls existing contacts from the temporary transient
  if (($page > 1) || ($type == "agency_contacts")) {

    // Just checking that we have already added temporary contacts
    $contacts = get_winmo_contacts('temp', '', '', 1);

    // Set current page - that way if something breaks we'll know where to START from next time - A bookmark!
    error_log("Contacts is of type: " . gettype($contacts));
    if ($contacts === NULL || sizeof($contacts)) {
      error_log("Setting the transient");
      set_transient('contacts_last_page', $page, 0);
    }
    // Something went wrong - our temporary contacts are missing - start over
    else {
      global $wpdb;
      error_log('Something went wrong. Dont delete unless we are actually getting back 0');
      //$wpdb->delete('winmo_contacts', array('status' => 'temp')); // start over
      //$page = 0; // so that it loops to page 1 next time
      error_log("Need to start over on contacts :(");
      $results = array();  // dont use the results because theyre not the first set
      return array('data' => true, 'page' => $page);
      exit;
    }
  }

  $contact_links = array();
  $rework = array();

  foreach ($results as $contact) :
    $permalink = strtolower(str_replace(array(" ", "'"), '-', $contact['fname'] . ' ' . $contact['lname']));

    if (isset($contact_links[$permalink])) {
      $contact_links[$permalink][] = $permalink;
      $permalink .= "-" . ceil(sizeof($contact_links[$permalink]) + 1);
    } else {
      $contact_links[$permalink] = array();
    }

    // Save all basic info for the index
    $rework[] = array($contact['id'], $contact['fname'], $contact['lname'], $page, $permalink);

    // Save permalink into database as well
    $contact['permalink'] = $permalink;

    // Set individual data into the winmo database
    set_contact_information($contact['id'], json_encode($contact));

  endforeach;

  // Take all the indexes we collected and all them to the database now
  add_winmo_contact($rework, 'temp');

  // We're at the end of the import - clean up
  if ($last) {
    error_log("THIS WAS THE END! Do something drastic now.");

    global $wpdb;
    error_log("We got to the last item");
    delete_transient('contacts_last_page'); // Remove last page check
    //delete_transient($transient_name); // Remove temporary transient

    // Turn all temporary transients into official ones
    $wpdb->delete('winmo_contacts', array('status' => 'official'));
    $wpdb->update('winmo_contacts', array('status' => 'official'), array('status' => 'temp'));
    error_log("Official deleted, and temp became official");
  }

  return array('data' => true, 'page' => $page, 'last' => $last);
}

function get_winmo_contacts($status = "official", $alpha = '', $permalink = '', $limit = '')
{
  global $wpdb;

  $wpdb->show_errors();

  // Pull all contacts from database
  $args = array();
  $args[] = $status;
  $sql = "SELECT * FROM `winmo_contacts` WHERE `status` = %s";
  if (!empty($alpha)) {
    $sql .= ' AND `last_name` LIKE %s';
    $args[] = $wpdb->esc_like($alpha) . '%';
  }
  if (!empty($permalink)) {
    $sql .= ' AND `permalink` = %s';
    $args[] = $permalink;
  }
  if ($limit !== 1) {
    $sql .= ' ORDER BY api_id DESC LIMIT 1';
  }
  if (!empty($limit)) {
    $sql .= " LIMIT %d";
    $args[] = $limit;
  }

  $sql = $wpdb->prepare(
    $sql,
    $args
  );

  $result = $wpdb->get_results($sql);

  return $result;
}

function add_winmo_contact($contact, $status)
{
  global $wpdb;

  $howditgo = false;

  $wpdb->show_errors();
  foreach ($contact as $insert) :
    $howditgo = $wpdb->insert(
      'winmo_contacts',
      array(
        'api_id' => $insert[0],
        'first_name' => $insert[1],
        'last_name' => $insert[2],
        'page' => $insert[3],
        'permalink' => $insert[4],
        'status' => $status,
      )
    );
  endforeach;

  $wpdb->hide_errors();
  if (!$howditgo) return false;
}

// Show unlock button in header of contact pages
add_filter('avf_main_menu_nav', function ($stuff) {
  $contact = get_query_var('pid');
  if ($contact && is_page('contacts')) {
    $stuff .= '<div class="button"><a class="modal" href="#request_demo"><img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/unlock.png"></a></div>';
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
    $contacts = $filtered = get_winmo_contacts("official", $alpha);
  } else {
    $contacts = get_winmo_contacts("official");
    $filtered = $contacts;
  }

  // filter companies array based on query
  if (!empty($search_filter)) {
    foreach ($filtered as $key => $contact) {
      if ((stripos($contact->first_name, urldecode($search_filter)) !== false) || (stripos($contact->last_name, urldecode($search_filter)) !== false)) {
      } else {
        unset($filtered[$key]);
      }
    }
  }

  // Define total products
  $total_items = sizeof($filtered);

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
      $html .= '<a href="/decision_makers/' . $contact->permalink . '/">' . $contact->first_name . ' ' . $contact->last_name . '</a>';
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
  return strcmp($a->last_name, $b->last_name);
}
