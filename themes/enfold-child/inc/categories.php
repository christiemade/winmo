<?php
// Show request a demo button in header of category pages
add_filter('avf_main_menu_nav', function ($stuff) {
  if (is_page('industries')) {
    $stuff .= '<div class="button"><a href="https://www.winmo.com/profile-1/"><img src="' . get_stylesheet_directory_uri() . '/assets/img/categories/request-a-demo.png"></a></div>';
  }
  return $stuff;
});
