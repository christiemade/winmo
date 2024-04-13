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

  <div class="container row">
    <aside>
      <nav>
        <ul>
          <li><a href="#advertising"><?php print $company_data['name']; ?> Advertising Agencies</a></li>
        </ul>
      </nav>
      <section id="cta">
        CTA
      </section>
    </aside>

    <main class="col">
      <section id="advertising">
        <?php print do_shortcode("[av_icon_box icon='ue8d2' font='entypo-fontello' title='" . $company_data['name'] . " Advertising Agency' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg=''][/av_icon_box]"); ?>
        <div class="row">
          <div class="col">
            <p>Explore a detailed list of current and past ad agencies that work with <?php print $company_data['name']; ?>. Sort its marketing agencies by the type of services they offer including creative, PR, media planning, media buying and more. With Winmo’s detailed database of <?php print $company_data['name']; ?>'s advertising agencies at your fingertips you will quickly be able to answer questions like these:</p>
          </div>
          <div class="col">
            <p><strong>Does <?php print $company_data['name']; ?> use a marketing agency?</strong><br>
              Yes, they use [number] unique marketing agencies.</p>
            <p><strong>Who does marketing for <?php print $company_data['name']; ?>?</strong><br>
              There are several companies that do marketing for [Nike] including [company 1].</p>
          </div>
        </div>

        <div class="row table" id="advertising_table">
          <div class="top"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/advertising-table-top.svg"></div>
          <div class="grid">
            <?php
            $total = sizeof($company_data['related_brands']);
            if ($total > 10) $total = 10;
            for ($i = 0; $i < 10; $i++) :
            ?><div class="row">
                <div><?php print $company_data['related_brands'][$i]['name']; ?></div>
                <div>Brand ID is <?php print $company_data['related_brands'][$i]['id']; ?>, but this does not exist as a company to pull any more information from.</div>
                <div></div>
                <div></div>
              </div><?php
                  endfor; ?>
          </div>
          <?php if (sizeof($company_data['related_brands']) > 10) : ?>
            <div class="bottom">
              <img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/pagination.svg">
            </div>
          <?php endif; ?>
        </div>
      </section>
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
              print '<li>' . $contact['id'] . ' : ' . $contact['fname'] . " " . $contact['lname'] . ', ' . $contact['title'] . ' (... and lots more )</li>';
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
        <li>profile_url: <?php print $company_data['profile_url']; ?></li>

      </ul>
    </main>
  </div><!--end container-->
<?php
}



?>