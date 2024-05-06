<header id="agency" class="business">
  <div class="container">
    <div id="overview" class="gray_box">
      <?php
      // Convert machine name
      $industries = get_industry_transient();
      $industry = $industries[$args]; ?>
      <h1>Top <?php print $industry['name']; ?> Advertising Companies <?php print date("Y"); ?></h1>
      <div class="row">
        <div class="col">
          <p>Explore the <?php print strtolower($industry['name']); ?> industry to discover which companies are spending the most in advertising in <?php print date("Y"); ?>. For each company, we provide a detailed analysis including which advertising agencies they use, who their ad agency contacts are, who is on their internal marketing team, what their ad spend is and more. Quickly assess who the key decision makers are on ad spend and which agencies to contact. With Winmo, you can quickly get answers to questions like these:</p>
        </div>
        <div class="col">
          <p><strong>Which <?php print strtolower($industry['name']); ?> company spends the most on advertising?</strong><Br>
            In <?php print date("Y", strtotime("-1 year")); ?>, [company] spent more on advertising than any other company in the <?php print strtolower($industry['name']); ?> industry.</p>
        </div>
      </div>
    </div>
  </div>
</header>

<div class="container row">
  <main class="col">
    <section id="columned">
      <?php print do_shortcode("[av_icon_box icon='ue813' font='winmo' title='" . $industry['name'] . " Industry Companies' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='fa-solid fa-gear' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg=''][/av_icon_box]"); ?>
      <div class="row">
        <div class="col columned">
          <?php foreach ($industry['companies'] as $pid => $name) : ?>
            <a href="/company/<?php print $pid; ?>"><?php print $name; ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  </main>
</div>

<div class="row alternate_color ha-center" id="win-more">
  <div class="col container">
    <h2>Win More with Winmo</h2>
    <p><a href="#"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/win-more-video.png"></a></p>
  </div>
</div>

<?php get_template_part('partials/footer', 'company');
