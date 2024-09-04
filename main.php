<?php
/*
Plugin Name: Parent Child Override Extension
Description: This plugin allows you to override a function in a parent plugin.
Version: 1.0
Author: Your Name
*/

// Add your custom code here
function enqueue_toggle_password_script()
{
    // Register the script
    wp_register_script('parent-js', plugin_dir_url(__FILE__) . 'parent.js', array('jquery'), time(), true);
    wp_register_script('chartjs-js', 'https://cdn.jsdelivr.net/npm/chart.js', array('jquery'), time(), true);
    wp_register_style('parent-style', plugin_dir_url(__FILE__) . 'parent-style.css', array(), time());
    wp_localize_script('parent-js', 'jquery_main_before', ['ajax_url' => admin_url('admin-ajax.php'),]);

    wp_enqueue_style('parent-style');
    // Enqueue the script
    wp_enqueue_script('parent-js');
    wp_enqueue_script('chartjs-js');
}
add_action('wp_enqueue_scripts', 'enqueue_toggle_password_script');

// Example: Override a function
if (function_exists('learndash_user_get_enrolled_courses')) {
    // Your custom implementation
    function get_all_course_ids_with_enrollment_status($user_id)
    {
        // Get all LearnDash courses
        $all_courses = get_posts(array(
            'post_type' => 'sfwd-courses',
            'posts_per_page' => -1,
            'fields' => 'ids', // Return only IDs
        ));

        // Get the child user IDs from the parent's user meta
        $child_user_ids = get_user_meta($user_id, 'child_users', true);

        // Ensure child_user_ids is an array
        if (!is_array($child_user_ids)) {
            $child_user_ids = array();
        }

        // Add the parent user ID to the array to check their enrollment too
        $user_ids_to_check = array_merge(array($user_id), $child_user_ids);

        // Prepare an array to hold course IDs
        $enrolled_course_ids = array();

        // Loop through all courses
        foreach ($all_courses as $course_id) {
            // Check if any of the users are enrolled in this course
            $is_enrolled = false;
            foreach ($user_ids_to_check as $user_id) {
                if (sfwd_lms_has_access($course_id, $user_id)) {
                    $is_enrolled = true;
                    break;
                }
            }

            // Add course ID to the array
            $enrolled_course_ids[] = $course_id;
        }

        return $enrolled_course_ids;
    }
}

// Handle the AJAX request
add_action('wp_ajax_update_grading_visibility', 'update_grading_visibility');

function update_grading_visibility()
{
    // Check for nonce security if needed
    // check_ajax_referer('your_nonce', 'security');

    // Validate and sanitize input
    $visibility = isset($_POST['visibility']) ? sanitize_text_field($_POST['visibility']) : 'hidden';
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : get_current_user_id(); // Default to current user if no user ID is provided

    // Update the user meta with the new visibility setting
    update_user_meta($user_id, 'grading_visibility', $visibility);

    // Respond to the AJAX request
    wp_send_json_success('Grading visibility updated successfully.');
}


function handle_user_profile_update() {
    if (!isset($_POST['action']) || $_POST['action'] !== 'update_user_profile') {
        wp_send_json_error('Invalid request');
    }

    $user_id = intval($_POST['user_id']);
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $date_of_birth = sanitize_text_field($_POST['date_of_birth']);
    $password = sanitize_text_field($_POST['password']);

    // Update user data
    $user_data = array(
        'ID' => $user_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
    );
    if ($password) {
        $user_data['user_pass'] = $password;
    }

    // Handle profile image upload
    $file_url = '';
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $uploadedfile = $_FILES['profile_image'];
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            $file_url = $movefile['url'];
            $file_path = $movefile['file'];

            // Create an attachment for the uploaded image
            $wp_filetype = wp_check_filetype($movefile['file'], null);
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title'     => sanitize_file_name($movefile['file']),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
            $attach_id = wp_insert_attachment($attachment, $file_path, $user_id);

            if (!is_wp_error($attach_id)) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
                wp_update_attachment_metadata($attach_id, $attach_data);

                // Update the custom avatar fields
                update_user_meta($user_id, 'ld_dashboard_avatar_id', $attach_id);

                $avatar_sizes = array(
                    'ld-medium' => wp_get_attachment_image_url($attach_id, 'medium'),
                    'medium'    => wp_get_attachment_image_url($attach_id, 'medium'), // Adjust sizes as needed
                );
                update_user_meta($user_id, 'ld_dashboard_avatar_sizes', $avatar_sizes);
            }
        }
    }

    // Update user meta for profile image if available
    if ($file_url) {
        update_user_meta($user_id, 'profile_picture', $file_url); // This line is optional if you're using the fields above
    }

    // Update user
    $updated = wp_update_user($user_data);

    if (is_wp_error($updated)) {
        wp_send_json_error($updated->get_error_message());
    } else {
        // Update date of birth if needed
        update_user_meta($user_id, 'date_of_birth', $date_of_birth);
        wp_send_json_success('Profile updated successfully' . ($file_url ? ' and profile image uploaded' : ''));
    }
}
add_action('wp_ajax_update_user_profile', 'handle_user_profile_update');


