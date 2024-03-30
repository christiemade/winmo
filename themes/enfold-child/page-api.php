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
    if (is_page('companies')) :
      $companies = get_transient('winmo_companies');
      $dataid = get_query_var('rid');
      if (is_array($companies)) {
       
        if ($dataid) {
           get_template_part( 'partials/business', 'company' );
        } else {
          print '<ul>';
          // Show first 20 companies
          $keys = array_keys($companies);
          for($i=0;$i<20;$i++):
            print '<li><a href="/company/'.$keys[$i].'">' . $companies[$keys[$i]] . '</a></li>';
          endfor;
          print '</ul>';
          
          print '<div id="pager">';
          $total = sizeof($companies);
          $items_per_page = 20;
          $page_count = round($total / $items_per_page);
          print "<h2>Pager?</h2>";
          print "<p>20 per page would give us ". $page_count." pages!!</p>";
          //for ($i = 1; $i < $page_count; $i++):
            //print $i;
          //endfor;
          print '</div>';
        }
      }
    endif;

    ?>


  </div><!--end container-->

</div><!-- close default .container_wrap element -->

<?php get_footer();
