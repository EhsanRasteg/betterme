<?php
/**
 * فایل index.php
 * یک نمایش‌دهنده دایرکتوری برای لیست کردن فایل‌ها به‌صورت دینامیک.
 * اطمینان حاصل کنید که یک دایرکتوری به نام "files" در کنار این فایل وجود دارد.
 */

// مسیر دایرکتوری فایل‌ها
$directory = __DIR__ . '/files';
$filesData = [];

// خواندن فایل‌ها از دایرکتوری در صورت وجود
if (is_dir($directory)) {
    foreach (scandir($directory) as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        $filepath = $directory . '/' . $file;
        if (is_file($filepath)) {
            $filesData[] = [
                'name'     => $file,
                'modified' => date("d M Y", filemtime($filepath)),
                'size'     => filesize($filepath) > 1024 ? round(filesize($filepath)/1024, 2) . ' KB' : filesize($filepath) . ' B'
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DIR.</title>
  <!-- لینک به فونت آیکن‌ها (Font Awesome) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- لینک به فایل CSS (در صورت جدا بودن) -->
  <style>
    html, body {
      margin: 0;
      font-family: 'Roboto', sans-serif;
      background: #2f3542;
      color: #ffffff;
    }
    
    /* -- Wrapper -- */
    .flex-wrapper {
      display: flex;
      align-items: stretch;
    }
    .v-flex {
      flex-direction: column;
    }
    
    .spacer {
      flex: 1;
    }
    
    /* -- Brand -- */
    #brand {
      flex: 1;
      font-family: monospace;
      font-size: 2.4em;
      letter-spacing: 4px;
      margin: 12px 0 12px 12px;
    }
    
    /* -- Search -- */
    #search {
      flex: 4;
      margin-right: 8px;
    }
    
    #search-bar {
      flex: 2;
      background: #747d8c;
      margin: 8px 0;
      border-radius: 4px;
      transition: all 200ms;
    }
    #search-bar.focused {
      background: #f1f2f6;
    }
    #search-bar button {
      background: none;
      border: none;
      padding: 0;
      outline: none;
      cursor: pointer;
    }
    #search-bar i {
      font-size: 1.4em;
      padding: 4px 12px;
      color: #dfe4ea;
      transition: all 200ms;
    }
    #search-bar.focused i {
      color: #747d8c;
    }
    #search-bar input {
      width: 100%;
      background: none;
      border: none;
      font-family: 'Roboto', sans-serif;
      font-size: 1.2em;
      color: #ffffff;
      outline: none;
    }
    #search-bar input::placeholder {
      color: #f1f2f6;
    }
    #search-bar.focused input {
      color: #2f3542;
    }
    
    #search-bar.focused + .spacer {
      transition: all 200ms;
      flex: 0.001;
    }
    
    /* -- Sidebar -- */
    #sidebar {
      list-style-type: none;
      flex: 1;
      padding: 0;
      margin: 0;
    }
    #sidebar li {
      padding: 10px 10px;
      margin: 0px 0;
      border-top-right-radius: 100px;
      border-bottom-right-radius: 100px;
    }
    #sidebar li:not(.active):hover {
      background: #a4b0be33;
    }
    #sidebar .active {
      background: #70a1ff;
      font-weight: 500;
    }
    #sidebar a {
      text-decoration: none;
      color: inherit;
      display: block;
    }
    #sidebar i {
      font-size: 1.2em;
      vertical-align: middle;
      margin-right: 10px;
    }
    
    .seperator {
      border-color: #a4b0be;
      margin: 8px 4px;
    }
    
    #main {
      flex: 4;
      margin-left: 8px;
    }
    
    /* -- File table -- */
    #file-table {
      width: calc(100% - 8px);
      border-collapse: collapse;
    }
    #file-table tr {
      display: flex;
      padding: 8px;
      align-items: center;
    }
    #file-table tr:not(.table-header):hover {
      background: #a4b0be33;
      cursor: pointer;
    }
    #file-table tr:not(:last-child) {
      border-bottom: 1px solid #a4b0be;
    }
    #file-table th {
      text-align: left;
      font-weight: 500;
    }
    #file-table i {
      font-size: 1.4em;
      vertical-align: middle;
      margin-right: 10px;
    }
    
    .table-name {
      flex: 5;
    }
    .table-modified-at {
      flex: 2;
    }
    .table-size {
      flex: 2;
    }
  </style>
</head>
<body>
  <div class="flex-wrapper v-flex">
    <div class="flex-wrapper">
      <h1 id="brand">DIR.</h1>
      <form action="#" id="search" class="flex-wrapper" method="GET">
        <div id="search-bar" class="flex-wrapper">
          <button type="submit">
            <i class="fa fa-search"></i>
          </button>
          <input type="text" placeholder="Search" name="query">
        </div>
        <div class="spacer"></div>
      </form>
    </div>
    <div class="flex-wrapper">
      <ul id="sidebar">
        <li class="active">
          <a href="#">
            <i class="fas fa-hdd"></i>
            &nbsp;My Files
          </a>
        </li>
        <li>
          <a href="#">
            <i class="fa fa-history"></i>
            &nbsp;Recent
          </a>
        </li>
        <li>
          <a href="#">
            <i class="fa fa-share-alt"></i>
            &nbsp;Shared With Me
          </a>
        </li>
        <li>
          <a href="#">
            <i class="fa fa-trash-alt"></i>
            &nbsp;Trash
          </a>
        </li>
        <hr class="seperator">
        <li>
          <a href="#">
            <i class="fa fa-cog"></i>
            &nbsp;Settings
          </a>
        </li>
        <li>
          <a href="#">
            <i class="fa fa-sign-out-alt"></i>
            &nbsp;Logout
          </a>
        </li>
      </ul>
  
      <div id="main">
        <table id="file-table">
          <tr class="table-header">
            <th class="table-name">Name</th>
            <th class="table-modified-at">Modified at</th>
            <th class="table-size">Size</th>
          </tr>
          <?php if (!empty($filesData)) : ?>
            <?php foreach ($filesData as $file) : ?>
              <tr>
                <td class="table-name">
                  <i class="far fa-file-alt"></i>
                  <?php echo htmlspecialchars($file['name']); ?>
                </td>
                <td class="table-modified-at">
                  <?php echo htmlspecialchars($file['modified']); ?>
                </td>
                <td class="table-size">
                  <?php echo htmlspecialchars($file['size']); ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else : ?>
            <tr>
              <td colspan="3">No files found.</td>
            </tr>
          <?php endif; ?>
        </table>
      </div>
    </div>
  </div>
  
  <!-- افزودن jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    // -- Search bar focus --
    $('#search-bar input').on('focus blur', function() {
      $('#search-bar').toggleClass('focused', $(this).is(':focus'));
    });
    
    // -- Search bar submit --
    $('#search').on('submit', function(e) {
      e.preventDefault();
      $('#search-bar input').blur();
      this.reset();
    });
  </script>
</body>
</html>