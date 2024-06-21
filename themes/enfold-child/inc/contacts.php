<?php

// Save the individual contact as a transient, if it doesn't exist yet
function set_contact_transient($contact_id)
{
  error_log("Single contact transient " . $contact_id);
  /* $contact = get_transient('winmo_contact_' . $contact_id);

  // check to see if companies was successfully retrieved from the cache
  if (false === $contact) {
    // do this if no transient set
    //$contact = winmo_contact_api($contact_id);
    // temporary testing JSON
    $contact = '{
      "id": 12345
      "type": "Agency",
      "entity_id": 234
      "fname": "Jill",
      "lname": "Smith",
      "title": "Chief Executive Officer",
      "phone": "(555) 555-5555",
      "email": "chiefjill@bigagency.com",
      "address1": "1234 Main St",
      "address2": "Suite 200",
      "city": "Anytown",
      "state": "CA",
      "zip_code": "12345",
      "country": "US"
    }';

    // store the company's data and set it to expire in 1 week
    set_transient('winmo_contact_' . $contact_id, $contact, 604800);
  }
  return json_decode($contact);*/
}

// Put all contacts into a transient
function set_contacts_transient($results = array(), $page = false, $last = false)
{
  $contacts = get_transient('winmo_contacts');

  // if we're rebuilding (page 1) then lets reset the array
  if ($page == 1) { // Rebuild transient
    $contacts = array();
  } elseif ($page > 1) { // Dont change transient until all data is uploaded
    $contacts = get_transient('winmo_contacts_temp');
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

    $rework[$contact['id']] = array($contact['fname'], $contact['lname'], $contact['title'], $contact['entity_id'], $permalink, $contact['type']);

  // Set individual data into database
  //set_transient('winmo_contacts',  $contacts, 0);
  endforeach;
  $contacts = $contacts + $rework;

  // store the companies array and set it to never expire
  // This doesnt need to expire, we can manually refresh the transient when we get a new CSV
  $transient_name = 'winmo_contacts_temp';
  if ($last) {
    delete_transient($transient_name); // Remove temporary transient
    $transient_name = 'winmo_contacts';  // Last page, now update officialdelete_transient($transient_name); // Remove temporary transient
    delete_transient($transient_name); // Remove previous transient
  }
  set_transient($transient_name, $contacts, 0);

  return array('data' => true);
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
