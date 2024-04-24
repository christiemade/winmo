<?php

// Load JS needed for sticky nav
wp_enqueue_script('gsap');
wp_enqueue_script('scrollTrigger');
wp_enqueue_script('sticky-nav');

// Grab data for page from query vars and the API
$contact = get_query_var('pid');
$contact_data = set_contact_transient($contact);

// Some contacts are part of an agency and some are part of a company
if (isset($contact_data[0]['company'])) :
  $company = $contact_data[0]['company'];
  $type = "company";
else :
  $company = $contact_data[0]['agency'];
  $type = "agency";
endif;

// Error check
if (is_wp_error($contact_data)) {
  $error_message = $contact_data->get_error_message();
  echo "<div id=\"error\"><h2>Error:</h2> <p>" . $error_message . '</p></div>';
} else {
  $full_name = $contact_data[0]['fname'] . " " . $contact_data[0]['lname'];
?>
  <header id="contact">
    <div class="container">
      <div id="overview" class="gray_box">
        <h1><?php print $full_name; ?></h1>
        <div class="row">
          <div class="col">

            <h5><?php print $contact_data[0]['title'] . " - " . $company['name']; ?></h5>
            <p><?php print $full_name; ?> is <?php print $contact_data[0]['title']; ?> for <?php print $company['name']; ?>s. On this page, you’ll find the business email and phone number for <?php print $full_name; ?> as unique insights such as do's and don'ts for engaging, and outreach tips based on both DiSC and Ocean personality profiles.</p>
          </div>
          <div class="col"><strong>Social:</strong><br><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/contacts/socials.svg"></div>
          <div class="col"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/contacts/action-buttons.svg?id=2"></div>
        </div>
      </div>
    </div>
  </header>

  <div class="container row">
    <aside>
      <nav>
        <ul>
          <li><a href="#contact_information">Contact Information</a></li>
          <li><a href="#personality_insights">Personality Insights</a></li>
          <li><a href="#disc_profile">Disc Profile</a></li>
          <li><a href="#OCEAN_profile">Ocean Profile</a></li>
          <li><a href="#company">Company Profile</a></li>
        </ul>
      </nav>
      <?php get_template_part('partials/sidebar_cta'); ?>
    </aside>

    <main class="col">
      <section id="contact_information">
        <div class="gray_box row">
          <div class="col">
            <?php
            if ($phone = $contact_data[0]['phone']) :
              $dash = strrpos($phone, "-");
              $phone = substr($phone, 0, 6) . str_repeat("*", ($dash - 6)) . "-" . str_repeat("*", (strlen($phone) - $dash - 1));
            else :
              $phone = 'N/A';
            endif;
            print do_shortcode("[av_icon_box icon='ue809' font='winmo' title='" . $full_name . " Phone Number' position='left_content' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='fa fa-phone' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='']
" . $phone . "[/av_icon_box]"); ?>
          </div>
          <div class="col">
            <?php
            // Generate a version of the email that only shows first letter TLD 
            $email = $contact_data[0]['email'];  // Grab the email
            $atpos = strpos($email, '@');  // Locate the @ sign
            $dotpos = strrpos($email, '.'); // Locate the very last dot
            $email = substr($email, 0, 1) . str_repeat('*', $atpos - 1) . '@' . str_repeat('*', ($dotpos - $atpos - 1)) . substr($email, $dotpos);

            print do_shortcode("[av_icon_box icon='ue809' font='winmo' title='" . $full_name . " Email Address' position='left_content' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='fa fa-envelope' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='']
" . $email . "[/av_icon_box]"); ?></div>
          <div class="col">
            <?php

            // Create an address string
            $location = $contact_data[0]['location'];
            $address = $location['address1'] ? $location['address1'] . '<br>' : "";
            $address .= $location['address2'] ? $location['address2'] . '<br>' : "";
            $address .= $location['city'] ? $location['city'] . ', ' : "";
            $address .= $location['state'] ? $location['state'] . ' ' : '';
            $address .= $location['zip_code'] ? $location['zip_code'] . '<br>' : '<br>';
            $address .= $location['country'];
            print do_shortcode("[av_icon_box icon='ue809' font='winmo' title='" . $full_name . " Office Address' position='left_content' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='fa fa-building' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='']
" . $address . '<br>' . "[/av_icon_box]"); ?></div>
        </div>
      </section>

      <section id="personality_insights">
        <div class="gray_box row">
          <div class="col">
            <?php print do_shortcode("[av_icon_box icon='ue800' font='winmo' title='" . $full_name . " Personality Insights' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='fa-solid fa-person-burst' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg=''][/av_icon_box]"); ?>
            <p>Get personality-driven outreach tips to strike the right chord with <?php print $full_name; ?>. From suggested email length and tone to AI-generated subject lines, personality-based tips make decision-makers like <?php print $full_name; ?> 233% more likely to reply to your outreach.</p>
            <p style="text-align: center;"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/contacts/personality-insights.svg"></p>
          </div>
        </div>
      </section>

      <section id="disc_profile">
        <div class="gray_box row va-center">
          <div class="col col-4">
            <img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/contacts/disc-profile.svg">
          </div>
          <div class="col">
            <h4><?php print $full_name; ?> DISC Profile</h4>
            <p>DiSC is an assessment tool used to improve communication, sales outreach and negotiations. Winmo subscribers use this assessment to get specific information about [John Lewnard] to help determine the best way to interact and engage.</p>
            <p><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/contacts/generate-recommended-email.svg"></p>
          </div>
        </div>
      </section>

      <section id="OCEAN_profile">
        <div class="gray_box row va-center">
          <div class="col">
            <img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/contacts/OCEAN-profile-graphic.png">
          </div>
          <div class="col">
            <h4><?php print $full_name; ?> OCEAN Profile</h4>
            <p>The OCEAN Profile is a general indicator of five (5) key universal personality dimensions. Winmo subscribers use this assessment to get specific information about <?php print $full_name; ?> to help determine the best way to interact and engage.</p>
          </div>
        </div>
      </section>

      <section id="company">
        <div class="gray_box">
          <h4><?php print $company['name']; ?></h4>
          <?php $company_data = set_company_transient($company['id'], $type); ?>
          <div class="row">
            <div class="col">
              <p>
                <?php if ($type == "agency") :
                  if (isset($company_data['notes'])) :
                    print $company['name']; ?> is an agency with a focus on <?php print $company_data['notes']; ?>. They are
                  <?php else :
                    print $company['name'] . " is ";
                  endif; ?>
                  a <?php print strtolower($company_data['type']); ?> company with <?php print $company_data['employees']; ?> employees located in <?php print $company_data['location']['city']; ?>, <?php print $company_data['location']['state']; ?>.
                  <?php if (!empty($company_data['holding_company'])) : ?> They are part of the holding company <?php print $company_data['holding_company']; ?>.<?php endif;
                                                                                                                                                              else :
                                                                                                                                                                print $company_data['description'];
                                                                                                                                                              endif; ?>
              </p>
            </div>
            <div class="col contact">
              <div class="phone">
                <?php print do_shortcode("[av_icon_box icon='ue809' font='winmo' title='Main Telephone' position='left_content' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='fa fa-phone' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg='']" .
                  $company_data['phone'] . "[/av_icon_box]"); ?>
              </div>
              <div class="address">
                <?php print do_shortcode("[av_icon_box icon='ue808' font='winmo' title='Primary Address' position='left_content' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='fa fa-building' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg='']" . $address . "[/av_icon_box]"); ?>
              </div>
            </div>
            <div class="col">
              <?php if (isset($company_data['fiscal_close'])) : ?>
                <p><strong>Fiscal Close</strong><br>
                  <?php print $company_data['fiscal_close']; ?></p>
              <?php endif; ?>
              <p><strong>#Employees</strong><br>
                <?php print $company_data['employees']; ?></p>
              <?php if (isset($company_data['founded'])) : ?>
                <p><strong>Founded</strong><br>
                  <?php print $company_data['founded']; ?></p>
              <?php endif; ?>
            </div>
            <div class="col buttons">
              <img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/contacts/company-info-buttons.svg">
            </div>
          </div>
        </div>
      </section>

      <?php
      //print_r($contact_data);
      ?>
    </main>
  </div><!--end container-->

  <div class="row alternate_color ha-center" id="win-more">
    <div class="col container">
      <h2>Engage <?php print $full_name; ?> for new business</h2>
      <p>Winmo can provide direct contact info, as well as current strategies, likely synergies and even do's and don'ts for writing an email that <?php print $full_name; ?> is likely to respond to. Winmo's award winning platform tracks this intel for those who control $100 billion in marketing spend each year, making it the top choice among sellers of agency services, advertising, marketing technology, or corporate sponsorships.</p>
      <p><a href="#"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/win-more-video.jpg"></a></p>
    </div>
  </div>

<?php get_template_part('partials/footer', 'company');
}
