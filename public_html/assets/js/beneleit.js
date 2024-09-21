// devuelve un número formateado con ceros a la izquierda
function id( n, digitos = 0 )
{
    res   = "<span class='fw-light opacity-50'>";

    for( a = 0; a < digitos - (String(n).length); a++)
        res += "0";
    res += "</span><span class='fw-bold'>";
    res += n;   
    res += "</span>";
    return res;
}

function notify( $mensaje ){
    // SI existe la propiedad de notificaciones en el navegador actual
    if( "Notification" in window ){

        // Si ya estan activas
        if( Notification.permission === "granted" ){  
            // Check whether notification permissions have already been granted;
            // if so, create a notification
            const notification = new Notification( $mensaje );
        }
    }
}

function activa_notificaciones(){
    Notification.requestPermission().then((permission) => {
        // If the user accepts, let's create a notification
        if (permission === "granted") {
            $( '#alerta_no_notifica' ).slideUp();
        }
    });
}

function alerta( clase, icono, mensaje, id = null ){

    $( '#contenedor-body' ).prepend( '<div ' + (id ? 'id="alerta_'+id : '') + '" class="alerta alert alert-' + clase + '"><i class="fa fa-' + icono + '"></i> ' + mensaje + '</div>' );

    $(".alerta").fadeTo(5000, 500).slideUp(500, function() {
        $(".alerta").slideUp();
    });
}

function delay(fn, ms) {
    let timer = 0
    return function(...args) {
    clearTimeout(timer)
    timer = setTimeout(fn.bind(this, ...args), ms || 0)
    }
}

function mes(mes)
{
    switch(mes)
    {
        case 1 : mespal = "enero";      break;
        case 2 : mespal = "febrero";    break;
        case 3 : mespal = "marzo";      break;
        case 4 : mespal = "abril";      break;
        case 5 : mespal = "mayo";       break;
        case 6 : mespal = "junio";      break;
        case 7 : mespal = "julio";      break;
        case 8 : mespal = "agosto";     break;
        case 9 : mespal = "septiembre"; break;
        case 10: mespal = "octubre";    break;
        case 11: mespal = "noviembre";  break;
        case 12: mespal = "diciembre";  break;
    }

    return mespal;
}

String.prototype.digitoVerificador = function()
{
    var luhnArr = [[0,1,2,3,4,5,6,7,8,9],[0,2,4,6,8,1,3,5,7,9]], sum = 0;
    this.replace(/\D+/g,"").replace(/[\d]/g, function(c, p, o){
        sum += luhnArr[ (o.length-p)&1 ][ parseInt(c,10) ]
    });
    return ((10 - sum%10)%10);
}


$(document).ready(function(){

    var myDefaultAllowList = bootstrap.Tooltip.Default.allowList;
    myDefaultAllowList['*'].push( 'style' );

    $('[data-bs-toggle="tooltip"]').tooltip({
        container: 'body',
        html: true,
        placement : 'top'
    });   

    $('[data-bs-toggle="popover"]').popover();

    // SI existe la propiedad de notificaciones en el navegador actual
    if( "Notification" in window ){

        // Si no estan activas
        if( Notification.permission !== "granted" ){  
            // Notification.requestPermission();

            alerta( 'warning', 'warning', 'No has autorizado las notificaciones. <button class="btn btn-sm btn-warning" onclick="activa_notificaciones()">Autorizar</button>', 'no_notifica' );
        }
    }

    $( '.submit' ).on( 'click', function(){
        $( this ).attr( 'disabled', true ).html( '<i class="fa fa-circle-notch fa-spin"></i> Espere...' );
        $( this ).closest( 'form' ).submit();
    });
});