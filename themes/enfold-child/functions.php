<?php

// Stylesheet caching version
function avia_get_theme_version($which = 'parent')
{
  return '1.0.0.0.4';
}

include("inc/companies.php");

// Remove portfolio functionality from backend
function winmo_avia_overrides()
{
  remove_action('init', 'portfolio_register');
}
add_action('after_setup_theme', 'winmo_avia_overrides');

// Custom vars to use for dynamic URLs
function winmo_query_var($vars)
{
  $vars[] = "rid";
  return $vars;
}
add_filter('query_vars', 'winmo_query_var');

// Custom URL rewriting for dynamic pages
function winmo_rewrite_basic()
{
  add_rewrite_rule('^company/([^/]*)/?', 'index.php?page_id=20&rid=$matches[1]', 'top');
}
add_action('init', 'winmo_rewrite_basic');

// Enqueue styles and scripts when ready
function form_styles()
{
  //wp_enqueue_style( 'cf7_custom', get_stylesheet_directory_uri() . '/forms.css' );
  //wp_enqueue_script('pager', get_stylesheet_directory_uri() . '/assets/js/pager.js', array('jquery'), '1.0.0', true);
}
//add_action('wp_enqueue_scripts', 'form_styles', 100);

// Adjust cURL timeout length
function winmo_http_request_timeout()
{
  return 35;
}
add_filter('http_request_timeout', 'winmo_http_request_timeout');
