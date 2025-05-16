<?php
// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="employer-form-container">
  <!-- Employer list table -->
  <h2>Registered Employers</h2>
  <table id="employer-table" border="1" cellspacing="0" cellpadding="5" style="width:100%; margin-bottom:20px;">
    <thead>
      <tr>
        <th>Personal Information</th>
        <th>Business Information</th>
        <th>Contact</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <!-- Rows will be populated via AJAX -->
    </tbody>
  </table>
  
  <!-- Employer form -->
  <h2 id="form-title">Add Employer</h2>
  <form id="employer-form">
    <!-- Hidden field for update -->
    <input type="hidden" name="record_id" id="record-id" value="">

    <div class="form-group">
      <label for="employer-username">User Name:</label>
      <input type="text" name="employer_username" id="employer-username" required>
      <div id="username-feedback" style="font-size:0.9em;"></div>
      <small>This username is for communication between you and the moderators.</small>
    </div>
    <div class="form-group">
      <label for="employer-name">Employer Name:</label>
      <input type="text" name="employer_name" id="employer-name" required>
    </div>
    <div class="form-group">
      <label for="email">Email:</label>
      <input type="email" name="email" id="email" required>
      <small>Please enter a valid email address.</small>
    </div>
    <div class="form-group">
      <label for="company-name">Company Name:</label>
      <input type="text" name="company_name" id="company-name" required>
    </div>
    <div class="form-group">
      <label for="activity-field">Field of Activity:</label>
      <input type="text" name="activity_field" id="activity-field" required>
    </div>
    <div class="form-group">
      <label for="brands">Brands:</label>
      <input type="text" name="brands" id="brands" placeholder="Brand1, Brand2, Brand3">
      <small>Separate multiple brands with commas.</small>
    </div>
    <div class="form-group">
      <label for="business-mobile">Business Mobile:</label>
      <input type="text" name="business_mobile" id="business-mobile" required>
    </div>
    <div class="form-group">
      <label for="site">Site:</label>
      <input type="url" name="site" id="site">
    </div>
    <button type="submit" id="form-submit-btn">Submit</button>
  </form>
</div>

<!-- Define ajaxurl for frontend AJAX calls -->
<script type="text/javascript">
  var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
</script>

<style>
.employer-form-container {
  width: 100%;
  max-width: 600px;
  margin: 0 auto;
}
.employer-form-container .form-group {
  margin-bottom: 15px;
}
.employer-form-container label {
  display: block;
  font-weight: bold;
  margin-bottom: 5px;
}
.employer-form-container input {
  width: 100%;
  padding: 8px;
  box-sizing: border-box;
}
.employer-form-container small {
  color: #777;
}
</style>

<script type="text/javascript">
jQuery(document).ready(function($){
  // Function to refresh the employer list table.
  function loadEmployers() {
    $.ajax({
      url: ajaxurl,
      method: 'GET',
      dataType: 'json',
      data: { action: 'commart_list_employers' },
      success: function(response) {
        if(response.success) {
          var tbody = '';
          $.each(response.data, function(index, emp){
            // Build cells with the given layout.
            var personal = 'Username: ' + emp.employer_username + '<br>Employer Name: ' + emp.employer_name;
            var business = 'Company Name: ' + emp.company_name + '<br>Field: ' + emp.activity_field;
            if(emp.brands) {
              business += '<br>Brands: ' + emp.brands;
            }
            // Contact column includes Business Mobile, Site and Email.
            var contact = 'Business Mobile: ' + emp.business_mobile + '<br>Site: ' + (emp.site ? emp.site : 'N/A') + '<br>Email: ' + emp.email;
            
            var actions = '<button class="edit-btn" data-id="'+ emp.id +'">Edit</button> ' +
                          '<button class="delete-btn" data-id="'+ emp.id +'">Delete</button>';
            
            tbody += '<tr>' +
                       '<td>' + personal + '</td>' +
                       '<td>' + business + '</td>' +
                       '<td>' + contact + '</td>' +
                       '<td>' + actions + '</td>' +
                     '</tr>';
          });
          $('#employer-table tbody').html(tbody);
        } else {
          $('#employer-table tbody').html('<tr><td colspan="4">No records found.</td></tr>');
        }
      }
    });
  }
  
  // Load employers on page load.
  loadEmployers();
  
  // Username validation on blur event.
  $('#employer-username').on('blur', function(){
    var username = $(this).val().trim();
    if(username.length === 0){
      return;
    }
    $.ajax({
      url: ajaxurl,
      method: 'POST',
      dataType: 'json',
      data: {
        action: 'commart_check_username',
        username: username
      },
      success: function(response) {
        if(response.success) {
          // Username is available.
          $('#employer-username').css('color', 'green');
          $('#username-feedback').html('<span style="color:green;">Approved!</span>');
        } else {
          // Username already exists.
          $('#employer-username').css('color', 'red');
          $('#username-feedback').html('<span style="color:red;">Already available!</span>');
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.error("AJAX error:", textStatus, errorThrown);
      }
    });
  });
  
  // Form submission: if record_id is empty use add endpoint; else use update.
  $('#employer-form').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    var recordId = $('#record-id').val();
    var actionName = recordId ? 'commart_update_employer' : 'commart_add_employer';
    
    $.ajax({
      url: ajaxurl,
      method: 'POST',
      dataType: 'json',
      data: {
        action: actionName,
        form_data: formData
      },
      success: function(response) {
        if(response.success) {
          alert(response.data);
          $('#employer-form')[0].reset();
          $('#record-id').val('');
          $('#form-title').text('Add Employer');
          $('#form-submit-btn').text('Submit');
          $('#employer-username').css('color', '');
          $('#username-feedback').html('');
          // Reload table.
          loadEmployers();
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
  
  // Handle Delete button click.
  $(document).on('click', '.delete-btn', function(){
    if(!confirm("Are you sure you want to delete this record?")){
      return;
    }
    var recId = $(this).data('id');
    $.ajax({
      url: ajaxurl,
      method: 'POST',
      dataType: 'json',
      data: {
        action: 'commart_delete_employer',
        employer_id: recId
      },
      success: function(response) {
        if(response.success) {
          alert(response.data);
          loadEmployers();
        } else {
          alert('Error: ' + response.data);
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.error("AJAX error:", textStatus, errorThrown);
      }
    });
  });
  
  // Handle Edit button click.
  $(document).on('click', '.edit-btn', function(){
    var recId = $(this).data('id');
    // Retrieve the row's data.
    $.ajax({
      url: ajaxurl,
      method: 'POST',
      dataType: 'json',
      data: {
        action: 'commart_get_employer',
        employer_id: recId
      },
      success: function(response) {
        if(response.success) {
          var emp = response.data;
          $('#record-id').val(emp.id);
          $('#employer-username').val(emp.employer_username);
          $('#employer-name').val(emp.employer_name);
          $('#email').val(emp.email);
          $('#company-name').val(emp.company_name);
          $('#activity-field').val(emp.activity_field);
          $('#brands').val(emp.brands);
          $('#business-mobile').val(emp.business_mobile);
          $('#site').val(emp.site);
          
          // Update form title and button text.
          $('#form-title').text('Edit Employer');
          $('#form-submit-btn').text('Update');
        } else {
          alert('Error fetching record: ' + response.data);
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.error("AJAX error:", textStatus, errorThrown);
      }
    });
  });
});
</script>