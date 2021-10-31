<?php
require_once 'conexion/Conexion.php';
require_once 'Respuestas.php';

class Auth extends Conexion{

    /**
     * Autenticar login 
     *
     * @param string $json Datos de acceso: usuario y password
     * @return void
     */
    public function login(string $json){

        $_respuestas = new Respuestas;
        $datos = json_decode($json, true); // con true lo hace array asociativo
        if(!isset($datos['usuario']) || !isset($datos["password"])){
            // si no existen los campos (usuario y password)
            // o hay error en el formato
            return $_respuestas->error_400();

        }else{
            // si existen los datos, guardamos lo que nos envian en variables
            $usuario = $datos['usuario'];
            $password = $datos['password'];
            $password = parent::encriptar($password);
            // hacemos consulta de usuarios a la BD con esos datos
            $datos = $this->obtenerDatosUsuario($usuario);
            // si la consulta nos devolvio un usuario existente
            if($datos){
                // si la contraseña del usuario es igual a la devuelta por BD
                if($password == $datos[0]['Password']){
                    // si el estado del usuario en BD es Activo
                    if($datos[0]['Estado'] == "Activo"){
                        // creamos el token (y lo guardamos)
                        $verificar = $this->insertarToken($datos[0]['UsuarioId']);
                        // si la consulta insertarToken nos devolvio 1
                        if($verificar){
                            $result = $_respuestas->response;
                            $result["result"] = array(
                                "token" => $verificar
                            );
                            return $result;

                        // error al guardar token
                        }else{
                            return $_respuestas->error_500("Error interno, No hemos podido guardar");

                        }

                    
                    // el usuario devuelto por la BD esta inactivo
                    }else{
                        return $_respuestas->error_200("El usuario esta inactivo");

                    }

                // la contraseña enviada no es igual a la devuelta por la BD
                }else{
                    return $_respuestas->error_200("El password es invalido");

                }

            // El usuario no existe en la BD
            }else{
                return $_respuestas->error_200();
            }


        }

    }

    /**
     * Buscar y obtener los datos del usuario segun un correo
     *
     * @param string $correo
     * @return array
     */
    private function obtenerDatosUsuario(string $correo){
        $query = "SELECT UsuarioId,Password,Estado FROM usuarios WHERE Usuario = '$correo'";
        // al no instanciar la clase conexion, usamos parent para 
        // obtener el metodo, esta clase ya extiende de conexion
        $datos = parent::obtenerDatos($query);
        if(isset($datos[0]["UsuarioId"])){
            return $datos;
        }else{
            return 0;
        }

    }

    /**
     * Creare insertar el token en la BD
     *
     * @param int $usuarioid El id del susuario en la BD
     * @return string El token
     */
    private function insertarToken(int $usuarioid){
        $val = true;
        // bin2hex devuelve un string hexadecimal ("1" al "9" y "A" a la "F")
        // openssl genera una cadena de bytes pseudo-aleatoria 
        // parametros: cantidad de bytes (en este caso 16), y la variable
        // $val con valor verdadero (no funciona si pongo true directamente)
        $token = bin2hex(openssl_random_pseudo_bytes(16,$val));
        $date = date("Y-m-d H:i");
        $estado = "Activo";
        $query = "INSERT INTO usuarios_token (UsuarioId, Token, Estado, Fecha)VALUES('$usuarioid', '$token', '$estado', '$date')";
        $verifica = parent::nonQuery($query);
        if($verifica){
            return $token;
        }else{
            return 0;
        }

    }

}