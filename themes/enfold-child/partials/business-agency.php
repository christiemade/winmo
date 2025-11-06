<?php

// Load JS needed for sticky nav
wp_enqueue_script('gsap');
wp_enqueue_script('scrollTrigger');
wp_enqueue_script('sticky-nav');

// Grab data for page from query vars and the API
$agency = get_query_var('rid');
$weird_company_id = get_query_var('wid');

error_log('business-agency.php');

// Old URL catcher
if (isset($weird_company_id) && $weird_company_id > 0) {

  // Old API IDs are off by 1423 for some reason
  $weird_company_id = (int)$weird_company_id - (int)1423;
     
  $agency_sql = "SELECT permalink FROM winmo WHERE type = 'agency' AND api_id = '".$weird_company_id."' LIMIT 1";
  error_log($agency_sql);
  $permalink = $wpdb->get_var($agency_sql);
  error_log(json_encode($permalink));
  
  // If the company was found, redirect the user
  if($permalink !== NULL) {
    $permalink = get_bloginfo('wpurl') . "/agency/" . $permalink . "/";
    header("Location: " . $permalink, true, 301);
  }
}

$second = false;
if (preg_match('#(' . substr($agency, 0, -2) . ')-\d#', $agency)) {
  $second = true;
}

global $wpdb;
$agencies = array();
$agency_sql = "SELECT data FROM winmo WHERE type = 'agency' AND permalink = '".$agency."' LIMIT 1";
$agency = $wpdb->get_var($agency_sql);

// Error check
if ($agency === NULL) {
  echo "<header id=\"page404\" class=\"\"><div class=\"container\"></div></header><div id=\"error\"><h2>Error:</h2> <p>This agency does not exist.</p></div>";
  exit;
}

$agency_data = json_decode($agency);

