<?php
function set_agencies_transient()
{
  $agencies = get_transient('winmo_agencies');

  // check to see if agencies were successfully retrieved from the cache
  if (false === $agencies) {
    // do this if no transient set
    $agencies = array();

    if ($file = fopen(get_stylesheet_directory() . "/inc/agencies.csv", "r")) {
      while (($data = fgetcsv($file)) !== FALSE) {
        if (!strpos($data[0], 'Id')) {
          $permalink = strtolower(str_replace(" ", '-', $data[2]));
          $permalink = str_replace(array(',-inc', ',-llc', "?", ".", ","), "", $permalink);
          $agencies[$data[0]] = array(
            'name' => $data[2],
            'location' => $data[6],
            'state' => $data[10],
            'industry' => $data[14],
            'permalink' => $permalink
          );
        }
      }

      // store the agencies array and set it to never expire
      // This doesnt need to expire, we can manually refresh the transient when we get a new CSV
      set_transient('winmo_agencies', $agencies, 0);
    }
    fclose($file);
  }
}
add_action('after_setup_theme', 'set_agencies_transient');

// Show unlock button in header of agency pages
add_filter('avf_main_menu_nav', function ($stuff) {
  $agency = get_query_var('rid');
  if ($agency && is_page('agencies')) {
    $stuff .= '<div class="button"><a class="modal" href="https://www.winmo.com/profile-1/"><img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/unlock.png"></a></div>';
  }
  return $stuff;
});

// Create AJAX Call for Agency Pager
add_action("wp_ajax_winmo_agency_list", "winmo_agency_list");
add_action("wp_ajax_nopriv_winmo_agency_list", "winmo_agency_list");

function winmo_agency_list()
{
  $data = $_POST["data"];
  if (!wp_verify_nonce($_POST['nonce'], "winmo_filter_nonce")) {
    exit("There has been an error.");
  }

  $agencies = get_transient('winmo_agencies');

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
  $html = json_encode($agencies);
  $alpha = $filter['alpha'];

  // If an alpha sort is provided, do that first
  if ($alpha) {
    $filtered = array_filter($agencies, function ($agency) use ($alpha) {
      $letter = substr($agency['name'], 0, 1);

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
  } else {
    $filtered = $agencies;
  }

  // filter companies array based on query
  if (!empty($search_filter)) {
    foreach ($filtered as $key => $agency) {
      if ((stripos($agency['name'], urldecode($search_filter)) !== false)) {
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
    foreach ($filtered as $key => $agency) {
      // Column shift
      if ($counter % $mod == 0) {
        if ($counter > 1) $html .= '</div><!-- /col -->';
        $html .= '<div class="col">';
      }
      $html .= '<a href="/agency/' . $key . '/">' . $agency['name'] . '</a>';
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
