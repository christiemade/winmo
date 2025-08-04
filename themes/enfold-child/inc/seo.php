<?php

add_filter('wpseo_canonical', function( $canonical ){

  $agency_page = get_option('winmo_agency_page');
  if( (get_the_ID() == $agency_page->ID) && (get_query_var('rid')))  {
    $canonical = get_bloginfo('wpurl').'/agency/'.get_query_var('rid')."/";
  }

  $company_page = get_option('winmo_company_page');
  if( (get_the_ID() == $company_page->ID) && (get_query_var('rid')))  {
    $canonical = get_bloginfo('wpurl').'/company/'.get_query_var('rid')."/";
  }

  $contact_page = get_option('winmo_contact_page');
  if( (get_the_ID() == $contact_page->ID) && (get_query_var('pid')))  {
    $canonical = get_bloginfo('wpurl').'/decision_makers/'.get_query_var('pid')."/";
  }

  $industries_page = get_option('winmo_industries_page');
  if( (get_the_ID() == $industries_page->ID) && (get_query_var('rid')))  {
    $canonical = get_bloginfo('wpurl').'/industries/'.get_query_var('rid')."/";
  }

  $agencies_page = get_option('winmo_agencies_page');
  if( (get_the_ID() == $agencies_page->ID) && (get_query_var('state')))  {
    $canonical = get_bloginfo('wpurl').'/agencies/'.get_query_var('state')."/";
  }


  return $canonical;
}, 100);