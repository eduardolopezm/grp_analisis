
<?php

	if (!headers_sent()){
		header('Content-type: text/html; charset=' . _('iso-8859-1'));
	}

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

	echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
	echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
	echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=' . _('ISO-8859-1') . '" />';
	echo '<link href="' . $rootpath . '/css/'. $_SESSION['Theme'] . '/default.css" rel="stylesheet" type="text/css" />';
	echo '<script type="text/javascript" src = "' . $rootpath . '/javascripts/MiscFunctions.js"></script>';

	echo '<script language="JavaScript">';

	echo 'function NoRefrescar()';
	echo '{';
	echo 'var tecla=window.event.keyCode;';

	echo 'if (tecla==116)';
	echo '{';
	echo 'event.keyCode=0;';
	echo 'event.returnValue=false;';
	echo '}';
	echo '}';

	echo '</script>';

	echo '</head>';
	/* funcion para redireccionar*/

	if (isset($_POST['directfunctionid']) and false) {
	/* trae submodulos por funcion*/
		/*$sql = "SELECT  distinct F.submoduleid,F.shortdescription,F.url,F.functionid,F.type,
					F.categoryid,C.name,C.imagecategory,sm.shortabrev,
					case when (FP.functionid is null and FxU.functionid is null)
					then 0
					else case when FxU.permiso is null
					       then 1
					       else FxU.permiso end
					end as permiso
		       FROM sec_modules s,
			    sec_submodules sm,
			    www_users u,
			    sec_profilexuser PU,
			    sec_funxprofile FP,
			    sec_functions F left join sec_funxuser FxU on  FxU.functionid=F.functionid and FxU.userid='".$_SESSION['UserID']."',
			    sec_categories C
		       WHERE s.moduleid=sm.moduleid and s.active=1
			     and FP.profileid=PU.profileid
			     and F.submoduleid=sm.submoduleid
			     and C.categoryid=F.categoryid
			     and u.userid=PU.userid and PU.userid='".$_SESSION['UserID']."'
			     and s.moduleid=".$_SESSION['Module']."
			     and u.userid=PU.userid
			     and F.functionid=FP.functionid
			     and F.submoduleid=".$_SESSION['Submodulo']."
			     and LENGTH(F.url)>0 and F.functionid=".$_POST['directfunctionid'];
		*/

		$sql=" SELECT  1 as permiso,FuxP.submoduleid,FuxP.shortdescription,FuxP.url,FuxP.functionid,FuxP.type,
					FuxP.categoryid,C.name,C.imagecategory,sm.shortabrev
		    FROM sec_modules s, sec_submodules sm, www_users u,
			  sec_profilexuser PU, sec_funxprofile FP,
			  sec_functions FuxP, sec_categories C
		    WHERE s.moduleid=sm.moduleid and s.active=1
			  and FP.profileid=PU.profileid and FuxP.submoduleid=sm.submoduleid and C.categoryid=FuxP.categoryid
			  and u.userid=PU.userid and PU.userid='".$_SESSION['UserID']."'
		          and u.userid=PU.userid and FuxP.functionid=FP.functionid
			  and  FP.functionid=".$_POST['directfunctionid']."
			  and FuxP.active=1
			   and LENGTH(FuxP.url)>0
			  and FuxP.functionid not in (select funCtionid from sec_funxuser where userid='".$consultausuario."')
		   UNION
		   SELECT  PU.permiso as permiso,FuxP.submoduleid,FuxP.shortdescription,FuxP.url,FuxP.functionid,FuxP.type,
					FuxP.categoryid,C.name,C.imagecategory,sm.shortabrev
		   FROM sec_modules s, sec_submodules sm, www_users u,
		      sec_functions FuxP, sec_categories C, sec_funxuser PU
		   WHERE s.moduleid=sm.moduleid and s.active=1
		      and FuxP.submoduleid=sm.submoduleid and C.categoryid=FuxP.categoryid
		      and u.userid=PU.userid and PU.userid='".$_SESSION['UserID']."'
		      and u.userid=PU.userid and FuxP.functionid=PU.functionid
		      and FuxP.functionid=".$_POST['directfunctionid']."
		      and LENGTH(FuxP.url)>0
		      and FuxP.active=1";

	       $ReFuntion = DB_query($sql, $db);
	       if (DB_num_rows($ReFuntion)>0 ) {

		       $nombrecategoryant='';
		       while($myrowFuntion = DB_fetch_array($ReFuntion)) {
				$URL=$myrowFuntion['url'];
				$titulo=strtolower(($myrowFuntion['shortdescription']));
				$Funtionid=$myrowFuntion['functionid'];
				$nombrecategoria=strtolower($myrowFuntion['name']);
				$imagencategoria=$myrowFuntion['imagecategory'];
				$tienepermiso=$myrowFuntion['permiso'];
				$tipo=$myrowFuntion['type'];
				if ($tienepermiso==1) {
					echo '<meta http-equiv="Refresh" content="0;  url='. $URL .'">';
					echo "<a target='". $tipo ."' href='". $URL ."' ><li>&nbsp;&nbsp;".str_repeat('0',3-strlen($Funtionid)).$Funtionid."&nbsp;&nbsp;". ucwords($titulo) ."</li></a>";
					echo "<table><tr class='menu_group_item_button' ><td class='menu_group_item_button'><a href='". $URL ."'><li>&nbsp;&nbsp;".str_repeat('0',3-strlen($Funtionid)).$Funtionid."&nbsp;&nbsp;". ucwords($titulo) .'</li></a></td></tr></table>';
					exit;
				} else {
					echo "<table><tr class='menu_group_item_button' ><td class='menu_group_item_button'><li>No tiene permisos para accesar a esa funcion</li></a></td></tr></table>";
				}
			}
		} else {?>
			<script type="text/javascript">
				alert("La funcion seleccionada no existe o no tiene permisos para accesarla...");
			</script>
		<?
		}
	}




	#echo '<body onKeyDown="javascript:NoRefrescar()">';
	echo '<body>';
	echo '<table class="callout_main" cellpadding="0" cellspacing="0">';
		echo '<tr>';
		echo '<td colspan="2" rowspan="2">';

			echo '<table class="main_page" cellpadding="0" cellspacing="0">';
				echo '<tr>';
				echo '<td>';
					/* TABLA QUE REDONDEA DESDE MENU PRINCIPAL HASTA PARTE DE ABAJO DE MENU DETALLADO Y ANTES DEL FOOTER */
					echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" >';
					echo '<tr>';
					echo '<td>';

					if (isset($_SESSION['UserID'])) {

						echo '<table cellpadding="0" cellspacing="0" border="0" id="quick_menu" class="quick_menu">';
						echo '<tr>';
						echo '<td align="center" style="width:100%;" class="quick_menu_left">';
							echo '<table cellpadding="0" cellspacing="0" border="0"  class="quick_menu"><tr><td><table cellpadding="0" cellspacing="0" border="0"><tr align=left><td>';

							echo '<img src="'.$rootpath . '/' . $_SESSION['LogoFile'] . '" align="left" height="50" TITLE="' . _('EMPRESA') . ':" ALT="' . _('EMPRESA') . ':">';
							echo '</td></tr><tr><td>';
							echo '</td></tr></table>';
							echo '</td><td width="80%" align="center">';
							echo '&nbsp;&nbsp;&nbsp;' . stripslashes($_SESSION['CompanyRecord']['coyname']);
							// Make the title text a class, can be set to display:none is some themes
							echo '<BR>&nbsp;&nbsp;&nbsp;<a href="' .  $rootpath . '/UserSettings.php?' . SID . '"><img src="'.$rootpath.'/css/'.$theme.'/images/user.png" TITLE="User" ALT="' . _('Usuario') . '"> ' . stripslashes($_SESSION['UsersRealName']) . '</a>';
							echo '<BR>&nbsp;&nbsp;&nbsp;<font class="header_title"> ' . $title . '</font>';
							echo '</td></tr></table>';
						echo '</td>';

					}

					echo '</td></tr>';
					echo '</table>';

?>

