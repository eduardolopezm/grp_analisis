<?php

date_default_timezone_set("America/Mexico_City");
$rootpath= dirname($_SERVER['PHP_SELF']);

if (!isset($PathPrefix)) {
    $PathPrefix = '';
}

if (!file_exists($PathPrefix . 'config.php')) {
    $rootpath = dirname($_SERVER['PHP_SELF']);
    if ($rootpath == '/' or $rootpath == "\\") {
        $rootpath = '';
    }
    header('Location:' . $rootpath . '/install/index.php');
}

include $PathPrefix . 'config.php';

if (isset($SessionSavePath)) {
    session_save_path($SessionSavePath);
}

if (strpos($_SERVER['HTTP_HOST'], 'localhost')  ){
    //or !is_numeric($getdir1)) {
    //echo strpos($_SERVER['HTTP_HOST'], 'localhost');
    ini_set('session.gc_Maxlifetime', $SessionLifeTime);
    ini_set('max_execution_time', $MaximumExecutionTime);
    ini_set("session.cookie_lifetime", $SessionLifeTime);
    session_set_cookie_params($SessionLifeTime);
    session_set_cookie_params($SessionLifeTime, "/", $_SERVER['HTTP_HOST'], false, false);
}

$DbgMsg = "El SQL que fallo es: ";

session_start();

if (isset($_SESSION['OpcionMenu'])) {
    $_SESSION['ShowIndex'] = $_SESSION['OpcionMenu'];
}

include $PathPrefix . 'includes/LanguageSetup.php';
include $PathPrefix . 'includes/ConnectDB.inc';
include $PathPrefix . 'includes/DateFunctions.inc';

if(isset($_POST['SubmitResetUser'])){
    $SQL = "UPDATE `www_users` SET `ln_token` = NULL, `dtm_ultimo_acceso_token` = '1900-01-01 00:00:00' WHERE `userid` = '".DB_escape_string($_SESSION['useridTemp'])."'";
    $ErrMsg = "No se pudo almacenar la información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    // var_dump($_SESSION['useridTemp']);
}

