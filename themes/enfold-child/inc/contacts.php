<?php

// Obtain the individual contact permalink by api_id
function get_contact_permalink($contact_id)
{

  global $wpdb;
  $result = null;

  $sql = "SELECT permalink FROM `winmo` WHERE type = 'contacts' AND `api_id` = '" . $contact_id . "' LIMIT 1";
  $result = $wpdb->get_var($sql);
  return $result;
}


// Grab individual contact from the database
function get_contact_information($contact_id)
{

  global $wpdb;

  // Pull company info from database
  $sql = "SELECT data FROM `winmo` WHERE `type` = 'contacts' AND `api_id` = '" . $contact_id . "' LIMIT 1";

  $result = $wpdb->get_var($sql);
  if ($result !== null) $result = json_decode($result);

  return $result;
}

// Put all contacts into a custom db table (because of how much data it is)
function set_contacts_information($results = array(), $atts = array())
{
  global $wpdb;
  $wpdb->suppress_errors = false;
  $wpdb->show_errors = false;
  
  $success = true;
  $error = "";
  $query = array();
  $file = ABSPATH . 'company-people-sitemap.txt';
  $parent_file = ABSPATH . 'content-sitemaps.xml';
  $sitemap_contents = "";
  extract($atts);

  error_log("PEr Page: ".$per_page);
  // Sitemap pager
  error_log($file);
  $file = checkforPagerReq($page, $per_page, $file, $parent_file);
  
  error_log($type . " page " . $page . " " . json_encode($atts));   // {"page":"539","total":"1033","last":false,"type":"company_contacts","first_total":"798"}
  
  // We're rebuilding (starting over)
  if (($page == 1) && ($type == "company_contacts")) { 
    error_log("Brand new contacts import.");

    // Reset temp table data to start a new import
    $permalinks = array();
    $undo_import = "DELETE FROM `winmo` WHERE `type` LIKE 'contacts2'";
    $wpdb->query( $undo_import );

    error_log('To do: update mod date on sitemap');
    siteMapCleanup($parent_file, $file);
  } else {
    $permalinks = get_transient('contacts_permalinks');
    if(!$permalinks) {
      // Transient expired, so lets generate a new one.
      $type = 'contacts2';
      $grab_permalinks = $wpdb->prepare( "SELECT permalink FROM `winmo` WHERE  type LIKE %s", $type );
      $permalinks = $wpdb->get_col( $grab_permalinks );
      //error_log("Please be a string: ".gettype($permalinks[0]));
      //error_log("Permalinks: ".json_encode($permalinks));
    }
  }

  // Prevent switch to agencies from breaking the pager
  //error_log($page . " (" . gettype($page) . ") + " . $first_total . "(" . gettype($first_total) . ")");
  if ($type == "agency_contacts") {
    
    $file = ABSPATH . 'agency-people-sitemap.txt';
    if($page == 1) {
      error_log("First page of agencies, filename changed to ".$file);
      siteMapCleanup($parent_file, $file);
    } else {

      $file = checkforPagerReq((int)$page, $per_page, $file, $parent_file);
      error_log("File?? ".$file);
    }
    $page = (int)$page + (int)$first_total;
    error_log("Inside " . $type . " so page is now: " . $page); // correct
  }

  //$contact_links = array();  // Current page permalinks

  foreach ($results as $contact) :
    $permalink = strtolower(str_replace(array(" ", "'"), '-', $contact['fname'] . ' ' . $contact['lname']));

    //$temparray = array_merge($permalinks,$contact_links);
    // Check if permalink already exists (agencies with identical names)  //
    $duplicates = array_filter($permalinks, function ($v) use ($permalink) {
      // Do the permalinks match exactly or close but with a -digit at the end?
      $answer = ($v == $permalink) || (preg_match('#(' . str_replace("+", "\+", $permalink) . ')-\d#', $v));
      return $answer;
    }, ARRAY_FILTER_USE_BOTH);
      
    if (sizeof($duplicates)) {
      //error_log("Found duplicates: ". json_encode($duplicates) . " ". sizeof($duplicates));
      $count = (int)sizeof($duplicates) + 1;
      $permalink .= "-" . $count;
    }

    // Save all basic info for the query
    $query[] = array($contact['id'], $contact['fname'] ." ".$contact['lname'], $permalink, json_encode($contact), $contact['lname']);
    
    // Save permalink into database
    $permalinks[] = $permalink;
    $sitemap_contents .= get_bloginfo('wpurl').'/decision_makers/'.$permalink."\n";

  endforeach;
  error_log("Last item of sitemap push: ".$permalink);

  $sql = "INSERT INTO winmo (`type`, `name`, `permalink`, `api_id`, `data`, `lname`) VALUES";
  
  // agency2 used as a placeholder to not override live data
  foreach($query as $columns):
    $sql .= "('contacts2', '".str_replace("'","\'",$columns[1])."', '".$columns[2]."', '".$columns[0]."', CAST('".addslashes($columns[3])."' AS JSON), '".str_replace("'","\'",$columns[4])."'),";
  endforeach;
  $sql = substr($sql,0,-1); // Remove trailing comma space

  // Incase the last import was interrupted, make sure this import won't fail when it finds a duplicate.
  $sql .= ' ON DUPLICATE KEY UPDATE ';
  $sql .= ' type = VALUES(type), name = VALUES(name), permalink = VALUES(permalink), api_id = VALUES(api_id), data = VALUES(data), lname = VALUES(lname);';

  $result = $wpdb->query( $sql );
  set_transient('contacts_permalinks', $permalinks, '1200'); // transient is only needed until the next loop


  if($result === false) {
    $error = "There was a problem importing page ".$page;
    error_log($error);
    error_log($wpdb->last_error);
    $success = false;

  } else {

    // After bulk import, bookmark our spot
    set_transient('contacts_last_page', $page, 0);

    // Import successfull, put this in the sitemap
    error_log("writing to:  ".$file);
    file_put_contents($file, $sitemap_contents, FILE_APPEND);

  }

  // We're at the end of the import - clean up
  if ($last) {
    delete_transient('contacts_last_page'); // Remove last page check
    
    error_log("We're finishing, but I don't think we actually need to do anything anymore.");

    // TO DO - Change the modify date on the sitemap
    
    // Change temp to official
    $deletesql = "DELETE FROM winmo WHERE type = 'contacts'";
    $wpdb->query( $deletesql );
    if($wpdb->last_error !== '') { 
      error_log("Ran into a problem deleting the old contacts items with ".$deletesql);
      error_log($wpdb->last_error);
    } else {
      $updatesql = "UPDATE winmo SET type = 'contacts' WHERE type = 'contacts2'";
      $wpdb->query( $updatesql );
      if($wpdb->last_error !== '') { 
        error_log("Ran into a problem updating contacts database ".$updatesql);
        error_log($wpdb->last_error);
        mail("christie@launchsnap.com","Winmo Database Error","Contacts API Update successfully removed old entries but failed in adding new entries.");
      } else {
        error_log("All IS WELL! LETS BE SUCCESSFULL!!!!!!");
        $last = true;
        delete_transient('contacts_permalinks');
      }
    }

  }

  return array('data' => $success, 'page' => $page, 'last' => $last, 'error' => $error);
}

