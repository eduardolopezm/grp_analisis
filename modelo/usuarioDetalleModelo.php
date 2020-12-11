<?php
/**
 * Resguardo de activo fijo.
 *
 * @category     Panel
 * @package      ap_grp
 * @version      0.1
 * @link /resguardo_detalles_modelo.php
 * Fecha Creación: 05.04.18
 * Se genera el presente programa para la visualización de la información
 * del detalle de los resguardos.
 */
// 

$PageSecurity = 1;
$PathPrefix = '../';
$funcion=90;

// include($PathPrefix.'includes/session.inc');
 session_start();
include($PathPrefix . 'config.php');
include $PathPrefix . "includes/SecurityUrl.php";
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

header('Content-type: text/html; charset=ISO-8859-1');
$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option =$_POST['option'];
$enc = new Encryption;

define('SUBIDAARCHIVOS', realpath(dirname(__FILE__)) . '/' . $PathPrefix . 'archivos/');

if($option == "obtenerInfoUsuario"){
    $post = $_POST;

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!                                               !!
    //!!     OBTENER INFORMACION GENERAL USUARIO.      !!
    //!!                                               !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    $datosGeneral[] ="";

    $SQL = "SELECT www_users.userid, 
                www_users.realname, 
                www_users.phone, 
                www_users.email,
                www_users.customerid, 
                www_users.branchcode, 
                www_users.salesman, 
                www_users.obraid,
                date_format(www_users.lastvisitdate, '%d-%m-%Y') as lastvisitdate,
                www_users.fullaccess, 
                www_users.pagesize, 
                www_users.theme,
                www_users.language, 
                (sec_profiles.name) as primerperfil, 
                www_users.blocked, 
                www_users.department,
                www_users.ImagenUsuario,
                tb_www_user_estatus.descripcion as desc_estatus
            FROM www_users
            INNER JOIN sec_unegsxuser on www_users.userid= sec_unegsxuser.userid
            LEFT join tb_www_user_estatus on www_users.blocked = tb_www_user_estatus.id
            LEFT JOIN sec_profilexuser ON www_users.userid = sec_profilexuser.userid
            LEFT JOIN  sec_profiles ON sec_profilexuser.profileid = sec_profiles.profileid
            WHERE 1=1 AND www_users.userid = '".$post['userid']."'
            GROUP BY www_users.userid, 
                www_users.realname, 
                www_users.phone, 
                www_users.email,
                www_users.customerid, 
                www_users.branchcode, 
                www_users.salesman, 
                www_users.lastvisitdate, 
                www_users.fullaccess, 
                www_users.pagesize, 
                www_users.theme,
                www_users.language,         
                www_users.blocked;";

    $TransResult = DB_query($SQL, $db);
    $myrowgeneral = DB_fetch_array($TransResult);
    
    $datosGeneral['userid'] = $myrowgeneral ['userid'];
    $datosGeneral['userid'] = $myrowgeneral ['userid'];
    $datosGeneral['realname'] = $myrowgeneral ['realname'];
    $datosGeneral['phone'] = $myrowgeneral ['phone'];
    $datosGeneral['email'] = $myrowgeneral ['email'];
    $datosGeneral['obraid'] = $myrowgeneral ['obraid'];
    $datosGeneral['department'] = $myrowgeneral ['department'];
    $datosGeneral['estatus'] = $myrowgeneral ['blocked'];
    $datosGeneral['ImagenUsuario'] = $myrowgeneral ['ImagenUsuario'];

    $infoGeneral[] = $datosGeneral;
    
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!                                               !!
    //!!    OBTENER LOS PERFILES DEL USUARIO.          !!
    //!!                                               !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    $datos[] ="";

    $SQL = "SELECT sec_profiles.profileid,
                    sec_profiles.name,
                    coalesce(sec_profilexuser.valor,0) as valor
            FROM sec_profiles 
            LEFT JOIN (SELECT DISTINCT profileid, 1 as valor FROM sec_profilexuser WHERE userid = '".$post['userid']."' )  sec_profilexuser 
                ON sec_profiles.profileid = sec_profilexuser.profileid
            WHERE sec_profiles.active = 1";

    $TransResult = DB_query($SQL, $db);

    while ( $myrow = DB_fetch_array($TransResult)) {
        $datos['profileid'] = $myrow ['profileid'];
        $datos['profilename'] = $myrow ['name'];
        $datos['profilevalue'] = $myrow ['valor'];
        $infoPerfil[] = $datos;
    }

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!      OBTENER OBJETOS PRINCIPALES.        !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    $datosOB[] ="";     
    $SQL = "SELECT sec_objetoprincipalxuser.loccode
            FROM sec_objetoprincipalxuser WHERE userid = '".$post['userid']."'";

    $TransResult = DB_query($SQL, $db);

    
    while ( $myrow = DB_fetch_array($TransResult)) {
        $datosOB['loccode'] = $myrow ['loccode'];
        $infoOB[] = $datosOB;
    }

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!      OBTENER LAS UNIDADES RESPONSABLE.        !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    $datosUR[] ="";     
    $SQL = "SELECT legalbusinessunit.legalid,
	defaultunidadNegocio,
                    legalbusinessunit.legalname,
                    tags.tagref,
                    tags.tagdescription,
                    coalesce(sec_unegsxuser.valor,0) AS valor,
                    IF(IFNULL(defaultunidadNegocio, '') = '', '0', 1) AS valDefault
            FROM legalbusinessunit
            INNER JOIN tags ON legalbusinessunit.legalid = tags.legalid
            inner JOIN (SELECT DISTINCT tagref, 1 as valor FROM sec_unegsxuser WHERE userid='".$_SESSION['UserID']."') sec_unegsxuser2 ON  tags.tagref = sec_unegsxuser2.tagref
            LEFT JOIN www_users on tags.tagref =  defaultunidadNegocio and userid ='".$post['userid']."' 
            LEFT JOIN (SELECT DISTINCT tagref, 1 as valor FROM sec_unegsxuser WHERE userid='".$post['userid']."') sec_unegsxuser ON  tags.tagref = sec_unegsxuser.tagref;";

    $TransResult = DB_query($SQL, $db);

    
    while ( $myrow = DB_fetch_array($TransResult)) {
        $datosUR['legalid'] = $myrow ['legalid'];
        $datosUR['legalname'] = $myrow ['legalname'];
        $datosUR['tagref'] = $myrow ['tagref'];
        $datosUR['tagdescription'] = $myrow ['tagdescription'];
        $datosUR['valor'] = $myrow ['valor'];
        $datosUR['valDefault'] = $myrow ['valDefault'];
        $infoUR[] = $datosUR;
    }

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!      OBTENER LAS UNIDADES EJECUTORAS.         !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    $datosUE[] ="";

    $SQL = "SELECT legalbusinessunit.legalid, 
                    legalbusinessunit.legalname,
                    tags.tagref,
                    tags.tagdescription,
                    tb_cat_unidades_ejecutoras.ue,
                    CONCAT(tb_cat_unidades_ejecutoras.ue ,' - ',tb_cat_unidades_ejecutoras.desc_ue) AS desc_ue,
                    coalesce(tb_sec_users_ue.valor,0) as valor,
                    IF(IFNULL(ln_ue, '') = '', '0', 1) AS valDefault
            FROM legalbusinessunit
            INNER JOIN tags ON legalbusinessunit.legalid = tags.legalid
            INNER JOIN tb_cat_unidades_ejecutoras ON tags.tagref = tb_cat_unidades_ejecutoras.ur
            INNER JOIN (SELECT  tagref, ue FROM tb_sec_users_ue WHERE userid='".$_SESSION['UserID']."' ) tb_sec_users_ue2 ON tb_cat_unidades_ejecutoras.ur =  tb_sec_users_ue2.tagref and tb_cat_unidades_ejecutoras.ue = tb_sec_users_ue2.ue
            LEFT JOIN www_users on tb_cat_unidades_ejecutoras.ue =  ln_ue and userid ='".$post['userid']."'
            LEFT JOIN (SELECT  tagref, ue, 1 as valor FROM tb_sec_users_ue WHERE userid='".$post['userid']."' ) tb_sec_users_ue ON tb_cat_unidades_ejecutoras.ur =  tb_sec_users_ue.tagref and tb_cat_unidades_ejecutoras.ue = tb_sec_users_ue.ue;";

    $TransResult = DB_query($SQL, $db);

    while ( $myrow = DB_fetch_array($TransResult)) {
        $datosUE['legalid'] = $myrow ['legalid'];
        $datosUE['legalname'] = $myrow ['legalname'];
        $datosUE['tagref'] = $myrow ['tagref'];
        $datosUE['tagdescription'] = $myrow ['tagdescription'];
        $datosUE['ue'] = $myrow ['ue'];
        $datosUE['desc_ue'] = $myrow ['desc_ue'];
        $datosUE['valor'] = $myrow ['valor'];
        $datosUE['valDefault'] = $myrow ['valDefault'];
        $infoUE[] = $datosUE;
    }

    $datosAlmacen[] ="";
    // $infoAlmacen[]="";
    if(count($infoUE) > 0){

        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        //!!            OBTENER ALMACENES.                 !!
        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        $SQL = "SELECT locations.ln_ue, 
                        locations.loccode, 
                        locations.locationname, 
                        coalesce(tb_locuser.valor,0) AS valor, 
                        desc_ue,
                        COALESCE(tb_Default.valDefault,0) AS valDefault
                FROM locations 
                LEFT JOIN tb_cat_unidades_ejecutoras ON locations.ln_ue = tb_cat_unidades_ejecutoras.ue
                LEFT JOIN (SELECT loccode, 1 AS valor FROM sec_loccxusser WHERE userid = '".$post['userid']."') tb_locuser ON  locations.loccode = tb_locuser.loccode
                LEFT JOIN (SELECT defaultlocation, 1 as valDefault FROM www_users WHERE userid ='".$post['userid']."') tb_Default ON locations.loccode = tb_Default.defaultlocation
                WHERE ln_ue IN (SELECT ue FROM tb_sec_users_ue WHERE userid='".$post['userid']."')
                ORDER BY locations.ln_ue, locations.loccode;";

        $TransResult = DB_query($SQL, $db);

        while ( $myrow = DB_fetch_array($TransResult)) {
            $datosAlmacen['ln_ue'] = $myrow ['ln_ue'];
            $datosAlmacen['desc_ue'] = $myrow ['desc_ue'];
            $datosAlmacen['loccode'] = $myrow ['loccode'];
            $datosAlmacen['locationname'] = $myrow ['locationname'];
            $datosAlmacen['valor'] = $myrow ['valor'];
            $datosAlmacen['valDefault'] = $myrow ['valDefault'];

            $infoAlmacen[] = $datosAlmacen;
        }
    }

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!            OBTENER CAPITULOS.                 !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    $datosCapitulos[] ="";

    $SQL = "SELECT tb_capitulos.ccap, 
                CONCAT(tb_capitulos.ccapmiles,' - ',tb_capitulos.descripcion) AS descripcion, 
                tb_capitulos.ccapmiles, 
                coalesce(sec_capituloxuser.valor,0) AS valor
            FROM tb_cat_partidaspresupuestales_capitulo tb_capitulos
            LEFT JOIN (SELECT DISTINCT sn_capitulo, 1 AS valor FROM sec_capituloxuser WHERE sn_userid = '".$post['userid']."')  sec_capituloxuser ON tb_capitulos.ccapmiles = sec_capituloxuser.sn_capitulo
            WHERE tb_capitulos.activo = 1 AND tb_capitulos.ccapmiles !='';";

    $TransResult = DB_query($SQL, $db);

    while ( $myrow = DB_fetch_array($TransResult)) {
        $datosCapitulos['ccap'] = $myrow ['ccap'];
        $datosCapitulos['ccapmiles'] = $myrow ['ccapmiles'];
        $datosCapitulos['descripcion'] = $myrow ['descripcion'];
        $datosCapitulos['valor'] = $myrow ['valor'];

        $infoCapitulos[] = $datosCapitulos;
    }

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!            OBTENER MODULOS.                 !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    $datosModulos[] ="";

    $SQL = "SELECT `submoduleid`, `moduleid`, `title` FROM sec_submodules s where active=1 and moduleid='1' ORDER BY orderno";

    $TransResult = DB_query($SQL, $db);

    while ( $myrow = DB_fetch_array($TransResult)) {
        $datosModulos['submoduleid'] = $myrow ['submoduleid'];
        $datosModulos['moduleid'] = $myrow ['moduleid'];
        $datosModulos['title'] = $myrow ['title'];

        $infoModulos[] = $datosModulos;
    }



    $contenido = array('general' => $infoGeneral, 'perfil' => $infoPerfil, 'unidadResponsable' => $infoUR, 'unidadEjecutora' => $infoUE, 'capitulos' => $infoCapitulos, 'modulos' => $infoModulos, 'almacenes' => $infoAlmacen, 'objetosprincipales' => $infoOB);
}

