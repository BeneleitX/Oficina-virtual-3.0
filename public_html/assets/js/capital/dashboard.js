

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
    return;
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



function ask_semilla( inversion ){
    var i   = $( '[inversion=' + inversion + ']' ),
        rendimiento = i.attr( 'rendimiento' ),
        semilla     = i.attr( 'semilla' ),
        mes         = i.attr( 'mes' );

    $( '#semilla_3' ).val( '' );

    if( aviso_semilla ){
        $( '#aviso_semilla' ).show();
    }
    else{
        $( '#aviso_semilla' ).hide();
    }

    $( '[name=inversion_id]' ).val( inversion );
    $( '#semilla_2' ).val( parseFloat( semilla ) );
    $( '[name=opciones_semilla]' ).prop( 'checked', false );
    $( '#confirma_semilla' ).prop( 'disabled', true ).removeClass( 'btn-success' ).addClass( 'btn-outline-danger' );
    $( '#semilla_modal' ).modal( 'show' );
}


function ask_retiro( inversion ){
    var i   = $( '[inversion=' + inversion + ']' ),
        rendimiento = i.attr( 'rendimiento' ),
        semilla     = i.attr( 'semilla' ),
        mes         = i.attr( 'mes' );

    $( '#cantidad_3' ).val( '' );
    $( '[name=inversion_id]' ).val( inversion );
    $( '#cantidad_1' ).val( mes );
    $( '#cantidad_2' ).val( parseFloat( rendimiento ) );
    $( '[name=opciones_retiro]' ).prop( 'checked', false );
    $( '#confirma_agregar' ).prop( 'disabled', true ).removeClass( 'btn-success' ).addClass( 'btn-outline-danger' );
    $( '#stock_modal' ).modal( 'show' );
}


function cancela_retiro( solicitud ){
    $( '[name=solicitud_id]' ).val( solicitud );
    $( '#cancela_retiro' ).modal( 'show' );
}

$(document).ready(function(){
    $( '.cantidades' ).on( 'click', function(){
        $( this ).closest( 'label' ).click();
        $( this ).focus();
    });

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

                    if( respuesta.error ){
                        $( '#error' ).html( respuesta.error ).show();
                        $( '#principal' ).slideDown();
                    }
                    else{
                        window.location.href = base_url + 'capital';
                    }
                }, 1000);

            }
        }); 
    });    

    $( '[name=opciones_retiro]' ).on( 'change', function(){
        if( $( this ).attr( 'id' ) == 'type_3' ){
            $( '#cantidad_3' ).keyup();
        }
        else{
            $( '#confirma_agregar' ).prop( 'disabled', false ).removeClass( 'btn-outline-danger' ).addClass( 'btn-success' );
        }
    } );

    $( '#cantidad_3' ).on( 'keyup', function(){
        var total = $( this).val();

        if( parseFloat( $( '#cantidad_3' ).val() ) > 10 && parseFloat( $( '#cantidad_2' ).val() ) >= parseFloat( total ) ){
            $( '#confirma_agregar' ).prop( 'disabled', false ).removeClass( 'btn-outline-danger' ).addClass( 'btn-success' );
        }
        else{
            $( '#confirma_agregar' ).prop( 'disabled', true ).removeClass( 'btn-success' ).addClass( 'btn-outline-danger' );
        }
    });


    $( '[name=opciones_semilla]' ).on( 'change', function(){
        if( $( this ).attr( 'id' ) == 'type_5' ){
            $( '#semilla_3' ).keyup();
        }
        else{
            $( '#confirma_semilla' ).prop( 'disabled', false ).removeClass( 'btn-outline-danger' ).addClass( 'btn-success' );
        }
    } );

    $( '#semilla_3' ).on( 'keyup', function(){
        var total = $( this).val();

        if( parseFloat( $( '#semilla_3' ).val() ) > 10 && parseFloat( $( '#semilla_2' ).val() ) >= parseFloat( total ) ){
            $( '#confirma_semilla' ).prop( 'disabled', false ).removeClass( 'btn-outline-danger' ).addClass( 'btn-success' );
        }
        else{
            $( '#confirma_semilla' ).prop( 'disabled', true ).removeClass( 'btn-success' ).addClass( 'btn-outline-danger' );
        }
    });    

});

