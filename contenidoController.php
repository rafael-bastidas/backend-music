<?php

/*
* =======================================================================================
*                               ARCHIVO CONTENIDO-CONTROLLER
* =======================================================================================
*   Este archivo se encarga de ejecutar las funciones para manipular la tabla citas_atendidas
*   segun las peticiones, mediante el archivo service/conectionDB.php.
*
*   
*   Para getContenidoInit y deleteCitasAtendidas el Dato sera una clausula WHERE:
*       $data = array(
*           'nickname'          => 'don_quijose'
*        );
*
*   Fines de prueba:
*       echo json_encode( array('response' => $response, 'sql' => $sql, 'request' => $data) );
*   SELECT * FROM series INNER JOIN myseries ON series.id_serie = myseries.id_serie;
*/

    include_once("conectionDB.php");
    
    function autenticar($data){
        $response = array("status"=>"ok","respuesta"=>"");
        $coincidencia = getBooleanQuery(array('table'=>'users', 'condition'=>"user='{$data['user']}' AND password='{$data['password']}'"));
        if ($coincidencia == 1){
            $sql = organizarSQL("select", "users", 'id_user', "WHERE user='{$data['user']}'");
            $resp = getArraySQL($sql);
            $response["respuesta"] = $resp[0]['id_user'];
        } else if ($coincidencia == 0) {
            $coincidencia = getBooleanQuery(array('table'=>'users', 'condition'=>"user='{$data['user']}'"));
            $response["respuesta"] = $coincidencia == 1 ? "Contraseña incorrecta" : "Usuario no registrado";
        } else {
            $response["status"] = "Error";
            $response["respuesta"] = "Fallo de comunicación con la base de datos";
        }
        return $response;
    }
    function registrar($data){
        $response = array("status"=>"ok","respuesta"=>"");
        if (getBooleanQuery(array('table'=>'users', 'condition'=>"user='{$data['user']}'")) == 1) {
            $response["respuesta"] = "El usuario ya se encuentra registrado";
        } else {
            $data['image'] = "https://thumbs.dreamstime.com/b/vector-de-usuario-redes-sociales-perfil-avatar-predeterminado-retrato-vectorial-del-176194876.jpg";
            $sql = organizarSQL("insert", "users", $data, "null");
            $response["respuesta"] = getLastIdSQL($sql);
        }
        
        return $response;
    }
    function editRegister($data){
        
        if (isset($_FILES['file_imgportada'])) {
            $extensionnombrearchivo = pathinfo($_FILES['file_imgportada']['name'], PATHINFO_EXTENSION);
            $archivotemporal = $_FILES['file_imgportada']['tmp_name'];
            $dir_subida = "/home/rafaelba/public_html/apis/api-music/media/";
            $newname = date("Y-m-d_H:i:s") . ".{$extensionnombrearchivo}";
            $fichero_subida = $dir_subida . $newname;
            if (move_uploaded_file($archivotemporal, $fichero_subida)) {
                $data['image'] = "https://rafaelbastidas.com/apis/api-music/media/" . $newname;
            }
        }
        
        $response = array("status"=>"ok","respuesta"=>"");
        $sql = organizarSQL("update", "users", $data, "id_user='{$data['id_user']}'");
        $response["respuesta"] = getBooleanSQL($sql);
        
        return $response;
    }
    
    function getUserById($id_user){
        error_log("users",0);
        $sql = organizarSQL("select", "users", "users.id_user, users.name, users.bio, users.image", "WHERE users.id_user = {$id_user}");
        return getArraySQL($sql);
    }
    function organizarList($array) {
        if (gettype($array) == "array" && isset($array[0])){
            $value = "{$array[0]}";
            for($i = 1; $i < count($array); $i++){
                $value .= ",{$array[$i]}";
            }
            return $value;
        } else {
            error_log("Fallo de lista",0);
            return "(-1)";
        }
    }
    function getListSongByListIdSong($list_IdSong){
        
        $values = organizarList($list_IdSong);
        $sql = organizarSQL("select", "songs", "songs.id_song, songs.title, songs.duration, songs.album, songs.group, songs.url", "WHERE id_song IN ({$values})");
        return getArraySQL($sql);
    }
    function getListFavoriteByIdUser($id_user){
        
        $sql = organizarSQL("select", "list_favorite", "list_favorite.id_list_favorite, list_favorite.list_id_song", "WHERE list_favorite.id_user = {$id_user}");
        $listFavorite = getArraySQL($sql);
        error_log("listFavorite ". json_decode("[]"),0);
        if (count($listFavorite) > 0) {
            $element = strpos($listFavorite[0]['list_id_song'], '[') === false ? [] : json_decode($listFavorite[0]['list_id_song']);
            $listFavorite[0]['list_id_song'] = getListSongByListIdSong($element);
        }
        return $listFavorite;
    }
    function getMyListByIdUser($id_user){
        
        $sql = organizarSQL("select", "my_list", "my_list.id_my_list, my_list.name, my_list.list_id_song", "WHERE my_list.id_user = {$id_user}");
        $myList = getArraySQL($sql);
        //error_log("my_list".json_encode($myList),0);
        for ($i = 0; $i < count($myList); $i++) {
            $element = strpos($myList[$i]['list_id_song'], '[') === false ? [] : json_decode($myList[$i]['list_id_song']);
            if (count($element) > 0) { $myList[$i]['list_id_song'] = getListSongByListIdSong($element); }
        }
        return $myList;
    }
    function getMyListByIdMyList($id_my_list){
        $sql = organizarSQL("select", "my_list", "my_list.id_my_list, my_list.name, my_list.list_id_song, my_list.id_user, users.name name_user", "INNER JOIN users ON my_list.id_user = users.id_user WHERE my_list.id_my_list = {$id_my_list}");
        error_log("otro sql".$sql,0);
        return getArraySQL($sql);
    }
    function getFollowListByIdUser($id_user){
        
        $sql = organizarSQL("select", "follow_list", "follow_list.id_follow_list, follow_list.list_id_my_list", "WHERE follow_list.id_user = {$id_user}");
        $followList = getArraySQL($sql);
        $listFollowList = [];
        $respuesta = [];
        //error_log("follow_list".json_encode($followList),0);
        if (count($followList) == 1) {
            $element_list_id_my_list = strpos($followList['0']['list_id_my_list'], '[') === false ? [] : json_decode($followList[0]['list_id_my_list']);
            for ($i = 0; $i < count($element_list_id_my_list); $i++) {
                $rowMyList = getMyListByIdMyList($element_list_id_my_list[$i])[0];
                $listIdSong = strpos($rowMyList['list_id_song'], '[') === false ? [] : json_decode($rowMyList['list_id_song']);
                //$rowMyList['list_id_song'] = getListSongByListIdSong($listIdSong); 
                //array_push($listFollowList, $rowMyList['list_id_song']);
                array_push($respuesta, array("id_my_list"=>$rowMyList['id_my_list'], "name"=>$rowMyList['name'], "list"=>getListSongByListIdSong($listIdSong), "id_user_creador"=>$rowMyList['id_user'], "name_user_creador"=>$rowMyList['name_user']));
            }
        } else {
            error_log("ERROR follow_list con el id_user: ".$id_user,0);
        }
        
        return $respuesta;
    }
    function getFollowListByIdFollowList($id_follow_list){
        $sql = organizarSQL("select", "follow_list", "follow_list.id_follow_list, follow_list.list_id_my_list, follow_list.id_user", "WHERE follow_list.id_follow_list = {$id_follow_list}");
        return getArraySQL($sql);
    }
    function getAllSong(){
        //error_log("AllSong",0);
        $sql = organizarSQL("select", "songs", "songs.id_song, songs.title, songs.duration, songs.album, songs.group, songs.url", "none");
        return getArraySQL($sql);
    }
    function getSongByLike($data){
        $sql = organizarSQL("select", "songs", "songs.id_song, songs.title, songs.duration, songs.album, songs.group, songs.url", "WHERE songs.title LIKE '%{$data['title']}%'");
        //error_log("SongByLike".$sql,0);
        $array_resp["all_songs"] = getArraySQL($sql);
        return $array_resp;
    }
    function getAlbumByLike($data){
        //error_log("AlbumByLike",0);
        $sql = organizarSQL("select", "songs", "songs.id_song, songs.title, songs.duration, songs.album, songs.group, songs.url", "WHERE songs.album LIKE '%{$data['album']}%'");
        $array_resp["all_songs"] = getArraySQL($sql);
        return $array_resp;
    }
    function getGroupByLike($data){
        //error_log("GroupByLike",0);
        $sql = organizarSQL("select", "songs", "songs.id_song, songs.title, songs.duration, songs.album, songs.group, songs.url", "WHERE songs.group LIKE '%{$data['group']}%'");
        $array_resp["all_songs"] = getArraySQL($sql);
        return $array_resp;
    }
    function getAllListSong(){
        
        $sql = organizarSQL("select", "my_list", "my_list.id_my_list, my_list.id_user, my_list.name, my_list.list_id_song, users.name name_user_creador", "INNER JOIN users ON my_list.id_user = users.id_user");
        $allListSong = getArraySQL($sql);
        //error_log("AllListSong".json_encode($allListSong),0);
        for ($i = 0; $i < count($allListSong); $i++) {
            $element = strpos($allListSong[$i]['list_id_song'], '[') === false ? [] : json_decode($allListSong[$i]['list_id_song']);
            if (count($element) > 0) { $allListSong[$i]['list_id_song'] = getListSongByListIdSong($element); }
        }
        return $allListSong;
    }
    function getAllListByLike($data){
        //error_log("AllListByLike",0);
        $sql = organizarSQL("select", "my_list", "my_list.id_my_list, my_list.id_user, my_list.name, my_list.list_id_song", "WHERE my_list.name LIKE '%{$data['name']}%'");
        $rowMyList = getArraySQL($sql);
        //error_log("rowMyList ".json_encode($rowMyList),0);
        for ($i = 0; $i < count($rowMyList); $i++) {
            $element = strpos($rowMyList[$i]['list_id_song'], '[') === false ? [] : json_decode($rowMyList[$i]['list_id_song']);
            if (count($element) > 0) { $rowMyList[$i]['list_id_song'] = getListSongByListIdSong($element); }
        }
        $array_resp["all_list"] = $rowMyList;
        return $array_resp;
    }
    function getAllSongAndList(){
        $array_resp["all_songs"] = getAllSong();
        $array_resp["all_list"] = getAllListSong();
        return $array_resp;
    }

    function getInit($data){
        
        $array_resp = array("profile"=>[], "favorite_list"=>[], "my_list"=>[], "follow_list"=>[], "all_songs"=>[], "all_list"=>[]);
        $array_resp["profile"] = getUserById($data['id_user']);
        $array_resp["favorite_list"] = getListFavoriteByIdUser($data['id_user']);
        $array_resp["my_list"] = getMyListByIdUser($data['id_user']);
        $array_resp["follow_list"] = getFollowListByIdUser($data['id_user']);
        $array_resp["all_songs"] = getAllSong();
        $array_resp["all_list"] = getAllListSong();
        
        return $array_resp;
    }
    
    function createList($data){
        $data['list_id_song'] = "[]";
        $sql = organizarSQL("insert", "my_list", $data, "null");
        return array("status"=>"ok","respuesta"=>getLastIdSQL($sql));
    }
    
    function addSongToFavorite($data){
        
        $response = array("status"=>"ok","respuesta"=>"");
        $rowTableFavorite = getArraySQL(organizarSQL("select", "list_favorite", "list_favorite.list_id_song", "WHERE list_favorite.id_user = {$data['id_user']}"));
        if ( count($rowTableFavorite) == 1 ) {
            $rowTableFavorite = strpos($rowTableFavorite[0]['list_id_song'], '[') === false ? [] : json_decode($rowTableFavorite[0]['list_id_song']);
            $coincidencia = in_array(intval($data['id_song']), $rowTableFavorite, true) || in_array((gettype($data['id_song']) == "string" ? $data['id_song'] : "{$data['id_song']}"), $rowTableFavorite, true);
            error_log("coincidencia " . $coincidencia . " list_id_song " . $rowTableFavorite,0);
            if ($coincidencia) {
                $response["status"] = "Ok";
                $response["respuesta"] = "La cancion ya existe en la lista";
            } else {
                array_push($rowTableFavorite, (gettype($data['id_song']) == "string" ? intval($data['id_song']) : $data['id_song']));
                $rowTableFavorite = json_encode($rowTableFavorite);
                //$values = array("list_id_song" => $rowTableFavorite['list_id_song']);
                $sql = organizarSQL("update", "list_favorite", array("list_id_song" => $rowTableFavorite), "id_user='{$data['id_user']}'");
                $response["respuesta"] = getBooleanSQL($sql);
            }
        } else if ( count($rowTableFavorite) == 0 ) {
            $sql = organizarSQL("insert", "list_favorite", array("id_user"=>$data['id_user'], "list_id_song"=>"[{$data['id_song']}]"), "null");
            $response["respuesta"] = getBooleanSQL($sql);
        }
        
        return $response;
    }
    function addSongToMyList($data){
        
        $response = array("status"=>"ok","respuesta"=>"");
        $rowTableMyList = getMyListByIdMyList($data['id_my_list'])['0'];
        $listIdSong = strpos($rowTableMyList['list_id_song'], '[') === false ? [] : json_decode($rowTableMyList['list_id_song']);
        $coincidencia = in_array(intval($data['id_song']), $listIdSong, true) || in_array((gettype($data['id_song']) == "string" ? $data['id_song'] : "{$data['id_song']}"), $listIdSong, true);
        if ($coincidencia) {
            $response["status"] = "Ok";
            $response["respuesta"] = "La cancion ya existe en la lista";
        } else {
            array_push($listIdSong, (gettype($data['id_song']) == "string" ? intval($data['id_song']) : $data['id_song']));
            $sql = organizarSQL("update", "my_list", array("list_id_song"=>json_encode($listIdSong)), "id_my_list='{$data['id_my_list']}'");
            $response["respuesta"] = getBooleanSQL($sql);
        }
        
        return $response;
    }
    function addListToFollowList($data){
        
        $validadorIdMyList = getArraySQL(organizarSQL("select", "my_list", "my_list.id_my_list", "WHERE my_list.id_my_list = {$data['id_my_list']}"));
        $response = array("status"=>"ok","respuesta"=>"");
        if (count($validadorIdMyList) == 1) {
            $rowTableFollowList = getArraySQL(organizarSQL("select", "follow_list", "follow_list.id_follow_list, follow_list.list_id_my_list", "WHERE follow_list.id_user = {$data['id_user']}"));
            if ( count($rowTableFollowList) == 1 ) {
                $listIdMyList = strpos($rowTableFollowList[0]['list_id_my_list'], '[') === false ? [] : json_decode($rowTableFollowList[0]['list_id_my_list']);
                $coincidencia = in_array(intval($data['id_my_list']), $listIdMyList, true) || in_array((gettype($data['id_my_list']) == "string" ? $data['id_my_list'] : "{$data['id_my_list']}"), $listIdMyList, true);
                if ($coincidencia) {
                    $response["respuesta"] = "La lista ya existe en la lista de seguimiento";
                } else {
                    array_push($listIdMyList, (gettype($data['id_my_list']) == "string" ? intval($data['id_my_list']) : $data['id_my_list']));
                    $sql = organizarSQL("update", "follow_list", array("list_id_my_list"=>json_encode($listIdMyList)), "id_user='{$data['id_user']}'");
                    $response["respuesta"] = getBooleanSQL($sql);
                }
            } else if ( count($rowTableFollowList) == 0 ) {
                $idMyList = gettype($data['id_my_list']) == "string" ? intval($data['id_my_list']) : $data['id_my_list'];
                $sql = organizarSQL("insert", "follow_list", array("id_user"=>$data['id_user'], "list_id_my_list"=>"[{$idMyList}]"), "null");
                $response["respuesta"] = getBooleanSQL($sql);
            }
        } else {
            $response["respuesta"] = "id_my_list invalido";
        }
        
        return $response;
    }
    
    function delSongFromFavorite($data){
        $response = array("status"=>"ok","respuesta"=>"");
        $rowTableFavorite = getArraySQL(organizarSQL("select", "list_favorite", "list_favorite.list_id_song", "WHERE list_favorite.id_user = {$data['id_user']}"));
        if ( count($rowTableFavorite) == 1 ) {
            $rowTableFavorite = strpos($rowTableFavorite[0]['list_id_song'], '[') === false ? [] : json_decode($rowTableFavorite[0]['list_id_song']);
            $coincidencia = in_array(intval($data['id_song']), $rowTableFavorite, true) || in_array((gettype($data['id_song']) == "string" ? $data['id_song'] : "{$data['id_song']}"), $rowTableFavorite, true);
            //error_log("coincidencia " . $coincidencia . " list_id_song " . $rowTableFavorite,0);
            if ($coincidencia) {
                $index = array_search($data['id_song'], $rowTableFavorite);
                //unset($rowTableFavorite[$index]);
                array_splice($rowTableFavorite, $index, 1);
                $rowTableFavorite = json_encode($rowTableFavorite);
                $sql = organizarSQL("update", "list_favorite", array("list_id_song" => $rowTableFavorite), "id_user='{$data['id_user']}'");
                $response["respuesta"] = getBooleanSQL($sql);
            } else {
                $response["status"] = "Ok";
                $response["respuesta"] = "La cancion no existe en la lista";
            }
        } else if ( count($rowTableFavorite) == 0 ) {
            $response["respuesta"] = "El id_user no tiene list_favorite";
        }
        
        return $response;
    }
    function delSongToMyList($data){
        
        $response = array("status"=>"ok","respuesta"=>"");
        $rowTableMyList = getMyListByIdMyList($data['id_my_list'])['0'];
        $listIdSong = strpos($rowTableMyList['list_id_song'], '[') === false ? [] : json_decode($rowTableMyList['list_id_song']);
        $coincidencia = in_array(intval($data['id_song']), $listIdSong, true) || in_array((gettype($data['id_song']) == "string" ? $data['id_song'] : "{$data['id_song']}"), $listIdSong, true);
        if ($coincidencia) {
            $index = array_search($data['id_song'], $listIdSong);
            unset($listIdSong[$index]);
            $sql = organizarSQL("update", "my_list", array("list_id_song"=>json_encode($listIdSong)), "id_my_list='{$data['id_my_list']}'");
            $response["respuesta"] = getBooleanSQL($sql);
        } else {
            $response["respuesta"] = "La cancion no existe en la lista";
        }
        
        return $response;
    }
    function delListFromMyList($data){
        $response = array("status"=>"ok","respuesta"=>"");
        
        $followList_coincidentes = getArraySQL(organizarSQL("select", "follow_list", "follow_list.id_follow_list, follow_list.list_id_my_list, follow_list.id_user", "WHERE follow_list.list_id_my_list LIKE '%{$data['id_my_list']}%'"));
        for($i = 0; $i < count($followList_coincidentes); $i++){
            $element = $followList_coincidentes[$i];
            $listIdMyList = json_decode($element['list_id_my_list']);
            unset($listIdMyList[array_search($data['id_my_list'], $listIdMyList)]);
            $sql = organizarSQL("update", "follow_list", array("list_id_my_list"=>json_encode($listIdMyList)), "id_follow_list='{$element['id_follow_list']}'");
            getBooleanSQL($sql);
        }
        
        $sql = organizarSQL("delete", "my_list", null, "WHERE id_my_list='{$data['id_my_list']}'");
        $response["respuesta"] = getBooleanSQL($sql);
        return $response;
    }
    function delListFromFollowList($data){
        $response = array("status"=>"ok","respuesta"=>"No se encontro datos para el usuario o hay un duplicado");
        
        $followList_coincidentes = getArraySQL(organizarSQL("select", "follow_list", "follow_list.id_follow_list, follow_list.list_id_my_list, follow_list.id_user", "WHERE follow_list.id_user = {$data['id_user']}"));
        if ( count($followList_coincidentes) == 1 ){
            $element = $followList_coincidentes[0];
            $listIdMyList = strpos($element['list_id_my_list'], '[') === false ? [] : json_decode($element['list_id_my_list']);
            $coincidencia = in_array(intval($data['id_my_list']), $listIdMyList, true) || in_array((gettype($data['id_my_list']) == "string" ? $data['id_my_list'] : "{$data['id_my_list']}"), $listIdMyList, true);
            if ($coincidencia) {
                unset($listIdMyList[array_search($data['id_my_list'], $listIdMyList)]);
                $sql = organizarSQL("update", "follow_list", array("list_id_my_list"=>json_encode($listIdMyList)), "id_follow_list='{$element['id_follow_list']}'");
                $response["respuesta"] = getBooleanSQL($sql);
            } else {
                $response["respuesta"] = "El id_my_list no exist en la lista de seguimiento";
            }
        }
        
        return $response;
    }
    
    
    
    function uploadSong($data){
        
        if (isset($_FILES['file_song'])) {
            $extensionnombrearchivo = pathinfo($_FILES['file_song']['name'], PATHINFO_EXTENSION);
            $archivotemporal = $_FILES['file_song']['tmp_name'];
            $dir_subida = "/home/rafaelba/public_html/apis/api-music/media/";
            $newname = date("Y-m-d_H:i:s") . ".{$extensionnombrearchivo}";
            $fichero_subida = $dir_subida . $newname;
            if (move_uploaded_file($archivotemporal, $fichero_subida)) {
                $data['url'] = $newname;
            }
        }
        
        $response = array("status"=>"ok","respuesta"=>"");
        $sql = organizarSQL("insert", "songs", $data, "null");
        error_log($sql,0);
        $response["respuesta"] = getBooleanSQL($sql);
        
        return $response;
    }
    function editSong($data){
        $song = getArraySQL(organizarSQL("select", "songs", "songs.id_song, songs.title, songs.duration, songs.album, songs.group, songs.url", "WHERE songs.id_song = {$data['id_song']}"));
        if ( count($song) == 1 ) {
            $sql = organizarSQL("update", "songs", $data, "id_song='{$data['id_song']}'");
            $response["respuesta"] = getBooleanSQL($sql);
        } else {
            $response["respuesta"] = "El id_song no existe o esta duplicado";
        }
        
        return $response;
    }
    function deleteSong($data){
        $song = getArraySQL(organizarSQL("select", "songs", "songs.id_song, songs.title, songs.duration, songs.album, songs.group, songs.url", "WHERE songs.id_song = {$data['id_song']}"));
        if ( count($song) == 1 ) {
            $myList_coincidentes = getArraySQL(organizarSQL("select", "my_list", "my_list.id_my_list, my_list.list_id_song", "WHERE my_list.list_id_song LIKE '%{$data['id_song']}%'"));
            for($i = 0; $i < count($myList_coincidentes); $i++){
                $element = $myList_coincidentes[$i];
                $listIdSong = json_decode($element['list_id_song']);
                unset($listIdSong[array_search($data['id_song'], $listIdSong)]);
                $sql = organizarSQL("update", "my_list", array("list_id_song"=>json_encode($listIdSong)), "id_my_list='{$element['id_my_list']}'");
                getBooleanSQL($sql);
            }
            $favoriteList_coincidentes = getArraySQL(organizarSQL("select", "list_favorite", "list_favorite.id_list_favorite, list_favorite.list_id_song", "WHERE list_favorite.list_id_song LIKE '%{$data['id_song']}%'"));
            for($i = 0; $i < count($favoriteList_coincidentes); $i++){
                $element = $favoriteList_coincidentes[$i];
                $listIdSong = json_decode($element['list_id_song']);
                unset($listIdSong[array_search($data['id_song'], $listIdSong)]);
                $sql = organizarSQL("update", "list_favorite", array("list_id_song"=>json_encode($listIdSong)), "id_list_favorite='{$element['id_list_favorite']}'");
                getBooleanSQL($sql);
            }
            $sql = organizarSQL("delete", "songs", "", "WHERE id_song='{$data['id_song']}'");
            $response["respuesta"] = getBooleanSQL($sql);
        } else {
            $response["respuesta"] = "El id_song no existe o esta duplicado";
        }
        
        return $response;
    }
    




















?>