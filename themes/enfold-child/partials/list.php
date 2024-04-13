<header style="min-height: 250px;">
  <!-- TEMPORARY - Waiting for design -->
</header>
<?php

$companies = get_transient('winmo_companies');

print '<ul>';
// Show first 20 companies
$keys = array_keys($companies);
for ($i = 0; $i < 20; $i++) :
  print '<li><a href="/company/' . $keys[$i] . '">' . $companies[$keys[$i]] . '</a></li>';
endfor;
print '</ul>';

print '<div id="pager">';
$total = sizeof($companies);
$items_per_page = 20;
$page_count = round($total / $items_per_page);
print "<h2>Pager?</h2>";
print "<p>20 per page would give us " . $page_count . " pages!!</p>";
//for ($i = 1; $i < $page_count; $i++):
//print $i;
//endfor;
print '</div>';
