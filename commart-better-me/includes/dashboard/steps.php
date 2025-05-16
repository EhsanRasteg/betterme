<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include AJAX timer functions
require_once plugin_dir_path( __FILE__ ) . 'lib/ajax-timer.php';

global $wpdb;
$current_user = wp_get_current_user();
$steps_table  = $wpdb->prefix . 'commart_better_me_steps';
$plans_table  = $wpdb->prefix . 'commart_better_me_plans';

// Fetch steps for the current user along with the plan title
$query = "SELECT s.*, p.description as plan_title 
          FROM $steps_table s 
          LEFT JOIN $plans_table p ON s.plan_id = p.id 
          WHERE s.user_id = %d 
          ORDER BY s.created_at DESC";
$steps = $wpdb->get_results( $wpdb->prepare( $query, $current_user->ID ) );

// Fetch all plans for the step form select options
$plans = $wpdb->get_results( $wpdb->prepare( "SELECT id, description FROM $plans_table WHERE user_id = %d ORDER BY description ASC", $current_user->ID ) );

/**
 * Helper to format elapsed seconds as hh:mm:ss.
 */
function format_elapsed_time( $seconds ) {
    $h = floor( $seconds / 3600 );
    $m = floor( ($seconds % 3600) / 60 );
    $s = $seconds % 60;
    return sprintf("%02d:%02d:%02d", $h, $m, $s);
}
?>
<div id="step-list">
  <h2>List of Steps</h2>
  <table id="steps-table" border="1" cellpadding="5" cellspacing="0">
    <thead>
      <tr>
        <th>Plan</th>
        <th>Step Title</th>
        <th>Deadline</th>
        <th>Timer</th>
        <th>Start/Pause</th>
        <th>Done</th>
        <th>Report</th>
        <th>Edit</th>
        <th>Delete</th>
      </tr>
    </thead>
    <tbody>
      <?php if($steps): ?>
        <?php foreach($steps as $step): 
          // Calculate elapsed time: if timer is running, compute from timer_start plus any previous elapsed_time; otherwise use stored elapsed_time.
          if( !empty($step->timer_start) ){
              $start_time = strtotime( $step->timer_start );
              $now = current_time('timestamp');
              $elapsed = intval($step->elapsed_time) + ($now - $start_time);
          } else {
              $elapsed = intval($step->elapsed_time);
          }
          $formatted = format_elapsed_time( $elapsed );
        ?>
          <tr id="step-row-<?php echo esc_attr( $step->id ); ?>">
            <td><?php echo esc_html( $step->plan_title ); ?></td>
            <td><?php echo esc_html( $step->title ); ?></td>
            <td><?php echo esc_html( $step->deadline ); ?></td>
            <td class="step-timer" data-elapsed="<?php echo esc_attr( $elapsed ); ?>" id="timer-<?php echo esc_attr( $step->id ); ?>">
              <?php echo esc_html( $formatted ); ?>
            </td>
            <td>
              <label class="betterme-container">
                <input type="checkbox" class="toggle-step" data-id="<?php echo esc_attr( $step->id ); ?>"
                  <?php echo ( !empty($step->timer_start) ? 'checked' : ''); ?>>
                <svg viewBox="0 0 384 512" height="1em" xmlns="http://www.w3.org/2000/svg" class="play">
                  <path d="M73 39c-14.8-9.1-33.4-9.4-48.5-.9S0 62.6 0 80V432c0 17.4 9.4 33.4 24.5 41.9s33.7 8.1 48.5-.9L361 297c14.3-8.7 23-24.2 23-41s-8.7-32.2-23-41L73 39z"></path>
                </svg>
                <svg viewBox="0 0 320 512" height="1em" xmlns="http://www.w3.org/2000/svg" class="pause">
                  <path d="M48 64C21.5 64 0 85.5 0 112V400c0 26.5 21.5 48 48 48H80c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48H48zm192 0c-26.5 0-48 21.5-48 48V400c0 26.5 21.5 48 48 48h32c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48H240z"></path>
                </svg>
              </label>
            </td>
            <td>
              <button class="stop-step" data-id="<?php echo esc_attr( $step->id ); ?>">Stop Step</button>
            </td>
            <td>
              <button class="report-step" data-id="<?php echo esc_attr( $step->id ); ?>" data-report="<?php echo esc_attr( $step->report ); ?>">Report</button>
            </td>
            <td>
              <button class="edit-step"
                data-id="<?php echo esc_attr( $step->id ); ?>"
                data-plan_id="<?php echo esc_attr( $step->plan_id ); ?>"
                data-title="<?php echo esc_attr( $step->description ); ?>"
                data-deadline="<?php echo esc_attr( $step->deadline ); ?>">
                Edit
              </button>
            </td>
            <td>
              <button class="delete-step" data-id="<?php echo esc_attr( $step->id ); ?>">Delete</button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
          <tr>
            <td colspan="9">No steps found.</td>
          </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div id="step-form">
  <h2>Add / Edit Step</h2>
  <form id="step-entry-form">
    <input type="hidden" name="step_id" id="step_id" value="">
    <p>
      <label for="step_plan_id">Plan:</label>
      <select name="step_plan_id" id="step_plan_id" required>
        <option value="">Select Plan</option>
        <?php foreach($plans as $plan): ?>
          <option value="<?php echo esc_attr( $plan->id ); ?>">
            <?php echo esc_html( $plan->description ); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <label for="step_title">Step Title:</label>
      <input type="text" name="step_title" id="step_title" required>
    </p>
    <p>
      <label for="step_deadline">Deadline:</label>
      <input type="date" name="step_deadline" id="step_deadline" required>
    </p>
    <p>
      <button type="submit" id="submit-step">Submit</button>
      <button type="reset" id="reset-step-form">Reset</button>
    </p>
  </form>
