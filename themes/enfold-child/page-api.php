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

  <div class='container'>

    <?php
    // Create a transient for company information if it doesn't already exist
    print "Content";
    if (is_page('companies')) :
      $companies = get_transient('winmo_companies');
      print_r($companies);
    endif;

    ?>


  </div><!--end container-->

</div><!-- close default .container_wrap element -->

<?php get_footer();