if($option == "obtenerPartidasEspecificas"){
    $datos[] ="";
    $post = $_POST;

    $SQL = "SELECT tb_capitulos.ccap, 
                    tb_capitulos.descripcion,
                    tb_partidas.partidacalculada, 
                    CONCAT(tb_partidas.partidacalculada,' - ', tb_partidas.descripcion) AS descripcion_partida, 
                    COALESCE(tb_sec_users_partida.valor,0) AS valor
            FROM tb_cat_partidaspresupuestales_capitulo tb_capitulos
            LEFT JOIN tb_cat_partidaspresupuestales_partidaespecifica tb_partidas ON tb_capitulos.ccap = tb_partidas.ccap
            LEFT JOIN (SELECT partidacalculada,1 AS valor FROM tb_sec_users_partida WHERE userid = '".$post['userid']."') tb_sec_users_partida ON tb_partidas.partidacalculada = tb_sec_users_partida.partidacalculada
            WHERE tb_capitulos.ccap IN (".$post['capitulos'].") AND tb_capitulos.activo = 1 AND tb_partidas.activo = 1
            ORDER BY tb_partidas.ccap, tb_partidas.partidacalculada";

    $TransResult = DB_query($SQL, $db);

    while ( $myrow = DB_fetch_array($TransResult)) {
        $datos['ccap'] = $myrow ['ccap'];
        $datos['descripcion'] = $myrow ['descripcion'];
        $datos['partidacalculada'] = $myrow ['partidacalculada'];
        $datos['descripcion_partida'] = $myrow ['descripcion_partida'];
        $datos['valor'] = $myrow ['valor'];

        $infoPartidas[] = $datos;
    }

    $contenido = array('partidas' => $infoPartidas);
}

