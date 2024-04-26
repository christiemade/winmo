<header>
  <div class="container"></div>
</header>

<div class="filters row">
  <div class="col container">
    <?php $nonce = wp_create_nonce("winmo_filter_nonce"); ?>
    <form id="filter-form" data-action="winmo_contact_list" data-nonce="<?php print $nonce; ?>" class="form" action='' method="POST">
      <span>Filter People</span>

      <!-- No filter for now <select name="cats" class="form-control form-control-sm">
        <option value="">Filter by Category</option>
        <option value=""></options>
        <option value=""></options>
      </select>-->
      <input type="text" name="search" placeholder="Search" class="form-control form-control-sm" title="Search by product name or SKU" />

      <span> Show </span>
      <select name="per-page" class="form-control form-control-sm">
        <option value="50">50</options>
        <option value="100">100</options>
        <option value="250">250</options>
        <option value="500">500</options>
      </select>

      <input type="submit" value="Filter" class="btn btn-sm btn-secondary" />
    </form>
  </div>
</div>

<!-- The filtered and paginated content will be dynamically loaded into the #all-products div -->
<div id="all-contacts" class="all-content">

</div>