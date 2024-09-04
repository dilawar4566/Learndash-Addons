jQuery(document).ready(function ($) {
    $(".wpProQuiz_points").each(function() {
        var pointsText = $(this).html();

        // Replace the text as required
        var updatedText = pointsText.replace("You have reached", "You have scored");
        updatedText = updatedText.replace("point(s)", "point");
        updatedText = updatedText.replace("points", "point");

        // Update the element's HTML
        $(this).html(updatedText);
    });

 const interval = setInterval(function () {
    if (jQuery('input[type="button"][value="Finish Quiz"]').is(":visible")) {
        clearInterval(interval); // Stop polling once the button is visible

        // Bind the click event to the "Finish Quiz" button
        jQuery('input[type="button"][value="Finish Quiz"]').on("click", function () {
            console.log("Finish clicked");

            // After the quiz is finished, give some time for the results to load
            setTimeout(function () {
                console.log("Checking percentage...");
				    jQuery('li:has(.video-class)').hide();


                // Find the percentage element (make sure the selector is correct)
               // Correct selector
					var percentageText = jQuery('.wpProQuiz_points span:nth-child(3)').text().trim();

					// Check if the text was correctly retrieved
					if (percentageText) {
						// Convert the percentage text to an integer, removing the '%' symbol
						var percentage = parseInt(percentageText.replace('%', ''), 10);
						console.log('percentage: ' + percentage);
					} else {
						percentage = 0;
					}


                // Determine which video to play based on the percentage
                var videoId;
               if (percentage >= 90) {
                    videoId = 'video90';
                } else if (percentage >= 80) {
                    videoId = 'video80';
                } else if (percentage >= 70) {
                    videoId = 'video70';
                } else if(percentage == 0){
					videoId = 'video0'
				} else {
                    videoId = 'video60';
                } 

                // Add the id to the video button after 1 second
                setTimeout(function () {
                    console.log('classname ' + videoId);

                    // Hide all videos first
                   

                    // Show and play the correct video
                    if (videoId) {
                        var selectedVideo = jQuery('.' + videoId);
						        selectedVideo.closest('li').show();
                        
                        selectedVideo[0].play();  // Play the video
                    }

                }, 1000); // Adjust this delay as needed
            }, 4000);
        });
    }
}, 500); // Polling interval


  $(".spald-ps__item ").addClass("spald-ps__item--is-active");
  $(".spald-ps__item--is-active").removeClass("spald-ps__item");

  $('input.link_child_input[name="password"]').val("").attr("type", "password");

  // Add the eye icon to each password field
  $('input[type="password"], input[name="password"]').each(function () {
    // Wrap the password field in a div to append the eye icon
    $(this).wrap(
      '<div class="password-wrapper" style="position: relative;"></div>'
    );

    // Create and append the eye icon
    var eyeIcon = $(
      '<span class="toggle-password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">👁️</span>'
    );
    $(this).after(eyeIcon);
  });

  // Toggle password visibility on icon click
  $(document).on("click", ".toggle-password", function () {
    var passwordInput = $(this).siblings(
      'input[type="password"], input[type="text"]'
    );
    if (passwordInput.attr("type") === "password") {
      passwordInput.attr("type", "text");
      $(this).text("🙈");
    } else {
      passwordInput.attr("type", "password");
      $(this).text("👁️");
    }
  });
});
var existingChart = null; // Declare the variable outside the click handler

