

var 
  options = {
    colors: ['var(--bs-gray-400)', 'var(--bs-teal)'],
    series: null,
    chart: {
        type: 'bar',
        height: 250,
        stacked: true,
        toolbar: {
            show: true
        },
        zoom: {
            enabled: false
        }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            borderRadiusApplication: 'end', // 'around', 'end'
            borderRadiusWhenStacked: 'last', // 'all', 'last'
        },
    },
    dataLabels: {
        enabled: false
    },        
    yaxis: {
        labels: {
            formatter: function (value) {
                return "$" + value ;
            }
        },
    },        
    xaxis: {
        categories: null,
    },
    legend: {
        show: false
    },
    fill: {
        opacity: 1
    }
};


$(document).ready(function(){
console.log(chart);
    $.each( chart, function( a, b){

        options.series = b.valores;
        options.xaxis.categories = b.meses;

        chart = new ApexCharts(document.querySelector( "#chart_" + b.id ), options);
        chart.render();
    });

});