function checkforPagerReq($page, $per_page, $filename,$parent_file) {
  $total_entries = (int)$per_page * $page;
  error_log("Total Entries: ".$total_entries);
  if ($total_entries >= 50000) {

    $pager = ceil($total_entries / 50000);

    // Mods of 50K are new files and should get mod triggers
    error_log("Mod? ". $total_entries % 50000);
    
    // If LAST page a mod then this one should be a new file.
    $last_total_entries = (int)$per_page * ($page - 1);
    if(($last_total_entries % 50000) === 0) {

      // Time for a new file
      $filename = str_replace('.txt','_'.$pager.'.txt', $filename);
      error_log("Checking for new filename: ".$filename);
      error_log("New file trigger here.");

      siteMapCleanup($parent_file, $filename);
    } elseif($pager > 1) {
      $filename = str_replace('.txt','_'.$pager.'.txt', $filename);
    }
  }
  return $filename;
}

function get_winmo_contact($permalink = '', $alpha = '', $api = '')
{
  global $wpdb;

  $wpdb->show_errors();

  // Pull all contacts from database
  $type = 'contacts';
  $args = array($type);
  $sql = "SELECT * FROM `winmo` WHERE `type` = %s";
  if (!empty($alpha)) {
    $sql .= ' AND `name` LIKE %s';
    $args[] = '% '.$wpdb->esc_like($alpha) . '%';
  }
  if (!empty($api)) {
    $sql .= ' AND `api_id` = %d';
    $args[] = $api;
  }
  if (!empty($permalink)) {
    $sql .= ' AND `permalink` = %s';
    $args[] = $permalink;
  }
  $sql .= ' GROUP BY(api_id)';

  $sql = $wpdb->prepare(
    $sql,
    $args
  );

  $result = $wpdb->get_results($sql);

  return $result;
}

function add_winmo_contact($contact, $status)
{
  global $wpdb;

  $howditgo = false;

  $wpdb->show_errors();
  foreach ($contact as $insert) :
    $howditgo = $wpdb->insert(
      'winmo_contacts',
      array(
        'api_id' => $insert[0],
        'first_name' => $insert[1],
        'last_name' => $insert[2],
        'page' => $insert[3],
        'permalink' => $insert[4],
        'status' => $status,
      )
    );
  endforeach;

  $wpdb->hide_errors();
  if (!$howditgo) return false;
}

// Show unlock button in header of contact pages
add_filter('avf_main_menu_nav', function ($stuff) {
  $contact = get_query_var('pid');
  if ($contact && is_page('contacts')) {
    $stuff .= '<div class="button"><a class="modal" href="#request_form"><img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/unlock.png"></a></div>';
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

  global $wpdb;
  $sql = "SELECT * FROM winmo WHERE type = 'contacts'";

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
     $sql .= ' AND LOWER(lname) LIKE \''.ucwords($alpha).'%\'';
  } 

  // keyword search on contact name
  if (!empty($search_filter)) {
    $sql .= ' AND name LIKE \'%'.$search_filter.'%\'';
  }

  // order by contact name
  $sql .= ' ORDER BY name ASC';
  
  $filtered = $wpdb->get_results($sql, 'ARRAY_A');

  // Define total products
  $total_items = sizeof($filtered);

  // Sort our filtered items
  usort($filtered, "last_name_sort");

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
      $data = json_decode($contact['data']);
      $html .= '<a href="/decision_makers/' . $contact['permalink'] . '/">' . $data->fname . ' ' . $data->lname . '</a>';
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

function last_name_sort($a, $b)
{
  $a1 = json_decode($a['data']);
  $b1 = json_decode($b['data']);
  return strcmp($a1->lname, $b1->lname);
}
