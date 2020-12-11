<?php
/**
 * ABC Dependencia
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 20/09/2017
 * Fecha Modificación: 20/09/2017
 * Se realizan operación pero el Alta, Baja y Modificación. Mediante las validaciones creadas para la operación seleccionada
 */

$PageSecurity = 8;
include('includes/session.inc');
$funcion=168;
$title = traeNombreFuncion($funcion, $db);
include "includes/SecurityUrl.php";
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/SecurityFunctions.inc');
include('includes/SimpleImage.php');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

//ini_set('display_errors', 1);
if (isset($_POST['edorfc'])) {
    $edorfc= $_POST['edorfc'];
} else {
        $edorfc = "";
}
    
if (isset($_POST['edonom'])) {
    $edonom= $_POST['edonom'];
} else {
        $edonom = "";
}
    
if (isset($_POST['edoadd'])) {
    $edoadd= $_POST['edoadd'];
} else {
        $edoadd = "";
}

if (isset($_POST['edotel'])) {
    $edotel= $_POST['edotel'];
} else {
        $edotel = "";
}

if (isset($_POST['edofax'])) {
    $edofax= $_POST['edofax'];
} else {
        $edofax = "";
}

if (isset($_POST['edoemail'])) {
    $edoemail= $_POST['edoemail'];
} else {
        $edoemail = "";
}
if (isset($_POST['regimen'])) {
    $regimen= $_POST['regimen'];
} else {
    $regimen = "";
}
/*if (isset($_POST['edotipo']))
{
	$edotipo= $_POST['edotipo'];
}	
else{
		$edotipo="";
	}	*/

if (isset($_GET['ordenpri'])) {
    $ordenpri = $_GET['ordenpri'];
} else {
    $ordenpri = "legalname";
}

/*if (isset($_GET['ordenseg']))
{
	$ordenseg = $_GET['ordenseg'];
}	
else{

	$ordenseg = "description";
}*/



$num_reg=10;

if (isset($_POST['num_reg'])) {
    $num_reg = $_POST['num_reg'];
}
//esta es la variable con la que nos guiamos para saber ssi es la primera vez que entran
//y para poder mostrar los datos del seleccionado cuando van a modificar
if (isset($_GET['u_tipovehiculo'])) {
    $u_tipovehiculo = $_GET['u_tipovehiculo'];
} elseif (isset($_POST['u_tipovehiculo'])) {
    $u_tipovehiculo = $_POST['u_tipovehiculo'];
}


if (isset($_GET['borrar'])) {
    $borrarr = $_GET['borrar'];
} elseif (isset($_POST['borrar'])) {
    $borrarr = $_POST['borrar'];
}

//$UploadTheFile='';
/*
if (isset($_FILES['ItemPicture']) AND $_FILES['ItemPicture']['name'] !='') {

	$result    = $_FILES['ItemPicture']['error'];
 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$filename = $_SESSION['part_pics_dir'] . '/' . $u_tipovehiculo . '.jpg';
	
	 //But check for the worst
	if (strtoupper(substr(trim($_FILES['ItemPicture']['name']),strlen($_FILES['ItemPicture']['name'])-3))!='JPG'){
		prnMsg(_('Solo archivos jpg son soportados - un archivo con terminacion jpg es esperado'),'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['ItemPicture']['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
		prnMsg(_('El tamano de archivo esta sobre el maximo permitido. El tama�o maximo en KB es') . ' ' . $_SESSION['MaxImageSize'],'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['ItemPicture']['type'] == "text/plain" ) {  //File Type Check
		prnMsg( _('Solo archivos de tipo graficos pueden ser subidos'),'warn');
         	$UploadTheFile ='No';
	} elseif (file_exists($filename)){
		prnMsg(_('Intentando sobreescribir una archivo de imagen'),'warn');
		$result = unlink($filename);
		if (!$result){
			prnMsg(_('La imagen actual no puede ser reemplazada'),'error');
			$UploadTheFile ='No'; 
		}
	}

	if ($UploadTheFile=='Yes'){
		$result  =  move_uploaded_file($_FILES['ItemPicture']['tmp_name'], $filename);
		$message = ($result)?_('File url') ."<a href='". $filename ."'>" .  $filename . '</a>' : _('Something is wrong with uploading a file');
		
	}
}*/ /* EOR Add Image upload for New Item  - by Ori */


