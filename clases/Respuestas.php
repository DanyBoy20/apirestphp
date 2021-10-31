<?php

class Respuestas{

    public $response = [
        "status" => "ok",
        "result" => "array()"
    ];

    /**
     * Dara el error correspondientes a los metodos no permitidos por la API
     *
     * @return array
     */
    public function error_405(){
        $this->response['status'] = "error";
        $this->response['result'] = array(
            "error_id" => "405",
            "error_msg" => "Metodo no permitido"
        );
        return $this->response;
    }

    /**
     * Error para cuando los datos son incorrectos
     *
     * @param string $valor Mensaje de error
     * @return array
     */
    public function error_200($valor = "Datos incorrectos"){
        $this->response['status'] = "error";
        $this->response['result'] = array(
            "error_id" => "200",
            "error_msg" => $valor
        );
        return $this->response;
    }

    /**
     * Error en datos: formato o incompletos
     *
     * @return array
     */
    public function error_400(){
        $this->response['status'] = "error";
        $this->response['result'] = array(
            "error_id" => "400",
            "error_msg" => "Datos enviados incompletos o con formato incorrecto"
        );
        return $this->response;
    }

    /**
     * error_500
     *
     * @param string $valor
     * @return void
     */
    public function error_500($valor = "Error interno del servidor"){
        $this->response['status'] = "error";
        $this->response['result'] = array(
            "error_id" => "500",
            "error_msg" => $valor
        );
        return $this->response;
    }

    public function error_401($valor = "No autorizado"){
        $this->response['status'] = "error";
        $this->response['result'] = array(
            "error_id" => "401",
            "error_msg" => $valor
        );
        return $this->response;
    }
    
}