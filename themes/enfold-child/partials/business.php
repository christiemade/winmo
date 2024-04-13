<?php
$company = get_query_var('rid');
$company_data = winmo_company_api($company);

if (is_wp_error($company_data)) {
  // Handle the WP_Error object
  $error_message = $company_data->get_error_message();
  echo "<div id=\"error\"><h2>Error:</h2> <p>" . $error_message . '</p></div>';
} else {

  // Create single variable for company address display
  $address = $company_data['location']['address1']
    . '<br>' . $company_data['location']['address2']
    . ($company_data['location']['address2'] ? "<Br>" : "") .
    $company_data['location']['city'] . ", " . $company_data['location']['state'] . " " . $company_data['location']['zip_code'] . "<br>" .
    $company_data['location']['country'];
?>
  <header id="business">
    <div class="container">
      <div id="overview">
        <h1><?php print $company_data['name']; ?> Advertising Profile</h1>
        <h4><?php print $company_data['name']; ?> Company Overview:</h4>
        <p><?php print $company_data['notes']; ?></p>
        <div class="contact">
          <div class="phone">
            <?php print do_shortcode("[av_icon_box icon='ue809' font='winmo' title='Main Telephone' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg='']" .
              $company_data['phone'] . "[/av_icon_box]"); ?>
          </div>
          <div class="address">
            <?php print do_shortcode("[av_icon_box icon='ue808' font='winmo' title='Primary Address' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg='']" . $address . "[/av_icon_box]"); ?>
          </div>
        </div>
      </div>
      <div id="data_boxes"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/data_boxes.png"></div>
    </div>
  </header>

  <div class="container">
    <aside>
      <nav>
        Sticky Menu
      </nav>
      <section id="cta">
        CTA
      </section>
    </aside>

    <main>
      Here is all we get from the API thats not already listed above:<br>
      <ul class="preview">
        <li>id: <?php print $company_data['id']; ?></li>
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
        <li>naics: <?php print $company_data['naics']; ?></li>
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
    </main>
  </div><!--end container-->
<?php
}



?>