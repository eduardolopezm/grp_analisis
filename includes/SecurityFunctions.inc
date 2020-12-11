<?php
/* ARCHIVO MODIFICADO POR: Desarrollador */
/* FECHA DE MODIFICACION: 20-NOV-2009 */
/* CAMBIOS:*/
/* 1.- FUNCION DE TIENE PERMISO, CON VALOR 0/1 SEGUN SEA EL CASO */
/* 2.- FUNCION DE TIENE PERMISO PARA DEVOLUCION DE URL*/
/* FIN DE CAMBIOS*/
function HavepermissionURLV2($consultausuario, $funcionvalida, &$db) {
	
	/*********OBTIENE URL PASANDO COMO PARAMETRO EL NUMERO DE FUNCION***********/

	$secFunctionTable = "";
	if (empty ( $_SESSION ['SecFunctionTable'] )) {
		$secFunctionTable = "sec_functions";
	} else {
		$secFunctionTable = $_SESSION ['SecFunctionTable'];
	}
	
	$QuerySQL = "SELECT  F.url
			FROM $secFunctionTable F
			WHERE F.functionid=" . $funcionvalida . "
			order by F.functionid limit 1";
	
	$ErrMsg = _ ( 'No Tiene Permisos' );
	$GetPermission = DB_query ( $QuerySQL, $db, $ErrMsg );
	if (DB_num_rows ( $GetPermission ) == 1) {
		// En Caso de no contar con permiso, devuelve valor de url
		$myrowpermiso = DB_fetch_row ( $GetPermission );
		return $myrowpermiso [0];
	} else {
		// En Caso de no contar con permiso, devuelve valor vacio para url
		return '';
	}
}

Function Havepermission ($consultausuario,$funcionvalida, &$db){

//OBTIENE SI EL USUARIO TIENE O  NO ACCESO A LA FUNCIONALIDAD DE LA PAGINA
 //DEVOLVIENDO 1 O 0 DE ACUERDO AL PERMISO QUE OBTIENE DE LA TABLA DE FUNCIONES POR USUARIO
 // Y/0 DE LAS FUNCIONES POR PERFIL DE USUARIO
	/*$QuerySQL = "SELECT  case when (FP.functionid is null and FxU.functionid is null) then 0 else case when FxU.permiso is null then 1 else FxU.permiso end end as permiso
		     FROM sec_modules s,
			sec_submodules sm,
			www_users u,
			sec_profilexuser PU,
			sec_funxprofile FP,
			sec_functions F left join sec_funxuser FxU on  FxU.functionid=F.functionid and FxU.userid='".$consultausuario."',
			sec_categories C
		   WHERE s.moduleid=sm.moduleid and s.active=1
			 and FP.profileid=PU.profileid
			 and F.submoduleid=sm.submoduleid
			 and C.categoryid=F.categoryid
			 and u.userid=PU.userid and PU.userid='".$consultausuario."'
			 and u.userid=PU.userid
			 and F.functionid=FP.functionid
			 and FP.functionid=".$funcionvalida."
			 order by sm.orderno";
			 
	*/
	$secFunctionTable = "";
	if(empty($_SESSION['SecFunctionTable'])) {
		$secFunctionTable = "sec_functions";
	} else {
		$secFunctionTable = $_SESSION['SecFunctionTable'];
	}
	$QuerySQL=" SELECT  1 as permiso
		    FROM sec_modules s, sec_submodules sm, www_users u,
			  sec_profilexuser PU, sec_funxprofile FP,
			  $secFunctionTable FuxP, sec_categories C
		    WHERE s.moduleid=sm.moduleid and s.active=1
			  and FP.profileid=PU.profileid and FuxP.submoduleid=sm.submoduleid and C.categoryid=FuxP.categoryid
			  and u.userid=PU.userid and PU.userid='".$consultausuario."'
		          and u.userid=PU.userid and FuxP.functionid=FP.functionid
			  and  FP.functionid=".$funcionvalida."
			  and FuxP.active=1
			  and FuxP.functionid not in (select funCtionid from sec_funxuser where userid='".$consultausuario."')
		   UNION
		   SELECT  PU.permiso as permiso
		   FROM sec_modules s, sec_submodules sm, www_users u,
		      $secFunctionTable FuxP, sec_categories C, sec_funxuser PU
		   WHERE s.moduleid=sm.moduleid and s.active=1
		      and FuxP.submoduleid=sm.submoduleid and C.categoryid=FuxP.categoryid
		      and u.userid=PU.userid and PU.userid='".$consultausuario."'
		      and u.userid=PU.userid and FuxP.functionid=PU.functionid
		      and FuxP.functionid=".$funcionvalida."
		      and FuxP.active=1";
	//echo $QuerySQL;
	$ErrMsg =  _('No Tiene Permisos');
	$GetPermission = DB_query($QuerySQL, $db, $ErrMsg);
	if (DB_num_rows($GetPermission)==1){
		$myrowpermiso = DB_fetch_row($GetPermission);
		return $myrowpermiso[0];
	} else {
		return 0;
	}
}


