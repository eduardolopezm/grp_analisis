<?php

/**
 * 
 * @param elemento $objeto - los elementos son (texto, check, combodia, combomes, boton, combotipodocto, comboconfirma, comborazonsocial, combounidadnegocio, comboareas)
 * @param string $nombre - nombre que se le va a dar al elemento
 * @param string $valor - valor a asignar al elemento
 * @param string $estilo - sirve para poner los script de tipo css
 * @param bool $disabled - opcion true o false para deshabilitar el elemento
 * @param string $complemento - cadena para agregar opciones adicionales al elemento
 * @param string $clase - sirve para asignar la clase de estilo
 */

//
function InsertaElemento($objeto, $nombre = "", $valor= "", $estilo= "", $disabled= true, $complemento= "", $clase= "") 
{
	global $db;
	
	$elemento= "";
	$habilitado= "";
	
	if (!$disabled) {
            $habilitado= "disabled";
	}
	
	if (empty($nombre) && $objeto!="procesar"){
            prnMsg(_("Necesita asignar nombre al objeto ").$objeto, "warn");	
	}
		
	if ($objeto == 'texto') {
            $elemento= "<input type='textbox' name='".$nombre."' value='".$valor."' style='".$estilo."' ".$complemento." ".$clase." ".$habilitado.">";
	}
        
        if ($objeto == 'oculto') {
            $elemento= "<input type='hidden' name='".$nombre."' value='".$valor."' style='".$estilo."' ".$complemento." ".$clase." ".$habilitado.">";
	}
	
	if ($objeto == 'check') {
            $elemento= "<input type='checkbox' name='".$nombre."' value='".$valor."' style='".$estilo."' ".$complemento." ".$clase." ".$habilitado.">";
	}
        
        if ($objeto == 'etiqueta') {
            $elemento= "<label name='".$nombre."' style='".$estilo."' ".$complemento." ".$clase." ".$habilitado.">".$valor."</label>";
        }
        
        if ($objeto == 'procesar') {
            $elemento= '<div id="myDivLoading" style="margin-left: 40%; display: none; border: 1px solid lightgray; background-color: white; position: absolute; z-index: 1; vertical-align: middle; padding: 10px;">
                            <img src="images/enProceso.gif" width=80 height=80 /><br>
                            <span style="color: gray;">Procesando...</span>
                        </div>';
        }
	
	// Genera un Combo de Dias del Mes
	if ($objeto == 'combodia')
	{
            $elemento= "<select Name='".$nombre."' style='".$estilo."' ".$habilitado.">";

            // Consulta para determinar los dias del mes
            $consulta= "SELECT * FROM cat_Days";

            $dias = DB_query($consulta, $db);

            while ($renglon = DB_fetch_array($dias))
            {
                $diabase=$renglon['DiaId'];

                if ($valor == $diabase) {
                    $elemento.= "<option  VALUE='" . $renglon['DiaId'] . "' selected>" .$renglon['Dia'];
                }else{
                    $elemento.= '<option  VALUE="' . $renglon['DiaId'] .  '" >' .$renglon['Dia'];
                }
            }

            $elemento.= '</select>';
	}
	
	// Genera un Combo de Meses
	if ($objeto == 'combomes')
	{
            // Sacar consulta con los meses
            $elemento= '<select Name="'.$nombre.'" style="'.$estilo.'">';

            $consulta = "SELECT * FROM cat_Months";

            $meses = DB_query($consulta, $db);

            while ($renglon = DB_fetch_array($meses))
            {
                $mesbase=$renglon['u_mes'];

                if (rtrim(intval($valor))==rtrim(intval($mesbase))){
                    $elemento.= '<option  VALUE="' . $renglon['u_mes'] .  '" selected>' .$renglon['mes'];
                }else{
                    $elemento.= '<option  VALUE="' . $renglon['u_mes'] .  '" >' .$renglon['mes'];
                }
            }
            $elemento.= '</select>';
	}

	// Genera un Combo de Semanas
	if ($objeto == 'combosemana')
	{
            // Sacar consulta con los meses
            $elemento= '<select Name="'.$nombre.'" style="'.$estilo.'">';

            $desp=array("Lunes","Martes", "Miercoles", "Jueves", "Viernes","Sabado","Domingo");
            $deng=array("Monday","Thuesday","Wednesday","Thursday","Friday","Saturday","Sunday");
            $mesp=array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Novimebre","Diciembre");
            $meng=array("January","February","March","April","May","June","July","August","September","October","November","December");

            $i=1;
            $conweeks= 1;
            
            while ($conweeks<=NumeroSemanasTieneUnAno(Date("Y")))
            {
                $sembase=$conweeks;
                $SQLdatethisweek="SELECT DATE_FORMAT(STR_TO_DATE('2017".$i++." Monday', '%X%V %W'),'%W %d de %M de %Y') AS datestartweek;";
                $resultdateethisweek=DB_query($SQLdatethisweek,$db);
                $myrowdatethisweek=DB_fetch_array($resultdateethisweek);
                if (rtrim($valor)==rtrim($sembase)){
                        $elemento.= '<option  VALUE="' . $conweeks .  '" selected> Semana ' .$conweeks.'('.str_replace($deng,$desp,str_replace($meng,$mesp,$myrowdatethisweek['datestartweek'])).')';
                }else{
                        $elemento.= '<option  VALUE="' . $conweeks .  '" >Semana ' .$conweeks.'('.str_replace($deng,$desp,str_replace($meng,$mesp,$myrowdatethisweek['datestartweek'])).')';
                }
                $conweeks++;
            }
            $elemento.= '</select>';
	}

	// Genera Boton normal
	if ($objeto == "boton")
	{
            $elemento= "<input type='submit' name='".$nombre."' value='".$valor."' style='".$estilo."' ".$complemento." ".$clase." ".$habilitado.">";
	}
	
	// Genera combo de tipo de documento
	if ($objeto =="combotipodocto") 
	{
		$consulta= "Select Distinct systypesbyreport.typedoc, RIGHT(CONCAT('000', systypesbyreport.typedoc),3) AS typeid, systypescat.typename
                            From sec_profilexuser
                            Inner Join sec_funxprofile on sec_profilexuser.profileid= sec_funxprofile.profileid
                            Inner Join systypesbyreport on sec_funxprofile.functionid= systypesbyreport.functionid
                            Inner Join systypescat on systypesbyreport.typedoc= systypescat.typeid
                            Where userid= '".$_SESSION["UserID"]."' 
                            Order by systypescat.typename";
		
		$resultado= DB_query($consulta, $db);
		
		$elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '.$clase.'>';
		$elemento.= "<option value='-1'>Todos los Tipos...</option>";
		
		while ($renglon = DB_fetch_array($resultado)) {
                    if ($renglon["typedoc"] == $valor) {
                        $elemento.= "<option value='".$renglon["typedoc"]."' selected>".$renglon["typeid"]." - ".$renglon["typename"]."</option>";
                    } else {
                        $elemento.= "<option value='".$renglon["typedoc"]."'>".$renglon["typeid"]." - ".$renglon["typename"]."</option>";
                    }
		} 
		
		$elemento.= "</select>";
		
		if (DB_num_rows($resultado) == 0) {
                    $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No tiene tipo de documentos asignados...</font>";
		}
	}
        
        // Genera combo de tipo de documento
	if ($objeto =="combotipodoctoinv") 
	{
            $consulta= "Select Distinct systypesbyreport.typedoc, RIGHT(CONCAT('000', systypesbyreport.typedoc),3) AS typeid, systypescat.typename
                        From sec_profilexuser
                        Inner Join sec_funxprofile on sec_profilexuser.profileid= sec_funxprofile.profileid
                        Inner Join systypesbyreport on sec_funxprofile.functionid= systypesbyreport.functionid AND systypesbyreport.functionid = 703
                        Inner Join systypescat on systypesbyreport.typedoc= systypescat.typeid
                        Where sec_profilexuser.userid= '".$_SESSION["UserID"]."' 
                        Order by systypescat.typename";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '.$clase.'>';
            $elemento.= "<option value='-1'>Todos los Tipos...</option>";

            while ($renglon = DB_fetch_array($resultado)) {
                if (in_array($renglon["typedoc"], $valor) || $renglon["typedoc"] == $valor){
                    $elemento.= "<option value='".$renglon["typedoc"]."' selected>".$renglon["typeid"]." - ".$renglon["typename"]."</option>";
                } else {
                    $elemento.= "<option value='".$renglon["typedoc"]."'>".$renglon["typeid"]." - ".$renglon["typename"]."</option>";
                }
            } 

            $elemento.= "</select>";

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No tiene tipo de documentos asignados...</font>";
            }
	}
	
	// Combo para seleccionar una respuesta de SI o NO
	if ($objeto =="comboconfirma") 
	{
            $elemento= '<select Name="'.$nombre.'" style="'.$estilo.'" '.$habilitado.'>';

            if ($valor == 0){
                $elemento.= '<option value=0 selected>' . _('No'). '</option>';
                $elemento.= '<option value=1>' . _('Si'). '</option>';
            } else {
                $elemento.= '<option value=0>' . _('No'). '</option>';
                $elemento.= '<option value=1 selected>' . _('Si'). '</option>';
            }
            $elemento.= '</select>';
	}
	
	// Genera combo de las razones sociales
	if ($objeto =="comborazonsocial")
	{
            $consulta ="SELECT legalbusinessunit.legalid, legalbusinessunit.legalname
                        FROM sec_unegsxuser u, tags t
                        JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid
                        WHERE u.tagref = t.tagref
                            AND u.userid = '" . $_SESSION['UserID'] . "'
                        GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname 
                        ORDER BY legalbusinessunit.legalname";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '.$clase.'>';
            $elemento.= "<option value='-1'>Todas las Razones Sociales...</option>";

            while ($renglon= DB_fetch_array($resultado))
            {
                if (in_array($renglon["legalid"], $valor) || $renglon["legalid"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['legalid'] . '>' .$renglon['legalname']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['legalid'] . '>' .$renglon['legalname']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No tiene Razones Sociales asignados...</font>";
            }
	}
	
	// Genera combo de las unidades de negocio
	if ($objeto =="combounidadnegocio")
	{
            $consulta ="SELECT  t.tagref, CONCAT(t.tagref,' - ',t.tagdescription) AS tagdescription, t.tagdescription
                        FROM sec_unegsxuser u
                        INNER JOIN tags t ON u.tagref = t.tagref
                        WHERE u.userid = '" . $_SESSION ['UserID'] . "' 
                        ORDER BY t.tagdescription";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '.$clase.'>';
            $elemento.= "<option value='-1'>Todas las Unidades de Negocio...</option>";

            while ($renglon= DB_fetch_array($resultado))
            {
                if (in_array($renglon["tagref"], $valor) || $renglon["tagref"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['tagref'] . '>' .$renglon['tagdescription']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['tagref'] . '>' .$renglon['tagdescription']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                    $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No tiene Unidades de Negocio asignados...</font>";
            }
	}
	
	// Genera combo de almacenes
	if ($objeto =="combo_almacenes")
	{
            $consulta= "SELECT t.tagdescription, l.loccode, l.locationname  
                        FROM  sec_loccxusser s, locations l
                        LEFT JOIN tags t ON l.tagref = t.tagref 
                        WHERE s.userid = '" . $_SESSION ['UserID'] . "' 
                            and s.loccode = l.loccode
                        ORDER BY l.locationname, t.tagdescription";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '.$clase.'>';
            $elemento.= "<option value='-1'>Todos los almacenes...</option>";

            while ($renglon= DB_fetch_array($resultado))
            {
                if (in_array($renglon["loccode"], $valor) || $renglon["loccode"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['loccode'] . '>' .$renglon['locationname']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['loccode'] . '>' .$renglon['locationname']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No tiene almacenes capturados...</font>";
            }
	}
        
        // Genera combo de localidades
	if ($objeto =="combo_localidades")
	{
		$consulta= "SELECT sectorlocations.seccode, sectorlocations.description
                            FROM sectorlocations
                            INNER JOIN locations ON sectorlocations.loccode= locations.loccode
                            INNER JOIN sec_loccxusser ON locations.loccode= sec_loccxusser.loccode
                            WHERE sectorlocations.active= 1 
                            AND sectorlocations.description != ''
                            AND sec_loccxusser.userid= '" . $_SESSION ['UserID'] . "'
                            ORDER BY sectorlocations.description";
	
		$resultado= DB_query($consulta, $db);
	
                $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '.$clase.'>';
		$elemento.= "<option value='-1'>Todas las localidades...</option>";
	
		while ($renglon= DB_fetch_array($resultado))
		{
			if ($renglon["seccode"] == $valor){
				$elemento.= '<option selected value=' . $renglon['seccode'] . '>' .$renglon['description']."</option>";
			}else{
				$elemento.= '<option value=' . $renglon['seccode'] . '>' .$renglon['description']."</option>";
			}
		}
	
		$elemento.= '</select>';
	
		if (DB_num_rows($resultado) == 0) {
			$elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No tiene localidades capturadas...</font>";
		}
	}
	
	// Genera combo de regiones
	if ($objeto =="combo_regiones")
	{
		$consulta= "SELECT regioncode, CONCAT(regioncode,' - ',name) as descripcion FROM regions";
	
		$resultado= DB_query($consulta, $db);
	
		$elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '.$clase.'>';
		$elemento.= "<option value='-1'>Todas las regiones...</option>";
	
		while ($renglon= DB_fetch_array($resultado))
		{
			if ($renglon["regioncode"] == $valor){
				$elemento.= '<option selected value=' . $renglon['regioncode'] . '>' .$renglon['descripcion']."</option>";
			}else{
				$elemento.= '<option value=' . $renglon['regioncode'] . '>' .$renglon['descripcion']."</option>";
			}
		}
	
		$elemento.= '</select>';
	
		if (DB_num_rows($resultado) == 0) {
			$elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No tiene regiones capturadas...</font>";
		}
	}
        
    // Genera combo de areas
	if ($objeto =="comboareas")
	{
            $consulta= "SELECT areas.areacode, SUBSTRING(areadescription, LOCATE(' ', areadescription)+1) AS nombrearea, areas.areadescription, areas.regioncode 
                        FROM areas 
                        INNER JOIN tags ON areas.areacode= tags.areacode
                        INNER JOIN sec_unegsxuser ON tags.tagref= sec_unegsxuser.tagref
                        WHERE sec_unegsxuser.userid= '" . $_SESSION ['UserID'] . "' 
                        GROUP BY areas.areacode, areas.areadescription, areas.regioncode
                        ORDER BY SUBSTRING(areas.areadescription, LOCATE(' ', areas.areadescription)+1)";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '.$clase.'>';
            $elemento.= "<option value='-1'>Todas las areas...</option>";

            while ($renglon= DB_fetch_array($resultado))
            {
                if ($renglon["areacode"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['areacode'] . '>' .$renglon['nombrearea']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['areacode'] . '>' .$renglon['nombrearea']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No tiene areas asignadas...</font>";
            }
	}
        
        // Genera Editor de Texto
	if ($objeto == "editor")
	{
            //$elemento.= "<textarea name='".$nombre."' rows='2' cols='40' style='border-color: #D8D8D8; background: #FDFDE2' "..">";
            $elemento.= "<textarea name='".$nombre."' rows='2' cols='40' style='".$estilo."' ".$complemento." ".$habilitado.">";
            $elemento.= $valor;
            $elemento.= "</textarea>";
        }
        
        // Genera Editor de Texto
	if ($objeto == "editor02")
	{
            //$elemento.= "<textarea name='".$nombre."' rows='2' cols='40' style='border-color: #D8D8D8; background: #FDFDE2' "..">";
            $elemento.= "<textarea name='".$nombre."' style='".$estilo."' ".$complemento." ".$habilitado.">";
            $elemento.= $valor;
            $elemento.= "</textarea>";
        }
        
    // Genera combo de perfiles
	if ($objeto == "comboperfil")
	{
            $consulta= "Select * 
                        From sec_profiles
                        Where active= 1 
                        Order by name";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '.$clase.'>';
            $elemento.= "<option value='-1'>Todos los Perfiles...</option>";

            while ($renglon= DB_fetch_array($resultado))
            {
                if ($renglon["profileid"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['profileid'] . '>' .$renglon['name']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['profileid'] . '>' .$renglon['name']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay perfiles creados...</font>";
            }
	}
        
        // Genera combo de usuarios
	if ($objeto == "combo_usuarios")
	{
		$consulta= "SELECt www_users.userid, www_users.realname
                            FROM www_users 
                            INNER JOIN sec_ROLxuser ON www_users.userid = sec_ROLxuser.userid
                            INNER JOIN sec_proyectoxuser ON www_users.userid = sec_proyectoxuser.userid																			 
                            WHERE blocked=0
                            GROUP BY www_users.userid, www_users.realname
                            ORDER BY www_users.realname";
	
		$resultado= DB_query($consulta, $db);
	
		$elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '.$clase.'>';
		$elemento.= "<option value='-1'>Todos los Recursos...</option>";
	
		while ($renglon= DB_fetch_array($resultado))
		{
                    if ($renglon["userid"] == $valor){
                        $elemento.= '<option selected value=' . $renglon['userid'] . '>' .$renglon['realname']."</option>";
                    }else{
                        $elemento.= '<option value=' . $renglon['userid'] . '>' .$renglon['realname']."</option>";
                    }
		}
	
		$elemento.= '</select>';
	
		if (DB_num_rows($resultado) == 0) {
                    $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay recursos creados...</font>";
		}
	}
        
        // Genera combo de usuarios flujo
	if ($objeto == "combo_usuariosflujo")
	{
		$consulta= "SELECT DISTINCT asignar_perfil, www_users.realname
                            FROM prdflujos_tareas
                            INNER JOIN www_users ON prdflujos_tareas.asignar_perfil= www_users.userid
                            WHERE asignar_perfil!= -1
                            UNION
                            SELECT DISTINCT asignar_perfil, www_users.realname 
                            FROM prdflujos_actividades
                            INNER JOIN www_users ON prdflujos_actividades.asignar_perfil= www_users.userid
                            WHERE asignar_perfil!= -1
                            GROUP BY asignar_perfil, www_users.realname";
	
		$resultado= DB_query($consulta, $db);
	
		$elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '.$clase.'>';
		$elemento.= "<option value='-1'>Todos los Usuarios...</option>";
	
		while ($renglon= DB_fetch_array($resultado))
		{
                    if ($renglon["asignar_perfil"] == $valor){
                            $elemento.= '<option selected value=' . $renglon['asignar_perfil'] . '>' .$renglon['realname']."</option>";
                    }else{
                            $elemento.= '<option value=' . $renglon['asignar_perfil'] . '>' .$renglon['realname']."</option>";
                    }
		}
	
		$elemento.= '</select>';
	
		if (DB_num_rows($resultado) == 0) {
                    $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay usuarios relacionados a proceso...</font>";
		}
	}
	
	// Genera combo de recursos
	if ($objeto == "comborecurso")
	{
		$consulta= "SELECt www_users.userid, www_users.realname
                            FROM www_users 
                            INNER JOIN sec_ROLxuser ON www_users.userid = sec_ROLxuser.userid
                            INNER JOIN sec_proyectoxuser ON www_users.userid = sec_proyectoxuser.userid																			 
                            WHERE sec_ROLxuser.profileid = 4 and blocked=0
                            GROUP BY www_users.userid, www_users.realname
                            ORDER BY www_users.realname";
	
		$resultado= DB_query($consulta, $db);
	
		$elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '.$clase.'>';
		$elemento.= "<option value='-1'>Todos los Recursos...</option>";
	
		while ($renglon= DB_fetch_array($resultado))
		{
                    if ($renglon["userid"] == $valor){
                        $elemento.= '<option selected value=' . $renglon['userid'] . '>' .$renglon['realname']."</option>";
                    }else{
                        $elemento.= '<option value=' . $renglon['userid'] . '>' .$renglon['realname']."</option>";
                    }
		}
	
		$elemento.= '</select>';
	
		if (DB_num_rows($resultado) == 0) {
			$elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay recursos creados...</font>";
		}
	}
        
        // Genera combo de recursos
	if ($objeto == "comborecurso_especifico")
	{
            $consulta= "SELECt www_users.userid, www_users.realname
                        FROM www_users 
                        INNER JOIN sec_profilexuser ON www_users.userid= sec_profilexuser.userid
                        INNER JOIN sec_profiles ON sec_profilexuser.profileid = sec_profiles.profileid AND sec_profiles.name IN ('PROYECTISTAS')
                        INNER JOIN sec_ROLxuser ON www_users.userid = sec_ROLxuser.userid
                        INNER JOIN sec_proyectoxuser ON www_users.userid = sec_proyectoxuser.userid																			 
                        WHERE sec_ROLxuser.profileid = 4 and blocked=0
                        GROUP BY www_users.userid, www_users.realname
                        ORDER BY www_users.realname";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '.$clase.'>';
            $elemento.= "<option value='-1'>Todos los Recursos...</option>";

            while ($renglon= DB_fetch_array($resultado))
            {
                if ($renglon["userid"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['userid'] . '>' .$renglon['realname']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['userid'] . '>' .$renglon['realname']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay recursos creados...</font>";
            }
	}
        
        // Genera combo de prioridades
	if ($objeto == "comboprioridad")
	{
            $consulta= "Select idprioridad, nombre
                        From prdprioridad
                        Order by idprioridad";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.'>';
            // $elemento.= "<option value='-1'>Todos los Perfiles...</option>";

            while ($renglon= DB_fetch_array($resultado))
            {
                if ($renglon["idprioridad"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['idprioridad'] . '>' .$renglon['nombre']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['idprioridad'] . '>' .$renglon['nombre']."</option>";
                }
            }

            $elemento.= '</select>';
	}
        
        // Genera combo de prioridades
	if ($objeto == "combo_prioridad_tareas")
	{
            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.'>';
            $elemento.= "<option value='-1'>Todas las Prioridades...</option>";
            
            if ($valor == 1){
                $elemento.= "<option selected value='1'>Critica</option>";
            } else {
                $elemento.= "<option value='1'>Critica</option>";
            }
            
            if ($valor == 2){
                $elemento.= "<option selected value='2'>Muy Importante</option>";
            } else {
                $elemento.= "<option value='2'>Muy Importante</option>";
            }
            
            if ($valor == 3){
                $elemento.= "<option selected value='3'>Importante</option>";
            } else {
                $elemento.= "<option value='3'>Importante</option>";
            }
            
            if ($valor == 4){
                $elemento.= "<option selected value='4'>Normal</option>";
            } else {
                $elemento.= "<option value='4'>Normal</option>";
            }
            
            if ($valor == 5){
                $elemento.= "<option selected value='5'>Seria Bueno</option>";
            } else {
                $elemento.= "<option value='5'>Seria Bueno</option>";
            }
            
            $elemento.= '</select>';
	}
        
        // Genera combo de proyectos
	if ($objeto == "comboproyectos")
	{
            $consulta= "Select idproyecto, nombre
                        From prdproyectos
                        Where nombre Is Not Null
                        Order by nombre";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'">';
            // $elemento.= "<option value='-1'>Todos los Perfiles...</option>";

            while ($renglon= DB_fetch_array($resultado))
            {
                if ($renglon["idproyecto"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['idproyecto'] . '>' .$renglon['nombre']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['idproyecto'] . '>' .$renglon['nombre']."</option>";
                }
            }

            $elemento.= '</select>';
	}
        
        // Genera combo de proyectos por proceso
	if ($objeto == "combo_proyectosproceso")
	{
            $consulta= "SELECT prdproyectos.idproyecto, prdproyectos.nombre
                        FROM prdflujos_proyectos
                        INNER JOIN prdproyectos ON prdflujos_proyectos.idproyecto= prdproyectos.idproyecto
                        INNER JOIN prdflujos ON prdflujos_proyectos.idflujo_original = prdflujos.idflujo AND prdflujos.version=1
                        GROUP BY prdproyectos.idproyecto, prdproyectos.nombre";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'">';
            $elemento.= "<option value='-1'>Todos los Proyectos...</option>";

            while ($renglon= DB_fetch_array($resultado))
            {
                if ($renglon["idproyecto"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['idproyecto'] . '>' .$renglon['nombre']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['idproyecto'] . '>' .$renglon['nombre']."</option>";
                }
            }

            $elemento.= '</select>';
            
            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay Proyectos ligados a procesos...</font>";
            }
	}
	
	// Genera combo de proyectos
	if ($objeto == "combo_odenestrabajo")
	{
		$consulta= "SELECT wo, CONCAT(wo, ' | ', CASE WHEN wodescription IS NULL THEN 'SIN DESCRIPCION' ELSE wodescription END) AS descripcion
                            FROM workorders
                            WHERE closed= 0 AND wodescription IS NOT NULL
                            ORDER BY wo";
	
		$resultado= DB_query($consulta, $db);
	
		$elemento= '<select name="'.$nombre.'" class="chosen-select" style="'.$estilo.'" '.$complemento.'>';
		$elemento.= "<option value='-1'>Seleccione una Orden de Trabajo...</option>";
	
		while ($renglon= DB_fetch_array($resultado))
		{
			if ($renglon["wo"] == $valor){
				$elemento.= '<option selected value=' . $renglon['wo'] . '>' .$renglon['descripcion']."</option>";
			}else{
				$elemento.= '<option value=' . $renglon['wo'] . '>' .$renglon['descripcion']."</option>";
			}
		}
	
		$elemento.= '</select>';
		
		if (DB_num_rows($resultado) == 0) {
			$elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay Ordenes de Trabajo generadas...</font>";
		}
	}
        
        // Genera combo de proyectos
	if ($objeto == "combo_odenes_trabajo_conc")
	{
		$consulta= "SELECT wo, CONCAT(CASE WHEN wodescription IS NULL THEN 'SIN DESCRIPCION' ELSE wodescription END, ' - OT: ', wo) AS        descripcion
                    FROM workorders
                    INNER JOIN wocontrolpanel_status ON wocontrolpanel_status.idstatus=workorders.idstatus
                    WHERE closed= 0 AND wocontrolpanel_status.flagcancelado!=1
                    AND wodescription IS NOT NULL
                    ORDER BY 2";
	
		$resultado= DB_query($consulta, $db);
	
		$elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.'>';
		$elemento.= "<option value='-1'>Seleccione una Orden de Trabajo...</option>";
	
		while ($renglon= DB_fetch_array($resultado))
		{
                    if ($renglon["wo"] == $valor){
                        $elemento.= '<option selected value=' . $renglon['wo'] . '>' .$renglon['descripcion']."</option>";
                    }else{
                        $elemento.= '<option value=' . $renglon['wo'] . '>' .$renglon['descripcion']."</option>";
                    }
		}
	
		$elemento.= '</select>';
		
		if (DB_num_rows($resultado) == 0) {
                    $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay Ordenes de Trabajo generadas...</font>";
		}
	}
        
        // Genera combo de proyectos
	if ($objeto == "combo_odenestrabajo_placa")
	{
            /*$consulta= "SELECT workorders.wo, workorders.wo AS descripcion, CASE WHEN archivos.datos IS NULL THEN '' ELSE 'Con Ptxs' END AS datos
                        FROM workorders
                        LEFT JOIN (SELECT wo, count(*) AS datos FROM wofileslabels GROUP BY wo) AS archivos ON workorders.wo= archivos.wo
                        ORDER BY workorders.wo";*/

            $consulta= "SELECT workorders.wo, workorders.wo AS descripcion, CASE WHEN archivos.datos IS NULL THEN '' ELSE 'Con Ptxs'        END AS datos
                        FROM workorders
                        LEFT JOIN (SELECT wo, count(*) AS datos FROM wofileslabels GROUP BY wo) AS archivos ON workorders.wo= archivos.wo
                        LEFT JOIN (SELECT DISTINCT wo FROM wopackages) AS paquete ON workorders.wo= paquete.wo
                        WHERE paquete.wo IS NULL 
                        AND workorders.requiredby >= '2016-01-01'
                        AND workorders.idstatus NOT IN (SELECT idstatus FROM wocontrolpanel_status WHERE flagcancelado)
                        ORDER BY workorders.wo";            

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" class="chosen-select" style="'.$estilo.'" '.$complemento.'>';
            $elemento.= "<option value='-1'>Seleccione OT...</option>";

            while ($renglon= DB_fetch_array($resultado))
            {
                if ($renglon["wo"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['wo'] . '>' .$renglon['descripcion']." ".$renglon['datos']."</option>";
                }else {
                    $elemento.= '<option value=' . $renglon['wo'] . '>' .$renglon['descripcion']." ".$renglon['datos']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay Ordenes de Trabajo generadas...</font>";
            }
	}
        
    // Genera combo de estatus OT
    if ($objeto == "combo_estatusot")
	{
            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '.$clase.'>';
            
            $elemento.= "<option value='-1'>Seleccione Estatus...</option>";
            
            if ($valor== 1) {
                $elemento.= '<option value="1" selected>Abierto</option>';
            } else {
                $elemento.= '<option value="1">Abierto</option>';    
            }
            
            if ($valor == 2){
                $elemento.= '<option value="2" selected>Cerrado</option>';    
            } else {
                $elemento.= '<option value="2">Cerrado</option>';
            }
            
            if ($valor == 3){
                $elemento.= '<option value="3" selected>Cancelado</option>'; 
            } else {
                $elemento.= '<option value="3">Cancelado</option>'; 
            }
            
            $elemento.= '</select>';
        }
        
        // Genera combo de procesos
	if ($objeto == "combo_procesos")
	{
            $consulta= "SELECT * 
                        FROM prdflujos
                        WHERE estatus=1 and version=1";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.'>';
            $elemento.= "<option value='-1'>Seleccione Proceso...</option>";

            while ($renglon= DB_fetch_array($resultado))
            {
                if ($renglon["idflujo"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['idflujo'] . '>' .$renglon['flujo']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['idflujo'] . '>' .$renglon['flujo']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay Procesos generados...</font>";
            }
	}
        
        // Genera combo de procesos por proyecto
	if ($objeto == "combo_procesosproyecto")
	{
            $consulta= "SELECT DISTINCT prdflujos.idflujo, prdflujos.flujo
                        FROM prdflujos
                        INNER JOIN prdflujos_proyectos ON prdflujos.idflujo= prdflujos_proyectos.idflujo_original
                        WHERE prdflujos.estatus=1 AND  version = 1
                        GROUP BY prdflujos.idflujo, prdflujos.flujo";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.'>';
            $elemento.= "<option value='-1'>Todos los Procesos...</option>";

            while ($renglon= DB_fetch_array($resultado))
            {
                if ($renglon["idflujo"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['idflujo'] . '>' .$renglon['flujo']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['idflujo'] . '>' .$renglon['flujo']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay Procesos relacionados a Proyecto...</font>";
            }
	}
        
        // Genera combo de tipo de niveles para tarea
        if ($objeto == "combo_tiponivel")
	{
            $consulta= "SELECT * 
                        FROM tasks_tiponivel
                        WHERE flagactive=1 
                        ORDER BY orden";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.'>';
            //$elemento.= "<option value='-1'>Seleccione Nivel...</option>";

            while ($renglon= DB_fetch_array($resultado))
            {
                if ($renglon["idtiponivel"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['idtiponivel'] . '>' .$renglon['descripcion']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['idtiponivel'] . '>' .$renglon['descripcion']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay Niveles creados...</font>";
            }
        }
        
        // Genera combo de estatus de flujos
        if ($objeto == "combo_estatusflujo")
	{
            $consulta= "SELECT * 
                        FROM prdflujos_estatus
                        ORDER BY orden";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.'>';
            $elemento.= "<option value='-1'>Seleccione Estatus...</option>";

            while ($renglon= DB_fetch_array($resultado))
            {
                if ($renglon["idstatus"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['idstatus'] . '>' .$renglon['nombrealterno']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['idstatus'] . '>' .$renglon['nombrealterno']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay estatus creados...</font>";
            }
        }
        
        // Genera combo con horarios
        if ($objeto == "combo_horas"){
            $listaDeHoras= array();
            
            $listaDeHoras[1] = '05:00';
            $listaDeHoras[2] = '05:30';
            $listaDeHoras[3] = '06:00';
            $listaDeHoras[4] = '06:30';
            $listaDeHoras[5] = '07:00';
            $listaDeHoras[6] = '07:30';
            $listaDeHoras[7] = '08:00';
            $listaDeHoras[8] = '08:30';
            $listaDeHoras[9] = '09:00';
            $listaDeHoras[10] = '09:15';
            $listaDeHoras[11] = '09:30';
            $listaDeHoras[12] = '09:45';
            $listaDeHoras[13] = '10:00';
            $listaDeHoras[14] = '10:15';
            $listaDeHoras[15] = '10:30';
            $listaDeHoras[16] = '10:45';
            $listaDeHoras[17] = '11:00';
            $listaDeHoras[18] = '11:15';
            $listaDeHoras[19] = '11:30';
            $listaDeHoras[20] = '11:45';
            $listaDeHoras[21] = '12:00';
            $listaDeHoras[22] = '12:15';
            $listaDeHoras[23] = '12:30';
            $listaDeHoras[24] = '12:45';
            $listaDeHoras[25] = '13:00';
            $listaDeHoras[26] = '13:15';
            $listaDeHoras[27] = '13:30';
            $listaDeHoras[28] = '13:45';
            $listaDeHoras[29] = '14:00';
            $listaDeHoras[30] = '14:15';
            $listaDeHoras[31] = '14:30';
            $listaDeHoras[32] = '14:45';
            $listaDeHoras[33] = '15:00';
            $listaDeHoras[34] = '15:15';
            $listaDeHoras[35] = '15:30';
            $listaDeHoras[36] = '15:45';
            $listaDeHoras[37] = '16:00';
            $listaDeHoras[38] = '16:15';
            $listaDeHoras[39] = '16:30';
            $listaDeHoras[40] = '16:45';
            $listaDeHoras[41] = '17:00';
            $listaDeHoras[42] = '17:15';
            $listaDeHoras[43] = '17:30';
            $listaDeHoras[44] = '17:45';
            $listaDeHoras[45] = '18:00';
            $listaDeHoras[46] = '18:15';
            $listaDeHoras[47] = '18:30';
            $listaDeHoras[48] = '18:45';
            $listaDeHoras[49] = '19:00';
            $listaDeHoras[50] = '19:15';
            $listaDeHoras[51] = '19:30';
            $listaDeHoras[52] = '19:45';
            $listaDeHoras[53] = '20:00';
            $listaDeHoras[54] = '20:15';
            $listaDeHoras[55] = '20:30';
            $listaDeHoras[56] = '20:45';
            $listaDeHoras[57] = '21:00';
            $listaDeHoras[58] = '21:15';
            $listaDeHoras[59] = '21:30';
            $listaDeHoras[60] = '21:45';
            $listaDeHoras[61] = '22:00';
            $listaDeHoras[62] = '22:30';
            $listaDeHoras[63] = '23:00';
            $listaDeHoras[64] = '23:30';
            
            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.'>';
            $elemento.= "<option value='0'></option>";

            foreach ($listaDeHoras as $llave => $hora) 
            {
                if ($llave == $valor){
                    $elemento.= '<option selected value=' . $llave . '>' .$hora."</option>";
                }else{
                    $elemento.= '<option value=' . $llave . '>' .$hora."</option>";
                }
            }

            $elemento.= '</select>';
        }
        
        // Genera combo con horarios en cantidad
        if ($objeto == "combo_montohoras"){
            $j = 0;
            $k = 0;
            
            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.'>';
            $elemento.= "<option value='0'></option>";

            for($i = 0.25; $i < 5.25; $i += 0.25) {
                $j += 0.25;
                $text_selected = "";
                
                switch ($j) {
                    case 0.25 :
                        if ($valor == $i)
                            $text_selected = "selected";
                        
                        $elemento.=  "<option " . $text_selected . " value='" . $i . "'>" . $k . ':15';
                        break;
                        
                    case 0.5 :
                        if ($valor == $i)
                            $text_selected = "selected";
                        
                        $elemento.=  "<option " . $text_selected . " value='" . $i . "'>" . $k . ':30';
                        break;
                        
                    case 0.75 :
                        if ($valor == $i)
                            $text_selected = "selected";
                        
                        $elemento.=  "<option " . $text_selected . " value='" . $i . "'>" . $k . ':45';
                        break;
                        
                    default :
                        $k ++;
                        $j = 0;
                        
                        if ($_POST ['montoHora'] == $i)
                            $text_selected = "selected";
                        
                        $elemento.=  "<option " . $text_selected . " value='" . $i . "'>" . $k . ':00';
                        break;
                }
            }
        }
        
        // Genera combo de estatus de flujos
        if ($objeto == "combo_categorias_ot")
	{
            $consulta= "SELECT stockcategory.categoryid, stockcategory.categorydescription, SUM(IF(woitems.stockid IS NULL, 0, 1)) AS ordenes
                        FROM stockcategory
                        INNER JOIN stockmaster ON stockcategory.categoryid= stockmaster.categoryid
                        LEFT JOIN woitems ON stockmaster.stockid= woitems.stockid
                        WHERE categorydescription!= ''
                        GROUP BY stockcategory.categoryid, stockcategory.categorydescription
                        ORDER BY stockcategory.categorydescription";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '. $clase.'>';
            $elemento.= "<option value='-1'>Todas las Categorias...</option>";
            
            while ($renglon= DB_fetch_array($resultado))
            {
                if (in_array($renglon["categoryid"], $valor) || $renglon["categoryid"] == $valor){
                        $elemento.= '<option selected value=' . $renglon['categoryid'] . '>' .$renglon['categorydescription']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['categoryid'] . '>' .$renglon['categorydescription']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay categorias dadas de alta...</font>";
            }
        }
        
        // Genera combo de estatus de flujos
        if ($objeto == "combo_productos_nuevos_lb")
	{
            $consulta= "SELECT DISTINCT stockmaster_bitacora_bom.parentid, stockmaster.description
                        FROM stockmaster_bitacora_bom
                        INNER JOIN stockmaster ON stockmaster_bitacora_bom.parentid= stockmaster.stockid
                        WHERE parentid IS NOT NULL";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '. $clase.'>';
            //$elemento.= "<option value='-1'>Todas las Categorias...</option>";
            
            while ($renglon= DB_fetch_array($resultado))
            {
                if (in_array($renglon["parentid"], $valor) || $renglon["parentid"] == $valor){
                        $elemento.= '<option selected value=' . $renglon['parentid'] . '>' .$renglon['parentid']. " - ".$renglon['description']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['parentid'] . '>' .$renglon['parentid']. " - ".$renglon['description']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay productos nuevos para configurar...</font>";
            }
        }
        
        // Genera combo de estatus panel 1538
        if ($objeto == "combo_estatus_1538")
	{
            $consulta= "SELECT idstatus, nombre 
                        FROM wocontrolpanel_status
                        WHERE active= 1
                        AND panelcontrol= 1538
                        ORDER BY orden";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '. $clase.'>';
            $elemento.= "<option value='-1'>Todos los Estatus...</option>";
            
            while ($renglon= DB_fetch_array($resultado))
            {
                if (in_array($renglon["idstatus"], $valor) || $renglon["idstatus"] == $valor){
                        $elemento.= '<option selected value=' . $renglon['idstatus'] . '>' .$renglon['nombre']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['idstatus'] . '>' .$renglon['nombre']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay estatus configurados...</font>";
            }
        }
        
        // Genera combo de estatus panel 1538
        if ($objeto == "combo_detalle_reporte")
	{
            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '. $clase.'>';
            
            $elemento.= "<option selected value='Yes'>" . _ ( 'X Sucursal' ) . "</option>";

            if ($valor == 'Tipodoc')
                $elemento.= "<option selected value='Tipodoc'>" . _ ( 'X Tipo Documento' ). "</option>";
            else
                $elemento.= "<option value='Tipodoc'>" . _ ( 'X Tipo Documento' ). "</option>";

            if ($valor == 'Producto')
                $elemento.= "<option selected value='Producto'>" . _ ( 'X Producto' ). "</option>";
            else
                $elemento.= "<option value='Producto'>" . _ ( 'X Producto' ). "</option>";
            
            $elemento.= '</select>';
        }
        
        // Genera combo de categorias de activo
        if ($objeto == "combo_categorias_activo")
	{
            $consulta= "SELECT categoryid, categorydescription 
                        FROM fixedassetcategories 
                        WHERE active=1
                        ORDER BY categorydescription";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '. $clase.'>';
            $elemento.= "<option value='-1'>Todas las categorias...</option>";
            
            while ($renglon= DB_fetch_array($resultado))
            {
                if (in_array($renglon["categoryid"], $valor) || $renglon["categoryid"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['categoryid'] . '>' .$renglon['categoryid']." - ".$renglon['categorydescription']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['categoryid'] . '>' .$renglon['categoryid']." - ".$renglon['categorydescription']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay estatus configurados...</font>";
            }
        }
        
        // Genera combo de activos
        if ($objeto == "combo_activos")
	{
            $consulta= "SELECT DISTINCT barcode, description 
                        FROM fixedassets
                        INNER JOIN sec_loccxusser ON fixedassets.loccode= sec_loccxusser.loccode
                        WHERE sec_loccxusser.userid= '".$_SESSION["UserID"]."'";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '. $clase.'>';
            //$elemento.= "<option value='-1'>Todas las categorias...</option>";
            
            while ($renglon= DB_fetch_array($resultado))
            {
                if (in_array($renglon["barcode"], $valor) || $renglon["barcode"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['barcode'] . '>' .$renglon['barcode']." - ".$renglon['description']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['barcode'] . '>' .$renglon['barcode']." - ".$renglon['description']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay activos dados de alta...</font>";
            }
        }
        
        // Genera combo de series activos
        if ($objeto == "combo_series_activos")
	{
            $consulta= "SELECT DISTINCT serialno, barcode, description 
                        FROM fixedassets
                        INNER JOIN sec_loccxusser ON fixedassets.loccode= sec_loccxusser.loccode
                        WHERE sec_loccxusser.userid= '".$_SESSION["UserID"]."'";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '. $clase.'>';
            //$elemento.= "<option value='-1'>Todas las categorias...</option>";
            
            while ($renglon= DB_fetch_array($resultado))
            {
                if (in_array($renglon["serialno"], $valor) || $renglon["serialno"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['serialno'] . '>' .$renglon['serialno']." - ".$renglon['barcode']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['serialno'] . '>' .$renglon['serialno']." - ".$renglon['barcode']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay activos dados de alta...</font>";
            }
        }
        
        // Genera combo de localidades de activo
        if ($objeto == "combo_localidades_activos")
	{
            $consulta= "SELECT locationid, locationdescription
                        FROM fixedassetlocations
                        INNER JOIN sec_loccxusser ON fixedassetlocations.loccode= sec_loccxusser.loccode
                        WHERE sec_loccxusser.userid= '".$_SESSION["UserID"]."' 
                        AND active= 1";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '. $clase.'>';
            //$elemento.= "<option value='-1'>Todas las categorias...</option>";
            
            while ($renglon= DB_fetch_array($resultado))
            {
                if (in_array($renglon["locationid"], $valor) || $renglon["locationid"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['locationid'] . '>' .$renglon['locationid']." - ".$renglon['locationdescription']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['locationid'] . '>' .$renglon['locationid']." - ".$renglon['locationdescription']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay localidades de activos...</font>";
            }
        }
        
        // Genera combo de estatus de activos
        if ($objeto == "combo_estatus_activos")
	{
            $consulta= "SELECT fixedassetstatusid, fixedassetstatus FROM fixedassetstatus";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '. $clase.'>';
            //$elemento.= "<option value='-1'>Todas las categorias...</option>";
            
            while ($renglon= DB_fetch_array($resultado))
            {
                if (in_array($renglon["fixedassetstatusid"], $valor) || $renglon["fixedassetstatusid"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['fixedassetstatusid'] . '>' .$renglon['fixedassetstatusid']." - ".$renglon['fixedassetstatus']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['fixedassetstatusid'] . '>' .$renglon['fixedassetstatusid']." - ".$renglon['fixedassetstatus']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay estatus configurados...</font>";
            }
        }
        
        // Genera combo de productos de tipo combustible
        if ($objeto == "combo_combustibles")
	{
            $consulta= "SELECT stockmaster.stockid, stockmaster.description
                        FROM stockmaster
                        INNER JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid 
                        WHERE stockcategory.categoryid IN ('COMBU', 'LUBRI')";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '. $clase.'>';
            $elemento.= "<option value='-1'>Todos los productos...</option>";
            
            while ($renglon= DB_fetch_array($resultado))
            {
                if (in_array($renglon["stockid"], $valor) || $renglon["stockid"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['stockid'] . '>' .$renglon['stockid']." - ".$renglon['description']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['stockid'] . '>' .$renglon['stockid']." - ".$renglon['description']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay combustibles configurados...</font>";
            }
        }
        
        // Genera combo de productos para cambio
        if ($objeto == "combo_productoscambio")
	    {
            $consulta= "SELECT stockmaster.stockid, stockmaster.description
                        FROM stockmaster
                        INNER JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid 
                        WHERE stockcategory.categoryid IN ('COMBU', 'LUBRI')";

            $resultado= DB_query($consulta, $db);

            $elemento= '<select name="'.$nombre.'" style="'.$estilo.'" '.$complemento.' '. $clase.'>';
            $elemento.= "<option value='-1'>Todos los productos...</option>";
            
            while ($renglon= DB_fetch_array($resultado))
            {
                if (in_array($renglon["stockid"], $valor) || $renglon["stockid"] == $valor){
                    $elemento.= '<option selected value=' . $renglon['stockid'] . '>' .$renglon['stockid']." - ".$renglon['description']."</option>";
                }else{
                    $elemento.= '<option value=' . $renglon['stockid'] . '>' .$renglon['stockid']." - ".$renglon['description']."</option>";
                }
            }

            $elemento.= '</select>';

            if (DB_num_rows($resultado) == 0) {
                $elemento.= "&nbsp;&nbsp;&nbsp;<font color='red' size='1'>* No hay combustibles configurados...</font>";
            }
        }
        
	return $elemento;
} 

// Objeto Vehiculo
class Vehiculo { 
	public $numerovehiculo;
	public $placa;
	public $serie;
	public $kilometraje;
	public $anio;
	public $color;
    public $idmark;
                
	function Vehiculo() {
		$this->numerovehiculo="";
		$this->placa="";
		$this->serie="";
		$this->kilometraje=0;
		$this->anio= 0;
        $this->color= "";
		$this->model= "";
        $this->idmark="";
	}
	
	// funcion para traer el vehiculo solicitado
	public function TraeVehiculo($numerovehiculo, $db){
		$consulta= "Select vehicleno, debtorno, plate, serie, lastmilage as kilometraje, color, yearvehicle,idmodel,idmark
					From vehiclesbycostumer
					Where vehicleno= '".$numerovehiculo."'";
		
		$resultado= DB_query($consulta, $db);
		
		if ($renglon= DB_fetch_array($resultado)) {
			$this->numerovehiculo= $renglon["vehicleno"];
			$this->placa= $renglon["plate"];
			$this->serie= $renglon["serie"];
			$this->kilometraje= $renglon["kilometraje"];
			$this->anio= $renglon["yearvehicle"];
			$this->color= $renglon["color"];
            $this->model= $renglon["idmodel"];
            $this->idmark=$renglon["idmark"];
		} else {
			prnMsg("No se encontro vehiculo para esa orden de venta", "info");
		}
	}
}

class ElementosGenerales {
	private $cadena_elemento;
	
	function __construct() {
		$this->cadena_elemento= "";	
	}
	
	function TraeComboDia($nombre = "", $valor= "", $estilo= "", $disabled= true, &$db) 
	{
		$habilitado= "";
		
		if (!$disabled) {
			$habilitado= "disabled";
		}
		
		$this->cadena_elemento= "<select Name='".$nombre."' style='".$estilo."' ".$habilitado.">";
		
		// Consulta para determinar los dias del mes
		$consulta= "SELECT * FROM cat_Days";
		
		$dias = DB_query($consulta, $db);
		
		while ($renglon = DB_fetch_array($dias))
		{
			$diabase=$renglon['DiaId'];
			
			if ($valor == $diabase) {
				$this->cadena_elemento.= "<option  VALUE='" . $renglon['DiaId'] . "' selected>" .$renglon['Dia'];
			}else{
				$this->cadena_elemento.= '<option  VALUE="' . $renglon['DiaId'] .  '" >' .$renglon['Dia'];
			}
		}
		
		$this->cadena_elemento.= '</select>';
		
		return $this->cadena_elemento; 
	}
}

class VentanaProcesarInfo
{
    private $mensaje;
    private $cargoestilos;
    
    function __construct() {
        $this->mensaje= "Procesando...";
        $this->cargoestilos= false;
    }
    
    function cargaEstilos () {
        $estilo= '<style type="text/css">
                    #procesando{
                            position: absolute;
                            font-family: "Lucida Grande", "Lucida Sans Unicode", Helvetica, Arial, Sans-serif;
                            z-index: 1;
                            border: 3px solid #F2F2F2;
                            border-radius: 5px;
                            background: #F2F2F2;
                            color: black;
                            width: 250px;s
                            height: 100px;
                            margin-top: auto;
                            margin-left: 50%;
                            top: 40%;
                            left: 40%;
                            text-align: center;
                            line-height: 100px;
                            font-size: 2em;
                    }

                    #procesando div
                    {
                            position: absolute;
                            font-family: "Lucida Grande", "Lucida Sans Unicode", Helvetica, Arial, Sans-serif;
                            z-index: 1;
                            border: 3px solid #F2F2F2;
                            border-radius: 5px;
                            background: #F2F2F2;
                            color: white;
                            width: 250px;
                            height: 100px;
                            margin-top: auto;
                            margin-left: 50%;
                            top: 40%;
                            left: 40%;
                            text-align: center;
                            line-height: 100px;
                            font-size: 2em;
                    }
                </style>';
        
        $this->cargoestilos= true;
        
        return $estilo;
    }
    
    function cargaProcesarOculto ($mensaje= "")
    {
        if (!$this->cargoestilos){
            $elemento= $this->cargaEstilos();
        }
        
        if (empty($mensaje)) {
            $mensaje= $this->mensaje;
        }
        
        $elemento.= '<div id="procesando" style="display:none;margin-left:auto;" ><img src="images/loading3.gif" width="40" height="40" style="vertical-align: middle;">'.$mensaje.' </div>';
        return $elemento;
    }

    function muestraProcesar (){
        $elemento= "<script>document.getElementById('procesando').style.display = 'inline';</script>";
        return $elemento;
    }
    
    function ocultaProcesar (){
        $elemento= "<script>document.getElementById('procesando').style.display = 'none';</script>";
        return $elemento;
    }
    
    function refrescar (){
        flush();
        ob_flush();
    }
}

function NumeroSemanasTieneUnAno($ano){
	$date = new DateTime;
	# Establecemos la fecha segun el estandar ISO 8601 (numero de semana)

	$date->setISODate("$ano", 53);
	# Si estamos en la semana 53 devolvemos 53, sino, es que estamos en la 52

	if($date->format("W")=="53")
		return 53;
	else
		return 52;
}

function traeHora($valor){
    $listaDeHoras= array();
    $listaDeHoras[0] = '00:00';
    $listaDeHoras[1] = '05:00';
    $listaDeHoras[2] = '05:30';
    $listaDeHoras[3] = '06:00';
    $listaDeHoras[4] = '06:30';
    $listaDeHoras[5] = '07:00';
    $listaDeHoras[6] = '07:30';
    $listaDeHoras[7] = '08:00';
    $listaDeHoras[8] = '08:30';
    $listaDeHoras[9] = '09:00';
    $listaDeHoras[10] = '09:15';
    $listaDeHoras[11] = '09:30';
    $listaDeHoras[12] = '09:45';
    $listaDeHoras[13] = '10:00';
    $listaDeHoras[14] = '10:15';
    $listaDeHoras[15] = '10:30';
    $listaDeHoras[16] = '10:45';
    $listaDeHoras[17] = '11:00';
    $listaDeHoras[18] = '11:15';
    $listaDeHoras[19] = '11:30';
    $listaDeHoras[20] = '11:45';
    $listaDeHoras[21] = '12:00';
    $listaDeHoras[22] = '12:15';
    $listaDeHoras[23] = '12:30';
    $listaDeHoras[24] = '12:45';
    $listaDeHoras[25] = '13:00';
    $listaDeHoras[26] = '13:15';
    $listaDeHoras[27] = '13:30';
    $listaDeHoras[28] = '13:45';
    $listaDeHoras[29] = '14:00';
    $listaDeHoras[30] = '14:15';
    $listaDeHoras[31] = '14:30';
    $listaDeHoras[32] = '14:45';
    $listaDeHoras[33] = '15:00';
    $listaDeHoras[34] = '15:15';
    $listaDeHoras[35] = '15:30';
    $listaDeHoras[36] = '15:45';
    $listaDeHoras[37] = '16:00';
    $listaDeHoras[38] = '16:15';
    $listaDeHoras[39] = '16:30';
    $listaDeHoras[40] = '16:45';
    $listaDeHoras[41] = '17:00';
    $listaDeHoras[42] = '17:15';
    $listaDeHoras[43] = '17:30';
    $listaDeHoras[44] = '17:45';
    $listaDeHoras[45] = '18:00';
    $listaDeHoras[46] = '18:15';
    $listaDeHoras[47] = '18:30';
    $listaDeHoras[48] = '18:45';
    $listaDeHoras[49] = '19:00';
    $listaDeHoras[50] = '19:15';
    $listaDeHoras[51] = '19:30';
    $listaDeHoras[52] = '19:45';
    $listaDeHoras[53] = '20:00';
    $listaDeHoras[54] = '20:15';
    $listaDeHoras[55] = '20:30';
    $listaDeHoras[56] = '20:45';
    $listaDeHoras[57] = '21:00';
    $listaDeHoras[58] = '21:15';
    $listaDeHoras[59] = '21:30';
    $listaDeHoras[60] = '21:45';
    $listaDeHoras[61] = '22:00';
    $listaDeHoras[62] = '22:30';
    $listaDeHoras[63] = '23:00';
    $listaDeHoras[64] = '23:30';
    
    return $listaDeHoras[$valor];
}

function restaTiempo($inicio, $fin)
{
  $dif= date("H:i:s", strtotime("00:00:00") + strtotime($fin) - strtotime($inicio) );
  return $dif;
}

function getHours($startDate, $endDate) {
    $startDate = strtotime ( $startDate );
    $endDate = strtotime ( $endDate );
    $diffSeconds = $endDate - $startDate;
    return number_format ( $diffSeconds / 60 / 60, 2, '.', '' );
}

// funcion para enviar mensaje a pantalla con formato
/**
 * 
 * @param type $strMensaje - cadena donde se envia el mensaje a pantalla
 * @param type $tipo - tipo de alerta (1-exito, 2-info, 3-alerta, 4-error)
 * @return string - Regresa el elemento de tipo DIV con el mensaje
 */
function mensajePantalla($strMensaje, $tipo){
    switch ($tipo) {
        case 1:
            $tipo_alerta= "alert-success";
            break;
        case 2:
            $tipo_alerta= "alert-info";
            break;
        case 3:
            $tipo_alerta= "alert-warning";
            break;
        case 4:
            $tipo_alerta= "alert-danger";
            break;

        default:
            break;
    }
    $mensaje= '<div class="alert '.$tipo_alerta.' alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    '.$strMensaje.'
                </div>';
    
    return $mensaje;
}

?>

<style>
    
.Encabezado01 
{
    background-color: #1F497D;
    color: white;
    font-size: 14px;
    font-weight: bold;
}

.Encabezado02
{
    background-color: #4F81BD;
    color: white;
    font-weight: bold;
}    

.Encabezado03
{
    background-color: #CEE3F6;
    color: #0B0B61;
    font-weight: bold;
}   

.PieTabla01
{
    background-color: #F2F2F2;
    color: black; 
    border-bottom: 5px solid #CCCCCC;
}

.PieTabla02
{
    background-color: #F2F2F2;
    color: black; 
    font-weight: bold;
    border-top: 1px solid #CCCCCC;
    border-bottom: 5px solid #CCCCCC;
}

.boton01 {
  background: #F7D358;
  background-image: -webkit-linear-gradient(top, #F7D358, #F3E2A9);
  background-image: -moz-linear-gradient(top, #F7D358, #F3E2A9);
  background-image: -ms-linear-gradient(top, #F7D358, #F3E2A9);
  background-image: -o-linear-gradient(top, #F7D358, #F3E2A9);
  background-image: linear-gradient(to bottom, #F7D358, #F3E2A9);
  -webkit-border-radius: 5;
  -moz-border-radius: 5;
  border-radius: 5px;
  font-family: Arial;
  color: black;
  font-size: 12px;
  padding: 3px 6px 3px 6px;
  text-decoration: none;
}

.boton01:hover {
  background: #3cb0fd;
  background-image: -webkit-linear-gradient(top, #F2F2F2, #F2F2F2);
  background-image: -moz-linear-gradient(top, #F2F2F2, #F2F2F2);
  background-image: -ms-linear-gradient(top, #F2F2F2, #F2F2F2);
  background-image: -o-linear-gradient(top, #F2F2F2, #F2F2F2);
  background-image: linear-gradient(to bottom, #F2F2F2, #F2F2F2);
  text-decoration: none;
  color: black;
}
/*
.boton02 {
  background: #366ce5;
  background-image: -webkit-linear-gradient(top, #366ce5, #366ce5);
  background-image: -moz-linear-gradient(top, #366ce5, #366ce5);
  background-image: -ms-linear-gradient(top, #366ce5, #366ce5);
  background-image: -o-linear-gradient(top, #366ce5, #366ce5);
  background-image: linear-gradient(to bottom, #366ce5, #366ce5);
  -webkit-border-radius: 5;
  -moz-border-radius: 5;
  border-radius: 5px;
  font-family: Arial;
  color: white;
  font-size: 12px;
  padding: 3px 6px 3px 6px;
  text-decoration: none;
}
*/
.boton02:hover {
  background: #b2b2b2;
  background-image: -webkit-linear-gradient(top, #F2F2F2, #F2F2F2);
  background-image: -moz-linear-gradient(top, #F2F2F2, #F2F2F2);
  background-image: -ms-linear-gradient(top, #F2F2F2, #F2F2F2);
  background-image: -o-linear-gradient(top, #F2F2F2, #F2F2F2);
  background-image: linear-gradient(to bottom, #F2F2F2, #F2F2F2);
  text-decoration: none;
  color: black;
}

.boton02 {
  background: #056693;
  background-image: -webkit-linear-gradient(top, #056693, #056693);
  background-image: -moz-linear-gradient(top, #056693, #056693);
  background-image: -ms-linear-gradient(top, #056693, #056693);
  background-image: -o-linear-gradient(top, #056693, #056693);
  background-image: linear-gradient(to bottom, #056693, #056693);
  -webkit-border-radius: 5;
  -moz-border-radius: 5;
  border-radius: 5px;
  font-family: Arial;
  color: white;
  font-size: 12px;
  padding: 3px 6px 3px 6px;
  text-decoration: none;
}

.tabla_01 {
    border: 1px solid lightgray;
    border-collapse: collapse; /* IE7 and lower */
    border-spacing: 0;
}

.encabezado10{
    color:white;
    font-size:12.0pt;
    font-weight:700;
    font-family:Calibri;
    mso-generic-font-family:auto;
    mso-font-charset:0;
    vertical-align:middle;
    border-top:.5pt solid #D9D9D9;
    border-right:none;
    border-bottom:none;
    border-left:.5pt solid #D9D9D9;
    background:#1F497D;
    mso-pattern:black none;
} 

.encabezado11{
    color:white;
    font-size:12.0pt;
    font-weight:700;
    font-family:Calibri;
    mso-generic-font-family:auto;
    mso-font-charset:0;
    vertical-align:middle;
    border-top:none;
    border-right:none;
    border-left:.5pt solid #D9D9D9;
    background:#4F81BD;
    mso-pattern:black none;
} 

.encabezado12 {
    color:#1F497D;
    font-weight:700;
    font-family:Calibri;
    mso-generic-font-family:auto;
    mso-font-charset:0;
    vertical-align:middle;
    background:#DCE6F1;
    mso-pattern:black none;
}

.encabezado13 {
    color:black;
    font-size: 10pt;
    font-weight:700;
    font-family:Calibri;
    mso-generic-font-family:auto;
    mso-font-charset:0;
    vertical-align:middle;
    border-top:.5pt solid #D9D9D9;
    border-right:none;
    border-bottom:.5pt solid #D9D9D9;
    border-left:none;
    background:#F2F2F2;
    mso-pattern:black none;
}

.encabezado14 {
    color:black;
    font-family:Calibri;
    mso-generic-font-family:auto;
    mso-font-charset:0;
    vertical-align:middle;
    border-top:.5pt solid #D9D9D9;
    border-right:none;
    border-bottom:.5pt solid #D9D9D9;
    border-left:none;
    background:#F2F2F2;
    mso-pattern:black none;
}

.encabezado15 {
    color:#1F497D;
    font-family:Calibri;
    mso-generic-font-family:auto;
    mso-font-charset:0;
    text-align:center;
    vertical-align:middle;
    background:#DCE6F1;
    mso-pattern:black none;
}

.expandir01 {
    color:black;
    font-family:Calibri;
    mso-generic-font-family:auto;
    mso-font-charset:0;
    text-align:center;
    vertical-align:middle;
    border-top:.5pt solid #D9D9D9;
    border-right:none;
    border-bottom:.5pt solid #D9D9D9;
    border-left:.5pt solid #D9D9D9;
    background:#BFBFBF;
    mso-pattern:black none;
}

.th01:first-child {
    border-radius: 6px 0 0 0;
}

.th01:last-child {
    border-radius: 0 6px 0 0;
}

.th01:only-child{
    border-radius: 6px 6px 0 0;
}

.bordered tr:hover {
  background: #fbf8e9;
  transition: all 0.1s ease-in-out;     
}

.highlight {
  background: #fbf8e9;
  transition: all 0.1s ease-in-out;     
}

.container-fluid {
    padding: 0px 20px;
    text-align: center;
}

.bg-grey {
    background-color: #f6f6f6;
}
  
#row{text-align: center; width: 100%;}

.col-sm-7{width:58.33333333%}

.col-sm-6{width:60%}

.form-group{margin-bottom:5px}

.form-control{display:block; width:100%; height:20px; padding:6px 12px; font-size:12px;line-height:1.42857143;color:#555;background-color:#fff;background-image:none;border:1px solid #ccc;border-radius:4px;-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,.075);box-shadow:inset 0 1px 1px rgba(0,0,0,.075);-webkit-transition:border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;-o-transition:border-color ease-in-out .15s,box-shadow ease-in-out .15s;transition:border-color ease-in-out .15s, box-shadow ease-in-out .15s}

.select_01{height: 20px; font-family: verdana; font-size: 11px; color: #555; background-color:white; border: 1px solid lightgray;}

.visibles {
    visibility: visible;
    opacity: 1;
    transition: opacity 1s linear;
}

.ocultos {
    visibility: hidden;
    opacity: 0;
    transition: visibility 0s 1s, opacity 1s linear;
}
</style>

<script language="javascript">
    
function muestra(){
    //Función para mostrar el div que contiene la imagen Loading
    div = document.getElementById("myDivLoading");
    div.style.display= "block";
    div.className = "visibles";
}

function oculta(){
    //Función para ocultar el div que contiene la imagen Loading
    div = document.getElementById("myDivLoading");
    div.style.display= "none";
    div.className = "ocultos";
}

</script>