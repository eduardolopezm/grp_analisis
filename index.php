<?php
/**
 * Pagina de inicio de aplicacion
 *
 * @category Inicio
 * @package ap_grp
 * @author Armando Barrientos Martinez <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link (target, link)
 * Fecha Creación: 01/08/2017
 * Fecha Modificación: 21/08/2017
 */

$PageSecurity = 1;

require 'includes/session.inc';
$title = _('Menu Principal');

require 'includes/SecurityFunctionsHeader.inc';
$theme = 'silverwolf';
/**
 * Inclución de clase generadora de encripcion
 * @date:16.03.18
 * @author: desarrollo
*/
include('includes/SecurityUrl.php');

if (HavepermissionHeader($_SESSION['UserID'], 68, $db) == 1) {
    //include('includes/MonitorDataware.inc');
}

echo "<div name='DivPrueba' id='DivPrueba'></div>";


if (empty($_GET['Mod'])) {
    $_GET['Mod']= "";
}

if (empty($_GET['functionid'])) {
    $_GET['functionid']= "";
}

if (isset($_GET['Oper']) && $_GET['Oper'] == "Agregar") {
    if ($_GET['categoryidfact'] != 2) {
        $tipo = 1;
    } else {
        $tipo = 2;
    }

    $SQL = "SELECT Count(functionid) as total
      FROM sec_favxuser
      WHERE userid = '" . $_SESSION['UserID'] . "'
      AND type = '" . $tipo . "'";
    $result = DB_query($SQL, $db);
    $myrow  = DB_fetch_array($result);

    if ($myrow['total'] < 3) {
        $SQL = "SELECT Count(functionid) as total
          FROM sec_favxuser
          WHERE 
          userid = '" . $_SESSION['UserID'] . "'
          AND functionid = '" . $_GET['functionfav'] . "'
          AND type = '" . $tipo . "'";
        $result2 = DB_query($SQL, $db);
        $myrow2  = DB_fetch_array($result2);

        if ($myrow2['total'] == 0) {
            //Validar que no exista por si recargan pagina no muestre error
            $SQL = "INSERT INTO sec_favxuser (
                  userid,
                  functionid,
                  type)
                  VALUES(
                  '" . $_SESSION['UserID'] . "',
                  '" . $_GET['functionfav'] . "',
                  '" . $tipo . "'
                  )";
            $result = DB_query($SQL, $db);
        }
    } else {
        if ($tipo == 1) {
            prnMsg("No se pudo agregar a favoritos ya que  solo tiene permitido agregar 3 funciones ", "error");
        } else {
            prnMsg("No se pudo agregar a favoritos ya que  solo tiene permitido agregar 3 reportes ", "error");
        }
    }
} elseif (isset($_GET['Oper']) && $_GET['Oper'] == "Eliminar") {
    $SQL = "DELETE
      FROM sec_favxuser
      WHERE functionid= '" . $_GET['functionfav'] . "'
      AND userid = '" . $_SESSION['UserID'] . "'";
    $result = DB_query($SQL, $db);
}

if (isset($_POST['GenerarDif'])) {
    //verificar existencaia del archivo
    if (isset($_POST['url'])) {
        if (file_exists($_POST['url']) == 1) {
            $urlValido = true;
        } else {
            $urlValido = false;
        }
    }
    
    if (isset($_POST['txt_nombre_funcion']) && isset($_POST['cmb_capituloid']) && isset($_POST['cmb_categoria']) && isset($_POST['txt_functionid'])) {
        $SQL = "update  sec_functions_new 
        set sec_functions_new.title='" . $_POST['txt_nombre_funcion'] . "',
        sec_functions_new.submoduleid=" . $_POST['cmb_capituloid'] . ",
        sec_functions_new.categoryid=" . $_POST['cmb_categoria'] . ",
        sec_functions_new.active=" . $_POST['cmb_activo'] . "
        where sec_functions_new.functionid=" . $_POST['txt_functionid'];
        $result = DB_query($SQL, $db);

        $SQL2 = "update  sec_functions
        set sec_functions.title='" . $_POST['txt_nombre_funcion'] . "',
        sec_functions.submoduleid=" . $_POST['cmb_capituloid'] . ",
        sec_functions.categoryid=" . $_POST['cmb_categoria'] . ",
        sec_functions.active=" . $_POST['cmb_activo'] . "
        where sec_functions.functionid=" . $_POST['txt_functionid'];
        $result = DB_query($SQL2, $db);

        if ($result) {
            prnMsg("El cambio fue realizado...", "Correcto");
        }
    } else {
        prnMsg("Se debe de agregar un nombre y un orden valido", "error");
    }
}

