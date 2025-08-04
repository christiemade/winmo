<?php
add_action('rest_api_init', function () {
  header("Access-Control-Allow-Origin: *");
});

function winmo_wp_admin_style()
{
  wp_register_style('custom_wp_admin_css', get_stylesheet_directory_uri() . '/admin-style.css', false, '1.0.29');
  wp_enqueue_style('custom_wp_admin_css');
  wp_register_script('api', get_stylesheet_directory_uri() . '/assets/js/api.js', array('jquery'), '1.0.2.40');
  wp_localize_script('api', 'apiAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
  wp_enqueue_script('api');
}
add_action('admin_enqueue_scripts', 'winmo_wp_admin_style');

add_action('admin_menu', 'my_admin_menu');

function my_admin_menu()
{
  add_menu_page('API Refresh', 'API', 'manage_options', 'inc/admin.php', 'api_refresh_admin_page', 'dashicons-tickets', 6);
}

function siteMapCleanup($sitemap, $filename) {
  error_log("Emptying ".$filename);

  file_put_contents($filename, ''); // Empty the sitemap file
  error_log("Emptying ".$filename);

  $parent_sitemap = file_get_contents($sitemap);
  
  if($parent_sitemap) {
    $parent_sitemap = updateSitemapDate($parent_sitemap, $filename);
    error_log("NEw sitemap file: ".$parent_sitemap);
    file_put_contents($sitemap, $parent_sitemap);
    
  }
}

function updateSitemapDate($parent_sitemap, $filename) {

  if($parent_sitemap) {
    // Location of this sitemap
    $lastslash = strrpos($filename,"/") + 1; // Remove dir path
    $local_file_name = substr($filename,$lastslash);
    $thisloc = strpos($parent_sitemap, $local_file_name);

    // File needs to be added to our sitemap
    if(!$thisloc) {
      // Add new file to the sitemap
      $last_sitemap = strrpos($parent_sitemap,'</sitemap>') + 10;
      $new_sitemap = "\n\t".'<sitemap>'."\r\n\t\t".'<loc>'.get_bloginfo('wpurl')."/".$local_file_name.'</loc>'."\r\n\t\t".'<lastmod>'.date('Y-m-d').'</lastmod>'."\r\n\t".'</sitemap>';
      $parent_sitemap = substr_replace($parent_sitemap, $new_sitemap, $last_sitemap, 0); 
    } else {
      // Update the date for this sitemap in the main file
      $datelocation = strpos($parent_sitemap, '<lastmod>', $thisloc) + 9;
      error_log($datelocation);
      $parent_sitemap = substr_replace($parent_sitemap, date('Y-m-d'), $datelocation, 10);
    }
  }
  return $parent_sitemap;
}

function api_refresh_admin_page()
{
?>
  <div class="wrap">
    <h1>API Refresh</h1>
    <p>Each refresh will take a significant amount of time. Please do not close the browser window until the process is complete!</p>
    <div class="row loaded">
      <div class="col">Companies</div>
      <div class="col"><input type="button" id="company_launch" class="launch" value="Update"></div>
      <div class="col">
        <div id="company_progress" class="progress">
          <div></div>
        </div>
        <div>Should take roughly 1 hour, 6 minutes on a non-quarantine server.</div>
      </div>
    </div>
    <div class="row loaded">
      <div class="col">Agencies</div>
      <div class="col"><input type="button" id="agency_launch" class="launch" value="Update"></div>
      <div class="col">
        <div id="agency_progress" class="progress">
          <div></div>
        </div>
        <div>Should take roughly 8 minutes on a non-quarantine server.</div>
      </div>
    </div>
    <div class="row loaded">
      <div class="col">Contacts</div>
      <div class="col"><input type="button" id="contacts_launch" class="launch" value="<?php
                                                                                        $last_contact_page = get_transient('contacts_last_page');
                                                                                        print $last_contact_page > 1 ? "Resume" : "Update";
                                                                                        ?>"></div>
      <div class="col">
        <div id="contacts_progress" class="progress">
          <div></div>
        </div>
        <div>Should take roughly 4.5 hours on a non-quarantine server.</div>
      </div>
    </div>
  </div>
<?php
}
