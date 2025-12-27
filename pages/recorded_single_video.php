<?php
require_once("../includes/config.php");
require_once("../includes/classes/Account.php");

$account = new Account($con);
$id = $_GET['id'];

// Validate video ID
if (!$id || !preg_match('/^[a-zA-Z0-9_-]+$/', $id)) {
    die("Invalid video ID.");
}

$getVideoDetails = $account->getRecordedVideo($id);
if (!$getVideoDetails || empty($getVideoDetails)) {
    die("Error: No video details found for the given ID.");
}

$videoUrl = isset($getVideoDetails['video_url']) ? $getVideoDetails['video_url'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cybervault - Learnlike Video Player</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
        text-align: center;
    }
    .logo-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 30px;
    }
    .logo-left {
        width: 140px;
    }
    .logo-right {
        width: 160px;
    }
    .container {
        max-width: 800px;
        margin: 15px auto;
        background: white;
        padding: 15px;
        border-radius: 6px;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
    }
    .video-container {
        margin-top: 15px;
    }
    video {
        width: 100%;
        height: auto;
        border-radius: 6px;
        background: #000;
    }
    .session-info {
        font-size: 14px;
        font-weight: bold;
        margin: 8px 0;
    }
    .summary-container {
        max-height: 180px;
        overflow-y: auto;
        background: #fafafa;
        padding: 8px;
        border-radius: 5px;
        border: 1px solid #ddd;
        text-align: left;
        font-size: 13px;
        line-height: 1.4;
    }
    .summary-container h1, 
    .summary-container h2, 
    .summary-container p {
        margin: 8px 0;
        font-size: 14px;
    }
    .summary-container ul, 
    .summary-container ol {
        padding-left: 18px;
        font-size: 13px;
    }
    footer {
        margin-top: 15px;
        padding: 8px 0;
        text-align: center;
        font-size: 11px;
        color: #666;
    }
    .footer-line {
        width: 100%;
        height: 1px;
        background: #d3d3d3;
        margin-bottom: 6px;
    }
</style>
</head>
<body>
    <div class="logo-container">
        <img src="../assets/images/brand/logo-big.png" class="logo-left" alt="Left Logo">
        <img src="../assets/images/brand/CV_Logo-cropped.svg" class="logo-right" alt="Right Logo">
    </div>
    
    <div class="container">
        <div class="video-container">
            <h2><?php echo htmlspecialchars($getVideoDetails['video_description'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($getVideoDetails['date_of_session'], ENT_QUOTES, 'UTF-8'); ?></p>
            <video id="video" controls data-src="<?php echo htmlspecialchars($videoUrl, ENT_QUOTES, 'UTF-8'); ?>">
                Your browser does not support video playback.
            </video>
        </div>
        
        <div class="session-info">
            <p><strong>Session Handled by:</strong> <?php echo htmlspecialchars($getVideoDetails['session_handled_by'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        
        <div class="summary-container">
            <?php echo $getVideoDetails['video_summary']; ?>
        </div>
    </div>
    
<footer>
    <div class="footer-line"></div>
    <p>&copy; <?php echo date('Y'); ?> - Cybervault & Learnlike. All Rights Reserved.</p>
    <p>Unauthorized distribution or publishing of this video is strictly prohibited.</p>
</footer>    
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <script>
        function initializeVideoPlayer(videoElement, videoSrc) {
            if (!videoElement || !videoSrc) {
                console.error("Error: Video element or source is missing.");
                return;
            }

            console.log("Initializing Video Player with source:", videoSrc);

            if (Hls.isSupported() && videoSrc.endsWith(".m3u8")) {
                console.log("HLS.js is supported");
                const hls = new Hls();
                hls.loadSource(videoSrc);
                hls.attachMedia(videoElement);
                hls.on(Hls.Events.MANIFEST_PARSED, function () {
                    console.log("HLS Manifest Loaded");
                    videoElement.play();
                });
                hls.on(Hls.Events.ERROR, function (event, data) {
                    console.error("HLS Error:", data);
                });
            } else if (videoElement.canPlayType("application/vnd.apple.mpegurl")) {
                console.log("Native HLS supported");
                videoElement.src = videoSrc;
                videoElement.addEventListener("loadedmetadata", function () {
                    videoElement.play();
                });
            } else {
                console.error("Your browser does not support HLS playback.");
                alert("Your browser does not support HLS playback.");
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            let videoElement = document.getElementById("video");
            let videoSrc = videoElement.getAttribute("data-src");

            if (videoElement && videoSrc) {
                initializeVideoPlayer(videoElement, videoSrc);
            }
        });
    </script>
</body>
</html>
