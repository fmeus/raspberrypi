showChart = function( area, sensor, period, unit ) {
    $( area ).empty();

    var margin = {top: 20, right: 50, bottom: 30, left: 50 },
        width = 700 - margin.left - margin.right,
        height = 250 - margin.top - margin.bottom;

    var parseDate = d3.time.format("%Y-%m-%d %X").parse;

    var x = d3.time.scale()
        .range([0, width]);

    var y = d3.scale.linear()
        .range([height, 0]);

    y.ticks(0.1);

    var xAxis = d3.svg.axis()
        .scale(x)
        .tickFormat( function(d) { return d3.time.format('%H:%M')(d); } )
        .orient("bottom");

    var yAxis = d3.svg.axis()
        .scale(y)
        .tickSize(-width,0,0)
        .tickFormat( d3.format(',.1f') )
        .orient("left");

    var yAxis2 = d3.svg.axis()
        .scale(y)
        .tickFormat( d3.format(',.1f') )
        .orient("right");

    // interpolate 'smoothens' the line
    var line = d3.svg.line()
        .interpolate("cardinal")
        .x(function (d) { return x(d.timestamp); })

    .y(function (d) {
        return y(d.value);
    });

    var svg = d3.select( area ).append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    d3.csv("/sensor-data.php?action=csv_data&id="+sensor+"&period="+period, function (error, data) {
        data.forEach(function (d) {
            d.timestamp = parseDate(d.timestamp);
            d.value = +d.value;
        });

        x.domain(d3.extent(data, function (d) {return d.timestamp; })); 
        y.domain( [d3.min(data, function (d) {return d.value; })-0.1,d3.max(data, function (d) {return d.value; })+0.1] ).nice();

        svg.append("g")
            .attr("class", "x axis")
            .attr("transform", "translate(0," + height + ")")
            .call(xAxis);

        svg.append("g")
            .attr("class", "y axis")
            .call(yAxis)
            .append("text")
            .attr("transform", "rotate(-90) translate(0,-50)")
            .attr("y", 6)
            .attr("dy", ".71em")
            .style("text-anchor", "end")
            .text(unit);

        svg.append("g")
            .attr("class", "y2 axis")
            .attr("transform", "translate(" + width + " ,0)")   
            .call(yAxis2)
            .append("text")
            .attr("transform", "rotate(90) translate(0, -50)")
            .attr("y", 6)
            .attr("dy", ".71em")
            .style("text-anchor", "start")
            .text(unit);

        svg.append("path")
            .datum(data)
            .attr("class", "line")
            .attr("d", line);

        svg.selectAll("dot")
            .data(data)
            .enter().append("circle")
            .style("fill","purple")
            .attr("r", 1.5)
            .attr("cx", function(d) { return x(d.timestamp); })
            .attr("cy", function(d) { return y(d.value); });
    });
};
