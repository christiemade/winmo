<?php
// Enter items as agency2 and change back to company at the end of import
function set_agencies_information($results = array(), $atts = array())
{
  $page = $atts['page'];
  $last = $atts['last'];
  $file = ABSPATH . 'agencies-sitemap.txt';
  $parent_file = ABSPATH . 'content-sitemaps.xml';
  $error = "";

  global $wpdb;
  $wpdb->suppress_errors = false;
  $wpdb->show_errors = false;
  $sitemap_contents = "";

  $success = true;

  // check to see if agencies were successfully retrieved from the cache
  $query = array();
  if ($page == 1) {
    // Reset temp table data to start a new import
    $permalinks = array();
    $undo_import = "DELETE FROM `winmo` WHERE `type` LIKE 'agency2'";
    $wpdb->query( $undo_import );
    
    siteMapCleanup($parent_file, $file);
    
  } else {
    $permalinks = get_transient('agency_permalinks');
  }

  foreach ($results as $agency) :

    $permalink = strtolower(str_replace(" ", '-', $agency['name']));
    $permalink = str_replace(array(',-inc', ',-llc', "?", ".", ",", "'", ")", "("), "", $permalink);

    // Special non-english character handling
    setlocale(LC_ALL, 'en_US.UTF8');
    $permalink = iconv("utf-8", "ascii//TRANSLIT", $permalink);

    // Check if permalink already exists (agencies with identical names)
    $duplicates = array_filter($permalinks, function ($v) use ($permalink) {
      // Do the permalinks match exactly or close but with a -digit at the end?
      $answer = ($v == $permalink) || (preg_match('#(' . str_replace("+", "\+", $permalink) . ')-\d#', $v));
      return $answer;
    }, ARRAY_FILTER_USE_BOTH);
    
    if (sizeof($duplicates)) $permalink .= "-" . ceil(sizeof($duplicates) + 1);

    $query[] = array($agency['name'], $permalink, $agency['id'], json_encode($agency));
    
    $permalinks[] = $permalink;
    $sitemap_contents .= get_bloginfo('wpurl').'/agency/'.$permalink."\n";

  endforeach;

  $sql = "INSERT INTO winmo (`type`, `name`, `permalink`, `api_id`, `data`) VALUES";
  
  // agency2 used as a placeholder to not override live data
  foreach($query as $columns):
    $sql .= "('agency2', '".str_replace("'","\'",$columns[0])."', '".$columns[1]."', '".$columns[2]."', CAST('".addslashes($columns[3])."' AS JSON)),";
  endforeach;
  $sql = substr($sql,0,-1).';';
  //$sql .= ' ON DUPLICATE KEY UPDATE ';
  //$sql .= ' type = VALUES(type), name = VALUES(name), 
  //permalink = CONCAT(permalink, IF(RIGHT(permalink, 1) REGEXP "[0-9]", CAST(RIGHT(permalink, 1) AS UNSIGNED) + 1, "-1")), 
  //api_id = VALUES(api_id), data = VALUES(data);';

  $result = $wpdb->query( $sql );
  set_transient('agency_permalinks', $permalinks, '1200');

  if($result === false) {
    $error = "There was a problem importing page ".$page;
    error_log($error);
    $undo_import = "DELETE FROM `winmo` WHERE `type` LIKE 'agency2'";
    error_log($wpdb->last_error);
    $success = false;
    $wpdb->query( $undo_import );
  } else {
    // Import successfull, put this in the sitemap
    error_log("writing to sitemap").
    file_put_contents($file, $sitemap_contents, FILE_APPEND);
  }

  // Last page in the API
  if ($last) {
    error_log("We're finishing, but I don't think we actually need to do anything anymore.");
    
    // Change temp to official
    $deletesql = "DELETE FROM winmo WHERE type = 'agency'";
    $wpdb->query( $deletesql );
    if($wpdb->last_error !== '') { 
      error_log("Ran into a problem deleting the old agency items with ".$deletesql);
      error_log($wpdb->last_error);
    } else {
      $updatesql = "UPDATE winmo SET type = 'agency' WHERE type = 'agency2'";
      $wpdb->query( $updatesql );
      if($wpdb->last_error !== '') { 
        error_log("Ran into a problem updating agency database ".$updatesql);
        error_log($wpdb->last_error);
        mail("christie@launchsnap.com","Winmo Database Error","Agency API Update successfully removed old entries but failed in adding new entries.");
      } else {
        error_log("All IS WELL! LETS BE SUCCESSFULL!!!!!!");
        $last = true;
        delete_transient('agency_permalinks');
      }
    }
  }

  return array('data' => $success, 'last' => $last, 'error' => $error);
}

// Show unlock button in header of agency pages
add_filter('avf_main_menu_nav', function ($stuff) {
  $agency = get_query_var('rid');
  if ($agency && is_page('agencies')) {
    $stuff .= '<div class="button"><a class="modal" href="#request_form"><img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/unlock.png"></a></div>';
  }
  return $stuff;
});

// Create AJAX Call for Agency Pager
add_action("wp_ajax_winmo_agency_list", "winmo_agency_list");
add_action("wp_ajax_nopriv_winmo_agency_list", "winmo_agency_list");

function get_agencies_by_state($state) {
  global $wpdb;
  $sql = "SELECT * FROM winmo WHERE type = 'agency' AND data LIKE '%\"state\": \"".strtoupper($state)."\",%'";
  $sql .= ' ORDER BY name ASC';
error_log($sql);
  return $wpdb->get_results($sql, 'ARRAY_A');
}

function winmo_agency_list()
{
  $data = $_POST["data"];
  if (!wp_verify_nonce($_POST['nonce'], "winmo_filter_nonce")) {
    exit("There has been an error.");
  }

  global $wpdb;
  $sql = "SELECT * FROM winmo WHERE type = 'agency'";

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
    $sql .= ' AND LOWER(name) LIKE \''.strtolower($alpha).'%\'';
  } 

  // keyword search on agency name
  if (!empty($search_filter)) {
    $sql .= ' AND name LIKE \'%'.$search_filter.'%\'';
  }

  // order by company name
  $sql .= ' ORDER BY name ASC';

  $filtered = $wpdb->get_results($sql, 'ARRAY_A');

  // Define total products
  $total_items = sizeof($filtered);

  // Sort our filtered items
  //usort($filtered, "name_sort");


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