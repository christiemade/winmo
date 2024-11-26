<?php
// Show request a demo button in header of category pages
add_filter('avf_main_menu_nav', function ($stuff) {
  if (is_page('industries')) {
    $stuff .= '<div class="button"><a href="#request_form" class="modal"><img src="' . get_stylesheet_directory_uri() . '/assets/img/categories/request-a-demo.png"></a></div>';
  }
  return $stuff;
});

function get_agencies_by_state_transient()
{
  $agencies_by_state = get_option('winmo_agencies_by_state');

  // check to see if industries is already saved
  if (false === $agencies_by_state) {

    // do this if no transient set
    $agencies = get_option('winmo_agencies');
    $agencies_by_state = array();

    foreach ($agencies as $aid => $agency) :
      if (!is_null($agency['state'])) {
        if (!isset($agencies_by_state[strtoupper($agency['state'])])) {
          $agencies_by_state[strtoupper($agency['state'])] = array();
        }
        $agencies_by_state[strtoupper($agency['state'])][$aid] = $agency;
      }
    endforeach;
    $agencies_by_state = array_filter($agencies_by_state);  // Remove empty items

    ksort($agencies_by_state);

    // Alpha sort agencies within each state
    foreach ($agencies_by_state as $state => $iagencies) :
      //error_log($state);
      usort($iagencies, "name_sort");
      $agencies_by_state[$state] = $iagencies;
      if (in_array($state, array("AB", "NL"))) unset($agencies_by_state[$state]);
    endforeach;

    // store the industry list as a transient
    update_option('winmo_agencies_by_state', $agencies_by_state);
  }
  return $agencies_by_state;
}

/* -----------------------------------
 * CONVERT STATE NAMES!
 * Goes both ways. e.g.
 * $name = 'Orgegon' -> returns "OR"
 * $name = 'OR' -> returns "Oregon"
 * ----------------------------------- */
function convertState($name)
{
  $states = array(
    array('name' => 'Alabama', 'abbr' => 'AL'),
    array('name' => 'Alaska', 'abbr' => 'AK'),
    array('name' => 'Arizona', 'abbr' => 'AZ'),
    array('name' => 'Arkansas', 'abbr' => 'AR'),
    array('name' => 'California', 'abbr' => 'CA'),
    array('name' => 'Colorado', 'abbr' => 'CO'),
    array('name' => 'Connecticut', 'abbr' => 'CT'),
    array('name' => 'District of Columbia', 'abbr' => 'DC'),
    array('name' => 'Delaware', 'abbr' => 'DE'),
    array('name' => 'Florida', 'abbr' => 'FL'),
    array('name' => 'Georgia', 'abbr' => 'GA'),
    array('name' => 'Hawaii', 'abbr' => 'HI'),
    array('name' => 'Idaho', 'abbr' => 'ID'),
    array('name' => 'Illinois', 'abbr' => 'IL'),
    array('name' => 'Indiana', 'abbr' => 'IN'),
    array('name' => 'Iowa', 'abbr' => 'IA'),
    array('name' => 'Kansas', 'abbr' => 'KS'),
    array('name' => 'Kentucky', 'abbr' => 'KY'),
    array('name' => 'Louisiana', 'abbr' => 'LA'),
    array('name' => 'Maine', 'abbr' => 'ME'),
    array('name' => 'Maryland', 'abbr' => 'MD'),
    array('name' => 'Massachusetts', 'abbr' => 'MA'),
    array('name' => 'Michigan', 'abbr' => 'MI'),
    array('name' => 'Minnesota', 'abbr' => 'MN'),
    array('name' => 'Mississippi', 'abbr' => 'MS'),
    array('name' => 'Missouri', 'abbr' => 'MO'),
    array('name' => 'Montana', 'abbr' => 'MT'),
    array('name' => 'Nebraska', 'abbr' => 'NE'),
    array('name' => 'Nevada', 'abbr' => 'NV'),
    array('name' => 'New Hampshire', 'abbr' => 'NH'),
    array('name' => 'New Jersey', 'abbr' => 'NJ'),
    array('name' => 'New Mexico', 'abbr' => 'NM'),
    array('name' => 'New York', 'abbr' => 'NY'),
    array('name' => 'North Carolina', 'abbr' => 'NC'),
    array('name' => 'North Dakota', 'abbr' => 'ND'),
    array('name' => 'Ohio', 'abbr' => 'OH'),
    array('name' => 'Oklahoma', 'abbr' => 'OK'),
    array('name' => 'Oregon', 'abbr' => 'OR'),
    array('name' => 'Pennsylvania', 'abbr' => 'PA'),
    array('name' => 'Rhode Island', 'abbr' => 'RI'),
    array('name' => 'South Carolina', 'abbr' => 'SC'),
    array('name' => 'South Dakota', 'abbr' => 'SD'),
    array('name' => 'Tennessee', 'abbr' => 'TN'),
    array('name' => 'Texas', 'abbr' => 'TX'),
    array('name' => 'Utah', 'abbr' => 'UT'),
    array('name' => 'Vermont', 'abbr' => 'VT'),
    array('name' => 'Virginia', 'abbr' => 'VA'),
    array('name' => 'Washington', 'abbr' => 'WA'),
    array('name' => 'West Virginia', 'abbr' => 'WV'),
    array('name' => 'Wisconsin', 'abbr' => 'WI'),
    array('name' => 'Wyoming', 'abbr' => 'WY'),
    array('name' => 'Virgin Islands', 'abbr' => 'V.I.'),
    array('name' => 'Guam', 'abbr' => 'GU'),
    array('name' => 'Puerto Rico', 'abbr' => 'PR')
  );

  $return = false;
  $strlen = strlen($name);

  foreach ($states as $state) :
    if ($strlen < 2) {
      return false;
    } else if ($strlen == 2) {
      if (strtolower($state['abbr']) == strtolower($name)) {
        $return = $state['name'];
        break;
      }
    } else {
      if (strtolower($state['name']) == strtolower($name)) {
        $return = strtoupper($state['abbr']);
        break;
      }
    }
  endforeach;

  return $return;
} // end function convertState()

function state_sort($a, $b)
{
  //error_log(json_encode($a));
  //error_log("1. " . key($a));
  //error_log("2. " . gettype($a[key($a)]));
  //error_log("3. " . $a[key($a)]['state']);
  //if (!$a[key($a)]['state']) error_log("4. A Is broken: " . json_encode($a[key($a)]));
  //if (!$b[key($b)]['state']) error_log("4. B Is broken: " . json_encode($b[key($b)]));
  return strcmp(convertState($a[key($a)]['state']), convertState($b[key($b)]['state']));
}
