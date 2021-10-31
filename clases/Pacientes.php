<?php
require_once 'conexion/Conexion.php';
require_once 'Respuestas.php';

class Pacientes extends Conexion{

    private $table = "pacientes";
    private $pacienteid = "";
    private $dni = "";
    private $nombre = "";
    private $direccion = "";
    private $codigoPostal = "";
    private $genero = "";
    private $telefono = "";
    private $fechaNacimiento = "0000-00-00";
    private $correo = "";
    private $token = "";
    private $imagen = "";

    /**
     * Mostrar lista de pacientes por pagina Metodo GET
     *
     * @param int $pagina Numero de pagina a mostrar
     * @return void
     */
    public function listaPacientes($pagina = 0){
        $inicio = 0;
        $cantidad = 100;
        if($pagina > 1){
            $inicio = ($cantidad * ($pagina -1)) + 1;
            $cantidad = $cantidad * $pagina;
        }
        $query = "SELECT PacienteId, Nombre, DNI, Telefono, Correo FROM " . $this->table . " limit $inicio, $cantidad";
        $datos = parent::obtenerDatos($query);
        return $datos;
    }

    /**
     * Seleccionar paciente por identificador Metodo GET
     *
     * @param int $id Identificador paciente
     * @return void
     */
    public function obtenerPaciente(int $id){
        $query = "SELECT * FROM " . $this->table . " WHERE PacienteId = '$id'";
        return parent::obtenerDatos($query);
    }

    /**
     * API Metodo POST Guardar paciente
     *
     * @param string $json Datos del paciente a insertar
     * @return void
     */
    public function post(string $json){
        $_respuestas = new Respuestas;
        $datos = json_decode($json, true);
        // si no envian el token
        if(!isset($datos['token'])){
            return $_respuestas->error_401();
        }else{
            $this->token = $datos['token'];
            // validamos token
            $arrayToken = $this->buscarToken();
            // si el token enviado es correcto
            if($arrayToken){
                // si no existen estos tres campos en los datos recibidos
                if(!isset($datos['nombre']) || !isset($datos['dni']) || !isset($datos['correo'])){
                    return $_respuestas->error_400();
                // datos recibidos correctos
                }else{
                    $this->nombre = $datos['nombre'];
                    $this->dni = $datos['dni'];
                    $this->correo = $datos['correo'];
                    if(isset($datos['telefono'])){$this->telefono = $datos['telefono'];}
                    if(isset($datos['direccion'])){$this->direccion = $datos['direccion'];}
                    if(isset($datos['codigoPostal'])){$this->codigoPostal = $datos['codigoPostal'];}
                    if(isset($datos['genero'])){$this->genero = $datos['genero'];}
                    if(isset($datos['fechaNacimiento'])){$this->fechaNacimiento = $datos['fechaNacimiento'];}

                    /* INICIA IMAGEN EN BASE64 */
                    if(isset($datos["imagen"])){
                        $resp = $this->procesarImagen($datos["imagen"]);
                        $this->imagen = $resp;
                    }
                    /* TERMINA IMAGEN EN BASE64 */

                    $resp = $this->insertarPaciente();
                    // si la respuesta nos devuelve un valor
                    if($resp){
                        // devolvemos el id del paciente insertado
                        $respuesta = $_respuestas->response;
                        $respuesta["result"] = array(
                            "pacienteId" => $resp
                        );
                        return $respuesta;
                    // la respuesta es 0 - no devolvio filas afectadas
                    }else{
                        return $_respuestas->error_500();
                    }
                }                
            // el token enviado no existe en la BD
            }else{
                return $_respuestas->error_401("El token recibido es invalido");
            }
        }        
    }

    /**
     * Imagen en base 64 decodificarla para su almacenamiento en disco
     *
     * @param mixed $img Imagen en base 64
     * @return string Direccion de la imagen
     */
    private function procesarImagen($img){
        // obtenemos la direccion donde almacenar las imagenes
        $direccion = dirname(__DIR__) . "\public\imagenes\\";
        // quitamos ;base64 de la cadena $img
        $partes = explode(";base64,",$img);
        /* print_r($partes); */
        $extension = explode('/',mime_content_type($img))[1];
        $imagen_base64 = base64_decode($partes[1]);
        $file = $direccion . uniqid() . "." . $extension;
        /* print_r($file); */
        file_put_contents($file, $imagen_base64);
        $nuevadireccion = str_replace('\\','/',$file);
        return $nuevadireccion;
    }

    /**
     * Insertar paciente por metodo POST
     *
     * @return int Retornar id o 0
     */
    private function insertarPaciente(){
        $query = "INSERT INTO " . $this->table . " (DNI,Nombre,Direccion,CodigoPostal,Telefono,Genero,FechaNacimiento,Correo,Imagen) VALUES ('" . $this->dni . "','" . $this->nombre . "','" . $this->direccion ."','" . $this->codigoPostal . "','"  . $this->telefono . "','" . $this->genero . "','" . $this->fechaNacimiento . "','" . $this->correo . "','" . $this->imagen . "')";
        // hacer el insert y devolver el ID con metodo nonQueryId
        $resp = parent::nonQueryId($query);
        if($resp){
            return $resp;
        }else{
            return 0;
        }

    }

