<?php

/*
* =======================================================================================
*                               ARCHIVO PRINCIPAL
* =======================================================================================
*   Este archivo se encarga de enrutar cada peticion a la route correspondiente,
*   las rutas estan definidas en la carpeta ROUTES.
*
*   Las peticiones o REQUEST tendran la siguiente estructura:
*   Ej. 
*      {
*          url: "contenido"|"comentarios",
*          params: "get", 
*          data: {  key1:"valor1", key2:"valor2", ...},        
*      }
*
*   Adicionalmente tendran un header con los siguientes valores de interes: 
*   
*      {
*          CONTENT_LENGTH:     "0",
*          HTTP_USER_AGENT:    "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.93 Safari/537.36",
*          REMOTE_ADDR:        "186.14.183.238",
*          REQUEST_METHOD:     "GET",
*          REQUEST_TIME:       "1619625721"
*          HTTP_KEY_HIST:      "Z9AQBQXUWDHRN5GYE3DUG52BTSFT1NMA"
*          CONTENT_TYPE:       multipart/form-data - application/json
*      }
*/

    #PERMITIR ACCESO DESDE OTRO DOMINIO (CORS)
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
    header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    #FIN

    include("/home/rafaelba/public_html/apis/api-music/contenidoRoute.php");

    if ($_SERVER['HTTP_KEY_MUSIC'] == 'Z9AQBQXUWDHRN5GYE3DUG52BTSFT1NMA') {
        if (isset($_SERVER['CONTENT_TYPE'])) {
            initializer();
        } else {
            $response = array("response" => "CONTENT_TYPE INVALID");
            echo json_encode($response);
        }
    } else {
        $response = array("response" => "API KEY INVALID");
        echo json_encode($response);
    }


    function initializer(){

        $request = readInput();
        error_log("Peticion: " . json_encode($request), 0);

        switch ($request["url"]) {
            case 'contenido':
                contenido($request);
                break;
            default:
                $response = array("response" => "URL INVALID");
                echo json_encode($response);
                break;
        }
    }

    function readInput(){

        if (is_numeric(strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data'))){
            $url = $_POST['url'];
            $params = $_POST['params'];
            $data = json_decode($_POST['data'], true);
            $request = array('url'=>$url, 'params'=>$params, 'data'=>$data);
        } else if (is_numeric(strpos($_SERVER['CONTENT_TYPE'], 'application/json'))){
            $json = file_get_contents('php://input');
            $request = json_decode($json, true);
        } else {
            $request = array("CONTENT_TYPE" => "CONTENT_TYPE INVALID");
        }

        return $request;
    }

?>