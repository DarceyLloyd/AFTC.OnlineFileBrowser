function log(str) {
    if (console) {
        console.log(str);
    }
}
window.isMobile = function() {
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
var AFTCFileBrowserBackground = function() {
    if (isMobile()) {
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
        if (typeof window.orientation === 'undefined') {
            window.addEventListener("mousemove", canvasOnMouseMoveHandler);
            canvasOnMouseMoveHandler();
        } else {
            animateForMobile();
        }
        window.addEventListener("resize", function() {
            params.w = (params.canvas1.width = window.innerWidth);
            params.h = (params.canvas1.height = window.innerHeight);
            params.halfW = params.w / 2;
            params.halfH = params.h / 2;
        });
    }

    function animateForMobile() {
        window.requestAnimationFrame(animateForMobile);
        params.t += 0.1;
        params.x = (params.halfW) + Math.floor(Math.sin(params.t / 5) * params.halfW);
        params.y = (params.halfH) + Math.floor(Math.cos(params.t / 15) * params.halfH);
        params.ctx1.clearRect(0, 0, params.w, params.h);
        var rad = 400 + Math.floor(Math.cos(params.t / 3) * 200);
        var grad = params.ctx1.createRadialGradient(params.x, params.y, 1, params.x, params.y, rad);
        var h1 = 0 + Math.floor(Math.cos(params.t / 100) * 360);
        var h2 = 0 + Math.floor(Math.cos((params.t + 404) / 100) * 360);
        grad.addColorStop(1, 'hsla(' + h1 + ',100%,70%,0)');
        grad.addColorStop(0, 'hsla(' + h2 + ',100%,40%,1)');
        params.ctx1.fillStyle = grad;
        params.ctx1.arc(params.x, params.y, rad, 0, Math.PI * 2, false);
        params.ctx1.fill();
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
        var h1 = 0 + Math.floor(Math.cos(params.t / 100) * 360);
        var h2 = 0 + Math.floor(Math.cos((params.t + 404) / 50) * 360);
        grad.addColorStop(1, 'hsla(' + h1 + ',100%,80%,0)');
        grad.addColorStop(0, 'hsla(' + h2 + ',100%,80%,1)');
        params.ctx1.fillStyle = grad;
        params.ctx1.arc(params.mousePos.x, params.mousePos.y, rad, 0, Math.PI * 2, false);
        params.ctx1.fill();
    }

    function getMousePos(canvas, evt) {
        var rect = canvas.getBoundingClientRect(),
            scaleX = canvas.width / rect.width,
            scaleY = canvas.height / rect.height;
        return {
            x: (evt.clientX - rect.left) * scaleX,
            y: (evt.clientY - rect.top) * scaleY
        }
    }

    function degToRad(deg) {
        return deg * Math.PI / 180;
    }
    init();
};
new AFTCFileBrowserBackground();