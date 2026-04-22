function fondeo( pedido, metodo, cantidad, salida = 0 ){

    $.ajax({
        url: base_url + "fondeo", 
        type: "POST",
        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
        data: { [csrf_token] : csrf_hash, pedido : pedido, metodo : metodo, cantidad: cantidad, salida : salida },
        success: function( result ){
            window.location.href = base_url + ( salida ? 'salidas/10-NUTRICION' : 'pedido/' + result );
        }
    });
}

$(document).ready(function()
{

});