if($option == "guardarInformacionUsuario"){
    $post = $_POST;
    list($blnValidacion, $msjError) = fnValidarUsuario($post,$db);
    
    $usuario="";
    if($blnValidacion){
        if($post['userid'] == ""){
            $caja = 0;
            if(!empty($post['nuCaja'])){
                $caja = $post['nuCaja'];
            }

            $SQL = "INSERT INTO www_users (userid,
                        realname,
                        password,
                        phone,
                        email,
                        department,
                        pagesize,
                        fullaccess,
                        blocked,
                        defaultunidadNegocio,
                        ln_ue,
                        obraid
                        )
                    VALUES ('" . $post['txtUsuario'] . "',
                        '" . $post['txtNombreUsuario'] ."',
                        '" . CryptPass($post['txtContrasena']) ."',
                        '" . $post['txtTelefono'] . "',
                        '" . $post['txtEmail'] ."',
                        '" . $post['txtDepartamento'] ."',      
                        'A4',
                        8,
                        '" . $post['selectEstatusUsuario'] ."',
                        '" . $post['urDefault'] ."',
                        '" . $post['ueDefault'] ."',
                        '" . $caja ."')";
            
            $rs= DB_query($SQL,$db);
            $usuario =$post['txtUsuario'];
        }else{
            $UpdatePassword = "";
            if ($post['txtContrasena'] != ""){
                $UpdatePassword = "password='" . CryptPass($post['txtContrasena']) . "',";
            }

            $caja = 0;
            if(!empty($post['nuCaja'])){
                $caja = $post['nuCaja'];
            }
            $SQL = "UPDATE www_users SET realname='" . $post['txtNombreUsuario'] . "',
                        phone='" . $post['txtTelefono'] ."',
                        obraid='" . $caja ."',
                        email='" . $post['txtEmail'] ."',
                        department='".$post['txtDepartamento']."',      
                        " . $UpdatePassword . "
                        fullaccess=8,
                        blocked=".$post['selectEstatusUsuario'].",
                        defaultunidadNegocio=".$post['urDefault'].",
                        ln_ue=".$post['ueDefault']."
                    WHERE userid = '".$post['userid']."'";
                    
            $rs= DB_query($SQL,$db);
            $usuario=$post['userid'];
        }

        if($rs){
            
            if (isset($_FILES['inputFile']) AND $_FILES['inputFile']['name'] !='') {
                $file_name = "";
                $file_name = "file_".date('Ymd_his')."";
                $name_visible = $_FILES['inputFile']['name'];
                $file_tmp =$_FILES['inputFile']['tmp_name'];

                $file_name=basename(str_replace(" ", "", $file_name));
                $name_visible=str_replace(" ", "", $name_visible);
                $file_type=$_FILES['inputFile']['type'];

                if(moverArchivo($file_name.$file_type, $file_tmp, SUBIDAARCHIVOS)){
                    $SQLUpdateImg = "UPDATE www_users SET ImagenUsuario = '".SUBIDAARCHIVOS."/".$file_name.$file_type."' WHERE  userid = '".$usuario."'";
                    $resulImagen = DB_query($SQLUpdateImg, $db);
                }
            }

             # GUARDAMOS LOS OBJETOS
             if($post['selectObjetoPrincipalUsuarios'] != "0"){
                $SQLUR = "DELETE FROM `sec_objetoprincipalxuser` WHERE `userid` = '".$usuario."'";
                $rs= DB_query($SQLUR,$db);
                $SQLUR = "INSERT INTO `sec_objetoprincipalxuser` (`userid`, `loccode`) VALUES ";
                foreach($post['selectObjetoPrincipalUsuarios'] as $value){
                   $SQLUR .= "('".$usuario."', '".$value."'),"; 
                }
             }
            
            //  for ($i=0; $i < $post['totalUR']; $i++) { 
            //  }
             $SQLUR = substr($SQLUR, 0, -1); 
             $result= DB_query($SQLUR,$db);
             $SQLUR = "DELETE FROM `sec_objetoprincipalxuser` WHERE `userid` = '".$usuario."' AND `loccode` = '0' ";
             $rs= DB_query($SQLUR,$db);

            # GUARDAMOS LAS UR
            $SQLUR = "DELETE FROM `sec_unegsxuser` WHERE `userid` = '".$usuario."'";
            $rs= DB_query($SQLUR,$db);
            $SQLUR = "INSERT INTO `sec_unegsxuser` (`userid`, `tagref`) VALUES ";
            for ($i=0; $i < $post['totalUR']; $i++) { 
               $SQLUR .= "('".$usuario."', '".$post['inputUR'.$i]."'),"; 
            }
            $SQLUR = substr($SQLUR, 0, -1); 
            $result= DB_query($SQLUR,$db);

            #GUARDAMOS LAS UE
            $SQLUE = "DELETE FROM `tb_sec_users_ue` WHERE `userid` = '".$usuario."'";
            $rs= DB_query($SQLUE,$db);
            $optionUE="";
            $SQLUE = "INSERT INTO `tb_sec_users_ue` (`userid`, `tagref`, `ue`, `ln_aux1`, `ind_activo`, `dtm_fecha_alta`, `dtm_fecha_actualizacion`) ";
            for ($i=0; $i < $post['totalUE']; $i++) { 
               $optionUE .=  "'".$post['inputUE'.$i]."',"; 
            }
            $optionUE = substr($optionUE, 0, -1); 
            if($optionUE != ""){
                $SQLUE .= "SELECT '".$usuario."', ur, ue, ln_aux1,1,sysdate(),sysdate() 
                        FROM tb_cat_unidades_ejecutoras 
                        WHERE ue IN (".$optionUE.")";
                $rs= DB_query($SQLUE,$db);
            }

            # GUARDAMOS LAS PERFIL
            $SQLPerfil = "DELETE FROM `sec_profilexuser` WHERE `userid` = '".$usuario."'";
            $rs= DB_query($SQLPerfil,$db);
            $SQLPerfil = "INSERT INTO `sec_profilexuser` (`userid`, `profileid`) VALUES ";
            for ($i=0; $i < $post['totalPerfil']; $i++) { 
               $SQLPerfil .= "('".$usuario."', '".$post['inputPerfil'.$i]."'),"; 
            }
            $SQLPerfil = substr($SQLPerfil, 0, -1); 
            if($post['totalPerfil'] > 0){
                $rs= DB_query($SQLPerfil,$db);
            }

            # GUARDAMOS LOS ALMACENES
            $SQLAlmacen = "DELETE FROM `sec_loccxusser` WHERE `userid` = '".$usuario."'";
            $rs= DB_query($SQLAlmacen,$db);
            $SQLAlmacen = "INSERT INTO `sec_loccxusser`  (`userid`, `loccode`) VALUES ";
            for ($i=0; $i < $post['totalAlmacen']; $i++) { 
               $SQLAlmacen .= "('".$usuario."', '".$post['inputAlmacen'.$i]."'),"; 
            }
            $SQLAlmacen = substr($SQLAlmacen, 0, -1); 
            if($post['totalAlmacen'] > 0){
                $rs= DB_query($SQLAlmacen,$db);
            }

            if(isset($post['almacenDefault'])){
                $SQLAlmacenDefault="UPDATE www_users SET defaultlocation= '".$post['almacenDefault']."' WHERE `userid` = '".$usuario."'";
                $rs= DB_query($SQLAlmacenDefault,$db);
            }

            # GUARDAMOS LOS CAPITULOS
            $SQLCapitulo = "DELETE FROM `sec_capituloxuser` WHERE `sn_userid` = '".$usuario."'";
            $rs= DB_query($SQLCapitulo,$db);
            $SQLCapitulo = "INSERT INTO `sec_capituloxuser` (`sn_userid`, `sn_capitulo`) VALUES ";
            for ($i=0; $i < $post['totalCapitulo']; $i++) { 
               $SQLCapitulo .= "('".$usuario."', '".$post['inputCapitulo'.$i]."'),"; 
            }
            $SQLCapitulo = substr($SQLCapitulo, 0, -1); 
            if($post['totalCapitulo'] > 0){
                $rs= DB_query($SQLCapitulo,$db);
            }

            # GUARDAMOS LOS PARTIDAS ESPECIFICAS
            $SQLPartidaEspecifica = "DELETE FROM `tb_sec_users_partida` WHERE `userid` = '".$usuario."'";
            $rs= DB_query($SQLPartidaEspecifica,$db);
            $SQLPartidaEspecifica = "INSERT INTO `tb_sec_users_partida` (`userid`, `partidacalculada`, `dtm_fecha_alta`, `dtm_fecha_actualizacion`) VALUES ";
            for ($i=0; $i < $post['totalPartidaEspecifica']; $i++) { 
               $SQLPartidaEspecifica .= "('".$usuario."', '".$post['inputPartida'.$i]."', sysdate(), sysdate()),"; 
            }
            $SQLPartidaEspecifica = substr($SQLPartidaEspecifica, 0, -1); 
            if( $post['totalPartidaEspecifica'] > 0){
                $rs= DB_query($SQLPartidaEspecifica,$db);
            }

            # GUARDAMOS LOS PARTIDAS ESPECIFICAS
            for ($i=0; $i < $post['totalFunciones']; $i++) { 
                $SQL="SELECT * FROM sec_funxuser WHERE userid ='".$post['userid']."' AND functionid = '".$post['inputFuncion'.$i]."';";
                $rs= DB_query($SQL,$db);
                if($rs){
                    if(DB_num_rows($rs)>0){
                        $SQL = " UPDATE sec_funxuser SET permiso = '".$post['inputValFuncion'.$i]."' WHERE userid ='".$post['userid']."' AND functionid = '".$post['inputFuncion'.$i]."';";
                        $rs= DB_query($SQL,$db);
                    }else{
                        $SQL = "SELECT DISTINCT * 
                                FROM sec_profilexuser 
                                LEFT JOIN sec_funxprofile ON sec_profilexuser.profileid = sec_funxprofile.profileid
                                WHERE sec_profilexuser.userid = '".$post['userid']."' AND sec_funxprofile.functionid='".$post['inputFuncion'.$i]."';";
                        $rs= DB_query($SQL,$db);
                        if($rs){
                            if(DB_num_rows($rs)>0){
                                if($post['inputValFuncion'.$i] == '0'){
                                    $SQL = "INSERT INTO `sec_funxuser` (`userid`, `functionid`, `permiso`)
                                            VALUES
                                            ('".$post['userid']."', '".$post['inputFuncion'.$i]."', 0);";
                                }

                            }else{
                                if($post['inputValFuncion'.$i] == '1'){
                                    $SQL = "INSERT INTO `sec_funxuser` (`userid`, `functionid`, `permiso`)
                                            VALUES
                                            ('".$post['userid']."', '".$post['inputFuncion'.$i]."', 1);";
                                }
                            }
                            $rs= DB_query($SQL,$db);
                        }
                    }
                }
                
            }
            $SQL = "DELETE FROM sec_funxuser WHERE permiso = 0";
            $rs= DB_query($SQL,$db);
        }

        $TransResult = true;
        $Mensaje = "<p>El usuario: <b>".$usuario."</b> se guardo correctamente.</p>";

    }else{
        $TransResult = false;
        $Mensaje = $msjError;
    }

}

