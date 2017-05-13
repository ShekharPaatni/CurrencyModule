(function ($, window, drupalSettings) {
 'use strict';
  Drupal.behaviors.currency = {
    attach: function (context, settings) {
   
               $('#frontpanel .form-submit').once().on('click',function(){
          
           setTimeout(function(){   
               InitChart();

function InitChart() {

  var lineData =JSON.parse(($('#graphResult div').text()));

  var vis = d3.select("#genrateGraph"),
    WIDTH = 200,
    HEIGHT = 200,
    MARGINS = {
      top: 10,
      right: 10,
      bottom: 10,
      left: 70
    },
    xRange = d3.scale.linear().range([MARGINS.left, WIDTH - MARGINS.right]).domain([d3.min(lineData, function (d) {
        return d.date;
      }),
      d3.max(lineData, function (d) {
        return d.date;
      })
    ]),

    yRange = d3.scale.linear().range([HEIGHT - MARGINS.top, MARGINS.bottom]).domain([d3.min(lineData, function (d) {
        return d.price;
      }),
      d3.max(lineData, function (d) {
        return d.price;
      })
    ]),

    xAxis = d3.svg.axis()
      .scale(xRange)
      .tickSize(4)
      .tickSubdivide(true)
      .tickFormat(d3.format('d')),
      

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

  var lineFunc = d3.svg.line()
  .x(function (d) {
    return xRange(d.date);
  })
  .y(function (d) {
    return yRange(d.price);
  })
  .interpolate('linear');

vis.append("svg:path")
  .attr("d", lineFunc(lineData))
  .attr("stroke", "blue")
  .attr("stroke-width", 2)
  .attr("fill", "none");
}
        });      
    }
}  
})(jQuery, window, drupalSettings);
