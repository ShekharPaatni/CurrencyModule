(function ($, window, drupalSettings) {
    'use strict';
    Drupal.behaviors.currency = {
        attach: function (context, settings) {

            $('#frontpanel .form-submit').once().on('click', function () {
            //The setTimeout function will help to load render data from the page.          
                setTimeout(function () {
                    //calling the intial chart.
                    InitChart();

                    function InitChart() {
                    //it will load the render data and change it into the json format.
                        var lineData = JSON.parse(($('#graphResult div').text()));
                    //it will set the graph margin and height by using the function and it will render it into the html selector.
                        var vis = d3.select("#genrateGraph"),
                                WIDTH = 200,
                                HEIGHT = 200,
                                MARGINS = {
                                    top: 10,
                                    right: 10,
                                    bottom: 10,
                                    left: 70
                                },
                            //setting the xAxis range and min and max limit.
                                xRange = d3.scale.linear().range([MARGINS.left, WIDTH - MARGINS.right]).domain([d3.min(lineData, function (d) {
                                return d.date;
                            }),
                            d3.max(lineData, function (d) {
                                return d.date;
                            })
                        ]),
                                //setting the yAxis range and min and max limit.
                                yRange = d3.scale.linear().range([HEIGHT - MARGINS.top, MARGINS.bottom]).domain([d3.min(lineData, function (d) {
                                return d.price;
                            }),
                            d3.max(lineData, function (d) {
                                return d.price;
                            })
                        ]),
                        //setting the property of the xAxis of the graph
                                xAxis = d3.svg.axis()
                                .scale(xRange)
                                .tickSize(4)
                                .tickSubdivide(true)
                                .tickFormat(d3.format('d')),
                        //setting the property of the yAxis of the graph
                                yAxis = d3.svg.axis()
                                .scale(yRange)
                                .tickSize(8)
                                .orient("left")
                                .tickSubdivide(true);


                        vis.append("svg:g")
                                .attr("class", "x axis")
                                .attr("transform", "translate(0," + (HEIGHT - MARGINS.bottom) + ")")
                                .call(xAxis);

                        vis.append("svg:g")
                                .attr("class", "y axis")
                                .attr("transform", "translate(" + (MARGINS.left) + ",0)")
                                .call(yAxis);
                        //it will help to create the line of the graph.
                        var lineFunc = d3.svg.line()
                                .x(function (d) {
                                    return xRange(d.date);
                                })
                                .y(function (d) {
                                    return yRange(d.price);
                                })
                                .interpolate('linear');
                        //it is the render the line which will show the graph using lineData data.
                        vis.append("svg:path")
                                .attr("d", lineFunc(lineData))
                                .attr("stroke", "blue")
                                .attr("stroke-width", 2)
                                .attr("fill", "none");

                    }
                }, 1500);
            });
        }
    }
})(jQuery, window, drupalSettings);
