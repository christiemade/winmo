<?php
function set_agencies_transient($results = array(), $atts = array())
{
  $agencies = get_option('winmo_agencies');
  $page = $atts['page'];
  $last = $atts['last'];

  // if we're rebuilding (page 1) then lets reset the array
  if ($page == 1) { // Rebuild transient
    $agencies = array();
  } elseif ($page > 1) { // Dont change transient until all data is uploaded
    $agencies = get_transient('winmo_agencies_temp');
  }

  // check to see if agencies were successfully retrieved from the cache
  $rework = array();
  foreach ($results as $agency) :

    $permalink = strtolower(str_replace(" ", '-', $agency['name']));
    $permalink = str_replace(array(',-inc', ',-llc', "?", ".", ",", ")", "("), "", $permalink);

    // Special non-english character handling
    setlocale(LC_ALL, 'en_US.UTF8');
    $permalink = iconv("utf-8", "ascii//TRANSLIT", $permalink);

    // Check if permalink already exists (agencies with identical names)
    $duplicates = array_filter($agencies, function ($v) use ($permalink) {
      // Do the permalinks match exactly or close but with a -digit at the end?
      $answer = ($v['permalink'] == $permalink) || (preg_match('#(' . str_replace("+", "\+", $permalink) . ')-\d#', $v['permalink']));
      return $answer;
    }, ARRAY_FILTER_USE_BOTH);
    if (!sizeof($duplicates)) {
      // Check if permalink already exists - within current loop
      $duplicates = array_filter($rework, function ($v) use ($permalink) {
        // Do the permalinks match exactly or close but with a -digit at the end?
        $answer = ($v['permalink'] == $permalink) || (preg_match('#(' . str_replace("+", "\+", $permalink) . ')-\d#', $v['permalink']));
        return $answer;
      }, ARRAY_FILTER_USE_BOTH);
    }
    if (sizeof($duplicates)) $permalink .= "-" . ceil(sizeof($duplicates) + 1);

    $rework[$agency['id']] = array(
      'name' => $agency['name'],
      'location' => $agency['location'],
      'state' => $agency['location']['state'],
      //'industry' => $data[14],
      'permalink' => $permalink
    );

    //error_log($agency['id'] . " entering for " . $agency['name']);
    set_company_transient($agency['id'], json_encode($agency), 'agency');
  endforeach;

  $agencies = $agencies ? $agencies + $rework : $rework;
  $transient_name = 'winmo_agencies_temp';
  if ($last) {
    delete_transient($transient_name); // Remove temporary transient
    $transient_name = 'winmo_agencies';  // Last page, now update officialdelete_transient($transient_name); // Remove temporary transient
    delete_transient($transient_name); // Remove previous transient
    delete_transient('winmo_agencies_by_state');
  }
  set_transient($transient_name, $agencies, 0);
  return array('data' => true);
}

// Show unlock button in header of agency pages
add_filter('avf_main_menu_nav', function ($stuff) {
  $agency = get_query_var('rid');
  if ($agency && is_page('agencies')) {
    $stuff .= '<div class="button"><a class="modal" href="#request_demo"><img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/unlock.png"></a></div>';
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
      //error_log($alpha);
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

  // Sort our filtered items
  usort($filtered, "name_sort");


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
      $html .= '<a href="/agency/' . $agency['permalink'] . '/">' . $agency['name'] . '</a>';
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