// Un comment to turn off attempts counter
//$_SESSION['AttemptsCounter'] = 0;
//include $PathPrefix . 'webIntelligencetecnoaplicada/includes/GeneralFunctions.inc';
function file_get_contents_curl($url) {
  if (strpos($url,'http://') !== FALSE) {
    $fc = curl_init();
    curl_setopt($fc, CURLOPT_URL,$url);
    curl_setopt($fc, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($fc, CURLOPT_HEADER,0);
    curl_setopt($fc, CURLOPT_VERBOSE,0);
    curl_setopt($fc, CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($fc, CURLOPT_TIMEOUT,30);
    $res = curl_exec($fc);
    curl_close($fc);
  }
  else $res = file_get_contents($url);
  return $res;
}

/**
    Funcion que regresa la descripcion de acuerdo al numero de funcion de la pagina
*/
function traeNombreFuncion($funcion, $db,$ponerNombre='0') {
    $descripcion= "";
    if($ponerNombre=='0'){
    if (!empty($funcion)) {
        $SQL = "SET NAMES 'utf8'";
        $TransResult = DB_query ( $SQL, $db );
        // consultar dato de funcion
        $consulta= "SELECT functionid, title FROM sec_functions_new WHERE functionid='".$funcion."'";
        $resultado= DB_query($consulta, $db);

        while ($registro= DB_fetch_array($resultado)) {
            $descripcion= $registro["title"];
        }

    }
        }else{
            $descripcion=$ponerNombre;
        }
    return $descripcion;
}

function GetAllCurrencyRatesFromPeriodDLS ($fromD, $toD) {
    ini_set('default_socket_timeout' , 2);
    $ratediario = array();

    $xml = @file_get_contents('http://dof.gob.mx/indicadores_detalle.php?cod_tipo_indicador=158&hfecha='.$toD.'&dfecha='.$fromD.'&accionI=imprimir');
    $moneda=substr($xml,strpos($xml,'<td height="17" width="48%" align="center" class="txt" style="padding: 3px;">'));
    $resdiario = explode('<td height="17" width="48%" align="center" class="txt" style="padding: 3px;">',$moneda);
    
    for ($elem=1;$elem<count($resdiario);$elem++) {
        $ratediario[$elem]['date'] = substr($resdiario[$elem],0,10);
        $ratediario[$elem]['rate'] = substr($resdiario[$elem],strpos($resdiario[$elem],'class="txt">')+12,9);

    }
    return $ratediario;
}

function GetAllCurrencyRatesEUR () {
    $resultado='';
    $fecha_tc='';
    $tc='';
    $salida_SOAP=array('','','','');
    $client = new SoapClient(null, array('location' => 'http://www.banxico.org.mx:80/DgieWSWeb/DgieWS?WSDL',
                                 'uri'      => 'http://DgieWSWeb/DgieWS?WSDL',
                                 'encoding' => 'ISO-8859-1',
                                 'trace'    => 1) );
    try{
      $salida_SOAP[0]= $client->reservasInternacionalesBanxico();
      $salida_SOAP[1]= $client->udisBanxico();
      $salida_SOAP[2]= $client->tiposDeCambioBanxico();    
      $salida_SOAP[3]= $client->tasasDeInteresBanxico();
    }catch (SoapFault $exception){
        
    }

    for($k=2; $k<3; $k++){
        
        if($salida_SOAP[$k]<>''){
            $dom = new DomDocument();
            $dom->loadXML($salida_SOAP[$k]);
            $xmlSeries = $dom->getElementsByTagName( "Series" );
            $xmlObs = $dom->getElementsByTagName( "Obs" );

            for($l=0; $l<$xmlObs->length; $l++){
                $itemSeries = $xmlSeries->item($l);
                $itemObs = $xmlObs->item($l);
                if ($itemSeries->getAttribute("IDSERIE") == "SF46410"){
                    $ratediario = $itemObs->getAttribute("OBS_VALUE");
                    //echo "<br>TIPO CAMBIO: " . $tipodecambio = $itemObs->getAttribute("OBS_VALUE");
                }
            }
        }else{

        }

    }
    return $ratediario;
}

function GetCurrencyRateByDate($fecha, $moneda, $db) {
    if ($moneda=='MXN'){
        return 1;   
    }else{
        $sqliva="select rate,fecha from tipocambio
                             where fecha <= '".$fecha." 23:59:59' and currency = '" . $moneda . "' and rate <> 0
                             order by fecha desc limit 1";
        
        //echo '<br><pre>'.$sqliva; 
        $result = DB_query($sqliva,$db);
        if ($myrow = DB_fetch_row($result)) {
            return $myrow[0]; 
        } else {
            //prnMsg( _('Fecha no valida ara tipo de cambio'),'error');
            return 0;
        }
    }
}

//Esta funcion devolvera ultimo tipo de cambio valido...
function GetCurrencyRateByLastDateWRate($fecha, $moneda, $db) {

    $sql = "SELECT 1/rate AS rate,
                   fecha
            FROM tipocambio
            WHERE DATE_FORMAT(fecha,'%Y-%m-%d') = '" . $fecha . "'
              AND currency = '" . $moneda . "'
              AND rate <> 0 LIMIT 1";
              
    $result = DB_query($sql,$db);
    if ($myrow = DB_fetch_row($result)) {
        return $myrow[0]; 
    } else {
        return 0;
    }
}

if (!isset($_SESSION['AttemptsCounter'])) {
    $_SESSION['AttemptsCounter'] = 0;
}

/* iterate through all elements of the $_POST array and DB_escape_string them
to limit possibility for SQL injection attacks and cross scripting attacks
 */

if (isset($_SESSION['DatabaseName'])) {
    foreach ($_POST as $key => $value) {
        if (gettype($value) != "array") {
            $_POST[$key] = DB_escape_string($value);
        } else {
            foreach ($value as $key1 => $value1) {

                switch (gettype($value1)) {
                    case 'string':
                        $value[$key1] = DB_escape_string($value1);
                        break;
                    default:
                        $value[$key1] = $value1;
                        break;
                }

            }
        }
    }

    /* iterate through all elements of the $_GET array and DB_escape_string them
    to limit possibility for SQL injection attacks and cross scripting attacks
     */
    foreach ($_GET as $key => $value) {
        if (gettype($value) != "array") {
            $_GET[$key] = DB_escape_string($value);
        }
    }
}

if (!isset($AllowAnyone)) {
    /* only do security checks if AllowAnyone is not true */

    if (!isset($_SESSION['AccessLevel']) or $_SESSION['AccessLevel'] == '' or
        (isset($_POST['UserNameEntryField']) and $_POST['UserNameEntryField'] != '')) {

        /* if not logged in */

        $_SESSION['AccessLevel']       = '';
        $_SESSION['CustomerID']        = '';
        $_SESSION['UserBranch']        = '';
        $_SESSION['SalesmanLogin']     = '';
        $_SESSION['Module']            = '';
        $_SESSION['PageSize']          = '';
        $_SESSION['UserStockLocation'] = '';
        $_SESSION['AttemptsCounter']++;
        $_SESSION['DefaultArea']   = '';
        $_SESSION['DefaultUnidad'] = '';
        $_SESSION['discount1']     = 0;
        $_SESSION['discount2']     = 0;
        $_SESSION['discount3']     = 0;
        $_SESSION['creditlimit']   = 0;
        //$_SESSION['XSA']="http://173.205.254.10/";
        $theme = 'silverwolf';

        // Show login screen
        if (!isset($_POST['UserNameEntryField']) or $_POST['UserNameEntryField'] == '') {
            include $PathPrefix . 'includes/Login.php';
            exit;
        }

        $sql = "SELECT www_users.fullaccess,
                www_users.customerid,
                www_users.lastvisitdate,
                www_users.pagesize,
                www_users.defaultarea,
                www_users.branchcode,
                www_users.modulesallowed,
                www_users.blocked,
                www_users.realname,
                www_users.theme,
                www_users.displayrecordsmax,
                www_users.userid,
                www_users.language,
                www_users.salesman,
                www_users.defaultunidadNegocio ,
                www_users.discount1 ,
                www_users.discount2 ,
                www_users.discount3 ,
                www_users.creditlimit,
                 www_users.email,
                www_users.ShowIndex,
                tags.tagdescription,
                www_users.defaultlocation,
                locations.locationname,
                www_users.login,
                www_users.dtm_ultimo_acceso_token
            FROM www_users
                LEFT JOIN tags ON www_users.defaultunidadNegocio = tags.tagref
                LEFT JOIN locations ON www_users.defaultlocation = locations.loccode
            WHERE www_users.userid='" . DB_escape_string($_POST['UserNameEntryField']) . "'
            AND (www_users.password='" . CryptPass(DB_escape_string($_POST['Password'])) . "'
            OR  www_users.password='" . DB_escape_string($_POST['Password']) . "')";
        $Auth_Result = DB_query($sql, $db);
        // Populate session variables with data base results
        if (DB_num_rows($Auth_Result) > 0) {

            $myrow = DB_fetch_row($Auth_Result);
            if ($myrow[7] == 1) {
                //the account is blocked
                $userBlocked = 1;
                die(include ($PathPrefix . 'includes/FailedLogin.php'));
            }
            $crearToken = false;
            $fechaLoginActual = new DateTime(date('Y-m-d H:i:s'));
            $fechaLoginConsulta = new DateTime($myrow[25]);
            $fechaLoginDiferencia = $fechaLoginActual->diff($fechaLoginConsulta);
            $_SESSION['useridTemp'] = $_POST['UserNameEntryField'];

            $minutosLogin = $fechaLoginDiferencia->days * 24 * 60;
            $minutosLogin += $fechaLoginDiferencia->h * 60;
            $minutosLogin += $fechaLoginDiferencia->i;

            $crearToken = ( $fechaLoginActual<$fechaLoginConsulta ? true : $crearToken );
            $crearToken = ( $fechaLoginActual>$fechaLoginConsulta&&$minutosLogin>=15 ? true : $crearToken );
            if(!$crearToken){
                //the account is blocked
                die(include ($PathPrefix . 'includes/FailedLogin.php'));
            }

            $SQL = "UPDATE `www_users` SET `ln_token` = NULL, `dtm_ultimo_acceso_token` = '1900-01-01 00:00:00' WHERE `userid` = '".DB_escape_string($_POST['UserNameEntryField'])."'";

            $ErrMsg = "No se pudo almacenar la información";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            /*reset the attempts counter on successful login */
            $_SESSION['AttemptsCounter'] = 0;
            $_SESSION['AccessLevel']     = $myrow[0];
            $_SESSION['CustomerID']      = $myrow[1];
            $_SESSION['UserBranch']      = $myrow[5];
            $_SESSION['DefaultPageSize'] = $myrow[3];

            //$_SESSION['UserStockLocation'] = $myrow[4]; // era el campo defaultlocation
            $_SESSION['ModulesEnabled']      = explode(",", $myrow[6]);
            $_SESSION['UsersRealName']       = $myrow[8];
            $_SESSION['Theme']               = $myrow[9];
            $_SESSION['UserID']              = $myrow[11];
            $_SESSION['Language']            = $myrow[12];
            $_SESSION['SalesmanLogin']       = $myrow[13];
            $_SESSION['DefaultUnidad']       = $myrow[14];
            $_SESSION['discount1']           = $myrow[15];
            $_SESSION['discount2']           = $myrow[16];
            $_SESSION['discount3']           = $myrow[17];
            $_SESSION['creditlimit']         = $myrow[18];
            $_SESSION['usremail']            = $myrow[19];
            $_SESSION['ShowIndex']           = $myrow[20];
            $_SESSION['OpcionMenu']          = $myrow[20];
            $_SESSION['ShowIndex']           = $_SESSION['OpcionMenu'];
            $_SESSION['defaulttagname']      = $myrow[21];
            $_SESSION['defaultlocation']     = $myrow[22];
            $_SESSION['defaultlocationname'] = $myrow[23];
            $_SESSION['Login'] = $myrow[24];

            $_SESSION['ejercicioFiscal'] = DB_escape_string($_POST['txtEjercioFiscal']);
            //echo "<br>Session: " . $_SESSION['ShowIndex'];

            //echo "entra akiiii";
            //exit;

            if ($myrow[10] > 0) {
                $_SESSION['DisplayRecordsMax'] = $myrow[10];
            } else {
                $_SESSION['DisplayRecordsMax'] = 15; # $_SESSION['DefaultDisplayRecordsMax'];  // default comes from config.php
            }

            //Seleccion de nuevo men� o viejo menu
            if ($_SESSION['ShowIndex'] != 0) {
                $sec_functions = "sec_functions_new";
            } else {
                $sec_functions = "sec_functions";
            }

            $sql = "SELECT b.id_boletin,b.funcion,smd.moduleid,smd.title as modulo,s.submoduleid,sm.moduleid,
                sm.title as submodulo,s.categoryid,sc.name as categoria,b.estado,b.usuario,b.fecha,b.descripcion,
                s.submoduleid,s.shortdescription,s.submoduleid,sp.userid
                FROM boletines b
                inner join $sec_functions s on b.funcion=s.functionid
                inner join sec_submodules sm on s.submoduleid=sm.submoduleid
                inner join sec_categories sc on s.categoryid=sc.categoryid
                inner join sec_modules smd on sm.moduleid=smd.moduleid
                inner join sec_funxprofile sf on s.functionid=sf.functionid
                inner join sec_profilexuser sp on sf.profileid=sp.profileid
                where b.fecha <= now()
                and b.fecha >= date_sub(now(), interval 1 month)
                and sp.userid='" . $_SESSION['UserID'] . "'";
            $result = DB_query($sql, $db);

            if (DB_num_rows($result) > 0) {
                echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/ReportBoletines.php?">';
            }
            $_SESSION['DefaultArea'] = $myrow[4];

            if (isset($_SESSION['LastAction']) && $_SESSION['LastAction'] == "ACTIVO") {
                $sql = "UPDATE www_users SET lastvisitdate='" . date("Y-m-d H:i:s") . "'
                        WHERE www_users.userid='" . DB_escape_string($_POST['UserNameEntryField']) . "'";
                $Auth_Result = DB_query($sql, $db);
            }
            /*get the security tokens that the user has access to */
            $sql = 'SELECT tokenid FROM securitygroups
                    WHERE secroleid =  ' . $_SESSION['AccessLevel'];
            $Sec_Result = DB_query($sql, $db);

            $_SESSION['AllowedPageSecurityTokens'] = array();
            if (DB_num_rows($Sec_Result) == 0) {
                $title = _('Reporte de Error de Cuenta de Usuario');
                include $PathPrefix . 'includes/header.inc';
                echo '<br /><br /><br />';
                prnMsg(_('El Perfil de tu Usuario no tiene ningun acceso definido para GRP. Existe un error en la configuracion de esta cuenta de usuario'), 'error');
                include $PathPrefix . 'includes/footer_Index.inc';
                exit;
            } else {
                $i = 0;
                while ($myrow = DB_fetch_row($Sec_Result)) {
                    $_SESSION['AllowedPageSecurityTokens'][$i] = $myrow[0];
                    $i++;
                }
            }

            echo '<meta http-equiv="refresh" content="0" url="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
            exit;
        } else {
            // Incorrect password

            // 5 login attempts, show failed login screen
            if (!isset($_SESSION['AttemptsCounter'])) {
                $_SESSION['AttemptsCounter'] = 0;

            } elseif ($_SESSION['AttemptsCounter'] >= 5 and isset($_POST['UserNameEntryField'])) {
                /*User blocked from future accesses until sysadmin releases */

                /* COMENTADO PARA INICIO DE OPERACIONES, DESPUES ANALIZAR LA CONVENIENCIA DE DESBLOQUEARLO */
                $sql = "UPDATE www_users
                        SET blocked=0
                    WHERE www_users.userid='" . $_POST['UserNameEntryField'] . "'";

                $Auth_Result = DB_query($sql, $db);
                die(include ($PathPrefix . 'includes/FailedLogin.php'));
            }
            $demo_text = '<font size="3" color="red"><b>' . _('Datos de Acceso Incorrectos') . '</b></font><br /><b>' . _('La combinaci&oacute;n de Usuario/Contrase&ntilde;a') . '<br />' . _('No son Válidos en el Sistema') . '</b>';
            die(include ($PathPrefix . 'includes/Login.php'));
        }
    } // End of userid/password check
    // Run with debugging messages for the system administrator(s) but not anyone else

    if (in_array(15, $_SESSION['AllowedPageSecurityTokens'])) {
        $debug = 0;
    } else {
        $debug = 0;
    }

} /* only do security checks if AllowAnyone is not true */

/*User is logged in so get configuration parameters  - save in session*/
include $PathPrefix . 'includes/GetConfig.php';

if (isset($_SESSION['DB_Maintenance'])) {
    if ($_SESSION['DB_Maintenance'] != 0) {
        if (DateDiff(Date($_SESSION['DefaultDateFormat']),
            ConvertSQLDate($_SESSION['DB_Maintenance_LastRun'])
            , 'd') > $_SESSION['DB_Maintenance']) {

            /*Do the DB maintenance routing for the DB_type selected */
            DB_Maintenance($db);
            //echo "entra";
            //purge the audit trail if necessary
            if (isset($_SESSION['MonthsAuditTrail'])) {
                // Actualizar  nombre de tabla de audittrail por mes
                $nombremes = glsnombremeslargo(date(m) - $_SESSION['MonthsAuditTrail']);
                //Verifica si la tabla existe la tabla de log por mes
                $sql    = "show tables   from " . $_SESSION['DatabaseName'] . " where Tables_in_" . $_SESSION['DatabaseName'] . "='audittrail" . $nombremes . date(Y) . "'";
                $result = DB_query($sql, $db);
                if (DB_num_rows($result) == 0) {
                    // CREA TABLA EN CASO DE QUE NO EXISTA

                    $sql = "CREATE  TABLE IF NOT EXISTS `audittrail" . $nombremes . date(Y) . "`";
                    $sql = $sql . "(
                      `transactiondate` datetime NOT NULL default '0000-00-00 00:00:00',
                      `userid` varchar(20) NOT NULL default '',
                      `querystring` text,
                      KEY `UserID` (`userid`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

                    $Auth_TablaResult = DB_query($sql, $db);
                    //INSERTA DATOS EN TABLA DE MES
                    $sql = " INSERT `audittrail" . $nombremes . date(Y) . "`
                        SELECT * FROM audittrail
                            WHERE  transactiondate <= '" . Date('Y-m-d', mktime(0, 0, 0, Date('m') - $_SESSION['MonthsAuditTrail'])) . "'";
                    $result = DB_query($sql, $db);
                    //echo $sql;
                    //exit;
                    //ELIMINA REGISTROS DE LA TABLA PRINCIPAL DEL AUDITTRAIL.

                    $sql = "DELETE FROM audittrail
                            WHERE  transactiondate <= '" . Date('Y-m-d', mktime(0, 0, 0, Date('m') - $_SESSION['MonthsAuditTrail'])) . "'";
                    $ErrMsg = _('Hubo un problema eliminando historia de logs de auditoria expirados');
                    $result = DB_query($sql, $db);

                    $sql    = "UPDATE config set confvalue=date_format(now(),'%Y-%m-%d') where confname='DB_Maintenance_LastRun'";
                    $ErrMsg = _('Hubo un problema eliminando historia de logs de auditoria expirados');
                    $result = DB_query($sql, $db);

                }
            }
            $_SESSION['DB_Maintenance_LastRun'] = Date('Y-m-d');
        }
    }
}

if (!function_exists('avisos_mantenimiento')) {
    function avisos_mantenimiento($db)
    {
        ###################################### INICIO-> AVISOS MANTENIMIENTO ########################################################

        //AVISOS CONFIGURADOS//
        $sql = "SELECT id_manto,fecha_registro,fecha_modificacion,userid,titulo,descripcion,activo,fecha_inicio,fecha_estimada_termino
                FROM sec_mantenimientos
                WHERE activo=1";

        $Aviso = DB_query($sql, $db);
        $div   = "";
        if (DB_num_rows($Aviso) > 0) {
            while ($Avisos = DB_fetch_array($Aviso)) {
                $id_aviso      = $Avisos['id_manto'];
                $aTitulo       = $Avisos['titulo'];
                $aMensaje      = $Avisos['descripcion'];
                $afecha_inicio = $Avisos['fecha_inicio'];
                $afecha_final  = $Avisos['fecha_estimada_termino'];
                $Mensaje       = html_entity_decode(str_replace('**', '"', $aMensaje));

                /* Seccion de mensajes
                 * Para que se muestre un mensaje es necesario registrarlo en la tabla "sec_mantenimientos"
                 * se debe especificar el titulo de mensaje, el contenido , el rengo de fechas para visualizarse
                 * el mensaje debe estar activo, el que se mostrara solo cuando la variable de sesion este
                 * $_SESSION['DB_Maintenance']=1
                 */
                //$div ='<body>';
                $div = '<html>';
                $div .= '    <head>';
                $div .= '        <title>Sistema en Mantenimiento</title>';
                $div .= '        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
                $div .= '        <style>';
                $div .= '        h1 { font-size: 30px; }';
                $div .= '        body {
                                        padding: 0px;
                                        margin: 0px;
                                        text-align: center;
                                        font: 12px "Lucida Grande", "Arial", "Helvetica", "Verdana", "sans-serif";
                                        background: #fff url(images/stripe.png) repeat;
                                        color: #777;
                                        background-color: #6E6E6E;
                                        background-position: center top;
                                        }

                                #container {
                                        text-align: center;
                                        margin-top: 0px;
                                        margin-left: auto;
                                        margin-right: auto;
                                        position: relative;
                                        z-index: 1000;
                                        width: 600px;
                                        background-image: url(images/hoja_membretada.png);
                                        background-size: 100%;
                                    }';
                $div .= '        </style>';
                $div .= '    </head>';
                $div .= '    <body  >';
                $div .= '   <div id="container" background="images/hoja_membretada.png">';
                $div .= '        <br/><br/><br/><br/><br/><br/>
                                <h1>Sistema en Mantenimiento</h1>';
                $div .= '           <form id="manto" method="post" action="index.php">';
                $div .= '                   <img src="http://tampograficas.com/mantenimiento/site-maintenance.gif">';
                $div .= '                   <div title="' . $aTitulo . '">';
                $div .= '                       <h2> "' . $aTitulo . '" </h2>';
                $div .= '                       <p>' . $Mensaje . '</p>';
                $div .= '                   </div>';
                $div .= '                   <input type="image" src="images/b_regresar_25.png" alt="Submit" title="Regresar">';
                $div .= '               </form>';

                $div .= '       </body>';
                $div .= '       </div>';
                $div .= '   </html>';
            }
        }
        echo $div;
        ###################################### FIN-> AVISOS MANTENIMIENTO ########################################################
    }
}

if (isset($_SESSION['DB_Maintenance_Update'])) {
    $opcion = $_SESSION['DB_Maintenance_Update'];
    switch ($opcion) {
        case '0':
            {
                //Estado de producción
            }
            break;
        case 'ACTUALIZANDO':
            {
                // Base de datos en mantenimiento
                avisos_mantenimiento($db);
                unset($_SESSION);
                session_destroy();
                exit();
            }
            break;
        case 'CANCELADO': // Cliente fuera de servicio
            {
                $div = '<html>';
                $div .= '    <head>';
                $div .= '        <title>GRP</title>';
                $div .= '        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
                $div .= '        <style>';
                $div .= '        h1 { font-size: 50px; }';
                $div .= '        body { text-align:center; font: 20px Helvetica, sans-serif; color: #333; }';
                $div .= '        </style>';
                $div .= '    </head>';
                $div .= '    <body>';
                $div .= '        <h1>Sistema Fuera de Linea</h1>';
                $div .= '       <form method="post" action="index.php">';
                $div .= '               <img src="http://tampograficas.com/mantenimiento/site-maintenance.gif">';
                $div .= '               <div>';
                $div .= '                   <h2> "IMPORTANTE" </h2>';
                $div .= '                   <p>El Acceso al sistema se encuentra CANCELADO.</p>';
                $div .= '               </div>';
                $div .= '               <input type="image" src="images/b_regresar_25.png" alt="Submit" title="Regresar">';
                $div .= '           </form>';
                $div .= '       </body>';
                $div .= '   </html>';
                echo $div;
                unset($_SESSION);
                session_destroy();
                exit();
            }
            break;
        case 'SUSPENDIDO': // Cliente fuera de servicio
            {
                $div = '<html>';
                $div .= '    <head>';
                $div .= '        <title>GRP</title>';
                $div .= '        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
                $div .= '        <style>';
                $div .= '        h1 { font-size: 50px; }';
                $div .= '        body { text-align:center; font: 20px Helvetica, sans-serif; color: #333; }';
                $div .= '        </style>';
                $div .= '    </head>';
                $div .= '    <body>';
                $div .= '        <h1>Sistema Suspendido</h1>';
                $div .= '       <form method="post" action="index.php">';
                $div .= '               <img src="http://tampograficas.com/mantenimiento/site-maintenance.gif">';
                $div .= '               <div>';
                $div .= '                   <h2> "IMPORTANTE" </h2>';
                $div .= '                   <p>El sistema se encuentra suspendido, por favor contacte a su Administrador.</p>';
                $div .= '               </div>';
                $div .= '               <input type="image" src="images/b_regresar_25.png" alt="Submit" title="Regresar">';
                $div .= '           </form>';
                $div .= '       </body>';
                $div .= '   </html>';
                echo $div;
                unset($_SESSION);
                session_destroy();
                exit();
            }
            break;
    }
}

// if ($funcion == 1226) {
//     echo "<br>UpdateCurrencyRatesDaily: ".$_SESSION['UpdateCurrencyRatesDaily'];
//     echo "<br>tiposCambio: ";
//     print_r($tiposCambio);
// }

if (isset($_SESSION['UpdateCurrencyRatesDaily'])) {
    if ($_SESSION['UpdateCurrencyRatesDaily'] != 0) {
        if (DateDiff(date($_SESSION['DefaultDateFormat']),ConvertSQLDate($_SESSION['UpdateCurrencyRatesDaily']), 'd') > 0) {

            // Definicion includes/MiscFunctions.php
            $CurrencyRates = GetECBCurrencyRates();
            
            $CurrenciesResult = DB_query('SELECT currabrev FROM currencies', $db);
            while ($CurrencyRow = DB_fetch_row($CurrenciesResult)) {

                if ($CurrencyRow[0] == 'EUR') {
                    $tipocambioeuro   = GetAllCurrencyRatesEUR();
                    $InsertTipoCambio = "INSERT INTO tipocambio(currency,rate,origrate,fecha)
                                        VALUES ('EUR',
                                            '" . (1 / $tipocambioeuro) . "',
                                            '" . (1 / $tipocambioeuro) . "',
                                            Now())";

                    $ErrMsg = _('Hubo un problema actualizando el tipo de cambio para EUR');
                    $result = DB_query($InsertTipoCambio, $db, $ErrMsg, $DbgMsg);

                    $UpdateCurrRateResult = DB_query('UPDATE currencies SET rate=' . (1 / $tipocambioeuro) . " WHERE currabrev='EUR'", $db);
                }

            }

            //$tiposCambio = GetAllCurrencyRatesFromPeriodDLS(Date('d/m/Y'), Date('d/m/Y'));
            $tiposCambio = GetAllCurrencyRatesFromPeriodDLS(Date('d/m/Y'), Date('d/m/Y'));
            
            // if ($funcion == 1226) {
            //     echo "<br>UpdateCurrencyRatesDaily: ".$_SESSION['UpdateCurrencyRatesDaily'];
            //     echo "<br>tiposCambio: ";
            //     print_r($tiposCambio);
            // }

            if (count($tiposCambio) > 0) {
                for ($loopid = 1; $loopid <= count($tiposCambio); $loopid++) {

                    if (is_numeric($tiposCambio[$loopid]['rate'])) {
                        if (checkdate(substr($tiposCambio[$loopid]['date'], 3, 2), substr($tiposCambio[$loopid]['date'], 0, 2), substr($tiposCambio[$loopid]['date'], 6, 4))) {
                            $InsertTipoCambio = "INSERT INTO tipocambio(currency,rate,origrate,fecha)
                                        VALUES ('USD','" . (1 / $tiposCambio[$loopid]['rate']) . "','" . (1 / $tiposCambio[$loopid]['rate']) . "','" . substr($tiposCambio[$loopid]['date'], 6, 4) . '/' . substr($tiposCambio[$loopid]['date'], 3, 2) . '/' . substr($tiposCambio[$loopid]['date'], 0, 2) . "')";

                            $ErrMsg = _('Hubo un problema actualizando el tipo de cambio para ' . $tiposCambio[$loopid]['date']);
                            $result = DB_query($InsertTipoCambio, $db, $ErrMsg, $DbgMsg);

                            $UpdateCurrRateResult = DB_query('UPDATE currencies SET
                                            rate=' . (1 / $tiposCambio[$loopid]['rate']) . "
                                            WHERE currabrev='USD'", $db);

                            if($InsertTipoCambio and $UpdateCurrRateResult) {
                                $sql    = "UPDATE config set confvalue=date_format(now(),'%Y-%m-%d') where confname='UpdateCurrencyRatesDaily'";
                                $ErrMsg = _('Hubo un problema actualizando la ultima fecha de actualizacion de tipo de cambio!');
                                $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

                                //para que no siga actualizando dentro de la misma session
                                $_SESSION['UpdateCurrencyRatesDaily'] = Date('Y-m-d');
                            }

                            if (isset($_SESSION['TipoCambioDiaAnterior']) and $_SESSION['TipoCambioDiaAnterior'] == 1) {

                                //obtengo la ultima actualizacion del TC
                                $sql = "SELECT origrate,
                                               currency
                                        FROM tipocambio
                                        WHERE fecha < '" . $_SESSION['UpdateCurrencyRatesDaily'] . "'
                                        ORDER BY fecha DESC LIMIT 1";
                                $rstc = DB_query($sql, $db);
                                $row  = DB_fetch_array($rstc);

                                $sql = "UPDATE tipocambio
                                        SET rate = " . $row['origrate'] . "
                                        WHERE fecha = '" . $_SESSION['UpdateCurrencyRatesDaily'] . "'
                                        AND currency = '" . $row['currency'] . "'";
                                $r = DB_query($sql, $db);
                            }

                            prnMsg('Se actualizó el tipo de cambio del dia ' . $tiposCambio[$loopid]['date'] . ' con valor ' . $tiposCambio[$loopid]['rate'] . ' de acuerdo al diario oficial de la federacion', 'success');
                        } else {
                            /*if(date('N') != DOMINGO) {
                        prnMsg('No fue posible actualizar el tipo de cambio del dia '.$tiposCambio[$loopid]['date'].' porque la fecha no es valida !, consultar con el administrador del sistema!','error');
                        }*/
                        }

                    } else {
                        /*if(date('N') != DOMINGO) {
                    prnMsg('No fue posible actualizar el tipo de cambio del dia '.$tiposCambio[$loopid]['date'].' valor no numerico:'.$tiposCambio[$loopid]['rate'].' !, consultar con el administrador del sistema!','error');
                    }*/
                    }

                }
            } else {

                /*if(date('N') != DOMINGO) {
            prnMsg('No fue posible actualizar el tipo de cambio, resultado sin valores !, DE:'.ConvertSQLDate($_SESSION['UpdateCurrencyRatesDaily']).' HASTA:'.ConvertSQLDate(Date('Y-m-d')).' consultar con el administrador del sistema!','error');
            }*/
            }

            //$_SESSION['UpdateCurrencyRatesDaily'] = Date('Y-m-d');
            //$UpdateConfigResult = DB_query("UPDATE config SET confvalue = '" . Date('Y-m-d') . "' WHERE confname='UpdateCurrencyRatesDaily'",$db);
        }
    }
}

if (isset($_POST['Theme']) && ($_SESSION['UsersRealName'] == $_POST['RealName'])) {
    $_SESSION['Theme'] = $_POST['Theme'];
    $theme             = $_POST['Theme'];
} elseif (!isset($_SESSION['Theme'])) {
    $theme             = $_SESSION['DefaultTheme'];
    $_SESSION['Theme'] = $_SESSION['DefaultTheme'];

} else {
    $theme = $_SESSION['Theme'];
}

/* Set the logo if not yet set.
 * will be done only once per session and each time
 * we are not in session (i.e. before login)
 */
if (empty($_SESSION['LogoFile'])) {
    /* find a logo in companies/$CompanyDir
     * (nice side effect of function:
     * variables are local, so we will never
     * cause name clashes)
     */

    function findLogoFile($CompanyDir, $PathPrefix)
    {
        $dir       = $PathPrefix . 'companies/' . $CompanyDir . '/';
        $DirHandle = dir($dir);
        while ($DirEntry = $DirHandle->read()) {
            if ($DirEntry != '.' and $DirEntry != '..') {
                $InCompanyDir[] = $DirEntry; //make an array of all files under company directory
            }
        } //loop through list of files in the company directory
        if ($InCompanyDir !== false) {
            foreach ($InCompanyDir as $logofilename) {
                if (strncasecmp($logofilename, 'logo.jpg', 8) === 0 and
                    is_readable($dir . $logofilename) and
                    is_file($dir . $logofilename)) {
                    $logo = $logofilename;
                    break;
                }
            }

            if (empty($logo)) {
                return null;
            } else {
                return 'companies/' . $CompanyDir . '/' . $logo;
            }
        } //end listing of files under company directory is not empty
    }

    /* Find a logo in companies/<company of this session> */
    if (!empty($_SESSION['DatabaseName'])) {
        $_SESSION['LogoFile'] = findLogoFile($_SESSION['DatabaseName'], $PathPrefix);
    }

}

if ($_SESSION['HTTPS_Only'] == 1) {
    if ($_SERVER['HTTPS'] != 'on') {
        prnMsg(_('GRP tecnoaplicada esta configurado para permitir solo conecciones encriptadas SSL seguras.') . '<br>Intentar entrar cambiando a <a href="' . str_replace("https", "http", $rootpath) . '">' . _('pulse aqui') . '</a>' . _('para continuar'), 'error');
        exit;
    }
}

function CryptPass($Password)
{
    global $CryptFunction;
    if ($CryptFunction == 'sha1') {
        return sha1($Password);
    } elseif ($CryptFunction == 'md5') {
        return md5($Password);
    } else {
        return $Password;
    }
}

