<?php
require_once 'clases/Respuestas.php';
require_once 'clases/Pacientes.php';

$_respuestas = new Respuestas;
$_pacientes = new Pacientes;

/**
 * SELECCIONAR DATOS
 */
if($_SERVER['REQUEST_METHOD'] == "GET"){
    // si el get solicita pagina
    if(isset($_GET["page"])){
        $pagina = $_GET["page"];
        $listapacientes = $_pacientes->listaPacientes($pagina);
        header("Content-Type: application/json");
        echo json_encode($listapacientes);
        http_response_code(200);
    // si el GET solicita paciento por ID
    }else if(isset($_GET["id"])){
        $pacienteid = $_GET["id"];
        $datosPaciente = $_pacientes->obtenerPaciente($pacienteid);
        header("Content-Type: application/json");
        echo json_encode($datosPaciente);
        http_response_code(200);
    }    

/**
 * GUARDAR DATOS
 */
}else if($_SERVER['REQUEST_METHOD'] == "POST"){
    // Recibir datos
    $postBody = file_get_contents("php://input");
    // mandamos los datos al manejador
    $datosArray = $_pacientes->post($postBody);
    // devolvemos respuesta
    header('Content-Type: application/json');
    // si en el array hay un campo "error_id"
    if(isset($datosArray["result"]["error_id"])){
        $responseCode = $datosArray["result"]["error_id"];
        http_response_code($responseCode);
    }else{
        http_response_code(200);
    }
    echo json_encode($datosArray);

/**
 * ACTUALIZAR DATOS
 */
}else if($_SERVER['REQUEST_METHOD'] == "PUT"){
    // Recibir datos
    $postBody = file_get_contents("php://input");
    // enviamos los datos al manejador
    $datosArray = $_pacientes->put($postBody);
    // devolvemos respuesta
    header('Content-Type: application/json');
    // si en el array hay un campo "error_id"
    if(isset($datosArray["result"]["error_id"])){
        $responseCode = $datosArray["result"]["error_id"];
        http_response_code($responseCode);
    }else{
        http_response_code(200);
    }
    echo json_encode($datosArray);
    

/**
 * ELIMINAR DATOS
 */
}else if($_SERVER['REQUEST_METHOD'] == "DELETE"){

    $headers = getallheaders();
    /* print_r($headers); */
    if(isset($headers['token']) && isset($headers['pacienteid'])){
        //recibimos los datos enviados por el header
        $send = [
            "token" => $headers['token'],
            "pacienteId" =>$headers['pacienteid']
        ];
        $postBody = json_encode($send);
    }else{
        //recibimos los datos enviados
        $postBody = file_get_contents("php://input");
    }
    
    // enviamos los datos al manejador
    $datosArray = $_pacientes->delete($postBody);
    // devolvemos respuesta
    header('Content-Type: application/json');
    if(isset($datosArray["result"]["error_id"])){
        $responseCode = $datosArray["result"]["error_id"];
        http_response_code($responseCode);
    }else{
        http_response_code(200);
    }
    echo json_encode($datosArray);
    

/**
 * CUALQUIER METODO DIFERENTE A GET|POST|PUT|DELETE DARA ERROR
 */
}else{
    header('Content-Type: application/json');
    $datosArray = $_respuestas->error_405();
    echo json_encode($datosArray);
}