</div>

<!-- Popup modal for entering a report when stopping a step -->
<div id="report-modal" style="display:none; position:fixed; top:50%; left:50%; transform: translate(-50%, -50%);
    background:#fff; padding:20px; border:1px solid #000; z-index:9999;">
  <h3>Enter Report</h3>
  <textarea id="step-report" rows="5" cols="40"></textarea>
  <br>
  <button id="submit-report">Submit Report</button>
  <button id="cancel-report">Cancel</button>
</div>

<!-- Popup modal for viewing/editing an existing report -->
<div id="view-report-modal" style="display:none; position:fixed; top:50%; left:50%; transform: translate(-50%, -50%);
    background:#fff; padding:20px; border:1px solid #000; z-index:9999;">
  <h3>Step Report</h3>
  <textarea id="step-report-view" rows="5" cols="40"></textarea>
  <br>
  <button id="update-report">Update Report</button>
  <button id="close-report">Close</button>
</div>

<style>
.betterme-container {
  --color: #a5a5b0;
  --size: 30px;
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
  cursor: pointer;
  font-size: var(--size);
  user-select: none;
  fill: var(--color);
}
.betterme-container .play {
  position: absolute;
  animation: keyframes-fill .5s;
}
.betterme-container .pause {
  position: absolute;
  display: none;
  animation: keyframes-fill .5s;
}
.betterme-container input:checked ~ .play {
  display: none;
}
.betterme-container input:checked ~ .pause {
  display: block;
}
.betterme-container input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}
@keyframes keyframes-fill {
  0% { transform: rotate(-180deg) scale(0); opacity: 0; }
  50% { transform: rotate(-10deg) scale(1.2); }
}
</style>

<script src="<?php echo plugin_dir_url(__FILE__); ?>lib/timer-script.js"></script>

