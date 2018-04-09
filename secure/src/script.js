function log(str) {
    console.log(str);
}


var AFTCFileBrowserBackground = function () {

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

        // params.ctx1.beginPath();
        // params.ctx1.fillStyle = "RGBA(0,0,0,0.5)";
        // params.ctx1.fillRect(5, 5, params.w - 5, params.h - 5);


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


// var AFTCFileBrowserBackground = function () {

//     var params = {
//         canvas: null,
//         ctx: null,
//         w: 0,
//         h: 0,
//         mousePos: {},
//         tiles:[],
//         opacityChangeSpeed:0.1
//     };

//     function init() {
//         params.canvas = document.getElementById('canvas1');
//         params.ctx = params.canvas.getContext('2d');
//         params.w = (params.canvas.width = window.innerWidth);
//         params.h = (params.canvas.height = window.innerHeight);

//         params.ctx.beginPath();
//         params.ctx.fillStyle = "RGBA(200,0,0,0.5)";
//         params.ctx.fillRect(5, 5, params.w - 5, params.h - 5);


//         params.canvas.addEventListener("mousemove", canvasOnMouseMoveHandler);

//         window.addEventListener("resize", function () {
//             params.w = (params.canvas.width = window.innerWidth);
//             params.h = (params.canvas.height = window.innerHeight);
//         });

//         generateTiles();
//         renderLoop();
//     }


//     function canvasOnMouseMoveHandler(e) {
//         log("canvasOnMouseMoveHandler(e)");
//         params.mousePos = getMousePos(params.canvas, e);
//         //log(params.mousePos);
//         document.getElementById("debug").innerHTML = params.mousePos.x.toFixed(1) + "   :   " + params.mousePos.y.toFixed(1);

//         // var rect = params.canvas.getBoundingClientRect(),
//         //     x = params.canvas.clientX - rect.left,
//         //     y = params.canvas.clientY - rect.top,
//         //     i = 0, r;
//         // log(x);
//     }


//     function generateTiles(){
//         log("generateTiles()");

//         params.grid = {};
//         params.tileSize = 50;
//         params.tileGap = 15;

//         var offset = 0,
//             _x = offset,
//             _y = offset,
//             _xLim = (params.w + offset),
//             _yLim = (params.h + offset),
//             tile;

//         // Store tiles as tile value objects in params.tiles
//         while (_y < _yLim) {
//             while (_x < _xLim) {
//                 tile = tileVo(_x,_y);
//                 params.tiles.push(tile);

//                 // Draw tile
//                 //params.ctx.fillStyle = getRandomColor();
//                 var RGBA = "RGBA(" + tile.r + "," + tile.g + "," + tile.b + "," + tile.alpha + ")";
//                 //log(RGBA);
//                 params.ctx.fillStyle = "RGBA(" + tile.r + "," + tile.g + "," + tile.b + "," + tile.alpha + ")";
//                 params.ctx.fillRect(tile.sX, tile.sY, params.tileSize, params.tileSize); // NOTE: Doesnt work like flash just width not actual end coordinate

//                 //log(_x + "," + _y + "," + params.tileSize + "," + params.tileSize);
//                 _x += (params.tileSize) + (params.tileGap);
//             }
//             _x = offset;
//             _y += (params.tileSize) + (params.tileGap);
//         }
//     }


//     function draw() {
//         log("draw()");

//         for (var index in params.tiles){
//             //log(index + ": " + params.tiles[index]);
//             tile = params.tiles[index];

//                 // Draw tile
//                 //params.ctx.fillStyle = getRandomColor();
//                 var RGBA = "RGBA(" + tile.r + "," + tile.g + "," + tile.b + "," + tile.alpha + ")";
//                 //log(RGBA);
//                 params.ctx.fillStyle = "RGBA(" + tile.r + "," + tile.g + "," + tile.b + "," + tile.alpha + ")";
//                 params.ctx.fillRect(tile.sX, tile.sY, params.tileSize, params.tileSize); // NOTE: Doesnt work like flash just width not actual end coordinate

//                 //log(_x + "," + _y + "," + params.tileSize + "," + params.tileSize);
//                 _x += (params.tileSize) + (params.tileGap);
//             _x = offset;
//             _y += (params.tileSize) + (params.tileGap);
//         }
//     }


//     function renderLoop(){
//         window.requestAnimationFrame(renderLoop);
//         var sX, eX, sY, eY, tile;
//         for (var index in params.tiles){
//             //log(index + ": " + params.tiles[index]);
//             tile = params.tiles[index];
//             if (params.mousePos.x >= tile.sX && params.mousePos.x <= tile.eX){
//                 log("MOUSE IS OVER TILE [" + index + "]");
//                 if (tile.opacity < 1)
//                 {
//                     tile.opacity += opacityChangeSpeed;
//                     if (tile.opacity > 1){
//                         tile.opacity = 1;
//                     }
//                 }
//             } else {
//                 if (tile.opacity > 0)
//                 {
//                     tile.opacity -= opacityChangeSpeed;
//                     if (tile.opacity < 1){
//                         tile.opacity = 0;
//                     }
//                 }
//             }
//         }
//     }


//     function tileVo(x,y){
//         var tile = {
//             sX:x,
//             sY:y,
//             eX:0,
//             eY:0,
//             alpha:1,
//             r:0,
//             g:0,
//             b:0
//         }

//         tile.eX = params.tileSize + x;
//         tile.eY = params.tileSize + y;
//         tile.r = Math.floor(Math.random() * 255);
//         tile.g = Math.floor(Math.random() * 255);
//         tile.b = Math.floor(Math.random() * 255);
//         return tile;
//     }


//     // Public


//     // Utility
//     function getMousePos(canvas, evt) {
//         var rect = canvas.getBoundingClientRect(), // abs. size of element
//             scaleX = canvas.width / rect.width,    // relationship bitmap vs. element for X
//             scaleY = canvas.height / rect.height;  // relationship bitmap vs. element for Y

//         return {
//             x: (evt.clientX - rect.left) * scaleX,   // scale mouse coordinates after they have
//             y: (evt.clientY - rect.top) * scaleY,     // been adjusted to be relative to element
//             xx: (evt.clientX - rect.left) / (rect.right - rect.left) * canvas.width,
//             yy: (evt.clientY - rect.top) / (rect.bottom - rect.top) * canvas.height
//         }
//     }

//     function getRandomColor() {
//         var letters = '0123456789ABCDEF';
//         var color = '#';
//         for (var i = 0; i < 6; i++) {
//             color += letters[Math.floor(Math.random() * 16)];
//         }
//         return color;
//     }


//     // Simulate constructor execution
//     init();
// }


// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
new AFTCFileBrowserBackground();




