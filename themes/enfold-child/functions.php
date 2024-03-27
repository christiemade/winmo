<?php

// Stylesheet caching version
function avia_get_theme_version($which = 'parent')
{
  return '1.0.0.0.0';
}

include("inc/companies.php");

function winmo_avia_overrides()
{
  remove_action('init', 'portfolio_register');
}
add_action('after_setup_theme', 'winmo_avia_overrides');