//esta es la variable que guarda el error
$InputError = 0;
$InputNuevo=0;
// Verifica si el usuario ya dio click en el boton de Enviar o en el link de modificar o borrar registro
if (isset($_POST['enviar']) || isset($_GET['borrar']) || isset($_POST['modificar'])) {
    $nombre_archivo = $_FILES['ItemPicture']['name'];
    $tipo_archivo = $_FILES['ItemPicture']['type'];
    $tamano_archivo = $_FILES['ItemPicture']['size'];
    
    $nombre_archivo2 = $_FILES['ItemPictureAlterna']['name'];
    $tipo_archivo2 = $_FILES['ItemPictureAlterna']['type'];
    $tamano_archivo2 = $_FILES['ItemPictureAlterna']['size'];
    
    //aqui verifica que el nombre del tipo del vehiculo sea mayor a tres letras
    if (isset($_POST['nom_tipovehiculo']) && strlen($_POST['nom_tipovehiculo'])<3) {
        //si fue menor a tres letras se llena la variable de error y muestra mensaje
        $InputError = 1;
        echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close"
            data-dismiss="alert">&times;</button><p> 
            La Dependencia debe ser de al menos 3 caracteres de longitud
            </p>
          </div>';
    } elseif (isset($_POST['rfc']) && strlen($_POST['rfc'])<3) {
        //si fue menor a tres letras se llena la variable de error y muestra mensaje
        $InputError = 1;
        echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close"
            data-dismiss="alert">&times;</button><p> 
            El RFC debe ser de al menos 3 caracteres de longitud
            </p>
          </div>';
    } elseif (isset($_POST['add1']) && strlen($_POST['add1'])<3) {
        //si fue menor a tres letras se llena la variable de error y muestra mensaje
        $InputError = 1;
        echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close"
            data-dismiss="alert">&times;</button><p> 
            La calle debe ser de al menos 3 caracteres de longitud
            </p>
          </div>';
    } elseif (isset($_POST['add2']) && strlen($_POST['add2'])<3) {
        //si fue menor a tres letras se llena la variable de error y muestra mensaje
        $InputError = 1;
        echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close"
            data-dismiss="alert">&times;</button><p> 
            La colonia debe ser de al menos 3 caracteres de longitud
            </p>
          </div>';
    } elseif (isset($_POST['add3']) && strlen($_POST['add3'])<3) {
        //si fue menor a tres letras se llena la variable de error y muestra mensaje
        $InputError = 1;
        echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close"
            data-dismiss="alert">&times;</button><p> 
            La ciudad debe ser de al menos 3 caracteres de longitud
            </p>
          </div>';
    } elseif (isset($_POST['add4']) && strlen($_POST['add4'])<3) {
        //si fue menor a tres letras se llena la variable de error y muestra mensaje
        $InputError = 1;
        echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close"
            data-dismiss="alert">&times;</button><p> 
            El estado debe ser de al menos 3 caracteres de longitud
            </p>
          </div>';
    } elseif (isset($_POST['add5']) && strlen($_POST['add5'])<3) {
        //si fue menor a tres letras se llena la variable de error y muestra mensaje
        $InputError = 1;
        echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close"
            data-dismiss="alert">&times;</button><p> 
            El código postal debe ser de al menos 3 caracteres de longitud
            </p>
          </div>';
    } /*
	elseif (isset($_POST['regimen']) && strlen($_POST['regimen'])<3){
		//si fue menor a tres letras se llena la variable de error y muestra mensaje
		$InputError = 1;
		prnMsg(_('El regimen patronal debe ser de al menos 3 caracteres de longitud'),'error');
	}
	*/
    elseif (isset($_POST['tel']) && strlen($_POST['tel'])<3) {
        //si fue menor a tres letras se llena la variable de error y muestra mensaje
        $InputError = 1;
        echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close"
            data-dismiss="alert">&times;</button><p> 
            El teléfono debe ser de al menos 3 caracteres de longitud
            </p>
          </div>';
    } elseif (isset($_POST['fax']) && strlen($_POST['fax'])<3) {
        //si fue menor a tres letras se llena la variable de error y muestra mensaje
        $InputError = 1;
        echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close"
            data-dismiss="alert">&times;</button><p> 
            El fax debe ser de al menos 3 caracteres de longitud
            </p>
          </div>';
    } elseif (isset($_POST['email']) && strlen($_POST['email'])<3) {
        //si fue menor a tres letras se llena la variable de error y muestra mensaje
        $InputError = 1;
        echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close"
            data-dismiss="alert">&times;</button><p> 
            El email debe ser de al menos 3 caracteres de longitud
            </p>
          </div>';
    }
    $ultimaid=$u_tipovehiculo;
    unset($sql);
    //aqui empieza a realizar las acciones que corresponda de acuerdo al boton o link seleccionado
    if (isset($_POST['modificar'])and ($InputError != 1)) {
        $sql = "UPDATE legalbusinessunit SET legalname='" .$_POST['nom_tipovehiculo']. "' ,taxid='" .$_POST['rfc']. "' ,
		address1='" .$_POST['add1']."', address2='" .$_POST['add2']."',
		address3='" .$_POST['add3']."', address4='" .$_POST['add4']."', address5='" .$_POST['add5']."',
		telephone='" .$_POST['tel']."', regimenpatronal='".$_POST['regimen']."', c_RegimenFiscal='".$_POST['cmbRegimenFiscal']."', fax='" .$_POST['fax']."', email='" .$_POST['email']."'
		where legalid=".$u_tipovehiculo;
        $ErrMsg = _('La actualización del tipo de vehículo fracaso porque');
        echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close"
            data-dismiss="alert">&times;</button><p> 
            La Dependencia '.$_POST['nom_tipovehiculo'].' se ha actualizado
            </p>
          </div>';
    } elseif (isset($_GET['borrar'])and ($InputError != 1)) {
        //aqui si no hubo registros guardados borra la zona de la tabla
        $sql="DELETE FROM legalbusinessunit WHERE legalid=" . $_GET['u_tipovehiculo'];
        echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close"
            data-dismiss="alert">&times;</button><p> 
            La Dependencia a sido eliminado
            </p>
          </div>';
    } elseif (isset($_POST['enviar'])and ($InputError != 1)) {
        //aqui verifica que no exista otro tipo de vehiculo con el mismo nombre
        //si existe le manda mensaje de error y no lo deja insertar
        $sql= "select count(*) from legalbusinessunit where legalname='".$_POST['nom_tipovehiculo']."'";
        $result = DB_query($sql, $db);
        $myrow = DB_fetch_row($result);
        if ($myrow[0]>0) {
            echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close"
                data-dismiss="alert">&times;</button><p> 
                No se da de alta la Dependencia por que ya hay un registro guardado
                </p>
              </div>';
        } else {
            $InputNuevo=1;
            //si no da de alta el registro
            $sql = "INSERT INTO legalbusinessunit (legalname,taxid,address1,address2,address3,address4,address5,telephone,fax,email, regimenpatronal, c_RegimenFiscal)
			VALUES ('".$_POST['nom_tipovehiculo']."','".$_POST['rfc']."','".$_POST['add1']."','".$_POST['add2']."',
			'".$_POST['add3']."','".$_POST['add4']."','".$_POST['add5']."','".$_POST['tel']."','".$_POST['fax']."','".$_POST['email']."', '".$_POST['regimen']."', '".$_POST['cmbRegimenFiscal']."')";
            $ErrMsg = _('La inserción de la Dependencia fracaso porque');
            echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close"      
                data-dismiss="alert">&times;</button><p> 
                La Dependencia '.$_POST['nom_tipovehiculo'].' se ha creado
                </p>
              </div>';
        }
    }
//aqui se inicializan las variables en vacio
    if ($InputError != 1) {
        unset($_POST['nom_tipovehiculo']);
        unset($_POST['rfc']);
        unset($_POST['add1']);
        unset($_POST['add2']);
        unset($_POST['add3']);
        unset($_POST['add4']);
        unset($_POST['add5']);
        unset($_POST['tel']);
        unset($_POST['fax']);
        unset($_POST['email']);
        unset($_POST['lb_debtorno']);
        unset($_POST['lb_supplierno']);
        unset($_POST['u_tipovehiculo']);
        unset($_POST['regimen']);
        unset($u_tipovehiculo);
    }
}


