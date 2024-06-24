function userdata( s ){
    var formData = new FormData(),
        modal    = $( '#modal_userdata' );

    formData.append( 'socio', s );
    formData.append( 'modelo', modelo );
    formData.append( [csrf_token] , csrf_hash ),

    modal.find( '.modal-title' ).html( 'Cargando datos...' );
    modal.find( '.modal-body' ).html( loader );

    modal.modal( 'show' );
    $.ajax({
        url: base_url + 'userdata',
        data: formData,
        type: 'POST',
        processData: false,
        contentType: false,
        cache: false,        
        async: true,
        success: function( respuesta ){
            modal.find( '.modal-title' ).html( 'Socio ' + s );
            modal.find( '.modal-body' ).html( respuesta );

            modal.find( '.modal-body a' ).on( 'click', function(){
                modal.find( '.modal-body a' ).addClass( 'disabled' );
            } );
        }
    });
}

$( document ).ready(function()
{
    $( canvas ).html( loader );
    
    $.ajax({
        url: base_url + "downlineJSON",
        async: true,
        dataType: "text",
        type  : 'POST',
        data: { [csrf_token] : csrf_hash, modelo : modelo, socio : socio },
        success: function( data ){
            $( canvas ).empty();

            downline = JSON.parse( data , ( key, value, context ) => {
                
                switch( key ){
                    case "profundidad":
                        return value ?? [0,0,0];

                    case "calificaciones":
                    case "id":
                    case "nivel":
                    case "padre":
                    case "patrocinador":
                    default:
                        return value;
                }
            });

            beneleit( downline );

            var popoverTriggerList = [].slice.call( document.querySelectorAll( 'g[data-bs-toggle="popover"]' ) )
            var popoverList = popoverTriggerList.map( function ( popoverTriggerEl ) {
                return new bootstrap.Popover( popoverTriggerEl );
            })
        
            $( 'g.node' ).on( 'show.bs.popover', function(){
        
                $( canvas ).on( 'mousedown', function(){
                    $( '[data-bs-toggle="popover"]' ).popover( 'hide' );
                });
            });

            osInstance2 = OverlayScrollbars( $( canvas ) , { });

            $( '.filtro' ).on( 'change', function(){
                var valor = $( this ).val(),
                    tipo  = $( this ).attr( 'tipo' );

                filtrar( tipo, valor );
            });
        },
        error: function( r ){
            console.log( 'error', r );
        }
    }); 
});


            // filtros

            function filtrar( variable, valor ){

                $.each( downline, function( indice, node ){
                    go = true;

                    switch( variable ){
                        case "rango":
                        case "estatus":
                                go = ( node[ variable ] == valor );
                            break;

                        case "patrocinador":
                                go = ( ( parseInt( valor ) == 1 && node[ variable ] == socio ) || ( parseInt( valor ) == 2 && node[ variable ] != socio ) );
                            break;

                        case "profundidad":
                            switch( valor ){
                                case 0 : 
                                    go = ( node[ 'profundidad' ][ 0 ] > 2 );
                                    break;
                                case 1 : 
                                    go = ( node[ 'profundidad' ][ 1 ] > 8 );
                                    break;
                                case 2 : 
                                    go = ( node[ 'profundidad' ][ 2 ] > 26 );
                                    break;
                                case 3 : 
                                    go = ( node[ 'profundidad' ][ 0 ] > 2 && node[ 'profundidad' ][ 1 ] > 8 && node[ 'profundidad' ][ 2 ] > 26 );
                                    break;
                            }
                            break;

                        case "califica_0":
                                go = ( node[ 'calificaciones' ][ 0 ] == valor );
                            break;

                        case "califica_1":
                                go = ( node[ 'calificaciones' ][ 1 ] == valor );
                            break;                            
                    }

                    // activa y desactiva

                    if( go ) 
                        $( '.node[socio=' + node[ 'id' ] + '] > rect.shadow' ).fadeOut();
                    else
                        $( '.node[socio=' + node[ 'id' ] + '] > rect.shadow' ).fadeIn();
                });
            }


            (function($bs) {
                const CLASS_NAME = 'has-child-dropdown-show';
                $bs.Dropdown.prototype.toggle = function(_orginal) {
                    return function() {
                        document.querySelectorAll('.' + CLASS_NAME).forEach(function(e) {
                            e.classList.remove(CLASS_NAME);
                        });
                        let dd = this._element.closest('.dropdown').parentNode.closest('.dropdown');
                        for (; dd && dd !== document; dd = dd.parentNode.closest('.dropdown')) {
                            dd.classList.add(CLASS_NAME);
                        }
                        return _orginal.call(this);
                    }
                }($bs.Dropdown.prototype.toggle);
            
                document.querySelectorAll('.dropdown').forEach(function(dd) {
                    dd.addEventListener('hide.bs.dropdown', function(e) {
                        if (this.classList.contains(CLASS_NAME)) {
                            this.classList.remove(CLASS_NAME);
                            e.preventDefault();
                        }
                        e.stopPropagation(); // do not need pop in multi level mode
                    });
                });
            
                // for hover
                document.querySelectorAll('.dropdown-hover, .dropdown-hover-all .dropdown').forEach(function(dd) {
                    dd.addEventListener('mouseenter', function(e) {
                        let toggle = e.target.querySelector(':scope>[data-bs-toggle="dropdown"]');
                        if (!toggle.classList.contains('show')) {
                            $bs.Dropdown.getOrCreateInstance(toggle).toggle();
                            dd.classList.add(CLASS_NAME);
                            $bs.Dropdown.clearMenus();
                        }
                    });
                    dd.addEventListener('mouseleave', function(e) {
                        let toggle = e.target.querySelector(':scope>[data-bs-toggle="dropdown"]');
                        if (toggle.classList.contains('show')) {
                            $bs.Dropdown.getOrCreateInstance(toggle).toggle();
                        }
                    });
                });
            })(bootstrap);