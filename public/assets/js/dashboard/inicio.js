
$(document).ready(function(){
    var options = {
        series: [40],
        chart: {
            height: 250,
            type: 'radialBar',
        },
        plotOptions: {
            radialBar: {
                startAngle: -120,
                endAngle: 120,                    
                hollow: {
                    
                    size: '60%',
                    image: base_url + 'assets/img/recompensas/10-CELULAR.png',
                    imageWidth: 100,
                    imageHeight: 100,
                    imageClipped: false
                },

                dataLabels: {
                    name: {
                        offsetY: 85,
                        fontSize: '12px',
                        show: true,
                        color: '#999',
                    },
                    value: {
                        formatter: function(val) {
                        return 43;
                        },
                        color: '#009779',
                        fontSize: '34px',
                        offsetY: 55,
                        show: true,
                    }
                }  
            },
        },
        stroke: {
            lineCap: 'round'
        },            
        colors: ['#009779'],
        labels: ['Estrellas']          
    };

    var chart = new ApexCharts(document.querySelector("#chart"), options);
    chart.render();
});