function get_user_data()
{
    if (!isset($_POST['action']) || $_POST['action'] !== 'get_user_data' || !isset($_POST['user_id'])) {
        wp_send_json_error('Invalid request');
    }

    $user_id = intval($_POST['user_id']);
    $user = get_user_by('ID', $user_id);

    if (!$user) {
        wp_send_json_error('User not found');
    }

    $profile_image = get_user_meta($user_id, 'profile_picture', true);
    wp_send_json_success(array(
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'date_of_birth' => get_user_meta($user_id, 'date_of_birth', true),
        'profile_image' => $profile_image
    ));
}
add_action('wp_ajax_get_user_data', 'get_user_data');


add_filter( 'avatar_defaults', 'wpb_new_gravatar' );
function wpb_new_gravatar ($avatar_defaults) {
$myavatar = 'https://dilawar.webifypro.online/wp-content/uploads/2024/08/IMG_0147-1.jpeg';
$avatar_defaults[$myavatar] = "Default Gravatar";
return $avatar_defaults;
}


// template override 

// Hook into the LearnDash action that outputs the course content
add_action('learndash-course-content-list-before', 'custom_learndash_override', 10, 2);

function custom_learndash_override($course_id, $user_id)
{
    ob_start();
    // Hook into the function that generates the content to capture and modify it
    add_action('learndash-course-content-list-after', 'custom_modify_learndash_output', 20);
}

