<?php
/*
	Template Name: API Display
*/
if (!defined('ABSPATH')) {
  die();
}

global $avia_config, $wp_query;

/*
	 * get_header is a basic wordpress function, used to retrieve the header.php file in your theme directory.
	 */
get_header();

/**
 * @used_by				enfold\config-wpml\config.php				10
 * @since 4.5.1
 */
do_action('ava_page_template_after_header'); ?>

<div class='container_wrap container_wrap_first main_color <?php avia_layout_class('main'); ?>'>

  <?php

  if (is_page('companies')) :
    $companies = get_transient('winmo_companies');
    if (is_array($companies)) :
      $dataid = get_query_var('rid');
      if ($dataid) {
        get_template_part('partials/business', 'company');
      } else {
        get_template_part('partials/list', 'company');
      }
    endif;
  elseif (is_page('contacts')) :
    $contacts = get_transient('winmo_contacts');
    $dataid = get_query_var('pid');
    if ($dataid) {
      get_template_part('partials/contact');
    } else {
      print '<header><div class="container"></div>
    </header>' . "<p>Need way to pull in decision makers.  <a href=\"/decision_makers/580439\">Here is one example (Mr. Melton Littlepage)</a> or <a href=\"/decision_makers/709842/\">Caitlin Sargent</a></p>";
    }
  endif;

  ?>

</div><!-- close default .container_wrap element -->

<?php get_footer();
