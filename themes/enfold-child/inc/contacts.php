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
    set_transient('winmo_company_' . $contact_id, $contact, 604800);
  }
  return $contact;
}

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
