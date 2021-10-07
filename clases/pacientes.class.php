<?php
    require_once "conexion/conexion.php";
    require_once "respuestas.class.php";

    class pacientes extends conexion {

        private $table           = "`pacientes`";
        private $pacienteId      = "";
        private $dni             = "";
        private $nombre          = "";
        private $direccion       = "";
        private $codigoPostal    = "";
        private $genero          = "";
        private $telefono        = "";
        private $fechaNacimiento = "0000-00-00";
        private $correo          = "";
        private $token           = "";
        // 912bc00f049ac8464472020c5cd06759
        private $imagen          = "";

        /**
         * Muestra todos los registros
         */
        public function listaPacientes($pagina = 1) {
            $inicio   = 0;
            $cantidad = 100;
            if($pagina > 1) {
                $inicio = ($cantidad * ($pagina - 1)) + 1;
                $cantidad = $cantidad * $pagina;
            }

            $query = "SELECT `PacienteId`, `Nombre`, `DNI`, `Telefono`, `Correo` FROM " . $this->table . " LIMIT $inicio, $cantidad;";
            //print_r($query);
            $datos = parent::obtenerDatos($query);
            return ($datos);
        }

        /**
         * Muestra un registro especifico
         */
        public function obtenerPaciente($id) {
            $query = "SELECT * FROM " . $this->table . " WHERE `PacienteId` = '$id';";
            return parent::obtenerDatos($query);
        }

        public function post($json) {
            /**
             * Formato JSON
             * {
             *     "dni": "adasdas",
             *     "nombre": "José Aleman",
             *     "correo": "jaleman@gmail.com"
             * }
             * 
             * o
             * 
             * {
             *      "dni":"E0000045",
             *      "nombre": "Pepito Gomez",
             *      "correo": "pepito@gmail.com",
             *      "genero": "F",
             *      "fechaNacimiento": "1989-02-03",
             *      "codigoPostal": "20006",
             *      "telefono": "633665656",
             *      "direccion": "Calle Easo"
             *  }
             * 
             * o
             * 
             * {
             *    "dni":"H55555",
             *    "nombre": "Maria José Gomez",
             *    "correo": "pepito@gmail.com",
             *    "genero": "F",
             *    "fechaNacimiento": "1989-02-03",
             *    "codigoPostal": "20006",
             *    "telefono": "633665656",
             *    "direccion": "Calle Easo"
             * }
             */
            $_respuestas = new respuestas;
            $datos       = json_decode($json, true);
            
            /**
             * Token
             */
            if(!isset($datos['token'])) {
                return $_respuestas->error_401();
            } else {
                $this->token = $datos['token'];
                $arrayToken = $this->buscarToken();
                if($arrayToken) {

                    if(!isset($datos['nombre']) || !isset($datos['dni']) || !isset($datos['correo'])) {
                        return $_respuestas->error_400();
                    } else {
                        $this->nombre = $datos["nombre"];
                        $this->dni    = $datos["dni"];
                        $this->correo = $datos["correo"];
        
                        if(isset($datos["telefono"]))        { $this->telefono = $datos["telefono"]; }
                        if(isset($datos["direccion"]))       { $this->direccion = $datos["direccion"]; }
                        if(isset($datos["codigoPostal"]))    { $this->codigoPostal = $datos["codigoPostal"]; }
                        if(isset($datos["genero"]))          { $this->genero = $datos["genero"]; }
                        if(isset($datos["fechaNacimiento"])) { $this->fechaNacimiento = $datos["fechaNacimiento"]; }
                        
                        if(isset($datos['imagen'])) {
                            //echo "Hola imágen.";
                            $resp         = $this->procesarImagen($datos['imagen']);
                            $this->imagen = $resp; 
                        }
                        
                        $resp = $this->insertarPaciente();
                        if($resp) {
                            $respuesta = $_respuestas->response;
                            $respuesta["result"] = array(
                                "pacienteId" => $resp
                            );
                            return $respuesta;
                        } else {
                            return $_respuestas->error_500();
                        }
                    }

                } else {
                    return $_respuestas->error_400("El token que envio es invalido o ha caducado.");
                }
            }

        }

        private function procesarImagen($img) {
            $direccion      = dirname(__DIR__) . "\public\imagenes\\";
            //echo $direccion;
            $partes         = explode(";base64,", $img);
            //print_r($partes);
            $extension      = explode('/', mime_content_type($img))[1];
            $imagen_base64  = base64_decode($partes[1]);
            $file           = $direccion . uniqid() . "." . $extension;
            //print_r($file);
            file_put_contents($file, $imagen_base64);
            $nuevadireccion = str_replace('\\', '/', $file);
            return $nuevadireccion;
        }

        private function insertarPaciente() {
            $query = "INSERT INTO " . $this->table . " (`DNI`, `Nombre`, `Direccion`, `CodigoPostal`, `Telefono`, `Genero`, `FechaNacimiento`, `Correo`, `Imagen`) VALUES ('" . $this->dni . "', '" . $this->nombre . "', '" . $this->direccion . "', '" . $this->codigoPostal . "', '" . $this->telefono . "', '" . $this->genero . "', '" . $this->fechaNacimiento . "', '" . $this->correo . "', '" . $this->imagen . "');";
            //print_r($query);
            $resp  = parent::nonQueryId($query);
            if($resp) {
                return $resp;
            } else {
                return 0;
            }
        }

        public function put($json) {
            /**
             * Formato JSON
             * {
             *     "dni": "adasdas",
             *     "nombre": "José Aleman",
             *     "correo": "jaleman@gmail.com"
             * }
             * 
             * o
             * 
             * {
             *      "dni":"E0000045",
             *      "nombre": "Pepito Gomez",
             *      "correo": "pepito@gmail.com",
             *      "genero": "F",
             *      "fechaNacimiento": "1989-02-03",
             *      "codigoPostal": "20006",
             *      "telefono": "633665656",
             *      "direccion": "Calle Easo"
             *  }
             * 
             * o
             * 
             * {
             *    "dni":"H55555",
             *    "nombre": "Maria José Gomez",
             *    "correo": "pepito@gmail.com",
             *    "genero": "F",
             *    "fechaNacimiento": "1989-02-03",
             *    "codigoPostal": "20006",
             *    "telefono": "633665656",
             *    "direccion": "Calle Easo"
             * }
             */
            $_respuestas = new respuestas;
            $datos       = json_decode($json, true);

            /**
             * Token
             */
            if(!isset($datos['token'])) {
                return $_respuestas->error_401();
            } else {
                $this->token = $datos['token'];
                $arrayToken = $this->buscarToken();
                if($arrayToken) {

                    if(!isset($datos['pacienteId'])) {
                        return $_respuestas->error_400();
                    } else {
                                                               $this->pacienteId      = $datos['pacienteId'];
                        if(isset($datos["nombre"]))          { $this->nombre          = $datos["nombre"];          }
                        if(isset($datos["dni"]))             { $this->dni             = $datos["dni"];             }
                        if(isset($datos["correo"]))          { $this->correo          = $datos["correo"];          } 
                        if(isset($datos["telefono"]))        { $this->telefono        = $datos["telefono"];        }
                        if(isset($datos["direccion"]))       { $this->direccion       = $datos["direccion"];       }
                        if(isset($datos["codigoPostal"]))    { $this->codigoPostal    = $datos["codigoPostal"];    }
                        if(isset($datos["genero"]))          { $this->genero          = $datos["genero"];          }
                        if(isset($datos["fechaNacimiento"])) { $this->fechaNacimiento = $datos["fechaNacimiento"]; }
                        $resp = $this->modificarPaciente();
                        if($resp) {
                            $respuesta = $_respuestas->response;
                            $respuesta["result"] = array(
                                "pacienteId" => $this->pacienteId
                            );
                            return $respuesta;
                        } else {
                            return $_respuestas->error_500();
                        }
                    }
                    
                } else {
                    return $_respuestas->error_400("El token que envio es invalido o ha caducado.");
                }
            }

        }

        private function modificarPaciente() {
            $query = "UPDATE " . $this->table . " SET `Nombre` = '" . $this->nombre . "', `Direccion` = '" . $this->direccion . "', `DNI` = '" . $this->dni . "', `CodigoPostal` = '" . $this->codigoPostal . "', `Telefono` = '" . $this->telefono . "', `Genero` = '" . $this->genero . "', `FechaNacimiento` = '" . $this->fechaNacimiento . "', `Correo` = '" . $this->correo . "' WHERE `PacienteId` = '" . $this->pacienteId . "';";
            //print_r($query);
            $resp  = parent::nonQuery($query);
            if($resp >= 1) {
                return $resp;
            } else {
                return 0;
            }
        }

        public function delete($json) {
 
            $_respuestas = new respuestas;
            $datos       = json_decode($json, true);

            /**
             * Token
             */
            if(!isset($datos['token'])) {
                return $_respuestas->error_401();
            } else {
                $this->token = $datos['token'];
                $arrayToken = $this->buscarToken();
                if($arrayToken) {

                    if(!isset($datos['pacienteId'])) {
                        return $_respuestas->error_400();
                    } else {
                        $this->pacienteId      = $datos['pacienteId'];                
                        $resp = $this->eliminarPaciente();
                        if($resp) {
                            $respuesta = $_respuestas->response;
                            $respuesta["result"] = array(
                                "pacienteId" => $this->pacienteId
                            );
                            return $respuesta;
                        } else {
                            return $_respuestas->error_500();
                        }
                    }
                    
                } else {
                    return $_respuestas->error_400("El token que envio es invalido o ha caducado.");
                }
            }

        }

        public function eliminarPaciente() {
            $query = "DELETE FROM " . $this->table . " WHERE `PacienteId` = '" . $this->pacienteId . "';";
            $resp = parent::nonQuery($query);
            if($resp >=1) {
                return $resp;
            } else {
                return 0;
            }
        }

        private function buscarToken() {
            $query = "SELECT `TokenId`, `UsuarioId`, `Estado` FROM `usuarios_token` WHERE `Token` = '" . $this->token . "' AND `Estado` = 'Activo';";
            $resp  = parent::obtenerDatos($query);
            if($resp) {
                return $resp;
            } else {
                return 0;
            }
        }

        private function actualizarToken($tokenid) {
            $date  = date("Y-m-d H:i");
            $query = "UPDATE `usuarios_token` SET `Fecha` = '$date' WHERE `TokenId` = '$tokenid';";
            $resp  = parent::nonQuery($query);
            if($resp >= 1) {
                return $resp;
            } else {
                return 0;
            }
        }

    }
?>