<?php

if (file_exists(__DIR__ . '/vendor/autoload.php'))
  require __DIR__ . '/vendor/autoload.php';

// Stylesheet caching version
function avia_get_theme_version($which = 'parent')
{
  return '1.0.0.0.39.63';
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

// CRON Jobs
include("inc/admin.php");

// API Call(s)
require_once("inc/winmo_api.php");

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
  $vars[] = "wid";
  $vars[] = "pid";
  $vars[] = "state";
  return $vars;
}
add_filter('query_vars', 'winmo_query_var');

// Custom URL rewriting for dynamic pages
function winmo_rewrite_basic()
{
  // Allow company page to have any ID
  $company_page = get_transient('winmo_company_page');
  if (!$company_page) {
    $company_page = get_page_by_path('companies');
    set_transient('winmo_company_page', $company_page, 5000);
  }
  $agency_page = get_transient('winmo_agency_page');
  if (!$agency_page) {
    $agency_page = get_page_by_path('agencies');
    set_transient('winmo_agency_page', $agency_page, 5000);
  }
  $agencies_page = get_transient('winmo_agencies_page');
  if (!$agencies_page) {
    $agencies_page = get_page_by_path('top-agencies');
    set_transient('winmo_agencies_page', $agencies_page, 5000);
  }
  $contact_page = get_transient('winmo_contact_page');
  if (!$contact_page) {
    $contact_page = get_page_by_path('contacts');
    set_transient('winmo_contact_page', $contact_page, 5000);
  }

  $industries_page = get_transient('winmo_industries_page');
  if (!$industries_page) {
    $industries_page = get_page_by_path('industries');
    set_transient('winmo_industries_page', $industries_page, 5000);
  }

  // Unique URLS generated for new website
  add_rewrite_rule('^company/([^/]*)/?$', 'index.php?page_id=' . $company_page->ID . '&rid=$matches[1]', 'top');
  add_rewrite_rule('^agency/([^/]*)/?$', 'index.php?page_id=' . $agency_page->ID . '&rid=$matches[1]', 'top');
  add_rewrite_rule('^agencies/([a-z]{2})/?', 'index.php?page_id=' . $agencies_page->ID . '&state=$matches[1]', 'top');
  add_rewrite_rule('^industries/([^/]*)/?', 'index.php?page_id=' . $industries_page->ID . '&rid=$matches[1]', 'top');
  add_rewrite_rule('^decision_makers/([^/]*)/?$', 'index.php?page_id=' . $contact_page->ID . '&pid=$matches[1]', 'top');

  // Redirects from original website
  add_rewrite_rule('^company/([^/]+)/([a-z][a-z])/([^/]+)/([^/]+)/([0-9]*)/?$', 'index.php?page_id=' . $company_page->ID . '&wid=$matches[5]', 'top');
  add_rewrite_rule('^agency/([a-z][a-z])/([^/]+)/([^/]+)/([0-9]*)/?$', 'index.php?page_id=' . $agency_page->ID . '&wid=$matches[4]', 'top');
  add_rewrite_rule('^decision_makers/([a-z][a-z])/([^/]+)/([^/]+)/([^/]+)/([0-9]*)/?$', 'index.php?page_id=' . $contact_page->ID . '&wid=$matches[5]', 'top');
}
add_action('init', 'winmo_rewrite_basic');

// Enqueue styles and scripts when ready
function winmo_load_scipts()
{
  wp_dequeue_script('avia-module-slideshow-video');
  wp_register_script('gsap', get_stylesheet_directory_uri() . '/assets/js/gsap.min.js');
  wp_register_script('scrollTrigger', get_stylesheet_directory_uri() . '/assets/js/ScrollTrigger.min.js', array('gsap'));
  wp_register_script('sticky-nav', get_stylesheet_directory_uri() . '/assets/js/sticky-nav.js', array('jquery', 'gsap', 'scrollTrigger'), '1.0.0.8');
  //wp_enqueue_script('fontawesome', get_stylesheet_directory_uri() . '/assets/fonts/js/all.min.js');

  wp_enqueue_script('popups', get_stylesheet_directory_uri() . '/assets/js/popups.js', array('jquery'), '1.0.0.9');
  wp_register_script('filters', get_stylesheet_directory_uri() . '/assets/js/filters.js', array('jquery'), '1.0.0.7');
  wp_localize_script('filters', 'winmoAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
  wp_enqueue_script('filters');
}
add_action('wp_enqueue_scripts', 'winmo_load_scipts', 100);

// Adjust cURL timeout length
add_filter('http_request_timeout', function () {
  return 60;
});

// Create table for WINMO content if it doesn't exist yet
add_action('after_switch_theme', function () {
  global $wpdb;
  $sql = 'CREATE TABLE IF NOT EXISTS `winmo` (
    `id` int NOT NULL,
    `type` varchar(20) NOT NULL,
    `api_id` int NOT NULL,
    `data` json NOT NULL
  )';
  $wpdb->query($sql);

  $wpdb->query('ALTER TABLE `winmo`
      ADD PRIMARY KEY (`api_id`),
      ADD KEY `id` (`id`)');

  $wpdb->query('CREATE TABLE `winmo_contacts` (
  `id` int NOT NULL,
  `api_id` int NOT NULL,
  `first_name` varchar(60) NOT NULL,
  `last_name` varchar(60) NOT NULL,
  `permalink` varchar(30) NOT NULL,
  `page` int NOT NULL,
  `status` varchar(10) NOT NULL
');


  $wpdb->query('ALTER TABLE `winmo_contacts`
  ADD PRIMARY KEY (`id`)');


  $wpdb->query('ALTER TABLE `winmo_contacts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT');
}, 10);


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
