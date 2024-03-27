<?php
function set_company_transient()
{
  $companies = get_transient('winmo_companies');

  // check to see if companies was successfully retrieved from the cache
  if (false === $companies) {
    // do this if no transient set
    $companies = array();

    if ($file = fopen(get_stylesheet_directory() . "/inc/companies.csv", "r")) {
      while (($data = fgetcsv($file)) !== FALSE) {
        $companies[$data[0]] = $data[1];
      }

      // store the companies array and set it to expire in 1 week
      set_transient('winmo_companies', $companies, 604800);
    } else {
      print $file;
    }
    fclose($file);
  }
}
add_action('after_setup_theme', 'set_company_transient');
