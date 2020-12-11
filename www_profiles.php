<?php
/**
 * wwww_profiles
 *
 * @category 
 * @package      ap_grp
 * @author       Arturo Lopez PeÒa <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha CreaciÛn: 13/11/2017
 * Fecha ModificaciÛn: 11/11/2017
 * Vista para el proceso de REPORTE DE PAGOS A PROVEEDORES
 */

$PageSecurity=15;

require "includes/SecurityUrl.php";

include('includes/session.inc');
$title = _('Mantenimiento de Perfiles');
include('includes/header.inc');
$funcion=147;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
//echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/group_add.png" title="' . _('Buscar') . '" alt="">' . '<h3> ' . $title.'</h3><br>';

//Seleccion de nuevo menú o viejo menu
header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

if($_SESSION['ShowIndex']!=0){
	$sec_functions = "sec_functions_new";
}else{
	$sec_functions = "sec_functions";
}

// Make an array of the security roles
$sql = 'SELECT secroleid, 
		secrolename 
	FROM securityroles ORDER BY secroleid';
$Sec_Result = DB_query($sql, $db);
$SecurityRoles = array();
// Now load it into an a ray using Key/Value pairs
while( $Sec_row = DB_fetch_row($Sec_Result) ) {
	$SecurityRoles[$Sec_row[0]] = $Sec_row[1];
}
DB_free_result($Sec_Result);

if (isset($_GET['Selectedprofile'])){
	$Selectedprofile = $_GET['Selectedprofile'];
} elseif (isset($_POST['Selectedprofile'])){
	$Selectedprofile = $_POST['Selectedprofile'];
}


if (!isset($_POST['NombrePerfil'])) {
	$_POST['NombrePerfil']='';
}
if (!isset($_POST['Blocked'])) {
	$_POST['Blocked']=0;
}

