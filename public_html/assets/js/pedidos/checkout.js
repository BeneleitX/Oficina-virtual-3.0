function fondeo( modelo, metodo, cantidad ){

        $.ajax({
            url: base_url + "fondeo", 
            type: "POST",
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            data: { [csrf_token] : csrf_hash, modelo : modelo, metodo : metodo, cantidad: cantidad },
            success: function( result ){
                window.location.href = base_url + "pedido/" + result;
            }
        });
}

$(document).ready(function()
{

});