if (isset($sql) && $InputError != 1) {
    $result = DB_query($sql, $db, $ErrMsg);
// DA DE ALTA ARCHIVOS 
    if ($tamano_archivo > 2097152 && $tamano_archivo > 2097152) {
        echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close"
                data-dismiss="alert">&times;</button><p> 
                El tamaño de los archivos no es correcta: <br> Se permiten archivos de 2 MB máximo
                </p>
              </div>';
    }
    $ruta = "images/";
    $filename = $ruta.$nombre_archivo;
    $smallfilename = $ruta.''.$nombre_archivo;
    $smallfilename2 = $ruta.''.$nombre_archivo2;
    $first = true;
    $doctoIDOrig="";
    if ($InputNuevo==1) {
        $ultimaid = DB_Last_Insert_ID($db, 'legalbusinessunit', 'legalid');
    }
    if (move_uploaded_file($_FILES['ItemPicture']['tmp_name'], $smallfilename)) {
        prnMsg(_('Los archivos an sido cargado correctamente ') . '!', 'info');
        $sql="UPDATE legalbusinessunit
			  SET logo='".$smallfilename."'
			  WHERE legalid=".$ultimaid;
        $result = DB_query($sql, $db, $ErrMsg);
    }
    if (move_uploaded_file($_FILES['ItemPictureAlterna']['tmp_name'], $smallfilename2)) {
        prnMsg(_('Los archivos an sido cargado correctamente ') . '!', 'info');
        $sql="UPDATE legalbusinessunit
			  SET logoAlterno='".$smallfilename2."'
			  WHERE legalid=".$ultimaid;
        $result = DB_query($sql, $db, $ErrMsg);
    }
}
if (isset($_POST['Go1'])) {
    $Offset = $_POST['Offset1'];
    $_POST['Go1'] = '';
}