jQuery(document).ready(function () {
  // Open the popup
  jQuery(".view-graphs").click(function (e) {
    e.preventDefault();
    var ctx = document.querySelector(".chart-student").getContext("2d");
    var question_category = jQuery(this).attr("data-question-category");
    var labels = question_category.split(",");

    // Get the percentage values from the data-percentage-category attribute
    var percentageData = JSON.parse(
      jQuery(this).attr("data-percentage-category")
    );
    var overallPercentageCategory = JSON.parse(
      jQuery(this).attr("data-overall-percentage-category")
    );
    var studentData = [];
    var hsttData = [];
    labels.forEach(function (label) {
      studentData.push(percentageData[label]);
      hsttData.push(overallPercentageCategory[label] || 0);
    });

    // Check if an existing chart instance exists and destroy it
    if (existingChart) {
      existingChart.destroy();
    }
    // Create a new chart instance and assign it to existingChart
    existingChart = new Chart(ctx, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Student",
            data: studentData,
            backgroundColor: "rgba(54, 162, 235, 0.6)",
            borderColor: "rgba(54, 162, 235, 1)",
            borderWidth: 1,
          },
          {
            label: "HSTT Average",
            data: hsttData,
            backgroundColor: "rgba(255, 99, 132, 0.6)",
            borderColor: "rgba(255, 99, 132, 1)",
            borderWidth: 1,
          },
          {
            label: "National Average",
            data: [70, 65, 60, 70, 65],
            backgroundColor: "rgba(255, 159, 64, 0.6)",
            borderColor: "rgba(255, 159, 64, 1)",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
          },
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: function (context) {
                return `${context.dataset.label}: ${context.raw}`;
              },
            },
          },
        },
      },
    });

    jQuery("#graphPopup").fadeIn();

    // Close the popup
    jQuery(".close").click(function () {
      jQuery("#graphPopup").fadeOut();
    });

    // Close the popup if the user clicks outside of it
    jQuery(window).click(function (e) {
      if (jQuery(e.target).is("#graphPopup")) {
        jQuery("#graphPopup").fadeOut();
      }
    });
  });
});

jQuery(document).ready(function ($) {
  // When the checkbox state changes
  $('input[name="grading-visibility"]').change(function () {
    var $successMessage = $(this).closest('div').find('.success_message');
    $successMessage.fadeIn();
    $successMessage.html("Updating...");
    var isChecked = $(this).is(":checked");
    var visibility = isChecked ? "visible" : "hidden";
    var student_id = $(this).attr("data-student-id");

    // Make an AJAX request to update grading visibility
    $.ajax({
      url: jquery_main_before.ajax_url,
      type: "POST",
      data: {
        action: "update_grading_visibility", // AJAX action hook
        visibility: visibility,
        user_id: student_id, // Pass current user ID or student ID here
      },
      success: function (response) {
        // Optionally handle the response
        $successMessage.css("color", "green");
        $successMessage.html(response.data);

        setTimeout(function () {
          $successMessage.fadeOut();
        }, 3000);
      },
    });
  });
});

jQuery(document).ready(function($) {
  // Open the popup
  $('.edit_profile').on('click', function() {
      $('#user-update-popup').show();
      var userId = $(this).data('student-id');
      $('#user-id').val(userId);
      
      // Fetch existing user data
      $.ajax({
        url: jquery_main_before.ajax_url,
          type: 'POST',
          data: {
              action: 'get_user_data',
              user_id: userId
          },
          success: function(response) {
              if(response.success) {
                  $('#first-name').val(response.data.first_name);
                  $('#last-name').val(response.data.last_name);
                  $('#date-of-birth').val(response.data.date_of_birth);
                  // The profile image can be handled with a preview
                  $('#profile-image-preview').attr('src', response.data.profile_image);
              } else {
                  alert('Failed to load user data');
              }
          },
          error: function() {
              alert('An error occurred');
          }
      });
  });

  // Close the popup
  $('.close').on('click', function() {
      $('#user-update-popup').hide();
  });

  // Form submission
  $('#update-user-form').on('submit', function(e) {
      e.preventDefault();

      var formData = new FormData(this);
      $.ajax({
        url: jquery_main_before.ajax_url,
          type: 'POST',
          data: formData,
          contentType: false,
          processData: false,
          success: function(response) {
            console.log(response);
              alert('Profile updated successfully');
              $('#user-update-popup').hide();
              // Optionally handle the response or refresh the page
          },
          error: function() {
              alert('An error occurred');
          }
      });
  });
});

