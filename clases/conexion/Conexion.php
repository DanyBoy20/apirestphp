<?php

class Conexion{

    private $server;
    private $user;
    private $password;
    private $database;
    private $port;
    private $conexion;

    function __construct(){

        $listadatos = $this->datosConexion();
        foreach ($listadatos as $key => $value) {
            $this->server = $value['server'];
            $this->user = $value['user'];
            $this->password = $value['password'];
            $this->database = $value['database'];
            $this->port = $value['port'];
        }
        $this->conexion = new mysqli($this->server, $this->user, $this->password, $this->database, $this->port);

        if($this->conexion->connect_errno){
            echo "No se pudo conectar";
            die();
        }
    }

    /**
     * Leer el archivo que contiene los datos de conexion y devolverlos
     * en un arreglo
     *
     * @return array Los datos de conexion
     */
    private function datosConexion() : array{
        $direccion = dirname(__FILE__);
        $jsondata = file_get_contents($direccion . "/" . "config");
        return json_decode($jsondata, true);
    }

    /**
     * Convertir a UTF8 lo que la BD nos devuelve (acentos etcetera)
     *
     * @param mixed $array  El arreglo que viene de la consulta a la BD
     * @return void
     */
    private function convertirUTF8($array){
        // pasamos el parametro por referencia
        // se declara con el simbolo & : &$variable
        array_walk_recursive($array, function(&$item, $key){
            // si mb_detect no detecta un caracter raro
            // en cada elemento del array
            // entonces el item lo convierte a UTF8
            if(!mb_detect_encoding($item, 'utf-8', true)){
                // si hay caracter raro, lo pasamos a UTF8
                $item = utf8_encode($item); 
            }
        });
        return $array; // retornamos los datos ya convertidos en UTF8
    }

    /**
     * Para obtener datos de la BD
     *
     * @param mixed $sqlstr La consulta (SELECT) a la BD
     * @return void
     */
    public function obtenerDatos($sqlstr){
        $results = $this->conexion->query($sqlstr);
        $resultArray = array();
        foreach ($results as $key) {
            // [] es como si hicieramos un psuh a un array
            // ingresaremos una fila/registro en cada vuelta
            $resultArray[] = $key;
        }
        return $this->convertirUTF8($resultArray);
    }

    /**
     * Metodo para los queries que no sean SELECT
     * Guardar, Eliminar, Editar
     *
     * @param mixed $sqlstr La query (instruccion) a ejecutar
     * @return void
     */
    protected function nonQuery($sqlstr){
        $results = $this->conexion->query($sqlstr);
        return $this->conexion->affected_rows;
    }

    /**
     * Metodo para INSERT - insertar registro y devolver
     * el ultimo ID insertado
     *
     * @param mixed $sqlstr Query (Insert) a ejecutar
     * @return int
     */
    protected function nonQueryId($sqlstr) : int{
        $results = $this->conexion->query($sqlstr);
        $filas = $this->conexion->affected_rows;
        // si la base de datos nos devuelve 1 fila, dos filas afectadas etc.
        if($filas >= 1){
            // retornamos el id que se inserto
            return $this->conexion->insert_id;
        }else{
            // si no inserto nada, devolver 0
            return 0;
        }
    }

    /**
     * Encriptar la conraseña recibida, protected para utilizarse
     * en clases que hereden de conexion
     *
     * @param mixed $string La contraseña a encriptar
     * @return string
     */
    protected function encriptar($string){
        return md5($string);
    }




}