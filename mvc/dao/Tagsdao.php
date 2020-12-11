<?php
 
class Tagsdao{

	
	function Gettags(){
		$pathprefix = "../.././";
		require_once $pathprefix . 'core/ModeloBase.php';
		
		$UserID = $_SESSION["UserID"];
		$success = true;
		$msgerror = "";
		$typeerror = "";
		$codeerror = 0;
		$sql = "";
		$arrtags = array();

		$modelo = new ModeloBase;

		$sql = "SELECT t.tagref, t.tagdescription
		FROM tags t
		INNER JOIN sec_unegsxuser s ON t.tagref = s.tagref and s.userid = '" . $_SESSION["UserID"] . "'"; 
		$sql = "SELECT CONCAT(t.tagref, '_99_', tb_cat_unidades_ejecutoras.ue) as tagref, tb_cat_unidades_ejecutoras.desc_ue as tagdescription
		FROM tags t
		INNER JOIN sec_unegsxuser s ON t.tagref = s.tagref and s.userid = '".$_SESSION["UserID"]."'
		JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ur = t.tagref
		JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = t.tagref AND tb_sec_users_ue.ue = tb_cat_unidades_ejecutoras.ue AND tb_sec_users_ue.userid = '".$_SESSION["UserID"]."'";
		// Solo visualizar la default
		// $sql = "SELECT
		// CONCAT(tags.tagref, '_99_', tb_cat_unidades_ejecutoras.ue) as tagref, CONCAT(www_users.ln_ue, ' - ', tb_cat_unidades_ejecutoras.desc_ue) as tagdescription
		// FROM www_users
		// JOIN tags ON tags.tagref = www_users.defaultunidadNegocio
		// JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ur = www_users.defaultunidadNegocio AND tb_cat_unidades_ejecutoras.ue = www_users.ln_ue
		// WHERE www_users.userid = '".$_SESSION['UserID']."'";
        
        $resp = $modelo->ejecutarsql($sql);
		$message = "";
		
		if ($resp == true and !is_array($resp)){
			$success = false;
			$message = _('No existe unidades de negocio asignadas para el Usuario ' . $_SESSION["UserID"]);

		}else{
			if ($resp == false){
				$msgerror = "ERROR EN LA CONSULTA";
				$typeerror = "MYSQL ERROR";
				$codeerror = "001";

			}else{
				
				for($xx = 0; $xx < count($resp); $xx++){
					$arrtag = array(
						"tagref" => $resp[$xx]['tagref'],
						"tagdescriptionc" => $resp[$xx]['tagdescription'],
					);
					//$response['data'][] = $arrdebtor;		
					array_push($arrtags, $arrtag);
				}
			}
			
		}
		
		$response['success'] = $success;
		$response['error']['msgerror'] = $msgerror;
		$response['error']['typeerror'] = $typeerror;
		//$response['error']['codeerror'] = $codeerror;
		$response['error']['codeerror'] = $sql;
		$response['data']['message'] = $message;
		$response['data'] = $arrtags;
		
		//var_dump($custbranch);
		//header('Content-type: application/json; charset=utf-8');
  		//return json_encode($response, JSON_FORCE_OBJECT);
  		return $response;
  		

	}



}


?>