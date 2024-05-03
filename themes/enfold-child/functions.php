<?php

// Stylesheet caching version
function avia_get_theme_version($which = 'parent')
{
  return '1.0.0.0.38.82';
}

// Allow for overriding of Enfold templates
add_filter('avia_load_shortcodes', 'avia_include_shortcode_template', 15, 1);
function avia_include_shortcode_template($paths)
{
  $template_url = get_stylesheet_directory();
  array_unshift($paths, $template_url . '/shortcodes/');
  return $paths;
}

// Company display related hooks
include("inc/companies.php");

// Agency display related hooks
include("inc/agencies.php");

// Contacts display related hooks
include("inc/contacts.php");

// Industry/Category display related hooks
include("inc/categories.php");

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
  $vars[] = "pid";
  $vars[] = "state";
  return $vars;
}
add_filter('query_vars', 'winmo_query_var');

// Custom URL rewriting for dynamic pages
function winmo_rewrite_basic()
{
  // Allow company page to have any ID
  $company_page = get_page_by_path('companies');
  $agency_page = get_page_by_path('agencies');
  $agencies_page = get_page_by_path('top-agencies');
  $contact_page = get_page_by_path('contacts');
  $industries_page = get_page_by_path('industries');

  add_rewrite_rule('^company/([^/]*)/?', 'index.php?page_id=' . $company_page->ID . '&rid=$matches[1]', 'top');
  add_rewrite_rule('^agency/([^/]*)/?', 'index.php?page_id=' . $agency_page->ID . '&rid=$matches[1]', 'top');
  add_rewrite_rule('^agencies/([a-z]{2})/?', 'index.php?page_id=' . $agencies_page->ID . '&state=$matches[1]', 'top');
  add_rewrite_rule('^industries/([^/]*)/?', 'index.php?page_id=' . $industries_page->ID . '&rid=$matches[1]', 'top');
  add_rewrite_rule('^decision_makers/([^/]*)/?', 'index.php?page_id=' . $contact_page->ID . '&pid=$matches[1]', 'top');
}
add_action('init', 'winmo_rewrite_basic');

// Enqueue styles and scripts when ready
function winmo_load_scipts()
{
  wp_register_script('gsap', get_stylesheet_directory_uri() . '/assets/js/gsap.min.js');
  wp_register_script('scrollTrigger', get_stylesheet_directory_uri() . '/assets/js/ScrollTrigger.min.js', array('gsap'));
  wp_register_script('sticky-nav', get_stylesheet_directory_uri() . '/assets/js/sticky-nav.js', array('jquery', 'gsap', 'scrollTrigger'), '1.0.0.8');
  //wp_enqueue_style( 'cf7_custom', get_stylesheet_directory_uri() . '/forms.css' );
  //wp_enqueue_script('pager', get_stylesheet_directory_uri() . '/assets/js/pager.js', array('jquery'), '1.0.0', true);
  wp_enqueue_script('fontawesome', get_stylesheet_directory_uri() . '/assets/fonts/js/all.min.js');

  wp_register_script('filters', get_stylesheet_directory_uri() . '/assets/js/filters.js', array('jquery'), '1.0.0.2');
  wp_localize_script('filters', 'winmoAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
  wp_enqueue_script('filters');
}
add_action('wp_enqueue_scripts', 'winmo_load_scipts', 100);

// Adjust cURL timeout length
add_filter('http_request_timeout', function () {
  return 60;
});

// Add custom body classes to each template
add_filter('body_class', function ($classes) {
  global $post;
  if (isset($post)) $classes = array_merge($classes, array($post->post_name));
  return $classes;
});
