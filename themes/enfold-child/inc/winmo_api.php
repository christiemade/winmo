<?php

use GuzzleHttp\Promise\Promise;

function winmo_api($type, $page = 1)
{
  // Generate URL for API
  $url = 'https://api.winmo.com/web_api/seo/' . $type . '/?page=' . $page;
  //error_log($url);

  $args = array(
    'headers' => array(
      'Content-Type' => 'application/json',
      'Authorization' => 'Bearer ' . WINMO_TOKEN
    )
  );

  $request = wp_remote_get($url, $args);
  $error = false;
  $body = 0;

  if (!is_wp_error($request)) {
    if ($request['response']['code'] == "404") {
      $error =  new WP_Error('broke', 'Page not found.');
    }
  } else {
    $body = wp_remote_retrieve_body($request);
    if (!isset($body['data'])) {
      $error = new WP_Error('broke', $request->get_error_message());
    }
  }

  if ($error === false) {
    if (!$body) $body = json_decode(wp_remote_retrieve_body($request), true);
    return $body;
  } else {
    return $error;
  }
}

add_action('wp_ajax_process_api_data', 'process_api_data');
add_action('wp_ajax_nopriv_process_api_data', 'process_api_data');

function process_api_data()
{
  if (isset($_POST['page'])) {
    $page = stripslashes($_POST['page']);
    $type = stripslashes($_POST['type']);
    $grab = stripslashes($_POST['grab']);
    $first_total = 0;
    $total = 0;
    if (isset($_POST['total'])) $total = stripslashes($_POST['total']);
    if (isset($_POST['first_total'])) $first_total = stripslashes($_POST['first_total']);

    $promise = new Promise(function () use ($type, $page, $grab, $total, $first_total, &$promise) {
      //error_log("Received from API: " . $type . " " . $page . " " . $grab . " " . $total . " " . $first_total);

      // Just send meta information
      if (($grab == "meta") && ($type != "company_contacts")) {
        // Include total for both contact APIs
        //error_log("Meta check for type: " . $type);
        if ($type === "contacts") {
          // error_log("First contacts check...");
          $result = winmo_api("agency_contacts", $page);
          $contact_set2 = winmo_api("company_contacts", $page);
          $response = $result['meta'];
          // Add results together
          $response['first_total'] = $result['meta']['total_pages'];
          $response['total_pages'] = $result['meta']['total_pages'] + $contact_set2['meta']['total_pages'];
          //error_log("Total: " . $response['total_pages']);
        } elseif ($type == "company_contacts") {
          //error_log("Second contacts check");
          $contact_set2 = winmo_api("agency_contacts", 1);
          $page = $page - $contact_set2['meta']['total_pages']; // Offset the page number
          $result = winmo_api($type, $page);
          $response = $result['meta'];
          // Add results together
          $response['total_pages'] = $result['meta']['total_pages'] + $contact_set2['meta']['total_pages'];
          $response['page'] = $result['meta']['page'] = $contact_set2['meta']['total_pages']; // Set current page to agency size, so it keeps flowing
          //error_log($response['page'] . " / " . $response['total_pages']);
        } else {
          $result = winmo_api($type, $page);
          $response = $result['meta'];
        }
      } elseif (($grab == "meta") && ($type == "company_contacts")) {

        $response = array();
      } else {
        $function = 'set_' . $type . '_transient';

        // Contacts broken into two API calls - second set here
        if ($page > $first_total) {
          $page = (int)$page - (int)$first_total;
          //error_log("Second set data... here is our new page #: " . $page);
          $type = "company_contacts";
        } else {
          $type = "agency_contacts";
        }

        //error_log("Grab page # " . $page . " for " . $type . " in " . $function);

        $result = winmo_api($type, $page);
        //error_log("Received back a " . substr(json_encode($result), 0, 200));
        $last = false;
        if ($total == $page) $last = true;

        // API Error scenario
        if (isset($result['error'])) {
          $response = $result['error'];
        } else {
          $response = $function($result['data'], $page, $last); // Send to processer
        }
      }
      $promise->resolve(json_encode($response));
    });

    // Calling wait will return the value of the promise.
    echo $promise->wait(); // outputs "foo"


  } else {
    wp_send_json_error('No data received.');
  }
  die();
}
