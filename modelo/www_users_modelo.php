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

//include($PathPrefix.'includes/session.inc');
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

$permisoAutorizador= Havepermission($_SESSION['UserID'], 2408, $db);
$permisoValidador= Havepermission($_SESSION['UserID'], 2406, $db);
$permisoCapturista= Havepermission($_SESSION['UserID'], 2410, $db);

if($option == "obtenerUsuarios"){

	$SQL = "select www_users.userid, 
                    www_users.realname, 
                    www_users.phone, 
                    www_users.email,
                    www_users.obraid,
                    www_users.customerid, 
                    www_users.branchcode, 
                    www_users.salesman, 
                    date_format(www_users.lastaction, '%d-%m-%Y') as lastvisitdate,
                    www_users.fullaccess, 
                    www_users.pagesize, 
                    www_users.theme,
                    www_users.language, 
                    (sec_profiles.name) as primerperfil, 
                    www_users.blocked, 
                    tb_www_user_estatus.descripcion as desc_estatus
            FROM www_users
            INNER JOIN sec_unegsxuser on www_users.userid= sec_unegsxuser.userid
            LEFT join tb_www_user_estatus on www_users.blocked = tb_www_user_estatus.id
            LEFT JOIN sec_profilexuser ON www_users.userid = sec_profilexuser.userid
            LEFT JOIN  sec_profiles ON sec_profilexuser.profileid = sec_profiles.profileid
            WHERE 1=1 ";

        if (Havepermission($_SESSION['UserID'],932, $db)==0){
            $SQL .= " AND www_users.userap = 0";
        }

        if(isset($_POST['selectUnidadNegocio']) and $_POST['selectUnidadNegocio']!="" and $_POST['selectUnidadNegocio']!="-1"){
            $SQL .= " AND sec_unegsxuser.tagref = ".$_POST['selectUnidadNegocio']."";
        }

        if(isset($_POST['selectEstatusUsuario']) and $_POST['selectEstatusUsuario']!="" and $_POST['selectEstatusUsuario']!="-1"){
            $SQL .= " AND www_users.blocked IN (".$_POST['selectEstatusUsuario'].")";
        }

        if(isset($_POST['txtNombreUsuario']) and $_POST['txtNombreUsuario']!="" and $_POST['txtNombreUsuario']!="-1"){
            $SQL .= " AND www_users.realname LIKE '%".$_POST['txtNombreUsuario']."%'";
        }

        if(isset($_POST['selectPerfilUsuario']) and $_POST['selectPerfilUsuario']!="" and $_POST['selectPerfilUsuario']!="-1"){
            $SQL .= " AND sec_profilexuser.profileid IN (".$_POST['selectPerfilUsuario'].")";
        }

    $SQL .= "GROUP BY www_users.userid, 
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

    $ErrMsg = "No se obtuvieron los usuarios";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {


        $url = "&idUsuario=>" . $myrow["userid"];
        $url = $enc->encode($url);
        $liga= "URL=" . $url;

        $liga_editar = "<a target='' href='./usuarioDetalle.php?$liga' title='Editar'>
                            <span class='glyphicon glyphicon glyphicon-edit'></span>
                        </a>";

        $info[] = array( 
            'userid' => $myrow ['userid'],
            'realname' => $myrow ['realname'],
            'email' => $myrow ['email'],
            'caja' => $myrow ['obraid'],
            'primerperfil' => $myrow ['primerperfil'],
            'lastvisitdate' => $myrow ['lastvisitdate'],
            'desc_estatus' => $myrow ['desc_estatus'],
            'editar' => $liga_editar
        );
    }

    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'userid', type: 'string' },";
    $columnasNombres .= "{ name: 'realname', type: 'string' },";
    $columnasNombres .= "{ name: 'email', type: 'string' },";
    $columnasNombres .= "{ name: 'caja', type: 'string' },";
    $columnasNombres .= "{ name: 'primerperfil', type: 'string' },";
    $columnasNombres .= "{ name: 'lastvisitdate', type: 'string' },";
    $columnasNombres .= "{ name: 'desc_estatus', type: 'string' },";
    $columnasNombres .= "{ name: 'editar', type: 'string' }";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Caja', datafield: 'caja', editable:false, width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Usuario', datafield: 'userid', editable:false, width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Nombre', datafield: 'realname', editable:false, width: '30%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Email', datafield: 'email', editable:false, width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Primer Perfil', datafield: 'primerperfil', editable:false, width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Ultima Visita', datafield: 'lastvisitdate', editable:false, width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'desc_estatus', editable:false, width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'editar',editable:false, width: '5%', cellsalign: 'center', align: 'center', hidden: false }";    
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;

}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $TransResult, 'ErrMsg' => $ErrMsg, 'DbgMsg' => $DbgMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);

?>