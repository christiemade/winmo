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
        if (!strpos($data[0], 'Id')) $agencies[$data[0]] = array(
          'name' => $data[2],
          'location' => $data[6],
          'state' => $data[10],
        );
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
    $stuff .= '<div class="button"><a href="https://www.winmo.com/profile-1/"><img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/unlock.png"></a></div>';
  }
  return $stuff;
});
