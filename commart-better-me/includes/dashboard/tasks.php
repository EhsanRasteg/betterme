<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="tasks-container">
  <h2>Tasks</h2>
  
  <!-- Task Form -->
  <form id="task-form">
    <!-- Hidden field for update -->
    <input type="hidden" name="task_id" id="task-id" value="">
    
    <div class="form-group">
      <label for="task-title">Task Title:</label>
      <input type="text" name="tasks_title" id="task-title" required>
    </div>
    
    <!-- Project selection as a searchable dropdown -->
    <div class="form-group">
      <label for="project">Project:</label>
      <select name="projects_id" id="project" required>
        <option value="">Select project...</option>
      </select>
    </div>
    
    <div class="form-group">
      <label for="tasks-deadline">Deadline:</label>
      <input type="date" name="tasks_deadline" id="tasks-deadline" required>
    </div>
    
    <div class="form-group">
      <label for="tasks-status">Status:</label>
      <select name="tasks_status" id="tasks-status" required>
        <option value="pending">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
      </select>
    </div>
    
    <div class="form-group">
      <label for="tasks-report">Report:</label>
      <textarea name="tasks_report" id="tasks-report"></textarea>
    </div>
    
    <button type="submit" id="task-submit-btn">Submit</button>
  </form>
  
  <!-- Tasks List -->
  <h2>Tasks List</h2>
  <table id="tasks-table" border="1" cellspacing="0" cellpadding="5" style="width:100%; margin-top:20px;">
    <thead>
      <tr>
        <th>Task Title</th>
        <th>Project</th>
        <th>Deadline</th>
        <th>Status</th>
        <th>Report</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <!-- Tasks will be populated here via AJAX -->
    </tbody>
  </table>
</div>

<script type="text/javascript">
jQuery(document).ready(function($){
  // همانطور که در بخش پروژه اعمال شده، ajaxurl رو مستقیماً تعریف می‌کنیم.
  var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
  
  // بارگذاری گزینه‌های پروژه برای فرم تسک.
  function loadProjectOptions(){
    $.ajax({
      url: ajaxurl,
      method: 'GET',
      dataType: 'json',
      data: { action: 'commart_get_projects' },
      success: function(response){
        if(response.success){
          var options = '<option value="">Select project...</option>';
          $.each(response.data, function(index, project){
            options += '<option value="'+ project.id +'">'+ project.projects_title +'</option>';
          });
          $('#project').html(options);
        } else {
          $('#project').html('<option value="">No projects available</option>');
        }
      }
    });
  }
  
  loadProjectOptions();
  
  // بارگذاری لیست تسک‌ها
  function loadTasks(){
    $.ajax({
      url: ajaxurl,
      method: 'GET',
      dataType: 'json',
      data: { action: 'commart_list_tasks' },
      success: function(response){
        if(response.success){
          var tbody = '';
          $.each(response.data, function(index, task){
            tbody += '<tr>';
            tbody += '<td>' + task.tasks_title + '</td>';
            tbody += '<td>' + (task.projects_title ? task.projects_title : 'N/A') + '</td>';
            tbody += '<td>' + task.tasks_deadline + '</td>';
            tbody += '<td>' + task.tasks_status.replace('_', ' ') + '</td>';
            tbody += '<td>' + (task.tasks_report ? task.tasks_report : '') + '</td>';
            tbody += '<td>' +
                        '<button class="edit-task-btn" data-id="'+ task.id +'">Edit</button> ' +
                        '<button class="delete-task-btn" data-id="'+ task.id +'">Delete</button>' +
                     '</td>';
            tbody += '</tr>';
          });
          $('#tasks-table tbody').html(tbody);
        } else {
          $('#tasks-table tbody').html('<tr><td colspan="6">No tasks found.</td></tr>');
        }
      }
    });
  }
  
  loadTasks();
  
  // هندل کردن ارسال فرم برای افزودن/بروزرسانی تسک
  $('#task-form').on('submit', function(e){
    e.preventDefault();
    var formData = $(this).serialize();
    var taskId = $('#task-id').val();
    var actionName = taskId ? 'commart_update_task' : 'commart_add_task';
    
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
          $('#task-form')[0].reset();
          $('#task-id').val('');
          $('#task-submit-btn').text('Submit');
          loadTasks();
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
  
  // هندل کردن دکمه حذف تسک
  $(document).on('click', '.delete-task-btn', function(){
    if(!confirm("Are you sure you want to delete this task?")){
      return;
    }
    var taskId = $(this).data('id');
    $.ajax({
      url: ajaxurl,
      method: 'POST',
      dataType: 'json',
      data: {
        action: 'commart_delete_task',
        task_id: taskId
      },
      success: function(response){
        if(response.success){
          alert(response.data);
          loadTasks();
        } else {
          alert('Error: ' + response.data);
        }
      },
      error: function(jqXHR, textStatus, errorThrown){
        console.error("AJAX error:", textStatus, errorThrown);
      }
    });
  });
  
  // هندل کردن دکمه ویرایش تسک
  $(document).on('click', '.edit-task-btn', function(){
    var taskId = $(this).data('id');
    $.ajax({
      url: ajaxurl,
      method: 'POST',
      dataType: 'json',
      data: {
        action: 'commart_get_task',
        task_id: taskId
      },
      success: function(response){
        if(response.success){
          var task = response.data;
          $('#task-title').val(task.tasks_title);
          $('#project').val(task.projects_id);
          $('#tasks-deadline').val(task.tasks_deadline);
          $('#tasks-status').val(task.tasks_status);
          $('#tasks-report').val(task.tasks_report);
          $('#task-id').val(task.id);
          $('#task-submit-btn').text('Update');
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