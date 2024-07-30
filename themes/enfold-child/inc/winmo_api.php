<?php

use GuzzleHttp\Promise\Promise;

function winmo_api($type, $page = 1)
{
  // Generate URL for API
  $url = 'https://api.winmo.com/web_api/seo/' . $type . '/?page=' . $page;
  error_log($url);

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
    error_log('Error: ' . json_encode($error->errors));
    $results = array(
      'error' => $error->errors['broke']
    );
    return $results;
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

      // Just send meta information
      if (($grab == "meta") && ($type != "company_contacts")) {

        // Include total for both contact APIs
        error_log("Meta check for type: " . $type);
        if ($type === "contacts") {
          error_log("First contacts check...");
          $result = winmo_api("company_contacts", $page);
          $contact_set2 = winmo_api("agency_contacts", $page);
          $response = $result['meta'];
          // Add results together
          $response['first_total'] = $result['meta']['total_pages'];
          $response['second_total'] = $contact_set2['meta']['total_pages'];
          $response['total_pages'] = $result['meta']['total_pages'] + $contact_set2['meta']['total_pages'];
          error_log("First total after first meta check: " . $response['first_total']);
          error_log("Total Pages: " . $response['total_pages']);
          // Confirm we're actually rebuilding and not restarting from a failed attempt
          $last_contact_page = get_transient('contacts_last_page');
          error_log("last_contact_page :" . $last_contact_page);
          if ($last_contact_page && ($last_contact_page > 1)) {
            error_log("GoOD.");
            // Removely previous added temp contacts from index for this page (to prevent duplicates)
            global $wpdb;
            $wpdb->delete('winmo_contacts', array('status' => 'temp', 'page' => $last_contact_page));

            // Set current page to the last saved page
            $response['page'] = $last_contact_page;

            // Check where we are between the two contact APIs
            error_log($last_contact_page . " > " . $result['meta']['total_pages']);
            if ($last_contact_page > $result['meta']['total_pages']) {
              // We got through an entire API the last time, so we need to start on the second one now
              $type = "agency_contacts";
              $page = $last_contact_page;
            }
          }
          error_log("The page we want to use is " . $response['page']);

          //error_log("Total: " . $response['total_pages']);
        }

        if ($type == "agency_contacts") {
          error_log("Second contacts check");
          $contact_set2 = winmo_api("company_contacts", 1);
          error_log("A" . json_encode($contact_set2['meta']));

          error_log("B" . $page . " - " . $contact_set2['meta']['total_pages']);

          $agency_page_number = $page - $contact_set2['meta']['total_pages']; // Offset the page number
          error_log("C: New page # for agencies: " . $agency_page_number . " but page page is still " . $page);

          $result = winmo_api($type, $agency_page_number);
          $response = $result['meta'];

          // Add results together
          $response['first_total'] = $contact_set2['meta']['total_pages'];
          error_log("D. First total after first meta check: " . json_encode($response['first_total']));

          $response['total_pages'] = $result['meta']['total_pages'] + $contact_set2['meta']['total_pages'];

          // Original intent of this line is not working
          $response['page'] = $result['meta']['page'] = $page; // Set current page to agency size, so it keeps flowing
          error_log("Need to limit Agency contact loop to new size..." . $response['page'] . " or " . $result['meta']['page'] . " or " . $page);
          $response['second_total'] = $result['meta']['total_pages'];
        } elseif ($type != "contacts") {
          $result = winmo_api($type, $page);
          $result['first_total'] = $result['meta']['total_pages'];
          $response = $result['meta'];
        }
      } elseif (($grab == "meta") && ($type == "agency_contacts")) {

        $response = array();
      } else {
        // Sometimes pages drop
        error_log("Page is: " . $page . " " . $total);

        $type = $type == "company_contacts" ? "contacts" : $type;
        $function = 'set_' . $type . '_transient';
        $last = false;

        // Contacts broken into two API calls - second set here
        if ($page > $first_total) {
          if ($page >= $total) {
            error_log("Ok stop this madneess");
            $last = true;
          }

          $page = (int)$page - (int)$first_total;

          // Make sure this ONLY applies to contact queries
          // We've gotten through the first total, so change which API is used now
          if ($type == "contacts") $type = "agency_contacts";
        } elseif ($type == "contacts") {
          $type = "company_contacts";  // Change API call for (first round) specific type of contacts
        }

        //error_log("Grab page # " . $page . " for " . $type . " in " . $function . " first_page_total is " . $first_total);

        $result = winmo_api($type, $page);

        if ($total <= $page) $last = true;
        $atts = array(
          'page' => $page,
          'total' => $total,
          'last' => $last,
          'type' => $type,
          'first_total' => $first_total
        );

        // API Error scenario
        if (isset($result['error'])) {
          error_log('The result has an error');
          $response = $result['error'];
        } else {
          $page = isset($result['page']) ? $result['page'] : $page;
          $response = $function($result['data'], $atts); // Send to processer
        }

        // Sometimes pages drop - why?
        if (gettype($response) == "string")
          error_log($response);
      }
      $promise->resolve(json_encode($response));
    });

    // Calling wait will return the value of the promise.
    echo $promise->wait();
  } else {
    wp_send_json_error('No data received.');
  }
  die();
}
