<?php

    function connectDB(){
    
        $server = "localhost";
        $user = "rafaelba";
        $pass = "EdNc)6X7m5U)g4";
        $bd = "rafaelba_music";
    
        $conexion = mysqli_connect($server, $user, $pass,$bd);
    
        if($conexion){
            //echo 'La conexion de la base de datos se ha hecho satisfactoriamente';
        }else{
            echo 'Ha sucedido un error inexperado en la conexion de la base de datos'.mysqli_connect_errno();
        }
    
        return $conexion;
    }

function disconnectDB($conexion){

    $close = mysqli_close($conexion);

        if($close){
            //echo 'La desconexion de la base de datos se ha hecho satisfactoriamente';
        }else{
            echo 'Ha sucedido un error inexperado en la desconexion de la base de datos';
        }   

    return $close;
}

function getArraySQL($sql){
    
    //Creamos la conexión con la función anterior
    $conexion = connectDB();

    //generamos la consulta

        mysqli_set_charset($conexion, "utf8"); //formato de datos utf8

    if(!$result = mysqli_query($conexion, $sql)) die(); //si la conexión cancelar programa

    $rawdata = array(); //creamos un array

    //guardamos en un array multidimensional todos los datos de la consulta
    $i=0;

    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC))
    {
        $rawdata[$i] = $row;
        $i++;
    }
    //error_log("sql ".$sql." result ".$rawdata ,0);
    disconnectDB($conexion); //desconectamos la base de datos

    return $rawdata; //devolvemos el array
}

function getLastIdSQL($sql){

    //Creamos la conexión con la función anterior
    $conexion = connectDB();
    mysqli_set_charset($conexion, "utf8"); //formato de datos utf8
    if(mysqli_query($conexion, $sql)){
        $last_id = mysqli_insert_id($conexion);
    } else {
        $last_id = -1;
    }

    disconnectDB($conexion); //desconectamos la base de datos

    return $last_id; //devolvemos el array
}
function getBooleanSQL($sql){

    //Creamos la conexión con la función anterior
    $conexion = connectDB();

    //generamos la consulta

        mysqli_set_charset($conexion, "utf8"); //formato de datos utf8

    $result = mysqli_query($conexion, $sql);
    $response = array("response" => $result); //TRUE: Operacion Correcta. FALSE: Operacion Incorecta.

    disconnectDB($conexion); //desconectamos la base de datos

    return $result; //devolvemos el array
}

function getBooleanQuery($array_value){

    //Creamos la conexión con la función anterior
    $conexion = connectDB();

    mysqli_set_charset($conexion, "utf8"); //formato de datos utf8

    $table = $array_value['table'];
    $condition = $array_value['condition'];
    $sql = "SELECT COUNT(*) total FROM {$table} WHERE {$condition}";
    $result = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($result);

    disconnectDB($conexion); //desconectamos la base de datos

    return $fila['total']; //devolvemos el array
}

function organizarSQL($type_sql, $tablename, $array_datos, $clausula_where){

    switch ($type_sql) {
        case 'select':
            $sql = "SELECT {$array_datos} FROM {$tablename}" . ($clausula_where != 'none' ? " {$clausula_where}" : "");
            break;
        case 'insert':
            $column='';
            $value='';
            foreach ($array_datos as $clave => $valor){
                $column .= "{$tablename}.{$clave},";
                $value .= "'{$valor}',";
            }
            $column = substr($column, 0, -1);
            $value = substr($value, 0, -1);
            $sql = "INSERT INTO {$tablename} ({$column}) VALUES ({$value})";
            break;
        case 'update':
            $value='';
            foreach ($array_datos as $clave => $valor){
                $value .= "{$tablename}.{$clave}='{$valor}',";
            }
            $value = substr($value, 0, -1);
            $sql = "UPDATE {$tablename} SET {$value} WHERE {$clausula_where}";
            break;
        case 'delete':
            $sql = "DELETE {$array_datos} FROM {$tablename}" . ($clausula_where != 'none' ? " {$clausula_where}" : "");
            break;
        default:
            # $sql INVALIDO
            break;
    }

    return $sql;

}

?>