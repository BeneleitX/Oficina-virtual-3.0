

var options = {
    colors: ['var(--bs-gray-500)', 'var(--bs-teal)', 'var(--bs-mustard)'],
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


function carga_hash( inversion ){
    var modal    = $( '#carga_hash' );

    modal.attr( 'inversion', inversion );

    $( '#loader' ).show();
    $( '#principal' ).hide();

    modal.modal( 'show' );

    setTimeout(function() {
        $( '#loader' ).slideUp();
        $( '#principal' ).slideDown();
    }, 1000);
    
}


$(document).ready(function(){
    $.each( chart, function( a, b){

        options.series = b.valores;
        options.xaxis.categories = b.meses;

        chart = new ApexCharts(document.querySelector( "#chart_" + b.id ), options);
        chart.render();
    });

    $( '#confirma_hash' ).on( 'click', function(){
        var formData = new FormData(),
            modal    = $( '#carga_hash' );
    
        formData.append( 'inversion', modal.attr( 'inversion' ) );
        formData.append( 'hash', $( '[name=_txhash]' ).val() );
        formData.append( [csrf_token] , csrf_hash );
    
        $( '#loader' ).slideDown();
        $( '#principal' ).slideUp();
    
        $.ajax({
            url: base_url + 'quick_data',
            data: formData,
            type: "POST",
            dataType: "json",
            processData: false,
            contentType: false,
            cache: false,        
            async: true,
            success: function( respuesta ){

                setTimeout(function() {
                    $( '#loader' ).slideUp();

                    if( respuesta.ok ){
                        window.location.href = base_url + 'capital';
                    }
                    else{
                        $( '#error' ).html( respuesta.error ).show();
                        $( '#principal' ).slideDown();
                    }
                }, 1000);

            }
        }); 
    });    
});