if (!isset($_GET["categorysel"])) {
    $_SESSION["categorysel"] = 4;
} else {
    $_SESSION["categorysel"] = $_GET["categorysel"];
}

if (isset($_SERVER['HTTPS'])) {
    if ($_SERVER['HTTPS'] == 'on') {
        $prefix = "https";
    } else {
        $prefix = "http";
    }
} else {
    $prefix = "http";
}

//Verificación de configuracion en caso que no exista ir a wizar de configuracion
if (!isset($_SESSION["VerificaConfigCADER"]) || $_SESSION["VerificaConfigCADER"] == "true") {
    $SQL      = "SELECT * FROM locations ";
    $rs_cader = DB_query($SQL, $db);
    $c_cader  = DB_num_rows($rs_cader);
    if ($c_cader < 1) {
        if ($_SESSION['UserID'] == "admin" || $_SESSION['UserID'] == "supervisor") {
            header('Location: ' . $prefix . '://' . $_SERVER['SERVER_NAME'] . '/proyectose/configure.php');
        } else {
            echo "<br/>IMPORTANTE - Por favor ingrese como usuario 'administrador' para realizar la configuraci&oacute;n faltante.";
            echo '<br/><a href="Logout.php" >
          <input class="button" type="submit" value="Salir" name="action" />
          </a>';
            exit;
        }
    }
}

require 'includes/header.inc';

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

?>
<script type="text/javascript" src="javascripts/index.js"></script>
<?php
  /* EJECUTA TODAS LAS OPERACIONES DE BD */
if (isset($_GET['modulosel'])) {
    /* Obtener el modulo seleccionado */
    $_SESSION['Module'] = $_GET['modulosel'];
} elseif (isset($_SESSION['Module']) and is_numeric($_SESSION['Module'])) {
    $_SESSION['Module'] = $_SESSION['Module'];
} else {
    $_SESSION['Module'] = 1;
}

//acceso por numero de funcion
if (isset($_SESSION['directfunctionid'])) {
    $SQL = "SELECT *
          FROM sec_favxuser
          WHERE userid = '" . $_SESSION['UserID'] . "'
          order by type
          limit 1";

    $result                       = DB_query($SQL, $db);
    $myrow                        = DB_fetch_array($result);
    $_SESSION['directfunctionid'] = $myrow['functionid'];

    $secFunctionTable = "";
    if (empty($_SESSION['SecFunctionTable'])) {
        $secFunctionTable = "sec_functions";
    } else {
        $secFunctionTable = $_SESSION['SecFunctionTable'];
    }

    $sql                   = "Select * from " . $secFunctionTable . " where functionId='" . $_SESSION['directfunctionid'] . "'";
    $res                   = DB_query($sql, $db);
    $myrec                 = DB_fetch_array($res);
    $url                   = trim($myrec['url']);

    $_SESSION['Submodulo'] = $myrec['submoduleid'];

    if (strlen($url) != 0) {
        $local = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], "/") + 1);
        $url   = "http://" . $_SERVER['HTTP_HOST'] . $local . $url;
        echo '<meta http-equiv="Refresh" content="0; url=' . $url . '">';
    }
}

unset($_SESSION['Module']);

