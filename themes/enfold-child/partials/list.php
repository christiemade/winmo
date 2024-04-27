<header>
  <div class="container" style="min-height: 300px;"></div>
</header>

<div class="filters row">
  <div class="col container">
    <?php $nonce = wp_create_nonce("winmo_filter_nonce"); ?>
    <form id="filter-form" data-action="winmo_company_list" data-nonce="<?php print $nonce; ?>" class="form" action='' method="POST">
      <span>Filter Companies</span>

      <input type="text" name="search" placeholder="Search" class="form-control form-control-sm" title="Search by product name or SKU" />

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