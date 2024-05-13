<header>
  <div class="container">
    <div id="overview" class="gray_box">
      <h1>Top Companies by Advertising Spend <?php print date('Y'); ?></h1>
      <div class="row">
        <div class="col">
          <p>Winmo tracks in-house and agency decision-makers who control over $100 billion in ad spend, rolling them up brand by brand as well as under parent companies so that you can see the entire group of buyers responsible for advertising budgets across TV, print, OOH, radio, cinema, digital, podcast, social advertising and both direct and programmatic ad buys.</p>
        </div>
        <div class="col col-6">
          <p><strong>Which companies spend the most money on advertising?</strong><Br>
            Topping the list of ad spenders Winmo tracks is consumer packaged goods (CPG) advertiser Proctor & Gamble, which spent over $3 billion on media in the past twelve months, ranging from print and broadcast to OOH and digital video. This includes advertising spend across its entire portfolio of brands. Other top advertising spenders include ecommerce retailer Amazon, Inc., ExxonMobile, General Motors, and Abbvie. To see which industries topped the list <a href="https://winmo.com" class="modal">click here</a>.
          </p>
          <p><strong>Who is the largest advertiser?</strong><br>
            It makes sense that the largest advertiser Winmo tracks is a consumer packaged goods company with multiple brands. In addition to the corporate advertising done by Procter & Gamble itself, P&G’s ad spend encompasses media budgets for over 90 brands tracked by Winmo including Vicks, Downy, Secret, Bounty, Dawn, Swiffer, Fabrese, Cascade, Charmin and Gillette. These advertisers have different marketing decision-makers and even different ad agencies, which is why Winmo segments them on the brand level in addition to presenting company information as a whole.</p>
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
      <?php print do_shortcode("[av_icon_box icon='ue813' font='winmo' title='Top 10 Companies by Ad Spend' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' ][/av_icon_box]"); ?>
      <div class="row">
        <div class="col">
          <ol>
            <li><a href="/company/5626">The Procter & Gamble Company</a></li>
            <li><a href="/company/326">Amazon.com, Inc.</a></li>
            <li><a href="/company/4820">ExxonMobil Corporation</a></li>
            <li><a href="/company/2888">General Motors Corporation</a></li>
            <li><a href="/company/50129">AbbVie, Inc.</a></li>
            <li><a href="/company/52879">Paramount</a></li>
            <li><a href="/company/10453">T-Mobile USA</a></li>
            <li><a href="/company/6891">Toyota Motor Sales, U.S.A., Inc.</a></li>
            <li><a href="/company/9649">Verizon Communications, Inc.</a></li>
            <li><a href="/company/363">American Honda Motor Co., Inc.</a></li>
          </ol>
        </div>
      </div>
    </section>
  </main>
</div>


<div class="filters row">
  <div class="col container">
    <h3>All Companies</h3>
    <?php $nonce = wp_create_nonce("winmo_filter_nonce"); ?>
    <form id="filter-form" data-action="winmo_company_list" data-nonce="<?php print $nonce; ?>" class="form" action='' method="POST">
      <span>Filter Companies</span>

      <input type="text" name="search" placeholder="Search" class="form-control form-control-sm" title="Search by company name" />

      <span> Alphasort </span>
      <select name="alpha" class="form-control form-control-sm">
        <option value="">- ANY -</option>
        <option value="#">#</option>
        <?php foreach (range('a', 'z') as $v) : ?>
          <option value="<?php print $v; ?>" <?php if ($v == "a") {
                                                print " selected=\"selected\"";
                                              } ?>><?php print strtoupper($v); ?></option>
        <?php endforeach; ?>
      </select>

      <input type="submit" value="Filter" class="btn btn-sm btn-secondary" />
    </form>
  </div>
</div>


<!-- The filtered and paginated content will be dynamically loaded into the #all-products div -->
<div id="all-companies" class="all-content">

</div>

<?php do_action('ava_after_content_templatebuilder_page'); ?>