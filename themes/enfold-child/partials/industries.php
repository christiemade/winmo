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
      <?php print do_shortcode("[av_icon_box icon='ue813' font='winmo' title='Top 10 Industries by Ad Spend' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class=''][/av_icon_box]"); ?>
      <div class="row">
        <div class="col">
          <ol>
            <li><a href="/industries/financial-services">Financial Services & Government</a></li>
            <li><a href="/industries/retail-stores-chains">Retail Stores & Chains</a></li>
            <li><a href="/industries/health-care">Health Care</a></li>
            <li><a href="/industries/entertainment">Entertainment</a></li>
            <li><a href="/industries/automotive">Automotive</a></li>
            <li><a href="/industries/financial-services">Insurance</a></li>
            <li><a href="/industries/publishing-printed-media">Publishing & Printed Media</a></li>
            <li><a href="/industries/travel-hospitality">Travel & Hospitality</a></li>
            <li><a href="/industries/packaged-goods">Packaged Goods</a></li>
            <li><a href="/industries/food">Food</a></li>
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
<?php do_action('ava_after_content_templatebuilder_page'); ?>