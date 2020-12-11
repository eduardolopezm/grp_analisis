<?php

include('includes/session.inc');
$title = _('INSERTA CUENTAS CONTABLES EN CHARTDETAILS');
include('includes/header.inc');
$funcion = 901;
include('includes/SecurityFunctions.inc');

//include('includes/SecurityFunctions.inc');***

if (isset($_POST['insertar'])) {
    $legalid = $_POST['legalid'];

    $result=DB_query("SELECT tagref FROM tags WHERE legalid in ('" . $legalid . "')", $db);

    if (DB_num_rows($result)==0) {
        prnMsg('<br><br>' . _('NO existen unidades de negocio para esta razon social'), 'error');
        include('includes/footer.inc');
        exit;
    } else {
        while ($myrow = DB_fetch_array($result)) {
            $isql = "INSERT INTO chartdetails
					SELECT accountcode, periodno, '" . $myrow['tagref'] . "' as tagref, 0 as budget, 
						0 as actual, 0 as bfwd, 0 as bfwdbudget, 0 as cargos, 0 as abonos 
					FROM chartmasterxlegal
						CROSS JOIN periods 
					WHERE chartmasterxlegal.legalid = '" . $legalid . "'
						AND (accountcode, periodno) NOT IN (
								SELECT accountcode, period 
								FROM chartdetails 
									INNER JOIN tags ON chartdetails.tagref = tags.tagref and tags.legalid = '" . $legalid . "'
								WHERE chartdetails.tagref = '" . $myrow['tagref'] . "'
							)";
            $iresult = DB_query($isql, $db);
            echo "<br>Actualiza la unidad de Negocio: " . $myrow['tagref'];
        }
    }
}


echo '<form method="POST" action="' . $_SERVER ['PHP_SELF'] . '?' . SID . '">';
    echo '<table>';
        echo '<tr>';
            echo '<td>' . _('Seleccione Una Razon Social:') . '</td>';
            echo '<td>';
                echo '<select name="legalid">';
       //              $SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname
							// FROM sec_unegsxuser u,tags t 
							// 	JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid
							// WHERE u.tagref = t.tagref 
							// 	and u.userid = '" . $_SESSION ['UserID'] . "'
				  	// 		GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.tagref";
                    $SQL = "SELECT distinct legalbusinessunit.legalid,legalbusinessunit.legalname
                    FROM sec_unegsxuser u,tags t 
                    JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid
                    WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION ['UserID'] . "'
                    -- GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname 
                    ORDER BY legalbusinessunit.legalid";
                    $result = DB_query($SQL, $db);
while ($myrow = DB_fetch_array($result)) {
    if (isset($_POST ['legalid']) and $_POST ['legalid'] == $myrow ["legalid"]) {
        echo '<option selected value=' . $myrow ['legalid'] . '>' . $myrow ['legalid'] . ' - ' . $myrow ['legalname'];
    } else {
        echo '<option value=' . $myrow ['legalid'] . '>' . $myrow ['legalid'] . ' - ' . $myrow ['legalname'];
    }
}
                echo '</select>';
            echo '</td>';
        echo '</tr>';
        echo '<tr>';
            echo '<td colspan="2" style="text-align:center">';
                echo '<input type="submit" Name="insertar" Value="' . _('Insertar') . '">';
            echo '</td>';
        echo '</tr>';
    echo '</table>';
echo '</form>';
