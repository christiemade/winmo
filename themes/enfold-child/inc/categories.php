<?php
// Show request a demo button in header of category pages
add_filter('avf_main_menu_nav', function ($stuff) {
  if (is_page('industries')) {
    $stuff .= '<div class="button"><a href="https://www.winmo.com/profile-1/"><img src="' . get_stylesheet_directory_uri() . '/assets/img/categories/request-a-demo.png"></a></div>';
  }
  return $stuff;
});

function get_industry_transient()
{
  $industries = get_transient('winmo_industries');

  // check to see if industries is already saved
  if (false === $industries) {

    // do this if no transient set
    $companies = get_transient('winmo_companies');
    $industries = array();
    foreach ($companies as $cid => $company) :
      $list = explode(",", $company['industry']);
      foreach ($list as $industry) :
        // Turn industry into a machine name
        $industry_mx = strtolower(str_replace(array(' ', '&'), '-', trim($industry)));
        $industry_mx = str_replace("---", "-", $industry_mx);
        if (!isset($industries[$industry_mx])) {
          $industries[$industry_mx] = array(
            'name' => $industry,
            'companies' => array()
          );
        }
      endforeach;
      $industries[$industry_mx]['companies'][$cid] = $company['name'];
    endforeach;

    // store the industry list as a transient
    set_transient('winmo_industries', $industries, 0);
  }
  return $industries;
}
