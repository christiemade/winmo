<?php

// Load JS needed for sticky nav
wp_enqueue_script('gsap');
wp_enqueue_script('scrollTrigger');
wp_enqueue_script('sticky-nav');

// Grab data for page from query vars and the API
$permalink = get_query_var('pid');
$weird_contact_id = get_query_var('wid');

if (isset($weird_contact_id) && ($weird_contact_id != "")) {
  // Get the company permalink to pull up the data
  $weird_contact_id = (int)$weird_contact_id - 1423;
  $contact = get_winmo_contact('','', $weird_contact_id);
  if (!empty($contact)) {
    $permalink = "/decision_makers/".$contact[0]->permalink;
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: ".$permalink);
    exit();
  }
}

// Reverse look up contact id
if($permalink && $permalink != "") {
  $contact = get_winmo_contact($permalink);
}

if (($contact === NULL) || gettype($contact) == "string") {
  echo "<header id=\"page404\" class=\"\"><div class=\"container\"></div></header><div id=\"error\"><h2>Error:</h2> <p>This decision maker does not exist.</p></div>";
  exit;
}

// Error check
if (!sizeof($contact)) {
  echo "<header id=\"page404\" class=\"\"><div class=\"container\"></div></header><div id=\"error\"><h2>Error:</h2> <p>This decision maker does not exist.</p></div>";
  exit;
}

$contact = $contact[0]->api_id;
$contact_data = get_contact_information($contact);

// Some contacts are part of an agency and some are part of a company
$type = strtolower($contact_data->type);
$company = $contact_data->entity_id;
$company_data = get_company($company);
if($company_data !== null) $company_data = json_decode($company_data);

