<?php

// Load JS needed for sticky nav
wp_enqueue_script('gsap');
wp_enqueue_script('scrollTrigger');
wp_enqueue_script('sticky-nav');

// Grab data for page from query vars and the API
$company = get_query_var('rid');
$weird_company_id = get_query_var('wid');

// Reverse look up company id
$companies = get_option('winmo_companies');
if (isset($weird_company_id) && $weird_company_id > 0) {
  // Get the company permalink to pull up the data
  $weird_company_id = (int)$weird_company_id - (int)1423;
  if (array_key_exists($weird_company_id, $companies)) {
    $company = $companies[$weird_company_id]['permalink'];
    $permalink = get_bloginfo('wpurl') . "/company/" . $company . "/";
    header("Location: " . $permalink, true, 301);
    exit;
  } else {
    $companies = array();  // Trying to get an old URL working but it doesnt exist
  }
}

$company = array_filter($companies, function ($v) use ($company) {
  return $v['permalink'] == $company;
}, ARRAY_FILTER_USE_BOTH);
$keys =  array_keys($company);


// Error check
if (!sizeof($keys)) {
  echo "<header id=\"page404\" class=\"\"><div class=\"container\"></div></header><div id=\"error\"><h2>Error:</h2> <p>This company does not exist.</p></div>";
  exit;
}

$company = $keys[0];
$company_data = set_company_information($company, "", "company");

