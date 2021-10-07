<?php
    require_once "conexion/conexion.php";
    require_once "respuestas.class.php";

    class auth extends conexion {

        public function login($json) {
            $_respuestas = new respuestas;
            $datos       = json_decode($json, true);
            if(!isset($datos['usuario']) || !isset($datos['password'])) {
                // Error con los campos
                return $_respuestas->error_400();
            } else {
                // Todo está bien                
                $usuario  = $datos['usuario'];
                $password = $datos['password'];
                $password = parent::encriptar($password);
                $datos    = $this->obtenerDatosUsuarios($usuario);
                if($datos) {
                    // Si existe el usuario
                    // Verfificar si la contraseña es igual
                    /**
                     * $datos[0]["Password"] => "Password" es el nombre del campo de la tabla "usuarios" de la base de datos "apirest"
                     */
                    if($password == $datos[0]["Password"]) {
                        /**
                         * $datos[0]["Estado"] => "Estado" es el nombre del campo de la tabla "usuarios" de la base de datos "apirest"
                         */
                        if($datos[0]["Estado"] == "Activo") {
                            // Crear el token
                            /**
                             * $datos[0]["UsuarioId"] => "UsuarioId" es el nombre del campo de la tabla "usuarios" de la base de datos "apirest"
                             */
                            $verificar = $this->insertarToken($datos[0]["UsuarioId"]);
                            if($verificar) {
                                // Si se guardó
                                $result = $_respuestas->response;
                                $result["result"] = array(
                                    "token" => $verificar
                                );
                                return $result;
                            } else {
                                // Error al guardar
                                return $_respuestas->error_500("Error interno, no hemos podido guardar.");
                            }
                        } else {
                            // El usuario está inactivo.
                            return $_respuestas->error_200("El usuario $usuario está inactivo.");
                        }
                    } else {
                        // La contraseña no es igual
                        return $_respuestas->error_200("El password es invalido.");
                    }
                } else {
                    // No existe el usuario
                    return $_respuestas->error_200("El usuario $usuario no existe.");
                }
            }
        }

        private function obtenerDatosUsuarios($correo){
            $query = "SELECT `UsuarioId`, `Password`, `Estado` FROM `usuarios` WHERE `Usuario` = '$correo';";
            $datos = parent::obtenerDatos($query);
            if(isset($datos[0]["UsuarioId"])) {
                return $datos;
            } else {
                return 0;
            }
        }

        private function insertarToken($usuarioid) {
            $val    = true;
            $token  = bin2hex(openssl_random_pseudo_bytes(16, $val));
            $date   = date("Y-m-d H:i");
            $estado = "Activo";
            $query  = "INSERT INTO `usuarios_token`(`Usuarioid`, `Token`, `Estado`, `Fecha`) VALUES ('$usuarioid', '$token', '$estado', '$date');";
            $verifica = parent::nonQuery($query);
            if($verifica) {
                return $token; 
            } else {
                return 0;
            }
        }

    }
?>