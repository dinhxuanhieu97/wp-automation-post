<?php
function custom_cron_intervals($schedules)
{
    // Add a custom interval for yearly (in seconds)
    $schedules['yearly'] = array(
        'interval' => 31536000, // 1 year in seconds
        'display'  => __('Once Yearly')
    );
    return $schedules;
}
add_filter('cron_schedules', 'custom_cron_intervals');
function fetch_and_cache_countries()
{
    $api_url = 'https://countriesnow.space/api/v0.1/countries/info?returns=iso2';
    $all_repos_data = array();
    $response = wp_remote_get("$api_url", array('headers' => array('User-Agent' => 'YourApp')));
    if (is_array($response) && !is_wp_error($response)) {
        $repos_data = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($repos_data)) {
            return; // No more repositories, exit the loop
        }
        // Merge the current page's data into the main array
        $all_repos_data = array_merge($all_repos_data, $repos_data);
        // Increment the page number for the next request
    } else {
        return;
    }
    if (!empty($all_repos_data)) {
        // Specify the JSON file path within your theme folder
        $json_file_path = get_template_directory() . '/countries.json';
        // Convert the fetched data to JSON format
        $json_data = json_encode($all_repos_data, JSON_PRETTY_PRINT);
        // Save the JSON data to the file
        file_put_contents($json_file_path, $json_data);
        // Optionally, you can store the last update timestamp in an option
        update_option('countries_last_updated', time());
    }
}
if (!wp_next_scheduled('update_countries_data_yearly')) {
    wp_schedule_event(time(), 'yearly', 'update_countries_data_yearly');
}
// Hook the data update function to the scheduled event
add_action('update_countries_data_yearly', 'fetch_and_cache_countries');