// Error check
if (is_wp_error($contact_data)) {
  $error_message = $contact_data->get_error_message();
  echo "<div id=\"error\"><h2>Error:</h2> <p>" . $error_message . '</p></div>';
} else {
  $full_name = $contact_data->fname . " " . $contact_data->lname;
?>
  <header id="contact">
    <div class="container">
      <div id="overview" class="gray_box">
        <h1><?php print $full_name; ?></h1>

        <?php if($company_data !== null): ?>
        <div class="row">
          <div class="col">
            <h5><?php print $contact_data->title . " - " . $company_data->name; ?></h5>
            <?php if(is_array($company_data)) error_log("Found an array on contact.php:62 that should be an object." .$full_name); ?>
            <p><?php print $full_name; ?> is <?php print $contact_data->title; ?> for <?php print $company_data->name ?: $company_data['name']; ?>s. On this page, you'll find the business email and phone number for <?php print $full_name; ?> as unique insights such as do's and don'ts for engaging, and outreach tips based on both DiSC and Ocean personality profiles.</p>
          </div>
          <div class="col"><strong>Social:</strong><br><a href="#request_form" class="modal"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/contacts/socials.svg"></a></div>
          <div class="col"><a href="#request_form" class="modal"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/contacts/action-buttons.svg?id=2"></a></div>
        </div>
        <?php endif; ?>
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
            if ($phone = $contact_data->phone) :
              $dash = strrpos($phone, "-");
              $phone = substr($phone, 0, 6) . str_repeat("*", ($dash - 6)) . "-" . str_repeat("*", (strlen($phone) - $dash - 1));
            else :
              $phone = 'N/A';
            endif;
            print do_shortcode("[av_icon_box icon='ue809' font='winmo2' title='" . $full_name . " Phone Number' position='left_content' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='']
" . $phone . "[/av_icon_box]"); ?>
          </div>
          <div class="col">
            <?php
            // Generate a version of the email that only shows first letter TLD 
            $email = $contact_data->email;  // Grab the email
            $atpos = strpos($email, '@');  // Locate the @ sign
            $dotpos = strrpos($email, '.'); // Locate the very last dot
            if ($atpos) {
              $email = substr($email, 0, 1) . str_repeat('*', $atpos - 1) . '@' . str_repeat('*', ($dotpos - $atpos - 1)) . substr($email, $dotpos);
            } else {
              $email = "N/A";
            }

            print do_shortcode("[av_icon_box icon='ue805' font='entypo-fontello' title='" . $full_name . " Email Address' position='left_content' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='']
" . $email . "[/av_icon_box]"); ?></div>
          <div class="col">
            <?php

            // Create an address string
            $address = $contact_data->address1 ? $contact_data->address1 . '<br>' : "";
            $address .= $contact_data->address2 ? $contact_data->address2 . '<br>' : "";
            $address .= $contact_data->city ? $contact_data->city . ', ' : "";
            $address .= $contact_data->state ? $contact_data->state . ' ' : '';
            $address .= $contact_data->zip_code ? $contact_data->zip_code . '<br>' : '<br>';
            $address .= $contact_data->country;
            print do_shortcode("[av_icon_box icon='ue808' font='winmo' title='" . $full_name . " Office Address' position='left_content' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='']
" . $address . '<br>' . "[/av_icon_box]"); ?></div>
        </div>
      </section>

      <section id="personality_insights">
        <div class="gray_box row">
          <div class="col">
            <?php print do_shortcode("[av_icon_box icon='ue800' font='winmo2' title='" . $full_name . " Personality Insights' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg=''][/av_icon_box]"); ?>
            <p>Get personality-driven outreach tips to strike the right chord with <?php print $full_name; ?>. From suggested email length and tone to AI-generated subject lines, personality-based tips make decision-makers like <?php print $full_name; ?> 233% more likely to reply to your outreach.</p>
            <p style="text-align: center;"><a href="#request_form" class="modal"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/contacts/personality-insights.svg"></a></p>
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
            <p><a href="#request_form" class="modal"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/contacts/generate-recommended-email.svg"></a></p>
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

      <?php if($company_data !== null): ?>
      <section id="company">
        <div class="gray_box">
          <h4><?php print $company_data->name; ?></h4>
          <div class="row">
            <div class="col">
              <p>
                <?php if ($type == "agency") :
                  if (isset($company_data->notes)) :
                    print $company_data->name; ?> is an agency with a focus on <?php print $company_data->notes; ?>. They are
                  <?php else :
                    print $company_data->name . " is ";
                  endif; ?>
                  a <?php print strtolower($company_data->type); ?> company with <?php print $company_data->employees; ?> employees located in <?php print $company_data->location->city; ?>, <?php print $company_data->location->state; ?>.
                  <?php if (!empty($company_data->holding_company)) : ?> They are part of the holding company <?php print $company_data->holding_company; ?>.<?php endif;
                                                                                                                                                          else :
                                                                                                                                                            print $company_data->notes;
                                                                                                                                                          endif; ?>
              </p>
            </div>
            <div class="col contact">
              <div class="phone">
                <?php print do_shortcode("[av_icon_box icon='ue809' font='winmo' title='Main Telephone' position='left_content' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='fa fa-phone' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg='']" .
                  $company_data->phone . "[/av_icon_box]"); ?>
              </div>
              <div class="address">
                <?php print do_shortcode("[av_icon_box icon='ue808' font='winmo' title='Primary Address' position='left_content' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='fa fa-building' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg='']" . $address . "[/av_icon_box]"); ?>
              </div>
            </div>
            <div class="col">
              <?php if (isset($company_data->fiscal_close)) : ?>
                <p><strong>Fiscal Close</strong><br>
                  <?php print $company_data->fiscal_close; ?></p>
              <?php endif; ?>
              <p><strong>#Employees</strong><br>
                <?php print $company_data->employees; ?></p>
              <?php if (isset($company_data->founded)) : ?>
                <p><strong>Founded</strong><br>
                  <?php print $company_data->founded; ?></p>
              <?php endif; ?>
            </div>
            <div class="col buttons">
              <a href="#request_form" class="modal"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/contacts/company-info-buttons.svg"></a>
            </div>
          </div>
        </div>
      </section>
      <?php endif; ?>

      <?php
      //print_r($contact_data);
      ?>
    </main>
  </div><!--end container-->

  <div class="row alternate_color ha-center" id="win-more">
    <div class="col container">
      <h2>Engage <?php print $full_name; ?> for new business</h2>
      <p>Winmo can provide direct contact info, as well as current strategies, likely synergies and even do's and don'ts for writing an email that <?php print $full_name; ?> is likely to respond to. Winmo's award winning platform tracks this intel for those who control $100 billion in marketing spend each year, making it the top choice among sellers of agency services, advertising, marketing technology, or corporate sponsorships.</p>
      <?php
      print do_shortcode("[av_video src='https://player.vimeo.com/video/864820000?h=7bed84b047&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479' mobile_image='https://open.winmo.com/wp-content/uploads/2024/11/vimeo-preview.jpg' attachment='141' attachment_size='full' format='16-9' width='16' height='9' conditional_play='confirm_all' id='' custom_class='' template_class='' av_uid='av-m3qqbn70' sc_version='1.0']");?>
      
      <!--<div style="padding:56.25% 0 0 0;position:relative;"><iframe src="https://player.vimeo.com/video/864820000?h=7bed84b047&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="Win More with Winmo"></iframe></div>
      <script src="https://player.vimeo.com/api/player.js"></script>-->
    </div>
  </div>

  <?php get_template_part('partials/footer', 'company');
        get_template_part('partials/request_form');
      }
