<?php
    require_once "clases/auth.class.php";
    require_once "clases/respuestas.class.php";

    $_auth = new auth;
    $_respuestas = new respuestas;

    if($_SERVER['REQUEST_METHOD'] == "POST") {

        // Recibir datos
        //echo "Hola desde el documento.";
        $postBody   = file_get_contents("php://input");
        //print_r($postBody);
        
        // Enviamos los datos al manejador
        $datosArray = $_auth->login($postBody);
        // Devolvemos una respuesta
        header("Content-Type: application/json");
        if(isset($datosArray["result"]["error_id"])) {
            $responseCode = $datosArray["result"]["error_id"];
            http_response_code($responseCode);
        } else {
            http_response_code(200);
        }
        //print_r(json_encode($datosArray));
        echo json_encode($datosArray);
    } else {
        //echo " método no permitido.";
        header("Content-Type: application/json");
        $datosArray = $_respuestas->error_405();
        echo json_encode($datosArray);
    }
?>