if($option == "obtenerFunciones"){
    $datos[] ="";
    $post = $_POST;

    $SQL = "SELECT tb_fun.functionid, 
                    concat(tb_fun.functionid, ' - ', tb_fun.title) as title , 
                    tb_fun.categoryid, 
                    tb_fun.type,
                    sec_categories.name,
                    coalesce(tb_functionxuser.valor,0) as valor
            FROM sec_functions_new tb_fun
            LEFT JOIN sec_categories on tb_fun.categoryid = sec_categories.categoryid
            LEFT JOIN (   SELECT  DISTINCT sec_functions_new.functionid, 1 as valor
                          FROM sec_functions_new
                          JOIN sec_funxprofile ON sec_functions_new.functionid = sec_funxprofile.functionid
                          JOIN sec_profilexuser ON sec_funxprofile.profileid = sec_profilexuser.profileid
                          JOIN sec_submodules ON sec_functions_new.submoduleid = sec_submodules.submoduleid
                          INNER JOIN sec_categories cat ON sec_functions_new.categoryid= cat.categoryid
                          WHERE sec_profilexuser.userid = '".$post['userid']."'
                          AND sec_submodules.submoduleid = '".$post['modulo']."'
                          AND sec_functions_new.active='1'
                          AND sec_functions_new.functionid NOT IN
                                    (SELECT funCtionid
                                     FROM sec_funxuser
                                     WHERE userid= '".$post['userid']."')
                          UNION 
                          SELECT DISTINCT FuxP.functionid, 1 as valor
                                FROM sec_modules s,
                                     sec_submodules sm,
                                     www_users u,
                                     sec_functions_new FuxP,
                                     sec_categories C,
                                     sec_funxuser PU
                                WHERE s.moduleid=sm.moduleid
                                  AND s.active=1
                                  AND FuxP.submoduleid=sm.submoduleid
                                  AND C.categoryid=FuxP.categoryid
                                  AND u.userid=PU.userid
                                  AND PU.userid= '".$post['userid']."'
                                  AND u.userid=PU.userid
                                  AND FuxP.functionid=PU.functionid
                                  AND FuxP.submoduleid= '".$post['modulo']."'
                                  AND FuxP.active=1
                                  AND PU.permiso = 1
                          ORDER BY
                          functionid) tb_functionxuser ON tb_fun.functionid =tb_functionxuser.functionid
            WHERE tb_fun.submoduleid = '".$post['modulo']."'
                    AND tb_fun.active=1 
            ORDER BY  sec_categories.name, tb_fun.type, tb_fun.functionid, tb_fun.title ;";

    $TransResult = DB_query($SQL, $db);

    while ( $myrow = DB_fetch_array($TransResult)) {
        $datos['functionid'] = $myrow ['functionid'];
        $datos['name'] = $myrow ['name'];
        $datos['title'] = $myrow ['title'];
        $datos['type'] = $myrow ['type'];
        $datos['valor'] = $myrow ['valor'];

        $infoFunciones[] = $datos;
    }

    $contenido = array('funciones' => $infoFunciones); 
}