    /**
     * API Metodo PUT actualizar paciente
     *
     * @param string $json Datos paciente a actualizar
     * @return void
     */
    public function put(string $json){
        $_respuestas = new respuestas;
        $datos = json_decode($json, true);
        // si no envian el token
        if(!isset($datos['token'])){
            return $_respuestas->error_401();
        }else{
            $this->token = $datos['token'];
            // validamos token
            $arrayToken = $this->buscarToken();
            // si el token enviado es correcto
            if($arrayToken){
                // si no existen estos tres campos en los datos recibidos
                if(!isset($datos['pacienteId'])){
                    return $_respuestas->error_400();
                // los datos recibidos son correctos
                }else{
                    $this->pacienteid = $datos['pacienteId'];
                    if(isset($datos['nombre'])) { $this->nombre = $datos['nombre']; }
                    if(isset($datos['dni'])) { $this->dni = $datos['dni']; }
                    if(isset($datos['correo'])) { $this->correo = $datos['correo']; }
                    if(isset($datos['telefono'])){$this->telefono = $datos['telefono'];}
                    if(isset($datos['direccion'])){$this->direccion = $datos['direccion'];}
                    if(isset($datos['codigoPostal'])){$this->codigoPostal = $datos['codigoPostal'];}
                    if(isset($datos['genero'])){$this->genero = $datos['genero'];}
                    if(isset($datos['fechaNacimiento'])){$this->fechaNacimiento = $datos['fechaNacimiento'];}
                    $resp = $this->modificarPaciente();
                    // si la respuesta nos devuelve un valor
                    if($resp){    
                        $respuesta = $_respuestas->response;
                        $respuesta["result"] = array(
                            "pacienteId" => $this->pacienteid
                        );
                        return $respuesta;
                    // la respuesta es 0 - no devolvio filas afectadas
                    }else{                
                        return $_respuestas->error_500();
                    }
                }
            // el token enviado no existe en la BD
            }else{
                return $_respuestas->error_401("El token recibido es invalido");
            }
        }
    }

    /**
     * Modificar paciente por metodo PUT
     *
     * @return int Retornor numero de filas modificadas
     */
    private function modificarPaciente(){
        $query = "UPDATE " . $this->table . " SET Nombre ='" . $this->nombre . "',Direccion = '" . $this->direccion . "', DNI = '" . $this->dni . "', CodigoPostal = '" . $this->codigoPostal . "', Telefono = '" . $this->telefono . "', Genero = '" . $this->genero . "', FechaNacimiento = '" . $this->fechaNacimiento . "', Correo = '" . $this->correo . "' WHERE PacienteId = '" . $this->pacienteid . "'";
        $resp = parent::nonQuery($query);
        if($resp >= 1){
            return $resp;
        }else{
            return 0;
        }
    }

    /**
     * API Metodo DELETE Eliminar paciente
     *
     * @param string $json
     * @return void
     */
    public function delete(string $json){
        $_respuestas = new respuestas;
        $datos = json_decode($json, true);
        // si no envian el token
        if(!isset($datos['token'])){
            return $_respuestas->error_401();
        }else{
            $this->token = $datos['token'];
            // validamos token
            $arrayToken = $this->buscarToken();
            // si el token enviado es correcto
            if($arrayToken){
                // si no envian el id del paciente
                if(!isset($datos['pacienteId'])){
                    return $_respuestas->error_400();
                // enviaron id paciente
                }else{
                    $this->pacienteid = $datos['pacienteId']; 
                    $resp = $this->eliminarPaciente();
                    // si la respuesta nos devuelve fila afectada (1)
                    if($resp){                        
                        $respuesta = $_respuestas->response;
                        $respuesta["result"] = array(
                            "pacienteId" => $this->pacienteid
                        );
                        return $respuesta;
                    // la respuesta es 0 - no devolvio filas afectadas
                    }else{                
                        return $_respuestas->error_500();
                    }
                }  
            // el token enviado no existe en la BD
            }else{
                return $_respuestas->error_401("El token recibido es invalido");
            }
        }
    }

    /**
     * Funcion para eliminar paciente segun ID
     *
     * @return int Numero de filas afectadas o 0
     */
    private function eliminarPaciente(){
        $query = "DELETE FROM " . $this->table . " WHERE PacienteId= '" . $this->pacienteid . "'";
        $resp = parent::nonQuery($query);
        if($resp >= 1){
            return $resp;
        }else{
            return 0;
        }
    }

    /**
     * Buscar token para validar usuario
     *
     * @return int Devolver fila afectada por query o 0 si no hay registro
     */
    private function buscarToken(){
        $query = "SELECT TokenId, UsuarioId, Estado FROM usuarios_token WHERE Token = '" . $this->token . "' AND Estado = 'Activo'";
        $resp = parent::obtenerDatos($query);
        if($resp){
            return $resp;
        }else{
            return 0;
        }
    }

    /**
     * Actualizar token (fecha)
     *
     * @param string $tokenid
     * @return int Devuelve fila afectada o 0 si no encontro registro
     */
    private function actualizarToken(string $tokenid){
        $date = date("Y-m-d H:i");
        $query = "UPDATE usuarios_token SET Fecha = '$date' WHERE TokenId = '$tokenid'";
        $resp = parent::nonQuery($query);
        if($resp >= 1){
            return $resp;
        }else{
            return 0;
        }
    }

}