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
        // desactivado temporalmente para revisar por qué edge lo sigue poniendo
        if( 0 && Notification.permission !== "granted" ){  
            // Notification.requestPermission();

            alerta( 'warning', 'warning', 'No has autorizado las notificaciones. <button class="btn btn-sm btn-warning" onclick="activa_notificaciones()">Autorizar</button>', 'no_notifica' );
        }
    }

    $( '.submit' ).on( 'click', function(){
        $( this ).attr( 'disabled', true ).html( '<i class="fa fa-circle-notch fa-spin"></i> Espere...' );
        $( this ).closest( 'form' ).submit();
    });
});


function formato_fecha_yyyy_mm_dd( fecha ){
    var array_fecha = fecha.split("/")
    return array_fecha[2] + "-" + array_fecha[1] + "-" + array_fecha[0];
}


//Función para validar una CURP
function curpValida(curp) {
    var re = /^([A-Z][AEIOUX][A-Z]{2}\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])[HM](?:AS|B[CS]|C[CLMSH]|D[FG]|G[TR]|HG|JC|M[CNS]|N[ETL]|OC|PL|Q[TR]|S[PLR]|T[CSL]|VZ|YN|ZS)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d])(\d)$/,
        validado = curp.match(re);
	
    if (!validado){  //Coincide con el formato general?
    	return false;
    }

    
    //Validar que coincida el dígito verificador
    function digitoVerificador(curp17) {
        //Fuente https://consultas.curp.gob.mx/CurpSP/
        var diccionario  = "0123456789ABCDEFGHIJKLMNÑOPQRSTUVWXYZ",
            lngSuma      = 0.0,
            lngDigito    = 0.0;
        for(var i=0; i<17; i++)
            lngSuma = lngSuma + diccionario.indexOf(curp17.charAt(i)) * (18 - i);
        lngDigito = 10 - lngSuma % 10;
        if (lngDigito == 10) return 0;
        return lngDigito;
    }
  
    if (validado[2] != digitoVerificador(validado[1])){ 
    	return false;
    }

    return true; //Validado
}

function calcular_edad(fecha){
    hoy = new Date()

    var array_fecha = fecha.split("-")

    var ano = parseInt(array_fecha[0]);
    var mes = parseInt(array_fecha[1]);
    var dia = parseInt(array_fecha[2]);

    var edad = hoy.getUTCFullYear() - ano - 1; //-1 porque no se si ha cumplido años ya este año

    if (hoy.getMonth() + 1 - mes < 0) //+ 1 porque los meses empiezan en 0
       return edad
    if (hoy.getMonth() + 1 - mes > 0)
       return edad+1

    if (hoy.getUTCDate() - dia >= 0)
       return edad + 1

    return edad
}


function valida_email(email) {
    // Expresión regular para validar un correo electrónico
    const regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    return regex.test(email);
}


