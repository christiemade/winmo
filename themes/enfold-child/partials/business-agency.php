<?php

// Load JS needed for sticky nav
wp_enqueue_script('gsap');
wp_enqueue_script('scrollTrigger');
wp_enqueue_script('sticky-nav');

// Grab data for page from query vars and the API
$agency = get_query_var('rid');
$agency_data = set_company_transient($agency, 'agency');

// Error check
if (is_wp_error($agency_data)) {
  $error_message = $agency_data->get_error_message();
  echo "<div id=\"error\"><h2>Error:</h2> <p>" . $error_message . '</p></div>';
} else {

  // Create single variable for agency address display
  $address = $agency_data['location']['address1']
    . '<br>' . $agency_data['location']['address2']
    . ($agency_data['location']['address2'] ? "<Br>" : "") .
    $agency_data['location']['city'] . ", " . $agency_data['location']['state'] . " " . $agency_data['location']['zip_code'] . "<br>" .
    $agency_data['location']['country'];
?>
  <header id="agency" class="business">
    <div class="container">
      <div id="overview" class="gray_box">
        <h1><?php print $agency_data['name']; ?> Agency Profile</h1>
        <div class="row">
          <div class="col">
            <h5>Company Overview:</h5>
            <p><?php print $agency_data['description']; ?></p>
          </div>
          <div class="col contact">
            <div class="phone">
              <?php print do_shortcode("[av_icon_box icon='ue809' font='winmo' title='Main Telephone' position='left_content' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='fa-solid fa-phone' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg='']" .
                $agency_data['phone'] . "[/av_icon_box]"); ?>
            </div>
            <div class="address">
              <?php print do_shortcode("[av_icon_box icon='ue808' font='winmo' title='Primary Address' position='left_content' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='fa-solid fa-building' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg='']" . $address . "[/av_icon_box]"); ?>
            </div>
          </div>
          <div class="col">
            <img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/buttons.svg">
          </div>
        </div>
      </div>
    </div>
  </header>

  <div class="container row">
    <aside>
      <nav>
        <ul>
          <li><a href="#current_clients"><?php print $agency_data['name']; ?> Current Clients</a></li>
          <li><a href="#marketing"><?php print $agency_data['name']; ?> Marketing Team</a></li>
          <li><a href="#ad_agency_contacts"><?php print $agency_data['name']; ?> Ad Agency Contacts</a></li>
        </ul>
      </nav>
      <?php get_template_part('partials/sidebar_cta'); ?>
    </aside>

    <main class="col">
      <section id="current_clients">
        <?php print do_shortcode("[av_icon_box icon='ue8d2' font='entypo-fontello' title='" . $agency_data['name'] . " Clients' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='fa-solid fa-user-tie' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg=''][/av_icon_box]"); ?>
        <div class="row">
          <div class="col">
            <p>Explore a detailed list of current and past client that work with <?php print $agency_data['name']; ?>. Sort clients by location, industry, and agency assignment including creative, PR, media planning, media buying and more. With Winmo’s detailed database, you can quickly see which clients list <?php print $agency_data['name']; ?> as the Agency of Record as well as the annual media spend.</p>
          </div>
          <div class="col">
            <p><strong>Who are the clients of <?php print $agency_data['name']; ?>?</strong><br>
              Answer.</p>

            <p><strong>Question</strong><br>
              Answer.</p>
          </div>
        </div>
      </section>
    </main>
  </div><!--end container-->

  <div class="row alternate_color ha-center" id="win-more">
    <div class="col container">
      <h2>Win More with Winmo</h2>
      <p>If you are looking to tap into <?php print $company_data['name']; ?> marketing spend, Winmo paves a clear path to engaging the right contacts at the right time. Winmo connects ad spend, marketing activity and peak buying periods to marketing team and ad agency budget-holders, even providing you with AI-powered email templates based on <?php print $company_data['name']; ?> decision-makers' personality types. Winmo's award winning platform tracks this intel for those who control $100 billion in marketing spend each year, making it the top choice among sellers of agency services, advertising, marketing technology, or corporate sponsorships.</p>
      <p><a href="#"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/win-more-video.jpg"></a></p>
    </div>
  </div>

<?php get_template_part('partials/footer', 'company');
}



?>