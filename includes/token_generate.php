<?php

function generateFormToken($form,$db) {


    if(isset($_SESSION['csrf'][$form.'_token'])){

        return $_SESSION['csrf'][$form.'_token']['token'];

    }else{

            $hora = date('H:i');
            $users = $_SESSION['UserID'];
            $session_id = uniqid(session_id());

            //unset($_SESSION['csrf'][$form.'_token']);

            $token = hash('sha256', uniqid($users.$session_id.$hora, true));

            //generar token de forma aleatoria
            //$token = md5(uniqid(microtime(), true));

            // generar fecha de generación del token
            $token_time = time();

            // escribir la información del token en sesión para poder
            // comprobar su validez cuando se reciba un token desde un formulario
            $_SESSION['csrf'][$form.'_token'] = array('token'=>$token, 'time'=>$token_time);

            $SQL = "UPDATE `www_users` SET `ln_token` = '$token', `dtm_ultimo_acceso_token` = '".date('Y-m-d H:i:s')."' WHERE `userid` = '$_SESSION[UserID]'";

            $ErrMsg = "No se pudo almacenar la información";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            return $token;

    }
}

function deleteToken($db){
    $SQL = "UPDATE `www_users` SET `ln_token` = NULL, `dtm_ultimo_acceso_token` = '1900-01-01 00:00:00' WHERE `userid` = '$_SESSION[UserID]'";

    $ErrMsg = "No se pudo almacenar la información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    return true;
}