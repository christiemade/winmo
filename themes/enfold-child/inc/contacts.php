<?php

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
    $contact_links = array();

    if ($file = fopen(get_stylesheet_directory() . "/inc/contacts.csv", "r")) {
      $keys = array();
      while (($data = fgetcsv($file)) !== FALSE) {
        if ($data[0] <> 'Contact Type') {

          $permalink = strtolower(str_replace(" ", '-', $data[3]));

          if (isset($contact_links[$permalink])) {
            $contact_links[$permalink][] = $permalink;
            $permalink .= "-" . ceil(sizeof($contact_links[$permalink]) + 1);
          } else {
            $contact_links[$permalink] = array();
          }

          $contacts[$data[$keys[0]]] = array($data[1], $data[2], $data[4], $data[5], $permalink);
        } else {
          // Find key for person ID
          $keys = array_keys($data, 'Person ID');
        }
      }

      // store the contacts array and set it to never expire
      // we can manually refresh the transient when we get a new CSV
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
      return new WP_Error('broke', 'Page not found 2.');
    } else {
      $body = json_decode(wp_remote_retrieve_body($request), true);
      return $body['result'];
    }
  } else {
    $body = wp_remote_retrieve_body($request);
    if (isset($body['result'])) {
      return $body['result'];
    } else {
      return new WP_Error('broke', "XXX" . $request->get_error_message());
    }
  }
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
      $html .= '<a href="/decision_makers/' . $contact[4] . '/">' . $contact[0] . ' ' . $contact[1] . '</a>';
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
