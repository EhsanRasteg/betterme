<?php
// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="my-employer-container">
  <h2>My Employer</h2>
  <form id="my-employer-form">
    <div class="form-group">
      <label for="employer-username">Employer Username:</label>
      <input type="text" name="employer_username" id="employer-username" required>
      <small>Enter the employer username you registered in the Add Employer section.</small>
    </div>
    <button type="submit">Set My Employer</button>
  </form>
  
  <div id="employer-info" style="margin-top:20px;">
    <!-- Employer information for the current user will appear here -->
  </div>
</div>

<!-- Define ajaxurl for frontend AJAX calls -->
<script type="text/javascript">
  var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
</script>

<style>
.my-employer-container {
  width: 100%;
  max-width: 600px;
  margin: 0 auto;
}
.my-employer-container .form-group {
  margin-bottom: 15px;
}
.my-employer-container label {
  display: block;
  font-weight: bold;
  margin-bottom: 5px;
}
.my-employer-container input {
  width: 100%;
  padding: 8px;
  box-sizing: border-box;
}
.my-employer-container small {
  color: #777;
}
</style>

<script type="text/javascript">
jQuery(document).ready(function($){
  // Function to load the current user's employer information from the employers table.
  function loadMyEmployer(){
    $.ajax({
      url: ajaxurl,
      method: 'GET',
      dataType: 'json',
      data: { action: 'commart_get_my_employer' },
      success: function(response){
        if(response.success){
          var emp = response.data;
          var html = '<table border="1" cellspacing="0" cellpadding="5" style="width:100%;">';
          html += '<tr><th>Employer Username</th><td>' + emp.employer_username + '</td></tr>';
          html += '<tr><th>Employer Name</th><td>' + emp.employer_name + '</td></tr>';
          html += '<tr><th>Email</th><td>' + emp.email + '</td></tr>';
          html += '<tr><th>Company Name</th><td>' + emp.company_name + '</td></tr>';
          html += '<tr><th>Field of Activity</th><td>' + emp.activity_field + '</td></tr>';
          html += '<tr><th>Brands</th><td>' + (emp.brands ? emp.brands : 'N/A') + '</td></tr>';
          html += '<tr><th>Business Mobile</th><td>' + emp.phone_number + '</td></tr>';
          html += '<tr><th>Site</th><td>' + (emp.site ? emp.site : 'N/A') + '</td></tr>';
          html += '</table>';
          $('#employer-info').html(html);
        } else {
          $('#employer-info').html('<p>No employer set yet.</p>');
        }
      }
    });
  }
  
  // Load the employer info when the page loads.
  loadMyEmployer();
  
  // Handle form submission to set "My Employer".
  $('#my-employer-form').on('submit', function(e){
    e.preventDefault();
    var formData = $(this).serialize();
    $.ajax({
      url: ajaxurl,
      method: 'POST',
      dataType: 'json',
      data: {
        action: 'commart_set_my_employer',
        form_data: formData
      },
      success: function(response){
        if(response.success){
          alert(response.data);
          loadMyEmployer();
        } else {
          alert('Error: ' + response.data);
        }
      },
      error: function(jqXHR, textStatus, errorThrown){
        console.error("AJAX error:", textStatus, errorThrown);
      }
    });
  });
});
</script>