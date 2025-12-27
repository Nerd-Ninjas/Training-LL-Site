<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iframe Resize Example</title>
  <style>
    .iframe-container {
      width: 100%;
      border: none;
    }
  </style>
</head>
<body>

<iframe id="dynamic-iframe" class="iframe-container" src="pages/startQuiz.php" scrolling="no"></iframe>
<script>
        // Function to detect the mode from the parent document
        function getMode() {
            return document.body.classList.contains('dark-mode') ? 'dark-mode' : 'light-mode';
        }

        // Function to send the mode to the iframe
        function sendModeToIframe() {
            const iframe = document.getElementById('myIframe');
            const mode = getMode();
            iframe.contentWindow.postMessage({ mode: mode }, '*');
        }

        // Send the mode to the iframe once it's loaded
        document.getElementById('myIframe').onload = sendModeToIframe;
    </script>

<script>
  function resizeIframe() {
    const iframe = document.getElementById('dynamic-iframe');
    iframe.style.height = iframe.contentWindow.document.body.scrollHeight + 100 + 'px';
  }

  document.getElementById('dynamic-iframe').onload = function() {
    resizeIframe();
    const observer = new MutationObserver(resizeIframe);
    observer.observe(document.getElementById('dynamic-iframe').contentDocument.body, {
      childList: true,
      subtree: true,
      attributes: true,
      characterData: true
    });
  };
</script>

</body>
</html>
