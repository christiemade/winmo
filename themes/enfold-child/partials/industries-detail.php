<header id="agency" class="business">
  <div class="container">
    <div id="overview" class="gray_box">
      <?php
      // Convert machine name
      $industries = get_industry_transient();
      $industry = $industries[$args]; ?>
      <h1>Top Ad Agencies in <?php print $industry['name']; ?> <?php print date("Y"); ?></h1>
      <div class="row">
        <div class="col">
          <p>Explore the advertising agencies in <?php print strtolower($industry['name']); ?> to discover which agencies are responsible for major advertising budgets in <?php print date("Y"); ?>. For each agency, we provide a detailed analysis including which companies they have as clients, the type of services they offer including creative, PR, media planning, media buying and more. Quickly assess who the key decision makers are and how to contact them. With Winmo, you can quickly get answers to questions like these:
          </p>
        </div>
        <div class="col">
          <p><strong>Who is the top advertising agency in the <?php print strtolower($industry['name']); ?> industry?</strong><Br>
            In <?php print date("Y", strtotime("-1 year")); ?>, [company] spent more on advertising than any other company in the <?php print strtolower($industry['name']); ?> industry.</p>
        </div>
      </div>
    </div>
  </div>
</header>

<div class="container" id="more">
  <h3><?php print $industry['name']; ?> Industry Companies</h3>

  <div class="row">
    <div class="col columned">
      <?php foreach ($industry['companies'] as $pid => $name) : ?>
        <a href="/company/<?php print $pid; ?>"><?php print $name; ?></a>
      <?php endforeach; ?>
    </div>

  </div>
</div>

<?php do_action('ava_after_content_templatebuilder_page'); ?>