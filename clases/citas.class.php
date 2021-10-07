<?php
    require_once "conexion/conexion.php";
    require_once "respuestas.class.php";

    class pacientes extends conexion {

        private $table           = "`citas`";
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
        public function listaCitas($pagina = 1) {
            $inicio   = 0;
            $cantidad = 100;
            if($pagina > 1) {
                $inicio = ($cantidad * ($pagina - 1)) + 1;
                $cantidad = $cantidad * $pagina;
            }

            $query = "SELECT `PacienteId`, `CitaId`, `Fecha`, `HoraInicio`, `HoraFin`, `Estado`, `Motivo` FROM " . $this->table . " LIMIT $inicio, $cantidad;";
            //print_r($query);
            $datos = parent::obtenerDatos($query);
            return ($datos);
        }

        /**
         * Muestra un registro especifico
         */
        public function obtenerCitas($id) {
            $query = "SELECT * FROM " . $this->table . " WHERE `CitaId` = '$id';";
            return parent::obtenerDatos($query);
        }

        public function post($json) {
            
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

                    if(!isset($datos['PacienteId']) || !isset($datos['HoraInicio']) || !isset($datos['HoraFin']) || !isset($datos['Estado']) || !isset($datos['Motivo'])) {
                        return $_respuestas->error_400();
                    } else {
                        $this->PacienteId = $datos["PacienteId"];
                        $this->Fecha    = $datos["Fecha"];
                        $this->HoraInicio    = $datos["HoraInicio"];
                        $this->HoraFin = $datos["HoraFin"];
                        $this->Estado = $datos["Estado"];
                        $this->Motivo = $datos["Motivo"];        
                        
                        
                        $resp = $this->insertarCita();
                        if($resp) {
                            $respuesta = $_respuestas->response;
                            $respuesta["result"] = array(
                                "citaId" => $resp
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

        private function insertarCita() {
            $query = "INSERT INTO " . $this->table . " (`PacienteId`, `Fecha`, `HoraInicio`, `HoraFin`, `Estado`, `Motivo`) VALUES ('" . $this->PacienteId . "', '" . $this->Fecha . "', '" . $this->HoraInicio . "', '" . $this->HoraFin . "', '" . $this->Estado . "', '" . $this->Motivo . "');";
            //print_r($query);
            $resp  = parent::nonQueryId($query);
            if($resp) {
                return $resp;
            } else {
                return 0;
            }
        }

        public function put($json) {
            
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

                    if(!isset($datos['citaId'])) {
                        return $_respuestas->error_400();
                    } else {
                        $this->PacienteId = $datos["PacienteId"];
                        $this->Fecha    = $datos["Fecha"];
                        $this->HoraInicio    = $datos["HoraInicio"];
                        $this->HoraFin = $datos["HoraFin"];
                        $this->Estado = $datos["Estado"];
                        $this->Motivo = $datos["Motivo"];    
                        $resp = $this->modificarPaciente();
                        if($resp) {
                            $respuesta = $_respuestas->response;
                            $respuesta["result"] = array(
                                "citaId" => $this->pacienteId
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

        private function modificarCita() {
            $query = "UPDATE " . $this->table . " SET `PacienteId` = '" . $this->PacienteId . "', `Fecha` = '" . $this->HoraInicio . "', `HoraInicio` = '" . $this->HoraInicio . "', `HoraFin` = '" . $this->HoraFin . "', `Estado` = '" . $this->Estado . "';";
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

                    if(!isset($datos['citaId'])) {
                        return $_respuestas->error_400();
                    } else {
                        $this->citaId      = $datos['citaId'];                
                        $resp = $this->eliminarCita();
                        if($resp) {
                            $respuesta = $_respuestas->response;
                            $respuesta["result"] = array(
                                "citaId" => $this->citaId
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
            $query = "DELETE FROM " . $this->table . " WHERE `CitaId` = '" . $this->citaId . "';";
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