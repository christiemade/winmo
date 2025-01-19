<header id="agency" class="business">
  <div class="container">
    <div id="overview" class="gray_box">
      <?php
      // Convert machine name
      $industries = get_all_industries($args);
      if (!is_array($industries)) {
      ?><h1>'<?php print $args; ?>' Industry not found.</h1><?php
                                                          } else {
                                                            $industry = $industries[0]; 
                                                            $companies = get_companies_by_industry($industry['industry_id']);
                                                           ?>
        <h1>Top Companies in the <?php print $industry['name']; ?> Industry in <?php print date("Y"); ?></h1>
        <div class="row">
          <div class="col">
            <p>Explore the companies in the <?php print strtolower($industry['name']); ?> industry to discover which ones are responsible for major advertising budgets in <?php print date("Y"); ?>. For each company, we provide a detailed analysis including which advertising agencies they work with and how they spend their advertising budgets. Quickly assess who the key decision makers are and how to contact them.
            </p>
          </div>
          <div class="col">
            <p><strong>How many companies advertise in the <?php print strtolower($industry['name']); ?> Industry?</strong><Br>
              Winmo tracks <?php print sizeof($companies); ?> advertisers in the <?php print strtolower($industry['name']); ?> industry.</p>
          </div>
        </div>
      <?php } ?>
    </div>
  </div>
</header>

<?php if (is_array($industries)) { ?>
  <div class="container" id="more">
    <h3><?php print $industry['name']; ?> Industry Companies</h3>

    <div class="row">
      <div class="col columned">
        <?php foreach ($companies as $company) : ?>
          <a href="/company/<?php print $company['permalink']; ?>"><?php print $company['name']; ?></a>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
<?php } ?>
<?php do_action('ava_after_content_templatebuilder_page'); ?>