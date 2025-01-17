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

    $dataid = get_query_var('rid');

    if ($dataid) {
      get_template_part('partials/business', '', 'company');
    } else {
      get_template_part('partials/list', 'company');
    }
  elseif (is_page('contacts')) :
    $dataid = get_query_var('pid');

    if ($dataid) {
      get_template_part('partials/contact');
    } else {
      get_template_part('partials/list', 'contact');
    }
  elseif (is_page('agencies')) :
    $dataid = get_query_var('rid');

    if ($dataid) {
      get_template_part('partials/business', 'agency');
    } else {
      get_template_part('partials/list', 'agency');
    }
  elseif (is_page('industries')) :
    $industry = get_query_var('rid');
    if ($industry) {
      get_template_part('partials/industries', 'detail', $industry);
    } else {
      get_template_part('partials/industries', '');
    }
  elseif (is_page('top-agencies')) :
    $state = get_query_var('state');
    $agencies_by_state = get_option('winmo_agencies_by_state');
    if(array_key_exists($state, $agencies_by_state)) {
      get_template_part('partials/top-agencies', '', $state);
    } else {
      //get_template_part('partials/list', 'agency');
      get_template_part('partials/top-agencies', '', '');
    }
  endif;

  ?>

</div><!-- close default .container_wrap element -->

<?php get_footer();
