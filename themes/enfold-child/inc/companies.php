<?php
function set_company_transient($company_id, $type = "company")
{

  $company = get_transient('winmo_' . $type . '_' . $company_id);

  // check to see if companies was successfully retrieved from the cache
  if (false === $company) {

    // do this if no transient set
    $company = winmo_company_api($company_id, $type);

    // store the company's data and set it to expire in 1 week
    set_transient('winmo_' . $type . '_' . $company_id, $company, 604800);
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
        error_log($data[0]);
        if (!strpos($data[0], 'Id')) $companies[$data[0]] = array(
          'name' => $data[1],
          'industry' => $data[8]
        );
      }

      // store the companies array and set it to never expire
      // This doesnt need to expire, we can manually refresh the transient when we get a new CSV
      set_transient('winmo_companies', $companies, 0);
    }
    fclose($file);
  }
}
add_action('after_setup_theme', 'set_companies_transient');


function winmo_company_api($id, $type)
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

// Create transients for all images in the provided placeholder folder
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

function winmo_brand_transients($brand_id, $callback)
{
  $brand_details = get_transient('winmo_brand_' . $brand_id);

  // check to see if this brand was successfully retrieved from the cache
  if (false === $brand_details) {

    // Grab brand details from the API
    $brand_details = winmo_company_api($brand_id, "brand");

    // store the brand as a transient and set it to expire in 1 week
    set_transient('winmo_brand_' . $brand_id, $brand_details, 604800);
  }

  return $callback($brand_details);
}



// Create AJAX Call for Company Pager
add_action("wp_ajax_winmo_company_list", "winmo_company_list");
add_action("wp_ajax_nopriv_winmo_company_list", "winmo_company_list");

function winmo_company_list()
{
  $data = $_POST["data"];
  if (!wp_verify_nonce($_POST['nonce'], "winmo_filter_nonce")) {
    exit("There has been an error.");
  }

  $companies = get_transient('winmo_companies');

  // Turn filter values into an array
  $data = explode('&', $data);
  $filter = array();
  error_log(json_encode($data));
  foreach ($data as $string) :
    $keyval = explode('=', $string);
    $filter[$keyval[0]] = $keyval[1];
  endforeach;

  // Filter query
  $search_filter = isset($filter['search']) ? $filter['search'] : '';

  // Pull out all entries by alpha
  $html = json_encode($companies);
  $alpha = $filter['alpha'];

  $filtered = array_filter($companies, function ($company) use ($alpha) {
    $letter = substr($company['name'], 0, 1);

    if (strtolower($letter) == strtolower($alpha)) {
      return true;
    }
    // In non-alpha sort
    elseif ($alpha == "#") {
      if (!ctype_alpha($letter)) {  // If this letter is NOT alpha then keep it
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  });

  // filter companies array based on query
  if (!empty($search_filter)) {
    foreach ($filtered as $key => $company) {
      if (!empty($search_filter)) {

        if ((strpos($company['name'], $search_filter) !== false)) {
        } else {
          unset($filtered[$key]);
        }
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
    foreach ($filtered as $key => $company) {
      // Column shift
      if ($counter % $mod == 0) {
        if ($counter > 1) $html .= '</div><!-- /col -->';
        $html .= '<div class="col">';
      }
      $html .= '<a href="/company/' . $key . '/">' . $company['name'] . '</a>';
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