if (!isset($_POST['Offset'])) {
    $_POST['Offset'] = 0;
} else {
    if ($_POST['Offset']==0) {
        $_POST['Offset'] = 0;
    }
}

if (isset($_POST['Next'])) {
    $Offset = $_POST['nextlist'];
}

if (isset($_POST['Prev'])) {
    $Offset = $_POST['previous'];
}

echo "<form method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . " enctype=multipart/form-data>";

if (!isset($u_tipovehiculo)) {
    ?>
        <div class="panel-body">
            <div class="col-md-4 col-xs-12">
                <component-text-label label="Dependencia:" id="edonom" name="edonom" 
                    placeholder="Dependencia" title="Dependencia" 
                    value="<?php echo $edonom; ?>"></component-text-label>
                <br>
                <component-text-label label="Teléfono:" id="edotel" name="edotel" 
                    placeholder="Teléfono" title="Teléfono" 
                    value="<?php echo $edotel; ?>"></component-text-label>
            </div>
            <div class="col-md-4 col-xs-12">
                <component-text-label label="Rfc:" id="edorfc" name="edorfc" 
                    placeholder="Rfc" title="Rfc" 
                    value="<?php echo $edorfc; ?>"></component-text-label>
                <br>
                <component-text-label label="Fax:" id="edofax" name="edofax" 
                    placeholder="Fax" title="Fax" 
                    value="<?php echo $edofax; ?>"></component-text-label>
            </div>
            <div class="col-md-4 col-xs-12">
                <component-text-label label="Dirección:" id="edoadd" name="edoadd" 
                    placeholder="Dirección" title="Dirección" 
                    value="<?php echo $edoadd; ?>"></component-text-label>
                <br>
                <component-text-label label="Email:" id="edoemail" name="edoemail" 
                    placeholder="Email" title="Email" 
                    value="<?php echo $edoemail; ?>"></component-text-label>
            </div>
            <div class="col-md-12 col-xs-12" align="center">
                <component-button type="submit" id="buscar" name="buscar" value="Buscar" class="glyphicon glyphicon-search"></component-button>
            </div>
        </div> 
    <?php
    
    echo "<div class='centre'><hr width=50%></div><br>";
    if ($Offset==0) {
        $numfuncion=1;
    } else {
        $numfuncion=$num_reg*$Offset+1;
    }
    $Offsetpagina=1;
//esta parte sirve para mostrar la primera tabla con todos los registros existentes
    $sql = "select l.legalid,
		l.legalname,
		l.taxid,
		l.address1,
		l.address2,
		l.address3,
		l.address4,
		l.address5,
		l.telephone,
		l.fax,
		l.email,
		lb_debtorno,
		lb_supplierno,
        l.regimenpatronal,
        l.c_RegimenFiscal
		from legalbusinessunit as l
		where legalid <>'' ";
    if (strlen($edonom)>=1) {
        $sql=$sql.' and legalname like "%'.$edonom.'%"  ';
    }
    if (strlen($edorfc)>=1) {
        $sql=$sql.' and taxid like "%'.trim($edorfc).'%"  ';
    }
    if (strlen($edoadd)>=1) {
        $sql=$sql.' and (address1 like "%'.$edoadd.'%"  or address2 like "%'.$edoadd.'%"
	or address3 like "%'.$edoadd.'%" or address4 like "%'.$edoadd.'%"
	or address5 like "%'.$edoadd.'%") ';
    }
    if (strlen($edotel)>=1) {
        $sql=$sql.' and l.telephone like "%'.$edotel.'%"  ';
    }
    if (strlen($edofax)>=1) {
        $sql=$sql.' and l.fax like "%'.$edofax.'%"   ';
    }
    if (strlen($edoemail)>=1) {
        $sql=$sql.' and l.email like "%'.$edoemail.'%"  ';
    }
    $sql=$sql.' order by '.$ordenpri;
    $result = DB_query($sql, $db);
    $ListCount=DB_num_rows($result);
    $ListPageMax=ceil($ListCount/$num_reg);
    
    $sql = "SELECT l.legalid,
    l.legalname,
    l.taxid,
    l.address1,
    l.address2,
    l.address3,
    l.address4,
    l.address5,
    l.telephone,
    l.fax,
    l.email,
    l.lb_debtorno,
    l.lb_supplierno,
    l.logo,
    l.logoAlterno,
    l.regimenpatronal,
    l.c_RegimenFiscal,
    sat_regimenfiscal.descripcion
    FROM legalbusinessunit as l
    JOIN sat_regimenfiscal ON sat_regimenfiscal.c_RegimenFiscal = l.c_RegimenFiscal
    WHERE l.legalid <>'' ";
    if (strlen($edonom)>=1) {
        $sql=$sql.' and legalname like "%'.$edonom.'%"  ';
    }
    if (strlen($edorfc)>=1) {
        $sql=$sql.' and taxid like "%'.trim($edorfc).'%"  ';
    }
    if (strlen($edoadd)>=1) {
        $sql=$sql.' and (address1 like "%'.$edoadd.'%"  or address2 like "%'.$edoadd.'%"
	or address3 like "%'.$edoadd.'%" or address4 like "%'.$edoadd.'%"
	or address5 like "%'.$edoadd.'%") ';
    }
    if (strlen($edotel)>=1) {
        $sql=$sql.' and l.telephone like "%'.$edotel.'%"  ';
    }
    if (strlen($edofax)>=1) {
        $sql=$sql.' and l.fax like "%'.$edofax.'%"   ';
    }
    if (strlen($edoemail)>=1) {
        $sql=$sql.' and l.email like "%'.$edoemail.'%"  ';
    }
    $sql=$sql.' order by '.$ordenpri;
    $sql = $sql . " LIMIT ".$num_reg." OFFSET ". ($Offset * $num_reg) ;
    
    $result = DB_query($sql, $db);
    if (!isset($u_tipovehiculo)) {
        //echo "<div class='centre'>" ._('Listado de Dependencias'). "</div>";
        echo '<table width=50%>';
        echo '	<tr>';
        if ($ListPageMax >1) {
            if ($Offset==0) {
                $Offsetpagina=1;
            } else {
                $Offsetpagina=$Offset+1;
            }
            echo '<td>'.$Offsetpagina. ' ' . _('de') . ' ' . $ListPageMax . ' ' . _('Paginas') . '. ' . _('Ir a la Pagina') . ':';
            echo '<select name="Offset1">';
                $ListPage=0;
            while ($ListPage < $ListPageMax) {
                if ($ListPage == $Offset) {
                    echo '<option VALUE=' . $ListPage . ' selected>' . ($ListPage+1) . '</option>';
                } else {
                    echo '<option VALUE=' . $ListPage . '>' . ($ListPage+1) . '</option>';
                }
                $ListPage++;
                $Offsetpagina=$Offsetpagina+1;
            }
            echo '</select></td>
			<td><input type="text" name="num_reg" size=1 value="' .$num_reg. '"></td>
			<td>
				<input type=submit name="Go1" VALUE="' . _('Ir') . '">
			</td>
			<td align=center cellpadding=3 >
				<input type="hidden" name="previous" value='.number_format($Offset-1).'>
				<input tabindex='.number_format($j+7).' type="submit" name="Prev" value="'._('Anterior').'">
			</td>
			<td style="text-align:right">
				<input type="hidden" name="nextlist" value='.number_format($Offset+1).'>
				<input tabindex='.number_format($j+9).' type="submit" name="Next" value="'._('Siguiente').'">
			</td>';
        }
        echo'</tr>
		</table>';
    }
    echo '<div style="width: 100%; overflow-x:scroll;overflow-y:scroll;">';
    echo '<table class="table table-bordered" border=1 width=70%>';
    echo "<tr class='header-verde'><th>" . _('No') . "</th>
	<th nowrap>" . _('Dependencia') . "</th>
	<th nowrap>" . _('RFC') . "</th>
	<th nowrap>" . _('Calle'
    . ''
    . '') . "</th>
	<th nowrap>" . _('Colonia') . "</th>
	<th nowrap>" . _('Ciudad') . "</th>
	<th nowrap>" . _('Estado') . "</th>
	<th nowrap>" . _('Código Postal') . "</th>
    <th nowrap>" . _('Registro Patronal') . "</th>
    <th nowrap>" . _('Regimen Fiscal') . "</th>
	<th nowrap>" . _('Teléfono') . "</th>
	<th nowrap>" . _('Fax') . "</th>
	<th nowrap>" . _('Email') . "</th>
	<th nowrap>" . _('Logo') . "</th>
	<th nowrap>" . _('Logo Alterno') . "</th>
	<th>"._('Acciones')."</th>
	<th>"._('Eliminar')."</th></tr>";
    $k=0; //row colour counter

    while ($myrow = DB_fetch_array($result)) {
        if ($k==1) {
            echo '<tr class="EvenTableRows">';
            $k=0;
        } else {
            echo '<tr class="OddTableRows">';
            $k=1;
        }
        
        if (!empty($myrow['logo'])) {
            $logo1="<td width='20' height='20'><img src='%s' width='30' height='20' /></td>";
        } else {
            $logo1="<td width='20' height='20'><img  width='0' height='0' />%s</td>";
        }
        if (!empty($myrow['logoAlterno'])) {
            $logo2="<td width='20' height='20'><img src='%s' width='30' height='20' /></td>";
        } else {
            $logo2="<td width='20' height='20'><img  width='0' height='0' />%s</td>";
        }
        
        $enc = new Encryption;
        $url = "&u_tipovehiculo=>" . $myrow['legalid'];
        $url = $enc->encode($url);
        $ligaMod= "URL=" . $url;

        $enc = new Encryption;
        $url = "&u_tipovehiculo=>" . $myrow['legalid'] . "&borrar=>1&nom_tipovehiculo=>" . urlencode($myrow['legalname']);
        $url = $enc->encode($url);
        $ligaEli= "URL=" . $url;

        printf(
            "<td>%s</td> 
		        <td nowrap>%s</td>
                <td nowrap>%s</td>
		        <td>%s</td>
                <td>%s</td>
				<td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
				<td>%s</td>
                <td nowrap>%s</td>
				<td nowrap>%s</td> 
                <td nowrap>%s</td>
					$logo1
          $logo2
			<td><a href=\"%s?%s\">Modificar</a></td>
			<td><a href=\"%s?%s\">" . _('Borrar') . "</a></td>
			</tr>",
            $numfuncion,
            ucwords(strtolower($myrow['legalname'])),
            $myrow['taxid'],
            ucwords(strtolower($myrow['address1'])),
            ucwords(strtolower($myrow['address2'])),
            ucwords(strtolower($myrow['address3'])),
            ucwords(strtolower($myrow['address4'])),
            ucwords(strtolower($myrow['address5'])),
            ucwords(strtolower($myrow['regimenpatronal'])),
            ($myrow['c_RegimenFiscal'] . " - " . $myrow['descripcion']),
            $myrow['telephone'],
            $myrow['fax'],
            $myrow['email'],
            $myrow['logo'],
            $myrow['logoAlterno'],
            $_SERVER['PHP_SELF'],
            $ligaMod,
            $_SERVER['PHP_SELF'],
            $ligaEli
        );
        $numfuncion=$numfuncion+1;
    }
    echo '</table>';
    echo '</div>';
}

