<?php
// incluir archivos adicionales 
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include("includes/ElementosGenerales.inc");
$funcion = 956;
include ('includes/SecurityFunctions.inc');

//error_reporting(E_ALL);
//ini_set('display_errors', '1');
// referencias a paginas de estilos
echo '<link href="' . $rootpath . '/css/'. $_SESSION['Theme'] . '/default.css" rel="stylesheet" type="text/css"/>';
echo '<link href="' . $rootpath . '/css/css_lh.css" rel="stylesheet" type="text/css"/>';


// declaracion de variables
if(isset($_GET['tagref'])){
    $_POST['tagref'] = $_GET['tagref'];
}elseif(isset ($_POST['tagref'])){
    $_POST['tagref'] = $_GET['tagref'];
}

if(isset($_GET['tipo'])){
    $_POST['tipo'] = $_GET['tipo'];
}elseif(isset ($_POST['tipo'])){
    $_POST['tipo'] = $_GET['tipo'];
}

if(isset($_GET['separado'])){
    $_POST['tipo'] = $_GET['tipo'];
}elseif(isset ($_POST['tipo'])){
    $_POST['tipo'] = $_GET['tipo'];
}

$objetogasto= "";
if (isset($_POST['CapturaObjeto'])){
    $objetogasto= $_POST['CapturaObjeto'];
}

if (isset($_POST['MostrarTodo'])){
    $objetogasto= "";
}

$identificador= $_GET["identificador"];
$linea= $_GET["linea"];
$separado= $_GET["separado"];
if($_POST['tipo'] == 1){
    $linea_clave= 0;
}else{
    $linea_clave= 1;
}
//Digitos a truncar
if(isset($_SESSION['TruncarDigitos']))
{
	$digitos=$_SESSION['TruncarDigitos'];
}else{
	$digitos=4;
}
//Permiso para visualizar claves de periodos anteriores
$per_ver_claves_periodos_anteriores=  Havepermission($_SESSION ['UserID'], 1477, $db);

// encabezado de la pagina
$title= "Busqueda de Claves Presupuestales";
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' ' . $title.'<br>';
echo "<br>";

// bloque de formulario para el manejo de submit
echo "<form name=form1 action='" . $_SERVER['PHP_SELF'] . "?" . SID . "identificador=".$identificador."&linea=".$linea."&tagref=".$_POST['tagref']."&tipo=".$_POST['tipo']."&separado=".$separado."' method=post>";
echo "<br>";
echo "<div style='text-align:center;'><font size=3>Objeto del Gasto:</font>";
echo InsertaElemento("texto", "CapturaObjeto", $objetogasto);
echo "&nbsp;".InsertaElemento("boton", "BuscarObjeto", "Buscar");
echo "&nbsp;|&nbsp;";
echo InsertaElemento("boton", "MostrarTodo", "Mostrar Todo");
echo "</div>";
echo "</form>";

echo "<br>";

// consulta para sacar el periodo considerando la fecha actual
$resultado= DB_query("SELECT periodno FROM periods WHERE DATE_FORMAT(lastdate_in_period, '%Y-%m-%d')=DATE_FORMAT(now(), CONCAT(LAST_DAY(now())))", $db);
$renglon_periodo= DB_fetch_array($resultado);
$periodo= $renglon_periodo["periodno"];

    
// consulta para sacar todas las claves presupuestales que tiene asignadas el usuario
$consulta= "SELECT DISTINCT 0 as period, chartdetailsbudgetbytag.accountcode, 
            tb_cat_partidaspresupuestales_partidaespecifica.descripcion as accountnamepres, sec_unegsxuser.tagref, '' as numero_oficio
            FROM chartdetailsbudgetbytag 
            INNER JOIN tb_cat_partidaspresupuestales_partidaespecifica ON chartdetailsbudgetbytag.partida_esp = tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada
            INNER JOIN tags ON chartdetailsbudgetbytag.tagref = tags.tagref 
            INNER JOIN sec_unegsxuser ON chartdetailsbudgetbytag.tagref= sec_unegsxuser.tagref
            WHERE sec_unegsxuser.userid= '".$_SESSION["UserID"]."' 
            AND chartdetailsbudgetbytag.tagref='".$_POST['tagref']."'";
             
if (!empty($objetogasto)){
    $consulta.= " AND tb_cat_partidaspresupuestales_partidaespecifica.descripcion LIKE '%".$objetogasto."%'";
}

//echo $consulta;

$resultado= DB_query($consulta, $db);
//

// tabla donde se muestran los datos
echo '<table cellpadding=5 cellspacing=0 border="1" style="border-collapse:collapse;margin-left:auto;border-color:#f6f6f6">';
echo "<tr>";
echo "<th class='titulos_principales'>Clave</th>";
echo "<th class='titulos_principales'>Periodo</th>";
echo "<th class='titulos_principales'>Folio</th>";
echo "<th class='titulos_principales'>Descripcion</th>";
echo "<th class='titulos_principales'>Disponibilidad</th>";
echo "</tr>";

while ($renglon= DB_fetch_array($resultado)) {    
    $disponibilidad = TraePresupuestoDisponible($renglon['accountcode'], $renglon['tagref'], $renglon['period'], $db); 
    $clave_simplificada= substr($renglon["accountcode"], 14, 17);
    
    echo "<tr>";
    echo "<td>".InsertaElemento("boton", "botonseleccion", $clave_simplificada, "", true, "onclick='enviar_codigo(".$linea_clave.", \"".$linea."\", ".$separado.");'")."</td>";
    echo "<td>".$renglon['mes']."</td>";
    echo "<td>".$renglon['numero_oficio']."</td>";
    echo "<td><label title='".$renglon['accountcode']."'>".$renglon["accountnamepres"]."</label><input type='hidden' name='ClavePresupuestal_".$linea_clave."' id='ClavePresupuestal_".$linea_clave."' value='".$renglon['accountcode']."'></td>";
    echo "<td style='text-align:right;'>$ ".number_format($disponibilidad,$digitos,'.',',')."</td>";  
    //echo "<td style='text-align:right;'>$ ".$disponibilidad."</td>";
    echo "</tr>";
    
    $linea_clave++;
}

echo "<tr>";
echo "<td colspan=5 style='text-align:center;'>".InsertaElemento("boton", "cerrarventana", "Cerrar", "", true, "onclick='window.close();'")."</td>";

echo "</table>";

?>

<script>

 function enviar_codigo(linea_clave, linea, separado)
 {
    var clave_presupuestal= document.getElementById("ClavePresupuestal_"+linea_clave).value;           
    var cadena_separada= clave_presupuestal.split("-"); 
    var indice= 0;   
    var contador= 0;
    
    window.opener.document.getElementById("clavepresupuestal_"+linea).value= clave_presupuestal;
    //window.opener.document.getElementById("presupuesto_"+linea).value= presupuesto;
    
    if (separado == 2) {
        for (indice in cadena_separada){
            contador++;
            var elemento = cadena_separada[indice];
            var lineaid= linea+"."+ contador;

            window.opener.document.getElementById(lineaid).value= elemento;
        }
    }    
    
    
    window.close();
 }
</script>