if (isset($_GET['submodulesel'])) {
    /* Obtener el submodulo seleccionado */
    $_SESSION['Submodulo'] = $_GET['submodulesel'];
} elseif (isset($_SESSION['Submodulo']) and is_numeric($_SESSION['Submodulo'])) {
    $_SESSION['Submodulo'] = $_SESSION['Submodulo'];
} else {
    $sql = "SELECT DISTINCT sm.submoduleid,
              sm.title,
              sm.url,
              sm.imageicon,sm.orderno
              FROM sec_modules s,
              sec_submodules sm,
              www_users u,
              sec_categories C,
              sec_profilexuser PU,
              sec_funxprofile FP,
              sec_functions_new FuxP
              WHERE s.moduleid=sm.moduleid
              AND s.active=1
              AND sm.active=1
              AND FP.profileid=PU.profileid
              AND FuxP.submoduleid=sm.submoduleid
              AND u.userid=PU.userid
              AND PU.userid='" . $_SESSION['UserID'] . "'
              AND u.userid=PU.userid
              AND FuxP.functionid=FP.functionid
              AND C.categoryid=FuxP.categoryid
              AND FuxP.active=1
              AND FuxP.functionid NOT IN
              (SELECT funCtionid
              FROM sec_funxuser
              WHERE userid='" . $_SESSION['UserID'] . "')
              AND FuxP.type='Funcion'
              ORDER BY sm.orderno";

    $ReFuntion             = DB_query($sql, $db);
    $ResFuntion            = DB_fetch_array($ReFuntion);
    $_SESSION['Submodulo'] = $ResFuntion['submoduleid'];
}