if (isset($u_tipovehiculo)) {
    echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Dependencias existentes') . '</a></div>';
}

//esta parte sirve para mostrar los datos del registro seleccionado para modificar
if (isset($u_tipovehiculo)) {
    $sql = "SELECT *
		FROM legalbusinessunit
		WHERE legalid=". $u_tipovehiculo ;
        
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        prnMsg(_('No hay registros.'), 'warn');
    } else {
        $myrow = DB_fetch_array($result);
        $_POST['u_tipovehiculo'] = $myrow['legalid'];
        $_POST['nom_tipovehiculo'] =ucwords(strtolower($myrow['legalname']));
        $_POST['rfc'] = $myrow['taxid'];
        $_POST['add1'] = ucwords(strtolower($myrow['address1']));
        $_POST['add2'] = ucwords(strtolower($myrow['address2']));
        $_POST['add3'] = ucwords(strtolower($myrow['address3']));
        $_POST['add4'] = ucwords(strtolower($myrow['address4']));
        $_POST['add5'] = ucwords(strtolower($myrow['address5']));
        $_POST['regimen'] = ucwords(strtolower($myrow['regimenpatronal']));
        $_POST['cmbRegimenFiscal'] = $myrow['c_RegimenFiscal'];
        $_POST['tel'] = $myrow['telephone'];
        $_POST['fax'] = $myrow['fax'];
        $_POST['email'] = $myrow['email'];
        $_POST['lb_debtorno'] = $myrow['lb_debtorno'];
        $_POST['lb_supplierno'] = $myrow['lb_supplierno'];
    }
}
echo '<br>';
if (isset($_POST['u_tipovehiculo'])) {
    echo "<input type=hidden name='u_tipovehiculo' VALUE='" . $_POST['u_tipovehiculo'] . "'>";
}
//echo "<div class='centre'><hr width=60%>" ._('Alta/Modificación de Dependencias'). "</div><br>";
?>
<div align="left">
  <!--Panel Busqueda-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne" style="margin-left: 20px;">
            Información Agregar/Modificar
          </a>
      </h4>
    </div>
    <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
        <div class="col-md-4 col-xs-12">
            <component-text-label label="Dependencia:" id="nom_tipovehiculo" name="nom_tipovehiculo" 
                    placeholder="Dependencia" title="Dependencia" 
                    value="<?php echo $_POST['nom_tipovehiculo']; ?>"></component-text-label>
            <br>
            <component-text-label label="Rfc:" id="rfc" name="rfc" 
                    placeholder="Rfc" title="Rfc" 
                    value="<?php echo $_POST['rfc']; ?>"></component-text-label>
            <br>
            <component-text-label label="Código Postal:" id="add5" name="add5" 
                    placeholder="Código Postal" title="Código Postal" 
                    value="<?php echo $_POST['add5']; ?>"></component-text-label>
            <br>
            <component-text-label label="Fax:" id="fax" name="fax" 
                    placeholder="Fax" title="Fax" 
                    value="<?php echo $_POST['fax']; ?>"></component-text-label>
            <!-- <br> -->
            <component-text-label label="Proveedor:" id="lb_supplierno" name="lb_supplierno" 
                    placeholder="Proveedor" title="Proveedor" 
                    value="<?php echo $_POST['lb_supplierno']; ?>" style="display: none;"></component-text-label>
        </div>
        <div class="col-md-4 col-xs-12">
            <component-text-label label="Calle:" id="add1" name="add1" 
                    placeholder="Calle" title="Calle" 
                    value="<?php echo $_POST['add1']; ?>"></component-text-label>
            <br>
            <component-text-label label="Colonia:" id="add2" name="add2" 
                    placeholder="Colonia" title="Colonia" 
                    value="<?php echo $_POST['add2']; ?>"></component-text-label>
            <br>
            <component-text-label label="Registro Patronal:" id="regimen" name="regimen" 
                    placeholder="Régimen" title="Régimen" 
                    value="<?php echo $_POST['regimen']; ?>"></component-text-label>
            <br>
            <component-text-label label="Email:" id="email" name="email" 
                    placeholder="Email" title="Email" 
                    value="<?php echo $_POST['email']; ?>"></component-text-label>
            <!-- <br>
            <div class="form-inline row">
                <div class="col-md-3">
                  <span><label>Logo: </label></span>
                </div>
                <div class="col-md-9">
                    <input type='file' id='ItemPicture' name='ItemPicture'>
                </div>
            </div> -->
        </div>
        <div class="col-md-4 col-xs-12">
            <component-text-label label="Ciudad:" id="add3" name="add3" 
                    placeholder="Ciudad" title="Ciudad" 
                    value="<?php echo $_POST['add3']; ?>"></component-text-label>
            <br>
            <component-text-label label="Estado:" id="add4" name="add4" 
                    placeholder="Estado" title="Estado" 
                    value="<?php echo $_POST['add4']; ?>"></component-text-label>
            <br>
            <component-text-label label="Teléfono:" id="tel" name="tel" 
                    placeholder="Teléfono" title="Teléfono" 
                    value="<?php echo $_POST['tel']; ?>"></component-text-label>
            <!-- <br> -->
            <component-text-label label="Cliente:" id="lb_debtorno" name="lb_debtorno" 
                    placeholder="Cliente" title="Cliente" 
                    value="<?php echo $_POST['lb_debtorno']; ?>" style="display: none;"></component-text-label>
            <br>
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Regimen Fiscal: </label></span>
                    </div>
                    
                    <div class="col-md-9">
                    <?PHP

                    echo "<select name='cmbRegimenFiscal' id='cmbRegimenFiscal' class='form-control selectGeneral'>";
                    echo "<option value='0'>Seleccionar...</option>";
                    $sql = "SELECT c_RegimenFiscal, descripcion FROM sat_regimenfiscal WHERE active = 1 ORDER BY descripcion asc";
                    $result = DB_query($sql, $db);
                    while ( $myrow = DB_fetch_array ( $result ) ) {
                        if ($myrow ['c_RegimenFiscal'] == $_POST['cmbRegimenFiscal']) {
                            echo "<option selected value='" . $myrow ['c_RegimenFiscal'] . "'>" . $myrow ['descripcion'] . "</option>";
                        } else {
                            echo "<option value='" . $myrow ['c_RegimenFiscal'] . "'>" . $myrow ['c_RegimenFiscal'] . " - " . $myrow ['descripcion'] . "</option>";
                        }
                    }
                    echo "</select>";
               
                ?>
                    </div>
            </div>
            <!-- <br>
            <div class="form-inline row">
                <div class="col-md-3">
                  <span><label>Logo Alterno: </label></span>
                </div>
                <div class="col-md-9">
                    <input type='file' id='ItemPicture' name='ItemPictureAlterna'>
                </div>
            </div> -->
        </div>
        <div class="col-md-6 col-xs-12">
            <br>
            <div class="form-inline row">
                <div class="col-md-3">
                  <span><label>Logo: </label></span>
                </div>
                <div class="col-md-9">
                    <input type='file' id='ItemPicture' name='ItemPicture'>
                </div>
            </div>
            <br>
            <div class="form-inline row">
                <div class="col-md-3">
                  <span><label>Logo Alterno: </label></span>
                </div>
                <div class="col-md-9">
                    <input type='file' id='ItemPicture' name='ItemPictureAlterna'>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
    //aqui verifica si esta vacia la variable si lo esta el valor de la accion sera enviar
if (!isset($u_tipovehiculo)) {
    //echo "<div class='centre'><input type='Submit' name='enviar' value='" . _('Enviar') . "'></div>";
    ?>
        <div class="col-md-12 col-xs-12" align="center">
            <component-button type="submit" id="enviar" name="enviar" value="Guardar" class="glyphicon glyphicon-floppy-disk"></component-button>
        </div>
    <?php
} //aqui verifica que este llena la variable si lo esta el valor de la accion sera modificar
elseif (isset($u_tipovehiculo)) {
    //echo "<div class='centre'><input type='Submit' name='modificar' value='" . _('Actualizar') . "'></div>";
    ?>
        <div class="col-md-12 col-xs-12" align="center">
            <component-button type="submit" id="modificar" name="modificar" value="Guardar" class="glyphicon glyphicon-floppy-disk"></component-button>
        </div>
    <?php
}
echo '</form>';
include 'includes/footer_Index.inc';
