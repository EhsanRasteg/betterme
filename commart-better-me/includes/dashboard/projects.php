<?php
// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="projects-container">
  <h2>Projects</h2>
  
  <!-- Project Form -->
  <form id="project-form">
    <!-- Hidden field for update -->
    <input type="hidden" name="project_id" id="project-id" value="">
    
    <div class="form-group">
      <label for="project-title">Project Title:</label>
      <!-- Changed name from project_title to projects_title to match DB column -->
      <input type="text" name="projects_title" id="project-title" required>
    </div>
    
    <!-- Brand selection as a searchable dropdown -->
    <div class="form-group">
      <label for="brand">Brand:</label>
      <select name="brand" id="brand" required>
        <option value="">Select brand...</option>
      </select>
    </div>
    
    <div class="form-group">
      <label for="start-date">Start Date:</label>
      <input type="date" name="start_date" id="start-date" required>
    </div>
    <div class="form-group">
      <label for="deadline">Deadline:</label>
      <input type="date" name="deadline" id="deadline" required>
    </div>
    <div class="form-group">
      <label for="status">Status:</label>
      <select name="status" id="status" required>
        <option value="in_progress">In progress</option>
        <option value="stopped">Stopped</option>
        <option value="done">Done</option>
      </select>
    </div>
    <div class="form-group">
      <label for="description">Description:</label>
      <textarea name="description" id="description" required></textarea>
    </div>
    <div class="form-group">
      <label for="project-amount">Project Amount:</label>
      <input type="number" step="0.01" name="project_amount" id="project-amount" required>
    </div>
    <button type="submit" id="project-submit-btn">Submit</button>
  </form>
  
  <!-- Projects List -->
  <h2>Projects List</h2>
  <table id="projects-table" border="1" cellspacing="0" cellpadding="5" style="width:100%; margin-top:20px;">
    <thead>
      <tr>
        <th>Project</th>
        <th>Brand</th>
        <th>Start Date</th>
        <th>Deadline</th>
        <th>Status</th>
        <th>Invoice</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <!-- Projects will be populated here via AJAX -->
    </tbody>
  </table>
</div>

<script type="text/javascript">
jQuery(document).ready(function($){
  // Function to load employer profile and populate brand select.
  function loadBrandOptions(){
    $.ajax({
      url: ajaxurl,
      method: 'GET',
      dataType: 'json',
      data: { action: 'commart_get_my_employer' },
      success: function(response){
        if(response.success){
          var emp = response.data;
          // Assuming the brands field is a comma separated list.
          if(emp.brands){
            var brands = emp.brands.split(',');
            // Trim and add each brand as option.
            $('#brand').html('<option value="">Select brand...</option>');
            brands.forEach(function(brand){
              brand = brand.trim();
              if(brand){
                $('#brand').append('<option value="'+brand+'">'+brand+'</option>');
              }
            });
          }
        } else {
          // No employer set; clear brand options.
          $('#brand').html('<option value="">No employer set</option>');
        }
      }
    });
  }
  
  // Call loadBrandOptions on page load.
  loadBrandOptions();
  
  // Optionally, you could add a keyup handler for searching within the select.
  // For a more robust searchable dropdown, consider integrating a library like Select2.
  
  // Function to load projects
  function loadProjects(){
    $.ajax({
      url: ajaxurl,
      method: 'GET',
      dataType: 'json',
      data: { action: 'commart_list_projects' },
      success: function(response){
        if(response.success){
          var tbody = '';
          $.each(response.data, function(index, project){
            tbody += '<tr>';
            // Use projects_title as column name
            tbody += '<td>' + project.projects_title + '</td>';
            tbody += '<td>' + project.brand + '</td>';
            tbody += '<td>' + project.start_date + '</td>';
            tbody += '<td>' + project.deadline + '</td>';
            tbody += '<td>' + project.status.replace('_', ' ') + '</td>';
            tbody += '<td><button class="invoice-btn" data-id="'+ project.id +'">Invoice</button></td>';
            tbody += '<td>' +
                        '<button class="edit-btn" data-id="'+ project.id +'">Edit</button> ' +
                        '<button class="delete-btn" data-id="'+ project.id +'">Delete</button>' +
                     '</td>';
            tbody += '</tr>';
          });
          $('#projects-table tbody').html(tbody);
        } else {
          $('#projects-table tbody').html('<tr><td colspan="7">No projects found.</td></tr>');
        }
      }
    });
  }

  loadProjects();

  // Handle form submission for adding/updating project.
  $('#project-form').on('submit', function(e){
    e.preventDefault();
    var formData = $(this).serialize();
    var projectId = $('#project-id').val();
    var actionName = projectId ? 'commart_update_project' : 'commart_add_project';

    $.ajax({
      url: ajaxurl,
      method: 'POST',
      dataType: 'json',
      data: {
        action: actionName,
        form_data: formData
      },
      success: function(response){
        if(response.success){
          alert(response.data);
          $('#project-form')[0].reset();
          $('#project-id').val('');
          $('#project-submit-btn').text('Submit');
          loadProjects();
        } else {
          alert('Error: ' + response.data);
        }
      },
      error: function(jqXHR, textStatus, errorThrown){
        console.error("AJAX error:", textStatus, errorThrown);
        alert('AJAX error occurred.');
      }
    });
  });

  // Handle Delete button click.
  $(document).on('click', '.delete-btn', function(){
    if(!confirm("Are you sure you want to delete this project?")){
      return;
    }
    var projectId = $(this).data('id');
    $.ajax({
      url: ajaxurl,
      method: 'POST',
      dataType: 'json',
      data: {
        action: 'commart_delete_project',
        project_id: projectId
      },
      success: function(response){
        if(response.success){
          alert(response.data);
          loadProjects();
        } else {
          alert('Error: ' + response.data);
        }
      },
      error: function(jqXHR, textStatus, errorThrown){
        console.error("AJAX error:", textStatus, errorThrown);
      }
    });
  });

  // Handle Edit button click.
  $(document).on('click', '.edit-btn', function(){
    var projectId = $(this).data('id');
    // For editing, assume that the row values could be extracted via a separate AJAX call.
    // Here we'll demonstrate a simple approach.
    var row = $(this).closest('tr');
    // Set project title (projects_title) from table cell.
    $('#project-title').val( row.find('td:eq(0)').text() );
    // Set brand select value.
    var currentBrand = row.find('td:eq(1)').text();
    $('#brand').val(currentBrand);
    $('#start-date').val( row.find('td:eq(2)').text() );
    $('#deadline').val( row.find('td:eq(3)').text() );
    var statusText = row.find('td:eq(4)').text().toLowerCase().replace(' ', '_');
    $('#status').val( statusText );
    // Description and project amount are not shown in the table.
    $('#project-id').val(projectId);
    $('#project-submit-btn').text('Update');
  });

  // Handle Invoice button click.
  $(document).on('click', '.invoice-btn', function(){
    var projectId = $(this).data('id');
    $.ajax({
      url: ajaxurl,
      method: 'POST',
      dataType: 'json',
      data: {
        action: 'commart_send_invoice',
        project_id: projectId
      },
      success: function(response){
        if(response.success){
          alert(response.data);
        } else {
          alert('Error: ' + response.data);
        }
      }
    });
  });
});
</script>