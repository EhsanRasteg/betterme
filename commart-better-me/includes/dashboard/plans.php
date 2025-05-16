<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$current_user   = wp_get_current_user();
$plans_table    = $wpdb->prefix . 'commart_better_me_plans';
$targets_table  = $wpdb->prefix . 'commart_better_me_targets';

// کوئری برای واکشی پلن‌های کاربر همراه با نام تارگت مربوطه
$query = "SELECT p.*, t.name as target_name 
          FROM $plans_table AS p 
          LEFT JOIN $targets_table AS t ON p.target_id = t.id 
          WHERE p.user_id = %d 
          ORDER BY p.created_at DESC";
$plans = $wpdb->get_results( $wpdb->prepare( $query, $current_user->ID ) );

// واکشی تمام تارگت‌های کاربر جهت استفاده در فیلد چند گزینه‌ای
$targets = $wpdb->get_results( $wpdb->prepare( "SELECT id, name FROM $targets_table WHERE user_id = %d ORDER BY name ASC", $current_user->ID ) );
?>
<div id="plan-list">
  <h2>List of Plans</h2>
  <table id="plans-table" border="1" cellpadding="5" cellspacing="0">
    <thead>
      <tr>
        <th>ID</th>
        <th>Target</th>
        <th>Plan Title</th>
        <th>Budget</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if( $plans ): ?>
        <?php foreach( $plans as $plan ): ?>
          <tr id="plan-row-<?php echo esc_attr( $plan->id ); ?>">
            <td><?php echo esc_html( $plan->id ); ?></td>
            <td><?php echo esc_html( $plan->target_name ); ?></td>
            <td><?php echo esc_html( $plan->description ); ?></td>
            <td><?php echo esc_html( $plan->budget ); ?></td>
            <td>
              <button class="edit-plan"
                data-id="<?php echo esc_attr( $plan->id ); ?>"
                data-target_id="<?php echo esc_attr( $plan->target_id ); ?>"
                data-title="<?php echo esc_attr( $plan->description ); ?>"
                data-budget="<?php echo esc_attr( $plan->budget ); ?>">
                Edit
              </button>
              <button class="delete-plan" data-id="<?php echo esc_attr( $plan->id ); ?>">Delete</button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
          <tr>
            <td colspan="5">No plans found.</td>
          </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div id="plan-form">
  <h2>Add / Edit Plan</h2>
  <form id="plan-entry-form">
    <input type="hidden" name="plan_id" id="plan_id" value="">
    <p>
      <label for="plan_target_id">Target:</label>
      <select name="plan_target_id" id="plan_target_id" required>
        <option value="">Select Target</option>
        <?php foreach( $targets as $target ): ?>
          <option value="<?php echo esc_attr( $target->id ); ?>">
            <?php echo esc_html( $target->name ); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <label for="plan_title">Plan Title:</label>
      <input type="text" name="plan_title" id="plan_title" required>
    </p>
    <p>
      <label for="plan_budget">Budget:</label>
      <input type="number" name="plan_budget" id="plan_budget" step="0.01" required>
    </p>
    <p>
      <button type="submit" id="submit-plan">Submit</button>
      <button type="reset" id="reset-plan-form">Reset</button>
    </p>
  </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($){
  // ارسال فرم برای افزودن/ویرایش پلن
  $('#plan-entry-form').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    $.ajax({
      url: '<?php echo admin_url("admin-ajax.php"); ?>',
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'commart_add_update_plan',
        form_data: formData
      },
      success: function(response){
        if(response.success) {
          var plan = response.data;
          // اگر ردیف موجود است، به‌روزرسانی می‌شود؛ در غیر اینصورت ردیف جدید در ابتدای جدول افزوده می‌شود
          if($('#plan-row-' + plan.id).length) {
            var row = $('#plan-row-' + plan.id);
            row.find('td:eq(1)').text(plan.target_name);
            row.find('td:eq(2)').text(plan.title);
            row.find('td:eq(3)').text(plan.budget);
          } else {
            var newRow = "<tr id='plan-row-" + plan.id + "'>" +
              "<td>" + plan.id + "</td>" +
              "<td>" + plan.target_name + "</td>" +
              "<td>" + plan.title + "</td>" +
              "<td>" + plan.budget + "</td>" +
              "<td>" +
                "<button class='edit-plan' data-id='" + plan.id + "' " +
                  "data-target_id='" + plan.target_id + "' " +
                  "data-title='" + plan.title + "' " +
                  "data-budget='" + plan.budget + "'>Edit</button> " +
                "<button class='delete-plan' data-id='" + plan.id + "'>Delete</button>" +
              "</td>" +
            "</tr>";
            $('#plans-table tbody').prepend(newRow);
          }
          // ریست فرم
          $('#plan-entry-form')[0].reset();
          $('#plan_id').val('');
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

  // رویداد کلیک برای ویرایش پلن
  $(document).on('click', '.edit-plan', function(){
    var id = $(this).data('id');
    $('#plan_id').val(id);
    $('#plan_target_id').val($(this).data('target_id'));
    $('#plan_title').val($(this).data('title'));
    $('#plan_budget').val($(this).data('budget'));
    $('html, body').animate({scrollTop: $("#plan-form").offset().top}, 500);
  });

  // رویداد کلیک برای حذف پلن
  $(document).on('click', '.delete-plan', function(){
    if(confirm("Are you sure you want to delete this plan?")) {
      var id = $(this).data('id');
      $.ajax({
        url: '<?php echo admin_url("admin-ajax.php"); ?>',
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'commart_delete_plan',
          plan_id: id
        },
        success: function(response){
          if(response.success) {
            $('#plan-row-' + id).remove();
          } else {
            alert('Error: ' + response.data);
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
           console.error("AJAX error:", textStatus, errorThrown);
           alert('AJAX error occurred. Check console for details.');
        }
      });
    }
  });
});
</script>