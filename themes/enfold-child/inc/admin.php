<?php
add_action('rest_api_init', function () {
  header("Access-Control-Allow-Origin: *");
});

function winmo_wp_admin_style()
{
  wp_register_style('custom_wp_admin_css', get_stylesheet_directory_uri() . '/admin-style.css', false, '1.0.29');
  wp_enqueue_style('custom_wp_admin_css');
  wp_register_script('api', get_stylesheet_directory_uri() . '/assets/js/api.js', array('jquery'), '1.0.2.05');
  wp_localize_script('api', 'apiAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
  wp_enqueue_script('api');
}
add_action('admin_enqueue_scripts', 'winmo_wp_admin_style');

add_action('admin_menu', 'my_admin_menu');

function my_admin_menu()
{
  add_menu_page('API Refresh', 'API', 'manage_options', 'inc/admin.php', 'api_refresh_admin_page', 'dashicons-tickets', 6);
}

function api_refresh_admin_page()
{
?>
  <div class="wrap">
    <h1>API Refresh</h1>
    <p>Each refresh will take a significant amount of time. Please do not close the browser window until the process is complete!</p>
    <div class="row loaded">
      <div class="col">Companies</div>
      <div class="col"><input type="button" id="company_launch" class="launch" value="Update"></div>
      <div class="col">
        <div id="company_progress" class="progress">
          <div></div>
        </div>
      </div>
    </div>
    <div class="row loaded">
      <div class="col">Agencies</div>
      <div class="col"><input type="button" id="agency_launch" class="launch" value="Update"></div>
      <div class="col">
        <div id="agency_progress" class="progress">
          <div></div>
        </div>
      </div>
    </div>
    <div class="row loaded">
      <div class="col">Contacts</div>
      <div class="col"><input type="button" id="contacts_launch" class="launch" value="<?php
                                                                                        $last_contact_page = get_transient('contacts_last_page');
                                                                                        print $last_contact_page > 1 ? "Resume" : "Update";
                                                                                        ?>"></div>
      <div class="col">
        <div id="contacts_progress" class="progress">
          <div></div>
        </div>
      </div>
    </div>
  </div>
<?php
}
