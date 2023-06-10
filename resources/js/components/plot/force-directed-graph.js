import * as d3 from "d3";

// Copyright 2021 Observable, Inc.
// Released under the ISC license.
// https://observablehq.com/@d3/force-directed-graph
function forceGraph({
        nodes, // an iterable of node objects (typically [{id}, …])
        links // an iterable of link objects (typically [{source, target}, …])
    }, {
        nodeId = d => d.id, // given d in nodes, returns a unique identifier (string)
        nodeGroup, // given d in nodes, returns an (ordinal) value for color
        nodeGroups, // an array of ordinal values representing the node groups
        nodeTitle, // given d in nodes, a title string
        nodeStyle, // given d in nodes, a style string
        nodeStrength,
        linkStrength,
        linkSource = ({source}) => source, // given d in links, returns a node identifier string
        linkTarget = ({target}) => target, // given d in links, returns a node identifier string
        linkStroke = "#999", // link stroke color
        colors = d3.schemeTableau10, // an array of color strings, for the node groups
        width = 1280, // outer width, in pixels
        height = 800, // outer height, in pixels
        invalidation // when this promise resolves, stop the simulation
    } = {}) {
    // Compute values.
    const nodeIds = d3.map(nodes, nodeId).map(intern);
    const linkSources = d3.map(links, linkSource).map(intern);
    const linkTargets = d3.map(links, linkTarget).map(intern);
    if (nodeTitle === undefined) {
        nodeTitle = (_, i) => nodeIds[i];
    }
    if (nodeStyle === undefined) {
        nodeStyle = (_, i) => '';
    }
    const styles = (nodeStyle == null) ? null : d3.map(nodes, nodeStyle);
    const titles = (nodeTitle == null) ? null : d3.map(nodes, nodeTitle);
    const allNodeGroups = (nodeGroup == null) ? null : d3.map(nodes, nodeGroup).map(intern);

    // Replace the input nodes and links with mutable objects for the simulation.
    nodes = d3.map(nodes, (_, i) => ({id: nodeIds[i]}));
    links = d3.map(links, (_, i) => ({source: linkSources[i], target: linkTargets[i]}));

    // Compute default domains.
    if (allNodeGroups && nodeGroups === undefined) {
        nodeGroups = d3.sort(allNodeGroups);
    }

    // Construct the scales.
    const color = (nodeGroup == null) ? null : d3.scaleOrdinal(nodeGroups, colors);

    // Construct the forces.
    const forceNode = d3.forceManyBody();
    const forceLink = d3.forceLink(links).id(({index: i}) => nodeIds[i]);
    if (nodeStrength !== undefined) {
        forceNode.strength(nodeStrength);
    }
    if (linkStrength !== undefined) {
        forceLink.strength(linkStrength);
    }

    const simulation = d3.forceSimulation(nodes)
        .force("link", forceLink)
        .force("charge", forceNode)
        .force("center",  d3.forceCenter())
        .on("tick", ticked);

    const d3svg = d3.create("svg")
        .attr("width", width)
        .attr("height", height)
        .attr("viewBox", [-width / 2, -height / 2, width, height])
        .attr("style", "max-width: 100%; height: auto; height: intrinsic;");

    let defs = d3svg.append("defs");

    // point an arrow to parent node
    let arrow = defs.append("marker")
        .attr("id", "triangle1")
        .attr("viewBox", "0 0 8 3")
        .attr("refX", "1")
        .attr("refY", "1.5")
        .attr("markerUnits", "strokeWidth")
        .attr("markerWidth", "8")
        .attr("markerHeight", "3")
        .attr("orient", "auto")
        .append("path")
        .attr("fill", "#FFF")
        .attr("opacity", 0.5)
        .attr("d", "M 8 0 L 5 1.5 L 8 3 z");

    const link = d3svg.append("g")
        .attr("stroke", typeof linkStroke !== "function" ? linkStroke : null)
        .attr("marker-start", "url(#triangle1)")
        .attr("stroke-opacity", 0.6)
        .attr("stroke-width", 1.5)
        .attr("stroke-linecap", "round")
        .selectAll("line")
        .data(links)
        .join("line");

    const node = d3svg.append("g")
        .attr("fill", "#FFF")
        .attr("stroke", "#FFF")
        .attr("stroke-opacity", 0.01)
        .attr("fill-opacity", 0.01)
        .attr("stroke-width", 0.1)
        .selectAll("circle")
        .data(nodes)
        .join("circle")
        .attr("r", 50)
        .call(drag(simulation));

    const text = d3svg.append("g")
        .attr("stroke", "#FCC")
        .attr("stroke-opacity", 0.9)
        .attr("text-anchor", "middle")
        .selectAll("text")
        .data(nodes)
        .join("text")
        // .attr("font-size", 8)
        .attr("style", ({index: i}) => styles[i])
        .text(({index: i}) => titles[i])
    ;

    if (invalidation != null) {
        invalidation.then(() => simulation.stop());
    }

    if (allNodeGroups) text.attr("stroke", ({index: i}) => color(allNodeGroups[i]));

    function intern(value) {
        return value !== null && typeof value === "object" ? value.valueOf() : value;
    }

    function ticked() {
        link
            .attr("x1", d => d.source.x)
            .attr("y1", d => d.source.y)
            .attr("x2", d => d.target.x)
            .attr("y2", d => d.target.y);

        node
            .attr("cx", d => d.x)
            .attr("cy", d => d.y);

        text
            .attr("x", d => d.x)
            .attr("y", d => d.y);
    }

    function drag(simulation) {
        function dragStarted(event) {
            if (!event.active) {
                simulation.alphaTarget(0.3).restart();
            }
            event.subject.fx = event.subject.x;
            event.subject.fy = event.subject.y;
        }

        function dragged(event) {
            event.subject.fx = event.x;
            event.subject.fy = event.y;
        }

        function dragEnded(event) {
            if (!event.active) {
                simulation.alphaTarget(0);
            }
            event.subject.fx = null;
            event.subject.fy = null;
        }

        return d3.drag()
            .on("start", dragStarted)
            .on("drag", dragged)
            .on("end", dragEnded);
    }

    return Object.assign(d3svg.node(), {scales: {color}});
};

export {forceGraph};
