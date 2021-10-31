<?php
require_once 'conexion/Conexion.php';

class Token extends Conexion{

    function actualizarTokens($fecha){

        $query = "UPDATE usuarios_token SET Estado = 'Inactivo' WHERE  Fecha < '$fecha' AND Estado = 'Activo'";
        $verificar = parent::nonQuery($query);
        if($verificar > 0){
            /* $this->escribirEntrada($verificar); */
            return $verificar;
        }else{
            return 0;
        }

    }


    // function crearTXT($direccion){

    // }


    // function escribirEntrada($registros){

    // }


    // function escribirTXT($direccion, $registros){

    // }


}