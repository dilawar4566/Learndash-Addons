<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://honorswp.com/
 * @since      1.0.0
 *
 * @package    Learndash_Access_For_Parents
 * @subpackage Learndash_Access_For_Parents/public/partials
 */

if (! defined('ABSPATH')) {
    exit;
}

?>

<?php
$expand_icon = '<span class="spald-ps__item__icon">
		<svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
		<defs><style>.cls-1{fill:none;stroke:#000;stroke-linecap:round;stroke-linejoin:round;stroke-width:2px;}</style></defs>
		<g id="chevron-bottom"><line class="cls-1" x1="16" x2="7" y1="20.5" y2="11.5"/><line class="cls-1" x1="25" x2="16" y1="11.5" y2="20.5"/></g></svg>
	</span>';

if (0 === count($students)) : ?>
    <div id="spald-pa-shortcode" class="spald-ps">
        <div class="spald-ps__header">
            <div class="spald-ps__header__title">
                <?php '<p>' . printf(esc_html_x('You don\'t have any associated %s accounts', 'placeholder: Child', 'learndash-access-for-parents'), LearnDash_Access_For_Parents_Custom_Label::get_label('child')) . '</p>'; ?>
            </div>
        </div>
    </div>
<?php else : ?>

    <div id="spald-pa-shortcode" class="spald-ps">
        <div class="spald-ps__header">
            <div class="spald-ps__header__title">
                <?php
                echo esc_html(LearnDash_Access_For_Parents_Custom_Label::get_label('children'));
                ?>
            </div>
            <input
                id="spald-student-search-input"
                class="spald-ps__search"
                placeholder="<?php
                                // translators: placeholder: children.
                                printf(esc_html_x('Search %s', 'placeholder: children', 'ea-student-parent-access'), LearnDash_Access_For_Parents_Custom_Label::get_label('children'));
                                ?>" />
        </div>
        <div class="spald-ps__container from child theme">
            <div id="user-update-popup" class="popup">
                <div class="popup-content">
                    <span class="close">&times;</span>
                    <h2>Update Student Profile</h2>
                    <form id="update-user-form" method="post" enctype="multipart/form-data">

                        <input type="hidden" name="action" value="update_user_profile">
                        <input type="hidden" name="user_id" id="user-id" value="">
                        <div class="group">
                            <label for="first-name">First Name:</label>
                            <input type="text" name="first_name" id="first-name" required>
                        </div>
                        <div class="group">
                            <label for="last-name">Last Name:</label>
                            <input type="text" name="last_name" id="last-name" required>
                        </div>
                        <div class="group">
                            <label for="date-of-birth">Date of Birth:</label>
                            <input type="date" name="date_of_birth" id="date-of-birth">
                        </div>
                        <div class="group">
                            <label for="password">Password:</label>
                            <input type="password" name="password" id="password">
                        </div>
                        <div class="group">
                            <label for="profile-image">Profile Image:</label>
                            <input type="file" name="profile_image" id="profile-image">
                        </div>
                        <button type="submit">Update</button>
                    </form>
                </div>
            </div>

            <?php
            foreach ($students as $student) {
                $student_id  = $student->child_id;
                $student     = get_user_by('id', $student_id);
                $user_all_meta = get_user_meta($student_id);

                $profile_pic = get_user_meta($student_id, 'profile_picture', true);
                $ld_dashboard_avatar_sizes = get_user_meta($student_id, 'ld_dashboard_user_profile_fields', true);


                $profile_pic = $profile_pic ? $profile_pic : get_avatar_url($student_id);
                $all_courses = ld_get_mycourses($student_id);
                // <div class="spald-ps__item--is-active"></div> // for active items
                $grading_visibility = get_user_meta($student_id, 'grading_visibility', true);
                if ($grading_visibility == 'visible') {
                    $grading_visibility = "checked";
                } else {
                    $grading_visibility = "unchecked";
                }

            ?>

                <div id="<?php echo esc_attr($student_id); ?>" class="spald-ps__item student-card" data-name="<?php echo esc_attr($student->display_name); ?>" data-email="<?php echo esc_attr($student->user_email); ?>">
                    <button class="spald-ps__item__button">
                        <span>
                            <img class="spald-ps__item__image" src="<?php echo esc_attr($profile_pic); ?>" alt="">
                        </span>
                        <span class="spald-ps__item__details">
                            <span class="spald-ps__item__heading"><?php echo esc_html($student->display_name); ?></span>
                            <span class="spald-ps__item__subheading"><?php echo esc_html($student->user_email); ?></span>
                        </span>
                        <?php echo $expand_icon; ?>
                    </button>
                    <div class="spald-ps__item__wrapper">
                        <div class="spald-ps__header">
                            <div class="spald-ps__header__title">
                                <?php esc_html_e('Courses', 'ea-student-parent-access'); ?>
                            </div>
                            <input class="spald-ps__search" placeholder="<?php esc_attr_e('Search Course', 'ea-student-parent-access'); ?>" />
                        </div>
                        <div class="spald-ps__container spald-ps__container--colored">
                            <div class="grading_switch">
                                <h2>Grading</h2>
                                <span class="span_title">Hidden</span>
                                <label class="switch">
                                    <input name="grading-visibility" data-student-id="<?php echo esc_attr($student_id); ?>" type="checkbox" value="unchecked" <?php echo $grading_visibility; ?>>
                                    <span class="slider round"></span>
                                </label>
                                <span class="span_title">Visible</span>
                                <span class="success_message">Grading visibility updated successfully.</span>
                                <span class="edit_profile" data-student-id="<?php echo esc_attr($student_id); ?>">Edit profile </span>
                                <span class="assign_grade" data-student-id="<?php echo esc_attr($student_id); ?>">Assign Grade </span>
                            </div>
                            <div class="progress-list">
                                <h2>Progress</h2>

                                <div id="graphPopup" class="popup">
                                    <div class="popup-content">
                                        <span class="close">&times;</span>
                                        <h2>Student Progress</h2>
                                        <canvas class="chart-student"></canvas> <!-- Placeholder for the graph -->
                                    </div>
                                </div>
                            </div>
                            <?php
                            // get number of user as student in all courses

                            $args = array(
                                'role'    => 'child',
                                'fields'  => 'ID', // We only need the user IDs
                            );

                            $user_query = new WP_User_Query($args);

                            // Get the total number of users
                            $total_students = $user_query->get_total();


                            // Example usage: echo the total number of students
                            // echo 'Total number of students: ' . $total_students . '<br>';
                            foreach ($all_courses as $course_id) {
                                $progress = learndash_user_get_course_progress($student_id, $course_id, 'summary');
                                $lessons  = learndash_course_get_lessons($course_id);
                                $lessons  = array_filter($lessons, 'learndash_lesson_hasassignments');

                                $quiz_activities = $ldpa->ldpa_get_user_quiz_activities($student_id, $course_id);
                                $progressPercentage = ($progress['completed'] / $progress['total']) * 100;

                            ?>
                                <div class="progress-list">
                                    <h2> <?php echo esc_html(get_the_title($course_id)); ?> </h2>
                                </div>
                                <?php
                                $quizzes = [];
                                foreach ($quiz_activities as $activity) {
                                    if (! in_array($activity['post_id'], $quizzes, true)) {
                                        $quizzes[] = $activity['post_id'];
                                    }
                                }

                                foreach ($quizzes as $key => $quiz_id) {

                                    $quiz               = get_post($quiz_id);
                                    global $wpdb; // WordPress database global object
                                    $questions = learndash_get_quiz_questions($quiz_id); // Retrieve all question IDs for the quiz
                                    $questions = array_keys($questions);

                                    // Initialize arrays for unique categories and questions by category
                                    $unique_categories = [];
                                    $questions_by_category = [];
                                    $student_correct_answers_by_category = [];
                                    $total_questions_by_category = [];

                                    // Loop through each question
                                    foreach ($questions as $question_id) {

                                        $question_meta = get_post_meta($question_id);
                                        $question_pro_category = $question_meta['question_pro_category'][0]; // Assuming this is the category_id

                                        // Fetch the category name from the database
                                        $category_name = $wpdb->get_var($wpdb->prepare(
                                            "SELECT `category_name` FROM `wp_learndash_pro_quiz_category` WHERE `category_id` = %d",
                                            $question_pro_category
                                        ));

                                        // Initialize the category in the array if it's not already set
                                        if (!isset($questions_by_category[$category_name])) {
                                            $questions_by_category[$category_name] = [];
                                            $student_correct_answers_by_category[$category_name] = 0;
                                            $total_questions_by_category[$category_name] = 0;
                                        }

                                        // Add the question ID to the category's array
                                        $questions_by_category[$category_name][] = $question_id;
                                        $total_questions_by_category[$category_name]++;

                                        // Add the category name to the array if it's not already there
                                        if ($category_name && !in_array($category_name, $unique_categories)) {
                                            $unique_categories[] = $category_name;
                                        }
                                    }

                                    // Fetch student's quiz statistics
                                    $statistic_ref_id_array = [];
                                    $statistic_ref_data = get_user_meta($student_id, '_sfwd-quizzes', true);

                                    if (!empty($statistic_ref_data) && is_array($statistic_ref_data)) {
                                        foreach ($statistic_ref_data as $quiz_data) {
                                            if (isset($quiz_data['statistic_ref_id'])) {
                                                $statistic_ref_id_array[] = $quiz_data['statistic_ref_id'];
                                            }
                                        }
                                    }

                                    // Loop through each question and calculate correct answers
                                    foreach ($questions as $question_id) {
                                        foreach ($statistic_ref_id_array as $statistic_ref_id) {
                                            // Fetch the student's answer data
                                            $quiz_statistic = $wpdb->get_row($wpdb->prepare(
                                                "SELECT `correct_count`, `answer_data` FROM `wp_learndash_pro_quiz_statistic` 
             											WHERE `statistic_ref_id` = %d AND `question_post_id` = %d",
                                                $statistic_ref_id,
                                                $question_id
                                            ), ARRAY_A);

                                            // Check if data exists and calculate correct answers
                                            if (!empty($quiz_statistic)) {
                                                $correct_count = $quiz_statistic['correct_count'];

                                                // Get category name for this question
                                                $question_meta = get_post_meta($question_id);
                                                $question_pro_category = $question_meta['question_pro_category'][0];
                                                $category_name = $wpdb->get_var($wpdb->prepare(
                                                    "SELECT `category_name` FROM `wp_learndash_pro_quiz_category` WHERE `category_id` = %d",
                                                    $question_pro_category
                                                ));

                                                // Increment correct answer count if correct
                                                if ($correct_count > 0) {
                                                    $student_correct_answers_by_category[$category_name]++;
                                                }
                                            }
                                        }
                                    }

                                    // Calculate percentage of correct answers by category
                                    $category_percentage = [];
                                    foreach ($unique_categories as $category_name) {
                                        $correct_answers = $student_correct_answers_by_category[$category_name];
                                        $total_questions = $total_questions_by_category[$category_name];
                                        $category_percentage[$category_name] = ($correct_answers / $total_questions) * 100;
                                    }

                                    // Output the results
                                    $percentage_category = [];
                                    foreach ($category_percentage as $category_name => $percentage) {
                                        // save in array key category anme and value percenatge
                                        $percentage_category[$category_name] = $percentage;
                                    }
                                    $category_percentages = [];

                                    foreach ($questions_by_category as $category => $questions) {
                                        $total_correct = 0;
                                        $total_count = 0;

                                        foreach ($questions as $question_id) {
                                            // Get counts for each question
                                            $query = $wpdb->prepare(
                                                "SELECT correct_count, COUNT(*) as count 
												 FROM {$wpdb->prefix}learndash_pro_quiz_statistic 
												 WHERE question_post_id = %d 
												 GROUP BY correct_count",
                                                $question_id
                                            );

                                            $results = $wpdb->get_results($query, ARRAY_A);

                                            $count_total = 0;
                                            $count_correct = 0;

                                            foreach ($results as $row) {
                                                if ($row['correct_count'] == 0) {
                                                    $count_total += $row['count'];
                                                } elseif ($row['correct_count'] == 1) {
                                                    $count_correct = $row['count'];
                                                    $count_total += $row['count'];
                                                }
                                            }

                                            $total_count += $count_total;
                                            $total_correct += $count_correct;
                                        }

                                        // Calculate the percentage for the category
                                        $category_percentage = ($total_count > 0) ? ($total_correct / $total_count) * 100 : 0;
                                        $category_percentages[$category] = round($category_percentage, 2);
                                    }
                                    $overall_percenage_category = [];
                                    foreach ($category_percentages as $category => $percentage) {
                                        $overall_percenage_category[$category] = $percentage;
                                    }



                                    $quiz_progress      = array_values(learndash_user_get_quiz_progress($student_id, $quiz_id, $course_id));
                                    $student_meta = get_userdata($student_id);
                                    // echo '<pre>';
                                    // // print_r($questions_by_category);
                                    // print_r($student_id);
                                    // echo '</pre>';
                                    $scores             = array_column(array_values($quiz_progress), 'points');
                                    $total_score        = array_column(array_values($quiz_progress), 'total_points');
                                    $highest_score      = ! empty($scores) ? max($scores) : '';
                                    $count              = learndash_get_user_quiz_attempts_count($student_id, $quiz_id);
                                    $certificate_result = $ldpa->ldpa_get_user_quiz_certificate_status($quiz_id, $student_id);
                                    $unique_categories = implode(',', $unique_categories);
                                    $percentage_category = json_encode($percentage_category);
                                    $overall_percenage_category = json_encode($overall_percenage_category);

                                ?>


                                    <div class="progress-list">
                                        <div class="progress-item">
                                            <span><?php echo esc_html($quiz->post_title); ?></span>
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: 0%;"></div>
                                                <span class="progress-text"><?php //echo esc_html($progress['completed']) . '/' . esc_html($progress['total']); 
                                                                            ?></span>
                                            </div>
                                            <a data-overall-percentage-category="<?php echo esc_attr($overall_percenage_category); ?>" data-percentage-category="<?php echo esc_attr($percentage_category); ?>" data-question-category="<?php echo esc_attr($unique_categories); ?>" href="#" class="view-graphs">View graphs</a>
                                        </div>
                                    </div>
                                    <!-- <div class="spald-ps__data__wrapper spald-ps__data__wrapper--quizzes">
										<div class="spald-ps__data__text"><?php echo esc_html($quiz->post_title); ?></div>
										<div class="spald-ps__data__text"><?php echo esc_html($count); ?></div>
										<div class="spald-ps__data__text"><?php echo esc_html($highest_score); ?></div>
										<div class="spald-ps__data__text"><?php echo esc_html($total_score[0]); ?></div>
										<div class="spald-ps__data__text"><?php echo esc_html($certificate_result); ?></div>
									</div> -->
                                <?php }


                                ?>

                                <div
                                    id="<?php echo esc_attr($student_id) . '-' . esc_attr($course_id); ?>"
                                    class="spald-ps__item"
                                    data-course-name="<?php echo esc_attr(get_the_title($course_id)); ?>"
                                    data-course-status="<?php echo esc_attr(learndash_course_status($course_id, $student_id)); ?>" style="display: none;">
                                    <button class="spald-ps__item__button">
                                        <span class="spald-ps__item__details">
                                            <span class="spald-ps__item__heading">
                                                <?php
                                                echo esc_html(
                                                    get_the_title($course_id)
                                                );
                                                ?>
                                            </span>
                                            <span class="spald-ps__item__subheading">
                                                <?php echo esc_html(learndash_course_status($course_id, $student_id)); ?>
                                            </span>
                                        </span>
                                        <span class="spald-ps__item__details">
                                            <span class="spald-ps__item__subheading">
                                                <?php
                                                printf(
                                                    // translators: placeholders: %1$d: Number of completed steps, %2$d: Number of total steps.
                                                    esc_html__('Progress: Completed %1$d of %2$d Total Steps.', 'ea-student-parent-access'),
                                                    esc_html($progress['completed']),
                                                    esc_html($progress['total'])
                                                );
                                                ?>
                                            </span>
                                        </span>
                                        <?php echo $expand_icon; ?>
                                    </button>
                                    <div class="spald-ps__item__wrapper">
                                        <div class="spald-ps__container">
                                            <div
                                                id="<?php echo esc_attr($student_id) . '-' . esc_attr($course_id) . '-assignments'; ?>"
                                                class="spald-ps__item">
                                                <button
                                                    class="spald-ps__item__button spald-ps__item__button--small"
                                                    <?php
                                                    if (0 === count($lessons)) {
                                                    ?>
                                                    disabled
                                                    <?php
                                                    }
                                                    ?>>
                                                    <span class="spald-ps__item__details">
                                                        <span class="spald-ps__item__heading">
                                                            <?php esc_html_e('Assignments', 'ea-student-parent-access'); ?>
                                                        </span>
                                                    </span>
                                                    <span class="spald-ps__item__details">
                                                        <?php if (0 === count($lessons)) { ?>
                                                            <span class="spald-ps__item__subheading">
                                                                <?php esc_html_e('No assignments required.', 'ea-student-parent-access'); ?>
                                                            </span>
                                                        <?php } ?>
                                                    </span>
                                                    <?php if (0 !== count($lessons)) { ?>
                                                        <?php echo $expand_icon; ?>
                                                    <?php } else { ?>
                                                        <span></span>
                                                    <?php } ?>
                                                </button>
                                                <div class="spald-ps__item__wrapper">
                                                    <div class="spald-ps__container">
                                                        <?php
                                                        foreach ($lessons as $key => $lesson) {
                                                            $assignment = learndash_get_user_assignments($lesson->ID, $student_id, $course_id);
                                                        ?>

                                                            <?php if (0 === $key) { ?>
                                                                <div class="spald-ps__data__wrapper spald-ps__data__wrapper--assignments">
                                                                    <div class="spald-ps__data__title">
                                                                        <?php esc_html_e('Attached to Lesson', 'ea-student-parent-access'); ?>
                                                                    </div>
                                                                    <div class="spald-ps__data__title">
                                                                        <?php esc_html_e('Status', 'ea-student-parent-access'); ?>
                                                                    </div>
                                                                </div>
                                                            <?php } ?>

                                                            <div class="spald-ps__data__wrapper spald-ps__data__wrapper--assignments">
                                                                <div class="spald-ps__data__text"><?php echo esc_html($lesson->post_title); ?></div>
                                                                <div class="spald-ps__data__text">
                                                                    <?php
                                                                    (! empty($assignment))
                                                                        ? esc_html_e('Submitted', 'ea-student-parent-access')
                                                                        : esc_html_e('Not Submitted', 'ea-student-parent-access');
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        <?php
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <div
                                                id="<?php echo esc_attr($student_id) . '-' . esc_attr($course_id) . '-quiz-activity'; ?>"
                                                class="spald-ps__item">
                                                <button
                                                    class="spald-ps__item__button spald-ps__item__button--small"
                                                    <?php
                                                    if (0 === count($quiz_activities)) {
                                                    ?>
                                                    disabled
                                                    <?php
                                                    }
                                                    ?>>
                                                    <span class="spald-ps__item__details">
                                                        <span class="spald-ps__item__heading">
                                                            <?php esc_html_e('Quiz Activity', 'ea-student-parent-access'); ?>
                                                        </span>
                                                    </span>
                                                    <span class="spald-ps__item__details">
                                                        <?php
                                                        if (0 === count($quiz_activities)) {
                                                        ?>
                                                            <span class="spald-ps__item__subheading">
                                                                <?php esc_html_e('No current Quiz activity.', 'ea-student-parent-access'); ?>
                                                            </span>
                                                        <?php
                                                        }
                                                        ?>
                                                    </span>
                                                    <?php if (0 !== count($quiz_activities)) { ?>
                                                        <?php echo $expand_icon; ?>
                                                    <?php } else { ?>
                                                        <span></span>
                                                    <?php } ?>
                                                </button>
                                                <div class="spald-ps__item__wrapper">
                                                    <div class="spald-ps__container">
                                                        <?php
                                                        $quizzes = [];
                                                        foreach ($quiz_activities as $activity) {
                                                            if (! in_array($activity['post_id'], $quizzes, true)) {
                                                                $quizzes[] = $activity['post_id'];
                                                            }
                                                        }

                                                        foreach ($quizzes as $key => $quiz_id) {
                                                            $quiz               = get_post($quiz_id);
                                                            $quiz_progress      = array_values(learndash_user_get_quiz_progress($student_id, $quiz_id, $course_id));
                                                            $scores             = array_column(array_values($quiz_progress), 'points');
                                                            $total_score        = array_column(array_values($quiz_progress), 'total_points');
                                                            $highest_score      = ! empty($scores) ? max($scores) : '';
                                                            $count              = learndash_get_user_quiz_attempts_count($student_id, $quiz_id);
                                                            $certificate_result = $ldpa->ldpa_get_user_quiz_certificate_status($quiz_id, $student_id);


                                                        ?>

                                                            <div class="spald-ps__data__wrapper spald-ps__data__wrapper--quizzes">
                                                                <div class="spald-ps__data__title">
                                                                    <?php esc_html_e('Quiz', 'ea-student-parent-access'); ?>
                                                                </div>
                                                                <div class="spald-ps__data__title">
                                                                    <?php esc_html_e('Attempts', 'ea-student-parent-access'); ?>
                                                                </div>
                                                                <div class="spald-ps__data__title">
                                                                    <?php esc_html_e('Highest Score', 'ea-student-parent-access'); ?>
                                                                </div>
                                                                <div class="spald-ps__data__title">
                                                                    <?php esc_html_e('Total Points', 'ea-student-parent-access'); ?>
                                                                </div>
                                                                <div class="spald-ps__data__title">
                                                                    <?php esc_html_e('Certificate', 'ea-student-parent-access'); ?>
                                                                </div>
                                                            </div>

                                                            <div class="spald-ps__data__wrapper spald-ps__data__wrapper--quizzes">
                                                                <div class="spald-ps__data__text"><?php echo esc_html($quiz->post_title); ?></div>
                                                                <div class="spald-ps__data__text"><?php echo esc_html($count); ?></div>
                                                                <div class="spald-ps__data__text"><?php echo esc_html($highest_score); ?></div>
                                                                <div class="spald-ps__data__text"><?php echo esc_html($total_score[0]); ?></div>
                                                                <div class="spald-ps__data__text"><?php echo esc_html($certificate_result); ?></div>
                                                            </div>

                                                            <div class="spald-ps__container spald-ps__container--quiz-attempts">
                                                                <?php
                                                                foreach ($quiz_progress as $key => $progress) {

                                                                ?>

                                                                    <?php if (0 === $key) { ?>
                                                                        <div class="spald-ps__data__wrapper spald-ps__data__wrapper--quiz-progress">
                                                                            <div class="spald-ps__data__title">
                                                                                <?php esc_html_e('Attempt', 'ea-student-parent-access'); ?>
                                                                            </div>
                                                                            <div class="spald-ps__data__title">
                                                                                <?php esc_html_e('Score', 'ea-student-parent-access'); ?>
                                                                            </div>
                                                                            <div class="spald-ps__data__title">
                                                                                <?php esc_html_e('Time spent (secs)', 'ea-student-parent-access'); ?>
                                                                            </div>
                                                                        </div>
                                                                    <?php } ?>

                                                                    <div class="spald-ps__data__wrapper spald-ps__data__wrapper--quiz-progress">
                                                                        <div class="spald-ps__data__text"><?php echo esc_html('#' . ($key + 1)); ?></div>
                                                                        <div class="spald-ps__data__text"><?php echo esc_html($progress['score']); ?></div>
                                                                        <div class="spald-ps__data__text"><?php echo esc_html($progress['timespent']); ?></div>
                                                                    </div>

                                                                <?php
                                                                }
                                                                ?>
                                                            </div>

                                                        <?php
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
<?php endif;
