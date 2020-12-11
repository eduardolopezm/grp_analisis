<?php
/**
 * Género
 *
 * @category Configuración
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 20/09/2017
 * Fecha Modificación: 20/09/2017
 * Configuración de Género
 */

$PageSecurity = 10;
include('includes/session.inc');
$title = _('Género');
$funcion=132;
include('includes/header.inc');
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

function CheckForRecursiveGroup($ParentGroupName, $GroupName, $db)
{
    /* returns true ie 1 if the group contains the parent group as a child group
	ie the parent group results in a recursive group structure otherwise false ie 0 */
    $ErrMsg = _('Se produjo un error en la recuperación del grupo de cuentas durante la verificación de la recursividad');
    $DbgMsg = _('El SQL que se utiliza para recuperar los grupos de cuentas  fracasó en el proceso fue');
    
    do {
        $sql = "SELECT parentgroupname FROM accountgroups WHERE groupname='" . $GroupName ."'";
        
        $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
        $myrow = DB_fetch_row($result);
        if ($ParentGroupName == $myrow[0]) {
            return true;
        }
        $GroupName = $myrow[0];
    } while ($myrow[0]!='');
    return false;
} //end of function CheckForRecursiveGroupName

// If $Errors is set, then unset it.
if (isset($Errors)) {
    unset($Errors);
}
    
$Errors = array();

