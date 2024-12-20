<?php

// Update or pulldown individual company information from the database
function set_company_information($company_id, $data = "", $type = "company")
{

  global $wpdb;

  // Pull company info from database
  $sql = "SELECT * FROM `winmo` WHERE `type` = '" . $type . "' AND `api_id` = '" . $company_id . "' LIMIT 1";
  $result = $wpdb->get_results($sql);

  if ($result) {
    $update_id = $result[0]->id;
    $result = json_decode($result[0]->data);
  }

  // Company info doesn't exist in the database, or we're here to change it
  if (!empty($data)) {
    // store the company's data into the DB table
    if ($result) {
      $sql = "UPDATE `winmo` SET `data` = CAST('" . addslashes($data) . "' AS JSON) WHERE id = '" . $update_id . "'";
    } else {
      $sql = "INSERT INTO `winmo` (`type`, `api_id`, `data`)
  VALUES('" . $type . "', '" . $company_id . "', CAST('" . addslashes($data) . "' AS JSON))";
    }
    //error_log($sql);
    $result = $wpdb->query($sql);
    if ($result) $result = $data;
  }

  return $result;
}

function set_companies_information($results = array(), $atts = array())
{
  $companies = get_transient('winmo_companies_temp');
  $page = $atts['page'];
  $last = $atts['last'];

  // if we're rebuilding (page 1) then lets reset the array
  if ($page == 1) { // Rebuild companies array
    $companies = array();
    $industries = array();
  } elseif ($page > 1) { // Dont change official companies array until all data is uploaded
    $industries = get_option('winmo_industries');
  }

  $rework = array();

  foreach ($results as $company) :
    if (isset($company['name'])) :

      // Prepare Permalink and individual contact information
      $permalink = strtolower(str_replace(" ", '-', $company['name']));
      $permalink = str_replace(array(',-inc', ',-llc', "?", ".", ",", "'"), "", $permalink);
      $rework[$company['id']] = array(
        'name' => $company['name'],
        'permalink' => $permalink
      );
      set_company_information($company['id'], json_encode($company), 'company');

      // Now is a great time to grab industry information, too
      $list = $company['industries'];
      if (is_array($list)) {
        foreach ($list as $industry) :
          // Turn industry into a machine name
          $industry_mx = strtolower(str_replace(array(' ', '&', ':', ','), '-', trim($industry)));
          $industry_mx = str_replace("---", "-", $industry_mx);
          if (!isset($industries[$industry_mx])) {
            $industries[$industry_mx] = array(
              'name' => ucwords($industry),
              'companies' => array()
            );
          }
        endforeach;
      }
      $industries[$industry_mx]['companies'][$permalink] = $company['name'];
    endif;
  endforeach;
  $companies = $companies ? $companies + $rework : $rework;

  // store the industry list as an option
  if (sizeof($industries)) update_option('winmo_industries', $industries);

  // store the companies array and set it to never expire
  // This doesnt need to expire, we can manually refresh the array when we get a new CSV
  if ($last) {
    error_log("Warning: we are deleting a transient...winmo_companies_temp");
    delete_transient('winmo_companies_temp'); // Remove temporary transient
    update_option('winmo_companies', $companies); 
  } else {
    set_transient('winmo_companies_temp', $companies, 0); // Add more to our temp transient
  }

  return array('data' => true);
}


// Show unlock button in header of company pages
add_filter('avf_main_menu_nav', function ($stuff) {
  $company = get_query_var('rid');
  if ($company && is_page('companies')) {
    $stuff .= '<div class="button"><a href="#request_demo" class="modal"><img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/unlock.png"></a></div>';
  }
  return $stuff;
});

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


// Create AJAX Call for Company Pager
add_action("wp_ajax_winmo_company_list", "winmo_company_list");
add_action("wp_ajax_nopriv_winmo_company_list", "winmo_company_list");

function winmo_company_list()
{
  $data = $_POST["data"];
  if (!wp_verify_nonce($_POST['nonce'], "winmo_filter_nonce")) {
    exit("There has been an error.");
  }

  $companies = get_option('winmo_companies');

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
  $html = json_encode($companies);
  $alpha = $filter['alpha'];

  // If an alpha sort is provided, do that first
  if ($alpha) {
    $filtered = array_filter($companies, function ($company) use ($alpha) {
      $letter = substr($company['name'], 0, 1);

      if (strtolower($letter) == strtolower($alpha)) {
        return true;
      }
      // In non-alpha sort
      elseif (in_array($alpha, array("#", "%23"))) {
        if (!ctype_alpha($letter)) {  // If this letter is NOT alpha then keep it
          return true;
        } else {
          return false;
        }
      } else {
        return false;
      }
    });
  } else {
    $filtered = $companies;
  }

  // filter companies array based on query
  if (!empty($search_filter)) {

    foreach ($filtered as $key => $company) {
      if (!empty($search_filter)) {
        if ((stripos($company['name'], urldecode($search_filter)) !== false)) {
        } else {
          unset($filtered[$key]);
        }
      }
    }
  }

  // Define total products
  $total_items = sizeof($filtered);

  // Sort our filtered items
  usort($filtered, "name_sort");


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
      $html .= '<a href="/company/' . $company['permalink'] . '/">' . $company['name'] . '</a>';
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

function name_sort($a, $b)
{
  return strcmp($a['name'], $b['name']);
}
