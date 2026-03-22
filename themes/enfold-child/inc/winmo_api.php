<?php

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\RejectionException;

function winmo_api($type, $page = 1)
{
  $url = 'https://api.winmo.com/web_api/seo/' . $type . '/?page=' . $page;

  $args = array(
    'headers' => array(
      'Content-Type'  => 'application/json',
      'Authorization' => 'Bearer ' . WINMO_TOKEN,
    ),
    'timeout' => 60,
  );

  $request = wp_remote_get($url, $args);

  // Turn this on only for the known failing case
  $debug_this_request = ($type === 'agency_contacts' && (int) $page === 252);

  if (is_wp_error($request)) {
    if ($debug_this_request) {
      error_log('WINMO DEBUG wp_error type=' . $type . ' page=' . $page . ' msg=' . $request->get_error_message());
      error_log('WINMO DEBUG url=' . $url);
    }

    return array(
      'error' => $request->get_error_message()
    );
  }

  $code = wp_remote_retrieve_response_code($request);
  $raw_body = wp_remote_retrieve_body($request);

  if ($debug_this_request) {
    error_log('WINMO DEBUG type=' . $type . ' page=' . $page);
    error_log('WINMO DEBUG url=' . $url);
    error_log('WINMO DEBUG http_code=' . $code);
    error_log('WINMO DEBUG raw_body=' . substr($raw_body, 0, 8000));
  }

  if ((int) $code === 404) {
    return array(
      'error' => 'Page not found.'
    );
  }

  if ((int) $code < 200 || (int) $code >= 300) {
    return array(
      'error' => 'HTTP ' . $code . ': ' . substr((string) $raw_body, 0, 1000)
    );
  }

  if (!is_string($raw_body) || $raw_body === '') {
    return array(
      'error' => 'There has been a problem with the data received.'
    );
  }

  $decoded = json_decode($raw_body, true);
  $json_error = json_last_error_msg();

  if ($debug_this_request) {
    error_log('WINMO DEBUG json_last_error=' . $json_error);
  }

  if (json_last_error() !== JSON_ERROR_NONE) {
    return array(
      'error' => 'Invalid JSON from API: ' . $json_error . '. Raw body: ' . substr($raw_body, 0, 1000)
    );
  }

  // If the API returns a JSON error payload, preserve it
  if (is_array($decoded) && isset($decoded['error'])) {
    if ($debug_this_request) {
      error_log('WINMO DEBUG decoded_error=' . print_r($decoded['error'], true));
    }

    return array(
      'error' => is_string($decoded['error']) ? $decoded['error'] : print_r($decoded['error'], true)
    );
  }

  return $decoded;
}

add_action('wp_ajax_process_api_data', 'process_api_data');
add_action('wp_ajax_nopriv_process_api_data', 'process_api_data');

function process_api_data() {
  file_put_contents(WP_CONTENT_DIR . '/api-test.log', date('c') . " START\n", FILE_APPEND);

  if (!isset($_POST['page'])) {
    wp_send_json_error('No data received.');
  }

  $page        = isset($_POST['page']) ? (int) wp_unslash($_POST['page']) : 1;
  $type        = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : '';
  $grab        = isset($_POST['grab']) ? sanitize_text_field(wp_unslash($_POST['grab'])) : '';
  $loop        = isset($_POST['loop']) ? (int) wp_unslash($_POST['loop']) : 0;
  $per_page    = isset($_POST['per_page']) ? (int) wp_unslash($_POST['per_page']) : 0;
  $first_total = isset($_POST['first_total']) ? (int) wp_unslash($_POST['first_total']) : 0;
  $total       = isset($_POST['total']) ? (int) wp_unslash($_POST['total']) : 0;

  $req_start = microtime(true);
  error_log("API Call START type={$type} grab={$grab} page={$page} loop={$loop}");

  if ($grab === 'meta') {
    $response = winmo_get_meta_response($type, $page, $first_total, $total);

    error_log("API Call END total request time: " . round(microtime(true) - $req_start, 2) . "s");

    if (isset($response['error'])) {
      wp_send_json_error($response['error']);
    }

    wp_send_json($response);
  }

  if ($grab === 'page') {
    $response = winmo_process_page_response($type, $page, $total, $first_total, $per_page, $loop, $req_start);

    if (is_array($response) && !empty($response['error'])) {
      wp_send_json_error($response['error']);
    } else {
      wp_send_json($response);
    }

  }
  
  wp_send_json_error('Invalid grab type.');
}


