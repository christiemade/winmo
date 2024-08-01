<header id="agency" class="business">
  <div class="container">
    <div id="overview" class="gray_box">
      <div class="row">
        <div class="col">
          <h1>Top Advertising Industries <?php print date('Y'); ?></h1>
          <p>Winmo tracks companies that fall within 36 different industries, ranging from Apparel and Accessories to Health Care. The majority of these industries are business-to-consumer, but a large number of business-to-business organizations are also tracked.
          </p>
        </div>
        <div class="col col-5-5">
          <p><strong>Which industry spends the most on advertising?</strong><Br>
            The industry that spends the most on advertising in the U.S. is Financial Services and Government, with over $42 billion in media spend over the past twelve months. Top advertisers in Financial Services and Government include H.I.G. Capital Management, Capital One Financial, DC Lottery, Centers for Disease Control & Prevention, and Wells Fargo.
          </p>
          <p><strong>What industry has the most advertising?</strong><br>
            In addition to Financial Services and Government, Retail Stores and Chains spend billions on advertising each year. Advertising from retail stores and chains in Winmo surpassed $18 billion last year, with the largest outlays from Amazon.com, Apple, Walmart Stores, The TJX Companies, and Macy’s.
          </p>
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
  <h3>More Industries that are Big Ad spenders</h3>
  <div class="row">
    <div class="col">
      <?php $industries = get_transient('winmo_industries');

      // Only show industries that have names
      $industries = array_filter($industries, function ($v) {
        return isset($v['name']);
      }, ARRAY_FILTER_USE_BOTH);

      // Sort our filtered items
      uasort($industries, "name_sort");

      foreach ($industries as $link => $industry) :
        print '<a href="/industries/' . $link . '">' . $industry['name'] . '</a>';
      endforeach;
      ?>
    </div>
  </div>
</div>
<?php do_action('ava_after_content_templatebuilder_page'); ?>