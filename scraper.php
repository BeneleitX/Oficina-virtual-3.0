<?php

function get_content($url){
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_URL, $url);
      $data = curl_exec($ch);
      curl_close($ch);
      return $data;
}

$url   = "https://glass-essence.com/product/{$_GET[ "producto" ]}/";
$html  = get_content( $url );
$doc   = new DOMDocument();
$d     = $doc->loadHTML( $html );
$forms = $doc->getElementsByTagName('form');
$final = [];

foreach ($forms as $f ) {
    $a = $f->getAttribute('data-product_variations');
    
    if( strlen($a) ){
        $json = json_decode( $a );
        
        foreach($json as $p){
            
            $final[] = [
                "presentacion" => $p->attributes->attribute_presentacion,
                "en_stock" => $p->is_in_stock ? true : false,
                "precio" => $p->display_price,
                "disponible" => $p->is_purchasable ? true : false,
                "cantidad" => $p->max_qty,
                "sku" => strtoupper( $p->sku ),
                "dimensiones" => $p->dimensions->length."x".$p->dimensions->width."x".$p->dimensions->height,
                "peso" => $p->weight * 1000
            ];
        }
    }
}

echo json_encode($final);
