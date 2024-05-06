<header>
  <div class="container">
    <div id="overview" class="gray_box">
      <h1>Advertising Spend by Company <?php print date('Y'); ?></h1>
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
      <?php print do_shortcode("[av_icon_box icon='ue813' font='winmo' title='Top 10 Companies by Ad Spend' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='fa-solid fa-money-bill-trend-up' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' ][/av_icon_box]"); ?>
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
    <h4>All Companies</h4>
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

<div class="row alternate_color ha-center" id="win-more">
  <div class="col container">
    <h2>Win More with Winmo</h2>
    <div style="padding:56.25% 0 0 0;position:relative;"><iframe src="https://player.vimeo.com/video/864820000?h=7bed84b047&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="Win More with Winmo"></iframe></div>
    <script src="https://player.vimeo.com/api/player.js"></script>
  </div>
</div>

<?php get_template_part('partials/footer', 'company');
