<?php

/**
 * ABC_Jerarquias_Modelo
 *
 * @category panel
 * @package  ap_grp
 * @author   Japheth Calzada López
 * @license  [<url>] [name]
 * @version  GIT: <1234>
 * @link     (target, link)
 * Fecha creacion: 10/07/2018
 * Fecha Modificacion: 10/07/2018
 * 
 * @file: ClasificacionProgramaticaModelo.php
 */

session_start();
$PageSecurity = 11;
$PathPrefix = '../';

include($PathPrefix. "includes/SecurityUrl.php");
include($PathPrefix.'abajo.php');
include($PathPrefix.'config.php');
include($PathPrefix.'includes/ConnectDB.inc');

include($PathPrefix.'includes/SecurityFunctionsHeader.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');
include($PathPrefix .'includes/DateFunctions.inc');

$ErrMsg = _('');
$contenido = array();
$result = false;
$sql = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

$option                     = $_POST['option'];
$columnasNombres            = '';
$funcion                    = 1345;
if ($option == 'mostrarClaves'){
    
    $sql = "SELECT id as value, clave as texto
                FROM clasprog
                WHERE activo = 1
                ORDER BY clave ASC";
    $ErrMsg = "No se obtuvo las información";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $texto = htmlspecialchars($myrow ['texto']);
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $texto );
    }
    $result     = true; 
    $contenido  = array('datos' => $info);
}
  
if ($option=='eliminarInformacion'){
    $idClave = $_POST["idClave"];
    $registroEliminable = fnRegistroEliminable($idClave,$db);
    if($registroEliminable){
        $sql = "UPDATE `clasprog` SET `activo` = 0 WHERE `id` = '$idClave'";
        $ErrMsg = "No se obtuvo la información";
        $TransResult = DB_query($sql, $db, $ErrMsg);
        if($TransResult){
            $result = true;
        }
    }
}
function fnRegistroEliminable($idClave,$db){
    $eliminable     = true;
    $sql            = "SELECT COUNT(`id_nu_programa_presupuestario`) AS 'RegistrosEncontrados' FROM `tb_cat_programa_presupuestario` WHERE `activo` = '1' AND `id_clasprog` = '$idClave'";
    $ErrMsg         = "No se obtuvo la información";
    if(DB_fetch_array(DB_query($sql, $db, $ErrMsg))['RegistrosEncontrados']>0){
        $eliminable = false;
    }
    return $eliminable;
}

if ($option == 'mostrarPrograma'){
    $sql = "SELECT id_programa as value, 
    desc_programa AS  texto
                FROM clasprog_prog
                ORDER BY desc_programa ASC";
    $ErrMsg = "No se obtuvo las información";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $texto = htmlentities($myrow ["texto"]);

        $info[] = array( 'value' => $myrow ['value'], 'texto' => $texto );
    }
    $result     = true; 
    $contenido  = array('datos' => $info);
}
if ($option == 'mostrarGrupo'){
    $sql = "SELECT id as value,  name as texto FROM clasprog_grupo  ORDER BY name";
    $ErrMsg = "No se obtuvo las Partidas Genéricas";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
    $texto = htmlspecialchars($myrow ['texto']);
    $info[] = array( 'value' => $myrow ['value'], 'texto' => $texto );
    }
    $result     = true; 
    $contenido  = array('datos' => $info);
}

if ($option == 'obtenerInformacion') {
    $clave           = $_POST["clave"];
    $grupo           = $_POST["grupo"];
    $programa        = $_POST["programa"];
    $comision        = ''; 
    $sqlWhere        = " WHERE activo=1 ";
    $ligaVer         = "";
    $ligaMod         = "";


    if ($clave != '') {
        $sqlClave   = strpos($clave,",");

        if ( $sqlClave === 4){
            $sqlWhere .= " AND clasprog.id in ($clave )";
        }else{
            $sqlWhere .= " AND clasprog.id = $clave ";
        }
    }

    if ($programa != '') {
        
        $sqlProgramaIn  = strpos($programa,",");
        if ( $sqlProgramaIn === 4){
            $sqlWhere .= " AND clasprog_prog.id_programa in ($programa )";
        }else{
            $sqlWhere .= " AND clasprog_prog.id_programa = $programa ";
        }
    }
        
    if ($grupo != '') {

        $sqlGrupoIn  = strpos($grupo,",");
        if ( $sqlGrupoIn === 4){
            $sqlWhere .= " AND clasprog_grupo.id in ($grupo )";
        }else{
            $sqlWhere .= " AND clasprog_grupo.id = $grupo ";
        }
    }
    

    $sql = " SELECT clasprog.id, clasprog.clave, clasprog.name, clasprog_grupo.name AS namegroup,
                    clasprog.name AS idprog , clasprog.nu_id_grupo
                    FROM clasprog
                    JOIN clasprog_grupo ON clasprog_grupo.id = clasprog.nu_id_grupo 
                    $sqlWhere
                    ORDER BY clasprog.clave, clasprog.name ASC";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {

        $enc        = new Encryption;

        $urlVer     = "id=".$myrow['id']."&ver=1";
        //$urlVer     = $enc->encode($urlVer);
        $ligaVer    =  $urlVer;

        $urlGeneral = "&id=>" . $myrow['id'] . "&ver=>1";
        $enc = new Encryption;
        $url = $enc->encode($urlGeneral);
        $ligaVer= "URL=" . $url;

        $urlMod        = "id=".$myrow['id']."&modificar=1&idGrupo=".$myrow["nu_id_grupo"];
        //$urlMod        = $enc->encode($urlMod);
        $ligaMod       =  $urlMod;

        $urlGeneral = "&id=>" . $myrow['id'] . "&modificar=>1&idGrupo=>".$myrow["nu_id_grupo"];
        $enc = new Encryption;
        $url = $enc->encode($urlGeneral);
        $ligaMod= "URL=" . $url;

        $info[] = array(
            'id'                => $myrow ['id'],
            'clave'             => $myrow ['clave'],
            'programa'          => htmlspecialchars($myrow ['name']),
            'grupo'             => htmlspecialchars($myrow ['namegroup']), 
            'Ver'               => '<a href="ClasificacionProgramatica_V.php?'.$ligaVer.'"><span class="glyphicon glyphicon-eye-open"></span></a>',
            'Modificar'         => '<a href="ClasificacionProgramatica_V.php?'.$ligaMod.'"><span class="glyphicon glyphicon-edit"></span></a>',
            'Eliminar'          => '<a href="javascript:eliminar('.$myrow ['id'].',\''. $myrow ['clave'].'\');"><span class="glyphicon glyphicon-trash"></span></a>'
        );

    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'clave', type: 'string' },";
    $columnasNombres .= "{ name: 'programa', type: 'string' },";
    $columnasNombres .= "{ name: 'grupo', type: 'string' },";
    $columnasNombres .= "{ name: 'Ver', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $columnasNombresGrid  = "[";
    $columnasNombresGrid .= " { text: 'Clave', datafield: 'clave', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Programa', datafield: 'programa', width: '45%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Grupo', datafield: 'grupo', width: '35%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Ver', datafield: 'Ver', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '5%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = 'ClasificacionProgramatica_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;

}
$dataObj = array('sql' => '',"contenido" => $contenido,"result"=>$result);  
echo json_encode($dataObj);
