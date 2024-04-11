<?php
$company = get_query_var('rid');
$company_data = winmo_company_api($company);

if (is_wp_error($company_data)) {
  // Handle the WP_Error object
  $error_message = $company_data->get_error_message();
  echo "<div id=\"error\"><h2>Error:</h2> <p>" . $error_message . '</p></div>';
} else {
?>
  <header id="business">

  </header>
  <div class="container">
  </div><!--end container-->

  Here is all we get from the API:<br>
  <ul class="preview">
    <li>id: <?php print $company_data['id']; ?></li>
    <li>name: <?php print $company_data['name']; ?></li>
    <li>company_pressroom: <?php print $company_data['company_pressroom']; ?></li>
    <li>website: <?php print $company_data['website']; ?></li>
    <li>type: <?php print $company_data['type']; ?></li>
    <li>employees: <?php print $company_data['employees']; ?></li>
    <li>fiscal_close: <?php print $company_data['fiscal_close']; ?></li>
    <li>description: <?php print $company_data['description']; ?></li>
    <li>founded: <?php print $company_data['founded']; ?></li>
    <li>company_nickname: <?php print $company_data['company_nickname']; ?></li>
    <li>media_spend: <?php print $company_data['media_spend']; ?></li>
    <li>stocksymbol: <?php print $company_data['stocksymbol']; ?></li>
    <li>notes: <?php print $company_data['notes']; ?></li>
    <li>naics: <?php print $company_data['naics']; ?></li>
    <li>phone: <?php print $company_data['phone']; ?></li>
    <li>location: <?php print $company_data['location']['address1']; ?><br><?php print $company_data['location']['address2']; ?><br>
      <?php print $company_data['location']['city']; ?>, <?php print $company_data['location']['state']; ?> <?php print $company_data['location']['zip_code']; ?><br>
      <?php print $company_data['location']['country']; ?></li>
    <li>contacts:
      <ul class="preview">
        <?php foreach ($company_data['contacts'] as $contact) :
          print '<li>' . $contact['person_id'] . ' : ' . $contact['title'] . '</li>';
        endforeach; ?>
      </ul>
    </li>
    <li>contact_count: <?php print $company_data['contact_count']; ?></li>
    <li>industries:
      <ul class="preview">
        <?php foreach ($company_data['industries'] as $industry => $machinekey) :
          print '<li>' . $industry . ' : <ul>';
          foreach ($machinekey as $key) :
            print '<li>' . $key . '</li>';
          endforeach;
          print '</ul></li>';
        endforeach; ?>
      </ul>
    </li>
    <li>brands:
      <ul class="preview">
        <?php foreach ($company_data['related_brands'] as $brand) :
          print '<li>' . $brand['id'] . ' : ' .  $brand['name'] . '</li>';
        endforeach; ?>
      </ul>
    </li>
    <li>profile_url: <?php print $company_data['profile_url']; ?></li>

  </ul>
<?php
}



?>