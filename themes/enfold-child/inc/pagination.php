<?php
$current_query = isset($_REQUEST) ? 'data-query="' . http_build_query($_REQUEST) . '"' : '';
?>
<div class="row pagination">
  <nav class="col">
    <ul class="pagination">

      <li class="page-item <?php echo $current_page === 1 ? 'disabled' : ''; ?>">
        <a class="page-link" href="#" data-page-number="<?php echo $current_page - 1; ?>" <?php echo $current_query; ?>>
          <span>«</span>
        </a>
      </li>

      <?php

      for ($page_number = 1; $page_number <= $total_pages; $page_number++) { ?>
        <li class="page-item <?php echo isset($_GET['page-number']) && $_GET['page-number'] == $page_number ? 'active' : ''; ?>"><a class="page-link" href="#" data-page-number="<?php echo $page_number; ?>" <?php echo $current_query; ?>><?php echo $page_number; ?></a></li>

      <?php } ?>

      <li class="page-item <?php echo $current_page === (int)$total_pages ? 'disabled' : ''; ?>">
        <a class="page-link" href="#" data-page-number="<?php echo $current_page + 1; ?>" <?php echo $current_query; ?>>
          <span>»</span>
        </a>
      </li>
    </ul>
  </nav>
</div>