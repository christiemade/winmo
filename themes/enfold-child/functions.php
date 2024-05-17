<?php

// Stylesheet caching version
function avia_get_theme_version($which = 'parent')
{
  return '1.0.0.0.39.42';
}

// Quick shortcode to display current year
function year_shortcode()
{
  $year = date('Y');
  return $year;
}
add_shortcode('year', 'year_shortcode');

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
  //wp_enqueue_script('fontawesome', get_stylesheet_directory_uri() . '/assets/fonts/js/all.min.js');

  wp_enqueue_script('popups', get_stylesheet_directory_uri() . '/assets/js/popups.js', array('jquery'), '1.0.0.8');
  wp_register_script('filters', get_stylesheet_directory_uri() . '/assets/js/filters.js', array('jquery'), '1.0.0.5');
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

add_action('ava_after_content_templatebuilder_page', function () { ?>
  <div class="row alternate_color ha-center" id="win-more">
    <div class="col container">
      <h2>Win More with Winmo</h2>
      <div style="padding:56.25% 0 0 0;position:relative;"><iframe src="https://player.vimeo.com/video/864820000?h=7bed84b047&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="Win More with Winmo"></iframe></div>
      <script src="https://player.vimeo.com/api/player.js"></script>
    </div>
  </div>

  <?php get_template_part('partials/footer', 'company'); ?>

  <div class="popup-wrapper">
    <div id="request_demo">
      <?php get_template_part('partials/sidebar_cta'); ?>
    </div>
  </div>
<?php
});
