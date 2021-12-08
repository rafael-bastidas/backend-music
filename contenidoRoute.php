<?php

/*
* =======================================================================================
*                               ARCHIVO CONTENIDO-ROUTE
* =======================================================================================
*   Este archivo se encarga de cargar la funcion del controlar correspondiente a las rutas
*   de CONTENIDO, las funciones estan definidas en la carpeta CONTROLLERS.
*
*   Tipos de params:
*      
*          GET:     -->     solicitud para OBTENER
*          POST:    -->     solicitud para INSERTAR
*          DELETE   -->     solicitud para ELIMINAR
*
*   
*/

    include_once("contenidoController.php");

    function contenido($request){
        switch ($request["params"]) {
            case 'get-init':
                $response = isset($request["data"]['id_user']) ? getInit($request["data"]) : "id_user no identificado";
                break;
            case 'get-filter':
                if (isset($request["data"]['title'])) {
                    $response = getSongByLike($request["data"]);
                } else if (isset($request["data"]['album'])) {
                    $response = getAlbumByLike($request["data"]);
                } else if (isset($request["data"]['group'])) {
                    $response = getGroupByLike($request["data"]);
                } else if (isset($request["data"]['name'])) {
                    $response = getAllListByLike($request["data"]);
                } else if (isset($request["data"]['all'])) {
                    $response = getAllSongAndList();
                } else { $response = "Filtro no identificado"; }
                break;
            case 'add-song-tofavorite':
                $response = isset($request["data"]['id_user']) && isset($request["data"]['id_song']) ? addSongToFavorite($request["data"]) : "id_user, id_song no identificado";
                break;
            case 'add-song-tolist':
                $response = isset($request["data"]['id_my_list']) && isset($request["data"]['id_song']) ? addSongToMyList($request["data"]) : "id_my_list, id_song no identificado";
                break;
            case 'add-list-tolist':
                $response = isset($request["data"]['id_my_list']) && isset($request["data"]['id_user']) ? addListToFollowList($request["data"]) : "id_my_list, id_user no identificado";
                break;
            case 'create-list':
                $response = isset($request["data"]['id_user']) && isset($request["data"]['name']) ? createList($request["data"]) : "id_user, name no identificado";
                break;
            case 'delete-song-fromfavorite':
                $response = isset($request["data"]['id_user']) && isset($request["data"]['id_song']) ? delSongFromFavorite($request["data"]) : "id_user, id_song no identificado";
                break;
            case 'delete-song-fromlist':
                $response = isset($request["data"]['id_my_list']) && isset($request["data"]['id_song']) ? delSongToMyList($request["data"]) : "id_my_list, id_song no identificado";
                break;
            case 'delete-list-frommylist':
                $response = isset($request["data"]['id_my_list']) ? delListFromMyList($request["data"]) : "id_my_list no identificado";
                break;
            case 'delete-list-fromfollowlist':
                $response = isset($request["data"]['id_my_list']) && isset($request["data"]['id_user']) ? delListFromFollowList($request["data"]) : "id_my_list, id_user no identificado";
                break;
            case 'autenticar':
                $response = autenticar($request["data"]);
                break;
            case 'registrar':
                $response = registrar($request["data"]);
                break;
            case 'edit-user':
                $response = isset($request["data"]['id_user']) ? editRegister($request["data"]) : "id_user no identificado";
                break;
            case 'upload-song':
                $response = uploadSong($request["data"]);
                break;
            case 'edit-song':
                $response = isset($request["data"]['id_song']) ? editSong($request["data"]) : "id_song no identificado";
                break;
            case 'delete-song':
                $response = isset($request["data"]['id_song']) ? deleteSong($request["data"]) : "id_song no identificado";
                break;
            default:
                $response = "params de la peticion invalida";
                break;
        }

        error_log("Respuesta: " . $response, 0);
        header("Content-Type: application/json");
        $response = array("response" => $response);
        echo json_encode($response);
    }

?>