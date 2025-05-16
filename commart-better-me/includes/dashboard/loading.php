<?php
// Prevent direct file access if needed.
if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Loading Popup</title>
  <style>
    /* Overlay that covers full page with black background */
    #loading-better-me {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: black;
      z-index: 9999; /* Ensures it's on top of all other content */
    }

    /* Container to center the loader horizontally and vertically */
    .loader-container {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 15%; /* loader container takes 15% width of the viewport */
      transform: translate(-50%, -50%);
    }

    /* Provided loader CSS */
    .loader {
      width: 80px;
      height: 50px;
      position: relative;
    }

    .loader-text {
      position: absolute;
      top: 0;
      padding: 0;
      margin: 0;
      color: #C8B6FF;
      animation: text_713 3.5s ease both infinite;
      font-size: 0.8rem;
      letter-spacing: 1px;
    }

    .load {
      background-color: #9A79FF;
      border-radius: 50px;
      display: block;
      height: 16px;
      width: 16px;
      bottom: 0;
      position: absolute;
      transform: translateX(64px);
      animation: loading_713 3.5s ease both infinite;
    }

    .load::before {
      position: absolute;
      content: "";
      width: 100%;
      height: 100%;
      background-color: #D1C2FF;
      border-radius: inherit;
      animation: loading2_713 3.5s ease both infinite;
    }

    @keyframes text_713 {
      0% {
        letter-spacing: 1px;
        transform: translateX(0px);
      }
      40% {
        letter-spacing: 2px;
        transform: translateX(26px);
      }
      80% {
        letter-spacing: 1px;
        transform: translateX(32px);
      }
      90% {
        letter-spacing: 2px;
        transform: translateX(0px);
      }
      100% {
        letter-spacing: 1px;
        transform: translateX(0px);
      }
    }

    @keyframes loading_713 {
      0% {
        width: 16px;
        transform: translateX(0px);
      }
      40% {
        width: 100%;
        transform: translateX(0px);
      }
      80% {
        width: 16px;
        transform: translateX(64px);
      }
      90% {
        width: 100%;
        transform: translateX(0px);
      }
      100% {
        width: 16px;
        transform: translateX(0px);
      }
    }

    @keyframes loading2_713 {
      0% {
        transform: translateX(0px);
        width: 16px;
      }
      40% {
        transform: translateX(0%);
        width: 80%;
      }
      80% {
        width: 100%;
        transform: translateX(0px);
      }
      90% {
        width: 80%;
        transform: translateX(15px);
      }
      100% {
        transform: translateX(0px);
        width: 16px;
      }
    }
  </style>
</head>
<body>
  <div id="loading-better-me">
    <div class="loader-container">
      <div class="loader">
        <span class="loader-text">loading</span>
        <span class="load"></span>
      </div>
    </div>
  </div>
</body>
</html>