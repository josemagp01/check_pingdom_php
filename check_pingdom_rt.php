<?php

// URL de la API de Pingdom
$pingdom_api_url = '<URL_API_PINGDOM>';

// Umbral tiempo de respuesta
$ok_response_time = 5000;

// Obtener el nombre del check a filtrar
$check_name = isset($argv[1]) ? $argv[1] : null;

// Obtener el nombre del check a filtrar de los argumentos de línea de comandos (ARG1 en Nagios)
if (isset($argv[1])) {
    $check_name = $argv[1];
} else {
    $check_name = null;
}

// Construir la solicitud de API
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $pingdom_api_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
// Se define el metodo de autorizacion mediante token en la cabezera
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("<Authorization_mode>"));

// Enviar la solicitud de API y obtener la respuesta
$response = curl_exec($curl);
$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

// Verificar si la solicitud de API fue exitosa
if ($status == 200) {

// Decodificar la respuesta de JSON a un array
  $result = json_decode($response, true);

// Obtener la lista de chequeos
  $checksList = $result['checks'];

// Recorrer la lista de chequeos
  foreach ($checksList as $check) {
    if ($check_name && $check['name'] != $check_name) {
        continue;
    }

// Obtener el estado actual de la URL
  $check_status = $check['status'];

// Obtener el tiempo de respuesta de la URL
  $response_time = $check['lastresponsetime'];

// Determinar el estado en base al estado actual de la URL
  if ($check_status == 'up') {
      if ($response_time <= $ok_response_time) {
          $status = 'OK';
          $exit_code = 0;
      } else {
          $status = 'WARNING';
          $exit_code = 1;
      }
  } else {
      $status = 'CRITICAL';
      $exit_code = 2;
  }

// Mostrar el resultado
  echo "$status: " . $check['name'] . " is " . "$status | response_time=$response_time"."ms \n";
}

// Salir con el código de estado apropiado
  exit($exit_code);
} else {
// Mostrar un mensaje de error
  echo "Error: Unable to retrieve data from the Pingdom API\n";
}
?>
