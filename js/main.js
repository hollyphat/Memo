/**
 * Created by Hollyphat on 10/19/2017.
 */

function create_img(s,id){
    var tCtx = document.getElementById(id).getContext('2d'),
        imageElem = document.getElementById('image');
        tCtx.canvas.width = tCtx.measureText(s).width;
        tCtx.fillText(this.value, 0, 10);
        imageElem.src = tCtx.canvas.toDataURL();
}
