<header id="agency" class="business">
  <div class="container">
    <div id="overview" class="gray_box">
      <h1><?php the_title(); ?></h1>
      <div class="row">
        <div class="col">
          <?php the_content(); ?>
        </div>
        <div class="col">
          <p><strong>Which industry spends the most on advertising?</strong><Br>
            The retail industry by far spends the most on advertising. In fact, in 2023, retailers spent over twice as much as any other industry. Retail will spend $73.55 billion on digital advertising in 2023. That’s over $34 billion more than the second-place spender, consumer packaged goods (CPG).</p>
          <p><strong>Which company spends the most on advertising?</strong><br>
            Comcast spent the most on advertising in 2023. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
        </div>
      </div>
    </div>
  </div>
</header>

<div class="container row">
  <aside>
    <?php get_template_part('partials/sidebar_cta', 'categories'); ?>
  </aside>

  <main class="col">
    <section id="top" class="gray_box">
      <?php print do_shortcode("[av_icon_box icon='ue813' font='winmo' title='Top 10 Industries that Spend the Most on Advertising' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='fa-solid fa-money-bill-trend-up' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg=''][/av_icon_box]"); ?>
      <div class="row">
        <div class="col">
          <ol>
            <li><a href="/industries/aftermarket-tires-parts">Aftermarket, Tires, Parts</a></li>
            <li>Agricultural Business</li>
            <li>Apparel & Accessories</li>
            <li>Associations & Organizations</li>
            <li>Auto Care</li>
            <li>Auto Manufacturers</li>
            <li>Automotive</li>
            <li>Beer, Wine, Liquor, Spirits</li>
            <li>Beverages</li>
            <li>Broadcasting</li>
          </ol>
        </div>
      </div>
    </section>
  </main>
</div>

<div class="container" id="more">
  <h4>See top Advertisers for each State</h4>
  <div class="row">
    <div class="col">
      <?php $agencies = get_agencies_by_state_transient();
      foreach ($agencies as $state => $agencylist) :
        print '<a href="/agencies/' . $state . '">' . convertState($state) . '</a>';
      endforeach;
      ?>
    </div>
  </div>
</div>
<div class="row alternate_color ha-center" id="win-more">
  <div class="col container">
    <h2>Win More with Winmo</h2>
    <p><a href="#"><img src="<?php print get_stylesheet_directory_uri(); ?>/assets/img/companies/win-more-video.png"></a></p>
  </div>
</div>

<?php get_template_part('partials/footer', 'company');
