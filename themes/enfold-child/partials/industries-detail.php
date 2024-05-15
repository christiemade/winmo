<header id="agency" class="business">
  <div class="container">
    <div id="overview" class="gray_box">
      <?php
      // Convert machine name
      $industries = get_industry_transient();
      $industry = $industries[$args]; ?>
      <h1>Top Companies in the <?php print $industry['name']; ?> Industry in <?php print date("Y"); ?></h1>
      <div class="row">
        <div class="col">
          <p>Explore the companies in the <?php print strtolower($industry['name']); ?> industry to discover which ones are responsible for major advertising budgets in <?php print date("Y"); ?>. For each company, we provide a detailed analysis including which advertising agencies they work with and how they spend their advertising budgets. Quickly assess who the key decision makers are and how to contact them.
          </p>
        </div>
        <div class="col">
          <p><strong>How many companies advertise in the <?php print strtolower($industry['name']); ?> Industry?</strong><Br>
            There are <?php print sizeof($industry['agencies']); ?> companies that advertise in the <?php print strtolower($industry['name']); ?> Industry.</p>
        </div>
      </div>
    </div>
  </div>
</header>

<div class="container" id="more">
  <h3><?php print $industry['name']; ?> Industry Companies</h3>

  <div class="row">
    <div class="col columned">
      <?php foreach ($industry['agencies'] as $pid => $name) : ?>
        <a href="/agency/<?php print $pid; ?>"><?php print $name; ?></a>
      <?php endforeach; ?>
    </div>

  </div>
</div>

<?php do_action('ava_after_content_templatebuilder_page'); ?>