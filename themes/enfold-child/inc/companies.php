<?php
function set_company_transient()
{
  $companies = get_transient('winmo_companies');

  // check to see if companies was successfully retrieved from the cache
  if (false === $companies) {
    // do this if no transient set
    $companies = array();

    if ($file = fopen(get_stylesheet_directory() . "/inc/companies.csv", "r")) {
      while (($data = fgetcsv($file)) !== FALSE) {
        if ($data[0] <> 'Id') $companies[$data[0]] = $data[1];
      }

      // store the companies array and set it to expire in 1 week
      set_transient('winmo_companies', $companies, 604800);
    }
    fclose($file);
  }
}
add_action('after_setup_theme', 'set_company_transient');


function winmo_company_api($id)
{
  // Include Request and Response classes
  $url = 'https://api.winmo.com/web_api/business?id=' . $id . '&entity_type=company';

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
