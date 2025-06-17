<?php
// Obtain the individual company permalink by api_id
function get_business_permalink($api_id)
{

  global $wpdb;
  $result = null;

  $sql = "SELECT permalink FROM `winmo` WHERE type = 'company' AND `api_id` = '" . $api_id . "' LIMIT 1";
  $result = $wpdb->get_var($sql);
  return $result;
}

// Enter items as company2 and change back to company at the end of import
function set_companies_information($results = array(), $atts = array())
{
  $page = $atts['page'];
  $last = $atts['last'];

  global $wpdb;
  $wpdb->suppress_errors = false;
  $wpdb->show_errors = false;

  error_log("Last is set to: ".$last);

  // We're rebuilding
  if ($page == 1) {
    //$industries = array();
    // Do we want to clear industry/company relations table? That would disable some features until import was complete
  } elseif ($page > 1) { // Dont change official companies array until all data is uploaded
    //$industries = get_current_industries();
  }

  $rework = array();
  $query = array();
  $companies = array(); // Keep track of companies and industries
  $count = highest_industries() + 1;
  error_log("New API page, count start is ".$count);
  $industries = get_current_industries(); // Industries already saved in the database
  $company_industries = array(); // Industries for this API page loop

  //error_log("Industries size: ".sizeof($industries));
  //error_log("Industries should come out with dealerships aready ". json_encode($industries['dealerships'])); 
  //error_log(json_encode($industries));

  foreach ($results as $company) :
    if (isset($company['name'])) :

      // Prepare Permalink and individual contact information
      $permalink = strtolower(str_replace(" ", '-', $company['name']));
      $permalink = str_replace(array(',-inc', ',-llc', "?", ".", ",", "'"), "", $permalink);
      $query[] = array($company['name'], $permalink, $company['id'], json_encode($company));

      // Now is a great time to grab industry information, too
      $list = $company['industries'];
      $company_industries_local = array(); // Industries for this company
      if (is_array($list)) {
        $list  = array_unique($list); // I've notice lots of repeats in the industry arrays
        error_log("Here is our industry list for ".$company['name']. " " . json_encode($list));
        foreach ($list as $industry) :

          // Turn industry into a machine name
          $industry_mx = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($industry)));
          $industry_mx = str_replace(array("---",".-","--","."), "-", $industry_mx);
          error_log("Determined permalink: ".$industry_mx);
          
          // PROBLEM: By the time we get to BMW of North America, LLC 'dealerships' should exist already in $industries.
          error_log("IF never gets called. Problem with our industries array. Is Data not going IN? Or is data not coming back?");
          if ((!isset($industries[$industry_mx])) && !isset($company_industries[$industry_mx])) {
            error_log("It's not found in Top or Middle , so add it to local with our new key made from COUNT ." . $count);
            $company_industries_local[$industry_mx] = array($count, ucwords($industry)); // Send to import
            error_log("COUNT only gets increased in this scenario.");
            $count++;
            
          } else {
            error_log("We're in the else. So we should already know about this industry.");
            $key = isset($industries[$industry_mx]) ? $industries[$industry_mx] : $company_industries[$industry_mx][0];
            error_log("So the industry id we found to use is: " .$key);
            $company_industries_local[$industry_mx] = array($key, ucwords($industry)); // Send to API page
        
          }
        endforeach;
        $companies[$company['id']] = $company_industries_local;
        error_log("Adding the contents of local to this company... (This is also what gets merged now into the Top array: " . json_encode($company_industries_local));
      }
      $company_industries = array_merge_recursive($company_industries, $company_industries_local); // Merge with other current API page newly found industry items
    endif;
  endforeach;

  // Run the bulk queries for Industry stuff for this entire API page
  // Store the updated industry list in the database
  // Also make a company/industry relation query
  if (sizeof($companies)) {
    $company_query = "INSERT INTO winmo_industries_companies (`api_id`, `industry_id`) VALUES";
    $industry_query = "INSERT INTO winmo_industries (`name`, `permalink`, `industry_id`) VALUES";
    $query_added = 0;

    foreach($companies as $companyID=>$industry):
      if(sizeof($industry)):
        foreach($industry as $permalink=>$indus):
          $query_added = 1;
          $company_query .= "('".$companyID."','".$indus[0]."'),";
          $industry_query .= "('".$indus[1]."','".$permalink."','".$indus[0]."'),";
        endforeach;
      endif;
    endforeach;

    if($query_added) {
      $industry_query = substr($industry_query,0,-1)." ON DUPLICATE KEY UPDATE ";
      $company_query = substr($company_query,0,-1)." ON DUPLICATE KEY UPDATE ";
      $industry_query .= "name = VALUES(name), permalink = VALUES(permalink), industry_id = VALUES(industry_id);";
      $company_query .= "api_id = VALUES(api_id), industry_id = VALUES(industry_id);";
      //error_log("Industry Query: ".$industry_query);
      //error_log("Company Query: ".$company_query);

      $industries_insert = $wpdb->query($industry_query);
      if($wpdb->last_error !== '') { 
        error_log("Industry Query: ".$industry_query);
        error_log($wpdb->last_error); 
        return array('data' => false); 
        exit;
      }
      $company_insert = $wpdb->query($company_query);
      //error_log("Howd the industry insert go? ".$industries_insert);
      //error_log("Howd the company go? ".$company_insert);
    }
  } else {
    error_log("No companies?");
  }

  // Run the bulk query for this entire API page
  $sql = "INSERT INTO winmo (`type`, `name`, `permalink`, `api_id`, `data`) VALUES";
  $update = "";

  // This could say 'company2' until complete?
  foreach($query as $columns):
    $sql .= "('company2', '".str_replace("'","\'",$columns[0])."', '".$columns[1]."', '".$columns[2]."', CAST('".addslashes($columns[3])."' AS JSON)),";
  endforeach;
  $sql = substr($sql,0,-1);
  $sql .= ' ON DUPLICATE KEY UPDATE ';
  $sql .= ' type = VALUES(type), name = VALUES(name), permalink = VALUES(permalink), api_id = VALUES(api_id), data = VALUES(data);';

  $result = $wpdb->query( $sql );
  if($result === false) {
    error_log("There was a problem importing page ".$page);
    error_log($wpdb->last_error);
  }
      
  // Last page in the API
  if ($last) {
    error_log("We're finishing, but I don't think we actually need to do anything anymore.");
    
    // Change temp to official
    $deletesql = "DELETE FROM winmo WHERE type = 'company'";
    $wpdb->query( $deletesql );
    if($wpdb->last_error !== '') { 
      error_log("Ran into a problem deleting the old company items with ".$deletesql);
      error_log($wpdb->last_error);
    } else {
      $updatesql = "UPDATE winmo SET type = 'company' WHERE type = 'company2'";
      $wpdb->query( $updatesql );
      if($wpdb->last_error !== '') { 
        error_log("Ran into a problem updating company database ".$updatesql);
        error_log($wpdb->last_error);
        mail("christie@launchsnap.com","Winmo Database Error","Company API Update successfully removed old entries but failed in adding new entries.");
      } else {
        error_log("All IS WELL! LETS BE SUCCESSFULL!!!!!!");
        $last = true;
      }
    }
  } 

  return array('data' => true, 'last' => $last);
}