Function HavepermissionURL ($consultausuario,$funcionvalida, &$db){

//OBTIENE SI EL USUARIO TIENE O NO PERMISO PARA VISUALIZAR LA URL,
//EN CASO DE CONTAR CON PERMISO DEVUELVE LA URL PARA SER MOSTRADA EN LA PAGINA,
//DE LO CONTRARIO DEVUELVE VACIO
	$secFunctionTable = "";
	if(empty($_SESSION['SecFunctionTable'])) {
		$secFunctionTable = "sec_functions";
	} else {
		$secFunctionTable = $_SESSION['SecFunctionTable'];
	}
	$QuerySQL = "SELECT  case when (FP.functionid is null and FxU.functionid is null) then 0 else case when FxU.permiso is null then 1 else FxU.permiso end end as permiso,F.url
		     FROM sec_modules s,
			sec_submodules sm,
			www_users u,
			sec_profilexuser PU,
			sec_funxprofile FP,
			$secFunctionTable F left join sec_funxuser FxU on  FxU.functionid=F.functionid and FxU.userid='".$_SESSION['UserID']."',
			sec_categories C
		   WHERE s.moduleid=sm.moduleid and s.active=1 and F.active=1
			 and FP.profileid=PU.profileid
			 and F.submoduleid=sm.submoduleid
			 and C.categoryid=F.categoryid
			 and u.userid=PU.userid and PU.userid='".$consultausuario."'
			 and u.userid=PU.userid
			 and F.functionid=FP.functionid
			 and FP.functionid=".$funcionvalida."
			 order by sm.orderno";
	//echo $QuerySQL;
	$ErrMsg =  _('No Tiene Permisos');
	$GetPermission = DB_query($QuerySQL, $db, $ErrMsg);
	if (DB_num_rows($GetPermission)>=1){
		// En Caso de no contar con permiso, devuelve valor de url 
		$myrowpermiso = DB_fetch_row($GetPermission);
		if ($myrowpermiso[0]==0)
		{
			return '';
		}else{
			 $url=explode('?', $myrowpermiso[1]);
			 $myrowpermiso[1]=$url[0];
			 return $myrowpermiso[1];	
			//echo $myrowpermiso[1];
		}
		
		
	} else {
		// En Caso de no contar con permiso, devuelve valor vacio para url 
		return '';
	}

	
}

$permiso = Havepermission($_SESSION['UserID'], $funcion, $db);

if ($permiso==0)
{
	$funcionNegada= traeNombreFuncion($funcion, $db);
	include 'includes/header.inc';
	?>
	<div class="row" style="margin-top: 10%;margin-bottom: 17%;">
		<div class="col-sm-12">
			<div class="col-sm-12  tituloPermiso" >
				Permiso Denegado!
			</div>
			<div >
				<div class="col-xs-2 col-sm-1 col-md-offset-4 text-right">
					<span class="glyphicon glyphicon-alert spanPermiso" aria-hidden="true" ></span>
				</div>
				<div class="col-xs-10 col-sm-6 text-left ">
					<strong><?= $funcion .' - ' .$funcionNegada ?></strong>
					<br>
					<p>No tiene acceso a esta pagina, consulte con el administrador.</p>
				</div>				
			</div>
			<div class="col-sm-12">
				<br>
				<a id="linkRegresar" name="linkRegresar" href="index.php" class="btn btn-default botonVerde glyphicon glyphicon-home"> Menu</a>
			</div>
		</div>
	</div>
	<?php
	include('includes/footer_Index.inc');	
	exit;	
}		
?>