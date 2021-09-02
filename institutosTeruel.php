<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$ciudad = "teruel";

$entrada = $ciudad.".txt";
$salida = $ciudad.".csv";

$archivo = fopen($entrada,'r');

$c=1;

while ($linea = fgets($archivo)) {
    if(   in_array($c, array(5,6,7))   ){
        if($c == 7){
            $c=1;
        }else{
            $c++;
        }
       
        continue;
    }
    if($c==2){
        $linea = substr($linea, 2, strlen($linea));
    }
    if($c==3){
        $linea = substr($linea, 12, strlen($linea));
    }
    if($c==4){
        $linea = substr($linea, 9, strlen($linea));
    }
    $resultados[] = trim($linea);
    $c++;
}

fclose($archivo);

$institutos = array_chunk($resultados, 4);

$fp = fopen($salida, 'w');

$cabecera = ["Instituto", "Población", "Nº alumnos", "Horario", "Distancia", "Duración"];
fputcsv($fp, $cabecera);

foreach ($institutos as $instituto) {
    $destino = urlencode($instituto[0]." ".$ciudad);
    $trayecto = duracionTrayecto($destino);
    $instituto[4] = $trayecto["distancia"];
    $instituto[5] = str_replace(["hour", "mins"],["horas", "minutos"],$trayecto["duracion"]);
    print_r($instituto);
    fputcsv($fp, $instituto);
}

fclose($fp);

echo "Archivo generado con éxito: ".$salida;

function duracionTrayecto($destino){

    $origen = "Amadeus%20Mozart%2017%2C%20Zaragoza"; // Amadeus Mozart 17, Zaragoza
    $key = "[REDACTED]";

    $url = "https://maps.googleapis.com/maps/api/directions/json?origin=".$origen."&destination=".$destino."&key=".$key;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_HEADER, 0); 
    $data = curl_exec($ch); 
    curl_close($ch); 
    
    $contenido = json_decode($data);
    $trayecto = array(
        "distancia" => $contenido->routes[0]->legs[0]->distance->text,
        "duracion" => $contenido->routes[0]->legs[0]->duration->text
    );

    return $trayecto;
}