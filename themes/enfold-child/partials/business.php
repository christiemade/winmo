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
    <?php

    $brands_total = sizeof($company_data['related_brands']); ?>
    <main class="col">
      <section id="advertising">
        <?php print do_shortcode("[av_icon_box icon='ue8d2' font='entypo-fontello' title='" . $company_data['name'] . " Advertising Agency' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg=''][/av_icon_box]"); ?>
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
            $agency_images = winmo_image_placeholder_transients('agency-blur-2x');
            $location_images = winmo_image_placeholder_transients('location-blur-2x');
            for ($i = 0; $i < $brands_total; $i++) :
            ?><div class="row">
                <div><?php print $company_data['related_brands'][$i]['name']; ?></div>
                <div class="blur"><img src="<?php print $agency_images[rand(0, sizeof($agency_images) - 1)]; ?>"></div>
                <div class="blur"><img src="<?php print $location_images[rand(0, sizeof($location_images) - 1)]; ?>"></div>
                <div class="pills"><span>Pills</span></div>
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
        $people_total = sizeof($company_data['contacts']);
        print do_shortcode("[av_icon_box icon='ue80b' font='entypo-fontello' title='" . $company_data['name'] . " Marketing Team' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg=''][/av_icon_box]"); ?>
        <div class="row">
          <div class="col">
            <p>Winmo tracks marketing team contacts brand by brand (budget by budget), with an update cycle o s for maximum accuracy. With Winmo, you can get detailed information on the entire <?php print $company_data['name']; ?> marketing team. Find basics on each marketer such as name, job title, brand responsibilities, email, and direct phone number, as well as current marketing strategies, areas of media investment, and do’s and don’ts for engaging. Here’s the kinds of questions you’ll be able to quickly answer with our database:</p>
          </div>
          <div class="col">
            <p><strong>Who is the CMO at <?php print $company_data['name']; ?>?</strong><br>
              The chief marketing officer at <?php print $company_data['name']; ?> is [First L.].</p>

            <p><strong>How big is the <?php print $company_data['name']; ?> Marketing Team?</strong><br>
              There are <?php print $people_total; ?> staff members currently involved in marketing for <?php print $company_data['name']; ?>.</p>
          </div>
        </div>

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
                      print '<div class="details">MORE STUFF HERE.</div>';
                    endif;
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
        <li>stocksymbol: <?php print $company_data['stocksymbol']; ?></li>
        <li>naics: <?php print $company_data['naics']; ?></li>
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