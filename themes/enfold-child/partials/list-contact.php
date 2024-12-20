<header>
  <div class="container">
    <div id="overview" class="list_view gray_box">
      <h1>Top Advertising Contacts <?php print date('Y'); ?></h1>
      <div class="row">
        <div class="col">
          <p>Winmo tracks over 200,000 decision-makers who collectively control over $100 billion in advertising spend. This includes 170,000 in-house brand marketing contacts as well as 50,000 related agency contacts who develop marketing strategies for media, creative, promotions and events.
          </p>
        </div>
        <div class="col">
          <p><strong>Who are the top Chief Marketing Officers?</strong><Br>
            Winmo tracks Chief Marketing Officers for 3,400 brands as well as 8,300 VPs of Marketing. The Chief Marketing Officer and VP Marketing titles searched most by Winmo users are at Procter & Gamble, Netflix, Amazon, The Coca-Cola Company, Este Lauder, Geico and Peloton.
          </p>
        </div>
      </div>
    </div>
  </div>
</header>

<div class="filters row">
  <div class="col container">
    <h3>Decision Makers</h3>
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
  <div class="row container">Loading....</div>
</div>

<?php do_action('ava_after_content_templatebuilder_page'); ?>