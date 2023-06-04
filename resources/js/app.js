import {drawTree} from "./components/draw-tree";
import {registerZoomButtons} from  './components/zoom-buttons';


window.onload = (() => {
    setTimeout(() => {
        registerZoomButtons();
        drawTree();
    }, 200);
});