const countries = [
    { name: "Afghanistan", code: "AF", phone: 93 },
    { name: "Aland Islands", code: "AX", phone: 358 },
    { name: "Albania", code: "AL", phone: 355 },
    { name: "Algeria", code: "DZ", phone: 213 },
    { name: "American Samoa", code: "AS", phone: 1684 },
    { name: "Andorra", code: "AD", phone: 376 },
    { name: "Angola", code: "AO", phone: 244 },
    { name: "Anguilla", code: "AI", phone: 1264 },
    { name: "Antarctica", code: "AQ", phone: 672 },
    { name: "Antigua and Barbuda", code: "AG", phone: 1268 },
    { name: "Argentina", code: "AR", phone: 54 },
    { name: "Armenia", code: "AM", phone: 374 },
    { name: "Aruba", code: "AW", phone: 297 },
    { name: "Australia", code: "AU", phone: 61 },
    { name: "Austria", code: "AT", phone: 43 },
    { name: "Azerbaijan", code: "AZ", phone: 994 },
    { name: "Bahamas", code: "BS", phone: 1242 },
    { name: "Bahrain", code: "BH", phone: 973 },
    { name: "Bangladesh", code: "BD", phone: 880 },
    { name: "Barbados", code: "BB", phone: 1246 },
    { name: "Belarus", code: "BY", phone: 375 },
    { name: "Belgium", code: "BE", phone: 32 },
    { name: "Belize", code: "BZ", phone: 501 },
    { name: "Benin", code: "BJ", phone: 229 },
    { name: "Bermuda", code: "BM", phone: 1441 },
    { name: "Bhutan", code: "BT", phone: 975 },
    { name: "Bolivia", code: "BO", phone: 591 },
    { name: "Bonaire, Sint Eustatius and Saba", code: "BQ", phone: 599 },
    { name: "Bosnia and Herzegovina", code: "BA", phone: 387 },
    { name: "Botswana", code: "BW", phone: 267 },
    { name: "Bouvet Island", code: "BV", phone: 55 },
    { name: "Brazil", code: "BR", phone: 55 },
    { name: "British Indian Ocean Territory", code: "IO", phone: 246 },
    { name: "Brunei Darussalam", code: "BN", phone: 673 },
    { name: "Bulgaria", code: "BG", phone: 359 },
    { name: "Burkina Faso", code: "BF", phone: 226 },
    { name: "Burundi", code: "BI", phone: 257 },
    { name: "Cambodia", code: "KH", phone: 855 },
    { name: "Cameroon", code: "CM", phone: 237 },
    { name: "Canada", code: "CA", phone: 1 },
    { name: "Cape Verde", code: "CV", phone: 238 },
    { name: "Cayman Islands", code: "KY", phone: 1345 },
    { name: "Central African Republic", code: "CF", phone: 236 },
    { name: "Chad", code: "TD", phone: 235 },
    { name: "Chile", code: "CL", phone: 56 },
    { name: "China", code: "CN", phone: 86 },
    { name: "Christmas Island", code: "CX", phone: 61 },
    { name: "Cocos (Keeling) Islands", code: "CC", phone: 672 },
    { name: "Colombia", code: "CO", phone: 57 },
    { name: "Comoros", code: "KM", phone: 269 },
    { name: "Congo", code: "CG", phone: 242 },
    { name: "Congo, Democratic Republic of the Congo", code: "CD", phone: 242 },
    { name: "Cook Islands", code: "CK", phone: 682 },
    { name: "Costa Rica", code: "CR", phone: 506 },
    { name: "Cote D'Ivoire", code: "CI", phone: 225 },
    { name: "Croatia", code: "HR", phone: 385 },
    { name: "Cuba", code: "CU", phone: 53 },
    { name: "Curacao", code: "CW", phone: 599 },
    { name: "Cyprus", code: "CY", phone: 357 },
    { name: "Czech Republic", code: "CZ", phone: 420 },
    { name: "Denmark", code: "DK", phone: 45 },
    { name: "Djibouti", code: "DJ", phone: 253 },
    { name: "Dominica", code: "DM", phone: 1767 },
    { name: "Dominican Republic", code: "DO", phone: 1809 },
    { name: "Ecuador", code: "EC", phone: 593 },
    { name: "Egypt", code: "EG", phone: 20 },
    { name: "El Salvador", code: "SV", phone: 503 },
    { name: "Equatorial Guinea", code: "GQ", phone: 240 },
    { name: "Eritrea", code: "ER", phone: 291 },
    { name: "Estonia", code: "EE", phone: 372 },
    { name: "Ethiopia", code: "ET", phone: 251 },
    { name: "Falkland Islands (Malvinas)", code: "FK", phone: 500 },
    { name: "Faroe Islands", code: "FO", phone: 298 },
    { name: "Fiji", code: "FJ", phone: 679 },
    { name: "Finland", code: "FI", phone: 358 },
    { name: "France", code: "FR", phone: 33 },
    { name: "French Guiana", code: "GF", phone: 594 },
    { name: "French Polynesia", code: "PF", phone: 689 },
    { name: "French Southern Territories", code: "TF", phone: 262 },
    { name: "Gabon", code: "GA", phone: 241 },
    { name: "Gambia", code: "GM", phone: 220 },
    { name: "Georgia", code: "GE", phone: 995 },
    { name: "Germany", code: "DE", phone: 49 },
    { name: "Ghana", code: "GH", phone: 233 },
    { name: "Gibraltar", code: "GI", phone: 350 },
    { name: "Greece", code: "GR", phone: 30 },
    { name: "Greenland", code: "GL", phone: 299 },
    { name: "Grenada", code: "GD", phone: 1473 },
    { name: "Guadeloupe", code: "GP", phone: 590 },
    { name: "Guam", code: "GU", phone: 1671 },
    { name: "Guatemala", code: "GT", phone: 502 },
    { name: "Guernsey", code: "GG", phone: 44 },
    { name: "Guinea", code: "GN", phone: 224 },
    { name: "Guinea-Bissau", code: "GW", phone: 245 },
    { name: "Guyana", code: "GY", phone: 592 },
    { name: "Haiti", code: "HT", phone: 509 },
    { name: "Heard Island and McDonald Islands", code: "HM", phone: 0 },
    { name: "Holy See (Vatican City State)", code: "VA", phone: 39 },
    { name: "Honduras", code: "HN", phone: 504 },
    { name: "Hong Kong", code: "HK", phone: 852 },
    { name: "Hungary", code: "HU", phone: 36 },
    { name: "Iceland", code: "IS", phone: 354 },
    { name: "India", code: "IN", phone: 91 },
    { name: "Indonesia", code: "ID", phone: 62 },
    { name: "Iran, Islamic Republic of", code: "IR", phone: 98 },
    { name: "Iraq", code: "IQ", phone: 964 },
    { name: "Ireland", code: "IE", phone: 353 },
    { name: "Isle of Man", code: "IM", phone: 44 },
    { name: "Israel", code: "IL", phone: 972 },
    { name: "Italy", code: "IT", phone: 39 },
    { name: "Jamaica", code: "JM", phone: 1876 },
    { name: "Japan", code: "JP", phone: 81 },
    { name: "Jersey", code: "JE", phone: 44 },
    { name: "Jordan", code: "JO", phone: 962 },
    { name: "Kazakhstan", code: "KZ", phone: 7 },
    { name: "Kenya", code: "KE", phone: 254 },
    { name: "Kiribati", code: "KI", phone: 686 },
    { name: "Korea, Democratic People's Republic of", code: "KP", phone: 850 },
    { name: "Korea, Republic of", code: "KR", phone: 82 },
    { name: "Kosovo", code: "XK", phone: 383 },
    { name: "Kuwait", code: "KW", phone: 965 },
    { name: "Kyrgyzstan", code: "KG", phone: 996 },
    { name: "Lao People's Democratic Republic", code: "LA", phone: 856 },
    { name: "Latvia", code: "LV", phone: 371 },
    { name: "Lebanon", code: "LB", phone: 961 },
    { name: "Lesotho", code: "LS", phone: 266 },
    { name: "Liberia", code: "LR", phone: 231 },
    { name: "Libyan Arab Jamahiriya", code: "LY", phone: 218 },
    { name: "Liechtenstein", code: "LI", phone: 423 },
    { name: "Lithuania", code: "LT", phone: 370 },
    { name: "Luxembourg", code: "LU", phone: 352 },
    { name: "Macao", code: "MO", phone: 853 },
    { name: "Macedonia, the Former Yugoslav Republic of", code: "MK", phone: 389 },
    { name: "Madagascar", code: "MG", phone: 261 },
    { name: "Malawi", code: "MW", phone: 265 },
    { name: "Malaysia", code: "MY", phone: 60 },
    { name: "Maldives", code: "MV", phone: 960 },
    { name: "Mali", code: "ML", phone: 223 },
    { name: "Malta", code: "MT", phone: 356 },
    { name: "Marshall Islands", code: "MH", phone: 692 },
    { name: "Martinique", code: "MQ", phone: 596 },
    { name: "Mauritania", code: "MR", phone: 222 },
    { name: "Mauritius", code: "MU", phone: 230 },
    { name: "Mayotte", code: "YT", phone: 262 },
    { name: "Mexico", code: "MX", phone: 52 },
    { name: "Micronesia, Federated States of", code: "FM", phone: 691 },
    { name: "Moldova, Republic of", code: "MD", phone: 373 },
    { name: "Monaco", code: "MC", phone: 377 },
    { name: "Mongolia", code: "MN", phone: 976 },
    { name: "Montenegro", code: "ME", phone: 382 },
    { name: "Montserrat", code: "MS", phone: 1664 },
    { name: "Morocco", code: "MA", phone: 212 },
    { name: "Mozambique", code: "MZ", phone: 258 },
    { name: "Myanmar", code: "MM", phone: 95 },
    { name: "Namibia", code: "NA", phone: 264 },
    { name: "Nauru", code: "NR", phone: 674 },
    { name: "Nepal", code: "NP", phone: 977 },
    { name: "Netherlands", code: "NL", phone: 31 },
    { name: "Netherlands Antilles", code: "AN", phone: 599 },
    { name: "New Caledonia", code: "NC", phone: 687 },
    { name: "New Zealand", code: "NZ", phone: 64 },
    { name: "Nicaragua", code: "NI", phone: 505 },
    { name: "Niger", code: "NE", phone: 227 },
    { name: "Nigeria", code: "NG", phone: 234 },
    { name: "Niue", code: "NU", phone: 683 },
    { name: "Norfolk Island", code: "NF", phone: 672 },
    { name: "Northern Mariana Islands", code: "MP", phone: 1670 },
    { name: "Norway", code: "NO", phone: 47 },
    { name: "Oman", code: "OM", phone: 968 },
    { name: "Pakistan", code: "PK", phone: 92 },
    { name: "Palau", code: "PW", phone: 680 },
    { name: "Palestinian Territory, Occupied", code: "PS", phone: 970 },
    { name: "Panama", code: "PA", phone: 507 },
    { name: "Papua New Guinea", code: "PG", phone: 675 },
    { name: "Paraguay", code: "PY", phone: 595 },
    { name: "Peru", code: "PE", phone: 51 },
    { name: "Philippines", code: "PH", phone: 63 },
    { name: "Pitcairn", code: "PN", phone: 64 },
    { name: "Poland", code: "PL", phone: 48 },
    { name: "Portugal", code: "PT", phone: 351 },
    { name: "Puerto Rico", code: "PR", phone: 1787 },
    { name: "Qatar", code: "QA", phone: 974 },
    { name: "Reunion", code: "RE", phone: 262 },
    { name: "Romania", code: "RO", phone: 40 },
    { name: "Russian Federation", code: "RU", phone: 7 },
    { name: "Rwanda", code: "RW", phone: 250 },
    { name: "Saint Barthelemy", code: "BL", phone: 590 },
    { name: "Saint Helena", code: "SH", phone: 290 },
    { name: "Saint Kitts and Nevis", code: "KN", phone: 1869 },
    { name: "Saint Lucia", code: "LC", phone: 1758 },
    { name: "Saint Martin", code: "MF", phone: 590 },
    { name: "Saint Pierre and Miquelon", code: "PM", phone: 508 },
    { name: "Saint Vincent and the Grenadines", code: "VC", phone: 1784 },
    { name: "Samoa", code: "WS", phone: 684 },
    { name: "San Marino", code: "SM", phone: 378 },
    { name: "Sao Tome and Principe", code: "ST", phone: 239 },
    { name: "Saudi Arabia", code: "SA", phone: 966 },
    { name: "Senegal", code: "SN", phone: 221 },
    { name: "Serbia", code: "RS", phone: 381 },
    { name: "Serbia and Montenegro", code: "CS", phone: 381 },
    { name: "Seychelles", code: "SC", phone: 248 },
    { name: "Sierra Leone", code: "SL", phone: 232 },
    { name: "Singapore", code: "SG", phone: 65 },
    { name: "St Martin", code: "SX", phone: 721 },
    { name: "Slovakia", code: "SK", phone: 421 },
    { name: "Slovenia", code: "SI", phone: 386 },
    { name: "Solomon Islands", code: "SB", phone: 677 },
    { name: "Somalia", code: "SO", phone: 252 },
    { name: "South Africa", code: "ZA", phone: 27 },
    { name: "South Georgia and the South Sandwich Islands", code: "GS", phone: 500 },
    { name: "South Sudan", code: "SS", phone: 211 },
    { name: "Spain", code: "ES", phone: 34 },
    { name: "Sri Lanka", code: "LK", phone: 94 },
    { name: "Sudan", code: "SD", phone: 249 },
    { name: "Suriname", code: "SR", phone: 597 },
    { name: "Svalbard and Jan Mayen", code: "SJ", phone: 47 },
    { name: "Swaziland", code: "SZ", phone: 268 },
    { name: "Sweden", code: "SE", phone: 46 },
    { name: "Switzerland", code: "CH", phone: 41 },
    { name: "Syrian Arab Republic", code: "SY", phone: 963 },
    { name: "Taiwan, Province of China", code: "TW", phone: 886 },
    { name: "Tajikistan", code: "TJ", phone: 992 },
    { name: "Tanzania, United Republic of", code: "TZ", phone: 255 },
    { name: "Thailand", code: "TH", phone: 66 },
    { name: "Timor-Leste", code: "TL", phone: 670 },
    { name: "Togo", code: "TG", phone: 228 },
    { name: "Tokelau", code: "TK", phone: 690 },
    { name: "Tonga", code: "TO", phone: 676 },
    { name: "Trinidad and Tobago", code: "TT", phone: 1868 },
    { name: "Tunisia", code: "TN", phone: 216 },
    { name: "Turkey", code: "TR", phone: 90 },
    { name: "Turkmenistan", code: "TM", phone: 7370 },
    { name: "Turks and Caicos Islands", code: "TC", phone: 1649 },
    { name: "Tuvalu", code: "TV", phone: 688 },
    { name: "Uganda", code: "UG", phone: 256 },
    { name: "Ukraine", code: "UA", phone: 380 },
    { name: "United Arab Emirates", code: "AE", phone: 971 },
    { name: "United Kingdom", code: "GB", phone: 44 },
    { name: "United States", code: "US", phone: 1 },
    { name: "United States Minor Outlying Islands", code: "UM", phone: 1 },
    { name: "Uruguay", code: "UY", phone: 598 },
    { name: "Uzbekistan", code: "UZ", phone: 998 },
    { name: "Vanuatu", code: "VU", phone: 678 },
    { name: "Venezuela", code: "VE", phone: 58 },
    { name: "Viet Nam", code: "VN", phone: 84 },
    { name: "Virgin Islands, British", code: "VG", phone: 1284 },
    { name: "Virgin Islands, U.s.", code: "VI", phone: 1340 },
    { name: "Wallis and Futuna", code: "WF", phone: 681 },
    { name: "Western Sahara", code: "EH", phone: 212 },
    { name: "Yemen", code: "YE", phone: 967 },
    { name: "Zambia", code: "ZM", phone: 260 },
    { name: "Zimbabwe", code: "ZW", phone: 263 }
];



