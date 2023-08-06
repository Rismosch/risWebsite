<div id="entire-content" style="display: none">
    <div id="unity-container" class="unity-desktop">
        <div id="unity-warning"> </div>
        <canvas id="unity-canvas" style="width:100%;"></canvas>
        <div id="unity-loading-bar">
            <div id="unity-logo"></div>
            <div id="unity-progress-bar-empty">
                <div id="unity-progress-bar-full"></div>
            </div>
        </div>
        <div id="unity-footer">
            <div id="unity-webgl-logo"></div>
            <div id="unity-fullscreen-button"></div>
            <!--<div id="unity-build-title">Quaternion Playground</div>-->
        </div>
    </div>

    <br>
    <br>

    <h2>Controls</h2>

    <ul>
    <li>rotate camera with right mouse button</li>
    <li>move point with left bouse button</li>
    <li>change quaternion value by dragging with left mouse button</li>
    <li>change quaternion value with mouse scrollwheel</li>
    <li>while changing quaternion value, press Ctrl to make finer adjustments</li>
    <li>set quaterion value to 0 by right clicking it</li>
    </ul>
    
    <h2>Links</h2>
    <ul>
    <li>GitHub: <a href="https://github.com/Rismosch/quaternion-playground" target="_blank" rel="noopener noreferrer">https://github.com/Rismosch/quaternion-playground</a></li>
    <li>itch.io: <a href="https://rismosch.itch.io/quaternion-playground" target="_blank" rel="noopener noreferrer">https://rismosch.itch.io/quaternion-playground</a></li>
    </ul>
</div>

<noscript>
<p style="color: var(--pico-8-red)">Error &#10007;</p>
<p>JavaScript is disabled :(</p>
</noscript>

<script>
    document.getElementById("entire-content").style.display = "block";

    var container = document.querySelector("#unity-container");
    var canvas = document.querySelector("#unity-canvas");
    var loadingBar = document.querySelector("#unity-loading-bar");
    var progressBarFull = document.querySelector("#unity-progress-bar-full");
    var fullscreenButton = document.querySelector("#unity-fullscreen-button");
    var warningBanner = document.querySelector("#unity-warning");
    
    // custom resize
    function resizeUnityWindow() {
        canvas.style.width = "100%";
        const canvaxBoundingRect = canvas.getBoundingClientRect();
        console.log("test " +  canvaxBoundingRect.width + "x" + canvaxBoundingRect.height);
        const newWidth = canvaxBoundingRect.width - 20;
        const newHeight = newWidth * 10 / 16;
        canvas.style.width = newWidth + "px";
        canvas.style.height = newHeight + "px";
        console.log("resized: " + canvas.width + "x" + canvas.height + " style: " + canvas.style.width + "x" + canvas.style.height);
    }

    onresize = resizeUnityWindow;
    resizeUnityWindow();

    // Shows a temporary message banner/ribbon for a few seconds, or
    // a permanent error message on top of the canvas if type=='error'.
    // If type=='warning', a yellow highlight color is used.
    // Modify or remove this function to customize the visually presented
    // way that non-critical warnings and error messages are presented to the
    // user.
    function unityShowBanner(msg, type) {
    function updateBannerVisibility() {
        warningBanner.style.display = warningBanner.children.length ? 'block' : 'none';
    }
    var div = document.createElement('div');
    div.innerHTML = msg;
    warningBanner.appendChild(div);
    if (type == 'error') div.style = 'background: red; padding: 10px;';
    else {
        if (type == 'warning') div.style = 'background: yellow; padding: 10px;';
        setTimeout(function() {
        warningBanner.removeChild(div);
        updateBannerVisibility();
        }, 5000);
    }
    updateBannerVisibility();
    }

    var buildUrl = "<?php echo get_source("Build"); ?>";
    var loaderUrl = buildUrl + "/build.loader.js";
    var config = {
    dataUrl: buildUrl + "/build.data",
    frameworkUrl: buildUrl + "/build.framework.js",
    codeUrl: buildUrl + "/build.wasm",
    streamingAssetsUrl: "StreamingAssets",
    companyName: "Rismosch",
    productName: "Quaternion Playground",
    productVersion: "1.0",
    showBanner: unityShowBanner,
    };

    // By default Unity keeps WebGL canvas render target size matched with
    // the DOM size of the canvas element (scaled by window.devicePixelRatio)
    // Set this to false if you want to decouple this synchronization from
    // happening inside the engine, and you would instead like to size up
    // the canvas DOM size and WebGL render target sizes yourself.
    // config.matchWebGLToCanvasSize = false;

    if (/iPhone|iPad|iPod|Android/i.test(navigator.userAgent)) {
    // Mobile device style: fill the whole browser client area with the game canvas:

    var meta = document.createElement('meta');
    meta.name = 'viewport';
    meta.content = 'width=device-width, height=device-height, initial-scale=1.0, user-scalable=no, shrink-to-fit=yes';
    document.getElementsByTagName('head')[0].appendChild(meta);
    container.className = "unity-mobile";
    canvas.className = "unity-mobile";

    // To lower canvas resolution on mobile devices to gain some
    // performance, uncomment the following line:
    // config.devicePixelRatio = 1;

    unityShowBanner('WebGL builds are not supported on mobile devices.');
    } else {
    // Desktop style: Render the game canvas in a window that can be maximized to fullscreen:

    // canvas.style.width = "960px";
    // canvas.style.height = "600px";
    }

    loadingBar.style.display = "block";

    var script = document.createElement("script");
    script.src = loaderUrl;
    script.onload = () => {
    createUnityInstance(canvas, config, (progress) => {
        progressBarFull.style.width = 100 * progress + "%";
    }).then((unityInstance) => {
        loadingBar.style.display = "none";
        fullscreenButton.onclick = () => {
        unityInstance.SetFullscreen(1);
        };
    }).catch((message) => {
        alert(message);
    });
    };
    document.body.appendChild(script);
</script>