// Error check
if (is_wp_error($company_data)) {
  $error_message = $company_data->get_error_message();
  echo "<div id=\"error\"><h2>Error:</h2> <p>" . $error_message . '</p></div>';
} elseif (empty($company_data)) {
  error_log("Missing data for " . $company);
  $error_message = "The data for this company could not be located.";
  echo "<header id=\"page404\" class=\"\"><div class=\"container\"></div></header><div id=\"error\"><h2>Error:</h2> <p>" . $error_message . "</p></div>";
} elseif (!is_object($company_data)) {
  echo '<meta http-equiv="Refresh" content="5">';
} else {

  // Create single variable for company address display
  $address = $company_data->location->address1
    . '<br>' . $company_data->location->address2
    . ($company_data->location->address2 ? "<Br>" : "") .
    $company_data->location->city . ", " . $company_data->location->state . " " . $company_data->location->zip_code . "<br>" .
    $company_data->location->country;
?>
  <header id="company" class="business">
    <div class="container">
      <div id="overview" class="gray_box">
        <h1><?php print $company_data->name; ?> Advertising Profile</h1>
        <h5><?php print $company_data->name; ?> Company Overview:</h5>
        <p><?php print $company_data->notes; ?></p>
        <div class="contact">
          <div class="phone">
            <?php print do_shortcode("[av_icon_box icon='ue809' font='winmo' title='Main Telephone' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg='']<a href='https://winmo.com' class=\"modal\">" .
              $company_data->phone . "</a>[/av_icon_box]"); ?>
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
          <li><a href="#advertising"><?php print $company_data->name; ?> Advertising Agencies</a></li>
          <li><a href="#marketing"><?php print $company_data->name; ?> Marketing Team</a></li>
          <li><a href="#ad_agency_contacts"><?php print $company_data->name; ?> Ad Agency Contacts</a></li>
          <li><a href="#ad_spend"><?php print $company_data->name; ?> Ad Spend <?php print date('Y'); ?></a></li>
          <li><a href="#social_media_marketing"><?php print $company_data->name; ?> Social Media Marketing</a></li>
        </ul>
      </nav>
      <?php get_template_part('partials/sidebar_cta'); ?>
    </aside>
    <?php

    $brands_total = sizeof($company_data->related_brands); ?>
    <main class="col">
      <section id="advertising">
        <?php print do_shortcode("[av_icon_box icon='ue812' font='winmo' title='" . $company_data->name . " Advertising Agencies' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg=''][/av_icon_box]"); ?>
        <div class="row">
          <div class="col">
            <p>Explore a detailed list of current and past ad agencies that work with <?php print $company_data->name; ?>. Sort its marketing agencies by the type of services they offer including creative, PR, media planning, media buying and more. With Winmo’s detailed database of <?php print $company_data->name; ?>'s advertising agencies at your fingertips you will quickly be able to answer questions like these:</p>
          </div>
          <div class="col">
            <p><strong>How many brands does <?php print $company_data->name; ?> have?</strong><br>
              <?php print $company_data->name; ?> has <?php print $brands_total; ?> unique brands.</p>

            <p><strong>How much does <?php print $company_data->name; ?> spend on media?</strong><br>
              <?php if (!empty($company_data->revenues)) :
                print $company_data->name . ' spends $' . $company_data->revenues;
              else : print 'It is unknown how much ' . $company_data->name . ' spends ';
              endif; ?> on media.</p>
          </div>
        </div>

        <div class="row table" id="advertising_table">
          <div class="top desktop"><a href="https://winmo.com" class="modal"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/marketing-table-top-1.svg"></a></div>
          <div class="top">
            <div><a href="https://winmo.com" class="modal"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/clients-top-brand.png"><span>Brand</span></a></div>
            <div><a href="https://winmo.com" class="modal"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/table-label-agency.png"><span>Agency</span></a></div>
            <div><a href="https://winmo.com" class="modal"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/table-label-location.png"><span>Location</span></a></div>
            <div><a href="https://winmo.com" class="modal"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/clients-top-services.png"><span>Service</span></a></div>
          </div>
          <div class="grid">
            <?php
            if ($brands_total > 10) $brands_total = 10;

            // Load up random image arrays
            $agency_images = winmo_image_placeholder_transients('agency-blur-2x');
            //$location_images = winmo_image_placeholder_transients('location-blur-2x');

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

              $brand_details = $company_data->related_brands[$i];

            ?><div class="row">
                <div><?php print $brand_details->name; ?></div>
                <div class="blur"><img src="<?php print $agency_images[rand(0, sizeof($agency_images) - 1)]; ?>"></div>
                <div><?php print $brand_details->location->city . ", " . $brand_details->location->state; ?></div>
                <div class="pills"><?php
                                    // Show AOR 80% of the time
                                    if (rand(1, 5) < 4) : ?><span class="aor">AOR</span><?php endif;
                                                                                      foreach ($services as $service) :
                                                                                        print '<span>' . $service . '</span>';
                                                                                      endforeach; ?></div>
              </div><?php
                  endfor; ?>
          </div>
          <?php if (sizeof($company_data->related_brands) > 10) : ?>
            <div class="bottom">
              <a href="https://winmo.com" class="modal"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/pagination.svg"></a>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <section id="marketing">
        <?php
        $people_total = $company_data->contact_count;

        // Find the CMO
        $cmo = false;
        foreach ($company_data->contacts as $person) :
          if (strpos($person->title, "Chief Marketing Officer") !== false) $cmo = $person->fname . " " . $person->lname;
        endforeach;

        // Section Title
        print do_shortcode("[av_icon_box icon='ue80b' font='entypo-fontello' title='" . $company_data->name . " Marketing Team' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg=''][/av_icon_box]"); ?>
        <div class="row">
          <div class="col">
            <p>Winmo tracks marketing team contacts brand by brand (budget by budget), with an update cycle of sixty days for maximum accuracy. With Winmo, you can get detailed information on the entire <?php print $company_data->name; ?> marketing team. Find basics on each marketer such as name, job title, brand responsibilities, email, and direct phone number, as well as current marketing strategies, areas of media investment, and do’s and don’ts for engaging. Here’s the kinds of questions you’ll be able to quickly answer with our database:</p>
          </div>
          <div class="col">
            <p><strong>Who is the CMO at <?php print $company_data->name; ?>?</strong><br>
              The chief marketing officer at <?php print $company_data->name; ?> is <?php $cmo ? print $cmo : print "N/A"; ?>.</p>

            <p><strong>How big is the <?php print $company_data->name; ?> Marketing Team?</strong><br>
              There are <?php print $people_total; ?> staff members currently involved in marketing for <?php print $company_data->name; ?>.</p>
          </div>
        </div>
      </section>

      <section id="ad_agency_contacts">
        <div class="row table" id="marketing_table">
          <div class="top desktop"><a href="#request_demo" class="modal"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/marketing-table-top-1.svg"></a></div>
          <div class="top">
            <div><a href="#request_demo" class="modal"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/table-label-name.png"><span>Name</span></a></div>
            <div><a href="#request_demo" class="modal"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/table-label-title.png"><span>Title</span></a></div>
            <div><a href="#request_demo" class="modal"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/clients-top-state.png"><span>State</span></a></div>
          </div>
          <div class="grid">
            <?php

            if ($people_total > 5) $people_total = 5;
            for ($i = 0; $i < $people_total; $i++) :
            ?><div class="row modal">
                <div><?php print $company_data->contacts[$i]->fname ?> <?php print substr($company_data->contacts[$i]->lname, 0, 1); ?>.</div>
                <div><?php print $company_data->contacts[$i]->title; ?></div>
                <div><?php print $company_data->contacts[$i]->state;
                      ?></div>
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
                        ' . $company_data->contacts[$i]->phone . '<br>
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
                        <a href="#request_demo" class="modal"><img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/social.svg"></a>
                        </div>
                      </div>
                      <div class="row buttons"><a href="#request_demo" class="modal"><img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/contact-details-footer.svg"></a></div>
                      </div>';
                    endif;
                  endfor; ?>
          </div>
          <?php if (sizeof($company_data->related_brands) > 10) : ?>
            <div class="bottom">
              <a href="#request_demo" class="modal"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/marketing-table-foot.svg"></a>
              <a href="#request_demo" class="modal"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/pagination.svg"></a>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <section id="ad_spend">
        <?php


        // Section Title
        print do_shortcode("[av_icon_box icon='ue8c5' font='entypo-fontello' title='" . $company_data->name . " Ad Spend " . date('Y') . "' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg=''][/av_icon_box]"); ?>
        <div class="row">
          <div class="col col-7">
            <p>This section digs into the specifics of the advertising spend at <?php print $company_data->name; ?> and activity across channels such as Out of Home, Radio, Broadcast, Print, Digital Display, CTV, Digital Video and Social Media, highlighting their peak buying times, media mix, and a month over month comparison.</p>
          </div>
        </div>
        <div class="row va-center">
          <div class="col">
            <h3><?php print $company_data->name; ?> Advertising Spend</h3>
            <p>Winmo provides comprehensive data detailing annual advertising spend for <?php print $company_data->name; ?>, showcasing the total spend broken down by month, fiscal quarter and monthly percentage change to illustrate the company's evolving marketing advertising strategies.</p>
          </div>
          <div class="col col-5-5"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/advertising-spend.png"></div>
        </div>
        <div class="row va-center">
          <div class="col">
            <h3><?php print $company_data->name; ?> Media Mix</h3>
            <p>Explore a detailed breakdown of the last 12 months media spending for <?php print $company_data->name; ?> across various advertising channels including digital, broadcast, print, radio and more. Those channels can then be broken down further and viewed monthly or quarterly.</p>
          </div>
          <div class="col col-5-5"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/media-mix.png"></div>
        </div>
      </section>
      <section id="social_media_marketing">
        <div class="row va-center">
          <div class="col">
            <h3><?php print $company_data->name; ?> Social Media Marketing Ad Spend</h3>
            <p>See whether <?php print $company_data->name; ?> is spending on social media platforms like X, TikTok, Facebook, Instagram and Pinterest, as well as how active it is on YouTube desktop, iOS or Android.</p>
          </div>
          <div class="col col-5-5"><a href="#request_demo" class="modal"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/social-media-marketing-ad-spend.png"></a></div>
        </div>
      </section>
    </main>
  </div><!--end container-->

  <div class="row alternate_color ha-center" id="win-more">
    <div class="col container">
      <h2>Win More with Winmo</h2>
      <p>If you are looking to tap into <?php print $company_data->name; ?> marketing spend, Winmo paves a clear path to engaging the right contacts at the right time. Winmo connects ad spend, marketing activity and peak buying periods to marketing team and ad agency budget-holders, even providing you with AI-powered email templates based on <?php print $company_data->name; ?> decision-makers' personality types. Winmo's award winning platform tracks this intel for those who control $100 billion in marketing spend each year, making it the top choice among sellers of agency services, advertising, marketing technology, or corporate sponsorships.</p>

      <?php
      print do_shortcode("[av_video src='https://player.vimeo.com/video/864820000?h=7bed84b047&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479' mobile_image='https://open.winmo.com/wp-content/uploads/2024/11/vimeo-preview.jpg' attachment='141' attachment_size='full' format='16-9' width='16' height='9' conditional_play='confirm_all' id='' custom_class='' template_class='' av_uid='av-m3qqbn70' sc_version='1.0']");?>
      
      <!--<div style="padding:56.25% 0 0 0;position:relative;"><iframe src="https://player.vimeo.com/video/864820000?h=7bed84b047&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="Win More with Winmo"></iframe></div>
      <script src="https://player.vimeo.com/api/player.js"></script>-->
    </div>
  </div>

  <?php get_template_part('partials/footer', 'company'); ?>

  <div class="popup-wrapper">
    <div id="request_demo">
      <?php get_template_part('partials/sidebar_cta'); ?>
    </div>
    <div id="request_form">
      <?php get_template_part('partials/hubspot_form'); ?>
    </div>
  </div><?php
      }



        ?>