if (is_wp_error($agency_data)) {
  $error_message = $agency_data->get_error_message();
  echo "<header id=\"page404\" class=\"\"><div class=\"container\"></div></header><div id=\"error\"><h2>Error:</h2> <p>" . $error_message . '</p></div>';
} elseif (empty($agency_data)) {
  error_log("Missing data for " . $agency);
  $error_message = "The data for this agency could not be located.";
  echo "<header id=\"page404\" class=\"\"><div class=\"container\"></div></header><div id=\"error\"><h2>Error:</h2> <p>" . $error_message . "</p></div>";
} else {

  // Create single variable for agency address display
  $address = $agency_data->location->address1
    . '<br>' . $agency_data->location->address2
    . ($agency_data->location->address2 ? "<Br>" : "") .
    $agency_data->location->city . ", " . $agency_data->location->state . " " . $agency_data->location->zip_code . "<br>" .
    $agency_data->location->country;
?>
  <header id="agency" class="business">
    <div class="container">
      <div id="overview" class="gray_box">
        <h1><?php print $agency_data->name; ?> Agency Profile</h1>
        <div class="row">
          <div class="col">
            <h5>Company Overview:</h5>
            <p><?php print $agency_data->name; ?> is an agency with a focus on <?php isset($agency_data->notes) ? print $agency_data->notes : print $agency_data->description; ?>. They are a <?php
                                                                                                                                                                                              if (isset($agency_data->type)) {
                                                                                                                                                                                                $type = "";
                                                                                                                                                                                                switch ($agency_data->type) {
                                                                                                                                                                                                  case 'Pu':
                                                                                                                                                                                                    $type = "publicly held";
                                                                                                                                                                                                    break;
                                                                                                                                                                                                  case 'Pr':
                                                                                                                                                                                                    $type = "privately held";
                                                                                                                                                                                                    break;
                                                                                                                                                                                                }
                                                                                                                                                                                                print $type;
                                                                                                                                                                                              } ?> agency.</p>
          </div>
          <div class="col contact">
            <div class="phone">
              <?php print do_shortcode("[av_icon_box icon='ue809' font='winmo' title='Main Telephone' position='left_content' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg='']<a href=\"https:\/\/www.winmo.com\/demo\" target=\"_blank\">" .
                $agency_data->phone . "</a>[/av_icon_box]"); ?>
            </div>
            <div class="address">
              <?php print do_shortcode("[av_icon_box icon='ue808' font='winmo' title='Primary Address' position='left_content' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg='']" . $address . "[/av_icon_box]"); ?>
            </div>
          </div>
          <div class="col social"><a href="https://www.winmo.com/demo" target="_blank"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/social.svg"></a></div>
          <div class="col">
            <a href="https://www.winmo.com/demo" target="_blank"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/buttons.svg"></a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <div class="container row">
    <aside>
      <nav>
        <ul>
          <li><a href="#current_clients"><?php print $agency_data->name; ?> Current Clients</a></li>
          <li><a href="#employees"><?php print $agency_data->name; ?> Employees</a></li>
          <li><a href="#more">...and more</a></li>
        </ul>
      </nav>
      <?php get_template_part('partials/sidebar_cta'); ?>
    </aside>

    <main class="col">
      <section id="current_clients">
        <?php print do_shortcode("[av_icon_box icon='ue8d2' font='entypo-fontello' title='" . str_replace("'", "&lsquo;", $agency_data->name) . " Clients' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg=''][/av_icon_box]"); ?>
        <div class="row">
          <div class="col col-6">
            <p>Explore a detailed list of current and past client that work with <?php print $agency_data->name; ?>. Sort clients by location, industry, and agency assignment including creative, PR, media planning, media buying and more. With Winmo’s detailed database, you can quickly see which clients list <?php print $agency_data->name; ?> as the Agency of Record as well as the annual media spend.</p>
          </div>
          <div class="col">
            <p><strong>Does <?php print $agency_data->name; ?> have a holding company? </strong><br>
              <?php (!empty($agency_data->holding_company)) ? print $agency_data->holding_company . " is the holding company for " . $agency_data->name : print "No, " . $agency_data->name . " does not have a holding company"; ?>.</p>
          </div>
        </div>
        <div class="row table" id="clients_table">
          <div class="top"><a href="https://www.winmo.com/demo" target="_blank"><img width="105" src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/media-cients-current-past-tabs.svg"></a><a href="https://www.winmo.com/demo" target="_blank"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/media-cients-state-industry-filters.svg"></a></div>
          <div class="top">
            <div><a href="https://www.winmo.com/demo" target="_blank"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/clients-top-brand.png"><span>Brand</span></a></div>
            <div><a href="https://www.winmo.com/demo" target="_blank"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/clients-top-state.png"><span>State</span></a></div>
            <div><a href="https://www.winmo.com/demo" target="_blank"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/clients-top-services.png"><span>Service</span></a></div>
            <div><a href="https://www.winmo.com/demo" target="_blank"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/clients-top-media-spend.png"><span>Est. Spend</span></a></div>
          </div>
          <div class="grid">
            <div class="row">
              <div><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/agency-blur-2x/agency-4.png"></div>
              <div>NJ</div>
              <div class="pills"><span class="aor">AOR</span><span>Media Planning</span><span>Media Buying</span></div>
              <div><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/spend-blur-2x/spend-5.png"></div>
            </div>
            <div class="row">
              <div><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/agency-blur-2x/agency-3.png"></div>
              <div>MA</div>
              <div class="pills"><span>Media Planning</span><span>Media Buying</span></div>
              <div><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/spend-blur-2x/spend-4.png"></div>
            </div>
            <div class="row">
              <div><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/agency-blur-2x/agency-6.png"></div>
              <div>PA</div>
              <div class="pills"><span class="aor">AOR</span><span>Media Planning</span><span>Media Buying</span></div>
              <div><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/spend-blur-2x/spend-3.png"></div>
            </div>
            <div class="row">
              <div><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/agency-blur-2x/agency-8.png"></div>
              <div>TX</div>
              <div class="pills"><span class="aor">AOR</span><span>Media Planning</span><span>Media Buying</span></div>
              <div><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/spend-blur-2x/spend-2.png"></div>
            </div>
            <div class="row">
              <div><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/agency-blur-2x/agency-9.png"></div>
              <div>NJ</div>
              <div class="pills"><span class="aor">AOR</span><span>Media Planning</span><span>Media Buying</span></div>
              <div><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/spend-blur-2x/spend-1.png"></div>
            </div>
          </div>

          <div class="bottom">
            <a href="https://www.winmo.com/demo" target="_blank"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/pagination.svg"></a>
          </div>

        </div>
      </section>

      <section id="employees"><?php $title =  $agency_data->name;
                              print do_shortcode("[av_icon_box icon='ue800' font='winmo' title='" . str_replace("'", "&lsquo;", $title) . " Employees' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg=''][/av_icon_box]"); ?>
        <div class="row">
          <div class="col col-6">
            <p>Explore a complete list of <?php print $agency_data->name; ?> employees. We not only have the basics on each team member such as name, job title, brand responsibilities, email, and direct phone number, we also have detailed profiles that include unique insights, do’s and don’ts for engaging, plus both DiSC and Ocean personality profiles. </p>
          </div>
          <div class="col">
            <?php $employee_count = $agency_data->employees;
            if (!$employee_count) $employee_count = "an unknown number of"; ?>
            <p><strong>How many employees does <?php print $agency_data->name; ?> have?</strong><br>
              <?php print $agency_data->name; ?> has <?php print $employee_count; ?> people on their staff.</p>
          </div>
        </div>
      </section>

      <section id="more">
        <div class="row table" id="marketing_table">
          <div class="top desktop"><a href="https://www.winmo.com/demo" target="_blank"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/employee-table-top.svg"></a></div>
          <div class="top">
            <div class="nosort">&nbsp;</div>
            <div><a href="https://www.winmo.com/demo" target="_blank"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/table-label-name.png"><span>Name</span></a></div>
            <div><a href="https://www.winmo.com/demo" target="_blank"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/table-label-title.png"><span>Title</span></a></div>
            <div><a href="https://www.winmo.com/demo" target="_blank"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/table-label-email.png"><span>Email</span></a></div>
            <div><a href="https://www.winmo.com/demo" target="_blank"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/clients-top-state.png"><span>State</span></a></div>
            <div class="nosort"><a href="https://www.winmo.com/demo" target="_blank"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/table-label-action.png"><span>Action</span></a></div>
          </div>
          <div class="grid">
            <?php
            $people_total = sizeof($agency_data->contacts);
            if ($people_total > 5) $people_total = 5;

            for ($i = 0; $i < $people_total; $i++) :
            ?><div class="row">
                <div><a hhref="https://www.winmo.com/demo" target="_blank"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/checkbox.svg"></a></div>
                <div><a hhref="https://www.winmo.com/demo" target="_blank"><?php print $agency_data->contacts[$i]->fname ?> <?php print substr($agency_data->contacts[$i]->lname, 0, 1); ?></a>.</div>
                <div><a hhref="https://www.winmo.com/demo" target="_blank"><?php print $agency_data->contacts[$i]->title; ?></a></div>
                <div><a hhref="https://www.winmo.com/demo" target="_blank"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/contact-email.png"></a></div>
                <div><a hhref="https://www.winmo.com/demo" target="_blank"><?php print $agency_data->contacts[$i]->state; ?></a></div>
                <div><a hhref="https://www.winmo.com/demo" target="_blank"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/export.svg"></a></div>
              </div><?php
                    if ($i === 0) {
                      print '<div class="details">
                      <div class="row">
                        <div>Main Phone:<br>
                        Email:<br>
                        Address:
                        </div>
                        <div>
                        <span class="phone">' . $agency_data->contacts[$i]->phone . '</span><br>
                        <img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/contact-email.svg"><br>
                        <img src="' . get_stylesheet_directory_uri() . '/assets/img/agencies/blurred-address.svg"><br>
                        </div>
                        <div class="social">
                        Social:<br>
                        <a href="https://www.winmo.com/demo" target="_blank"><img src="' . get_stylesheet_directory_uri() . '/assets/img/companies/social.svg"></a>
                        </div>
                        <div>
                        Related Brands:<br>
                        <img src="' . get_stylesheet_directory_uri() . '/assets/img/agencies/blurred-brands.svg">
                        </div>
                      </div>
                      <div class="row buttons"><a href="https://www.winmo.com/demo" target="_blank"><img src="' . get_stylesheet_directory_uri() . '/assets/img/agencies/employees-details-buttons.svg"></a></div>
                      </div>';
                    }
                  endfor; ?>
          </div>

          <div class="bottom">
            <a href="https://www.winmo.com/demo" target="_blank"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/agencies/employees-footer-buttons.svg"></a>
            <?php if (sizeof($agency_data->contacts) > 10) { ?><a href="https://www.winmo.com/demo" target="_blank"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/pagination.svg"></a><?php } ?>
          </div>

        </div>
      </section>
    </main>
  </div><!--end container-->

  <div class="row alternate_color ha-center" id="win-more">
    <div class="col container">
      <h2>Win More with Winmo</h2>
      <p>If you are looking to tap into marketing spend managed by <?php print $agency_data->name; ?>, Winmo paves a clear path to engaging the right contacts at the right time. Whether you are navigating large holding company agencies, or need to know account responsibilities of boutique shops, Winmo connects agencies, clients, and ad spend in an intuitive platform built for new business - even providing you with AI-powered email templates based on <?php print $agency_data->name; ?> decision-makers' personality types. Winmo's award winning platform tracks this intel for those who control $100 billion in marketing spend each year, making it the top choice among sellers of agency services, advertising, marketing technology, or corporate sponsorships.</p>

      <?php
      print do_shortcode("[av_video src='https://player.vimeo.com/video/864820000?h=7bed84b047&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479' mobile_image='https://open.winmo.com/wp-content/uploads/2024/11/vimeo-preview.jpg' attachment='141' attachment_size='full' format='16-9' width='16' height='9' conditional_play='confirm_all' id='' custom_class='' template_class='' av_uid='av-m3qqbn70' sc_version='1.0']");?>
    </div>
  </div>

  <?php get_template_part('partials/footer', 'company'); ?>
  <?php get_template_part('partials/request_form'); ?>
<?php
} ?>