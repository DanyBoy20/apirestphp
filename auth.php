<?php
require_once 'clases/Auth.php';
require_once 'clases/Respuestas.php';

$_auth = new Auth;
$_respuestas = new Respuestas;

if($_SERVER['REQUEST_METHOD'] == "POST"){
    // Recibir datos
    $postBody = file_get_contents("php://input");
    // Enviar datos al manejador
    $datosArray = $_auth->login($postBody);

    // devolver respuesta
    header('Content-Type: application/json');

    // si en datosArray hay un errorId
    if(isset($datosArray["result"]["error_id"])){
        $responseCode = $datosArray["result"]["error_id"];
        http_response_code($responseCode);
    // no hay error
    }else{
        http_response_code(200);
    }
    // enviamos respuesta
    echo json_encode($datosArray);

// si el metodo en la autenticacion no es POST
}else{
    header('Content-Type: application/json');
    $datosArray = $_respuestas->error_405();
    echo json_encode($datosArray);
}