function custom_modify_learndash_output()
{
    $content = ob_get_clean();

    // Now modify the div with class "ld-item-list"
    $hide_below_section = "";
    $current_user_id = get_current_user_id();
    $course_id = get_the_ID();
    $parent_id = $course_id;

    $query_args = array(
        'post_type'      => 'sfwd-quiz',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'meta_key'       => 'course_id',
        'meta_value'     => $course_id,
        'meta_compare'   => '=',
        'fields'         => 'ids'
    );

    $query_results = new WP_Query($query_args);

    // Define quiz icons
    $science = plugins_url('parent-child-override-extension/assets/science.png');
    $notes = plugins_url('parent-child-override-extension/assets/notes.png');
    $book = plugins_url('parent-child-override-extension/assets/book.png');
    $mathematics = plugins_url('parent-child-override-extension/assets/mathematic.png');
    $quiz_icons = array($science, $notes, $book, $mathematics);
    $icon_index = 0;

    // Check if the current user is a student
    $current_user = wp_get_current_user();

    if (in_array('child', $current_user->roles)) {
        $hide_below_section = "hide_below_section";

        if ($query_results->have_posts()) {
            echo '<div class="assignments">';

            while ($query_results->have_posts()) {
                $query_results->the_post();
                $quiz_id = get_the_ID();
                $certificate_id = get_post_meta($quiz_id, '_sfwd-quiz', true);
                if (!empty($certificate_id)) {
                    $certificate_id = $certificate_id['sfwd-quiz_certificate'];
                    $certificate_url = get_permalink($certificate_id);
                }
                $certificate_url = learndash_get_certificate_link($quiz_id, $current_user_id);
                if (!empty($certificate_url)) {
                    $certificate_url = preg_replace('#^<a[^>]+href="([^"]+)"[^>]*>.*</a>$#i', '$1', $certificate_url);
                }

                // echo esc_url($certificate_url);
                // Retrieve quiz progress
                $quiz_progress = learndash_user_get_quiz_progress($current_user_id, $quiz_id, $course_id);




                if (!empty($quiz_progress || empty($quiz_progress))) {
                    $latest_attempt = end($quiz_progress);
                    if ($latest_attempt) {
                        $latest_attempt = $latest_attempt['quiz'];
                    }
                    if (!empty($latest_attempt)) {
                        $quiz_status = 'completed';
                    } else {
                        $quiz_status = 'Start';
                    }
                    // $quiz_status = ($latest_attempt['pass'] ? 'completed' : 'Start');
                    $percentage_complete = $latest_attempt['score'];
                    $quiz_title = get_the_title($quiz_id);
                    // Get a sequence of icons
                    $quiz_icon = $quiz_icons[$icon_index];

                    echo '<div class="assignment test" data-id="' . esc_attr($quiz_id) . '">';
                    echo '<div class="icon">';
                    echo '<img src="' . esc_url($quiz_icon) . '" alt="' . esc_attr($quiz_title) . '">';
                    echo '</div>';
                    echo '<div class="details">';
                    echo '<h3>' . esc_html($quiz_title) . '</h3>';
                    echo '<div class="progress">';
                    // Display the progress if needed
                    echo '</div>';

                    echo '</div>';
                    // certificate url
                    if (!empty($certificate_url)) {
                        echo '<div class="details"><a class="certificate-link" href="' . esc_url($certificate_url) . '" target="_new" aria-label="Certificate"><span class="ld-icon ld-icon-certificate"></span> Certificate</a> </div>';
                    }

                    $button_label = ($quiz_status === 'completed') ? 'Retake' : 'Start';

                    echo '<div class="action">';
                    echo '<a href="' . esc_url(get_permalink($quiz_id)) . '" class="continue"><button class="override-button ' . esc_attr(strtolower($button_label)) . '">' . esc_html($button_label) . '</button></a>';
                    echo '</div>';
                    echo '</div>';

                    // Cycle the icons
                    $icon_index++;
                    if ($icon_index >= count($quiz_icons)) {
                        $icon_index = 0;
                    }
                } else {
                    echo '<p>No quiz found for this Course.</p>';
                }
            }

            wp_reset_postdata();
            echo '</div>';
        } else {
            echo '<p>No quizzes found for this course.</p>';
        }
    } else {
        echo '<p>You do not have permission to view this content.</p>';
    }
}


// spald-public-parent-access-shortcode-display.php override file 

// Hook into 'init' action to override the shortcode callback
add_action('init', 'override_ldpa_parent_report');

function override_ldpa_parent_report() {
    // Check if the main class exists
    if (class_exists('Learndash_Access_For_Parents')) {
        // Remove the existing shortcode and re-add it with the custom callback
        remove_shortcode('ldpa_page');
        remove_shortcode('parent_access');

        // Register the shortcode with the custom callback
        add_shortcode('ldpa_page', 'custom_ldpa_parent_report_callback');
        add_shortcode('parent_access', 'custom_ldpa_parent_report_callback');
    }
}

// Custom callback function to replace the original one
function custom_ldpa_parent_report_callback($atts) {
    if (!is_user_logged_in()) {
        return '<p>' . esc_html__('Please login to access this page', 'ea-student-parent-access') . '</p>';
    }
    wp_enqueue_style('custom-learndash-override-parent-shortcode');
    wp_enqueue_script('custom-learndash-override-parent-shortcode');

    $current_user = wp_get_current_user();
    $ldpa = new Learndash_Access_For_Parents();
    $students = $ldpa->ldpa_get_current_user_children($current_user->ID);
    $output = null;

    ob_start();
    // Include your custom file instead of the original one
    include plugin_dir_path(__FILE__) . 'public/partials/custom-parent-access-shortcode-display.php';
    $output .= ob_get_contents();
    ob_end_clean();
    return $output;
}


