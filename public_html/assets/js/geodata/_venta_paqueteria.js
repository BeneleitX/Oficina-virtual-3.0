$( '#container_mexico' ).css( 'height', $( window ).height() - 230 );

(async () => {

    const topology = await fetch(
        'https://code.highcharts.com/mapdata/countries/mx/mx-all.topo.json'
    ).then(response => response.json());

    // Create the chart
    Highcharts.mapChart('container_mexico', {
        chart: {
            map: topology
        },

        mapNavigation: {
            enabled: true,
            buttonOptions: {
                verticalAlign: 'bottom'
            }
        },

        colorAxis: {
            min: 0,
            minColor: '#fff',
            maxColor: '#009779'           
        },

        series: [{
            data: chart_data,
            name: 'Venta',
            states: {
                hover: {
                    color: '#1A2542'
                }
            },
            dataLabels: {
                enabled: true,
                format: '{point.name}'
            }
        }]
    });

})();