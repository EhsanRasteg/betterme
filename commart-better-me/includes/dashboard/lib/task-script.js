jQuery(document).ready(function($) {
  let intervalId;

  /**
   * --- Task Timer Functions (Combined from task-timer-script.js) ---
   */

  // Start task timer: send ajax request to start the task timer.
  function startTaskTimer(taskId) {
    $.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
        action: 'commart_start_task',
        task_id: taskId
      },
      success: function(response) {
        if (response.success) {
          // Start dynamic timer update.
          startDynamicTaskTimer(taskId);
        } else {
          alert(response.data);
        }
      }
    });
  }

  // Pause task timer: send ajax request to pause the timer.
  function pauseTaskTimer(taskId) {
    clearInterval(intervalId);
    $.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
        action: 'commart_pause_task',
        task_id: taskId
      },
      success: function(response) {
        if (response.success) {
          $('#timer-' + taskId).text(formatTime(response.data.elapsed));
          $('#timer-' + taskId).attr('data-elapsed', response.data.elapsed);
        } else {
          alert(response.data);
        }
      }
    });
  }

  // Stop task timer: send ajax request to stop the timer with an optional report.
  function stopTaskTimer(taskId, report) {
    clearInterval(intervalId);
    $.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
        action: 'commart_stop_task',
        task_id: taskId,
        report: report
      },
      success: function(response) {
        if (response.success) {
          $('#timer-' + taskId).text(response.data.elapsed_formatted);
          $('#timer-' + taskId).attr('data-elapsed', response.data.elapsed);
        } else {
          alert(response.data);
        }
      }
    });
  }

  // Dynamically updates the task timer if it is running.
  function startDynamicTaskTimer(taskId) {
    let elapsed = parseInt($('#timer-' + taskId).attr('data-elapsed')) || 0;
    intervalId = setInterval(function() {
      elapsed++;
      $('#timer-' + taskId).attr('data-elapsed', elapsed);
      $('#timer-' + taskId).text(formatTime(elapsed));
    }, 1000);
  }

  // Format seconds into hh:mm:ss.
  function formatTime(seconds) {
    let hrs = Math.floor(seconds / 3600);
    let mins = Math.floor((seconds % 3600) / 60);
    let secs = seconds % 60;
    return ("0" + hrs).slice(-2) + ":" + ("0" + mins).slice(-2) + ":" + ("0" + secs).slice(-2);
  }

  // Event listeners for task timer control buttons.
  $('.start-task-timer').on('click', function() {
    let taskId = $(this).data('id');
    startTaskTimer(taskId);
  });

  $('.pause-task-timer').on('click', function() {
    let taskId = $(this).data('id');
    pauseTaskTimer(taskId);
  });

  $('.stop-task-timer').on('click', function() {
    let taskId = $(this).data('id');
    let report = prompt("لطفاً گزارش (اختیاری) را وارد کنید:", "");
    stopTaskTimer(taskId, report);
  });

  /**
   * --- Task Main Script Functions (Combined from task-script.js) ---
   */

  // Handle form submission for adding/updating a task.
  $('#task-entry-form').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    $.ajax({
      url: ajaxurl,
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'commart_add_update_task',
        form_data: formData
      },
      success: function(response) {
        if (response.success) {
          var task = response.data;
          if ($('#task-row-' + task.id).length) {
            var row = $('#task-row-' + task.id);
            row.find('td:eq(0)').text(task.project_title);
            row.find('td:eq(1)').text(task.title);
            row.find('td:eq(2)').text(task.deadline);
            row.find('td:eq(3)').text(task.elapsed_formatted).attr('data-elapsed', task.elapsed);
          } else {
            var newRow = "<tr id='task-row-" + task.id + "'>" +
              "<td>" + task.project_title + "</td>" +
              "<td>" + task.title + "</td>" +
              "<td>" + task.deadline + "</td>" +
              "<td class='task-timer' data-elapsed='" + task.elapsed + "' id='timer-" + task.id + "'>" + task.elapsed_formatted + "</td>" +
              "<td>" +
                "<label class='betterme-container'>" +
                  "<input type='checkbox' class='toggle-task' data-id='" + task.id + "'>" +
                  "<svg viewBox='0 0 384 512' height='1em' xmlns='http://www.w3.org/2000/svg' class='play'><path d='M73 39c-14.8-9.1-33.4-9.4-48.5-.9S0 62.6 0 80V432c0 17.4 9.4 33.4 24.5 41.9s33.7 8.1 48.5-.9L361 297c14.3-8.7 23-24.2 23-41s-8.7-32.2-23-41L73 39z'></path></svg>" +
                  "<svg viewBox='0 0 320 512' height='1em' xmlns='http://www.w3.org/2000/svg' class='pause'><path d='M48 64C21.5 64 0 85.5 0 112V400c0 26.5 21.5 48 48 48H80c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48H48zm192 0c-26.5 0-48 21.5-48 48V400c0 26.5 21.5 48 48 48h32c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48H240z'></path></svg>" +
                "</label>" +
              "</td>" +
              "<td>" +
                "<button class='stop-task' data-id='" + task.id + "'>تکمیل تسک</button>" +
              "</td>" +
              "<td>" +
                "<button class='report-task' data-id='" + task.id + "' data-report=''>گزارش</button>" +
              "</td>" +
              "<td>" +
                "<button class='edit-task' data-id='" + task.id + "' data-projects_id='" + task.projects_id + "' data-title='" + task.title + "' data-deadline='" + task.deadline + "'>ویرایش</button>" +
              "</td>" +
              "<td>" +
                "<button class='delete-task' data-id='" + task.id + "'>حذف</button>" +
              "</td>" +
            "</tr>";
            $('#tasks-table tbody').prepend(newRow);
          }
          $('#task-entry-form')[0].reset();
          $('#task_id').val('');
        } else {
          alert('خطا: ' + response.data);
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
         console.error("AJAX error:", textStatus, errorThrown);
         alert('خطای AJAX رخ داده است. لطفاً کنسول را بررسی کنید.');
      }
    });
  });

  // Task Editing: populate form with existing task data.
  $(document).on('click', '.edit-task', function() {
    var id = $(this).data('id');
    $('#task_id').val(id);
    $('#task_projects_id').val($(this).data('projects_id'));
    $('#task_title').val($(this).data('title'));
    $('#task_deadline').val($(this).data('deadline'));
    $('html, body').animate({scrollTop: $("#task-form").offset().top}, 500);
  });

  // Task Deletion: remove task after confirming.
  $(document).on('click', '.delete-task', function() {
    if (confirm("آیا از حذف این تسک اطمینان دارید؟")) {
      var id = $(this).data('id');
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'commart_delete_task',
          task_id: id
        },
        success: function(response) {
          if(response.success) {
            $('#task-row-' + id).remove();
          } else {
            alert('خطا: ' + response.data);
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.error("AJAX error:", textStatus, errorThrown);
          alert('خطای AJAX رخ داده است. لطفاً کنسول را بررسی کنید.');
        }
      });
    }
  });

  // Handling the toggle for starting/pausing task timer.
  $(document).on('change', '.toggle-task', function() {
    var taskId = $(this).data('id');
    if ($(this).is(':checked')) {
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'commart_start_task',
          task_id: taskId
        },
        success: function(response) {
          if (!response.success) {
            alert('خطا: ' + response.data);
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.error("AJAX error:", textStatus, errorThrown);
          alert('خطای AJAX رخ داده است. لطفاً کنسول را بررسی کنید.');
        }
      });
    } else {
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'commart_pause_task',
          task_id: taskId
        },
        success: function(response) {
          if (!response.success) {
            alert('خطا: ' + response.data);
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.error("AJAX error:", textStatus, errorThrown);
          alert('خطای AJAX رخ داده است. لطفاً کنسول را بررسی کنید.');
        }
      });
    }
  });

  // Handling report modal display when "تکمیل تسک" button is clicked.
  var currentStopTaskId = null;
  $(document).on('click', '.stop-task', function() {
    currentStopTaskId = $(this).data('id');
    $('#task-report').val('');
    $('#task-report-modal').show();
  });

  // Cancel task report modal.
  $('#cancel-task-report').on('click', function() {
    $('#task-report-modal').hide();
    currentStopTaskId = null;
  });

  // Submit task report and stop the timer.
  $('#submit-task-report').on('click', function() {
    var reportText = $('#task-report').val();
    if (currentStopTaskId) {
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'commart_stop_task',
          task_id: currentStopTaskId,
          report: reportText
        },
        success: function(response) {
          if (response.success) {
            $('#task-row-' + currentStopTaskId).find('.task-timer')
              .text(response.data.elapsed_formatted)
              .attr('data-elapsed', response.data.elapsed);
            $('#task-row-' + currentStopTaskId).find('td:eq(6)').html('<button class="report-task" data-id="'+currentStopTaskId+'" data-report="'+reportText+'">گزارش</button>');
            $('#task-report-modal').hide();
            currentStopTaskId = null;
          } else {
            alert('خطا: ' + response.data);
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.error("AJAX error:", textStatus, errorThrown);
          alert('خطای AJAX رخ داده است. لطفاً کنسول را بررسی کنید.');
        }
      });
    }
  });

  // Show task report modal for viewing/editing report.
  var currentTaskReportId = null;
  $(document).on('click', '.report-task', function() {
    currentTaskReportId = $(this).data('id');
    var currentReport = $(this).data('report') || '';
    $('#task-report-view').val(currentReport);
    $('#view-task-report-modal').show();
  });

  // Update task report.
  $('#update-task-report').on('click', function() {
    var updatedReport = $('#task-report-view').val();
    if (currentTaskReportId) {
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'commart_update_task_report',
          task_id: currentTaskReportId,
          report: updatedReport
        },
        success: function(response) {
          if (response.success) {
            $('button.report-task[data-id="'+currentTaskReportId+'"]').data('report', updatedReport);
            $('#view-task-report-modal').hide();
            currentTaskReportId = null;
          } else {
            alert('خطا: ' + response.data);
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.error("AJAX error:", textStatus, errorThrown);
          alert('خطای AJAX رخ داده است. لطفاً کنسول را بررسی کنید.');
        }
      });
    }
  });

  // Close task report modal.
  $('#close-task-report').on('click', function() {
    $('#view-task-report-modal').hide();
    currentTaskReportId = null;
  });

  // IMPORTANT: Do not forget to define the paths for the ajax and script files in the main tasks file.
  // In your main tasks file (e.g. /commart-better-me/includes/dashboard/tasks.php),
  // ensure to include the following script declarations:
  // <script src="<?php echo plugin_dir_url(__FILE__); ?>lib/task-script.js"></script>
  // and update ajax URLs as needed.
});
