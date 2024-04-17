<?php

// Load JS needed for sticky nav
wp_enqueue_script('gsap');
wp_enqueue_script('scrollTrigger');
wp_enqueue_script('sticky-nav');

// Grab data for page from query vars and the API
$company = get_query_var('rid');
$company_data = set_company_transient($company);

// Error check
if (is_wp_error($company_data)) {
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
        <h5><?php print $company_data['name']; ?> Company Overview:</h5>
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
          <li><a href="#marketing"><?php print $company_data['name']; ?> Marketing Team</a></li>
          <li><a href="#ad_agency_contacts"><?php print $company_data['name']; ?> Ad Agency Contacts</a></li>
          <li><a href="#ad_spend"><?php print $company_data['name']; ?> Ad Spend <?php print date('Y'); ?></a></li>
          <li><a href="#social_media_marketing"><?php print $company_data['name']; ?> Social Media Marketing</a></li>
        </ul>
      </nav>
      <?php get_template_part('partials/sidebar_cta'); ?>
    </aside>
    <?php

    $brands_total = sizeof($company_data['related_brands']); ?>
    <main class="col">
      <section id="advertising">
        <?php print do_shortcode("[av_icon_box icon='ue8d2' font='entypo-fontello' title='" . $company_data['name'] . " Advertising Agencies' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg=''][/av_icon_box]"); ?>
        <div class="row">
          <div class="col">
            <p>Explore a detailed list of current and past ad agencies that work with <?php print $company_data['name']; ?>. Sort its marketing agencies by the type of services they offer including creative, PR, media planning, media buying and more. With Winmo’s detailed database of <?php print $company_data['name']; ?>'s advertising agencies at your fingertips you will quickly be able to answer questions like these:</p>
          </div>
          <div class="col">
            <p><strong>How many brands does <?php print $company_data['name']; ?> have?</strong><br>
              <?php print $company_data['name']; ?> has <?php print $brands_total; ?> unique brands.</p>

            <p><strong>How much does <?php print $company_data['name']; ?> spend on media?</strong><br>
              <?php print $company_data['name']; ?> spends <?php print $company_data['media_spend']; ?> on media.</p>
          </div>
        </div>

        <div class="row table" id="advertising_table">
          <div class="top"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/advertising-table-top.svg"></div>
          <div class="grid">
            <?php
            if ($brands_total > 10) $brands_total = 10;

            // Load up random image arrays
            $agency_images = winmo_image_placeholder_transients('agency-blur-2x');
            $location_images = winmo_image_placeholder_transients('location-blur-2x');

            // Load up random services array
            $optional = array('Branding & Identity', 'Digital Creative', 'Experiential', 'Multicultural', 'Political', 'Production Services', 'Programmatic', 'Research', 'Shopper', 'Strategy');
            $required = array('Creative', 'Digital', 'Media Buying', 'Media Planning', 'Public Relations', 'Social');

            // Loop through up to 10 brands
            for ($i = 0; $i < $brands_total; $i++) :

              // Pick some services to show
              $services = array();
              $optional_keys = array_rand($optional, rand(1, 2));
              if (is_array($optional_keys)) $services = array_intersect_key($optional, $optional_keys);
              shuffle($services); // Randomize the optionals
              $required_keys = array_rand($required, rand(1, 4));
              if (!is_array($required_keys)) $required_keys = array($required_keys);
              $services = array_merge($services, array_intersect_key($required, $required_keys));

              // No more than 5 items
              if (sizeof($services) > 4) $services = array_slice($services, 1, 4);

            ?><div class="row">
                <div><?php print $company_data['related_brands'][$i]['name']; ?></div>
                <div class="blur"><img src="<?php print $agency_images[rand(0, sizeof($agency_images) - 1)]; ?>"></div>
                <div class="blur"><img src="<?php print $location_images[rand(0, sizeof($location_images) - 1)]; ?>"></div>
                <div class="pills"><?php
                                    // Show AOR 80% of the time
                                    if (rand(1, 5) < 4) : ?><span class="aor">AOR</span><?php endif;
                                                                                      foreach ($services as $service) :
                                                                                        print '<span>' . $service . '</span>';
                                                                                      endforeach; ?></div>
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

      <section id="marketing">
        <?php
        $people_total = $company_data['contact_count'];

        // Find the CMO
        $cmo = false;
        foreach ($company_data['contacts'] as $person) :
          if (strpos($person['title'], "Chief Marketing Officer") !== false) $cmo = $person['fname'] . " " . $person['lname'];
        endforeach;

        // Section Title
        print do_shortcode("[av_icon_box icon='ue80b' font='entypo-fontello' title='" . $company_data['name'] . " Marketing Team' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg=''][/av_icon_box]"); ?>
        <div class="row">
          <div class="col">
            <p>Winmo tracks marketing team contacts brand by brand (budget by budget), with an update cycle o s for maximum accuracy. With Winmo, you can get detailed information on the entire <?php print $company_data['name']; ?> marketing team. Find basics on each marketer such as name, job title, brand responsibilities, email, and direct phone number, as well as current marketing strategies, areas of media investment, and do’s and don’ts for engaging. Here’s the kinds of questions you’ll be able to quickly answer with our database:</p>
          </div>
          <div class="col">
            <p><strong>Who is the CMO at <?php print $company_data['name']; ?>?</strong><br>
              The chief marketing officer at <?php print $company_data['name']; ?> is <?php $cmo ? print $cmo : print "N/A"; ?>.</p>

            <p><strong>How big is the <?php print $company_data['name']; ?> Marketing Team?</strong><br>
              There are <?php print $people_total; ?> staff members currently involved in marketing for <?php print $company_data['name']; ?>.</p>
          </div>
        </div>
      </section>

      <section id="ad_agency_contacts">
        <div class="row table" id="marketing_table">
          <div class="top"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/marketing-table-top.svg"></div>
          <div class="grid">
            <?php

            if ($people_total > 5) $people_total = 5;

            for ($i = 0; $i < $people_total; $i++) :
            ?><div class="row">
                <div><?php print $company_data['contacts'][$i]['fname'] ?> <?php print substr($company_data['contacts'][$i]['lname'], 0, 1); ?>.</div>
                <div><?php print $company_data['contacts'][$i]['title']; ?></div>
                <div><?php print $company_data['contacts'][$i]['location']['state']; ?></div>
              </div><?php
                    if ($i === 0) :
                      print '<div class="details">
                      <div class="row">
                        <div>Email:<br>
                        Main Phone:<br>
                        Direct Phone:<br>
                        Assistant Name:<br>
                        Assistant Phone:
                        </div>
                        <div>
                        <img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/contact-email.svg"><br>
                        ' . $company_data['contacts'][$i]['phone'] . '<br>
                        <img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/contact-direct-phone.svg"><br>
                        <img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/contact-assistant-name.svg"><br>
                        <img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/contact-assistant-phone.svg"><br>
                        </div>
                        <div>
                        Sample of Related Brands:<br>
                        <img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/related-brands.svg">
                        </div>
                        <div>
                        Social:<br>
                        <img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/social.svg">
                        </div>
                      </div>
                      <div class="row buttons"><img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/contact-details-footer.svg"></div>
                      </div>';
                    endif;
                  endfor; ?>
          </div>
          <?php if (sizeof($company_data['related_brands']) > 10) : ?>
            <div class="bottom">
              <img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/marketing-table-foot.svg">
              <img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/pagination.svg">
            </div>
          <?php endif; ?>
        </div>
      </section>

      <section id="ad_spend">
        <?php


        // Section Title
        print do_shortcode("[av_icon_box icon='ue80b' font='entypo-fontello' title='" . $company_data['name'] . " Ad Spend " . date('Y') . "' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg=''][/av_icon_box]"); ?>
        <div class="row">
          <div class="col col-7">
            <p>This section digs into the specifics of the advertising spend at <?php print $company_data['name']; ?> and activity across channels such as Out of Home, Radio, Broadcast, Print, Digital Display, CTV, Digital Video and Social Media, highlighting their peak buying times, media mix, and a month over month comparison.</p>
          </div>
        </div>
        <div class="row va-center">
          <div class="col">
            <h3><?php print $company_data['name']; ?> Advertising Spend</h3>
            <p>Winmo provides comprehensive data detailing annual advertising spend for <?php print $company_data['name']; ?>, showcasing the total spend broken down by month, fiscal quarter and monthly percentage change to illustrate the company's evolving marketing advertising strategies.</p>
          </div>
          <div class="col col-6"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/advertising-spend.svg"></div>
        </div>
        <div class="row va-center">
          <div class="col">
            <h3><?php print $company_data['name']; ?> Media Mix</h3>
            <p>Explore a detailed breakdown of the last 12 months media spending for <?php print $company_data['name']; ?> across various advertising channels including digital, broadcast, print, radio and more. Those channels can then be broken down further and viewed monthly or quarterly.</p>
          </div>
          <div class="col col-6"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/media-mix.svg"></div>
        </div>
      </section>
      <section id="social_media_marketing">
        <div class="row va-center">
          <div class="col">
            <h3><?php print $company_data['name']; ?> Social Media Marketing Ad Spend</h3>
            <p>See whether <?php print $company_data['name']; ?> is spending on social media platforms like X, TikTok, Facebook, Instagram and Pinterest, as well as how active it is on YouTube desktop, iOS or Android.</p>
          </div>
          <div class="col col-6"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/social-media-marketing-ad-spend.svg"></div>
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
        <li>stocksymbol: <?php print $company_data['stocksymbol']; ?></li>
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

  <div class="row alternate_color ha-center" id="win-more">
    <div class="col container">
      <h2>Win More with Winmo</h2>
      <p>If you are looking to tap into <?php print $company_data['name']; ?> marketing spend, Winmo paves a clear path to engaging the right contacts at the right time. Winmo connects ad spend, marketing activity and peak buying periods to marketing team and ad agency budget-holders, even providing you with AI-powered email templates based on <?php print $company_data['name']; ?> decision-makers' personality types. Winmo's award winning platform tracks this intel for those who control $100 billion in marketing spend each year, making it the top choice among sellers of agency services, advertising, marketing technology, or corporate sponsorships.</p>
      <p><a href="#"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/win-more-video.jpg"></a></p>
    </div>
  </div>

  <div id="cta-footer" class="cta row va-center">
    <div>
      <a href="#"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/request-trial.png"></a>
    </div>
    <div>
      <h4>Get your unfair advantage with Winmo</h4>
      <p>Request trial to view full profile and more!</p>
      <?php print do_shortcode("[av_button label='Request Full Access' icon_select='no' icon='ue800' link='manually,https://www.winmo.com/profile-1/' link_target='' size='medium' position='left' label_display='' title_attr='' size-text='' av-desktop-font-size-text='' margin='10px, 0px, 0px, 0px' margin_sync='true' padding='' padding_sync='true' av-desktop-margin_sync='true' av-desktop-padding='' av-desktop-padding_sync='true' av-medium-margin='' av-medium-margin_sync='true' av-medium-padding='' av-medium-padding_sync='true' av-small-margin='' av-small-margin_sync='true' av-small-padding='' av-small-padding_sync='true' av-mini-margin='' av-mini-margin_sync='true' av-mini-padding='' av-mini-padding_sync='true' color_options='' color='theme-color' custom_bg='#444444' custom_font='#ffffff' btn_color_bg='theme-color' btn_custom_grad_direction='vertical' btn_custom_grad_1='#000000' btn_custom_grad_2='#ffffff' btn_custom_grad_3='' btn_custom_grad_opacity='0.7' btn_custom_bg='#444444' btn_color_bg_hover='theme-color-highlight' btn_custom_bg_hover='#444444' btn_color_font='theme-color' btn_custom_font='#ffffff' btn_color_font_hover='white' btn_custom_font_hover='#ffffff' border='' border_width='' border_width_sync='true' border_color='' border_radius='' border_radius_sync='true' box_shadow='' box_shadow_style='0px,0px,0px,0px' box_shadow_color='' animation='' animation_duration='' animation_custom_bg_color='' animation_z_index_curtain='100' hover_opacity='' sonar_effect_effect='' sonar_effect_color='' sonar_effect_duration='1' sonar_effect_scale='' sonar_effect_opac='0.5' css_position='' css_position_location=',,,' css_position_z_index='' av-desktop-css_position='' av-desktop-css_position_location=',,,' av-desktop-css_position_z_index='' av-medium-css_position='' av-medium-css_position_location=',,,' av-medium-css_position_z_index='' av-small-css_position='' av-small-css_position_location=',,,' av-small-css_position_z_index='' av-mini-css_position='' av-mini-css_position_location=',,,' av-mini-css_position_z_index='' id='' custom_class='' template_class=''61a2d' sc_version='1.0']"); ?>
    </div>
  </div>
<?php
}



?>