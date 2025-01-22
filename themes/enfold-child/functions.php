<?php

if (file_exists(__DIR__ . '/vendor/autoload.php'))
  require __DIR__ . '/vendor/autoload.php';

// Stylesheet caching version
function avia_get_theme_version($which = 'parent')
{
  return '1.0.0.0.39.75';
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

// SEO related adjustments
include("inc/seo.php");

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
  $company_page = get_option('winmo_company_page');
  if (!$company_page) {
    $company_page = get_page_by_path('companies');
    update_option('winmo_company_page', $company_page);
  }
  $agency_page = get_option('winmo_agency_page');
  if (!$agency_page) {
    $agency_page = get_page_by_path('agencies');
    update_option('winmo_agency_page', $agency_page);
  }
  $agencies_page = get_option('winmo_agencies_page');
  if (!$agencies_page) {
    $agencies_page = get_page_by_path('top-agencies');
    update_option('winmo_agencies_page', $agencies_page);
  }
  $contact_page = get_option('winmo_contact_page');
  if (!$contact_page) {
    $contact_page = get_page_by_path('contacts');
    update_option('winmo_contact_page', $contact_page);
  }

  $industries_page = get_option('winmo_industries_page');
  if (!$industries_page) {
    $industries_page = get_page_by_path('industries');
    update_option('winmo_industries_page', $industries_page);
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
  wp_enqueue_script('hubspot', '//js.hsforms.net/forms/embed/v2.js');

  wp_enqueue_script('popups', get_stylesheet_directory_uri() . '/assets/js/popups.js', array('jquery'), '1.0.0.24');
  wp_register_script('filters', get_stylesheet_directory_uri() . '/assets/js/filters.js', array('jquery'), '1.0.0.7');
  wp_localize_script('filters', 'winmoAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
  wp_enqueue_script('filters');

  wp_dequeue_style( 'wp-block-library' ); // Wordpress core
  wp_dequeue_style( 'wp-block-library-theme' ); // Wordpress core
  wp_dequeue_style( 'wc-block-style' ); // WooCommerce
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

      <?php
      print do_shortcode("[av_video src='https://player.vimeo.com/video/864820000?h=7bed84b047&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479' mobile_image='https://open.winmo.com/wp-content/uploads/2024/11/vimeo-preview.jpg' attachment='141' attachment_size='full' format='16-9' width='16' height='9' conditional_play='confirm_all' id='' custom_class='' template_class='' av_uid='av-m3qqbn70' sc_version='1.0']");?>
    </div>
  </div>

  <?php get_template_part('partials/footer', 'company'); ?>

  <div class="popup-wrapper">
    <div id="request_demo">
      <?php get_template_part('partials/sidebar_cta'); ?>
    </div>
    <div id="request_form">
      <?php get_template_part('partials/hubspot_form'); ?>
    </div>
  </div>
<?php
});

// Disable heartbeat - it may be slowing the site down unneccesarily
function wb_stop_heartbeat()
{
  wp_deregister_script('heartbeat');
}
add_action('init', 'wb_stop_heartbeat', 1);

add_filter('wpseo_title', function ($title) {
  if (is_page_template('page-api.php')) {
    $pid = get_query_var('rid') ?: get_query_var('pid');
    $state = get_query_var('state');

    // Company
    if (isset($pid) && is_page(20)) {
      // Get company name
      $companies = get_option('winmo_companies');
      if(is_array($companies)) {
        $company = array_filter($companies, function ($v) use ($pid) {
          return $v['permalink'] == $pid;
        }, ARRAY_FILTER_USE_BOTH);
        $keys =  array_keys($company);

        // Found a company
        if (sizeof($keys)) {
          $title = $company[$keys[0]]['name'] . " Advertising Profile - Winmo";
        }
      }
    }

    // Industry
    elseif (isset($pid) && is_page(74)) {
      // Get Industry name
      $industry = get_query_var('rid');
      $title = "Top Companies in the " . ucwords($industry) . " Industry in 2024";
    }

    // Agencies by State
    elseif (isset($state) && is_page(82)) {
      $title = "Top Ad Agencies in " . ucwords($state) . " 2024 - Winmo";
    }

    // Agency
    elseif (isset($pid) && is_page(71)) {
      $agencies = get_option('winmo_agencies');
      $agency = array_filter($agencies, function ($v) use ($pid) {
        return $v['permalink'] == $pid;
      }, ARRAY_FILTER_USE_BOTH);
      $keys =  array_keys($agency);

      // Found an agency
      if (sizeof($keys)) {
        $title = $agency[$keys[0]]['name'] . " Agency Profile - Winmo";
      }
    }
    // Decision Makers
    elseif (isset($pid) && (!empty($pid)) && is_page(56)) {

      $contact = get_winmo_contact($pid);
      if (sizeof($contact)) {
        $contact_data = json_decode($contact[0]->data);
        $contact = $contact[0]->api_id;
        $type = strtolower($contact_data->type);
        $company = $contact_data->entity_id;
        $company_data = get_company($company);
        if (isset($contact_data)) {
          $company_data = json_decode($company_data); 
          $title = $contact_data->fname . " " . $contact_data->lname . ", " . $contact_data->title . " at " . $company_data->name . " - Winmo";
        }
      }
    }
  }
  return $title;
});


function prefix_filter_description_example($description)
{

  if (is_page_template('page-api.php')) {
    $pid = get_query_var('rid') ?: get_query_var('pid');
    $state = get_query_var('state');

    // Company
    if (isset($pid) && is_page(20)) {
      // Get company name
      $companies = get_option('winmo_companies');
      if(is_array($companies)) {
        $company = array_filter($companies, function ($v) use ($pid) {
          return $v['permalink'] == $pid;
        }, ARRAY_FILTER_USE_BOTH);
        $keys =  array_keys($company);

        // Found a company
        if (sizeof($keys)) {
          $description = "Explore the advertising profile of " . $company[$keys[0]]['name'] . ". Access detailed info on ad agencies, media spend, and the marketing team. ";
        }
      }
    }

    // Industry
    elseif (isset($pid) && is_page(74)) {
      // Get Industry name
      $industry = get_query_var('rid');
      $description = "Discover top companies in the " . ucwords($industry) . " industry for 2024. Access detailed analyses, key decision-makers, and contact info. Request a trial today!";
    }

    // Agencies by State
    elseif (isset($state) && is_page(82)) {
      $description = "Discover top ad agencies in " . ucwords($state) . " for 2024. Access detailed analyses, key decision-makers, and contact info. Request a trial today!";
    }

    // Agency
    elseif (isset($pid) && is_page(71)) {
      $agencies = get_option('winmo_agencies');
      $agency = array_filter($agencies, function ($v) use ($pid) {
        return $v['permalink'] == $pid;
      }, ARRAY_FILTER_USE_BOTH);
      $keys =  array_keys($agency);

      // Found an agency
      if (sizeof($keys)) {
        $description = "Explore the agency profile for " . $agency[$keys[0]]['name'] . ". Access client lists, employee insights, media spend details, and more. Boost your outreach with Winmo.";
      }
    }

    // Decision Makers
    elseif (isset($pid) && is_page(56)) {

      $contact = array();
    //  $contact = get_winmo_contact($pid);
      if (sizeof($contact)) {
        $contact = $contact[0]->api_id;
        $contact_data = ($contact);
        $type = strtolower($contact_data->type);
        $company = $contact_data->entity_id;
        $company_data = get_company($company);
        if (isset($contact_data)) {
          $company_data = json_decode($company_data); 
          $description =  "Connect with " . $contact_data->fname . " " . $contact_data->lname . ", " . $contact_data->title . " at " . $company_data->name . ". Access business email, insights, and personality-driven outreach tips. ";      
        }
      }
    }
  }
  return $description;
}
add_filter('wpseo_metadesc', 'prefix_filter_description_example');



function winmo_defer_css( $html, $handle ) {
  $handles = array( 'avia-grid', 'avia-module-icon', 'avia-module-slideshow', 'avia-widget-css','avia-layout','avia-module-dynamic-field', 'enfold-custom-block-css');
error_log($handle);
  if ( in_array( $handle, $handles ) ) {
    // Find HREF
    $pos = strpos($html, "href") + 6;
    $end = strpos($html, "'", $pos);
    $href = substr($html,$pos, $end-$pos);
    $html = '<link rel="preload" href="'.$href.'" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">';
    $html .= '<noscript><link rel="stylesheet" href="'.$href.'"></noscript>';

  }
  return $html;
}
add_filter( 'style_loader_tag', 'winmo_defer_css', 10, 2 );