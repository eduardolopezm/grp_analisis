<?php


Function HavepermissionHeader ($consultausuario,$funcionvalida, &$db){

//OBTIENE SI EL USUARIO TIENE O  NO ACCESO A LA FUNCIONALIDAD DE LA PAGINA
 //DEVOLVIENDO 1 O 0 DE ACUERDO AL PERMISO QUE OBTIENE DE LA TABLA DE FUNCIONES POR USUARIO
 // Y/0 DE LAS FUNCIONES POR PERFIL DE USUARIO
	
	//Seleccion de nuevo menœ o viejo menu
	if($_SESSION['ShowIndex']!=0){
		$sec_functions = "sec_functions_new";
	}else{
		$sec_functions = "sec_functions";
	}
	
	$QuerySQL=" SELECT  1 as permiso
		    FROM sec_modules s, sec_submodules sm, www_users u,
			  sec_profilexuser PU, sec_funxprofile FP,
			  $sec_functions FuxP, sec_categories C
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
		      $sec_functions FuxP, sec_categories C, sec_funxuser PU
		   WHERE s.moduleid=sm.moduleid and s.active=1
		      and FuxP.submoduleid=sm.submoduleid and C.categoryid=FuxP.categoryid
		      and u.userid=PU.userid and PU.userid='".$consultausuario."'
		      and u.userid=PU.userid and FuxP.functionid=PU.functionid
		      and FuxP.functionid=".$funcionvalida."
		      and FuxP.active=1";
	
	$ErrMsg =  _('No Tiene Permisos');
	$GetPermission = DB_query($QuerySQL, $db, $ErrMsg);
	if (DB_num_rows($GetPermission)==1){
		$myrowpermiso = DB_fetch_row($GetPermission);
		return $myrowpermiso[0];
	} else {
		return 0;
	}
}
?>