function get_company($value, $how = 'api_id') {
  global $wpdb;
  $company_sql = "SELECT data FROM winmo WHERE type = 'company' AND ".$how." = '".$value."' LIMIT 1";
  $company = $wpdb->get_var($company_sql);
  return $company;
}

// Industry index start
function highest_industries() {
  global $wpdb;
  $id = $wpdb->get_var('SELECT industry_id FROM winmo_industries ORDER BY industry_id DESC');
  if($id === NULL) $id = 1;
  return $id;
}

// Grab existing industries from database
function get_current_industries() {
  global $wpdb;
  $industries_results = $wpdb->get_results('SELECT permalink, industry_id FROM winmo_industries', 'ARRAY_A');
  $industries = array();
  foreach ($industries_results as $result)
  {
    $industries[$result['permalink']] = $result['industry_id'];
  }
  return $industries;
}

// Grab existing industries from database
function get_all_industries($industry = "") {
  global $wpdb;
  $sql = 'SELECT permalink, name, industry_id FROM winmo_industries ';
  if (!empty($industry)) $sql .= "WHERE permalink = '".$industry."' ";
  $sql .= 'ORDER BY name ASC';
  if (!empty($industry)) $sql .= " LIMIT 1";
  error_log($sql);
  $industries = $wpdb->get_results($sql, 'ARRAY_A');
  return $industries;
}

// Get all companies associated with an industry
function get_companies_by_industry($industry_id) {
  global $wpdb;
  $sql = 'SELECT c.api_id, w.name, w.permalink FROM winmo_industries i INNER JOIN winmo_industries_companies c ON i.industry_id = c.industry_id LEFT JOIN winmo w ON c.api_id = w.api_id WHERE i.industry_id = \''.$industry_id.'\' AND w.type = \'company\'';
  error_log($sql);
  $companies = $wpdb->get_results($sql, 'ARRAY_A');
  return $companies;
}

// Show unlock button in header of company pages
add_filter('avf_main_menu_nav', function ($stuff) {
  $company = get_query_var('rid');
  if ($company && is_page('companies')) {
    $stuff .= '<div class="button"><a href="#request_form" class="modal"><img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/unlock.png"></a></div>';
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

  global $wpdb;
  $sql = "SELECT * FROM winmo WHERE type = 'company'";

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

  // keyword search on company name
  if (!empty($search_filter)) {
    $sql .= ' AND name LIKE \'%'.$search_filter.'%\'';
  }

  // order by company name
  $sql .= ' ORDER BY name ASC';

  $filtered = $wpdb->get_results($sql, 'ARRAY_A');

  // Define total products
  $total_items = sizeof($filtered);

  // Sort our filtered items
  // usort($filtered, "name_sort");


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
