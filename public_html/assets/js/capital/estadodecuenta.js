

var options = {
    colors: ['var(--bs-gray-500)', 'var(--bs-teal)', 'var(--bs-mustard)'],
    series: null,
    chart: {
        type: 'bar',
        height: 500,
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


        options.series = chart.valores;
        options.xaxis.categories = chart.meses;

        chart = new ApexCharts(document.querySelector( "#chart" ), options);
        chart.render();


});