//realiza operaciones de alta y modificacion de perfil
if (isset($_POST['submit'])) {
	//initialise no input errors assumed initially before we test
	$InputError = 0;
	if (ContainsIllegalCharacters($_POST['NombrePerfil'])) {
		$InputError = 1;
		prnMsg(_('El nombre de perfil no puede contener los siguientes caracteres ') . " - ' & + \" \\ " . _('o espacios'),'error');
	}
	
	if ($Selectedprofile AND $InputError !=1) {
		if ((strlen($_POST['NombrePerfil'])>0) AND ($InputError !=1)) {
			// check that the entered branch is valid for the customer code
			$sql = "SELECT * from sec_profiles where name='" . $_POST['NombrePerfil'] . "' and profileid<>".$Selectedprofile;
			$ErrMsg = _('Ya existe este perfil en la base de datos');
			$DbgMsg = _('El SQL usado en la validacion del perfil fue:');
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
			if (DB_num_rows($result)!=0){
				prnMsg(_('El Nombre del perfil ya existe '),'error');
				$InputError = 1;
			}
		}
		$sql = "UPDATE sec_profiles
		        SET name='" . $_POST['NombrePerfil'] . "',
			    active=" . $_POST['Blocked'] . "
			WHERE profileid = ".$Selectedprofile;
			
		$msg = _('El perfil seleccionado ha sido actualizado');
		$perfilseleccionado=$Selectedprofile;
		
	}
	elseif ($InputError !=1) {
		$sql = "INSERT INTO sec_profiles (name,active)
			VALUES ('" . $_POST['NombrePerfil'] . "',".$_POST['Blocked'].")";
		$msg = _('Registro de usuario Exitoso');
		
	}
	
	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		$ErrMsg = _('Las operaciones sobre el registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		$sql="select * from sec_profiles WHERE name = '".$_POST['NombrePerfil']."'";
		$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		$myrow = DB_fetch_array($result);
		$perfilseleccionado=$myrow['profileid'];
		//eliminamos los permisos por perfil 
		$sql="Delete from sec_funxprofile WHERE profileid = ".$perfilseleccionado;
		$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		
		if (isset($_POST['total'])){
			$totalfunciones=$_POST['total'];
			for ( $funciones = 0 ; $funciones <= $totalfunciones ; $funciones++) {
				if ($_POST['funcion'.$funciones]==TRUE){
					$idfuncionr=$_POST['fun'.$funciones];
					$sql="insert into sec_funxprofile (profileid,functionid)";
					$sql=$sql." values(".$perfilseleccionado.",".$idfuncionr.")";
					//echo '<br>'.$sql.'<br>';
					$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
					$DbgMsg = _('El SQL utilizado es:');
					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}
			
		}
	}
	
	
	unset($_POST['NombrePerfil']);
	unset($_POST['Blocked']);
	unset($Selectedprofile);
	
}

//echo "<div class='panel panel-default'>";
/*Consulta de los perfiles dados de alta */
if (!isset($Selectedprofile)) {
	$sql = 'SELECT * FROM sec_profiles order by name';
	$result = DB_query($sql,$db);
	echo '<div class="container"><div class="col-xs-12 col-md-12 "> <div class="col-md-7" style="float: none; margin:auto;"> <table class="table table-striped table-bordered" width="90%">';
	echo "<thead style='color:#fff'><th class='text-center bgc8'>" . _('#') . "</th>
		<th class='text-center bgc8'>" . _('Nombre') . "</th>
		<th class='text-center bgc8'>" . _('Activo') . "</th>
		<th colspan=3 class='text-center bgc8'>" . _('Editar') ."</th>
	</thead><tbody class='text-center'>";

	$k=0; //row colour counter
	$contador=0;
	while ($myrow = DB_fetch_row($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		$contador=$contador+1;
		if($myrow[2]==1){
			$statusprofile=_('Activo');
		} else{
			$statusprofile=_('Inactivo');
		}
		/*Muestra Listado de Perfiles*/
		$enc = new Encryption;
        $url = "&Selectedprofile=>".$myrow[0];
        $url = $enc->encode($url);
        $ligaEncriptada= "URL=" . $url;

       
   

		/*printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href=\"%s&Selectedprofile=%s\">" . _('Editar') . "</a></td>
			</tr>",
			$contador,
			$myrow[1],
			$statusprofile,
			$_SERVER['PHP_SELF']  . "?" . SID,
			$myrow[0]
			);*/
		echo "<td>".$contador."</td>";
		echo "<td>".$myrow[1]."</td>";
		echo "<td>".$statusprofile."</td>";
		echo "<td><a href=\"".$_SERVER['PHP_SELF']."?".$ligaEncriptada."\">" . _('Editar') . "</a></td>";

		
	} //END WHILE LIST LOOP
	echo '</tbody></table></div></div></div><br>';
} //end of ifs and buts!

/*Seccion de alta de Perfiles*/
if (isset($Selectedprofile)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('') . '</a></div><br>';
}//. _('Consulta de Perfiles Existentes') 
echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<table whidth="90%" class="text-left"> <div class="col-xs-12 col-md-12 "> <div class="col-md-6" style="float: none; margin:auto;">';
if (isset($Selectedprofile)) {
	//Editar un perfil seleccionado

	$sql = " SELECT * FROM sec_profiles WHERE profileid=" .$Selectedprofile ;
	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);
	$_POST['profileid'] = $myrow['profileid'];
	$_POST['NombrePerfil'] = $myrow['name'];
	$_POST['Blocked'] = $myrow['active'];
	echo "<input type='hidden' name='Selectedprofile' value='" . $Selectedprofile . "'>";
	echo "<input type='hidden' name='PerfilID' value='" . $_POST['PerfilID'] . "'>";
	
	//echo '<table> <tr><td>' . _('Nombre Perfil') . ':</td><td><b>';
	//echo $_POST['NombrePerfil'] . '</b></td>';
	echo "<input type='hidden' name='NombrePerfil' size=22 maxlength=100 value='" . $_POST['NombrePerfil'] . "'></tr>";
	 echo'<div>
            <component-text-label label="Nombre Perfil:" id="perfil" Name="perfil" placeholder="Nombre Perfil" maxlength="50"
        value="'.$_POST['NombrePerfil'] .'" ></component-text-label>
        </div>
        <br/>';
}else { //end of if $SelectedUser only do the else when a new record is being entered
	//echo "<table bordercolor=white><tr bgcolor=white><td>" . _('Nombre Perfil') . ":</td><td><input type='TEXT' name='NombrePerfil' size=22 maxlength=100 value='" . $_POST['NombrePerfil'] . "'></td></tr>";
	echo '<div>
            <component-text-label label="Nombre Perfil:" id="perfil" name="NombrePerfil" placeholder="Nombre Perfil" maxlength="50"
        value="'.$_POST['NombrePerfil'] .'"></component-text-label>
        </div>
        <br/>';
	
}

//echo '<tr bgcolor=white><td>' . _('Estatus') . ":</td><td><select name='Blocked'>";
echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Estatus: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="Blocked" tabindex="13" Name=Blocked class="estatus"  >';

if ($_POST['Blocked']==0){
	echo '<option selected value=0>' . _('Inactivo');
	echo '<option value=1>' . _('Activo');
} else {
 	echo '<option selected value=1>' . _('Activo');
	echo '<option value=0>' . _('Inactivo');
}
//echo '</select></td></tr></table>';
echo '</select></div></div> <br>';
echo '</div></div></table>';
if (isset($_POST['UserID'])){
	$perfilid=$_POST['PerfilID'];	
} elseif (isset($_GET['Selectedprofile'])) {
	$perfilid=$_GET['Selectedprofile'];	
}else {
	$perfilid=0;
}
$z=0;
/*asignacion de funciones por perfil*/
    //$sql = "SELECT * FROM sec_modules s where active=1 ";
    $sql = "SELECT * FROM sec_modules  where active=1 ";
    $Result = DB_query($sql, $db);
    echo '<table width=90% >';
    echo '<div class="centre"> ';
    echo "<tr class='text-center'><td style=vertical-align:center;><font style=font-size:15px;><b>"._('Funciones por Perfil')."</b></font></td></tr>";
    echo "</div>";
    
    $encontromodulo=false;
    if (DB_num_rows($Result)>0 ) {
       // echo "<tr><td><hr></td></tr>";
	$encontromodulo=true;
    }
    $k=0; //row colour counter
    $j=0;
    while($AvailRow = DB_fetch_array($Result)) {
                            
            if ($k==1){
			echo '<tr class="EvenTableRows">';
                    $k=0;
            } else {
                    echo '<tr class="OddTableRows">';
                    $k=1;
            }
            $moduleid=$AvailRow['moduleid'];
            $namemodule=strtolower($AvailRow['title']);
            //echo "<td ><li><font style=font-size:11px;><b>".ucwords($namemodule)."</b></font></li></td></tr>";
            echo "<td ><H4><span class='glyphicon glyphicon-plus'> </span>".ucwords($namemodule)."</H4></td></tr>";
            /* trae submodulos*/
          
            $y=0;
            $a=0;
	    $nombrecategoria="";
	    $nombrecategoryant="";
            $sql = "SELECT * FROM sec_submodules s where active=1 and moduleid=".$moduleid;
            $ReSubmodule = DB_query($sql, $db);
            $condmodule=false;
            if (DB_num_rows($ReSubmodule)>0 ) {
                    echo "<tr><td><table width=90% >";
                    $condmodule=true;
            }
            while($submoduleRow = DB_fetch_array($ReSubmodule)) {
                    if ($y==1){
                            echo '<tr class=EvenTableRows>';
                            $y=0;
                    } else {
                            echo '<tr class=OddTableRows>';
                            $y=1;
                    }
                    $submoduleid=$submoduleRow['submoduleid'];
                    $namesubmodule=$submoduleRow['title'];
                   // $namesubmodule=strtolower($submoduleRow['title']);

                   // $t=substr($namesubmodule,1);// no incluye la primer letra
                   // echo '<td ><li><font style=font-size:10px;><b>'.ucwords($namesubmodule).'S</b></font></li>';
                   //$subcadena= substr($namesubmodule, 1);
                   //$subcadenaacentos=utf8_encode(strtr($subcadena, "¿¡¬√ƒ≈«»… ÀÃÕŒœ–—“”‘’÷ÿŸ‹⁄", "‡·‚„‰ÂÁËÈÍÎÏÌÓÔÒÚÛÙıˆ¯˘¸˙"));
                   //$min=  strtolower($subcadenaacentos);


                   echo '<td> <div class="panel panel-default"><!-- '.$namesubmodule.' -->
    <div role="tab" id="headingOne" class="panel-heading text-left">
        <h4 class="panel-title row">
            <div class="col-md-3 col-xs-3">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#'.str_replace(' ', '_',$namesubmodule ).'" aria-expanded="false" aria-controls="collapse" class="collapsed"><span class="glyphicon glyphicon-chevron-down"></span>
                    '.$namesubmodule.'
                </a>
            </div>
        </h4>
    </div>
    <div id="'.ucwords(str_replace(' ', '_',$namesubmodule )).'" name="'.ucwords(str_replace(' ', '_',$namesubmodule )).'" role="tabpanel" aria-labelledby="headingOne" class="panel-collapse collapse out"><br>

        <div class="text-left container">';
                    /* trae funciones*/
                    $sql = "SELECT distinct F.title,
				   FP.profileid as profilea,
				   FP.functionid as functiona,
				   F.functionid as funcion,
				   F.type,
				   C.name as category
			    FROM $sec_functions F left join  sec_funxprofile FP on F.functionid = FP.functionid
			         and FP.profileid=".$perfilid.",
				 sec_profiles P, sec_categories C
			    WHERE F.active=1 and C.categoryid=F.categoryid
			    and F.SubModuleid=".$submoduleid." order by  C.name, F.type, F.title";
                    $ReFuntion = DB_query($sql, $db);
                    $condfuntion=false;
                    if (DB_num_rows($ReFuntion)>0 ) {
                           // echo "<table width=100% CELLPADDING=0 CELLSPACING=0 border=1 bordercolor=black><tr>";
                            echo "<table width=100% CELLPADDING=0 CELLSPACING=0 ><tr>";
                            $condfuntion=true;
                    }
		    $nombrecategoryant = "";
                    while($ResFuntion = DB_fetch_array($ReFuntion)) {
                            $Funtionid=$ResFuntion['funcion'];
                            $nameFuntion=strtolower($ResFuntion['title']);
			    $Funtionval=$ResFuntion['functiona'];
			    $nombrecategoria= strtolower($ResFuntion['category']);
			    
			if ($nombrecategoria!=$nombrecategoryant and $nombrecategoryant!=''){
				echo "</table></td>";
			    }
			    if ($nombrecategoria!=$nombrecategoryant){
				//echo '<td style=vertical-align:top;><li><b><font style=font-size:12px;>'.ucwords($nombrecategoria).'</font></b></li><table width=100% >';
				echo '<td style=vertical-align:top;><b><span class=" 	glyphicon glyphicon-minus"> </span><font style=font-size:12px;>'.ucwords($nombrecategoria).'</font></b><table width=100% >';
				//categoria
			    }
			    if (is_null($Funtionval)){
				echo '<tr>
					<td>
					    <input type=checkbox name=funcion'.$z.'>
						<font style=font-size:10px;>'.$Funtionid.' '.ucwords($nameFuntion).'</font>
						<input type=hidden name=fun'.$z.' value=' .$Funtionid . '>
					</td>
					</tr>';
			    } else{
				echo '<tr>
					<td >
					     <input type=checkbox name=funcion'.$z.' checked>
						<font style=font-size:10px;>'.str_repeat('0',4-strlen($Funtionid)).$Funtionid.' '.ucwords($nameFuntion).'</font>
						<input type=hidden name=fun'.$z.' value=' .$Funtionid . '>
					</td>
				      </tr>';
			    }
			    $nombrecategoryant=$nombrecategoria;
                            $z=$z+1;
                    }//Fin de while para extraer funciones
		    
                    if ($condfuntion==true){// si existian funciones para ese submodulo se cierra la tabla de funciones y submodulos por modulos
			  // echo "</table></td>";
			  echo '</table>  </div><!--container-->
</div><!--fin contenido -->
</div><!-- fin panel datos de la empresa -->';
			    echo "</tr></table>";
                    }	
		   
		    
            }
            $condfuntion==false;
	    if ($condmodule==true){// Si existian modulos se cierra la tabla
		echo "</table></td></tr>";
            }
		
            $j=$j+1;
            $submoduleid=0;
            
    }
    echo '</table>';
    echo '<div class="centre"><input type="hidden" name="total" value="' .$z . '"></div>';
    echo '<div class="text-center"><input class="btn bgc8" style="color:#fff" type="submit" name="submit" value="' . _('Registrar') . '"></div></form>';
    
    include('includes/footer_Index.inc');
   // exit ;
?>
<?php //echo "</div>";?>
<script type="text/javascript">
	fnFormatoSelectGeneral(".estatus");

</script>