<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$current_user = wp_get_current_user();
$table = $wpdb->prefix . 'commart_better_me_targets';
?>
<div id="target-list">
  <h2>List of Targets</h2>
  <table id="targets-table" border="1" cellpadding="5" cellspacing="0">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Description</th>
        <th>Start Date</th>
        <th>Deadline</th>
        <th>Type</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
      // Fetch targets for the current user
      $targets = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC", $current_user->ID ) );
      if ( $targets ) {
          foreach ( $targets as $target ) {
              ?>
              <tr id="target-row-<?php echo esc_attr( $target->id ); ?>">
                <td><?php echo esc_html( $target->id ); ?></td>
                <td><?php echo esc_html( $target->name ); ?></td>
                <td><?php echo esc_html( $target->description ); ?></td>
                <td><?php echo esc_html( $target->start_date ); ?></td>
                <td><?php echo esc_html( $target->deadline ); ?></td>
                <td><?php echo esc_html( $target->type ); ?></td>
                <td>
                  <button class="edit-target" data-id="<?php echo esc_attr( $target->id ); ?>"
                    data-name="<?php echo esc_attr( $target->name ); ?>"
                    data-description="<?php echo esc_attr( $target->description ); ?>"
                    data-start_date="<?php echo esc_attr( $target->start_date ); ?>"
                    data-deadline="<?php echo esc_attr( $target->deadline ); ?>"
                    data-type="<?php echo esc_attr( $target->type ); ?>">Edit</button>
                  <button class="delete-target" data-id="<?php echo esc_attr( $target->id ); ?>">Delete</button>
                </td>
              </tr>
              <?php
          }
      } else {
          ?>
          <tr>
            <td colspan="7">No targets found.</td>
          </tr>
          <?php
      }
      ?>
    </tbody>
  </table>
</div>

<div id="target-form">
  <h2>Add / Edit Target</h2>
  <form id="target-entry-form">
    <input type="hidden" name="target_id" id="target_id" value="">
    <p>
      <label for="target_name">Name:</label>
      <input type="text" name="target_name" id="target_name" required>
    </p>
    <p>
      <label for="target_description">Description:</label>
      <textarea name="target_description" id="target_description" required></textarea>
    </p>
    <p>
      <label for="target_start_date">Start Date:</label>
      <input type="date" name="target_start_date" id="target_start_date" required>
    </p>
    <p>
      <label for="target_deadline">Deadline:</label>
      <input type="date" name="target_deadline" id="target_deadline" required>
    </p>
    <p>
      <label for="target_type">Type:</label>
      <select name="target_type" id="target_type" required>
        <option value="short">Short</option>
        <option value="medium">Medium</option>
        <option value="long">Long</option>
      </select>
    </p>
    <p>
      <button type="submit" id="submit-target">Submit</button>
      <button type="reset" id="reset-target-form">Reset</button>
    </p>
  </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($){
  // Handle form submission for adding/updating target
  $('#target-entry-form').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    $.ajax({
      url: '<?php echo admin_url("admin-ajax.php"); ?>',
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'commart_add_update_target',
        form_data: formData
      },
      success: function(response){
        if(response.success) {
          var target = response.data;
          // If row exists, update it; otherwise, add a new row at the top
          if($('#target-row-' + target.id).length) {
            var row = $('#target-row-' + target.id);
            row.find('td:eq(1)').text(target.name);
            row.find('td:eq(2)').text(target.description);
            row.find('td:eq(3)').text(target.start_date);
            row.find('td:eq(4)').text(target.deadline);
            row.find('td:eq(5)').text(target.type);
          } else {
            var newRow = "<tr id='target-row-" + target.id + "'>" +
              "<td>" + target.id + "</td>" +
              "<td>" + target.name + "</td>" +
              "<td>" + target.description + "</td>" +
              "<td>" + target.start_date + "</td>" +
              "<td>" + target.deadline + "</td>" +
              "<td>" + target.type + "</td>" +
              "<td>" +
                "<button class='edit-target' data-id='" + target.id + "' " +
                  "data-name='" + target.name + "' " +
                  "data-description='" + target.description + "' " +
                  "data-start_date='" + target.start_date + "' " +
                  "data-deadline='" + target.deadline + "' " +
                  "data-type='" + target.type + "'>Edit</button> " +
                "<button class='delete-target' data-id='" + target.id + "'>Delete</button>" +
              "</td>" +
            "</tr>";
            $('#targets-table tbody').prepend(newRow);
          }
          // Reset the form
          $('#target-entry-form')[0].reset();
          $('#target_id').val('');
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

  // Handle Edit button click
  $(document).on('click', '.edit-target', function(){
    var id = $(this).data('id');
    $('#target_id').val(id);
    $('#target_name').val($(this).data('name'));
    $('#target_description').val($(this).data('description'));
    $('#target_start_date').val($(this).data('start_date'));
    $('#target_deadline').val($(this).data('deadline'));
    $('#target_type').val($(this).data('type'));
    // Scroll to the form
    $('html, body').animate({scrollTop: $("#target-form").offset().top}, 500);
  });

  // Handle Delete button click
  $(document).on('click', '.delete-target', function(){
    if(confirm("Are you sure you want to delete this target?")) {
      var id = $(this).data('id');
      $.ajax({
        url: '<?php echo admin_url("admin-ajax.php"); ?>',
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'commart_delete_target',
          target_id: id
        },
        success: function(response){
          if(response.success) {
            $('#target-row-' + id).remove();
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