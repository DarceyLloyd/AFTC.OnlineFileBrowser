function log(str) {
    if (console) { console.log(str); }
}

window.isMobile = function () {
    // Windows Phone must come first because its UA also contains "Android"!
    var ua = navigator.userAgent.toLowerCase();
    if (/windows phone/i.test(ua)) {
        return true;
    } else {
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            return true;
        } else {
            return false;
        }
    }
}


var AFTCFileBrowser = function (imageMode,animateBg,OpenFilesInNewTab) {

    if (isMobile()) {
        // No funky backgrounds for mobile, save the load on cpu/gpu
        return;
    }

    var params = {
        canvas1: null,
        ctx1: null,
        w: 0,
        h: 0,
        halfW: 0,
        halfh: 0,
        mousePos: {},
        t: 0,
        x: 0,
        y: 0
    };

    function init() {
        params.canvas1 = document.getElementById('canvas1');
        params.ctx1 = params.canvas1.getContext('2d');
        params.w = (params.canvas1.width = window.innerWidth);
        params.h = (params.canvas1.height = window.innerHeight);
        params.halfW = params.w / 2;
        params.halfH = params.h / 2;
        params.mousePos.x = params.halfW;
        params.mousePos.y = params.halfH;

        if (animateBg){
            if (typeof window.orientation === 'undefined') {
                // Desktop
                window.addEventListener("mousemove", canvasOnMouseMoveHandler);
                canvasOnMouseMoveHandler();
                //animateForDesktop();
            } else {
                // Mobile
                animateForMobile();
                //window.addEventListener("touchmove",canvasOnMouseMoveHandler);
            }
    
            window.addEventListener("resize", function () {
                params.w = (params.canvas1.width = window.innerWidth);
                params.h = (params.canvas1.height = window.innerHeight);
                params.halfW = params.w / 2;
                params.halfH = params.h / 2;
            });
        }
        

        // Set thumbnails via background css
        if (imageMode){
            
            var elements = document.getElementsByClassName("img-container");
            for (var i = 0; i < elements.length; i++) {
                var element = elements[i];
                var bgContainer = element.getElementsByClassName("bg-container")[0];
                var src = element.getAttribute("data-link");
                // log(src);
                bgContainer.style.backgroundImage = "url(\"" + src + "\")";
                //log(element);

                // var img = new Image();
                // img.src = src;
                // img.classList.add("img-preview");
                // element.appendChild(img);
            }
        }
    }




    // function animateForDesktop(){
    //     window.requestAnimationFrame(animateForDesktop);
    //
    //     params.t += 0.1;
    //     params.ctx1.clearRect(0, 0, params.w, params.h)
    //     params.ctx1.beginPath();
    //     var rad = 300 + Math.floor( Math.sin(params.t/50) * 200 );
    //     var grad = params.ctx1.createRadialGradient(params.mousePos.x, params.mousePos.y, 1, params.mousePos.x, params.mousePos.y, rad);
    //     grad.addColorStop(1, 'rgba(255,255,255,0)');
    //     grad.addColorStop(0, 'rgba(100,0,0,0.5)');
    //     params.ctx1.fillStyle = grad;
    //     params.ctx1.arc(params.mousePos.x, params.mousePos.y, rad, 0, Math.PI*2, false);
    //     params.ctx1.fill();
    // }


    function animateForMobile() {
        window.requestAnimationFrame(animateForMobile);

        params.t += 0.1;
        params.x = (params.halfW) + Math.floor(Math.sin(params.t / 5) * params.halfW);
        params.y = (params.halfH) + Math.floor(Math.cos(params.t / 15) * params.halfH);
        params.ctx1.clearRect(0, 0, params.w, params.h);
        //params.ctx1.beginPath();

        var rad = 400 + Math.floor(Math.cos(params.t / 3) * 200);
        var grad = params.ctx1.createRadialGradient(params.x, params.y, 1, params.x, params.y, rad);

        // RGBA
        // var r = 0 + Math.floor( Math.cos(params.t/3) * 255 );
        // grad.addColorStop(1, 'rgba('+r+',255,255,0)');
        // grad.addColorStop(1, 'rgba(255,255,255,0)');
        // grad.addColorStop(0, 'rgba(255,0,0,0.5)');

        // hsla(hue, saturation, lightness, alpha)
        var h1 = 0 + Math.floor(Math.cos(params.t / 100) * 360);
        var h2 = 0 + Math.floor(Math.cos((params.t + 404) / 100) * 360);
        grad.addColorStop(1, 'hsla(' + h1 + ',100%,70%,0)');
        grad.addColorStop(0, 'hsla(' + h2 + ',100%,40%,1)');
        params.ctx1.fillStyle = grad;
        params.ctx1.arc(params.x, params.y, rad, 0, Math.PI * 2, false);
        params.ctx1.fill();

        // params.ctx1.fillRect(params.x,params.y,200,30);
        //var r = 20 + Math.floor( Math.cos(params.t/100) * 20 );
        // params.ctx1.rotate(degToRad(1))
    }


    function canvasOnMouseMoveHandler(e) {
        if (e) {
            params.mousePos = getMousePos(params.canvas1, e);
        }

        params.t += 0.1;
        params.ctx1.clearRect(0, 0, params.w, params.h);
        params.ctx1.beginPath();
        var rad = 600 + Math.floor(Math.sin(params.t / 10) * 200);
        var grad = params.ctx1.createRadialGradient(params.mousePos.x, params.mousePos.y, 1, params.mousePos.x, params.mousePos.y, rad);
        // grad.addColorStop(1, 'rgba(255,255,255,0)');
        // grad.addColorStop(0, 'rgba(100,0,0,0.5)');
        // hsla(hue, saturation, lightness, alpha)
        var h1 = 0 + Math.floor(Math.cos(params.t / 100) * 360);
        var h2 = 0 + Math.floor(Math.cos((params.t + 404) / 50) * 360);
        grad.addColorStop(1, 'hsla(' + h1 + ',100%,80%,0)');
        grad.addColorStop(0, 'hsla(' + h2 + ',100%,80%,1)');
        params.ctx1.fillStyle = grad;
        params.ctx1.arc(params.mousePos.x, params.mousePos.y, rad, 0, Math.PI * 2, false);
        params.ctx1.fill();

        // params.ctx1.clearRect(0, 0, params.w, params.h)
        // params.ctx1.beginPath();
        // var x = params.mousePos.x,
        //     y = params.mousePos.y,
        //     r = 250,
        //     c = "0,100,100";
        // var rad = params.ctx1.createRadialGradient(x, y, 1, x, y, r);
        // rad.addColorStop(1, 'rgba(100,0,0,0)');
        // rad.addColorStop(0, 'rgba(100,0,0,1)');
        // params.ctx1.fillStyle = rad;
        // params.ctx1.arc(x, y, r, 0, Math.PI*2, false);
        // params.ctx1.fill();
    }


    // Public


    // Utility
    function getMousePos(canvas, evt) {
        // scaleX: relationship bitmap vs. element for X
        // scaleY: relationship bitmap vs. element for Y
        var rect = canvas.getBoundingClientRect(),
            scaleX = canvas.width / rect.width,
            scaleY = canvas.height / rect.height;

        // x: scale mouse coordinates after they have
        // y: been adjusted to be relative to element
        // alternate method
        // xx: (evt.clientX - rect.left) / (rect.right - rect.left) * canvas.width,
        // yy: (evt.clientY - rect.top) / (rect.bottom - rect.top) * canvas.height
        return {
            x: (evt.clientX - rect.left) * scaleX,
            y: (evt.clientY - rect.top) * scaleY
        }
    }

    function degToRad(deg) {
        return deg * Math.PI / 180;
    }


    // Constructor / Init
    init();
};


// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
new AFTCFileBrowser(imageMode,animateBg,OpenFilesInNewTab);