if($option == "obtenerAlmacenes"){
    $post=$_POST;
    $SQL = "SELECT locations.ln_ue, 
                        locations.loccode, 
                        locations.locationname, 
                        coalesce(tb_locuser.valor,0) AS valor, 
                        desc_ue,
                        COALESCE(tb_Default.valDefault,0) AS valDefault
                FROM locations 
                LEFT JOIN tb_cat_unidades_ejecutoras ON locations.ln_ue = tb_cat_unidades_ejecutoras.ue
                LEFT JOIN (SELECT loccode, 1 AS valor FROM sec_loccxusser WHERE userid = '".$post['userid']."') tb_locuser ON  locations.loccode = tb_locuser.loccode
                LEFT JOIN (SELECT defaultlocation, 1 as valDefault FROM www_users WHERE userid ='".$post['userid']."') tb_Default ON locations.loccode = tb_Default.defaultlocation
                WHERE ln_ue IN (".$post['almacen'].")
                ORDER BY locations.ln_ue, locations.loccode;";

    $TransResult = DB_query($SQL, $db);

    while ( $myrow = DB_fetch_array($TransResult)) {
        $datosAlmacen['ln_ue'] = $myrow ['ln_ue'];
        $datosAlmacen['desc_ue'] = $myrow ['desc_ue'];
        $datosAlmacen['loccode'] = $myrow ['loccode'];
        $datosAlmacen['locationname'] = $myrow ['locationname'];
        $datosAlmacen['valor'] = $myrow ['valor'];
        $datosAlmacen['valDefault'] = $myrow ['valDefault'];
        $infoAlmacen[] = $datosAlmacen;
    }

    $contenido = array('almacenes' => $infoAlmacen);
}

