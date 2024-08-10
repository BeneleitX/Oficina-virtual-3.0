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

            $('[data-bs-toggle="tooltip"]').tooltip({
                container: 'body',
                html: true,
                placement : 'top'
            });

            clon = $( 'g.node[socio=' + s + ']' ).clone( true )[0];
            $( 'g.vaciado' ).html( clon );
            $( 'g.vaciado > g.node[socio=' + s + ']' ).attr( 'transform', 'translate(60,25)' );            
        }
    });
}


function beneleit( data ){

    var dataMap = data.reduce( function( map, node ){
        
            map[ node.id ] = node;
            return map;
        }, {}),
        treeData = [];

    function get_parent( node )
    {
        if( node.padre == 0 ){
            return 0;
        }
        else{
            var parent = dataMap[ node.padre ];
        
            if( parent.estatus.substring( 0, 3 ) > estatus_minimo ){
                return parent;
            }
            else{
                return get_parent( parent );
            }
        }
    }

    data.forEach( function( node ){
        var parent = get_parent( node );

        if( parent && parseInt( node.estatus.substring( 0, 3 ) ) > estatus_minimo ){
            ( parent.children || ( parent.children = [] ) ).push( node );
        } else {
            treeData.push( node );
        }
    });
   
    var totalNodes = 0,
        i = 0,
        final_x = 0, final_y = 0,
        root
        viewerWidth  = $( canvas ).width(),
        viewerHeight = $( canvas ).height();
        tree = d3.layout
            .tree()
            .separation( function( a, b){ return 1; })
            .size( [ viewerHeight, viewerWidth ] ),
        diagonal = d3.svg
            .diagonal()
            .projection( function( d ){ return [ d.y, d.x ]; } ),
        baseSvg = d3
            .select( canvas ).append( 'svg' )
            .attr( 'width', viewerWidth )
            .attr( 'height', viewerHeight ),
        date = new Date(),
        lastdate = new Date( date.getFullYear(), date.getMonth() -1 ,1 );


    function elbow( d, i ) {
        var baja   = 95,
            curva  = 15,
            curva2 = 15,
            x1 = d.source.x,
            y1 = d.source.y,
            x2 = d.target.x,
            y2 = d.target.y;

        if( Math.abs( x1 - x2 ) < 2 )  curva  = 0;
        if( Math.abs( x1 - x2 ) < 16 ) curva2 = 0;
        
        return "M" + x1 + "," + y1 
            + " V" + ( y1+baja )
            + " Q" + x1 + "," + ( y1 + baja + curva) + " " + ( x1 + ( ( x1 < x2 ) ? curva : ( curva * -1 ) ) ) + "," + ( y1 + baja + curva )
            + " H" + ( x2- ( ( x1 < x2 ) ? curva2 : ( curva2 * -1 ) ) ) 
            + " Q" + x2 + "," + ( y1 + baja + curva ) + " " + x2 + "," + ( y1 + baja + ( curva * 2 ) )
            + " V" + y2;
    }

    function hexagono( x, y, direccion = 1, w = 20, t = 10, h = 10 ){
        return x + "," + y + " " 
            + ( x + t * direccion ) + "," + y + " "
            + ( x + w * direccion ) + "," + ( y + h / 2 ) + " "
            + ( x + t * direccion ) + "," + ( y + h ) + " "
            + x + "," + ( y + h );
    }

    function poligono( x, y, w = 20, t = 10, h = 10 ){
        return ( x - t ) + "," + y + " " + ( x + t ) + "," + y + " "
            + ( x + w ) + "," + ( y + h / 2 ) + " " + ( x + t) + "," + ( y + h ) + " "
            + ( x - t ) + "," + ( y + h ) + " " + ( x - w ) + "," + ( y + h / 2 );
    }

    function rounded_rect(x, y, w, h, r) {
        var retval;
        retval  = "M" + (x + r) + "," + y;
        retval += "h" + (w - 2*r);
        retval += "a" + r + "," + r + " 0 0 1 " + r + "," + r; 
        retval += "v" + (h - 2*r);
        retval += "a" + r + "," + r + " 0 0 1 " + -r + "," + r; 
        retval += "h" + (2*r - w);
        retval += "a" + r + "," + r + " 0 0 1 " + -r + "," + -r; 
        retval += "v" + (2*r - h);
        retval += "a" + r + "," + r + " 0 0 1 " + r + "," + -r; 
        retval += "z";
        return retval;
    }


    function roundedRect(x, y, w, h, r, tl, tr, bl, br) {
        let retval;
        retval = `M${x + r},${y}`;
        retval += `h${w - (2 * r)}`;
        if (tr) {
          retval += `a${r},${r} 0 0 1 ${r},${r}`;
        } else {
          retval += `h${r}`; retval += `v${r}`;
        }
        retval += `v${h - (2 * r)}`;
        if (br) {
          retval += `a${r},${r} 0 0 1 ${-r},${r}`;
        } else {
          retval += `v${r}`; retval += `h${-r}`;
        }
        retval += `h${(2 * r) - w}`;
        if (bl) {
          retval += `a${r},${r} 0 0 1 ${-r},${-r}`;
        } else {
          retval += `h${-r}`; retval += `v${-r}`;
        }
        retval += `v${((2 * r) - h)}`;
        if (tl) {
          retval += `a${r},${r} 0 0 1 ${r},${-r}`;
        } else {
          retval += `v${-r}`; retval += `h${r}`;
        }
        retval += 'z';
        return retval;
      }
      
    function circulos( d ){
    // console.log(d);
    }

    function update( source ) {
        var levelWidth = [ 1 ],
            childCount = function( level, n ){
                if( n.children && n.children.length > 0 ){
                    if( levelWidth.length <= level + 1 ){
                        levelWidth.push( 0 );
                    }
                    levelWidth[ level + 1 ] += n.children.length;
                    n.children.forEach( function( d ){ 
                        childCount( level + 1, d ); 
                    });
                }
            };

        childCount( 0, root );
        
        var ancho = d3.max( levelWidth ) * 120; 
            tree  = tree.size( [ ancho , 100 ] ), // 500
            nodes = tree.nodes( root ).reverse(),
            links = tree.links( nodes );

        nodes.forEach(function( d ){ 
            d.y = 25 + d.depth * 150; // 150
            
        });

        var node = svgGroup
            .selectAll( 'g.node' )
            .data( nodes, function( d ){ 
                return d.id || ( d.id = ++i ); 
            });

        const parentTree = ( d ) => {
            let nodeLinks = [];

            while( d.padre != 0 ){
                nodeLinks.push( d.id );
                d = d.parent;
            }

            nodeLinks = nodeLinks.concat( d.id );
            return nodeLinks;
        }
        
        // funcionalidad de popover
        var nodeEnter = node
            .enter()
            .append( 'g' )
             .attr( 'class', 'node')
            .attr( 'socio', function( d ){ return d.id; } )
      
            // Colocar en posición
            .attr( 'transform', function( d ){
                if( d.x > final_x ){ final_x = d.x; }
                if( d.y > final_y ){ final_y = d.y; }
                return 'translate(' + d.x + ',' + d.y + ')';
            }) 


            .on( 'click', function( d ){
                userdata( d.id );
            })

            // Cambiar color de linea upline al pasar el mouse por encima
            .on( 'mouseover', function( d ){
                var linkedNodes = parentTree( d );

                link
                    .style( 'transition', '.4s all ease' )
                    .style( 'stroke', function( e, a ){
                        if ( linkedNodes.includes( e.target.id ) ){
                            this.parentNode.append( this );
                            return e.target.id == 0 ? 'var(--bs-gray-500)' : 'var(--bs-' + estatus[ d.estatus ].color + ')';
                        }
                        else
                            return 'var(--bs-gray-500)';
                    });

                node.each( function( e,n ){
                    if( linkedNodes.includes( e.id ) ) 
                        this.parentNode.append( this );
                });
            })
            
            // Cambiar color de linea upline al pasar el mouse por encima
            .on( 'mouseout', function( d ){
                var linkedNodes = parentTree( d );

                link
                    .style( 'transition', '.4s all ease' )
                    .style( 'stroke', 'var(--bs-gray-500)' );

                node.each( function( e,n ){
                    if( linkedNodes.includes( e.id ) ) 
                        this.parentNode.append( this );
                });
            });

    // Crear pattern de foto para rellenar circulo (transparente png si no hay foto)
    nodeEnter.append( 'svg:pattern' )
        .attr( 'id', function( d ){ return "_" + d.id } )
        .attr( 'patternUnits', 'userSpaceOnUse' )
        .attr( 'x', -30 )
        .attr( 'y', 5 )
        .attr( 'width', 60 )
        .attr( 'height', 60 )
        .append( 'svg:image' )
        .attr( 'xlink:href', function( d ){
            return d.avatar == null ? 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==' : base_url + '/data/' + d.id + '/avatar/' + d.avatar; 
        } )
        .attr( 'x', 0 )
        .attr( 'y', 0 )
        .attr( 'width', 60 )
        .attr( 'height', 60 );



/*       nodeEnter.append( 'svg:pattern' )
        .attr( 'id', function(d){ return '_nuevo_' + d.antiguedad; } )
        .attr( 'patternUnits', 'userSpaceOnUse' )
        .attr( 'x', -55 )
        .attr( 'y', -20 )
        .attr( 'width', 110 )
        .attr( 'height', 110 )
        .append( 'svg:image' )
       .attr( 'xlink:href', function(d){ return base_url + '/assets/img/m' + d.antiguedad + '.png'; } ) // 42, 44
        .attr( 'x', 0 )
        .attr( 'y', 0 )
        .attr( 'width', 110 )
        .attr( 'height', 110 );  

        // Circulo exterior con el estatus
        nodeEnter.append('svg:circle')
            .attr( 'cy', 35 )
            .attr( 'r', 55 )
            .style( 'stroke-width', 0 )
            .style( 'stroke', 'transparent' )
            .attr( 'class', '' )
            .style( 'fill', function( d ){ return d.antiguedad <3 ? 'url(#_nuevo_' +d.antiguedad+')' : 'transparent'; } );    */      



        nodeEnter.append('svg:circle')
            .attr( 'cy', 35 )
            .attr( 'r', 40 )
            .style( 'stroke-width', 0 )
            .style( 'stroke', 'transparent' )
            .attr( 'class', '' )
            .style( 'fill', function( d ){ return 'var(--bs-' + estatus[ d.estatus ].color + ')' } );

        
        // circulo interior para identificar los socios directos
         nodeEnter.append( 'svg:circle' )
            .attr( 'cy', 35 )
            .attr( 'r', 27 )
            .style( 'stroke-width', 2 )
            .style( 'stroke', function( d ){ return 'var(--bs-'+ ( d.patrocinador != socio ? 'white' : 'gray-900') + ')'; } )              
            .style( 'fill', 'white' );

        // Circulo central con foto (color de estatus si no hay foto)
        nodeEnter.append( 'svg:circle' )
            .attr( 'cy', 35 )
            .attr( 'r', 27 )
            .style( 'stroke-width', 2 )
            .style( 'stroke', function( d ){ return 'var(--bs-'+ ( d.patrocinador != socio ? 'white' : 'gray-900') + ')'; } )            
            .style( 'opacity', function( d ){ return d.avatar == null ? 0.6 : 1 ; } )
            .style( 'fill', function( d ){ return d.avatar == null ? 'var(--bs-' + estatus[ d.estatus ].color + ')' : 'url(#_' + d.id + ')'; } );

        // Texto de iniciales (Solo cuando no hay foto)
        nodeEnter.append( 'text' )
            .attr( 'dx', 0 )
            .attr( 'class', 'iniciales' )
            .attr( 'dy', 43 ) 
            .attr( 'text-anchor', 'middle' )
            .style( 'font-size', '24px' )
            .style( 'font-weight', 600 )
            .style( 'fill', 'white' )
            .text( function( d ){ return d.avatar == null ? d.iniciales : ''; } );

nodeEnter.append('path')
    .attr("d", roundedRect(-26, -5, 26, 20, 5, 0, 0, 1, 0))
    .attr( 'class', function( d ){ return 'compra_' + ( parseInt( d.calificaciones[ 0 ].substring( 0, 2 ) ) >= 10 ? 'si' : 'no' ); } );


nodeEnter.append('path')
.attr("d", roundedRect(0, -5, 26, 20, 5, 0, 0, 0, 1))
.style( 'fill', function( d ){ return 'var(--bs-' + estatus[ d.estatus ].color + ')' } );


nodeEnter.append('path')
.attr("d", roundedRect(-26, -17, 17, 12, 5, 1, 0, 0, 0))
.style( 'fill', function( d ){ return d.profundidad[ 0 ] > 2 ? 'var(--bs-teal)' : 'var(--bs-gray-400)'; } );  

nodeEnter.append('path')
.attr("d", roundedRect(-9, -17, 18, 12, 5, 0, 0, 0, 0))
.style( 'fill', function( d ){ return d.profundidad[ 1 ] > 8 ? 'var(--bs-teal)' : 'var(--bs-gray-400)'; } );     

nodeEnter.append('path')
.attr("d", roundedRect(9, -17, 17, 12, 5, 0, 1, 0, 0))
.style( 'fill', function( d ){ return d.profundidad[ 2 ] > 26 ? 'var(--bs-teal)' : 'var(--bs-gray-400)'; } );            



 


nodeEnter.append("rect")
.attr("x", -26)
.attr("y", 55)
.attr("rx", 5)
.attr("height", 32)
.attr("width", 52)
.style("fill", function( d ){ return 'var(--bs-'+ rangos[ d.rango ].color + ')'; } );
nodeEnter.append("rect")
.attr("x", -27)
.attr("y", 74)
.style( 'opacity', .3 )
.attr("height", 13)
.attr("width", 54)
.style("fill", 'white');

        // Texto numero de socio en parte inferior
        nodeEnter.append( 'text' )
            .attr( 'dy', 69 )
            .attr( 'text-anchor', 'middle')
            .style( 'font-size', '12px' )
            .style( 'fill', 'white' )
            .style( 'font-weight', 'bold' )
            .text( function( d ){ return d.id ; });

        // Texto rango
        nodeEnter.append( 'text' )
            .attr( 'dy', 83 )
            .attr( 'text-anchor', 'middle')
            .style( 'font-size', '7px' )
            .style( 'font-weight', 'bold' )
            .style( 'fill', 'white' )
            .text( function( d ){ return d.rango.substring( 3 ); });            

        // Texto calificación mes anterior
        nodeEnter.append('text')
            .attr( 'dx', -17 )
            .attr( 'dy', 8 )
            .attr( 'text-anchor', 'start' )
            .style( 'fill', 'white' )
            .style( 'font-size', '9px' )
            .style( 'font-weight', 'bold' )
            .text(function( d ){ return d.calificaciones[ 0 ].substring( 3, 5 ); } );

        // Texto calificación mes actual
        nodeEnter.append( 'text' )
            .attr( 'dx', 17 )
            .attr( 'dy', 8 )
            .attr( 'text-anchor', 'end' )
            .style( 'fill', 'white' )
            .style( 'font-size','9px' )
            .style( 'font-weight','bold' )
            .text( function( d ){ return d.calificaciones[ 1 ].substring( 3, 5 ); } );


        // texto profundidad
        // -26, -15, 52, 10
        nodeEnter.append('text')
            .attr( 'dx', -17 )
            .attr( 'dy', -8 )
            .attr( 'text-anchor', 'middle' )
            .style( 'fill', function( d ){ return d.profundidad[ 0 ] > 2 ? 'var(--bs-white)' : 'var(--bs-gray-600)'; } )
            .style( 'font-size', '7px' )
            .style( 'font-weight', function( d ){ return d.profundidad[ 0 ] > 2 ? 900 : 700; } )
            .text( function( d ){ return d.profundidad[ 0 ]; } );  
            nodeEnter.append('text')
            .attr( 'dx', 0 )
            .attr( 'dy', -8 )
            .attr( 'text-anchor', 'middle' )
            .style( 'fill', function( d ){ return d.profundidad[ 1 ] > 8 ? 'var(--bs-white)' : 'var(--bs-gray-600)'; } )
            .style( 'font-size', '7px' )
            .style( 'font-weight', function( d ){ return d.profundidad[ 1 ] > 8 ? 900 : 700; } )
            .text( function( d ){ return d.profundidad[ 1 ]; } );  
            nodeEnter.append('text')
            .attr( 'dx', 17 )
            .attr( 'dy', -8 )
            .attr( 'text-anchor', 'middle' )
            .style( 'fill', function( d ){ return d.profundidad[ 2 ] > 26 ? 'var(--bs-white)' : 'var(--bs-gray-600)'; } )
            .style( 'font-size', '7px' )
            .style( 'font-weight', function( d ){ return d.profundidad[ 2 ] > 26 ? 900 : 700; } )
            .text( function( d ){ return d.profundidad[ 2 ]; } );



            nodeEnter.append( 'svg:pattern' )
.attr( 'id', function(d){ return '_nuevo_' + d.antiguedad; } )
.attr( 'patternUnits', 'userSpaceOnUse' )
.attr( 'x', -20 )
.attr( 'y', -10 )
.attr( 'width', 30 )
.attr( 'height', 30 )
.append( 'svg:image' )
.attr( 'xlink:href', function(d){ return base_url + '/assets/img/m' + d.antiguedad + '.png'; } ) // 42, 44
.attr( 'x', 0 )
.attr( 'y', 0 )
.attr( 'width', 30 )
.attr( 'height', 30 );  

// Circulo exterior con el estatus
nodeEnter.append('svg:circle')
    .attr( 'cx', -35 )
    .attr( 'cy', 35 )
    .attr( 'r', 15 )
    .style( 'stroke-width', 0 )
    .style( 'stroke', 'red' )
    .attr( 'class', '' )
    .style( 'fill', function( d ){ return d.antiguedad <3 ? 'url(#_nuevo_' +d.antiguedad+')' : 'transparent'; } );  


        // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

        // mascara para atenuar socios cuando queden fuera de los filtros

        nodeEnter.append("rect")
            .attr("x", -40)
            .attr("y", -16)
            .style( 'opacity', .85 )
            .attr("height", 102)
            .attr("width", 80)
            .style("fill", 'white')
            .style("display", 'none')
            .attr( 'class', 'shadow');

        // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

        // lineas de enlace en arbol
        var link = svgGroup.selectAll( 'path.link' )
            .data( links, function( d ){ return d.target.id; })
            .enter()
            .insert( 'path', 'g' )
            .attr( 'class', 'link' )
            .style( 'transition', '.5s all ease' )
            .style( 'stroke-width', 14 )
            .attr( 'd', elbow );
    }

    var svgGroup = baseSvg.append( 'g' );

    root = treeData[ 0 ];
    root.x0 = viewerHeight / 2;
    root.y0 = 0;

    update( root );  

    $( canvas + ' svg' ).attr( 'width', final_x + 60 ).attr( 'height', final_y + 100 ); 
}

$( document ).ready(function()
{
    $( canvas ).html( loader );
    
    $.ajax({
        url: base_url + "uplineJSON",
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