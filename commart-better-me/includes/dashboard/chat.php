<?php
// index.php - قالب اصلی چت اپلیکیشن به زبان PHP با کلاس‌ها و شناسه‌های پیشفرض بهتر-می-چت
$site_url = $_SERVER['HTTP_HOST']; // دریافت آدرس سایت
?>
<!DOCTYPE html>
<html lang="fa">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>چت اپلیکیشن</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="/commart-better-me/includes/css/chatstyle.css">
  
  <script>
    // Make sure the ajaxurl is defined. On the front-end, you might need to localize this variable.
    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
  </script>
  
</head>
<body>
  <!-- نمایش آدرس سایت -->
  <div class="site-url" style="text-align: center; padding: 10px; background: #eee;">
    <?php echo $site_url; ?>
  </div>
  
  <!-- char-area -->
  <section class="better-me-chat-message-area">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <div class="better-me-chat-chat-area">
            <!-- chatlist -->
            <div class="better-me-chat-chatlist">
              <div class="modal-dialog-scrollable better-me-chat-modal-dialog-scrollable">
                <div class="modal-content better-me-chat-modal-content">
                  <div class="better-me-chat-chat-header">
                    <div class="better-me-chat-msg-search">
                      <input type="text" class="form-control" id="inlineFormInputGroup" placeholder="Enter username for chat" aria-label="search">
                      <a class="better-me-chat-add" href="#">
                        <img class="img-fluid" src="https://mehedihtml.com/chatbox/assets/img/add.svg" alt="add">
                      </a>
                    </div>
                    <ul class="nav nav-tabs better-me-chat-nav-tabs" id="myTab" role="tablist">
                      <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="Open-tab" data-bs-toggle="tab" data-bs-target="#Open" type="button" role="tab" aria-controls="Open" aria-selected="true">Open</button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button class="nav-link" id="Closed-tab" data-bs-toggle="tab" data-bs-target="#Closed" type="button" role="tab" aria-controls="Closed" aria-selected="false">Closed</button>
                      </li>
                    </ul>
                  </div>
                  <div class="modal-body">
                    <!-- chat-list -->
                    <div class="better-me-chat-chat-lists">
                      <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="Open" role="tabpanel" aria-labelledby="Open-tab">
                          <!-- Initial chat-list item for Open -->
                          <div class="better-me-chat-chat-list">
                            <a href="#" class="d-flex align-items-center">
                              <div class="flex-shrink-0">
                                <img class="img-fluid" src="https://mehedihtml.com/chatbox/assets/img/user.png" alt="user img">
                                <span class="better-me-chat-active"></span>
                              </div>
                              <div class="flex-grow-1 ms-3">
                                <h3>Mehedi Hasan</h3>
                                <p>front end developer</p>
                              </div>
                            </a>
                          </div>
                        </div>
                        <div class="tab-pane fade" id="Closed" role="tabpanel" aria-labelledby="Closed-tab">
                          <!-- chat-list items for Closed -->
                          <div class="better-me-chat-chat-list">
                            <a href="#" class="d-flex align-items-center">
                              <div class="flex-shrink-0">
                                <img class="img-fluid" src="https://mehedihtml.com/chatbox/assets/img/user.png" alt="user img">
                                <span class="better-me-chat-active"></span>
                              </div>
                              <div class="flex-grow-1 ms-3">
                                <h3>Mehedi Hasan</h3>
                                <p>front end developer</p>
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                    <!-- end chat-list -->
                  </div>
                </div>
              </div>
            </div>
            <!-- end chatlist -->
    
            <!-- chatbox -->
            <div class="better-me-chat-chatbox">
              <div class="modal-dialog-scrollable">
                <div class="modal-content">
                  <div class="better-me-chat-msg-head">
                    <div class="row">
                      <div class="col-8">
                        <div class="d-flex align-items-center">
                          <span class="better-me-chat-chat-icon">
                            <img class="img-fluid" src="https://mehedihtml.com/chatbox/assets/img/arroleftt.svg" alt="image title">
                          </span>
                          <div class="flex-shrink-0">
                            <img class="img-fluid" src="https://mehedihtml.com/chatbox/assets/img/user.png" alt="user img">
                          </div>
                          <div class="flex-grow-1 ms-3">
                            <h3>Mehedi Hasan</h3>
                            <p>front end developer</p>
                          </div>
                        </div>
                      </div>
                      <div class="col-4">
                        <ul class="better-me-chat-moreoption">
                          <li class="navbar nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                              <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu">
                              <li><a class="dropdown-item" href="#">Action</a></li>
                              <li><a class="dropdown-item" href="#">Another action</a></li>
                              <li><hr class="dropdown-divider"></li>
                              <li><a class="dropdown-item" href="#">Something else here</a></li>
                            </ul>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                  <div class="modal-body">
                    <div class="better-me-chat-msg-body">
                      <ul>
                        <li class="sender">
                          <p> Hey, Are you there? </p>
                          <span class="time">10:06 am</span>
                        </li>
                        <li class="sender">
                          <p> Hey, Are you there? </p>
                          <span class="time">10:16 am</span>
                        </li>
                        <li class="repaly">
                          <p>yes!</p>
                          <span class="time">10:20 am</span>
                        </li>
                        <!-- سایر پیام‌ها -->
                        <li>
                          <div class="better-me-chat-divider">
                            <h6>Today</h6>
                          </div>
                        </li>
                        <li class="repaly">
                          <p> yes, tell me</p>
                          <span class="time">10:36 am</span>
                        </li>
                        <li class="repaly">
                          <p>yes... on it</p>
                          <span class="time">just now</span>
                        </li>
                      </ul>
                    </div>
                  </div>
                  <div class="better-me-chat-send-box">
                    <form action="">
                      <input type="text" class="form-control" aria-label="message…" placeholder="Write message…">
                      <button type="button">
                        <i class="fa fa-paper-plane" aria-hidden="true"></i> Send
                      </button>
                    </form>
                    <div class="better-me-chat-send-btns">
                      <div class="better-me-chat-attach">
                        <div class="better-me-chat-button-wrapper">
                          <span class="label">
                            <img class="img-fluid" src="https://mehedihtml.com/chatbox/assets/img/upload.svg" alt="image title"> attached file 
                          </span>
                          <input type="file" name="upload" id="better-me-chat-upload" class="upload-box" placeholder="Upload File" aria-label="Upload File">
                        </div>
                        <select class="form-control" id="exampleFormControlSelect1">
                          <option>Select template</option>
                          <option>Template 1</option>
                          <option>Template 2</option>
                        </select>
                        <div class="better-me-chat-add-apoint">
                          <a href="#" data-toggle="modal" data-target="#exampleModal4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                              <path d="M8 16C3.58862 16 0 12.4114 0 8C0 3.58862 3.58862 0 8 0C12.4114 0 16 3.58862 16 8C16 12.4114 12.4114 16 8 16ZM8 1C4.14001 1 1 4.14001 1 8C1 11.86 4.14001 15 8 15C11.86 15 15 11.86 15 8C15 4.14001 11.86 1 8 1Z" fill="#7D7D7D"/>
                              <path d="M11.5 8.5H4.5C4.224 8.5 4 8.276 4 8C4 7.724 4.224 7.5 4.5 7.5H11.5C11.776 7.5 12 7.724 12 8C12 8.276 11.776 8.5 11.5 8.5Z" fill="#7D7D7D"/>
                              <path d="M8 12C7.724 12 7.5 11.776 7.5 11.5V4.5C7.5 4.224 7.724 4 8 4C8.276 4 8.5 4.224 8.5 4.5V11.5C8.5 11.776 8.276 12 8 12Z" fill="#7D7D7D"/>
                            </svg> Appoinment
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- end chatbox -->
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- end char-area -->
  
  <!-- jQuery and Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
          integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
          crossorigin="anonymous"></script>
  <script>
    jQuery(document).ready(function() {
      // Open chatbox on click of any chat list item
      $(".better-me-chat-chat-list a").click(function() {
        $(".better-me-chat-chatbox").addClass('showbox');
        return false;
      });
      
      // Hide chatbox on click of chat icon
      $(".better-me-chat-chat-icon").click(function() {
        $(".better-me-chat-chatbox").removeClass('showbox');
      });
      
      // When user clicks the "add" button
      $(".better-me-chat-add").on("click", function(e) {
        e.preventDefault();
        var username = $("#inlineFormInputGroup").val().trim();
        if(username === "") {
          alert("Please enter a username");
          return;
        }
        
        // AJAX call to our custom handler in ajax-chat-handler.php
        jQuery.ajax({
          url: ajaxurl,
          type: "POST",
          data: {
            action: "check_user",
            username: username
          },
          success: function(response) {
            if(response.success) {
              var user = response.data;
              // Create a new chat list item using the returned data
              var newItem = `
                <div class="better-me-chat-chat-list">
                  <a href="#" class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                      <img class="img-fluid" src="${user.profile}" alt="${user.username}">
                      <span class="better-me-chat-active"></span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                      <h3>${user.username}</h3>
                      <p>front end developer</p>
                    </div>
                  </a>
                </div>`;
              // Append the new item to the "Open" tab
              $("#Open").append(newItem);
            } else {
              alert(response.data.message);
            }
          },
          error: function(xhr, status, error) {
            alert("AJAX error: " + error);
          }
        });
      });
    });
  </script>
</body>
</html>