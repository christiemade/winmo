<header>
  <div class="container">
    <div id="overview" class="gray_box agency-list">
      <div class="row">
        <div class="col">
          <h1>Top Advertising Agencies <?php print date('Y'); ?></h1>
          <p>Winmo tracks roughly 7,000 ad agencies in the US and Canada. These include top agencies in Branding, Creative, Design, Digital, Event and Sponsorship, Full Service, Media Planning and Media Buying, Production, Shopper Marketing and Public Relations, among other disciplines. These agencies include subsidiaries of Holding Companies such as Dentsu, Omnicom Group, Havas, Interpublic Group (IPG), Stagwell, Publicis and WPP, as well as independent agencies and shops belonging to independent agency networks such as AMIN.</p>
        </div>
        <div class="col">
          <p><strong>Which agencies’ accounts are worth the most marketing dollars?</strong><Br>
            According to Winmo, ad agencies with advertising accounts worth the most are independent media agency Horizon Media (client accounts worth $4.83 billion in estimated ad spend); Omnicom Group-owned full service shop BBDO Worldwide (client accounts worth $4.82 billion estimated ad spend), and media planning and Interpublic Group-owned media planning and buying agency Initiative (client accounts worth $4.27 in estimated ad spend).</p>
          <p><strong>Which media agencies have the most ad spend?</strong><br>
            As of <?php print date('Y'); ?>, the media agency with top client media spend is Horizon Media. Horizon Media is one of the nation's largest independent media-buying firms. Founded in 1988 by current President and CEO, Bill Koenigsberg, the New York City-based firm coordinates and negotiates deals across the media spectrum, with television accounting for about half of its billings. According to Winmo, Horizon Media is the media agency of record for financial institutions, retail stores, eCommerce brands, media corporations and social media apps, among others.</p>
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
      <?php print do_shortcode("[av_icon_box icon='ue813' font='winmo' title='Top 10 Agencies by Ad Spend' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' ][/av_icon_box]"); ?>
      <div class="row">
        <div class="col">
          <ol>
            <li><a href="/agency/horizon-media/">Horizon Media Inc.</a></li>
            <li><a href="/agency/bbdo-worldwide/">BBDO Worldwide</a></li>
            <li><a href="/agency/initiative/">Initiative</a></li>
            <li><a href="/agency/essencemediacom">EssenceMediaCom</a></li>
            <li><a href="/agency/3848">Publicis North America</a></li>
            <li><a href="/agency/37662">VML</a></li>
            <li><a href="/agency/1325">Carat</a></li>
            <li><a href="/agency/37782">Initiative</a></li>
            <li><a href="/agency/15221">OMD</a></li>
            <li><a href="/agency/60979">MediaMonks</a></li>
          </ol>
        </div>
      </div>
    </section>
  </main>
</div>




<div class="filters row">
  <div class="col container">
    <h3>All Agencies</h3>
    <?php $nonce = wp_create_nonce("winmo_filter_nonce"); ?>
    <form id="filter-form" data-action="winmo_agency_list" data-nonce="<?php print $nonce; ?>" class="form" action='' method="POST">
      <span>Filter Agencies</span>

      <input type="text" name="search" placeholder="Search" class="form-control form-control-sm" title="Search by Agency Name" />

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
<div id="all-agencies" class="all-content">

</div>

<?php do_action('ava_after_content_templatebuilder_page'); ?>