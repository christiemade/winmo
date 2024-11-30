<?php

add_filter('wpseo_canonical', function( $canonical ){
  error_log($canonical);

  $agency_page = get_option('winmo_agency_page');
  if( (get_the_ID() == $agency_page->ID) && (get_query_var('rid')))  {
    $canonical = get_bloginfo('wpurl').'/agency/'.get_query_var('rid')."/";
  }

  $company_page = get_option('winmo_company_page');
  if( (get_the_ID() == $company_page->ID) && (get_query_var('pid')))  {
    $canonical .= get_query_var('pid')."/";
    error_log($canonical);
  }


  return $canonical;
}, 100);