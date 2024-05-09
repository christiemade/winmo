<header id="agency" class="business">
  <div class="container">
    <div id="overview" class="gray_box">
      <h1><?php if ($args) : print "Top Ad Agencies in " . convertState($args) . " " . date('Y');
          else :  the_title();
          endif; ?></h1>
      <div class="row">
        <div class="col">
          <?php if ($args) :  ?>
            <p> Explore the advertising agencies in <?php print convertState($args); ?> to discover which agencies are responsible for major advertising budgets in <?php print date('Y'); ?>. For each agency, we provide a detailed analysis including which companies they have as clients, the type of services they offer including creative, PR, media planning, media buying and more. Quickly assess who the key decision makers are and how to contact them. With Winmo, you can quickly get answers to questions like these:</p>
          <?php else : the_content();
          endif;
          ?>

        </div>
        <div class="col col-6">
          <p><strong>Which state is home to the most advertising agencies?</strong><br>
            New York and California have the highest concentration of advertising agencies tracked by Winmo. Not surprisingly, New York has the most advertising agencies, with over 1,100 agencies. Top New York ad agencies include Horizon Media, PHD and BBDO Worldwide. A very close second is in California, home to over 1,000 ad agencies. Top California ad agencies include Omnicom subsidiary RAPP Worldwide, Publicis-owned Publicis.Sapient and Interpublic Group-owned digital agency R/GA. The third-highest concentration of ad agencies is in Illinois, with over 380 ad agencies. Top Illinois ad agencies include independent creative shop Cramer-Krasselt, Interpublic Group-owned media agency Initiative, Omnicom-owned creative shop DDB Chicago, and Stagwell-owned PR agency Allison+Partners.</p>
        </div>
      </div>
    </div>
  </div>
</header>

<div class="container row">
  <?php if (!$args) : ?>
    <aside>
      <?php get_template_part('partials/sidebar_cta', 'categories'); ?>
    </aside>
  <?php endif; ?>

  <main class="col">
    <section id="columned" class="gray_box">
      <?php
      $agencies = get_agencies_by_state_transient();
      if ($args) : ?>
        <?php print do_shortcode("[av_icon_box icon='ue8b1' font='entypo-fontello' title='Agencies in " . convertState($args) . "' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg=''][/av_icon_box]"); ?>
        <div class="row">
          <div class="col columned">
            <?php
            $agencies = $agencies[strtoupper($args)];  // Pull specific state if provideds
            foreach ($agencies as $state => $agencylist) :
              print '<a href="/agency/' . $state . '">' . $agencylist['name'] . '</a>';
            endforeach;
            ?>
          </div>
        </div>
      <?php else : ?>
        <?php print do_shortcode("[av_icon_box icon='ue813' font='winmo' title='Top 10 Holding Companies by Client Spend' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg=''][/av_icon_box]"); ?>
        <div class="row">
          <div class="col">
            <ol>
              <li>Independent/Other</li>
              <li>Omnicom</li>
              <li>Publicis</li>
              <li>Interpublic Group</li>
              <li>WPP</li>
              <li>Dentsu</li>
              <li>Stagwell</li>
              <li>Havas</li>
              <li>Accenture Interactive</li>
              <li>Chiel Worldwide</li>
            </ol>
          </div>
        </div>
      <?php endif; ?>
    </section>
  </main>
</div>

<?php if (!$args) : ?>
  <div class="container" id="more">
    <h4>See top Advertisers for each State</h4>
    <div class="row">
      <div class="col">
        <?php
        foreach ($agencies as $state => $agencylist) :
          print '<a href="/agencies/' . strtolower($state) . '">' . convertState($state) . '</a>';
        endforeach;
        ?>
      </div>
    </div>
  </div>
<?php endif; ?>

<div class="row alternate_color ha-center" id="win-more">
  <div class="col container">
    <h2>Win More with Winmo</h2>
    <div style="padding:56.25% 0 0 0;position:relative;"><iframe src="https://player.vimeo.com/video/864820000?h=7bed84b047&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="Win More with Winmo"></iframe></div>
    <script src="https://player.vimeo.com/api/player.js"></script>
  </div>
</div>

<?php get_template_part('partials/footer', 'company');