var MD5 = function (string) {

   function RotateLeft(lValue, iShiftBits) {
           return (lValue<<iShiftBits) | (lValue>>>(32-iShiftBits));
   }

   function AddUnsigned(lX,lY) {
           var lX4,lY4,lX8,lY8,lResult;
           lX8 = (lX & 0x80000000);
           lY8 = (lY & 0x80000000);
           lX4 = (lX & 0x40000000);
           lY4 = (lY & 0x40000000);
           lResult = (lX & 0x3FFFFFFF)+(lY & 0x3FFFFFFF);
           if (lX4 & lY4) {
                   return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
           }
           if (lX4 | lY4) {
                   if (lResult & 0x40000000) {
                           return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
                   } else {
                           return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
                   }
           } else {
                   return (lResult ^ lX8 ^ lY8);
           }
   }

   function F(x,y,z) { return (x & y) | ((~x) & z); }
   function G(x,y,z) { return (x & z) | (y & (~z)); }
   function H(x,y,z) { return (x ^ y ^ z); }
   function I(x,y,z) { return (y ^ (x | (~z))); }

   function FF(a,b,c,d,x,s,ac) {
           a = AddUnsigned(a, AddUnsigned(AddUnsigned(F(b, c, d), x), ac));
           return AddUnsigned(RotateLeft(a, s), b);
   };

   function GG(a,b,c,d,x,s,ac) {
           a = AddUnsigned(a, AddUnsigned(AddUnsigned(G(b, c, d), x), ac));
           return AddUnsigned(RotateLeft(a, s), b);
   };

   function HH(a,b,c,d,x,s,ac) {
           a = AddUnsigned(a, AddUnsigned(AddUnsigned(H(b, c, d), x), ac));
           return AddUnsigned(RotateLeft(a, s), b);
   };

   function II(a,b,c,d,x,s,ac) {
           a = AddUnsigned(a, AddUnsigned(AddUnsigned(I(b, c, d), x), ac));
           return AddUnsigned(RotateLeft(a, s), b);
   };

   function ConvertToWordArray(string) {
           var lWordCount;
           var lMessageLength = string.length;
           var lNumberOfWords_temp1=lMessageLength + 8;
           var lNumberOfWords_temp2=(lNumberOfWords_temp1-(lNumberOfWords_temp1 % 64))/64;
           var lNumberOfWords = (lNumberOfWords_temp2+1)*16;
           var lWordArray=Array(lNumberOfWords-1);
           var lBytePosition = 0;
           var lByteCount = 0;
           while ( lByteCount < lMessageLength ) {
                   lWordCount = (lByteCount-(lByteCount % 4))/4;
                   lBytePosition = (lByteCount % 4)*8;
                   lWordArray[lWordCount] = (lWordArray[lWordCount] | (string.charCodeAt(lByteCount)<<lBytePosition));
                   lByteCount++;
           }
           lWordCount = (lByteCount-(lByteCount % 4))/4;
           lBytePosition = (lByteCount % 4)*8;
           lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80<<lBytePosition);
           lWordArray[lNumberOfWords-2] = lMessageLength<<3;
           lWordArray[lNumberOfWords-1] = lMessageLength>>>29;
           return lWordArray;
   };

   function WordToHex(lValue) {
           var WordToHexValue="",WordToHexValue_temp="",lByte,lCount;
           for (lCount = 0;lCount<=3;lCount++) {
                   lByte = (lValue>>>(lCount*8)) & 255;
                   WordToHexValue_temp = "0" + lByte.toString(16);
                   WordToHexValue = WordToHexValue + WordToHexValue_temp.substr(WordToHexValue_temp.length-2,2);
           }
           return WordToHexValue;
   };

   function Utf8Encode(string) {
           string = string.replace(/\r\n/g,"\n");
           var utftext = "";

           for (var n = 0; n < string.length; n++) {

                   var c = string.charCodeAt(n);

                   if (c < 128) {
                           utftext += String.fromCharCode(c);
                   }
                   else if((c > 127) && (c < 2048)) {
                           utftext += String.fromCharCode((c >> 6) | 192);
                           utftext += String.fromCharCode((c & 63) | 128);
                   }
                   else {
                           utftext += String.fromCharCode((c >> 12) | 224);
                           utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                           utftext += String.fromCharCode((c & 63) | 128);
                   }

           }

           return utftext;
   };

   var x=Array();
   var k,AA,BB,CC,DD,a,b,c,d;
   var S11=7, S12=12, S13=17, S14=22;
   var S21=5, S22=9 , S23=14, S24=20;
   var S31=4, S32=11, S33=16, S34=23;
   var S41=6, S42=10, S43=15, S44=21;

   string = Utf8Encode(string);

   x = ConvertToWordArray(string);

   a = 0x67452301; b = 0xEFCDAB89; c = 0x98BADCFE; d = 0x10325476;

   for (k=0;k<x.length;k+=16) {
           AA=a; BB=b; CC=c; DD=d;
           a=FF(a,b,c,d,x[k+0], S11,0xD76AA478);
           d=FF(d,a,b,c,x[k+1], S12,0xE8C7B756);
           c=FF(c,d,a,b,x[k+2], S13,0x242070DB);
           b=FF(b,c,d,a,x[k+3], S14,0xC1BDCEEE);
           a=FF(a,b,c,d,x[k+4], S11,0xF57C0FAF);
           d=FF(d,a,b,c,x[k+5], S12,0x4787C62A);
           c=FF(c,d,a,b,x[k+6], S13,0xA8304613);
           b=FF(b,c,d,a,x[k+7], S14,0xFD469501);
           a=FF(a,b,c,d,x[k+8], S11,0x698098D8);
           d=FF(d,a,b,c,x[k+9], S12,0x8B44F7AF);
           c=FF(c,d,a,b,x[k+10],S13,0xFFFF5BB1);
           b=FF(b,c,d,a,x[k+11],S14,0x895CD7BE);
           a=FF(a,b,c,d,x[k+12],S11,0x6B901122);
           d=FF(d,a,b,c,x[k+13],S12,0xFD987193);
           c=FF(c,d,a,b,x[k+14],S13,0xA679438E);
           b=FF(b,c,d,a,x[k+15],S14,0x49B40821);
           a=GG(a,b,c,d,x[k+1], S21,0xF61E2562);
           d=GG(d,a,b,c,x[k+6], S22,0xC040B340);
           c=GG(c,d,a,b,x[k+11],S23,0x265E5A51);
           b=GG(b,c,d,a,x[k+0], S24,0xE9B6C7AA);
           a=GG(a,b,c,d,x[k+5], S21,0xD62F105D);
           d=GG(d,a,b,c,x[k+10],S22,0x2441453);
           c=GG(c,d,a,b,x[k+15],S23,0xD8A1E681);
           b=GG(b,c,d,a,x[k+4], S24,0xE7D3FBC8);
           a=GG(a,b,c,d,x[k+9], S21,0x21E1CDE6);
           d=GG(d,a,b,c,x[k+14],S22,0xC33707D6);
           c=GG(c,d,a,b,x[k+3], S23,0xF4D50D87);
           b=GG(b,c,d,a,x[k+8], S24,0x455A14ED);
           a=GG(a,b,c,d,x[k+13],S21,0xA9E3E905);
           d=GG(d,a,b,c,x[k+2], S22,0xFCEFA3F8);
           c=GG(c,d,a,b,x[k+7], S23,0x676F02D9);
           b=GG(b,c,d,a,x[k+12],S24,0x8D2A4C8A);
           a=HH(a,b,c,d,x[k+5], S31,0xFFFA3942);
           d=HH(d,a,b,c,x[k+8], S32,0x8771F681);
           c=HH(c,d,a,b,x[k+11],S33,0x6D9D6122);
           b=HH(b,c,d,a,x[k+14],S34,0xFDE5380C);
           a=HH(a,b,c,d,x[k+1], S31,0xA4BEEA44);
           d=HH(d,a,b,c,x[k+4], S32,0x4BDECFA9);
           c=HH(c,d,a,b,x[k+7], S33,0xF6BB4B60);
           b=HH(b,c,d,a,x[k+10],S34,0xBEBFBC70);
           a=HH(a,b,c,d,x[k+13],S31,0x289B7EC6);
           d=HH(d,a,b,c,x[k+0], S32,0xEAA127FA);
           c=HH(c,d,a,b,x[k+3], S33,0xD4EF3085);
           b=HH(b,c,d,a,x[k+6], S34,0x4881D05);
           a=HH(a,b,c,d,x[k+9], S31,0xD9D4D039);
           d=HH(d,a,b,c,x[k+12],S32,0xE6DB99E5);
           c=HH(c,d,a,b,x[k+15],S33,0x1FA27CF8);
           b=HH(b,c,d,a,x[k+2], S34,0xC4AC5665);
           a=II(a,b,c,d,x[k+0], S41,0xF4292244);
           d=II(d,a,b,c,x[k+7], S42,0x432AFF97);
           c=II(c,d,a,b,x[k+14],S43,0xAB9423A7);
           b=II(b,c,d,a,x[k+5], S44,0xFC93A039);
           a=II(a,b,c,d,x[k+12],S41,0x655B59C3);
           d=II(d,a,b,c,x[k+3], S42,0x8F0CCC92);
           c=II(c,d,a,b,x[k+10],S43,0xFFEFF47D);
           b=II(b,c,d,a,x[k+1], S44,0x85845DD1);
           a=II(a,b,c,d,x[k+8], S41,0x6FA87E4F);
           d=II(d,a,b,c,x[k+15],S42,0xFE2CE6E0);
           c=II(c,d,a,b,x[k+6], S43,0xA3014314);
           b=II(b,c,d,a,x[k+13],S44,0x4E0811A1);
           a=II(a,b,c,d,x[k+4], S41,0xF7537E82);
           d=II(d,a,b,c,x[k+11],S42,0xBD3AF235);
           c=II(c,d,a,b,x[k+2], S43,0x2AD7D2BB);
           b=II(b,c,d,a,x[k+9], S44,0xEB86D391);
           a=AddUnsigned(a,AA);
           b=AddUnsigned(b,BB);
           c=AddUnsigned(c,CC);
           d=AddUnsigned(d,DD);
   		}

   	var temp = WordToHex(a)+WordToHex(b)+WordToHex(c)+WordToHex(d);

   	return temp.toLowerCase();
}