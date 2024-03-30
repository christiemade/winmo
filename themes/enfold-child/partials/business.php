
<?php
$company = get_query_var('rid');
$company_data = winmo_company_api($company);

if (is_wp_error($company_data)) {
  // Handle the WP_Error object
  $error_message = $company_data->get_error_message();
  echo "<div id=\"error\"><h2>Error:</h2> <p>" . $error_message . '</p></div>';
} else {

  print_r($company_data);
}



?>