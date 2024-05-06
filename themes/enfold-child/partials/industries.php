<header id="agency" class="business">
  <div class="container">
    <div id="overview" class="gray_box">
      <h1>Advertising Spend by Industry <?php print date('Y'); ?></h1>
      <div class="row">
        <div class="col">
          <p>Explore each industry to discover which companies are spending the most in advertising in <?php print date('Y'); ?>. For each company, we provide a detailed analysis including which advertising agencies they use, who their ad agency contacts are, who is on their internal marketing team, what their ad spend is and more. Quickly assess who the key decision makers are on ad spend and which agencies to contact. With Winmo, you can quickly get answers to questions like these:</p>
        </div>
        <div class="col">
          <p><strong>Which industry spends the most on advertising?</strong><Br>
            The retail industry by far spends the most on advertising. In fact, in the trailing 12 months, retailers spent over twice as much as any other industry with $73.55 billion on digital advertising. That's over $34 billion more than the second-place spender, consumer packaged goods (CPG).</p>
          <p><strong>Which company spends the most on advertising?</strong><br>
            Comcast spent the most on advertising in the trailing 12 months. </p>
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
            <li><a href="/industries/financial-services">Financial Services & Government</a></li>
            <li><a href="/industries/retail-stores-chains">Retail Stores & Chains</a></li>
            <li><a href="/industries/health-care">Health Care</a></li>
            <li><a href="/industries/entertainment">Entertainment</a></li>
            <li><a href="/industries/automotive">Automotive</li>
            <li>Insurance</li>
            <li><a href="/industries/publishing-printed-media">Publishing & Printed Media</li>
            <li><a href="/industries/travel-hospitality">Travel & Hospitality</li>
            <li><a href="/industries/packaged-goods">Packaged Goods</li>
            <li><a href="/industries/food">Food</li>
          </ol>
        </div>
      </div>
    </section>
  </main>
</div>

<div class="container" id="more">
  <h4>More Industries that are big ad spenders</h4>
  <div class="row">
    <div class="col">
      <?php $industries = get_industry_transient();
      foreach ($industries as $link => $industry) :
        print '<a href="/industries/' . $link . '">' . $industry['name'] . '</a>';
      endforeach;
      ?>
    </div>
  </div>
</div>
<div class="row alternate_color ha-center" id="win-more">
  <div class="col container">
    <h2>Win More with Winmo</h2>
    <div style="padding:56.25% 0 0 0;position:relative;"><iframe src="https://player.vimeo.com/video/864820000?h=7bed84b047&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="Win More with Winmo"></iframe></div>
    <script src="https://player.vimeo.com/api/player.js"></script>
  </div>
</div>

<?php get_template_part('partials/footer', 'company');