// Mostrar Menu por SUBMODULOS
if (isset($_SESSION['Submodulo'])) {
    echo '  <tr>';
    echo '    <td><table width="100%" border="0" cellpadding="0" cellspacing="0" class="tabla_area_trabajo">';
    echo '      <tr>';
    echo '        <td><table width="100%" border="0" cellspacing="0" cellpadding="0">';
    echo '          <tr>';
    /*     * ******* trae categorias de funciones ********************** */
    $secFunctionTable = "";
    if (empty($_SESSION['SecFunctionTable'])) {
        $secFunctionTable = "sec_functions";
    } else {
        $secFunctionTable = $_SESSION['SecFunctionTable'];
    }

    $sql = "SELECT DISTINCT sm.submoduleid,
              sm.title,
              sm.url,
              sm.imageicon, sm.orderno
              FROM sec_modules s,
              sec_submodules sm,
              www_users u,
              sec_categories C,
              sec_profilexuser PU,
              sec_funxprofile FP,
              sec_functions_new FuxP
              WHERE s.moduleid=sm.moduleid
              AND s.active=1
              AND sm.active=1
              AND FP.profileid=PU.profileid
              AND FuxP.submoduleid=sm.submoduleid
              AND u.userid=PU.userid
              AND PU.userid='" . $_SESSION['UserID'] . "'
              AND u.userid=PU.userid
              AND FuxP.functionid=FP.functionid
              AND C.categoryid=FuxP.categoryid
              AND FuxP.active=1
              AND FuxP.functionid NOT IN
              (SELECT funCtionid
              FROM sec_funxuser
              WHERE userid='" . $_SESSION['UserID'] . "')
              AND FuxP.type='Funcion'
              ORDER BY sm.orderno";

    $ReFuntion = DB_query($sql, $db);

    if ($_SESSION['Submodulo'] != 0) {
        $submodulo = $_SESSION['Submodulo'];
    } else {
        $submodulo = 14;
    }

    if (DB_num_rows($ReFuntion) > 0) {
        $contador       = 0;
        $limitepestanas = 4;
        $linea          = 0;

        while ($ResFuntion = DB_fetch_array($ReFuntion)) {
            $imagefilename   = $ResFuntion['imageicon'];
            $imagefilenameOn = substr($ResFuntion['imageicon'], 0, strpos($ResFuntion['imageicon'], '.')) . '_on.' . substr($ResFuntion['imageicon'], strpos($ResFuntion['imageicon'], '.') + 1);

            if ($submodulo == $ResFuntion['submoduleid']) {
                //$imagetoDisplay = $imagefilenameOn;
                $imagetoDisplay = $imagefilename;
                $imageOnLeft    = 'icp.png';
                $imageOnRight   = 'icp2.jpg';
                $classPestanas  = 'pestanas_on';
                $classLink      = 'pestanas_link_on';
            } else {
                $imagetoDisplay = $imagefilename;
                $imageOnLeft    = 'iz.jpg';
                $imageOnRight   = 'der.jpg';
                $classPestanas  = 'pestanas_off';
                $classLink      = 'pestanas_link';
            }

            if ($contador == $limitepestanas) {
                echo "</tr>";
                echo "<tr>";
                $contador = 0;
            }

            $contador = $contador + 1;

            //AQUI COMIENZA
            echo '      <td width="80" class="tb_pestanas_menu"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="' . $classPestanas . '">';
            echo '        <tr>';

            if ($classPestanas == 'pestanas_off') {
                echo '          <td width="8"><img src="images/imgs/' . $imageOnLeft . '" width="8" height="32" /></td>';
            }
              
            echo '<td width="23">
                      <i class="fa '.$imagetoDisplay.'" aria-hidden="true"></i>
                    </td>';
                    
            echo '          <td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&submodulesel=' . $ResFuntion['submoduleid'] . '" class="' . $classLink . '">' . str_replace(" ", "&nbsp;", $ResFuntion['title']) . '</a></td>';
            echo '          <td width="10"></td>';
            if ($classPestanas == 'pestanas_off') {
                echo '          <td width="8"><img src="images/imgs/' . $imageOnRight . '" width="8" height="32" /></td>';
            }
            echo '        </tr>';
            echo '      </table></td>';
        }

        $restantes    = $limitepestanas - $contador;
        $imageOnLeft  = 'iz3.png';
        $imageOnRight = 'iz3.png';

        for ($y = 0; $y < $restantes; $y++) {
            echo '<td width="80">';
            echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" >';
            echo '<tr>';
            echo '<td width="8" style=background:#ffffff>';
            echo '<img src="images/imgs/' . $imageOnLeft . '" width="8" height="32" />';
            echo '</td>';
            echo '<td width="100%" style=background:#ffffff>';
            // echo '<img src="images/imgs/'.$imagetoDisplay.'" width="23" height="23" />';
            echo '</td>';
            echo '<td width="100%" style=background:#ffffff>';
            // echo '--';
            echo '</td>';
            echo '<td width="100%" style=background:#ffffff>';
            echo '</td>';
            echo '<td width="8" style=background:#ffffff>';
            echo '<img src="images/imgs/' . $imageOnRight . '" width="8" height="32" />';
            echo '</td>';
            echo '</tr>';
            echo '</table>';
            echo "</td>";
        }
    }

    echo '      <td width="100%"></td>';
    echo '          </tr>';
    echo '        </table></td>';
    echo '      </tr>';
    echo '      <tr>';
    echo '        <td></td>';
    echo '      </tr>';
    echo '      <tr style="height: 5px;">';
    echo '        <td style="background-color: #727378; border-right: 1px solid rgb(27, 105, 63);"></td>';
    echo '      </tr>';
    echo '      <tr><td style="border-left: 1px solid rgb(27, 105, 63); border-right: 1px solid rgb(27, 105, 63);">&nbsp;</td></tr>';
    echo '      <tr>';
    //echo '        <td class="bkg_areat"><table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">';
    echo '        <td style="border-left: 1px solid #1B693F; border-right: 1px solid #1B693F;"><table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">';
    echo '          <tr>';



    $sql = "SELECT DISTINCT sec_functions_new.functionid,
              cat.imagecategory,
              cat.name,
              concat(sec_functions_new.functionid,' ',sec_functions_new.shortdescription) AS shortdescription,
              sec_functions_new.url,
              sec_functions_new.comments,
              sec_functions_new.functionid,
              sec_functions_new.categoryid,
              sec_functions_new.submoduleid,
              sec_functions_new.title as nombreModificar, sec_submodules.orderno
              FROM sec_functions_new
              JOIN sec_funxprofile ON sec_functions_new.functionid = sec_funxprofile.functionid
              JOIN sec_profilexuser ON sec_funxprofile.profileid = sec_profilexuser.profileid
              JOIN sec_submodules ON sec_functions_new.submoduleid = sec_submodules.submoduleid
              INNER JOIN sec_categories cat ON sec_functions_new.categoryid= cat.categoryid
              WHERE sec_profilexuser.userid = '" . $_SESSION['UserID'] . "'
              AND sec_submodules.submoduleid = '" . $submodulo . "'
              AND sec_functions_new.active='1'
              AND sec_functions_new.type='Funcion'
              ORDER BY 
              name,
              functionid,
              imagecategory,
              shortdescription,
              url,
              comments,
              functionid,
              categoryid,
              submoduleid,
              nombreModificar, 
              orderno";

    $sql= "SELECT DISTINCT sec_functions_new.functionid,
              cat.imagecategory,
              cat.name,
              concat(sec_functions_new.functionid,' ',sec_functions_new.shortdescription) AS shortdescription,
              sec_functions_new.url,
              sec_functions_new.comments,
              sec_functions_new.categoryid,
              sec_functions_new.submoduleid,
              sec_functions_new.title AS nombreModificar, sec_submodules.orderno
              FROM sec_functions_new
              JOIN sec_funxprofile ON sec_functions_new.functionid = sec_funxprofile.functionid
              JOIN sec_profilexuser ON sec_funxprofile.profileid = sec_profilexuser.profileid
              JOIN sec_submodules ON sec_functions_new.submoduleid = sec_submodules.submoduleid
              INNER JOIN sec_categories cat ON sec_functions_new.categoryid= cat.categoryid
              WHERE sec_profilexuser.userid = '" . $_SESSION['UserID'] . "'
              AND sec_submodules.submoduleid = '" . $submodulo . "'
              AND sec_functions_new.active='1'
              AND sec_functions_new.type='Funcion'
              AND sec_functions_new.functionid NOT IN
                        (SELECT funCtionid
                         FROM sec_funxuser
                         WHERE userid= '" . $_SESSION['UserID'] . "')
              UNION 
              SELECT FuxP.functionid, C.imagecategory, C.name, concat(FuxP.functionid,' ', FuxP.shortdescription)
              AS shortdescription, FuxP.url, FuxP.comments AS comments,                                                                    
                           FuxP.categoryid, FuxP.submoduleid, FuxP.title, sm.orderno
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
                      AND PU.userid= '" . $_SESSION['UserID'] . "'
                      AND u.userid=PU.userid
                      AND FuxP.functionid=PU.functionid
                      AND FuxP.submoduleid= '" . $submodulo . "'
                      AND LENGTH(FuxP.url)>0
                      AND FuxP.active=1
                      AND PU.permiso = 1
              ORDER BY 
              NAME,
              functionid,
              imagecategory,
              shortdescription,
              url,
              comments,
              functionid,
              categoryid,
              submoduleid,
              nombreModificar, 
              orderno";
    // Nota: *
    // se agrego AND PU.permiso = 1, 
    // el 0 es cuando le quitan la funcion al usuario pero proviene de un perfil

    $ReFuntion = DB_query($sql, $db);

    if (DB_num_rows($ReFuntion) > 0) {
        $nombrecategoryant = '';
        $columnaEnLinea    = 0;
        $numeroLinea       = 0;
        $ventana= 0;

        while ($myrowFuntion = DB_fetch_array($ReFuntion)) {
            if ($nombrecategoryant != $myrowFuntion['name']) {
                $columnaEnLinea = $columnaEnLinea + 1;

                if ($nombrecategoryant != '') {
                    echo '        </table></td>';

                    if ($columnaEnLinea < 4) {
                        echo '      <td width="80" style="background-image: url(\'images/imgs/div3.jpg\'); background-repeat:repeat-y;"></td>';
                    }

                    if ($columnaEnLinea >= 4) {
                        echo '          </tr>';
                        echo '          </table></td>';
                        echo '      </tr>';

                        echo '      <tr>';
                        //echo '        <td class="bkg_areat"><table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">';
                        echo '        <td style="border-left: 1px solid #1B693F; border-right: 1px solid #1B693F;"><table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">';
                        echo '          <tr>';
                        $columnaEnLinea = 1;
                        $numeroLinea    = $numeroLinea + 1;
                    }
                }

                echo '      <td width="270" valign="top"><table width="252" border="0" cellspacing="0" cellpadding="0">';

                if ($numeroLinea > 0) {
                    echo '        <tr><td><br></td><td></td>';
                    echo '          </tr>';
                }

                echo '        <tr>';
                //echo '                <td width="33">&nbsp;&nbsp;<img src="images/imgs/' . $myrowFuntion['imagecategory'] . '" width="23" height="23" /></td>';
                echo '<td width="33">&nbsp;&nbsp;<i class="' . $myrowFuntion['imagecategory'] . '"></i></td>';
                echo '          <td width="225" class="titulo_gris">' . ($myrowFuntion['name']) . '</td>';
                echo '          </tr>';

                $nombrecategoryant = $myrowFuntion['name'];
            }

            echo '        <tr>';
            echo '          <td colspan="2"></td>';
            echo '          </tr>';
            echo '        <tr>';
            //echo '                <td width="33">&nbsp;&nbsp;&nbsp;<img src="images/imgs/mas.png" width="12" height="8" /></td>';
            echo '<td width="33">&nbsp;&nbsp;&nbsp;<i class="fa fa-angle-right text-danger"></i></td>';

            $SQL = "SELECT *
              FROM sec_favxuser
              WHERE functionid= '" . $myrowFuntion['functionid'] . "'
                AND userid = '" . $_SESSION['UserID'] . "'";
            $result = DB_query($SQL, $db);

            $tipo = '_self';
            if ($submodulo == '30') {
                $tipo = '_blank';
            }

            echo "<td>";

            if (HavepermissionHeader($_SESSION['UserID'], 1278, $db) == 1) {
                echo '<a href="index.php?Mod=Agregar&functionid=' . $myrowFuntion['functionid'] . '&sub=' . $myrowFuntion['submoduleid'] . '&submodulesel='.$myrowFuntion['submoduleid'].'&categoryid=' . $myrowFuntion['categoryid'] . '">
                        <img width=17 height=17 src="images/Edit.ico" HSPACE="1"  align="left" border="0" title="' . _('Editar o Modificar') . '" onclick="fnMoverFuncion(\''.$myrowFuntion['functionid'].'\', \''.$myrowFuntion['submoduleid'].'\', \''.$myrowFuntion['categoryid'].'\', \''.$myrowFuntion['nombreModificar'].'\'); return false;">
                      </a>';
            }

            # Generación de url encriptada para el envío a los catalogos generales
            $enc = new Encryption();
            $urlDominio = $enc->encode("&dominiogeneral=>{$myrowFuntion['functionid']}");

            if (DB_num_rows($result) > 0) {
                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a name="NombreFuncion_'.$myrowFuntion['functionid'].'" id="NombreFuncion_'.$myrowFuntion['functionid'].'" target="' . $tipo . '" href="' . $myrowFuntion['url'] . '?URL='.$urlDominio.'" class="tabla_combos" title="' . ($myrowFuntion['comments']) . '">' . ((($myrowFuntion['shortdescription']))) . '</a></td>';
            } else {
                echo '<i class="fa fa-star text-default" title="' . _('Agregar a Favoritos') . '" onclick="location.href=\'index.php?Oper=Agregar&functionfav=' . $myrowFuntion['functionid'] . '&categoryidfact=' . $myrowFuntion['categoryid'] . '\'" style="cursor:pointer"></i>&nbsp;<a name="NombreFuncion_'.$myrowFuntion['functionid'].'" id="NombreFuncion_'.$myrowFuntion['functionid'].'"  target="' . $tipo . '" href="' . $myrowFuntion['url'] . '?URL='.$urlDominio.'" class="tabla_combos" title="' . ($myrowFuntion['comments']) . '">' . ((($myrowFuntion['shortdescription']))) . '</a></td>';
            }

            echo '</tr>';

            //if ($_GET['Mod'] == "Agregar" & $_GET['functionid'] == $myrowFuntion['functionid']) {
                //echo "<tr name='DivModFuncion' id='DivModFuncion'>";
            
            if ($ventana == 0) {
                echo "<div name='DivModFuncion' id='DivModFuncion' style='display: none; background-color: #D8D8D8; border-radius: 12px 12px 12px 12px; padding: 10px; position: fixed; width: 400px;'>";
                echo '<div ALIGN=right width="200px"><a href="index.php?"><img width=17 height=17 src="images/cancel.gif" HSPACE="1"   border="0" title="' . _('Cancelar') . '" onclick="fnCerrarVentana(); return false;"></a></div>';
                echo "Nombre: <input size=34% type=text name=txt_nombre_funcion id=txt_nombre_funcion value='" .
                $myrowFuntion['nombreModificar'] . "'>";
                echo "<input type=hidden name=txt_functionid id=txt_functionid value='" . $myrowFuntion['functionid'] . "'>";
                echo "<input type=hidden name=ocultar value='" . _('si') . "'>";
                  
                // llenamos el drop Dawun con los submodulos
                echo '<br>' . _('M&oacute;dulo :') . '';
                echo '<select name="cmb_capituloid" id="cmb_capituloid">';
                $sql = "SELECT DISTINCT IFNULL(sm.linea,1) AS linea, sm.orderno, 
                  sm.submoduleid,
                  sm.title,
                  sm.url,
                  s.moduleid
                  FROM sec_modules s,
                  sec_submodules sm                
                  WHERE s.moduleid=sm.moduleid
                  AND s.active=1                                  
                  AND s.moduleid=1
                  AND sm.active=1
                  ORDER BY linea, sm.orderno";

                $result = DB_query($sql, $db);
                while ($row = DB_fetch_array($result, $db)) {
                    if ($row['submoduleid'] == $_GET['sub']) {
                        echo '<option value="' . $row['submoduleid'] . '  " selected>' . $row['title']."</option>";
                    } else {
                        echo '<option value="' . $row['submoduleid'] . '" >' . $row['title']."</option>";
                    }
                }
                echo "</select>";
                
                echo '</br>' . _('Sub M&oacute;dulo:') . '';
                echo '<select name="cmb_categoria" id="cmb_categoria" style="margin-left: 13px">';
                $sql = "SELECT  categoryid, name, active, imagecategory, orderno
                        FROM sec_categories
                        ORDER BY orderno";

                $result = DB_query($sql, $db);
                while ($row = DB_fetch_array($result, $db)) {
                    if ($row['categoryid'] == $_GET['categoryid']) {
                        echo '<option value="' . $row['categoryid'] . '  " selected>' . $row['name']."</option>";
                    } else {
                        echo '<option value="' . $row['categoryid'] . '" >' . $row['name']."</option>";
                    }
                }
                echo '</select>';
                  
                // agregamos el select para Activo Inactivo
                echo '<br>' . _('Estatus: ');
                echo '<select name="cmb_activo" id="cmb_activo">';
                if (!isset($_POST['activo'])) {
                    $_POST['cmb_activo'] = 1;
                }
                if ($_POST['cmb_activo'] == 1) {
                    echo '<option selected value="1">Activo</option>';
                    echo '<option  value="0">InActivo</option>';
                } elseif ($_POST['cmb_activo'] == 0) {
                    echo '<option value="1">Activo</option>';
                    echo '<option selected  value="0">InActivo</option>';
                } else {
                    echo '<option selected value="1">Activo</option>';
                    echo '<option value="0">InActivo</option>';
                }
                echo '</select>';
                  
                echo "</br><div align=right width=100%><button type='submit' name='GenerarDif' onclick='fnModificarFuncion();'>Guardar Cambio</button></div>";
                echo "</div>";

                $ventana++;
            }
        }

        echo '        </table></td>';
        echo '      <td width="80" style="background-image: url(\'images/imgs/div3.jpg\'); background-repeat:repeat-y;"></td>';

        for ($idxy = $columnaEnLinea; $idxy < 3; $idxy++) {
            echo '      <td width="270" valign="top"><table width="252" border="0" cellspacing="0" cellpadding="0">';
            echo '        <tr>';
            echo '          <td width="33"></td>';
            echo '          <td width="225" class="titulo_gris"></td>';
            echo '          </tr>';
            echo '        </table></td>';
            if ($idxy < 2) {
                echo '<td width="80" style="background-image: url(\'images/imgs/div3.jpg\'); background-repeat:repeat-y;"></td>';
            }
        }
    }

    echo '          </tr>';
    echo '          </table></td>';
    echo '      </tr>';
    
    echo '      <tr style="height: 5px;">';
    // echo '        <td height="22"><img src="images/iconos/footer_verde.png" width="100.1%" height="22" /></td>';
    echo '        <td style="background-color: #727378; border-right: 1px solid rgb(27, 105, 63);"></td>';
    echo '      </tr>';

    echo '    </table></td>';
    echo '        </tr>';
    echo '<br>';

    ?>
      <!-- <br>
<div class="container">
    <div class='col-sm-6'>
      <component-text id="txtRFC" name="txtRFC" placeholder="Agrega RFC"></component-text>
      <br><br>
      <component-text-label label="Label: " id="hola" name="hola"></component-text-label>
      <br><br>
      <component-number id="txtNumber" name="txtNumber"></component-number>
      <br><br>
      <component-number-label label="Cantidad:" id="txtNumber" name="txtNumber"></component-number-label>
      <br><br>
      <component-date></component-date>
      <br><br>
      <component-date-label label="Fecha 2: "></component-date-label>
      <br>
      <component-button type="submit" id="btn" name="btn" onclick="fnPrueba()" value="Mostrar Funcion"></component-button>
      <component-decimales id="txtNumberDecimalesSinLabel" name="txtNumberDecimalesSinLabel"></component-decimales>
      <br>
      <component-decimales-label label="Numeros Decimales:" id="txtNumberDecimales" name="txtNumberDecimales"></component-decimales-label>
    </div>
</div>
      <br> -->
        <?php if ($_SESSION['UserID'] == "desarrollo") : ?>
        <!-- <div class="input-group">
          <span class="input-group-addon" style="background: none; border: none;"> selectRazonSocial </span>
          <select id="selectRazonSocial" name="razo[]" class="form-control selectRazonSocial" multiple="multiple"></select>
        </div>
        <div class="input-group">
          <span class="input-group-addon" style="background: none; border: none;"> selectURG </span>
          <select id="selectURG" name="selectURG[]" class="form-control selectURG" multiple="multiple"></select>
        </div>
        <div class="input-group">
          <span class="input-group-addon" style="background: none; border: none;"> selectRegiones </span>
          <select id="selectRegiones" name="selectRegiones[]" class="form-control selectRegiones" multiple="multiple"></select>
        </div>
        <div class="input-group">
          <span class="input-group-addon" style="background: none; border: none;"> selectArea </span>
          <select id="selectArea" name="selectArea[]" class="form-control selectArea" multiple="multiple"></select>
        </div>
        <div class="input-group">
          <span class="input-group-addon" style="background: none; border: none;"> selectAlmacen </span>
          <select id="selectAlmacen" name="selectAlmacen[]" class="form-control selectAlmacen" multiple="multiple"></select>
        </div>
        <div class="input-group">
          <span class="input-group-addon" style="background: none; border: none;"> selectDepartamentos </span>
          <select id="selectDepartamentos" name="selectDepartamentos[]" class="form-control selectDepartamentos" multiple="multiple"></select>
        </div>
        <div class="input-group">
          <span class="input-group-addon" style="background: none; border: none;"> selectCapitulos </span>
          <select id="selectCapitulos" name="selectCapitulos[]" class="form-control selectCapitulos" multiple="multiple"></select>
        </div> 
        <div class="input-group">
          <span class="input-group-addon" style="background: none; border: none;"> Estado </span>
          <select id="cmbEstado" name="cmbEstado[]" class="form-control selectGeografico" multiple="multiple"></select>
        </div> -->

        <?php endif ?>

        <?php

        include 'includes/footer_Index.inc';
} else {
    echo "Compruebe Acceso al sistema";
    include 'includes/footer_Index.inc';
    exit;
}
?>
<script type="text/javascript">
  var ventana = null;
  var ventanaBig = null;

  function openNewWindow(url) {
      if (ventana == null || ventana.closed)
          ventana = window.open(url, '', 'width=650,height=440'); // 500 y 200
      else
          alert('Esta funcion ya se esta ejecuntando, favor de cerrarl la ventana antes de abrir otra');
  }

  function openNewWindowBig(url) {
      if (ventanaBig == null || ventanaBig.closed)
          ventana = window.open(url, '', 'width=500,height=430');
      else
          alert('Esta funcion ya se esta ejecuntando, favor de cerrarl la ventana antes de abrir otra');
  }

function fnMoverFuncion (pFuncion, pSubmodulo, pCategoria, pNombre) {
  document.getElementById("txt_nombre_funcion").value= pNombre;
  document.getElementById("txt_functionid").value= pFuncion;
  document.getElementById("DivModFuncion").style.display= "inline";
}

function fnCerrarVentana(){
  document.getElementById("DivModFuncion").style.display= "none"; 
}
</script>