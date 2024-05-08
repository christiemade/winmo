<header>
  <div class="container">
    <div id="overview" class="list_view gray_box">
      <h1>Top Advertising Contacts <?php print date('Y'); ?></h1>
      <div class="row">
        <div class="col">
          <p>Winmo tracks over 200,000 decision-makers who collectively control over $100 billion in advertising spend. This includes 170,000 in-house brand marketing contacts as well as 50,000 related agency contacts who develop marketing strategies for media, creative, promotions and events. </p>
        </div>
        <div class="col">
          <p><strong>Who are the top Chief Marketing Officers?</strong><Br>
            Winmo tracks Chief Marketing Officers for 3,400 brands as well as 8,300 VPs of Marketing. The Chief Marketing Officer and VP Marketing titles searched most by Winmo users are at Procter & Gamble, Netflix, Amazon, The Coca-Cola Company, Este Lauder, Geico and Peloton.</p>
        </div>
      </div>
    </div>
  </div>
</header>

<div class="filters row">
  <div class="col container">
    <?php print do_shortcode("[av_icon_box icon='ue813' font='winmo' title='Decision Makers' position='left' icon_style='' boxed='' font_color='' custom_title='' custom_content='' color='' custom_bg='' custom_font='fa-solid fa-user-tie' custom_border='' custom_title_size='' av-desktop-font-size-title='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' custom_content_size='' av-desktop-font-size='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' heading_tag='h2' heading_class='' link='' linktarget='' title_attr='' linkelement='' id='' custom_class='' template_class='' av_uid='av-luvpcjbw' sc_version='1.0' admin_preview_bg=''][/av_icon_box]"); ?>
    <?php $nonce = wp_create_nonce("winmo_filter_nonce"); ?>
    <form id="filter-form" data-action="winmo_contacts_list" data-nonce="<?php print $nonce; ?>" class="form" action='' method="POST">
      <span>Filter Decision Makers</span>

      <input type="text" name="search" placeholder="Search" class="form-control form-control-sm" title="Search by Agency Name" />

      <span> Alphasort </span>
      <select name="alpha" class="form-control form-control-sm">
        <option value="">- ANY -</option>
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
<div id="all-contacts" class="all-content">

</div>

<div class="row alternate_color ha-center" id="win-more">
  <div class="col container">
    <h2>Win More with Winmo</h2>
    <div style="padding:56.25% 0 0 0;position:relative;"><iframe src="https://player.vimeo.com/video/864820000?h=7bed84b047&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="Win More with Winmo"></iframe></div>
    <script src="https://player.vimeo.com/api/player.js"></script>
  </div>
</div>

<?php get_template_part('partials/footer', 'company');
