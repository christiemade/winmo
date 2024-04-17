<?php
function set_contact_transient($contact)
{
  $company = get_transient('winmo_company_' . $company_id);

  // check to see if companies was successfully retrieved from the cache
  if (false === $company) {
    // do this if no transient set
    $company = winmo_company_api($company_id);

    // store the company's data and set it to expire in 1 week
    set_transient('winmo_company_' . $company_id, $company, 604800);
  }
  return $company;
}

function winmo_contact_api($id, $type = "company")
{
  // Include Request and Response classes
  $url = 'https://api.winmo.com/web_api/business_details?id=' . $id . '&entity_type=' . $type;

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
