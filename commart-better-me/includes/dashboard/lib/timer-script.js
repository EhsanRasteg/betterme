jQuery(document).ready(function($) {
  let intervalId;

  // Start timer: save the start time and update container_status (play) on the server.
  function startTimer(stepId) {
    $.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
        action: 'commart_start_step',
        step_id: stepId
      },
      success: function(response) {
        if (response.success) {
          // On success, update the UI and start dynamic timing.
          // Optionally, you can update a container status UI element,
          // e.g. mark button as "play" or update a data-attribute.
          startTimming(stepId);
        } else {
          alert(response.data);
        }
      }
    });
  }

  // Pause timer: stop the dynamic update and update container_status (pause) on the server.
  function pauseTimer(stepId) {
    clearInterval(intervalId);
    $.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
        action: 'commart_pause_step',
        step_id: stepId
      },
      success: function(response) {
        if (response.success) {
          // Update timer display with the newly paused elapsed time.
          $('#timer-' + stepId).text(formatTime(response.data.elapsed));
          $('#timer-' + stepId).data('elapsed', response.data.elapsed);
        } else {
          alert(response.data);
        }
      }
    });
  }

  // Stop timer: finalizes the timer and updates the database.
  function stopTimer(stepId, report) {
    clearInterval(intervalId);
    $.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
        action: 'commart_stop_step',
        step_id: stepId,
        report: report
      },
      success: function(response) {
        if (response.success) {
          $('#timer-' + stepId).text(response.data.elapsed_formatted);
          $('#timer-' + stepId).data('elapsed', response.data.elapsed);
        } else {
          alert(response.data);
        }
      }
    });
  }

  // Start dynamic timer update if container_status is play.
  // Retrieves the initial elapsed time from data attribute.
  function startTimming(stepId) {
    let elapsed = parseInt($('#timer-' + stepId).data('elapsed')) || 0;
    intervalId = setInterval(function() {
      elapsed++;
      $('#timer-' + stepId).data('elapsed', elapsed);
      $('#timer-' + stepId).text(formatTime(elapsed));
    }, 1000);
  }

  // Format seconds into hh:mm:ss format.
  function formatTime(seconds) {
    let hrs = Math.floor(seconds / 3600);
    let mins = Math.floor((seconds % 3600) / 60);
    let secs = seconds % 60;
    return ("0" + hrs).slice(-2) + ":" + ("0" + mins).slice(-2) + ":" + ("0" + secs).slice(-2);
  }

  // Event listener for starting the timer.
  $('.start-timer').on('click', function() {
    let stepId = $(this).data('id');
    startTimer(stepId);
  });

  // Event listener for pausing the timer.
  $('.pause-timer').on('click', function() {
    let stepId = $(this).data('id');
    pauseTimer(stepId);
  });

  // Event listener for stopping the timer.
  $('.stop-timer').on('click', function() {
    let stepId = $(this).data('id');
    let report = prompt("Enter report (optional):", "");
    stopTimer(stepId, report);
  });
});