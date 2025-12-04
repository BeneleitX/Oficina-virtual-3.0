$( '#container_mexico' ).css( 'height', $( window ).height() - 230 );

(async () => {

    const topology = await fetch(
       'https://code.highcharts.com/mapdata/countries/mx/mx-all.topo.json'
    ).then(response => response.json());

    chart_data.forEach(p => {
        p.z = p.venta;
        p.venta = Moneda.format( p.venta );
    });

    const H = Highcharts;

    const chart = Highcharts.mapChart('container_mexico', {
        title: {
            text: '',
            floating: true
        },

        tooltip: {
            pointFormat: '<br>{point.nombre}<br><br>' +
                'Pedidos: {point.pedidos}<br>' +
                'Venta: ${point.venta}'
        },
        mapNavigation: {
            enabled: true,
            buttonOptions: {
                verticalAlign: 'bottom'
            }
        },
        series: [{
            name: 'Basemap',
            mapData: topology,
            accessibility: {
                exposeAsGroupOnly: true
            },
            borderColor: 'rgba(200, 200, 200)',
            nullColor: '#009779',
            showInLegend: false
        }, {
            type: 'mapbubble',
            name: 'BENELEIT',
            dataLabels: {
                enabled: true,
                format: '{point.codigo}',
                style: {
                    color: 'var(--highcharts-neutral-color-100, black)'
                }
            },
            data: chart_data,
            showInLegend: false,
            maxSize: '15%',
            color: '#1A2542'
        }]
    });


})();