<script type="text/javascript">
jQuery(document).ready(function($){
  // Handle form submission for adding/updating a step
  $('#step-entry-form').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    $.ajax({
      url: '<?php echo admin_url("admin-ajax.php"); ?>',
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'commart_add_update_step',
        form_data: formData
      },
      success: function(response){
        if(response.success) {
          var step = response.data;
          if($('#step-row-' + step.id).length) {
            var row = $('#step-row-' + step.id);
            row.find('td:eq(0)').text(step.plan_title);
            row.find('td:eq(1)').text(step.title);
            row.find('td:eq(2)').text(step.deadline);
            row.find('td:eq(3)').text(step.elapsed_formatted).attr('data-elapsed', step.elapsed);
          } else {
            var newRow = "<tr id='step-row-" + step.id + "'>" +
              "<td>" + step.plan_title + "</td>" +
              "<td>" + step.title + "</td>" +
              "<td>" + step.deadline + "</td>" +
              "<td class='step-timer' data-elapsed='" + step.elapsed + "' id='timer-" + step.id + "'>" + step.elapsed_formatted + "</td>" +
              "<td>" +
                "<label class='betterme-container'>" +
                  "<input type='checkbox' class='toggle-step' data-id='" + step.id + "'>" +
                  "<svg viewBox='0 0 384 512' height='1em' xmlns='http://www.w3.org/2000/svg' class='play'><path d='M73 39c-14.8-9.1-33.4-9.4-48.5-.9S0 62.6 0 80V432c0 17.4 9.4 33.4 24.5 41.9s33.7 8.1 48.5-.9L361 297c14.3-8.7 23-24.2 23-41s-8.7-32.2-23-41L73 39z'></path></svg>" +
                  "<svg viewBox='0 0 320 512' height='1em' xmlns='http://www.w3.org/2000/svg' class='pause'><path d='M48 64C21.5 64 0 85.5 0 112V400c0 26.5 21.5 48 48 48H80c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48H48zm192 0c-26.5 0-48 21.5-48 48V400c0 26.5 21.5 48 48 48h32c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48H240z'></path></svg>" +
                "</label>" +
              "</td>" +
              "<td>" +
                "<button class='stop-step' data-id='" + step.id + "'>Stop Step</button>" +
              "</td>" +
              "<td>" +
                "<button class='report-step' data-id='" + step.id + "' data-report=''>Report</button>" +
              "</td>" +
              "<td>" +
                "<button class='edit-step' data-id='" + step.id + "' data-plan_id='" + step.plan_id + "' data-title='" + step.title + "' data-deadline='" + step.deadline + "'>Edit</button>" +
              "</td>" +
              "<td>" +
                "<button class='delete-step' data-id='" + step.id + "'>Delete</button>" +
              "</td>" +
            "</tr>";
            $('#steps-table tbody').prepend(newRow);
          }
          $('#step-entry-form')[0].reset();
          $('#step_id').val('');
        } else {
          alert('Error: ' + response.data);
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
         console.error("AJAX error:", textStatus, errorThrown);
         alert('AJAX error occurred. Check console for details.');
      }
    });
  });

  // Handle Edit button click for a step
  $(document).on('click', '.edit-step', function(){
    var id = $(this).data('id');
    $('#step_id').val(id);
    $('#step_plan_id').val($(this).data('plan_id'));
    $('#step_title').val($(this).data('title'));
    $('#step_deadline').val($(this).data('deadline'));
    $('html, body').animate({scrollTop: $("#step-form").offset().top}, 500);
  });

  // Handle Delete button click for a step
  $(document).on('click', '.delete-step', function(){
    if(confirm("Are you sure you want to delete this step?")){
      var id = $(this).data('id');
      $.ajax({
        url: '<?php echo admin_url("admin-ajax.php"); ?>',
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'commart_delete_step',
          step_id: id
        },
        success: function(response){
          if(response.success){
            $('#step-row-' + id).remove();
          } else {
            alert('Error: ' + response.data);
          }
        },
        error: function(jqXHR, textStatus, errorThrown){
          console.error("AJAX error:", textStatus, errorThrown);
          alert('AJAX error occurred. Check console for details.');
        }
      });
    }
  });

  // Handle toggle for starting/pausing a step timer and update container status in the database.
  $(document).on('change', '.toggle-step', function(){
    var stepId = $(this).data('id');
    if ($(this).is(':checked')){
      // Timer is being started; update container_status to "play".
      $.ajax({
        url: '<?php echo admin_url("admin-ajax.php"); ?>',
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'commart_start_step',
          step_id: stepId
        },
        success: function(response){
          if(!response.success){
            alert('Error: ' + response.data);
          }
        },
        error: function(jqXHR, textStatus, errorThrown){
          console.error("AJAX error:", textStatus, errorThrown);
          alert('AJAX error occurred. Check console for details.');
        }
      });
    } else {
      // Timer is being paused; update container_status to "pause".
      $.ajax({
        url: '<?php echo admin_url("admin-ajax.php"); ?>',
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'commart_pause_step',
          step_id: stepId
        },
        success: function(response){
          if(!response.success){
            alert('Error: ' + response.data);
          }
        },
        error: function(jqXHR, textStatus, errorThrown){
          console.error("AJAX error:", textStatus, errorThrown);
          alert('AJAX error occurred. Check console for details.');
        }
      });
    }
  });

  // When "Stop Step" is clicked, show the report popup.
  var currentStopStepId = null;
  $(document).on('click', '.stop-step', function(){
    currentStopStepId = $(this).data('id');
    $('#step-report').val('');
    $('#report-modal').show();
  });

  $('#cancel-report').on('click', function(){
    $('#report-modal').hide();
    currentStopStepId = null;
  });

  // Submit report and stop the step timer
  $('#submit-report').on('click', function(){
    var reportText = $('#step-report').val();
    if (currentStopStepId) {
      $.ajax({
        url: '<?php echo admin_url("admin-ajax.php"); ?>',
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'commart_stop_step',
          step_id: currentStopStepId,
          report: reportText
        },
        success: function(response){
          if(response.success){
            $('#step-row-' + currentStopStepId).find('.step-timer')
              .text(response.data.elapsed_formatted)
              .attr('data-elapsed', response.data.elapsed);
            // Replace the Stop button with a Report button and remove toggle control (if needed)
            $('#step-row-' + currentStopStepId).find('td:eq(6)').html('<button class="report-step" data-id="'+currentStopStepId+'" data-report="'+reportText+'">Report</button>');
            $('#report-modal').hide();
            currentStopStepId = null;
          } else {
            alert('Error: ' + response.data);
          }
        },
        error: function(jqXHR, textStatus, errorThrown){
          console.error("AJAX error:", textStatus, errorThrown);
          alert('AJAX error occurred. Check console for details.');
        }
      });
    }
  });

  // When "Report" button is clicked, show the report view/edit popup.
  var currentReportStepId = null;
  $(document).on('click', '.report-step', function(){
    currentReportStepId = $(this).data('id');
    var currentReport = $(this).data('report') || '';
    $('#step-report-view').val(currentReport);
    $('#view-report-modal').show();
  });
  
  $('#update-report').on('click', function(){
    var updatedReport = $('#step-report-view').val();
    if(currentReportStepId) {
      $.ajax({
        url: '<?php echo admin_url("admin-ajax.php"); ?>',
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'commart_update_step_report',
          step_id: currentReportStepId,
          report: updatedReport
        },
        success: function(response){
          if(response.success){
            $('button.report-step[data-id="'+currentReportStepId+'"]').data('report', updatedReport);
            $('#view-report-modal').hide();
            currentReportStepId = null;
          } else {
            alert('Error: ' + response.data);
          }
        },
        error: function(jqXHR, textStatus, errorThrown){
          console.error("AJAX error:", textStatus, errorThrown);
          alert('AJAX error occurred. Check console for details.');
        }
      });
    }
  });

  $('#close-report').on('click', function(){
    $('#view-report-modal').hide();
    currentReportStepId = null;
  });

  // Update live timers every second
  setInterval(function(){
    $('.step-timer').each(function(){
      var $cell = $(this);
      var elapsed = parseInt($cell.attr('data-elapsed')) || 0;
      var stepId = $cell.closest('tr').attr('id').replace('step-row-', '');
      var $toggle = $('input.toggle-step[data-id="'+stepId+'"]');
      if($toggle.length && $toggle.is(':checked')){
        elapsed++;
        $cell.attr('data-elapsed', elapsed);
        var hrs = Math.floor(elapsed / 3600);
        var mins = Math.floor((elapsed % 3600)/60);
        var secs = elapsed % 60;
        var formatted = ("0" + hrs).slice(-2) + ":" + ("0" + mins).slice(-2) + ":" + ("0" + secs).slice(-2);
        $cell.text(formatted);
      }
    });
  }, 1000);
});
</script>