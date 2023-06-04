let zoomLevel = 1;
let centerX = 0;
let centerY = 0;

function zoomIn() {
    zoomLevel *= 1.2;
    zoom(zoomLevel);
}

function zoomOut() {
    zoomLevel /= 1.2;
    zoom();
}

function zoom() {
    const svg = document.querySelector('svg');
    const width = svg.getAttribute('width') / zoomLevel;
    const height = svg.getAttribute('height') / zoomLevel;
    const viewBoxX = centerX - width / 2;
    const viewBoxY = centerY - height / 2;

    svg.setAttribute('viewBox', `${viewBoxX} ${viewBoxY} ${width} ${height}`);
}

let registerZoomButtons = () => {
    document.querySelectorAll('.js-zoom-in').forEach((button) => {
        button.addEventListener('click', zoomIn);
    });

    document.querySelectorAll('.js-zoom-out').forEach((button) => {
        button.addEventListener('click', zoomOut);
    });
}

export {registerZoomButtons};