function fnValidarUsuario($post, $db){
    // variables
    $blnValidacion = false;
    $msjError = "";

    // validaciones
    
    if (strlen($post['txtUsuario'])<3){
        $msjError .= '<p> <i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La longitud del login de usuario debe ser mayor a 4 caracteres</p>';
    } 

    if (ContainsIllegalCharacters($post['txtUsuario'])) {
        $msjError .= '<p> <i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El nombre de usuario no puede contener los siguientes caracteres  - \' & + \" \\ o espacios.';
    } 

    if (strlen($post['txtContrasena'])<5){
        if ($post['userid'] ==""){
            $msjError .= '<p> <i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La contraseña debe ser mayor a 5 caracteres</p>';
        }
    } 

    if (strstr($post['txtContrasena'],$post['txtUsuario'])!= False){
        $msjError .= '<p> <i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La Contraseña debe ser diferente al login de Usuario</p>';
    } 

    if ($post['userid'] ==""){
        $SQL = "SELECT * FROM www_users WHERE userid='".$post['txtUsuario']."'";
        $result= DB_query($SQL,$db);
        if(DB_num_rows($result)>0){
            $msjError .= '<p> <i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ya existe un registro con este Usuario</p>';
        }
    }

    // retornar resultado

    if($msjError ==""){
        $blnValidacion = true;
    }

    return array ($blnValidacion, $msjError);
}
 

//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//!!         Mover archivo al servidor.            !!
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

function moverArchivo($docName, $docTemp, $ubicacion){
    
    if(!file_exists($ubicacion)){ crearCarpeta($ubicacion); }
    $name = $ubicacion . $docName;
    
    if(is_uploaded_file($docTemp)){
        
        $conf = move_uploaded_file($docTemp, $name);
         @chown($name, 'git');
         @chgrp($name, 'git');
        return $conf;
    }

    return false;
}

function crearCarpeta($directorio)
{
    # crea el directorio indicado
    @mkdir($directorio);
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


$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $TransResult, 'ErrMsg' => $ErrMsg, 'DbgMsg' => $DbgMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);

?>