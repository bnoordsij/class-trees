import {forceGraph} from './plot/force-directed-graph'

var drawTree = () => {
    var parent = document.querySelector('.js-draw-tree');
    if (!parent) {
        return;
    }

    var dataMule = parent.querySelector('.js-data-mule');
    if (!dataMule) {
        return;
    }
    var data = {
        nodes: JSON.parse(dataMule.getAttribute('data-nodes')),
        links: JSON.parse(dataMule.getAttribute('data-links')),
    };

    var svg = forceGraph(
        data,
        {
            nodeTitle: (node) => node.title,
            nodeGroup: (node) => node.group, // set group color
            nodeStyle: (node) => node.style, // set group color
            // width: 1500,
            // height: 1000,
        },
    );

    parent.append(svg);
};

export {drawTree};