function winmo_get_meta_response($type, $page = 1, $first_total = 0, $total = 0) {
  error_log("Meta check for type: " . $type);

  if ($type === 'contacts') {
    $company_meta_result = winmo_api('company_contacts', 1);
    $agency_meta_result  = winmo_api('agency_contacts', 1);

    if (isset($company_meta_result['error'])) {
      return array('error' => $company_meta_result['error']);
    }

    if (isset($agency_meta_result['error'])) {
      return array('error' => $agency_meta_result['error']);
    }

    $company_total     = (int) $company_meta_result['meta']['total_pages'];
    $agency_total      = (int) $agency_meta_result['meta']['total_pages'];
    $company_per_page  = (int) $company_meta_result['meta']['per_page'];
    $agency_per_page   = (int) $agency_meta_result['meta']['per_page'];

    $response = array(
      'page'            => 1,
      'first_total'     => $company_total,
      'second_total'    => $agency_total,
      'first_per_page'  => $company_per_page,
      'second_per_page' => $agency_per_page,
      'per_page'        => $company_per_page,
      'total_pages'     => $company_total + $agency_total,
    );

    error_log("First total: " . $response['first_total']);
    error_log("Second total: " . $response['second_total']);
    error_log("Total Pages: " . $response['total_pages']);

    // Resume support
    $last_contact_page = (int) get_transient('contacts_last_page');
    error_log("last_contact_page: " . $last_contact_page);

    if ($last_contact_page > 1) {
      global $wpdb;

      // Remove temp rows from the page being retried
      $wpdb->delete('winmo_contacts', array(
        'status' => 'temp',
        'page'   => $last_contact_page,
      ));

      $response['page'] = $last_contact_page;

      // If resuming after company contacts finished, switch per_page to agency
      if ($last_contact_page > $company_total) {
        $response['per_page'] = $agency_per_page;
      }
    }

    error_log("Meta response page: " . $response['page']);

    return $response;
  }

  // All non-combined import types
  $result = winmo_api($type, $page);

  if (isset($result['error'])) {
    return array('error' => $result['error']);
  }

  $response = $result['meta'];
  $response['first_total'] = (int) $result['meta']['total_pages'];

  return $response;
}


function winmo_process_page_response($type, $page, $total, $first_total, $per_page, $loop, $req_start = null) {
  error_log("Page is: {$page} out of {$total}");

  $resolved = winmo_resolve_page_request($type, $page, $total, $first_total);

  $api_type        = $resolved['api_type'];
  $api_page        = $resolved['api_page'];
  $processor_type  = $resolved['processor_type'];
  $last            = $resolved['last'];

  $function = 'set_' . $processor_type . '_information';

  if (!function_exists($function)) {
    return array('error' => 'Processor function does not exist: ' . $function);
  }

  $step = microtime(true);
  $result = winmo_api($api_type, $api_page);
  error_log("After winmo_api ({$api_type}, {$api_page}): " . round(microtime(true) - $step, 2) . "s");

  $atts = array(
    'page'        => $api_page,
    'total'       => $total,
    'last'        => $last,
    'type'        => $api_type,
    'first_total' => $first_total,
    'per_page'    => $per_page,
    'loop'        => $loop,
  );

  error_log("Loop value: " . $loop);

  if (isset($result['error'])) {
    error_log('API returned an error');
    return array('error' => $result['error']);
  }

  $step = microtime(true);
  $response = $function($result['data'], $atts);
  error_log("After {$function}: " . round(microtime(true) - $step, 2) . "s");

  // Retry once if processor returned a string unexpectedly
  if (is_string($response)) {
    error_log("Unexpected string response from {$function}: " . $response);
    error_log("Retrying next page after skipping page " . $api_page);

    $api_page++;
    $atts['page'] = $api_page;

    $result = winmo_api($api_type, $api_page);

    if (isset($result['error'])) {
      error_log('API returned an error for type ' . $api_type . ' page ' . $api_page . ': ' . print_r($result['error'], true));
      return array('error' => $result['error']);
    }

    if (!function_exists($function)) {
      error_log('Missing processor: ' . $function);
      return array('error' => 'Processor function does not exist: ' . $function);
    }

    if (isset($result['error'])) {
      return array('error' => $result['error']);
    }

    $response = $function($result['data'], $atts);

    error_log('Processor response for ' . $function . ': ' . print_r($response, true));

    if (is_string($response)) {
      error_log("Retry also returned string response.");
    }
  }

  if ($req_start !== null) {
    error_log("API Call END total request time: " . round(microtime(true) - $req_start, 2) . "s");
  }

  return $response;
}


function winmo_resolve_page_request($type, $page, $total, $first_total) {
  $page = (int) $page;
  $total = (int) $total;
  $first_total = (int) $first_total;

  $resolved = array(
    'api_type'       => $type,
    'api_page'       => $page,
    'processor_type' => $type,
    'last'           => false,
  );

  // Combined contacts import:
  // pages 1..first_total = company_contacts
  // pages (first_total+1)..total = agency_contacts
  if ($type === 'contacts' || $type === 'agency_contacts' || $type === 'company_contacts') {
    $resolved['processor_type'] = 'contacts';

    if ($page > $first_total && $first_total > 0) {
      $resolved['api_type'] = 'agency_contacts';
      $resolved['api_page'] = $page - $first_total;
    } else {
      $resolved['api_type'] = 'company_contacts';
      $resolved['api_page'] = $page;
    }

    if ($page >= $total) {
      $resolved['last'] = true;
    }

    return $resolved;
  }

  // Everything else
  $resolved['api_type'] = $type;
  $resolved['api_page'] = $page;
  $resolved['processor_type'] = $type;

  if ($total > 0 && $page >= $total) {
    $resolved['last'] = true;
  }

  return $resolved;
}