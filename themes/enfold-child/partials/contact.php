<?php

// Load JS needed for sticky nav
wp_enqueue_script('gsap');
wp_enqueue_script('scrollTrigger');
wp_enqueue_script('sticky-nav');

// Grab data for page from query vars and the API
$contact = get_query_var('pid');
$contact_data = set_contact_transient($contact);

// Error check
if (is_wp_error($contact_data)) {
  $error_message = $contact_data->get_error_message();
  echo "<div id=\"error\"><h2>Error:</h2> <p>" . $error_message . '</p></div>';
} else { ?>
  <header id="contact">
    <div class="container">
      <div id="overview" class="gray_box">
        <h1><?php print $contact_data[0]['fname'] . " " . $contact_data[0]['lname']; ?></h1>
        <div class="row">
          <div class="col col-4">

            <h5><?php print $contact_data[0]['title'] . " - " . $contact_data[0]['company']['name']; ?></h5>
            <p><?php print $contact_data[0]['fname'] . " " . $contact_data[0]['lname']; ?> is <?php print $contact_data[0]['title']; ?> for <?php print $contact_data[0]['company']['name']; ?>s. On this page, you’ll find the business email and phone number for <?php print $contact_data[0]['fname'] . " " . $contact_data[0]['lname']; ?> as unique insights such as do's and don'ts for engaging, and outreach tips based on both DiSC and Ocean personality profiles.</p>
          </div>
          <div class="col"><strong>Social:</strong><br><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/contacts/socials.svg"></div>
          <div class="col"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/contacts/action-buttons.svg"></div>
        </div>
      </div>
    </div>
  </header>

  <div class="container row">
    <aside>
      <nav>
        <ul>
          <li><a href="#contact_information">Contact Information</a></li>
          <li><a href="#marketing">Personality Insights</a></li>
          <li><a href="#ad_agency_contacts">Disc Profile</a></li>
          <li><a href="#ad_spend">Ocean Profile</a></li>
          <li><a href="#social_media_marketing">Company Profile</a></li>
        </ul>
      </nav>
      <?php get_template_part('partials/sidebar_cta'); ?>
    </aside>

    <main class="col">
      <section id="contact_information">
        <div class="gray_box row">
          <div class="col">
            <?php
            $phone = $contact_data[0]['phone'] ? $contact_data[0]['phone'] : 'N/A';
            print do_shortcode("[av_icon_box icon='ue809' font='winmo' title='" . $contact_data[0]['fname'] . " " . $contact_data[0]['lname'] . " Phone Number' position='left_content' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='fa fa-phone' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='']
" . $phone . "[/av_icon_box]"); ?>
          </div>
          <div class="col">
            <?php
            // Generate a version of the email that only shows first letter TLD 
            $email = $contact_data[0]['email'];  // Grab the email
            $atpos = strpos($email, '@');  // Locate the @ sign
            $dotpos = strrpos($email, '.'); // Locate the very last dot
            $email = substr($email, 0, 1) . str_repeat('*', $atpos - 1) . '@' . str_repeat('*', ($dotpos - $atpos - 1)) . substr($email, $dotpos);

            print do_shortcode("[av_icon_box icon='ue809' font='winmo' title='" . $contact_data[0]['fname'] . " " . $contact_data[0]['lname'] . " Email Address' position='left_content' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='fa fa-envelope' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='']
" . $email . "[/av_icon_box]"); ?></div>
          <div class="col">c</div>
        </div>
      </section>
      <?php
      print_r($contact_data);
      ?>
    </main>
  </div><?php
      }