if (isset($_POST['submit'])) {
    //initialise no input errors assumed initially before we test

    $InputError = 0;

    /* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

    //first off validate inputs sensible
    $i=1;
    
    $sql="SELECT count(groupname) 
			FROM accountgroups WHERE groupname='".$_POST['GroupName']."'";

    $DbgMsg = _('El SQL que se utiliza para recuperar la informaci�n fue');
    $ErrMsg = _('No se puede comprobar si el Género existe porque');

    $result=DB_query($sql, $db, $ErrMsg, $DbgMsg);
    $myrow=DB_fetch_row($result);

    if ($myrow[0]!=0 and $_POST['SelectedAccountGroup']=='') {
        $InputError = 1;
        prnMsg(_('El nombre del género de la cuenta ya existe en la base de datos'), 'error');
        $Errors[$i] = 'GroupName';
        $i++;
    }
    if (ContainsIllegalCharacters($_POST['GroupName'])) {
        $InputError = 1;
        prnMsg(_('El nombre del género cuenta no puede contener el carácter') . " '&' " . _('o el carácter') ." '", 'error');
        $Errors[$i] = 'GroupName';
        $i++;
    }
    if (strlen($_POST['GroupName'])==0) {
        $InputError = 1;
        prnMsg(_('El nombre de género de cuenta debe ser al menos de un  carácter de longitud'), 'error');
        $Errors[$i] = 'GroupName';
        $i++;
    }
    if ($_POST['ParentGroupName'] !='') {
        if (CheckForRecursiveGroup($_POST['GroupName'], $_POST['ParentGroupName'], $db)) {
            $InputError =1;
            prnMsg(_('El género cuenta matriz seleccionada parece ser el resultado de una estructura de cuentas recursivo - seleccionar un género de cuentas matriz alternativa o hacen de este género un género de cuentas de nivel superior'), 'error');
            $Errors[$i] = 'ParentGroupName';
            $i++;
        } else {
            $sql = "SELECT pandl, 
				sequenceintb, 
				sectioninaccounts,
				groupcodetb 
			FROM accountgroups 
			WHERE groupname='" . $_POST['ParentGroupName'] . "'";
            
            $DbgMsg = _('El SQL que se utiliza para recuperar la informaci�n fue');
            $ErrMsg = _('No se puede comprobar si el género es recursiva, porque');

            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

            $ParentGroupRow = DB_fetch_array($result);
            $_POST['SequenceInTB'] = $ParentGroupRow['sequenceintb'];
            $_POST['PandL'] = $ParentGroupRow['pandl'];
            $_POST['SectionInAccounts']= $ParentGroupRow['sectioninaccounts'];
            //$_POST['AgrupaInTB'] = $ParentGroupRow['groupcodetb'];
        }
    }
    if (!is_long((int) $_POST['SectionInAccounts'])) {
        $InputError = 1;
        prnMsg(_('La sección de cuentas debe ser un entero'), 'error');
        $Errors[$i] = 'SectionInAccounts';
        $i++;
    }
    if (!is_long((int) $_POST['SequenceInTB'])) {
        $InputError = 1;
        prnMsg(_('La secuencia en la balanza de comprobación debe ser un entero'), 'error');
        $Errors[$i] = 'SequenceInTB';
        $i++;
    }
    if (!is_numeric($_POST['SequenceInTB']) or $_POST['SequenceInTB'] > 10000) {
        $InputError = 1;
        prnMsg(_('La secuencia en la TB debe ser numérico y menor que') . ' 10,000', 'error');
        $Errors[$i] = 'SequenceInTB';
        $i++;
    }
    if (!isset($_POST['AgrupaInTB'])) {
        $InputError = 1;
        prnMsg(_('El código de agrupación de la TB debe ser introducido.') . ' ', 'error');
        $Errors[$i] = 'AgrupaInTB';
        $i++;
    }
    if ($_POST['SelectedAccountGroup']!='' and $InputError !=1) {
        //CONDICION PARA VALIDAR EL NIVEL DE CADA CUENTA
        if ($_POST['ParentGroupName']!='') {
            $sql = "SELECT level
			FROM accountgroups 
			WHERE groupname='" . $_POST['ParentGroupName'] . "'";
            
            $DbgMsg = _('El SQL que se utiliza para recuperar la información fue');
            $ErrMsg = _('No se puede comprobar si el género es recursiva, porque');

            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

            $ParentGroupRow = DB_fetch_array($result);
            $_POST['level'] = $ParentGroupRow['level']+1;
        } else {
            $_POST['level'] = 1;
        }
        //FIN DE IF, EL VALOR DE NIVEL YA TIENE ASIGANDO UN VALOR
        /*SelectedAccountGroup could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

        $sql = "UPDATE accountgroups
				SET groupname='" . $_POST['GroupName'] . "',
					groupnameing='" . $_POST['GroupNameing'] . "',
					sectioninaccounts=" . $_POST['SectionInAccounts'] . ",
					pandl=" . $_POST['PandL'] . ",
					sequenceintb=" . $_POST['SequenceInTB'] . ",
					parentgroupname='" . $_POST['ParentGroupName'] . "',
					level= '" . $_POST['level'] . "',
					groupcodetb= '".$_POST['AgrupaInTB']."'
					WHERE groupname = '" . $_POST['SelectedAccountGroup'] . "'";
        
        $ErrMsg = _('Se produjo un error en la actualización del género de cuentas');
        $DbgMsg = _('El SQL que se utiliz� para actualizar el género de cuentas se');

        $msg = _('Registro Actualizado');
    } elseif ($InputError !=1) {
    /*Selected group is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new account group form */
    //CONDICION PARA VALIDAR EL VALOR DE NIVEL
        if ($_POST['ParentGroupName']!='') {
            $sql = "SELECT level
			FROM accountgroups 
			WHERE groupname='" . $_POST['ParentGroupName'] . "'";
            
            $DbgMsg = _('El SQL que se utiliza para recuperar la información fue');
            $ErrMsg = _('No se puede comprobar si el género es recursiva, porque');

            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

            $ParentGroupRow = DB_fetch_array($result);
            $_POST['level'] = $ParentGroupRow['level']+1;
        } else {
            $_POST['level'] = 1;
        }
        
    //FIN DE VALIDACION, EL VALOR DE NIVEL YA TRE UN VALOR.
        
        $sql = "INSERT INTO accountgroups (
					groupname,
					groupnameing,
					sectioninaccounts,
					sequenceintb,
					pandl,
					parentgroupname,
					level,
					groupcodetb
					)
			VALUES (
				'" . $_POST['GroupName'] . "',
				'" . $_POST['GroupNameing'] . "',
				" . $_POST['SectionInAccounts'] . ",
				" . $_POST['SequenceInTB'] . ",
				" . $_POST['PandL'] . ",
				'" . $_POST['ParentGroupName'] . "',
				'" . $_POST['level'] . "',
				'" . $_POST['AgrupaInTB'] . "'
				)";
        $ErrMsg = _('Se produjo un error en la inserción del género de cuentas');
        $DbgMsg = _('El SQL que se utilizar� para introducir el género de cuentas se');
        $msg = _('Registro insertado');
    }

    if ($InputError!=1) {
        //run the SQL from either of the above possibilites
        $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
        prnMsg($msg, 'success');
        unset($_POST['SelectedAccountGroup']);
        unset($_POST['GroupName']);
        unset($_POST['GroupNameing']);
        unset($_POST['SequenceInTB']);
        unset($_POST['AgrupaInTB']);
    }
} elseif (isset($_GET['eliminar'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'ChartMaster'

    $sql= "SELECT COUNT(*) FROM chartmaster WHERE chartmaster.group_='" . $_GET['SelectedAccountGroup'] . "'";
    $ErrMsg = _('Se produjo un error en la recuperación de la información de género de chartmaster');
    $DbgMsg = _('El SQL que se utiliza para recuperar la información fue');
    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
    $myrow = DB_fetch_row($result);
    if ($myrow[0]>0) {
        prnMsg(_('No se puede eliminar este género de cuentas porque las cuentas del libro mayor general se han creado con este género'), 'warn');
        echo '<br />' . _('Hay') . ' ' . $myrow[0] . ' ' . _('cuentas del libro mayor que se refieren a este género de cuentas') . '</font>';
    } else {
        $sql = "SELECT COUNT(groupname) FROM accountgroups WHERE parentgroupname = '" . $_GET['SelectedAccountGroup'] . "'";
        $ErrMsg = _('Se ha producido un error al recuperar la información del género de padres');
        $DbgMsg = _('El SQL que se utiliza para recuperar la informacion es');
        $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
        $myrow = DB_fetch_row($result);
        if ($myrow[0]>0) {
            prnMsg(_('No se puede eliminar este género de cuentas, ya que es un género de cuentas de los padres de otro género de cuenta(s)'), 'warn');
            echo '<br />' . _('Hay') . ' ' . $myrow[0] . ' ' . _('géneros de cuentas que tienen este género como su/género de cuentas de los padres') . '</font>';
        } else {
            $sql="DELETE FROM accountgroups WHERE groupname='" . $_GET['SelectedAccountGroup'] . "'";
            $ErrMsg = _('Se ha producido un error en la eliminación de la cuenta de género');
            $DbgMsg = _('El SQL que se utiliza para eliminar el género de cuentas es');
            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
            prnMsg($_GET['SelectedAccountGroup'] . ' ' . _('Género ha sido Eliminado') . '!', 'success');
        }
    } //end if account group used in GL accounts
}

if (!isset($_GET['SelectedAccountGroup']) or !isset($_POST['SelectedAccountGroup'])) {
    echo '<table><form method="post" id="buscar" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';

    echo '<tr><td>' . _('Nombre del Género de Cuenta') . ':' . '</td>
        <td><input tabindex="1" ' . (in_array('GroupName', $Errors) ? 'class="inputerror"' : '' ) . ' 
            type="text" name="GroupName" size="50"  value="' . $_POST['GroupName'] . '" /></td></tr>';


    echo '<tr><td>' . _('Género Padre') . ':' . '</td>
<td><select style="width:350px" tabindex="2" ' . (in_array('ParentGroupName', $Errors) ? 'class="selecterror"' : '' ) .
    '  name="ParentGroupName">';
    $sql = 'SELECT groupname,groupcodetb FROM accountgroups order by groupcodetb,groupname';
    $groupresult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
    if (!isset($_POST['ParentGroupName'])) {
        echo '<option selected="selected" value="">' . _('Género de Nivel superior') . '</option>';
    } else {
        echo '<option value="">' . _('Género de Nivel Superior') . '</option>';
    }
    while ($grouprow = DB_fetch_array($groupresult)) {
        if (isset($_POST['ParentGroupName']) and $_POST['ParentGroupName'] == $grouprow['groupname']) {
            echo '<option selected="selected" value="' . $grouprow['groupname'] . '">' . $grouprow['groupcodetb'] .' - '. $grouprow['groupname'] . '</option>';
        } else {
            echo '<option value="' . $grouprow['groupname'] . '">' . $grouprow['groupcodetb'] .' - '. $grouprow['groupname'] . '</option>';
        }
    }
    echo '</select>';
    echo '</td></tr>';




    echo "<tr><td>" . _("Código Agrupación:") . "</td>";
    echo '<td><input tabindex="2" ' . (in_array('AgrupaInTB', $Errors) ? 'class="inputerror"' : '' ) .
    ' type="text"  name="AgrupaInTB" onkeypress="return " 
value="' . $_POST['AgrupaInTB'] . '" /></td></tr>';
    echo '<tr><td><input type="submit" name="Buscar" value="Buscar"></td></tr>';
    echo '</form></table>';


    //echo 'Buscar. ' . $_POST['GroupName'] . '-' . $_POST['ParentGroupName'] . '-' . $_POST['AgrupaInTB'];

    $and = '';
    if (isset($_POST['GroupName']) and $_POST['GroupName'] != '') {
        if ($and >= 1) {
            $filtro.=' AND groupname like "' . $_POST['GroupName'] . '%"';
        } else {
            $filtro.=' groupname like "' . $_POST['GroupName'] . '%"';
        }
        $and+=1;
    }
    if (isset($_POST['ParentGroupName']) and $_POST['ParentGroupName'] != '') {
        if ($and >= 1) {
            $filtro.='AND parentgroupname="' . $_POST['ParentGroupName'] . '"';
        } else {
            $filtro.=' parentgroupname="' . $_POST['ParentGroupName'] . '"';
        }
        $and+=1;
    }
    if (isset($_POST['AgrupaInTB']) and $_POST['AgrupaInTB'] != '') {
        if ($and >= 1) {
            $filtro.='AND groupcodetb like "' . $_POST['AgrupaInTB'] . '%"';
        } else {
            $filtro.=' groupcodetb like "' . $_POST['AgrupaInTB'] . '%"';
        }
        $and+=1;
    }
    if (isset($filtro)) {
        $filtros = 'WHERE ' . $filtro;
    }
    /* An account group could be posted when one has been edited and is being updated or GOT when selected for modification
 SelectedAccountGroup will exist because it was sent with the page in a GET .
 If its the first time the page has been displayed with no parameters
then none of the above are true and the list of account groups will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/


    $sql = "SELECT groupname,
			groupnameing,
			sectionname,
			sequenceintb,
			pandl,
			parentgroupname,
			groupcodetb
		FROM accountgroups 
		LEFT JOIN accountsection ON sectionid = sectioninaccounts
".$filtros."		
ORDER BY sequenceintb,groupcodetb";


    $DbgMsg = _('El sql que se utiliz� para recuperar la información de género de cuentas se ');
    $ErrMsg = _('No se pudo obtener de la cuenta porque los géneros');
    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
    echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Search') . '" alt="">' . ' ' . $title.'<br>';
                
    echo "<table> 
		<tr>
			<th>" . _('Lin.') . "</th>
			<th>" . _('Codigo Agrupación TB') . "</th>
			<th>" . _('Nombre del Género') . "</th>
			<th>" . _('Nombre del Género En') . "</th>
			<th>" . _('Sección') . "</th>
			<th>" . _('Sequencia en TB') . "</th>	
			<th>" . _('Perdidas y Ganancias') . "</th>
			<th>" . _('Género Padre') . "</th>
		</tr>";
        
       '<table> 
		<tr>
		<th>' . _('Nombre del Género') . "</th>
		<th>" . _('Nombre del Género En') . "</th>
		<th>" . _('Sección') . "</th>
		<th>" . _('Sequencia en TB') . "</th>
		<th>" . _('Código Agrupación TB') . "</th>		
		<th>" . _('Perdidas y Ganancias') . "</th>
		<th>" . _('Género Padre') . "</th>
		</tr>";

    $lin=0; //contador de linea
    $k=0; //row colour counter
    while ($myrow = DB_fetch_row($result)) {
        if ($k==1) {
            echo '<tr class="EvenTableRows">';
            $k=0;
        } else {
            echo '<tr class="OddTableRows">';
            $k++;
        }
        $lin++;

        switch ($myrow[4]) {
            case -1:
                $PandLText=_('Si');
                break;
            case 1:
                $PandLText=_('Si');
                break;
            case 0:
                $PandLText=_('No');
                break;
        } //end of switch statement
        
        $sql = 'SELECT groupname, groupcodetb FROM accountgroups WHERE groupname="'.$myrow[5].'"';
        $DbgMsg = _('El sql que se utilizó para recuperar la información de género Para ');
        $ErrMsg = _('No se pudo obtener de la cuenta porque los géneros');
        $result2 = DB_query($sql, $db, $ErrMsg, $DbgMsg);
        $myrow2 = DB_fetch_row($result2);
        $grupopadredesc=$myrow2[1].' - '.$myrow[5];
        echo '
			<td>' . $lin . '</td>
			<td>' . $myrow[6] . '</td>
			<td>' . $myrow[0] . '</td>
			<td>' . $myrow[1] . '</td>
			<td>' . $myrow[2] . '</td>
			<td>' . $myrow[3] . '</td>
			<td>' . $PandLText . '</td>
			<td>' . $grupopadredesc . '</td>';
        echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&amp;SelectedAccountGroup=' . $myrow[0] . '">' . _('Modificar') . '</a></td>';
        echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&amp;SelectedAccountGroup=' . $myrow[0] . '&amp;eliminar=1">' . _('Eliminar') .'</a></td></tr>';
    } //END WHILE LIST LOOP
    echo '</table>';
} //end of ifs and buts!


if (isset($_POST['SelectedAccountGroup']) or isset($_GET['SelectedAccountGroup'])) {
    echo '<div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '?' . SID .'">' . _('Mostrar Géneros') . '</a></div>';
}

if (!isset($_GET['eliminar'])) {
    echo '<form method="post" id="AccountGroups" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';

    if (isset($_GET['SelectedAccountGroup'])) {
        //editing an existing account group

        $sql = "SELECT groupname,
				groupnameing,
				sectioninaccounts,
				sequenceintb,
				pandl,
				parentgroupname,
				groupcodetb
			FROM accountgroups
			WHERE groupname='" . $_GET['SelectedAccountGroup'] ."'";

        $ErrMsg = _('Se produjo un error en la recuperación del género de información de la cuenta');
        $DbgMsg = _('The SQL that was used to retrieve the account group and that failed in the process was');
        $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
        $myrow = DB_fetch_array($result);

        $_POST['GroupName'] = $myrow['groupname'];
        $_POST['GroupNameing'] = $myrow['groupnameing'];
        $_POST['SectionInAccounts']  = $myrow['sectioninaccounts'];
        $_POST['SequenceInTB']  = $myrow['sequenceintb'];
        $_POST['PandL']  = $myrow['pandl'];
        $_POST['ParentGroupName'] = $myrow['parentgroupname'];
        $_POST['AgrupaInTB']  = $myrow['groupcodetb'];

        echo '<table><tr><td>';
        echo '<input type="hidden" name="SelectedAccountGroup" value="' . $_GET['SelectedAccountGroup'] . '" />';
        echo '<input type="hidden" name="GroupName" value="' . $_POST['GroupName'] . '" />';
        echo '<input type="hidden" name="GroupNameing" value="' . $_POST['GroupNameing'] . '" />';
        echo _('Género de Cuentas') . ':' . '</td>';

        echo '<td>' . $_POST['GroupName'] . '</td>';
        echo '<td>' . $_POST['GroupNameing'] . '</td></tr>';
    } else { //end of if $_POST['SelectedAccountGroup'] only do the else when a new record is being entered

        if (!isset($_POST['SelectedAccountGroup'])) {
            $_POST['SelectedAccountGroup']='';
        }
        if (!isset($_POST['GroupName'])) {
            $_POST['GroupName']='';
        }
        if (!isset($_POST['GroupNameing'])) {
            $_POST['GroupNameing']='';
        }
        if (!isset($_POST['SectionInAccounts'])) {
            $_POST['SectionInAccounts']='';
        }
        if (!isset($_POST['SequenceInTB'])) {
            $_POST['SequenceInTB']='';
        }
        if (!isset($_POST['PandL'])) {
            $_POST['PandL']='';
        }
        if (!isset($_POST['AgrupaInTB'])) {
            $_POST['AgrupaInTB']='';
        }

        echo '<table style="margin: 10px auto;"><tr><td>';
        echo '<input  type="hidden" name="SelectedAccountGroup" value="' . $_POST['SelectedAccountGroup'] . '" />';
        echo _('Nombre del Género de Cuenta') . ':' . '</td><td>
		<input tabindex="1" ' . (in_array('GroupName', $Errors) ?  'class="inputerror"' : '' ) .' type="text" name="GroupName" size="50"  value="' . $_POST['GroupName'] . '" /></td></tr><tr><td>';
        echo _('Nombre del Género de Cuenta En') . ':' . '</td><td>
		<input tabindex="2" ' . (in_array('GroupNameing', $Errors) ?  'class="inputerror"' : '' ) .' type="text" name="GroupNameing" size="50"  value="' . $_POST['GroupNameing'] . '" /></td></tr>';
    }
    echo '<tr><td>' . _('Género Padre') . ':' . '</td>
	<td><select style="width: 300px" tabindex="3" ' . (in_array('ParentGroupName', $Errors) ?  'class="selecterror"' : '' ) .
        '  name="ParentGroupName">';

    $sql = 'SELECT groupname,groupcodetb  FROM accountgroups order by groupcodetb,groupname';
    $groupresult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
    if (!isset($_POST['ParentGroupName'])) {
        echo '<option selected="selected" value="">' ._('Género de Nivel superior').'</option>';
    } else {
        echo '<option value="">' ._('Género de Nivel Superior').'</option>';
    }
    while ($grouprow = DB_fetch_array($groupresult)) {
        if (isset($_POST['ParentGroupName']) and $_POST['ParentGroupName']==$grouprow['groupname']) {
            echo '<option selected="selected" value="'.$grouprow['groupname'].'">' . $grouprow['groupcodetb'] .' - '.$grouprow['groupname'].'</option>';
        } else {
            echo '<option value="'.$grouprow['groupname'].'">' . $grouprow['groupcodetb'] .' - '.$grouprow['groupname'].'</option>';
        }
    }
    echo '</select>';
    echo '</td></tr>';

    echo '<tr><td>' . _('Sección en Cuentas') . ':' . '</td>
	<td><select style="width: 300px" tabindex="4" ' . (in_array('SectionInAccounts', $Errors) ?  'class="selecterror"' : '' ) .
      '  name="SectionInAccounts">';

    $sql = 'SELECT sectionid, sectionname FROM accountsection ORDER BY sectionid';
    $secresult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
    while ($secrow = DB_fetch_array($secresult)) {
        if ($_POST['SectionInAccounts']==$secrow['sectionid']) {
            echo '<option selected="selected" value="'.$secrow['sectionid'].'">'.$secrow['sectionname'].' ('.$secrow['sectionid'].')</option>';
        } else {
            echo '<option value="'.$secrow['sectionid'].'">'.$secrow['sectionname'].' ('.$secrow['sectionid'].')</option>';
        }
    }
    echo '</select>';
    echo '</td></tr>';
    
    echo '<tr><td>' . _('Ganancias y Perdidas') . ':' . '</td>
	<td><select tabindex="5" name="PandL">';

    if ($_POST['PandL']!=0) {
        echo '<option selected="selected" value="1">' . _('Si').'</option>';
    } else {
        echo '<option value="1">' . _('Si').'</option>';
    }
    if ($_POST['PandL']==0) {
        echo '<option selected="selected" value="0">' . _('No').'</option>';
    } else {
        echo '<option value="0">' . _('No').'</option>';
    }

    echo '</select></td></tr>';

    echo '<tr><td>' . _('Secuencia en TB') . ':' . '</td>';
    echo '<td><input tabindex="6" ' . (in_array('SequenceInTB', $Errors) ? 'class="inputerror"' : '' ) .
        ' type="text" maxlength="4" name="SequenceInTB" onkeypress="return restrictToNumbers(this, event)" 
		 value="' . $_POST['SequenceInTB'] . '" /></td></tr>';
    
    echo "<tr><td>"._("Código Agrupación:")."</td>";
    echo '<td><input tabindex="7" ' . (in_array('AgrupaInTB', $Errors) ? 'class="inputerror"' : '' ) .
        ' type="text"  name="AgrupaInTB" onkeypress="return " 
		 value="' . $_POST['AgrupaInTB'] . '" /></td></tr>';

    echo '</table>';

    echo '<div class="centre"><input tabindex="7" type="submit" name="submit" value="' . _('Ingresar Información') . '" /></div>';

    echo '<script  type="text/javascript">defaultControl(document.forms[0].GroupName);</script>';
    
    echo '</form>';
} //end if record deleted no point displaying form to add record
include 'includes/footer_Index.inc';
