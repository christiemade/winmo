<?php
function set_company_transient($company_id)
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
function set_companies_transient()
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
add_action('after_setup_theme', 'set_companies_transient');


function winmo_company_api($id)
{
  // Include Request and Response classes
  $url = 'https://api.winmo.com/web_api/business_details?id=' . $id . '&entity_type=company';

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

// Show unlock button in header of company pages
add_filter('avf_main_menu_nav', function ($stuff) {
  $company = get_query_var('rid');
  if ($company && is_page('companies')) {
    $stuff .= '<div class="button"><a href="https://www.winmo.com/profile-1/"><img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/unlock.png"></a></div>';
  }
  return $stuff;
});

// Grab details on a brand from the API
function winmo_brand_api($id)
{
  // Include Request and Response classes
  $url = 'https://api.winmo.com/web_api/business_details?id=' . $id . '&entity_type=company';

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

function winmo_image_placeholder_transients($type)
{
  $images = get_transient('winmo_image_placeholders_' . $type);

  // check to see if companies was successfully retrieved from the cache
  if (false === $images) {

    // do this if no transient set
    $directory = get_stylesheet_directory() . '/assets/img/companies/' . $type . '/';
    $images = glob($directory . "*");
    foreach ($images as $key => $val) :
      $images[$key] = substr($val, strpos($val, '/wp-content'));
    endforeach;

    // store the image options and set it to expire in 1 week
    set_transient('winmo_image_placeholders_' . $type, $images, 604800);
  }
  return $images;
}
