<?php
    require_once "clases/respuestas.class.php";
    require_once "clases/pacientes.class.php";

    $_respuestas = new respuestas;
    $_pacientes = new pacientes;

    if($_SERVER['REQUEST_METHOD'] == "GET") {

        //echo "Hola GET";
        /**
         * Example:
         * http://localhost:3306/pagina/pacientes?page=1
         */
        if(isset($_GET["page"])) {
            $pagina         = $_GET["page"];
            $listaPacientes = $_pacientes->listaPacientes($pagina);
            header("Content-Type: application/json");
            echo json_encode($listaPacientes);
            http_response_code(200);
        } else if(isset($_GET["id"])) {
            $pacienteid     = $_GET["id"];
            $datosPacientes = $_pacientes->obtenerPaciente($pacienteid);
            header("Content-Type: application/json");
            echo json_encode($datosPacientes);
            http_response_code(200);
        }
        
    } else if($_SERVER['REQUEST_METHOD'] == "POST"){

        //echo "Hola POST";
        // Recibimos los datos enviados
        $postBody   = file_get_contents("php://input");
        // Enviamos al manejador
        $datosArray = $_pacientes->post($postBody);
        //print_r($resp);
        // Devolvemos una respuesta
        header("Content-Type: application/json");
        if(isset($datosArray["result"]["error_id"])) {
            $responseCode = $datosArray["result"]["error_id"];
            http_response_code($responseCode);
        } else {
            http_response_code(200);
        }
        echo json_encode($datosArray);

    } else if($_SERVER['REQUEST_METHOD'] == "PUT"){

        //echo "Hola PUT";
        // Recibimos los datos enviados
        $postBody   = file_get_contents("php://input");
        // Enviamos datos al manejador
        $datosArray = $_pacientes->put($postBody);
        //print_r($postBody);
        // Devolvemos una respuesta
        header("Content-Type: application/json");
        if(isset($datosArray["result"]["error_id"])) {
            $responseCode = $datosArray["result"]["error_id"];
            http_response_code($responseCode);
        } else {
            http_response_code(200);
        }
        echo json_encode($datosArray);

    }  else if($_SERVER['REQUEST_METHOD'] == "DELETE"){

        $header = getallheaders();
        //print_r($header);
        if(isset($header["token"]) && isset($header["pacienteId"])) {
            // Recibimos los datos enviados por el 'header'
            $send    = [
                    "token"      => $header["token"],
                    "pacienteId" => $header["pacienteId"]
            ];
            $postBody = json_encode($send);
        } else {
            //echo "Hola DELETE";
            // Recibimos los datos enviados
            $postBody   = file_get_contents("php://input");
        }        
        // Enviamos datos al manejador
        $datosArray = $_pacientes->delete($postBody);
        //print_r($postBody);
        // Devolvemos una respuesta
        header("Content-Type: application/json");
        if(isset($datosArray["result"]["error_id"])) {
            $responseCode = $datosArray["result"]["error_id"];
            http_response_code($responseCode);
        } else {
            http_response_code(200);
        }
        echo json_encode($datosArray);

    } else {
        header("Content-Type: application/json");
        $datosArray = $_respuestas->error_405();
        echo json_encode($datosArray);
    }
?>