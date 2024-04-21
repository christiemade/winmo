<?php

// Create AJAX Call for Contact Pager
add_action("wp_ajax_winmo_contact_list", "winmo_contact_list");
add_action("wp_ajax_nopriv_winmo_contact_list", "winmo_contact_list");

function winmo_contact_list()
{
  $data = $_POST["data"];
  if (!wp_verify_nonce($_POST['nonce'], "winmo_contact_filter_nonce")) {
    exit("There has been an error.");
  }

  $contacts = get_transient('winmo_contacts');
  $page_number = $_POST["pageNumber"] ? $_POST["pageNumber"] : 1;

  // Turn filter values into an array
  $data = explode('&', $data);
  $filter = array();
  foreach ($data as $string) :
    $keyval = explode('=', $string);
    $filter[$keyval[0]] = $keyval[1];
  endforeach;

  // Filter query
  $cat_filter = isset($filter['cats']) ? $filter['cats'] : '';
  $search_filter = isset($filter['search']) ? $filter['search'] : '';

  // Default limit
  $limit = isset($filter['per-page']) ? $filter['per-page'] : 100;

  // Default offset
  $offset = 0;
  $current_page = 1;
  if (isset($page_number)) {
    $current_page = (int)$page_number;
    $offset = ($current_page * $limit) - $limit;
  }

  // filter contacts array based on query
  if (!empty($cat_filter) || !empty($search_filter)) {
    $filtered_contacts = array();
    foreach ($contacts as $person) {
      if (!empty($cat_filter) && !empty($search_filter)) {

        if ((strpos($person[0], $search_filter) !== false || $person[1] == $search_filter) && $person[2] == $cat_filter) {
          $filtered_contacts[] = $person;
        }
      } else if (!empty($cat_filter) && $person['category'] == $cat_filter) {

        $filtered_contacts[] = $person;
      } else if (!empty($search_filter) && (strpos($person[0], $search_filter) !== false || $person[1] == $search_filter)) {

        $filtered_contacts[] = $person;
      }
    }

    $contacts = $filtered_contacts;
  }

  // Alter the array
  $paged_contacts = array_slice($contacts, $offset, $limit, true);

  // Define total products
  $total_contacts = count($contacts);

  // Get the total pages rounded up the nearest whole number
  $total_pages = ceil($total_contacts / $limit);

  // Determine whether or not pagination should be made available.
  $paged = $total_contacts > count($paged_contacts) ? true : false;

  /*********************
    The Template
   ********************/
  $html = "<div class=\"row container\"><h2>Here is our epic pager:</h2></div><div class=\"row container\">";
  $mod = round(count($paged_contacts) / 3) + 1; // 3 columns
  $counter = 0;
  if (count($paged_contacts)) {
    foreach ($paged_contacts as $key => $person) {
      // Column shift
      if ($counter % $mod == 0) {
        if ($counter > 1) $html .= '</div><!-- /col -->';
        $html .= '<div class="col">';
      }
      $html .= '<a href="/decision_makers/' . $key . '/">' . $person[0] . ' ' . $person[1] . '</a>';
      $counter++;
    }
    $html .= '</div><!-- /col --></div><!-- /row -->';
  } else {
    $html .= '<p class="alert alert-warning" >No results found.</p>';
  }

  if ($paged) {
    ob_start();
    include "pagination.php";
    $html .= ob_get_contents();
    ob_end_clean();
  }

  if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $html = json_encode($html);
    echo $html;
  } else {
    header("Location: " . $_SERVER["HTTP_REFERER"]);
  }

  die();
}

// Save the individual contact as a transient, if it doesn't exist yet
function set_contact_transient($contact_id)
{
  $contact = get_transient('winmo_contact_' . $contact_id);

  // check to see if companies was successfully retrieved from the cache
  if (false === $contact) {
    // do this if no transient set
    $contact = winmo_contact_api($contact_id);

    // store the company's data and set it to expire in 1 week
    set_transient('winmo_contact_' . $contact_id, $contact, 604800);
  }
  return $contact;
}

// Pull in all contacts from a CSV
function set_contacts_transient()
{
  $contacts = get_transient('winmo_contacts');

  // check to see if companies was successfully retrieved from the cache
  if (false === $contacts) {
    // do this if no transient set
    $contacts = array();

    if ($file = fopen(get_stylesheet_directory() . "/inc/contacts.csv", "r")) {
      $keys = array();
      while (($data = fgetcsv($file)) !== FALSE) {
        if ($data[0] <> 'Contact Type') {
          $contacts[$data[$keys[0]]] = array($data[1], $data[2], $data[4], $data[5]);
        } else {
          // Find key for person ID
          $keys = array_keys($data, 'Person ID');
        }
      }

      // store the companies array and set it to never expire
      // This doesnt need to expire, we can manually refresh the transient when we get a new CSV
      set_transient('winmo_contacts',  $contacts, 0);
    }
    fclose($file);
  }
}
add_action('after_setup_theme', 'set_contacts_transient');

// Grab an individual contact from the API
function winmo_contact_api($id)
{
  // Include Request and Response classes
  $url = 'https://api.winmo.com/web_api/contacts?ids[]=' . $id;

  $args = array(
    'headers' => array(
      'Content-Type' => 'application/json',
      'Authorization' => 'Bearer ' . WINMO_TOKEN
    ),
  );

  $request = wp_remote_get($url, $args);

  if (!is_wp_error($request)) {
    if ($request['response']['code'] == "404") {
      return new WP_Error('broke', 'Page not found.');
    } else {
      $body = json_decode(wp_remote_retrieve_body($request), true);
      return $body['result'];
    }
  } else {
    $body = wp_remote_retrieve_body($request);
    if (isset($body['result'])) {
      return $body['result'];
    } else {
      return new WP_Error('broke', $request->get_error_message());
    }
  }
}

// Show unlock button in header of contact pages
add_filter('avf_main_menu_nav', function ($stuff) {
  $contact = get_query_var('pid');
  if ($contact && is_page('contacts')) {
    $stuff .= '<div class="button"><a href="https://www.winmo.com/profile-1/"><img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/unlock.png"></a></div>';
  }
  return $stuff;
});
