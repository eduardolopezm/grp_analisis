//***************************
//Funciones generales, accesibles desde cualquier página
//FECHA: 16/01/2001
//***************************


function comprobar_formato_fecha(fecha)
	{
	 var longfecha=fecha.length;
	 var caracterseparador=fecha.charAt(4);
	 var poscarsep = fecha.indexOf(caracterseparador);
	 if (poscarsep==-1){
	 return (0);
	 }else{
	   var anio=fecha.substring(0,poscarsep);
	   if((anio<1920)||(anio>2050)){
	  return(0);
	   }// cierre if((annio<1900)||(annio>2100))
	   var poscarsepj = fecha.indexOf(caracterseparador,poscarsep+1);
	  if ((poscarsepj==-1)||(poscarsepj!=7)){
		return(0);
		}else{
		 var mes=fecha.substring(poscarsep+1,poscarsepj);
		 if((mes<0)||(mes>12)){
		 return(0);
		 }//cierre if((mes<0)||(mes>12))
		 var dia=fecha.substring(poscarsepj+1,longfecha);
			 for (var i=0;i<2;i++)
			 {
				j=dia.substring(i,i+1);
				if(j==""){return (0);}
			 }
		 if((dia<1)||(dia>31)){
		 return(0);
		 }//cierre if((dia<1)||(dia>31))
		}//cierre if (poscarsepj==-1)
	 }//cierre if (poscarsep==-1)
	 return(1);
	
}//cierre funcion




 //***************************
//Funciones de la pagina de alta de usuarios alta_usuarios_nuevos.jsp
//FECHA: 28/11/2000
//***************************

function filtrar_alta_user(numero){
					
		if (numero == 1)
		{

			document.list_anidadas.pais.value = document.alta_user.STD_ID_COUNTRY.options[document.alta_user.STD_ID_COUNTRY.selectedIndex].value;
			document.list_anidadas.com.value = "";
			document.list_anidadas.prov.value = "";
			document.list_anidadas.place.value = "";
			document.list_anidadas.pais1.value = document.alta_user.STD_ID_COUNTRY_1.options[document.alta_user.STD_ID_COUNTRY_1.selectedIndex].value;
			document.list_anidadas.com1.value = document.alta_user.STD_ID_GEO_DIV_1.options[document.alta_user.STD_ID_GEO_DIV_1.selectedIndex].value;
			document.list_anidadas.prov1.value = document.alta_user.STD_ID_SUB_GEO_DIV_1.options[document.alta_user.STD_ID_SUB_GEO_DIV_1.selectedIndex].value;
			document.list_anidadas.place1.value = document.alta_user.STD_ID_GEO_PLACE_1.options[document.alta_user.STD_ID_GEO_PLACE_1.selectedIndex].value;
			document.list_anidadas.lis.value = 1;
		}
		if (numero == 2)
		{

			document.list_anidadas.pais.value = document.alta_user.STD_ID_COUNTRY.options[document.alta_user.STD_ID_COUNTRY.selectedIndex].value;
			document.list_anidadas.com.value = document.alta_user.STD_ID_GEO_DIV.options[document.alta_user.STD_ID_GEO_DIV.selectedIndex].value;
			document.list_anidadas.prov.value = "";
			document.list_anidadas.place.value = "";
			document.list_anidadas.pais1.value = document.alta_user.STD_ID_COUNTRY_1.options[document.alta_user.STD_ID_COUNTRY_1.selectedIndex].value;
			document.list_anidadas.com1.value = document.alta_user.STD_ID_GEO_DIV_1.options[document.alta_user.STD_ID_GEO_DIV_1.selectedIndex].value;
			document.list_anidadas.prov1.value = document.alta_user.STD_ID_SUB_GEO_DIV_1.options[document.alta_user.STD_ID_SUB_GEO_DIV_1.selectedIndex].value;
			document.list_anidadas.place1.value = document.alta_user.STD_ID_GEO_PLACE_1.options[document.alta_user.STD_ID_GEO_PLACE_1.selectedIndex].value;
			document.list_anidadas.lis.value = 1;
		}

		if (numero == 3)
		{

			document.list_anidadas.pais.value = document.alta_user.STD_ID_COUNTRY.options[document.alta_user.STD_ID_COUNTRY.selectedIndex].value;
			document.list_anidadas.com.value = document.alta_user.STD_ID_GEO_DIV.options[document.alta_user.STD_ID_GEO_DIV.selectedIndex].value;
			document.list_anidadas.prov.value = document.alta_user.STD_ID_SUB_GEO_DIV.options[document.alta_user.STD_ID_SUB_GEO_DIV.selectedIndex].value;
			document.list_anidadas.place.value = "";
			document.list_anidadas.pais1.value = document.alta_user.STD_ID_COUNTRY_1.options[document.alta_user.STD_ID_COUNTRY_1.selectedIndex].value;
			document.list_anidadas.com1.value = document.alta_user.STD_ID_GEO_DIV_1.options[document.alta_user.STD_ID_GEO_DIV_1.selectedIndex].value;
			document.list_anidadas.prov1.value = document.alta_user.STD_ID_SUB_GEO_DIV_1.options[document.alta_user.STD_ID_SUB_GEO_DIV_1.selectedIndex].value;
			document.list_anidadas.place1.value = document.alta_user.STD_ID_GEO_PLACE_1.options[document.alta_user.STD_ID_GEO_PLACE_1.selectedIndex].value;
			document.list_anidadas.lis.value = 1;

		}
		if (numero == 7)
		{

			document.list_anidadas.pais.value = document.alta_user.STD_ID_COUNTRY.options[document.alta_user.STD_ID_COUNTRY.selectedIndex].value;
			document.list_anidadas.com.value = document.alta_user.STD_ID_GEO_DIV.options[document.alta_user.STD_ID_GEO_DIV.selectedIndex].value;
			document.list_anidadas.prov.value = document.alta_user.STD_ID_SUB_GEO_DIV.options[document.alta_user.STD_ID_SUB_GEO_DIV.selectedIndex].value;
			document.list_anidadas.place.value = document.alta_user.STD_ID_GEO_PLACE.options[document.alta_user.STD_ID_GEO_PLACE.selectedIndex].value;
			document.list_anidadas.pais1.value = document.alta_user.STD_ID_COUNTRY_1.options[document.alta_user.STD_ID_COUNTRY_1.selectedIndex].value;
			document.list_anidadas.com1.value = document.alta_user.STD_ID_GEO_DIV_1.options[document.alta_user.STD_ID_GEO_DIV_1.selectedIndex].value;
			document.list_anidadas.prov1.value = document.alta_user.STD_ID_SUB_GEO_DIV_1.options[document.alta_user.STD_ID_SUB_GEO_DIV_1.selectedIndex].value;
			document.list_anidadas.place1.value = document.alta_user.STD_ID_GEO_PLACE_1.options[document.alta_user.STD_ID_GEO_PLACE_1.selectedIndex].value;
			document.list_anidadas.lis.value = 1;

		}


		if (numero == 4)
		{

			document.list_anidadas.pais1.value = document.alta_user.STD_ID_COUNTRY_1.options[document.alta_user.STD_ID_COUNTRY_1.selectedIndex].value;
			document.list_anidadas.com1.value = "";
			document.list_anidadas.prov1.value = "";
			document.list_anidadas.place1.value = "";
//			document.list_anidadas.pais.value = document.alta_user.STD_ID_COUNTRY.options[document.alta_user.STD_ID_COUNTRY.selectedIndex].value;
//			document.list_anidadas.com.value = document.alta_user.STD_ID_GEO_DIV.options[document.alta_user.STD_ID_GEO_DIV.selectedIndex].value;
//			document.list_anidadas.prov.value = document.alta_user.STD_ID_SUB_GEO_DIV.options[document.alta_user.STD_ID_SUB_GEO_DIV.selectedIndex].value;
//			document.list_anidadas.place.value = document.alta_user.STD_ID_GEO_PLACE.options[document.alta_user.STD_ID_GEO_PLACE.selectedIndex].value;
			document.list_anidadas.pais.value = document.alta_user.STD_ID_COUNTRY.value;
			document.list_anidadas.com.value = document.alta_user.STD_ID_GEO_DIV.value;;
			document.list_anidadas.prov.value = document.alta_user.STD_ID_SUB_GEO_DIV.value;;
			document.list_anidadas.place.value = document.alta_user.STD_ID_GEO_PLACE.value;;
			document.list_anidadas.lis.value = 2;
		}
		if (numero == 5)
		{

			document.list_anidadas.pais1.value = document.alta_user.STD_ID_COUNTRY_1.options[document.alta_user.STD_ID_COUNTRY_1.selectedIndex].value;
			document.list_anidadas.com1.value = document.alta_user.STD_ID_GEO_DIV_1.options[document.alta_user.STD_ID_GEO_DIV_1.selectedIndex].value;
			document.list_anidadas.prov1.value = "";
			document.list_anidadas.place1.value = "";
			document.list_anidadas.pais.value = document.alta_user.STD_ID_COUNTRY.options[document.alta_user.STD_ID_COUNTRY.selectedIndex].value;
			document.list_anidadas.com.value = document.alta_user.STD_ID_GEO_DIV.options[document.alta_user.STD_ID_GEO_DIV.selectedIndex].value;
			document.list_anidadas.prov.value = document.alta_user.STD_ID_SUB_GEO_DIV.options[document.alta_user.STD_ID_SUB_GEO_DIV.selectedIndex].value;
			document.list_anidadas.place.value = document.alta_user.STD_ID_GEO_PLACE.options[document.alta_user.STD_ID_GEO_PLACE.selectedIndex].value;
			document.list_anidadas.lis.value = 2;
		}
		if (numero == 6)
		{

			document.list_anidadas.pais1.value = document.alta_user.STD_ID_COUNTRY_1.options[document.alta_user.STD_ID_COUNTRY_1.selectedIndex].value;
			document.list_anidadas.com1.value = document.alta_user.STD_ID_GEO_DIV_1.options[document.alta_user.STD_ID_GEO_DIV_1.selectedIndex].value;
			document.list_anidadas.prov1.value = document.alta_user.STD_ID_SUB_GEO_DIV_1.options[document.alta_user.STD_ID_SUB_GEO_DIV_1.selectedIndex].value;
			document.list_anidadas.place1.value = "";
			document.list_anidadas.pais.value = document.alta_user.STD_ID_COUNTRY.options[document.alta_user.STD_ID_COUNTRY.selectedIndex].value;
			document.list_anidadas.com.value = document.alta_user.STD_ID_GEO_DIV.options[document.alta_user.STD_ID_GEO_DIV.selectedIndex].value;
			document.list_anidadas.prov.value = document.alta_user.STD_ID_SUB_GEO_DIV.options[document.alta_user.STD_ID_SUB_GEO_DIV.selectedIndex].value;
			document.list_anidadas.place.value = document.alta_user.STD_ID_GEO_PLACE.options[document.alta_user.STD_ID_GEO_PLACE.selectedIndex].value;
			document.list_anidadas.lis.value = 2;
		}

		if (numero == 8)
		{

			document.list_anidadas.pais1.value = document.alta_user.STD_ID_COUNTRY_1.options[document.alta_user.STD_ID_COUNTRY_1.selectedIndex].value;
			document.list_anidadas.com1.value = document.alta_user.STD_ID_GEO_DIV_1.options[document.alta_user.STD_ID_GEO_DIV_1.selectedIndex].value;
			document.list_anidadas.prov1.value = document.alta_user.STD_ID_SUB_GEO_DIV_1.options[document.alta_user.STD_ID_SUB_GEO_DIV_1.selectedIndex].value;
			document.list_anidadas.place1.value = document.alta_user.STD_ID_GEO_PLACE_1.options[document.alta_user.STD_ID_GEO_PLACE_1.selectedIndex].value;
			document.list_anidadas.pais.value = document.alta_user.STD_ID_COUNTRY.options[document.alta_user.STD_ID_COUNTRY.selectedIndex].value;
			document.list_anidadas.com.value = document.alta_user.STD_ID_GEO_DIV.options[document.alta_user.STD_ID_GEO_DIV.selectedIndex].value;
			document.list_anidadas.prov.value = document.alta_user.STD_ID_SUB_GEO_DIV.options[document.alta_user.STD_ID_SUB_GEO_DIV.selectedIndex].value;
			document.list_anidadas.place.value = document.alta_user.STD_ID_GEO_PLACE.options[document.alta_user.STD_ID_GEO_PLACE.selectedIndex].value;
			document.list_anidadas.lis.value = 2;
		}

			document.list_anidadas.docum.value = document.alta_user.SSP_ID_TP_DOC.value;
			document.list_anidadas.nom.value= document.alta_user.nombre.value;
			document.list_anidadas.ape.value = document.alta_user.apellido.value;
			document.list_anidadas.ape2.value = document.alta_user.apellido_2.value;
			document.list_anidadas.nif.value = document.alta_user.nif.value;
			//document.list_anidadas.sig.value = document.alta_user.SSP_ID_SIGLA_DOMIC.value;
			document.list_anidadas.dir.value = document.alta_user.STD_ADDRESS_LINE_1.value;
			document.list_anidadas.codig.value = document.alta_user.SSP_DISTRIT_POSTAL.value;
			document.list_anidadas.bloq.value = document.alta_user.SSP_BLOQUE.value;
			//document.list_anidadas.escal.value = document.alta_user.SSP_ESCALERA.value;
			document.list_anidadas.num.value = document.alta_user.SSP_NUM_VIA.value;
			document.list_anidadas.piso.value = document.alta_user.SSP_PISO.value;
			//document.list_anidadas.puerta.value = document.alta_user.SSP_PUERTA.value;
			document.list_anidadas.t_linea.value = document.alta_user.STD_ID_LINE_TYPE.value;
			document.list_anidadas.loc.value = document.alta_user.STD_ID_LOCATION_TYPE.value;
			document.list_anidadas.loc1.value = document.alta_user.STD_ID_LOCATION_TYPE_1.value;
			document.list_anidadas.nac.value = document.alta_user.SCO_DT_BIRTH.value;
			document.list_anidadas.corr.value = document.alta_user.EMAIL.value;
	                document.list_anidadas.corr_alter.value = document.alta_user.EMAIL_ALTER.value; 	
                	document.list_anidadas.tel.value = document.alta_user.TELEFONO.value;
			list_anidadas.submit();
	}

		function comprobar_espacios(formulario,elemento)
		{
		 var dato_comprobar = document.forms[formulario].elements[elemento].value;
		 var long_dato_comprobar = dato_comprobar.length;
		 var indicador = 0;
		 for (i=0; i<long_dato_comprobar;i++)
		  {
 		   var valor = dato_comprobar.charAt(i)
		   if (valor != " ")
		    indicador = 1;
		  }
         if (indicador == 0)
		  return(0);
		 else
		  return(1);
		}

		function comprobar_alta_user()
		{

		   var pais_nulo = comprobar_espacios("alta_user","STD_ID_COUNTRY")
			if (pais_nulo == 0)
			{
			alert(msg_1);
			//document.alta_user.STD_ID_COUNTRY.focus();
			return false;
			}

		   var geo_div_nulo = comprobar_espacios("alta_user","STD_ID_GEO_DIV")
			if (geo_div_nulo == 0)
			{
			alert(msg_2);
			//document.alta_user.STD_ID_GEO_DIV.focus();
			return false;
			}
// Cambio para que no valide la delegación de nacimiento
//		   var sub_div_nulo = comprobar_espacios("alta_user","STD_ID_SUB_GEO_DIV")
//			if (sub_div_nulo == 0)
//			{
//			alert(msg_3);
			//document.alta_user.STD_ID_SUB_GEO_DIV.focus();
//			return false;
//			}
			
			var rfc_nulo = comprobar_espacios("alta_user","STD_SS_NUMBER")
			if (rfc_nulo == 0)
			{
			alert(msg_101);
			document.alta_user.STD_SS_NUMBER.focus();
			return false;
			}
			

//		    var geo_place_nulo = comprobar_espacios("alta_user","STD_ID_GEO_PLACE")
//			if (geo_place_nulo == 0)
//			{
//			alert(msg_4);
			//document.alta_user.STD_ID_GEO_PLACE.focus();
//			return false;
//			}

		   var pais_nulo_1 = comprobar_espacios("alta_user","STD_ID_COUNTRY_1")
			if (pais_nulo_1 == 0)
			{
			alert(msg_5);
			//document.alta_user.STD_ID_COUNTRY_1.focus();
			return false;
			}

		   var geo_div_nulo_1 = comprobar_espacios("alta_user","STD_ID_GEO_DIV_1")
			if (geo_div_nulo_1 == 0)
			{
			alert(msg_6);
			//document.alta_user.STD_ID_GEO_DIV_1.focus();
			return false;
			}

		   var sub_div_nulo_1 = comprobar_espacios("alta_user","STD_ID_SUB_GEO_DIV_1")
			if (sub_div_nulo_1 == 0)
			{
			alert(msg_7);
			//document.alta_user.STD_ID_SUB_GEO_DIV_1.focus();
			return false;
			}

//		    var geo_place_nulo_1 = comprobar_espacios("alta_user","STD_ID_GEO_PLACE_1")
//			if (geo_place_nulo_1 == 0)
//			{
//			alert(msg_8);
			//document.alta_user.STD_ID_GEO_PLACE_1.focus();
//			return false;
//			}

		   var nombre_nulo = comprobar_espacios("alta_user","nombre")
			if (nombre_nulo == 0)
			{
			alert(msg_9);
			document.alta_user.nombre.focus();
			return false;
			}

		   var apellido_nulo = comprobar_espacios("alta_user","apellido")
			if (apellido_nulo == 0)
			{
			alert(msg_10);
			document.alta_user.apellido.focus();
			return false;
			}

		   
	var hoy = new Date();
	var mes_hoy = hoy.getMonth() + 1;
	var dia_hoy = hoy.getDate();
	//var anio_hoy = hoy.getYear()-16;
	var anio_hoy = hoy.getFullYear()-16;
	if (mes_hoy < "9") mes_hoy = "0" + mes_hoy;
	if (dia_hoy < "9") dia_hoy = "0" + dia_hoy;
	fecha_hoy = anio_hoy + "-" + mes_hoy + "-" + dia_hoy;
	var nac_nulo = comprobar_espacios("alta_user","SCO_DT_BIRTH")
	if (nac_nulo == 0)
	{
		alert(msg_12);
		document.alta_user.SCO_DT_BIRTH.focus();
		return false;
		}else{
			fecha_nacimiento = comprobar_formato_fecha(document.alta_user.SCO_DT_BIRTH.value)
				if (fecha_nacimiento == 0)
				{
				alert(msg_13);
				document.alta_user.SCO_DT_BIRTH.focus();
				return false;
					}else{
						var fec_nac      =  document.alta_user.SCO_DT_BIRTH.value;
						var rfcdia       =  fec_nac.substring(8,10);
						var rfcmes       =  fec_nac.substring(5,7);
						var rfcanio      =  fec_nac.substring(2,4);
						var fecha_rfcnac_correcta = rfcanio + rfcmes + rfcdia;
						var rfc          =  document.alta_user.STD_SS_NUMBER.value;
						var dt_capturada =  rfc.substring(4,10);
						if (fecha_hoy <= fec_nac)
						{
							alert(msg_14);	
							document.alta_user.SCO_DT_BIRTH.focus();
							return false;
						}//cierre if (fecha_hoy <= fecha_nacimiento)
						if(fecha_rfcnac_correcta != dt_capturada  )
						{
							alert("La fecha capturada no corresponde a la de su RFC");
							document.alta_user.SCO_DT_BIRTH.focus();
							return false;
						}//cierre if (fecha_nacimiento !=document.alta_user.STD_SS_NUMBER.substring(5,4))
				}//cierre if (fecha_nacimiento == 0)
	}//cierre if (nac_nulo == 0)

		   var calle_nulo = comprobar_espacios("alta_user","STD_ADDRESS_LINE_1")
			if (calle_nulo == 0)
			{
			alert(msg_15);
			document.alta_user.STD_ADDRESS_LINE_1.focus();
			return false;
			}
			var numero_nulo = comprobar_espacios("alta_user","SSP_NUM_VIA")
			if (numero_nulo == 0)
			{
			alert(msg_16);
			document.alta_user.SSP_NUM_VIA.focus();
			return false;
			}
	var codigo_nulo = comprobar_espacios("alta_user","SSP_DISTRIT_POSTAL")
	if (codigo_nulo == 0)
	{
		alert(msg_17);
		document.alta_user.SSP_DISTRIT_POSTAL.focus();
		return false;
		}else{
		cod_postal = document.alta_user.SSP_DISTRIT_POSTAL.value;
		if (isNaN(cod_postal))
		{
			alert(msg_18);
			document.alta_user.SSP_DISTRIT_POSTAL.focus();
			return false;
		}else{
			num_dig_cod_postal = cod_postal.length;
			if (num_dig_cod_postal!= 5)
			{
				alert(msg_19);
				document.alta_user.SSP_DISTRIT_POSTAL.focus();
				return false;
			}//cierre if (num_dig_cod_postal!= 5)
		}//cierre if (isNaN(cod_postal))
	}//cierre if (codigo_nulo == 0)
	var tel_nulo = comprobar_espacios("alta_user","TELEFONO")
	if (tel_nulo == 0)
	{
	alert(msg_20);
	document.alta_user.TELEFONO.focus();
	return false;
	}else{
	var tel_valid = document.alta_user.TELEFONO.value;
			var l_tel_valid = tel_valid.length;
			var indicador_tel = 0;
			var numeros = "0123456789";
			for (idx_tel=0; idx_tel<l_tel_valid; idx_tel++)
			{
	 		   var valor_tel = tel_valid.charAt(idx_tel);
			   if (valor_tel == " ")
			   {
			    alert(msg_21)
				document.alta_user.TELEFONO.focus();
				return false;
			   }
				if (-1 == numeros.indexOf(valor_tel))
				 indicador_tel = 1;
			}
			if (indicador_tel == 1)
		    {
			    alert(msg_22)
				document.alta_user.TELEFONO.focus();
				return false;
			}
	}//cierre if (tel_nulo == 0)

		   var user_nulo = comprobar_espacios("alta_user","usuario")
			if (user_nulo == 0)
			{
			alert(msg_26);
			document.alta_user.usuario.focus();
			return false;
			}

		   var cont_nulo = comprobar_espacios("alta_user","contrasenia")
			if (cont_nulo == 0)
			{
			alert(msg_27);
			document.alta_user.contrasenia.focus();
			return false;
			}

		   var cont_conf_nulo = comprobar_espacios("alta_user","contrasenia_conf")
			if (cont_conf_nulo == 0)
			{
			alert(msg_28);
			document.alta_user.contrasenia_conf.focus();
			return false;
			}else{
				if (document.alta_user.contrasenia.value != document.alta_user.contrasenia_conf.value)
			{
				alert(msg_29);
				document.alta_user.contrasenia_conf.focus();
				return false;
  		    }
			
			
			}
/*
		   var pre_nulo = comprobar_espacios("alta_user","pregunta")
			if (pre_nulo == 0)
			{
			alert(msg_30);
			document.alta_user.pregunta.focus();
			return false;
			}

		   var re_nulo = comprobar_espacios("alta_user","respuesta")
			if (re_nulo == 0)
			{
			alert(msg_31);
			document.alta_user.respuesta.focus();
			return false;
			}
*/
			 return true;

		}
//////////////////////////////////////////////////
	function comprobar_cambio_contrasena()
	{		  
        var cont_nulo = comprobar_espacios("cambio_contrasena","contrasenia")
		if (cont_nulo == 0)
		{
			alert(msg_27);
			document.cambio_contrasena.contrasenia.focus();
			return;
		}

		var cont_conf_nulo = comprobar_espacios("cambio_contrasena","contrasenia_conf")
		if (cont_conf_nulo == 0)
			{
			alert(msg_28);
			document.cambio_contrasena.contrasenia_conf.focus();
			return;
		}
		else
		{
			if (document.cambio_contrasena.contrasenia.value != document.cambio_contrasena.contrasenia_conf.value)
			{
				alert(msg_29);
				document.cambio_contrasena.contrasenia_conf.focus();
				return;
  		    }					
		}				
		cambio_contrasena.submit();
	}
		
//***************************
//Funciones de la pagina de datos direccion js_datos_direccion.jsp
//FECHA: 28/11/2000
//***************************

	function filtrar_datos_direccion(numero){

	if (numero==1)
	document.list_anidadas.pais.value = document.alta_user.STD_ID_COUNTRY.options[document.alta_user.STD_ID_COUNTRY.selectedIndex].value;

	if (numero==2)
	{
	document.list_anidadas.pais.value = document.alta_user.STD_ID_COUNTRY.options[document.alta_user.STD_ID_COUNTRY.selectedIndex].value;

	document.list_anidadas.com.value = 
	document.alta_user.STD_ID_GEO_DIV.options[document.alta_user.STD_ID_GEO_DIV.selectedIndex].value;
	}
	if (numero==3)
	{
	document.list_anidadas.pais.value = document.alta_user.STD_ID_COUNTRY.options[document.alta_user.STD_ID_COUNTRY.selectedIndex].value;

	document.list_anidadas.com.value = 
	document.alta_user.STD_ID_GEO_DIV.options[document.alta_user.STD_ID_GEO_DIV.selectedIndex].value;

	document.list_anidadas.prov.value = 
	document.alta_user.STD_ID_SUB_GEO_DIV.options[document.alta_user.STD_ID_SUB_GEO_DIV.selectedIndex].value;
	}
	if (numero==4)
	{
	document.list_anidadas.pais.value = document.alta_user.STD_ID_COUNTRY.options[document.alta_user.STD_ID_COUNTRY.selectedIndex].value;

	document.list_anidadas.com.value = 
	document.alta_user.STD_ID_GEO_DIV.options[document.alta_user.STD_ID_GEO_DIV.selectedIndex].value;

	document.list_anidadas.prov.value = 
	document.alta_user.STD_ID_SUB_GEO_DIV.options[document.alta_user.STD_ID_SUB_GEO_DIV.selectedIndex].value;

	document.list_anidadas.place.value = 
	document.alta_user.STD_ID_GEO_PLACE.options[document.alta_user.STD_ID_GEO_PLACE.selectedIndex].value;

	}

			document.list_anidadas.dir.value = document.alta_user.STD_ADDRESS_LINE_1.value;
			document.list_anidadas.blo.value = document.alta_user.SSP_BLOQUE.value;
			//document.list_anidadas.esc.value = document.alta_user.SSP_ESCALERA.value;
			document.list_anidadas.num.value = document.alta_user.SSP_NUM_VIA.value;
			document.list_anidadas.piso.value = document.alta_user.SSP_PISO.value;
			//document.list_anidadas.puer.value = document.alta_user.SSP_PUERTA.value;
			document.list_anidadas.cod.value = document.alta_user.SSP_DISTRIT_POSTAL.value;
			//document.list_anidadas.sigla.value = document.alta_user.SSP_ID_SIGLA_DOMIC.value;

			list_anidadas.submit();

		}

		function comprobar_espacios(formulario,elemento)
		{
	
		 var dato_comprobar = document.forms[formulario].elements[elemento].value;
		 var long_dato_comprobar = dato_comprobar.length;
		 var indicador = 0;
		 for (i=0; i<long_dato_comprobar;i++)
		  {
 		   var valor = dato_comprobar.charAt(i)
		   if (valor != " ")
		    indicador = 1;
		  }
         if (indicador == 0)
		  return(0);
		 else
		  return(1);
		}


		function comprobar_datos_direccion()
		{
	
			var error = 0;
			var texto = msg_32;

//			var geo_div_value =	document.alta_user.STD_ID_GEO_DIV.options[document.alta_user.STD_ID_GEO_DIV.selectedIndex].value;
//			var subgeo_div_value = document.alta_user.STD_ID_SUB_GEO_DIV.options[document.alta_user.STD_ID_SUB_GEO_DIV.selectedIndex].value;
//			var geoplace_value = document.alta_user.STD_ID_GEO_PLACE.options[document.alta_user.STD_ID_GEO_PLACE.selectedIndex].value;
			var geo_div_value =	document.alta_user.STD_ID_GEO_DIV.value;
			var subgeo_div_value = document.alta_user.STD_ID_SUB_GEO_DIV.value;
			var geoplace_value = document.alta_user.STD_ID_GEO_PLACE.value;

			 var long_geo_div_value = geo_div_value.length;
			 var indicador = 0;
			 for (i=0; i<long_geo_div_value;i++)
			  {
	 		   var valor = geo_div_value.charAt(i)
			   if (valor != " ")
			    indicador = 1;
			  }
		     if (indicador == 0)
			 {
				alert(msg_33);
				return;
			  } 

			 var long_subgeo_div_value = subgeo_div_value.length;
			 var indicador = 0;
			 for (i=0; i<long_subgeo_div_value;i++)
			  {
	 		   var valor = subgeo_div_value.charAt(i)
			   if (valor != " ")
			    indicador = 1;
			  }
		     if (indicador == 0)
			 {
				alert(msg_34);
				return;
			  } 


			// var long_geoplace_value = geoplace_value.length;
			//var indicador = 0;
			// for (i=0; i<long_geoplace_value;i++)
			//  {
	 		//   var valor = geoplace_value.charAt(i)
			//   if (valor != " ")
			//   indicador = 1;
			//  }
		     //if (indicador == 0)
		     //	 {
		     //		alert(msg_35);
		     //		return;
		     //	  } 


		   var calle_nulo = comprobar_espacios("alta_user","STD_ADDRESS_LINE_1")
			if (calle_nulo == 0)
			{
			alert(msg_36);
			return;
			}

		   var codigo_nulo = comprobar_espacios("alta_user","SSP_DISTRIT_POSTAL")
			if (codigo_nulo == 0)
			{
			alert(msg_37);
			return;
			}

			cod_postal = document.alta_user.SSP_DISTRIT_POSTAL.value;
			if (isNaN(cod_postal))
			{
				alert(msg_18);
				return;
			}

			num_dig_cod_postal = cod_postal.length;
			if (num_dig_cod_postal!= 5)
			{
				alert(msg_19);
				return;
			}

		   var numero_nulo = comprobar_espacios("alta_user","SSP_NUM_VIA")
			if (numero_nulo == 0)
			{
			alert(msg_38);
			return;
			}

			var informacion = "";
			informacion = "_NODO=SCO_CURR_ADDRESS{_ACCION=UPDATE{_REGISTRO=0{SSP_BLOQUE=";
			informacion += document.alta_user.SSP_BLOQUE.value + "{SSP_DISTRIT_POSTAL=";
			informacion += document.alta_user.SSP_DISTRIT_POSTAL.value + "{SSP_NUM_VIA=";
			//informacion += document.alta_user.SSP_DISTRIT_POSTAL.value + "{SSP_ESCALERA=";
			//informacion += document.alta_user.SSP_ESCALERA.value + "{SSP_NUM_VIA=";
			//informacion += document.alta_user.SSP_ESCALERA.value + "{SSP_ID_SIGLA_DOMIC=";
			//informacion += document.alta_user.SSP_ID_SIGLA_DOMIC.value +"{SSP_N_SIGLA_DOMIC=";
			//informacion += document.alta_user.SSP_ID_SIGLA_DOMIC.options[document.alta_user.SSP_ID_SIGLA_DOMIC.selectedIndex].text +"{SSP_NUM_VIA=";
			informacion += document.alta_user.SSP_NUM_VIA.value + "{SSP_PISO=";
			informacion += document.alta_user.SSP_PISO.value + "{STD_ADDRESS_LINE_1=";
			//informacion += document.alta_user.SSP_PISO.value + "{SSP_PUERTA=";
			//informacion += document.alta_user.SSP_PUERTA.value + "{STD_ADDRESS_LINE_1=";
			informacion += document.alta_user.STD_ADDRESS_LINE_1.value + "{STD_ID_COUNTRY=";
			//informacion += document.alta_user.STD_ID_COUNTRY.options[document.alta_user.STD_ID_COUNTRY.selectedIndex].value + "{STD_ID_GEO_DIV=";
			//informacion += document.alta_user.STD_ID_GEO_DIV.options[document.alta_user.STD_ID_GEO_DIV.selectedIndex].value + "{STD_ID_SUB_GEO_DIV=";
			//informacion +=document.alta_user.STD_ID_SUB_GEO_DIV.options[document.alta_user.STD_ID_SUB_GEO_DIV.selectedIndex].value + "{STD_ID_GEO_PLACE=";				
			//informacion +=document.alta_user.STD_ID_GEO_PLACE.options[document.alta_user.STD_ID_GEO_PLACE.selectedIndex].value;
			informacion += document.alta_user.STD_ID_COUNTRY.value + "{STD_ID_GEO_DIV=";
			informacion += document.alta_user.STD_ID_GEO_DIV.value + "{STD_ID_SUB_GEO_DIV=";
			informacion +=document.alta_user.STD_ID_SUB_GEO_DIV.value + "{STD_ID_GEO_PLACE=";				
			informacion +=document.alta_user.STD_ID_GEO_PLACE.value;
			//informacion += "{STD_N_COUNTRY=" + document.alta_user.STD_ID_COUNTRY.options[document.alta_user.STD_ID_COUNTRY.selectedIndex].text;
			//informacion += "{STD_N_GEO_DIV=" + document.alta_user.STD_ID_GEO_DIV.options[document.alta_user.STD_ID_GEO_DIV.selectedIndex].text;
			//informacion += "{STD_N_SUB_GEO_DIV=" + document.alta_user.STD_ID_SUB_GEO_DIV.options[document.alta_user.STD_ID_SUB_GEO_DIV.selectedIndex].text;
			//informacion += "{STD_N_GEO_PLACE=" + document.alta_user.STD_ID_GEO_PLACE.options[document.alta_user.STD_ID_GEO_PLACE.selectedIndex].text;
			informacion += "{STD_N_COUNTRY=" + document.alta_user.STD_N_COUNTRY.value;
			informacion += "{STD_N_GEO_DIV=" + document.alta_user.STD_N_GEO_DIV.value;
			informacion += "{STD_N_SUB_GEO_DIV=" + document.alta_user.STD_N_SUB_GEO_DIV.value;
			informacion += "{STD_N_GEO_PLACE=" + document.alta_user.STD_N_GEO_PLACE.value;
			
			document.forma_parametro.parametro.value = informacion;
			forma_parametro.submit();
		}

		function comprobar_mail_datos_direccion(contador_reg)
		{
					
		    var num_registros = contador_reg;
			var informacion = "";
			var num_borrados = 0;
				for (i=0;i<num_registros;i++)
				{
					if (informacion != "")
						{informacion += "{";}
						
					var idx_check = "DEL_REG" + i;
					var val_check = document.alta_telef[idx_check].checked;

					if (num_registros == 1)					
						if (val_check == true)
						{
							var email = document.forms["alta_telef"].elements["STD_EMAIL"].value;
							if ((email==null)||(email=="")){
							alert(msg_39);
							document.alta_telef[idx_check].checked = false;
							return;
							}//cierr if(email==null)||(email=="")
						}


					var idx_mail = "STD_EMAIL" + i;
					var idx_location = "STD_ID_LOCATION_TYPE" + i;
					var idn_location = "STD_N_LOCATION_TYPE" + i;
					var idx_num_reg = "NUM_REG" + i;
					var val_mail = document.alta_telef[idx_mail].value;
					var val_location = document.alta_telef[idx_location].value;
					var val_n_location = document.alta_telef[idx_location].options[document.alta_telef[idx_location].selectedIndex].text;

					var val_num_reg = document.alta_telef[idx_num_reg].value;
					var accion = "";
					if ((val_mail == "") || (val_mail == null))
					{
					 alert(msg_40);
					 document.alta_telef[idx_mail].focus();
					 return;
					}else{
						if(i!=num_registros-1){
							for(var j=i+1;j<num_registros;j++){
							var indexEmailMov="STD_EMAIL" + j;
							var valEmailMov = document.alta_telef[indexEmailMov].value;
								if(val_mail==valEmailMov){
								alert(msg_41);
								document.alta_telef[idx_mail].focus();
								return;
								}//cierre if (val_mail==valEmailMov
							}//cierre for j
						}//cierre if i!=num_registros-1
						
					
					}//cierre if ((val_mail == "") || (val_mail == null))
					var l_email_valid = val_mail.length;
					var indicador_email = 0;
					for (idx=0; idx<l_email_valid; idx++)
					{
	 					var valor_mail = val_mail.charAt(idx);
					    if (valor_mail == " ")
					   {
						alert(msg_42)
						document.alta_telef[idx_mail].focus();
						return;
					   }
					   if (valor_mail == "@")
						 indicador_email = 1;
					}//cierre for idx=0; i
					if (indicador_email == 0)
					{
				    alert(msg_43)
					document.alta_telef[idx_mail].focus();
					return;
					}

					if (val_check == true)
					{
						accion = "DELETE";
						num_borrados = num_borrados + 1;
					}
					else
						accion = "UPDATE";

					informacion += "_NODO*" + i + "=SCO_CURR_EMAIL{_ACCION*" + i + "=" + accion + "{_REGISTRO*" + i + "=" + val_num_reg + "{";
					informacion += "STD_EMAIL*" + i + "=" + val_mail + "{STD_ID_LOCATION_TYPE*" + i + "=" + val_location + "{STD_N_LOCATION_TYPE*" + i+ "=" + val_n_location;
				}//cierre for i

				if (num_borrados == num_registros)
				{

				   var email = document.forms["alta_telef"].elements["STD_EMAIL"].value;
				   if ((email==null)||(email==""))
				    {
					   alert(msg_39);
					   document.forms["alta_telef"].elements["STD_EMAIL"].focus();
					   return;
					}
				}//cierre if (num_borrados == num_registros)

				var mail_nuevo = document.alta_telef.STD_EMAIL.value

				if ((mail_nuevo != null) && (mail_nuevo != ""))
				{
					var l_email_valid = mail_nuevo.length;
					var indicador_email = 0;
					for (idx=0; idx<l_email_valid; idx++)
					{
	 					var valor_mail = mail_nuevo.charAt(idx);
					    if (valor_mail == " ")
					   {
						alert(msg_42)
						document.alta_telef.STD_EMAIL.focus();
						return;
					   }
					   if (valor_mail == "@")
						 indicador_email = 1;
					}//cierre for (idx=0; idx<l_email_valid; idx++)
					if (indicador_email == 0)
					{
				    alert(msg_43)
					document.alta_telef.STD_EMAIL.focus();
					return;
					}
					//comprobamos que el correo e nuevo no esta duplicado
					
					if(num_registros!=0){
						for(var i=0;i<num_registros;i++){
						var correo_exist = "STD_EMAIL" + i;
						var val_correo_exist = document.alta_telef[correo_exist].value;
							if(mail_nuevo==val_correo_exist){
							alert(msg_41);
							document.alta_telef.STD_EMAIL.focus();
							return;
							}//cierre if
						}//cierre for
					}//cierre if(num_registros!=0)
					if ((document.alta_telef.STD_ID_LOCATION_TYPE.value == "") || (document.alta_telef.STD_ID_LOCATION_TYPE.value == null))
					{
						alert(msg_44);
						return;
				    }

					informacion += "{_NODO=SCO_CURR_EMAIL{_ACCION=INSERT{_REGISTRO={STD_EMAIL=" + mail_nuevo + "{STD_ID_LOCATION_TYPE=" + document.alta_telef.STD_ID_LOCATION_TYPE.value + "{STD_N_LOCATION_TYPE=" + document.alta_telef.STD_ID_LOCATION_TYPE.options[document.alta_telef.STD_ID_LOCATION_TYPE.selectedIndex].text;
				}
				document.forma_parametro.parametro.value = informacion; 

				forma_parametro.submit();
		}

	function filtrar_hist_academ(numero){

	var numeros = "0123456789";
	if (numero==1)
	{
	document.list_anidadas.pais.value = document.historial_existente.STD_ID_COUNTRY_1.options[document.historial_existente.STD_ID_COUNTRY_1.selectedIndex].value;
	document.list_anidadas.idz_reg.value = document.historial_existente.idz_reg1.value;
	list_anidadas.submit();
	}
	if (numero==2)
	{
	document.list_anidadas2.pais.value = document.historial_acad.STD_ID_COUNTRY.options[document.historial_acad.STD_ID_COUNTRY.selectedIndex].value;
	document.list_anidadas2.n_pais.value = document.historial_acad.STD_ID_COUNTRY.options[document.historial_acad.STD_ID_COUNTRY.selectedIndex].text;
	list_anidadas2.submit();
	}

}


//***************************
//Funciones de la pagina de datos personales js_datos_personales.jsp
//FECHA: 28/11/2000
//***************************

	function filtrar_datos_personales(numero){
	var numeros = "0123456789";
	if (numero==1)
	document.list_anidadas.pais.value = document.alta_user.STD_ID_COUNTRY.options[document.alta_user.STD_ID_COUNTRY.selectedIndex].value;

	if (numero==2)
	{
	document.list_anidadas.pais.value = document.alta_user.STD_ID_COUNTRY.options[document.alta_user.STD_ID_COUNTRY.selectedIndex].value;

	document.list_anidadas.com.value = 
	document.alta_user.STD_ID_GEO_DIV.options[document.alta_user.STD_ID_GEO_DIV.selectedIndex].value;
	}
	if (numero==3)
	{
	document.list_anidadas.pais.value = document.alta_user.STD_ID_COUNTRY.options[document.alta_user.STD_ID_COUNTRY.selectedIndex].value;

	document.list_anidadas.com.value = 
	document.alta_user.STD_ID_GEO_DIV.options[document.alta_user.STD_ID_GEO_DIV.selectedIndex].value;

	document.list_anidadas.prov.value = 
	document.alta_user.STD_ID_SUB_GEO_DIV.options[document.alta_user.STD_ID_SUB_GEO_DIV.selectedIndex].value;
	}

	if (numero==4)
	{
	document.list_anidadas.pais.value = document.alta_user.STD_ID_COUNTRY.options[document.alta_user.STD_ID_COUNTRY.selectedIndex].value;

	document.list_anidadas.com.value = 
	document.alta_user.STD_ID_GEO_DIV.options[document.alta_user.STD_ID_GEO_DIV.selectedIndex].value;

	document.list_anidadas.prov.value = 
	document.alta_user.STD_ID_SUB_GEO_DIV.options[document.alta_user.STD_ID_SUB_GEO_DIV.selectedIndex].value;

	document.list_anidadas.place.value = 
	document.alta_user.STD_ID_GEO_PLACE.options[document.alta_user.STD_ID_GEO_PLACE.selectedIndex].value;
	}

			
			document.list_anidadas.nom.value = document.alta_user.nombre.value;
			document.list_anidadas.ape.value = document.alta_user.apellido.value;
			document.list_anidadas.ape2.value = document.alta_user.apellido_2.value;
			document.list_anidadas.nif.value = document.alta_user.nif_user.value;
			document.list_anidadas.SREFER_HR.value = document.alta_user.S_REFER_HR.options[document.alta_user.S_REFER_HR.selectedIndex].value;

				var dt_nac = document.alta_user.STD_DT_BIRTH.value;
				var es_correcta = dt_nac.substring(2,3);
				var fecha_nac_correcta = dt_nac;

				if (-1 == numeros.indexOf(es_correcta.charAt(0)))
			     {
			  	  var dia = dt_nac.substring(0,2);
				  var mes = dt_nac.substring(3,5);
				  var anio = dt_nac.substring(6,10);
				  fecha_nac_correcta = anio + "-" + mes + "-" + dia;
				 }

		    document.forms["list_anidadas"].elements["nac"].value = fecha_nac_correcta;
			document.list_anidadas.sex.value = document.alta_user.STD_ID_GENDER.value;
			document.list_anidadas.tp_doc.value = document.alta_user.SSP_ID_TP_DOC.value;
			document.list_anidadas.mar.value = document.alta_user.STD_ID_MARITAL_STAT.value;
			//document.list_anidadas.cons.value = document.alta_user.SCO_PERS_CONSIDE.value;
			list_anidadas.submit();
			
		}

		function comprobar_datos_personales()
		{

			var error = 0;
			var texto = msg_45;
			var numeros = "0123456789"

//			geo_div_value =	document.alta_user.STD_ID_GEO_DIV.options[document.alta_user.STD_ID_GEO_DIV.selectedIndex].value;
//			subgeo_div_value = document.alta_user.STD_ID_SUB_GEO_DIV.options[document.alta_user.STD_ID_SUB_GEO_DIV.selectedIndex].value;
//			geoplace_value = document.alta_user.STD_ID_GEO_PLACE.options[document.alta_user.STD_ID_GEO_PLACE.selectedIndex].value;
            country_value = document.alta_user.STD_ID_COUNTRY.value;
			geo_div_value =	document.alta_user.STD_ID_GEO_DIV.value;
			subgeo_div_value = document.alta_user.STD_ID_SUB_GEO_DIV.value;
			geoplace_value = document.alta_user.STD_ID_GEO_PLACE.value;

			var long_geo_div_value = geo_div_value.length;
			var indicador = 0;
			for (i=0; i<long_geo_div_value;i++)
			{
	 		 var valor = geo_div_value.charAt(i)
			 if (valor != " ")
				indicador = 1;
			}
		   if (indicador == 0)
		   	 {
		   		alert(msg_33);
		   		return;
		  	  } 


			 var long_subgeo_div_value = subgeo_div_value.length;
			 var indicador = 0;
			 for (i=0; i<long_subgeo_div_value;i++)
			  {
	 		   var valor = subgeo_div_value.charAt(i)
			   if (valor != " ")
			    indicador = 1;
			  }
		     if (indicador == 0)
			 {
				alert(msg_34);
				return;
			  } 


			// var long_geoplace_value = geoplace_value.length;
			//var indicador = 0;
			// for (i=0; i<long_geoplace_value;i++)
			// {
	 		//   var valor = geoplace_value.charAt(i)
			//   if (valor != " ")
			 //  indicador = 1;
			 // }
		     //if (indicador == 0)
			// {
			//	alert(msg_35);
			//	return;
			//  } 


		   var nombre_nulo = comprobar_espacios("alta_user","nombre")
			if (nombre_nulo == 0)
			{
			alert(msg_9);
			return;
			}

		   var apellido_nulo = comprobar_espacios("alta_user","apellido")
			if (apellido_nulo == 0)
			{
			alert(msg_10);
			return;
			}

			var informacion = "";
			informacion = "_NODO=SCO_CURR_PERSON{_ACCION=UPDATE{_REGISTRO=0{STD_N_FIRST_NAME=";
			informacion += document.alta_user.nombre.value + "{STD_N_FAMILY_NAME_1=";
			informacion += document.alta_user.apellido.value + "{STD_N_USUAL_NAME=";
			informacion += document.alta_user.apellido_2.value + "{STD_SS_NUMBER=";
			informacion += document.alta_user.SSP_ID_TP_DOC.value + "{STD_ID_GENDER=";
			informacion += document.alta_user.STD_ID_GENDER.value + "{STD_ID_MARITAL_STAT=";
			informacion += document.alta_user.STD_ID_MARITAL_STAT.value +"{STD_ID_COUNTRY=";
			
//			informacion += document.alta_user.STD_ID_MARITAL_STAT.value + "{SCO_PERS_CONSIDE=";
	//		informacion += document.alta_user.SCO_PERS_CONSIDE.value +"{STD_ID_COUNTRY=";
//-			informacion += document.alta_user.SCO_PERS_CONSIDE.value; 

//			informacion += document.alta_user.STD_ID_COUNTRY.options[document.alta_user.STD_ID_COUNTRY.selectedIndex].value + "{STD_ID_GEO_DIV=";
//			informacion += document.alta_user.STD_ID_GEO_DIV.options[document.alta_user.STD_ID_GEO_DIV.selectedIndex].value + "{STD_ID_SUB_GEO_DIV=";
//			informacion +=document.alta_user.STD_ID_SUB_GEO_DIV.options[document.alta_user.STD_ID_SUB_GEO_DIV.selectedIndex].value + "{STD_ID_GEO_PLACE=";				
//			informacion +=document.alta_user.STD_ID_GEO_PLACE.options[document.alta_user.STD_ID_GEO_PLACE.selectedIndex].value;

			informacion += document.alta_user.STD_ID_COUNTRY.value + "{STD_ID_GEO_DIV=";
			informacion += document.alta_user.STD_ID_GEO_DIV.value + "{STD_ID_SUB_GEO_DIV=";
			informacion +=document.alta_user.STD_ID_SUB_GEO_DIV.value + "{STD_ID_GEO_PLACE=";				
			informacion +=document.alta_user.STD_ID_GEO_PLACE.value;

//			informacion += "{STD_ID_COUNTRY=" + document.alta_user.STD_ID_COUNTRY.value;

			informacion += "{STD_N_MARITAL_STAT=" + document.alta_user.STD_ID_MARITAL_STAT.options[document.alta_user.STD_ID_MARITAL_STAT.selectedIndex].text;
			informacion += "{STD_N_GENDER=" + document.alta_user.STD_ID_GENDER.options[document.alta_user.STD_ID_GENDER.selectedIndex].text;

//			informacion += "{STD_N_COUNTRY=" + document.alta_user.STD_ID_COUNTRY.options[document.alta_user.STD_ID_COUNTRY.selectedIndex].text;
//			informacion += "{STD_N_GEO_DIV=" + document.alta_user.STD_ID_GEO_DIV.options[document.alta_user.STD_ID_GEO_DIV.selectedIndex].text;
//			informacion += "{STD_N_SUB_GEO_DIV=" + document.alta_user.STD_ID_SUB_GEO_DIV.options[document.alta_user.STD_ID_SUB_GEO_DIV.selectedIndex].text;
//			informacion += "{STD_N_GEO_PLACE=" + document.alta_user.STD_ID_GEO_PLACE.options[document.alta_user.STD_ID_GEO_PLACE.selectedIndex].text;

			informacion += "{STD_N_COUNTRY=" + document.alta_user.STD_N_COUNTRY.text;
			informacion += "{STD_N_GEO_DIV=" + document.alta_user.STD_N_GEO_DIV.text;
			informacion += "{STD_N_SUB_GEO_DIV=" + document.alta_user.STD_N_SUB_GEO_DIV.text;
			informacion += "{STD_N_GEO_PLACE=" + document.alta_user.STD_N_GEO_PLACE.text;

			informacion += "{STD_SSN=" + document.alta_user.CURP.value;
			informacion += "{CFP_CARTILLA=" + document.alta_user.CARTILLA.value;
			informacion += "{CFP_IFE=" + document.alta_user.IFE.value;
			informacion += "{CFP_CEDULA_PROF=" + document.alta_user.CEDULA.value;
			
			informacion += "{STD_DT_BIRTH=" + document.alta_user.STD_DT_BIRTH.value;						
			//alert(informacion);

			document.forma_parametro.parametro.value = informacion;
			forma_parametro.submit();

		}

		function comprobar_datos_perfil()
		{

			var error = 0;
			var texto = msg_45;
			var numeros = "0123456789"

			var informacion = "";
			informacion = "_NODO=SCO_CURR_PERSON{_ACCION=UPDATE{_REGISTRO=0{CFP_CK_PERFIL=";
			informacion += document.perfil.perfil_v.value + "{CFP_CK_CONFIDENCIALIDAD=";
			informacion += document.perfil.confidencialidad_v.value;
//alert(informacion);
			document.forma_parametro.parametro.value = informacion;
			forma_parametro.submit();

		}


		function comprobar_telefono_datos_personales(contador_reg)
		{
			    var num_registros = contador_reg;
				var informacion = "";
				var numeros = "0123456789";
				var num_borrados = 0;
				for (i=0; i<num_registros;i++)
				{
					if (informacion != "")
						informacion += "{"
					var idx_check = "DEL_REG" + i;
					var val_check = document.alta_telef[idx_check].checked;

					if (num_registros == 1)					
						if (val_check == true)
						{
							
							var telefono = document.forms["alta_telef"].elements["STD_PHONE"].value;
							if ((telefono==null)||(telefono=="")){
							alert(msg_46);
							document.alta_telef[idx_check].checked = false;
							return;
							}//cierre if 
						}

					var idx_telefono = "STD_PHONE" + i;
					var idx_location = "STD_ID_LOCATION_TYPE" + i;
					var idx_linea = "STD_ID_LINE_TYPE" + i;
					var idx_num_reg = "NUM_REG" + i;
					var val_telefono = document.alta_telef[idx_telefono].value;
					var val_linea = document.alta_telef[idx_linea].value;
					var val_n_linea = document.alta_telef[idx_linea].options[document.alta_telef[idx_linea].selectedIndex].text;
					var val_location = document.alta_telef[idx_location].value;
					var val_n_location = document.alta_telef[idx_location].options[document.alta_telef[idx_location].selectedIndex].text; 

					var val_num_reg = document.alta_telef[idx_num_reg].value;

					if ((val_telefono == "") || (val_telefono == null))
					{
					alert(msg_47);
					document.alta_telef[idx_telefono].focus();
					return;
					}else{
					if(i!=num_registros-1){
						for(var j=i+1;j<num_registros;j++){
						var indexTelMov="STD_PHONE" + j;
						var valTelMov = document.alta_telef[indexTelMov].value;
						if(val_telefono==valTelMov){
						alert(msg_48);
						document.alta_telef[idx_telefono].focus();
						return;
						}//cierre if (valTelefFijo==valTelMov
						
						}//cierre j
					}//cierre if
					}
					var accion = "";

					var l_tel_valid = val_telefono.length;
					var indicador_tel = 0;
					var numeros = "0123456789";
					for (idx_tel=0; idx_tel<l_tel_valid; idx_tel++)
					{
			 		   var valor_tel = val_telefono.charAt(idx_tel);
					   if (valor_tel == " ")
					   {
					    alert(msg_21)
						return;
					   }
						if (-1 == numeros.indexOf(valor_tel))
						 indicador_tel = 1;
					}
					if (indicador_tel == 1)
				    {
					    alert(msg_22)
						return;
					}
					
					if (val_check == true)
					{
						accion = "DELETE";
						num_borrados = num_borrados + 1;
					}
					else
						accion = "UPDATE";
					informacion += "_NODO*" + i + "=SCO_CURR_PHONE{_ACCION*" + i + "=" + accion + "{_REGISTRO*" + i + "=" + val_num_reg + "{";
					informacion += "STD_PHONE*" + i + "=" + val_telefono + "{STD_ID_LOCATION_TYPE*" + i + "=" + val_location + "{STD_N_LOCATION_TYPE*" + i + "=" + val_n_location + "{";
					informacion += "STD_ID_LINE_TYPE*" + i + "=" + val_linea + "{STD_N_LINE_TYPE*" + i + "=" + val_n_linea;
				}
				if (num_borrados == num_registros)
				{
					var telefono = document.forms["alta_telef"].elements["STD_PHONE"].value;
					if ((telefono==null)||(telefono==""))
					{
					  alert(msg_46);

					  for (j=0; j<num_registros;j++)
 					  document.alta_telef[j].checked = false;
					  return;
				    }
				}

				var telef_nuevo = document.alta_telef.STD_PHONE.value;
				if ((telef_nuevo != null) && (telef_nuevo != ""))
				{
					var l_tel_valid = telef_nuevo.length;
					var indicador_tel = 0;
					var numeros = "0123456789";
					for (idx_tel=0; idx_tel<l_tel_valid; idx_tel++)
					{
			 		   var valor_tel = telef_nuevo.charAt(idx_tel);
					   if (valor_tel == " ")
					   {
					    alert(msg_21)
						document.alta_telef.STD_PHONE.focus();
						return;
					   }
						if (-1 == numeros.indexOf(valor_tel))
						 indicador_tel = 1;
					}
					if (indicador_tel == 1)
				    {
					    alert(msg_22)
						document.alta_telef.STD_PHONE.focus();
						return;
					}
					//comprobamos que el telefono nuevo no esta duplicado
					
					if(num_registros!=0){
						for(var i=0;i<num_registros;i++){
						var telefono_exist = "STD_PHONE" + i;
						var val_telefono_exist = document.alta_telef[telefono_exist].value;
							if(telef_nuevo==val_telefono_exist){
							alert(msg_48);
							document.alta_telef.STD_PHONE.focus();
							return;
							}//cierre if
						}//cierre for
					}//cierre if(num_registros!=0)
					if ((document.alta_telef.STD_ID_LOCATION_TYPE.value == "") || (document.alta_telef.STD_ID_LOCATION_TYPE.value == null))
					{
					   alert(msg_49);
					   return;
					}

					if ((document.alta_telef.STD_ID_LINE_TYPE.value == "") || (document.alta_telef.STD_ID_LINE_TYPE.value == null))
					{
					   alert(msg_50);
					   return;
					}

					informacion += "{_NODO=SCO_CURR_PHONE{_ACCION=INSERT{_REGISTRO={STD_PHONE=" + telef_nuevo + "{STD_ID_LOCATION_TYPE=" + document.alta_telef.STD_ID_LOCATION_TYPE.value + "{STD_ID_LINE_TYPE=" + document.alta_telef.STD_ID_LINE_TYPE.value + "{STD_N_LOCATION_TYPE=" + document.alta_telef.STD_ID_LOCATION_TYPE.options[document.alta_telef.STD_ID_LOCATION_TYPE.selectedIndex].text + "{STD_N_LINE_TYPE=" + document.alta_telef.STD_ID_LINE_TYPE.options[document.alta_telef.STD_ID_LINE_TYPE.selectedIndex].text;
				}
				document.forma_parametro.parametro.value = informacion; 
				forma_parametro.submit();
			}

//***************************
//Funciones de la pagina de paqueteria js_paqueteria.jsp
//FECHA: 13/04/2005
//***************************
	function selectedradio_paqueteria()
    {		
		var valorseleccionadoex="";
		var longex=document.forms["paqueteria"].elements["paqueteria"].length;
		var i=0;
		for(i=0;i<longex;i++)
		{
			if(document.forms["paqueteria"].elements["paqueteria"][i].checked)
			{
				valorseleccionadoex=document.forms["paqueteria"].elements["paqueteria"][i].value;
				document.forms["paqueteria"].elements["paqueteria_v"].value=valorseleccionadoex;
				return;
			}
		}
    }	

//***************************
//Funciones de la pagina de paqueteria js_perfil.jsp
//FECHA: 13/04/2005
//***************************
	function selectedradio_perfil()
    {		
		var valorseleccionadoex="";
		var longex=document.forms["perfil"].elements["perfil"].length;
		var i=0;
		for(i=0;i<longex;i++)
		{
			if(document.forms["perfil"].elements["perfil"][i].checked)
			{
				valorseleccionadoex=document.forms["perfil"].elements["perfil"][i].value;
				document.forms["perfil"].elements["perfil_v"].value=valorseleccionadoex;
				return;
			}
		}
    }	

//***************************
//Funciones de la pagina de confidencialidad js_confidencialidad.jsp
//FECHA: 13/04/2005
//***************************
	function selectedradio_confidencialidad()
    {		
		var valorseleccionadoex="";
		var longex=document.forms["perfil"].elements["confidencialidad"].length;
		var i=0;
		for(i=0;i<longex;i++)
		{
			if(document.forms["perfil"].elements["confidencialidad"][i].checked)
			{
				valorseleccionadoex=document.forms["perfil"].elements["confidencialidad"][i].value;
				document.forms["perfil"].elements["confidencialidad_v"].value=valorseleccionadoex;
				return;
			}
		}
    }	
		
//***************************
//Funciones de la pagina de historial academico js_historial_academico.jsp
//FECHA: 28/11/2000
//***************************

	function selectedradio_hist_acad()
	{
		
		var valorseleccionado="";
		var long=document.historial_acad.SCO_CK_END_DIP.length;
		var i=0;
		for(i=0;i<long;i++)
		{
			if(document.historial_acad.SCO_CK_END_DIP[i].checked)
			{
			valorseleccionado=document.historial_acad.SCO_CK_END_DIP[i].value;
			document.historial_acad.SCO_CK_END_DIP_V.value=valorseleccionado;
			return;
			
			}
		}
    }	


    function selectedradioex_hist_acad()
    {
		
		var valorseleccionadoex="";
		var longex=document.forms["historial_existente"].elements["SCO_CK_END_DIP*1"].length;
		var i=0;
		for(i=0;i<longex;i++)
		{
			if(document.forms["historial_existente"].elements["SCO_CK_END_DIP*1"][i].checked)
			{
				valorseleccionadoex=document.forms["historial_existente"].elements["SCO_CK_END_DIP*1"][i].value;
				document.forms["historial_existente"].elements["SCO_CK_END_DIP_V*1"].value=valorseleccionadoex;
				return;
			
			}
		}
    }	


function comprobar_hist_acad(accion_a_tomar)
	{	var action = accion_a_tomar;
		var informacion = "";
	 
   	    if (informacion != "")
	   	    informacion += "{";

   	    var n_nodo = "_NODO*" + "0";
		var n_registro = "_REGISTRO*" + "0";
			
		if (action == 2)
		{
		
		//alert ("comprobar_hist_acad");
		
			informacion="_NODO=SCO_CURR_ACAD_BACK{_ACCION=DELETE{_REGISTRO=";
			informacion+=document.forms["historial_existente"].elements["_REGISTRO*1"].value;
		    document.forms["forma_parametro"].elements["parametro"].value=informacion;		
			forma_parametro.submit();
			return;
		}
  
		var id_country = "STD_ID_COUNTRY*" + "0";
		var n_country = "STD_N_COUNTRY*" + "0";
		var id_geodiv = "STD_ID_GEO_DIV*" + "0";
		var n_geodiv = "STD_N_GEO_DIV*" + "0";

		var id_diploma = "STD_ID_DIPLOMA*" + "0";
		var n_diploma = "STD_N_DIPLOMA*" + "0";
//		var id_edu_sp = "STD_ID_EDU_SP*" + "0";
//		var n_edu_sp = "STD_N_EDU_SP*" + "0";
	    var id_educ_center = "STD_ID_EDU_CENTER*" + "0";
		var n_educ_center = "STD_N_EXT_ORG*" + "0";
		var id_edu_type = "STD_ID_EDU_TYPE*" + "0";
		var n_edu_type = "STD_N_EDU_TYPE*" + "0";
		var dt_start = "STD_DT_START*" + "0";
		var dt_earned_expe = "STD_DT_EARNED_EXPE*" +"0";
//		var total_years_dip = "STD_TOTAL_YEARS_DIP*" +"0";
//		var ck_end_dip = "SCO_CK_END_DIP*" +"0";
//		var completed_years = "STD_COMPLETED_YEARS*" +"0";
		var cedula = "CMX_N_CEDULA*" + "0";
//		var grado_avance = "CMX_N_GRADOAVANCE*" + "0";
		var otra_inst = "CMX_N_OTRAINSTITUC*" + "0";
		var reconocimiento = "CMX_N_RECONOCIMIEN*" + "0";
		
		var id_area = "CFP_ID_AREA_GENERAL*" + "0";
		var n_area = "CFP_N_AREA_GENERAL*" + "0";
		var id_carrera = "CFP_ID_CARRERA_GEN*" + "0";
		var n_carrera = "CFP_N_CARRERA_GEN*" + "0";		
		var nombre_carrera = "CMX_N_NOMBRE_CARRERA*" + "0";		
		var titulo = "CMX_N_TITULO*" + "0";				
				
		var comment = "STD_COMMENT*" +"0";
		var v_nodo = document.forms["historial_acad"].elements["_NODO*0"].value;
		var formulario = "";

		if (action == 1)
		{ 
			var v_registro = document.forms["historial_existente"].elements["_REGISTRO*1"].value;
			var v_accion_canal = "UPDATE";
			formulario = "historial_existente";

//		alert("Pasax");
//		alert(v_registro);	
					
			var v_id_country=document.forms[formulario].elements["STD_ID_COUNTRY_1"].value;

//			var v_n_country=document.forms[formulario].elements["STD_ID_COUNTRY_1"].options[document.forms[formulario].elements["STD_ID_COUNTRY_1"].selectedIndex].text; 
//			var v_id_geodiv=document.forms[formulario].elements["STD_ID_GEO_DIV*1"].value;
//			var v_n_geodiv=document.forms[formulario].elements["STD_ID_GEO_DIV*1"].options[document.forms[formulario].elements["STD_ID_GEO_DIV*1"].selectedIndex].text; 
			var v_n_country=document.forms[formulario].elements["STD_N_COUNTRY_1"].value; 
			var v_id_geodiv=document.forms[formulario].elements["STD_ID_GEO_DIV_1"].value;
			var v_n_geodiv=document.forms[formulario].elements["STD_N_GEO_DIV_1"].value; 

			
			var v_id_edu_type=document.forms[formulario].elements["STD_ID_EDU_TYPE*1"].value;
			var v_n_edu_type=document.forms[formulario].elements["STD_ID_EDU_TYPE*1"].options[document.forms[formulario].elements["STD_ID_EDU_TYPE*1"].selectedIndex].text; 
			var v_id_diploma=document.forms[formulario].elements["STD_ID_DIPLOMA_1"].value; 
			var v_n_diploma=document.forms[formulario].elements["STD_ID_DIPLOMA_1"].options[document.forms[formulario].elements["STD_ID_DIPLOMA_1"].selectedIndex].text; 
//			var v_id_edu_sp=document.forms[formulario].elements["STD_ID_EDU_SP*1"].value; 
//			var v_n_edu_sp=document.forms[formulario].elements["STD_ID_EDU_SP*1"].options[document.forms[formulario].elements["STD_ID_EDU_SP*1"].selectedIndex].text; 
			//var v_id_educ_center=document.forms[formulario].elements["STD_ID_EDU_CENTER*1"].value; 
			//var v_n_educ_center=document.forms[formulario].elements["STD_ID_EDU_CENTER*1"].options[document.forms[formulario].elements["STD_ID_EDU_CENTER*1"].selectedIndex].text; 
			var v_dt_start=document.forms[formulario].elements["STD_DT_START*1"].value; 
			var v_dt_earned_expe=document.forms[formulario].elements["STD_DT_EARNED_EXPE*1"].value; 
//			var v_total_years_dip=document.forms[formulario].elements["STD_TOTAL_YEARS_DIP*1"].value; 
//			var v_completed_years=document.forms[formulario].elements["STD_COMPLETED_YEARS*1"].value;
//			var v_ck_end_dip=document.forms[formulario].elements["SCO_CK_END_DIP_V*1"].value;

			var v_cedula=document.forms[formulario].elements["CMX_N_CEDULA*1"].value;
//			var v_grado_avance=document.forms[formulario].elements["CMX_N_GRADOAVANCE*1"].value;
			var v_otra_institucion=document.forms[formulario].elements["CMX_N_OTRAINSTITUC*1"].value;
			
		    var v_id_area=document.forms[formulario].elements["CFP_ID_AREA_GENERAL_1"].value;			
		    if (v_id_area != null && v_id_area != "")
		    {		 		 						
			    var v_n_area=document.forms[formulario].elements["CFP_N_AREA_GENERAL_1"].value; 
			
			    var v_id_carrera=document.forms[formulario].elements["CFP_ID_CARRERA_GEN_1"].value;
			    var v_n_carrera=document.forms[formulario].elements["CFP_N_CARRERA_GEN_1"].value; 			

    			var v_nombre_carrera=document.forms[formulario].elements["CMX_N_NOMBRE_CARRERA*1"].value;
	    		//var v_titulo=document.forms[formulario].elements["CMX_N_TITULO*1"].value;									
		    	//var v_reconocimiento=document.forms[formulario].elements["CMX_N_RECONOCIMIEN*1"].value;
			}			
						
			//var v_comment=document.forms[formulario].elements["STD_COMMENT*1"].value;
		}
		if (action == 3)
		{
			var v_registro =0
			var v_accion_canal = "INSERT";
			formulario = "historial_acad";
			//alert("paso por" + action );
			var v_id_country =  document.forms[formulario].elements["STD_ID_COUNTRY"].value; 
//			var v_n_country = document.forms[formulario].elements["STD_ID_COUNTRY"].options[document.forms[formulario].elements["STD_ID_COUNTRY"].selectedIndex].text; 
			var v_n_country = document.forms[formulario].elements["STD_N_COUNTRY"].value; 
			var v_id_geodiv =  document.forms[formulario].elements["STD_ID_GEO_DIV"].value; 
//			var v_n_geodiv = document.forms[formulario].elements["STD_ID_GEO_DIV"].options[document.forms[formulario].elements["STD_ID_GEO_DIV"].selectedIndex].text; 
			var v_n_geodiv = document.forms[formulario].elements["STD_N_GEO_DIV"].value; 
			var v_id_edu_type = document.forms[formulario].elements["STD_ID_EDU_TYPE"].value;
			var v_n_edu_type = document.forms[formulario].elements["STD_ID_EDU_TYPE"].options[document.forms[formulario].elements["STD_ID_EDU_TYPE"].selectedIndex].text; 
			var v_id_diploma =  document.forms[formulario].elements["STD_ID_DIPLOMA"].value; 
			var v_n_diploma = document.forms[formulario].elements["STD_ID_DIPLOMA"].options[document.forms[formulario].elements["STD_ID_DIPLOMA"].selectedIndex].text; 
//			var v_id_edu_sp = document.forms[formulario].elements["STD_ID_EDU_SP"].value; 
//			var v_n_edu_sp = document.forms[formulario].elements["STD_ID_EDU_SP"].options[document.forms[formulario].elements["STD_ID_EDU_SP"].selectedIndex].text; 
			//var v_id_educ_center = document.forms[formulario].elements["STD_ID_EDU_CENTER"].value; 
			//var v_n_educ_center = document.forms[formulario].elements["STD_ID_EDU_CENTER"].options[document.forms[formulario].elements["STD_ID_EDU_CENTER"].selectedIndex].text; 
			var v_dt_start = document.forms[formulario].elements["STD_DT_START"].value; 
			var v_dt_earned_expe = document.forms[formulario].elements["STD_DT_EARNED_EXPE"].value; 
//			var v_total_years_dip = document.forms[formulario].elements["STD_TOTAL_YEARS_DIP"].value; 
//			var v_completed_years=document.forms[formulario].elements["STD_COMPLETED_YEARS"].value; 
//			var v_ck_end_dip = document.forms[formulario].elements["SCO_CK_END_DIP_V"].value;

			var v_cedula=document.forms[formulario].elements["CMX_N_CEDULA"].value;
//			var v_grado_avance=document.forms[formulario].elements["CMX_N_GRADOAVANCE"].value;
			var v_otra_institucion=document.forms[formulario].elements["CMX_N_OTRAINSTITUC"].value;
						
			var v_id_area =  document.forms[formulario].elements["CFP_ID_AREA_GENERAL_2"].value; 
			
			if (v_id_area != null && v_id_area != "")
			{
			    var v_n_area = document.forms[formulario].elements["CFP_N_AREA_GENERAL_2"].value; 
			    var v_id_carrera =  document.forms[formulario].elements["CFP_ID_CARRERA_GEN_2"].value; 
			    var v_n_carrera = document.forms[formulario].elements["CFP_N_CARRERA_GEN_2"].value; 						
						
			    var v_nombre_carrera=document.forms[formulario].elements["CMX_N_NOMBRE_CARRERA"].value;
				//alert(document.forms[formulario].elements["CMX_N_NOMBRE_CARRERA"].value);
			    //var v_titulo=document.forms[formulario].elements["CMX_N_TITULO"].value;
    			//var v_reconocimiento=document.forms[formulario].elements["CMX_N_RECONOCIMIEN"].value;				
			}	
												
			//var v_comment = document.forms[formulario].elements["STD_COMMENT"].value;
		}
		 informacion += n_nodo + "=" + v_nodo + "{_ACCION*0="+ v_accion_canal +"{";
		 informacion += n_registro + "=" + v_registro + "{";

		 informacion += id_country + "=" + v_id_country+ "{";
		 informacion += n_country + "=" + v_n_country + "{";
		 informacion += id_geodiv + "=" + v_id_geodiv + "{";
		 informacion += n_geodiv + "=" + v_n_geodiv + "{";
		 
		 informacion += id_diploma + "=" + v_id_diploma + "{" 
//		 informacion += id_edu_sp + "=" + v_id_edu_sp + "{"
		 informacion += id_edu_type + "=" + v_id_edu_type + "{"; 
		 informacion += n_diploma + "=" + v_n_diploma + "{";
//		 informacion += n_edu_sp + "=" + v_n_edu_sp + "{";
		 informacion += n_edu_type + "=" + v_n_edu_type + "{" + dt_start + "=" + v_dt_start + "{";
		// informacion += id_educ_center + "=" + v_id_educ_center + "{ " + dt_start + "=" + v_dt_start + "{";
		 //informacion += n_educ_center + "=" + v_n_educ_center + "{"+ comment + "=" + v_comment + "{";
		 if ((v_dt_earned_expe =="") || (v_dt_earned_expe == null))
			 informacion += dt_earned_expe + "=4000-01-01{";
		 else
		 informacion += dt_earned_expe + "=" + v_dt_earned_expe + "{";  		 
//		 informacion += total_years_dip + "=" + v_total_years_dip + "{" ; 
//		 informacion += completed_years + "=" + v_completed_years + "{"; 
//		 informacion += ck_end_dip + "=" + v_ck_end_dip + "{";		

		 informacion += cedula + "=" + v_cedula + "{";
//		 informacion += grado_avance + "=" + v_grado_avance + "{";
		 informacion += otra_inst + "=" + v_otra_institucion + "{";
		 
		if (v_id_area != null && v_id_area != "")
		{		 		 
		     informacion += id_area + "=" + v_id_area+ "{";
		     informacion += n_area + "=" + v_n_area + "{";		 
		 
		     informacion += id_carrera + "=" + v_id_carrera+ "{";
		     informacion += n_carrera + "=" + v_n_carrera + "{";			 
		 
		     informacion += nombre_carrera + "=" + v_nombre_carrera + "{";
		     //informacion += titulo + "=" + v_titulo + "{";		 		 
		     //informacion += reconocimiento + "=" + v_reconocimiento + "{";		 
         }
		 
		 //alert(informacion);
	     document.forms["forma_parametro"].elements["parametro"].value = informacion;
	     forma_parametro.submit();  
    }

		 
function validar_datos_form_acad(accion){
	var accion_bbdd = accion;
	var numeros = "0123456789";
		
	if (accion == 3)
	{ 
//		var o_total_years_dip = document.forms["historial_acad"].elements["STD_TOTAL_YEARS_DIP"].value; 
//		var o_completed_years=document.forms["historial_acad"].elements["STD_COMPLETED_YEARS"].value; 
		var o_dt_earned_expe = document.forms["historial_acad"].elements["STD_DT_EARNED_EXPE"].value; 
		var o_dt_start = document.forms["historial_acad"].elements["STD_DT_START"].value;
//		var o_ck_dip = document.forms["historial_acad"].elements["SCO_CK_END_DIP_V"].value;
		var o_id_diploma = document.forms["historial_acad"].elements["STD_ID_DIPLOMA"].value;
//		var o_id_edu_sp = document.forms["historial_acad"].elements["STD_ID_EDU_SP"].value;
		//var o_id_edu_center = document.forms["historial_acad"].elements["STD_ID_EDU_CENTER"].value;
//		var o_id_edu_type = document.forms["historial_acad"].elements["STD_ID_EDU_TYPE"].value;

//		var anios_nulo = comprobar_espacios("historial_acad","STD_TOTAL_YEARS_DIP")
//		if (anios_nulo == 0)
//		{
//		alert(msg_51);
//		document.forms["historial_acad"].elements["STD_TOTAL_YEARS_DIP"].focus();
//		return;
//		}else{
//			if (isNaN(o_total_years_dip))
//			{
//			alert(msg_52);
//			document.forms["historial_acad"].elements["STD_TOTAL_YEARS_DIP"].focus();
//			return;
//			}//cierre if (isNaN(o_total_years_dip))
//		}//cierre if (anios_nulo == 0)

		var fechafin_nula = comprobar_espacios("historial_acad","STD_DT_EARNED_EXPE");
		var fecha_nula = comprobar_espacios("historial_acad","STD_DT_START");
		if (fecha_nula == 0)
			{
			alert(msg_53);
			document.forms["historial_acad"].elements["STD_DT_START"].focus();
			return;
			}else{
				var formato_fecha_inicio = comprobar_formato_fecha(o_dt_start);
				if(formato_fecha_inicio!=1){
				alert(msg_54);
				document.forms["historial_acad"].elements["STD_DT_START"].focus();
				return;
				}//cierre if(formato_fecha_inicio!=1)
		}//cierre if (fecha_nula == 0)
		
//		if ((o_ck_dip != 0)&&(fechafin_nula==0))
//			{
//			alert(msg_55);
//			document.forms["historial_acad"].elements["STD_DT_EARNED_EXPE"].focus();
//			return;
//			}
			
//		var anioscomp_nulo = comprobar_espacios("historial_acad","STD_COMPLETED_YEARS")
//		if (anioscomp_nulo == 0)
//		{
//		alert(msg_56);
//		document.forms["historial_acad"].elements["STD_COMPLETED_YEARS"].focus();
//		return;
//		}else{
//			if (isNaN(o_completed_years))
//			{
//			alert(msg_52);
//			document.forms["historial_acad"].elements["STD_COMPLETED_YEARS"].focus();
//			return;
//			}//cierre if (isNaN(o_completed_years))
//		}//cierre if (anioscomp_nulo == 0)	
		if (fechafin_nula!=0)
		{
			var formato_fecha_fin = comprobar_formato_fecha(o_dt_earned_expe);
			if(formato_fecha_fin!=1){
			alert(msg_57);
			document.forms["historial_acad"].elements["STD_DT_EARNED_EXPE"].focus();
			return;
			}else{
				if (o_dt_start>o_dt_earned_expe)
				{
				alert(msg_58);
				document.forms["historial_acad"].elements["STD_DT_START"].focus();
				return;
				}// cierre if (o_dt_start>o_dt_earned_expe)
			}//cierre if(formato_fecha_fin!=1)
		}//cierre if (fechafin_nula!=0)
	}//cierre accion = 3

	if (accion == 1)
		{ 
//		var o_total_years_dip = document.forms["historial_existente"].elements["STD_TOTAL_YEARS_DIP*1"].value; 
//		var o_completed_years=document.forms["historial_existente"].elements["STD_COMPLETED_YEARS*1"].value; 
		var o_dt_earned_expe = document.forms["historial_existente"].elements["STD_DT_EARNED_EXPE*1"].value; 
	    var o_id_diploma =  document.forms["historial_existente"].elements["STD_ID_DIPLOMA_1"].value;
		var o_dt_start = document.forms["historial_existente"].elements["STD_DT_START*1"].value; 
		//var o_id_educ_center = document.forms["historial_existente"].elements["STD_ID_EDU_CENTER*1"].value; 
		var o_id_edu_type = document.forms["historial_existente"].elements["STD_ID_EDU_TYPE*1"].value;
//		var o_ck_dip = document.forms["historial_existente"].elements["SCO_CK_END_DIP_V*1"].value;

//		var anios_nulo = comprobar_espacios("historial_existente","STD_TOTAL_YEARS_DIP*1")
//		if (anios_nulo == 0)
//		{
//		alert(msg_59);
//		document.forms["historial_existente"].elements["STD_TOTAL_YEARS_DIP*1"].focus();
//		return;
//		}else{
//			if (isNaN(o_total_years_dip))
//			{
//			alert(msg_52);
//			document.forms["historial_existente"].elements["STD_TOTAL_YEARS_DIP*1"].focus();
//			return;
//			}//cierre if (isNaN(o_total_years_dip))
//		}//cierre if (anios_nulo == 0)
		var fechafin_nula = comprobar_espacios("historial_existente","STD_DT_EARNED_EXPE*1");
		var fecha_nula = comprobar_espacios("historial_existente","STD_DT_START*1")
		if (fecha_nula == 0)
		{
		alert(msg_60);
		document.forms["historial_existente"].elements["STD_DT_START*1"].focus();
		return;
		}else{
			var formato_fecha_inicio = comprobar_formato_fecha(o_dt_start);
			if(formato_fecha_inicio!=1){
			alert(msg_61);
			document.forms["historial_existente"].elements["STD_DT_START*1"].focus();
			return;
			}//cierre if(formato_fecha_inicio!=1)
		}//cierre if (fecha_nula == 0)
//		if ((o_ck_dip != 0)&&(fechafin_nula==0))
//		{
//		alert(msg_55);
//		document.forms["historial_existente"].elements["STD_DT_EARNED_EXPE*1"].focus();
//		return;
//		}
//		var anioscomp_nulo = comprobar_espacios("historial_existente","STD_COMPLETED_YEARS*1")
//		if (anioscomp_nulo == 0)
//		{
//		alert(msg_62);
//		document.forms["historial_existente"].elements["STD_COMPLETED_YEARS*1"].focus();
//		return;
//		}else{
//			if (isNaN(o_completed_years))
//			{
//			alert(msg_52);
//			document.forms["historial_existente"].elements["STD_COMPLETED_YEARS*1"].focus();
//			return;
//			}//cierre if (isNaN(o_completed_years))
//		}//cierre if (anioscomp_nulo == 0)
		if (fechafin_nula!=0){
			var formato_fecha_fin = comprobar_formato_fecha(o_dt_earned_expe);
			if(formato_fecha_fin!=1){
			alert(msg_57);
			document.forms["historial_existente"].elements["STD_DT_EARNED_EXPE*1"].focus();
			return;
			}else{
				if (o_dt_start>o_dt_earned_expe)
				{
				alert(msg_58);
				document.forms["historial_existente"].elements["STD_DT_START*1"].focus();
				return;
				}//fin del if (o_dt_start>o_dt_earned_expe)
			}// cierre if(formato_fecha_fin!=1)
		}//cierre if (fechafin_nula!=0)

	}// fin accion = 1
	
	comprobar_hist_acad(accion_bbdd);
}//function validar_datos_form_acad(accion)



//***************************
//Funciones de la pagina de formacion complementaria js_formacion_complementaria.jsp
//FECHA: 28/11/2000
//***************************

	function comprobar_form_complem(accion_a_tomar)
	{
		var action = accion_a_tomar;
		var informacion = "";
	 
   	    if (informacion != "")
   	    { informacion += "{";}
  
		var n_nodo = "_NODO*" + "0";
		var n_registro = "_REGISTRO*" + "0";
		

		if (action == 2)
		{
		informacion = "_NODO=SCO_CURR_COMP_BACKGROUND{_ACCION=DELETE{_REGISTRO=";
		informacion += document.forms["formacion_existente"].elements["_REGISTRO*1"].value;
	    document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		forma_parametro.submit();
		return;
		}

		var id_country = "STD_ID_COUNTRY*" +"0";
		var n_country = "STD_N_COUNTRY*" +"0";
		var n_course = "SCO_N_COURSE*" + "0";
		var n_center = "SCO_N_CENTER*" + "0";
		var number_hours = "SCO_NUMBER_HOURS*" + "0";
		var dt_start = "SCO_DT_START*" + "0";
		var dt_end = "SCO_DT_END*" +"0";
		var grants = "SCO_GRANTS*" +"0";
		var coment = "SCO_COMMENT*" +"0";

		var id_tipocurso = "CMX_TIPOSCURSO*" +"0";
		var n_tipocurso = "CMX_N_TIPOCURSO*" +"0";
		var id_gradoavance = "STD_ID_EDU_TYPE*" +"0";
		var n_gradoavance = "STD_N_EDU_TYPE*" +"0";

		var id_estado = "STD_ID_GEO_DIV*" +"0";
		var n_estado = "STD_N_GEO_DIV*" +"0";
		var id_area = "CFP_ID_AREA_GENERAL*" +"0";
		var n_area = "CFP_N_AREA_GENERAL*" +"0";
		var reconocimineto = "CMX_N_RECONOCIMIENTO*" +"0";


		var v_nodo = document.forms["formacion_comp"].elements["_NODO*0"].value;
		var formulario = "";

		if (action == 1)
		{
			var v_registro = document.forms["formacion_existente"].elements["_REGISTRO*1"].value;
			var v_accion_canal = "UPDATE";
			formulario = "formacion_existente";

    		//var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY*1"].value;
//			var v_n_country = document.forms[formulario].elements["STD_ID_COUNTRY*1"].options[document.forms[formulario].elements["STD_ID_COUNTRY*1"].selectedIndex].text;
			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY_1"].value;
			var v_n_country = document.forms[formulario].elements["STD_N_COUNTRY_1"].value;
			
			var v_n_course = 	document.forms[formulario].elements["SCO_N_COURSE*1"].value;
			var v_n_center = document.forms[formulario].elements["SCO_N_CENTER*1"].value;
			var v_number_hours = document.forms[formulario].elements["SCO_NUMBER_HOURS*1"].value;
			var v_dt_start = document.forms[formulario].elements["SCO_DT_START*1"].value;
			var v_dt_end = document.forms[formulario].elements["SCO_DT_END*1"].value;
			//var v_grants = document.forms[formulario].elements["SCO_GRANTS*1"].value;
			//var v_coment = document.forms[formulario].elements["SCO_COMMENT*1"].value;

			var v_id_tipocurso = document.forms[formulario].elements["CMX_TIPOSCURSO*1"].value;
			var v_n_tipocurso = document.forms[formulario].elements["CMX_TIPOSCURSO*1"].options[document.forms[formulario].elements["CMX_TIPOSCURSO*1"].selectedIndex].text;
			var v_id_gradoavance = document.forms[formulario].elements["STD_ID_EDU_TYPE*1"].value;
			var v_n_gradoavance = document.forms[formulario].elements["STD_ID_EDU_TYPE*1"].options[document.forms[formulario].elements["STD_ID_EDU_TYPE*1"].selectedIndex].text;

//			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV*1"].value;
//			var v_n_estado = document.forms[formulario].elements["STD_ID_GEO_DIV*1"].options[document.forms[formulario].elements["STD_ID_GEO_DIV*1"].selectedIndex].text;
			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV_1"].value;
			var v_n_estado = document.forms[formulario].elements["STD_N_GEO_DIV_1"].value;

			var v_id_area = document.forms[formulario].elements["CFP_ID_AREA_GENERAL_1"].value;
			var v_n_area = document.forms[formulario].elements["CFP_N_AREA_GENERAL_1"].value;

			//var v_reconocimineto = document.forms[formulario].elements["CMX_N_RECONOCIMIENTO*1"].value;

		}

		if (action == 3)
		{
			var v_registro =0
			var v_accion_canal = "INSERT";
			formulario = "formacion_comp";
		
			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY"].value;
//			var v_n_country = document.forms[formulario].elements["STD_ID_COUNTRY"].options[document.forms[formulario].elements["STD_ID_COUNTRY"].selectedIndex].text;
			var v_n_country = document.forms[formulario].elements["STD_N_COUNTRY"].value;
			var v_n_course = document.forms[formulario].elements["SCO_N_COURSE"].value;
			var v_n_center = document.forms[formulario].elements["SCO_N_CENTER"].value;
			var v_number_hours = document.forms[formulario].elements["SCO_NUMBER_HOURS"].value;
			var v_dt_start = document.forms[formulario].elements["SCO_DT_START"].value;
			var v_dt_end = document.forms[formulario].elements["SCO_DT_END"].value;
			//var v_grants = document.forms[formulario].elements["SCO_GRANTS"].value;
			//var v_coment = document.forms[formulario].elements["SCO_COMMENT"].value;

			var v_id_tipocurso = document.forms[formulario].elements["CMX_TIPOSCURSO"].value;
			var v_n_tipocurso = document.forms[formulario].elements["CMX_TIPOSCURSO"].options[document.forms[formulario].elements["CMX_TIPOSCURSO"].selectedIndex].text;
			var v_id_gradoavance = document.forms[formulario].elements["STD_ID_EDU_TYPE"].value;
			var v_n_gradoavance = document.forms[formulario].elements["STD_ID_EDU_TYPE"].options[document.forms[formulario].elements["STD_ID_EDU_TYPE"].selectedIndex].text;

			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV"].value;
//			var v_n_estado = document.forms[formulario].elements["STD_ID_GEO_DIV"].options[document.forms[formulario].elements["STD_ID_GEO_DIV"].selectedIndex].text;
			var v_n_estado = document.forms[formulario].elements["STD_N_GEO_DIV"].value;

			var v_id_area = document.forms[formulario].elements["CFP_ID_AREA_GENERAL_2"].value;
			var v_n_area = document.forms[formulario].elements["CFP_N_AREA_GENERAL_2"].value;

			//var v_reconocimineto = document.forms[formulario].elements["CMX_N_RECONOCIMIENTO"].value;

		}


		 informacion += n_nodo + "=" + v_nodo + "{_ACCION*0="+ v_accion_canal + "{";
		 informacion += n_registro + "=" + v_registro + "{";
		 informacion += id_country + "=" + v_id_country +  "{"; 
		 informacion += n_country + "=" + v_n_country +  "{"; 
		 informacion += n_course + "=" + v_n_course + "{"; 
		 informacion += n_center + "=" + v_n_center + "{"; 
		 informacion += number_hours + "=" + v_number_hours + "{"; 
		 informacion += dt_start + "=" + v_dt_start + "{"; 
		 //informacion += grants + "=" + v_grants + "{";
		 //informacion += coment + "=" + v_coment + "{"; 

		 informacion += id_tipocurso + "=" + v_id_tipocurso +  "{"; 
		 informacion += n_tipocurso + "=" + v_n_tipocurso +  "{"; 
		 informacion += id_gradoavance + "=" + v_id_gradoavance +  "{"; 
		 informacion += n_gradoavance + "=" + v_n_gradoavance +  "{";  

		 informacion += id_estado + "=" + v_id_estado +  "{"; 
		 informacion += n_estado + "=" + v_n_estado +  "{"; 
		 informacion += id_area + "=" + v_id_area +  "{"; 
		 informacion += n_area + "=" + v_n_area +  "{"; 

		 //informacion += reconocimineto + "=" + v_reconocimineto + "{"; 




		 if ((v_dt_end =="") || (v_dt_end == null))
			 informacion += dt_end + "=4000-01-01";
		 else
			 informacion += dt_end + "=" + v_dt_end;
			 document.forms["forma_parametro"].elements["parametro"].value = informacion;		
			 forma_parametro.submit();
   	}



	function validar_datos_form_compl(accion){

		var accion_bbdd = accion;
		var numeros = "0123456789";

		if (accion == 3)
		{
		
			
			var num_horas = document.forms["formacion_comp"].elements["SCO_NUMBER_HOURS"].value; 
			var fec_ini=document.forms["formacion_comp"].elements["SCO_DT_START"].value; 
			var fec_fin = document.forms["formacion_comp"].elements["SCO_DT_END"].value;  
			var curso_nulo = comprobar_espacios("formacion_comp","SCO_N_COURSE")
			if (curso_nulo == 0)
			{
			alert( msg_90);
			document.forms["formacion_comp"].elements["SCO_N_COURSE"].focus();
			return;
			}
			
			
			var centro_nulo = comprobar_espacios("formacion_comp","SCO_N_CENTER")
			if (centro_nulo == 0)
			{
			alert(msg_95);
			document.forms["formacion_comp"].elements["SCO_N_CENTER"].focus();
			return;
			}
			
			var pa_is = comprobar_espacios("formacion_comp","STD_ID_COUNTRY")
			if (pa_is == 0)
			{
			alert(msg_92);
			//document.forms["formacion_comp"].elements["STD_ID_COUNTRY"].focus();
			return;
			}
			
			var  es_ta = comprobar_espacios("formacion_comp","STD_ID_GEO_DIV")
			if (es_ta == 0)
			{
			alert(msg_93);
			//document.forms["formacion_comp"].elements["STD_ID_GEO_DIV"].focus();
			return;
			}
			var  ar_ea = comprobar_espacios("formacion_comp","CFP_ID_AREA_GENERAL_2")
			if (ar_ea == 0)
			{
			alert(msg_94);
			document.forms["formacion_comp"].elements["CFP_ID_AREA_GENERAL_2"].focus();
			return;
			}
			
						
			
			if ((num_horas !="") && (isNaN(num_horas)))
			{
				alert(msg_65);
				document.forms["formacion_comp"].elements["SCO_NUMBER_HOURS"].focus();
				return;
			}
		    var fecha_nula = comprobar_espacios("formacion_comp","SCO_DT_START")
			if (fecha_nula == 0)
			{
			alert(msg_60);
			document.forms["formacion_comp"].elements["SCO_DT_START"].focus();
			return;
			}else{
					var formato_fecha=comprobar_formato_fecha(document.forms["formacion_comp"].elements["SCO_DT_START"].value);
						if(formato_fecha==0){
						alert(msg_61);
						document.forms["formacion_comp"].elements["SCO_DT_START"].focus();
							return;
								}//cierre if (formato_fecha==0)
				else{
						if ((str2date(document.FECHAS.FECHA_HOY.value)<=strformatodate(document.forms["formacion_comp"].elements["SCO_DT_START"].value)))
						{
						alert("La fecha de inicio  no puede ser mayor a hoy");
						return;		}
					}
			}//cierre if (fecha_nula == 0)
			if (fec_fin != "")
			{
				
				var formato_fecha=comprobar_formato_fecha(document.forms["formacion_comp"].elements["SCO_DT_END"].value);
				if(formato_fecha==0)
				{
					alert(msg_61);
					document.forms["formacion_comp"].elements["SCO_DT_END"].focus();
					return;
				}
				else
				{
					if (fec_ini>fec_fin)
					{
						alert(msg_58);
						document.forms["formacion_comp"].elements["SCO_DT_END"].focus();
						return;
					}//cierre if (fec_ini>fec_fin)
				}//cierre if (formato_fecha==0)
				
						if ((str2date(document.FECHAS.FECHA_HOY.value)<=strformatodate(document.forms["formacion_comp"].elements["SCO_DT_END"].value)))
						{
						alert("La fecha de fin no puede ser mayor a hoy");
						return;	
						}
			
			}//cierre (fechafin_nula != "")
}//cierre if accion =3 

		if (accion == 1)
		{

			var num_horas = document.forms["formacion_existente"].elements["SCO_NUMBER_HOURS*1"].value; 
			var fec_ini=document.forms["formacion_existente"].elements["SCO_DT_START*1"].value; 
			var fec_fin = document.forms["formacion_existente"].elements["SCO_DT_END*1"].value;  
			var curso_nulo = comprobar_espacios("formacion_existente","SCO_N_COURSE*1")
			if (curso_nulo == 0)
			{
			alert( msg_90);
			document.forms["formacion_existente"].elements["SCO_N_COURSE*1"].focus();
			return;
			}
		    var centro_nulo = comprobar_espacios("formacion_existente","SCO_N_CENTER*1")
			if (centro_nulo == 0)
			{
			alert( msg_91);
			document.forms["formacion_existente"].elements["SCO_N_CENTER*1"].focus();
			return;
			}
			if ((num_horas !="") && (isNaN(num_horas)))
			{
				alert(msg_66);
				document.forms["formacion_existente"].elements["SCO_NUMBER_HOURS*1"].focus();
				return;
			}
		    var fecha_nula = comprobar_espacios("formacion_existente","SCO_DT_START*1")
			if (fecha_nula == 0)
			{
						alert(msg_60);
							document.forms["formacion_existente"].elements["SCO_DT_START*1"].focus();
							return;
			}else{
							var formato_fecha=comprobar_formato_fecha(document.forms["formacion_existente"].elements["SCO_DT_START*1"].value);
							if(formato_fecha==0){
							alert(msg_61);
							document.forms["formacion_existente"].elements["SCO_DT_START*1"].focus();
							return;
							}//cierre if (formato_fecha==0)
				else{
				     if ((str2date(document.FECHAS.FECHA_HOY.value)<=strformatodate(document.forms["formacion_existente"].elements["SCO_DT_START*1"].value)))
						{
							alert("La fecha de inicio no puede ser mayor a hoy");
							return;
						}	
					}
					
			}//cierre if (fecha_nula == 0)

			if (fec_fin != "")
			{
				var formato_fecha=comprobar_formato_fecha(document.forms["formacion_existente"].elements["SCO_DT_END*1"].value);
				if(formato_fecha==0)
				{
					alert(msg_67);
					document.forms["formacion_existente"].elements["SCO_DT_END*1"].focus();
					return;
				}
				else
				{
					if (fec_ini>fec_fin)
					{
						alert(msg_58);
						document.forms["formacion_existente"].elements["SCO_DT_END*1"].focus();
						return;
					}//cierre if (fec_ini>fec_fin)
				}//cierre if (formato_fecha==0)
				if ((str2date(document.FECHAS.FECHA_HOY.value)<=strformatodate(document.forms["formacion_existente"].elements["SCO_DT_END*1"].value)))
				{
				alert("La fecha de fin no puede ser mayor a hoy");
				return;
				}	
			}//cierre fec_fin != ""
		}//cierre if accion = 1
	comprobar_form_complem(accion_bbdd);
}//cierre funcion

//***************************
//Funciones de la pagina de docencia js_docencia.jsp
//FECHA: 04/04/2005
//***************************
	function comprobar_docencia(accion_a_tomar)
	{
		var action = accion_a_tomar;
		var informacion = "";
	 
   	    if (informacion != "")
   	    { informacion += "{";}  

		var n_nodo = "_NODO*" + "0";
		var n_registro = "_REGISTRO*" + "0";

		if (action == 2)
		{
		informacion = "_NODO=CMX_X_DOCENCIA{_ACCION=DELETE{_REGISTRO=";
		informacion += document.forms["docencia_existente"].elements["_REGISTRO*1"].value;
	    document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		forma_parametro.submit();

		return;
		}

		var id_country = "STD_ID_COUNTRY*" +"0";
		var n_country = "STD_N_COUNTRY*" +"0";
		var id_estado = "STD_ID_GEO_DIV*" +"0";
		var n_estado = "STD_N_GEO_DIV*" +"0";
		var id_area = "CFP_ID_AREA_GENERAL*" +"0";
		var n_area = "CFP_N_AREA_GENERAL*" +"0";
		var materia = "CMX_N_MATERIA*" +"0";
		var otra_institucion = "CMX_N_OTRAINSTITUC*" +"0";
		var mes_inicio = "CMX_N_MESINICIO*" +"0";
		var ano_inicio = "CMX_N_ANOINICIO*" +"0";
		var mes_fin = "CMX_N_MESFIN*" +"0";
		var ano_fin = "CMX_N_ANOFIN*" +"0";
		var id_diploma = "STD_ID_DIPLOMA*" +"0";
		var n_diploma = "STD_N_DIPLOMA*" +"0";
		var id_educ_center = "SCO_ID_EDUC_CENTER*" + "0";
		var n_educ_center = "STD_N_EXT_ORG*" + "0";


		var v_nodo = document.forms["docencia"].elements["_NODO*0"].value;
		var formulario = "";

		if (action == 1)
		{
			var v_registro = document.forms["docencia_existente"].elements["_REGISTRO*1"].value;
			var v_accion_canal = "UPDATE";
			formulario = "docencia_existente";

//			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY*1"].value;
//			var v_n_country = document.forms[formulario].elements["STD_ID_COUNTRY*1"].options[document.forms[formulario].elements["STD_ID_COUNTRY*1"].selectedIndex].text;
			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY_1"].value;
			var v_n_country = document.forms[formulario].elements["STD_N_COUNTRY_1"].value;

//			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV*1"].value;
//			var v_n_estado = document.forms[formulario].elements["STD_ID_GEO_DIV*1"].options[document.forms[formulario].elements["STD_ID_GEO_DIV*1"].selectedIndex].text;
			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV_1"].value;
			var v_n_estado = document.forms[formulario].elements["STD_N_GEO_DIV_1"].value;

			var v_id_area = document.forms[formulario].elements["CFP_ID_AREA_GENERAL_1"].value;
			var v_n_area = document.forms[formulario].elements["CFP_N_AREA_GENERAL_1"].value;

			var v_materia = document.forms[formulario].elements["CMX_N_MATERIA*1"].value;
			var v_otra_institucion = document.forms[formulario].elements["CMX_N_OTRAINSTITUC*1"].value;
			var v_mes_inicio = document.forms[formulario].elements["CMX_N_MESINICIO*1"].value;
			var v_ano_inicio = document.forms[formulario].elements["CMX_N_ANOINICIO*1"].value;
			var v_mes_fin = document.forms[formulario].elements["CMX_N_MESFIN*1"].value;
			var v_ano_fin = document.forms[formulario].elements["CMX_N_ANOFIN*1"].value;

			var v_id_diploma = document.forms[formulario].elements["STD_ID_DIPLOMA*1"].value;
			var v_n_diploma = document.forms[formulario].elements["STD_ID_DIPLOMA*1"].options[document.forms[formulario].elements["STD_ID_DIPLOMA*1"].selectedIndex].text;

			//var v_id_educ_center=document.forms[formulario].elements["SCO_ID_EDUC_CENTER*1"].value; 
			//var v_n_educ_center=document.forms[formulario].elements["SCO_ID_EDUC_CENTER*1"].options[document.forms[formulario].elements["SCO_ID_EDUC_CENTER*1"].selectedIndex].text; 

		}

		if (action == 3)
		{
			var v_registro =0
			var v_accion_canal = "INSERT";
			formulario = "docencia";

			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY"].value;
//			var v_n_country = document.forms[formulario].elements["STD_ID_COUNTRY"].options[document.forms[formulario].elements["STD_ID_COUNTRY"].selectedIndex].text;
			var v_n_country = document.forms[formulario].elements["STD_N_COUNTRY"].value;

			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV"].value;
//			var v_n_estado = document.forms[formulario].elements["STD_ID_GEO_DIV"].options[document.forms[formulario].elements["STD_ID_GEO_DIV"].selectedIndex].text;
			var v_n_estado = document.forms[formulario].elements["STD_N_GEO_DIV"].value;

			var v_id_area = document.forms[formulario].elements["CFP_ID_AREA_GENERAL_2"].value;
			var v_n_area = document.forms[formulario].elements["CFP_N_AREA_GENERAL_2"].value;

			var v_materia = document.forms[formulario].elements["CMX_N_MATERIA"].value;
			var v_otra_institucion = document.forms[formulario].elements["CMX_N_OTRAINSTITUC"].value;
			var v_mes_inicio = document.forms[formulario].elements["CMX_N_MESINICIO"].value;
			var v_ano_inicio = document.forms[formulario].elements["CMX_N_ANOINICIO"].value;
			var v_mes_fin = document.forms[formulario].elements["CMX_N_MESFIN"].value;
			var v_ano_fin = document.forms[formulario].elements["CMX_N_ANOFIN"].value;

			var v_id_diploma = document.forms[formulario].elements["STD_ID_DIPLOMA"].value;
			var v_n_diploma = document.forms[formulario].elements["STD_ID_DIPLOMA"].options[document.forms[formulario].elements["STD_ID_DIPLOMA"].selectedIndex].text;

			//var v_id_educ_center = document.forms[formulario].elements["SCO_ID_EDUC_CENTER"].value; 
		//	var v_n_educ_center = document.forms[formulario].elements["SCO_ID_EDUC_CENTER"].options[document.forms[formulario].elements["SCO_ID_EDUC_CENTER"].selectedIndex].text; 
					

	
		}

		 informacion += n_nodo + "=" + v_nodo + "{_ACCION*0="+ v_accion_canal + "{";
		 informacion += n_registro + "=" + v_registro + "{";

		 informacion += id_country + "=" + v_id_country +  "{"; 
		 informacion += n_country + "=" + v_n_country +  "{"; 

		 informacion += id_estado + "=" + v_id_estado +  "{"; 
		 informacion += n_estado + "=" + v_n_estado +  "{"; 
		 informacion += id_area + "=" + v_id_area +  "{"; 
		 informacion += n_area + "=" + v_n_area +  "{"; 

		 informacion += materia + "=" + v_materia + "{"; 

//alert("Pasa2");		 		 
//alert(v_otra_institucion);		 
	     if ((v_otra_institucion != null) && (v_otra_institucion != ""))
	     {
		     informacion += otra_institucion + "=" + v_otra_institucion + "{"; 		 	
		 }	 		 												 
		 
		 informacion += mes_inicio + "=" + v_mes_inicio + "{"; 
		 informacion += ano_inicio + "=" + v_ano_inicio + "{"; 
		 informacion += mes_fin + "=" + v_mes_fin + "{"; 
		 informacion += ano_fin + "=" + v_ano_fin + "{"; 
		 informacion += id_diploma + "=" + v_id_diploma +  "{"; 
		 informacion += n_diploma + "=" + v_n_diploma +  "{"; 

		// informacion += id_educ_center + "=" + v_id_educ_center + "{"; 
		// informacion += n_educ_center + "=" + v_n_educ_center;
//alert(informacion);
		
		 document.forms["forma_parametro"].elements["parametro"].value = informacion;			 	
		 forma_parametro.submit();
   	}

	function validar_datos_docencia(accion){

		var accion_bbdd = accion;
		var numeros = "0123456789";

		if (accion == 3)
		{					
			var materia_nulo = comprobar_espacios("docencia","CMX_N_MATERIA")
			if (materia_nulo == 0)
			{
			alert(msg_64);
			document.forms["docencia"].elements["CMX_N_MATERIA"].focus();
			return;
			}
			
		}//cierre if accion =3 

		if (accion == 1)
		{

			var materia_nulo = comprobar_espacios("docencia_existente","CMX_N_MATERIA*1")
			if (materia_nulo == 0)
			{
			alert(msg_63);
			document.forms["docencia_existente"].elements["CMX_N_MATERIA*1"].focus();
			return;
			}
		}//cierre if accion = 1

	comprobar_docencia(accion_bbdd);

}//cierre funcion

//***************************
//Funciones de la pagina de asociacion js_asociacion.jsp
//FECHA: 04/04/2005
//***************************
	function comprobar_asociacion(accion_a_tomar)
	{
		var action = accion_a_tomar;
		var informacion = "";
		//alert(action);
		//alert(document.forms["asociacion_existente"].elements["STD_ID_COUNTRY_1"].value);
	 
   	    if (informacion != "")
   	    { informacion += "{";}  

		var n_nodo = "_NODO*" + "0";
		var n_registro = "_REGISTRO*" + "0";

		if (action == 2)
		{
		informacion = "_NODO=CMX_X_ASOCIACIONES{_ACCION=DELETE{_REGISTRO=";
		informacion += document.forms["asociacion_existente"].elements["_REGISTRO*1"].value;
	    document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		forma_parametro.submit();
		return;
		}

		var id_country = "STD_ID_COUNTRY*" +"0";
		var n_country = "STD_N_COUNTRY*" +"0";
		var id_estado = "STD_ID_GEO_DIV*" +"0";
		var n_estado = "STD_N_GEO_DIV*" +"0";
		var asociacion = "CMX_N_ASOCIACION*" +"0";
		var mes_ingreso = "CMX_N_MESINGRESO*" +"0";
		var ano_ingreso = "CMX_N_ANOINGRESO*" +"0";
		var cargo = "CMX_N_CARGO*" +"0";

		var v_nodo = document.forms["asociacion"].elements["_NODO*0"].value;
		var formulario = "";

		if (action == 1)
		{
			var v_registro = document.forms["asociacion_existente"].elements["_REGISTRO*1"].value;
			var v_accion_canal = "UPDATE";
			formulario = "asociacion_existente";

 			//var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY*1"].value;
			//var v_n_country = document.forms[formulario].elements["STD_ID_COUNTRY*1"].options[document.forms[formulario].elements["STD_ID_COUNTRY*1"].selectedIndex].text;
			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY_1"].value;
			var v_n_country = document.forms[formulario].elements["STD_N_COUNTRY_1"].value;

 		//	var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV*1"].value;
			//var v_n_estado = document.forms[formulario].elements["STD_ID_GEO_DIV*1"].options[document.forms[formulario].elements["STD_ID_GEO_DIV*1"].selectedIndex].text;
			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV_1"].value;
			var v_n_estado = document.forms[formulario].elements["STD_N_GEO_DIV_1"].value;

			var v_asociacion = document.forms[formulario].elements["CMX_N_ASOCIACION*1"].value;
			var v_mes_ingreso = document.forms[formulario].elements["CMX_N_MESINGRESO*1"].value;
			var v_ano_ingreso = document.forms[formulario].elements["CMX_N_ANOINGRESO*1"].value;

			var v_cargo = document.forms[formulario].elements["CMX_N_CARGO*1"].value;

		}

		if (action == 3)
		{
			var v_registro =0
			var v_accion_canal = "INSERT";
			formulario = "asociacion";

			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY"].value;
  			//var v_n_country = document.forms[formulario].elements["STD_ID_COUNTRY"].options[document.forms[formulario].elements["STD_ID_COUNTRY"].selectedIndex].text;
			var v_n_country = document.forms[formulario].elements["STD_N_COUNTRY"].value;

			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV"].value;
			//var v_n_estado = document.forms[formulario].elements["STD_ID_GEO_DIV"].options[document.forms[formulario].elements["STD_ID_GEO_DIV"].selectedIndex].text;
			var v_n_estado = document.forms[formulario].elements["STD_N_GEO_DIV"].value;

			var v_asociacion = document.forms[formulario].elements["CMX_N_ASOCIACION"].value;

			var v_mes_ingreso = document.forms[formulario].elements["CMX_N_MESINGRESO"].value;
			var v_ano_ingreso = document.forms[formulario].elements["CMX_N_ANOINGRESO"].value;

			var v_cargo = document.forms[formulario].elements["CMX_N_CARGO"].value;
	
		}

		 informacion += n_nodo + "=" + v_nodo + "{_ACCION*0="+ v_accion_canal + "{";
		 informacion += n_registro + "=" + v_registro + "{";

		 informacion += id_country + "=" + v_id_country +  "{"; 
		 informacion += n_country + "=" + v_n_country +  "{"; 

		 informacion += id_estado + "=" + v_id_estado +  "{"; 
		 informacion += n_estado + "=" + v_n_estado +  "{"; 

		 informacion += asociacion + "=" + v_asociacion + "{"; 

		 informacion += mes_ingreso + "=" + v_mes_ingreso + "{"; 
		 informacion += ano_ingreso + "=" + v_ano_ingreso + "{";
		 
		 informacion += cargo + "=" + v_cargo + "{"; 		


		 document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		 //alert(informacion);
		 forma_parametro.submit();
   	}

	function validar_datos_asociacion(accion){

		var accion_bbdd = accion;
		var numeros = "0123456789";

		if (accion == 3)
		{					
		 	if(document.forms["asociacion"].elements["CMX_N_MESINGRESO"].value!="")
			{
				 if(!validaNumEntero(document.forms["asociacion"].elements["CMX_N_MESINGRESO"].value))
				 {
					 alert("El campo mes debe ser númerico");
						return;
				 }
			}
			if(document.forms["asociacion"].elements["CMX_N_ANOINGRESO"].value!="")
			{
				 if(!validaNumEntero(document.forms["asociacion"].elements["CMX_N_ANOINGRESO"].value))
				 {
					 alert("El campo Año debe ser númerico");
						return;
				 }
			}
			
			var asociacion_nulo = comprobar_espacios("asociacion","CMX_N_ASOCIACION")
			if (asociacion_nulo == 0)
			{
			alert(msg_96);
			document.forms["asociacion"].elements["CMX_N_ASOCIACION"].focus();
			return;
			}
			
			
			if(document.forms["asociacion"].elements["STD_ID_COUNTRY"].value=="")
			{
			alert("el campo pais es obligatorio");
			return;
			}
			
			if(document.forms["asociacion"].elements["STD_ID_GEO_DIV"].value=="")
			{
			alert("el campo estado es obligatorio");
			return;
			}
		    if (document.forms["asociacion"].elements["CMX_N_MESINGRESO"].value > 12)
		    {
		        alert("El mes no puede ser mayor de 12");
			    document.forms["asociacion"].elements["CMX_N_MESINGRESO"].focus();
			    return;				
		    }			
			
		}//cierre if accion =3 

		if (accion == 1)
		{
            
			if(document.forms["asociacion_existente"].elements["STD_ID_COUNTRY_1"].value=="")
			{
					 alert("El campo pais es obligatorio");
						return;
				
			}
			if(document.forms["asociacion_existente"].elements["STD_ID_GEO_DIV_1"].value=="")
			{
					 alert("El campo estado es obligatorio");
						return;
				
			}
			if(document.forms["asociacion_existente"].elements["CMX_N_MESINGRESO*1"].value!="")
			{
				 if(!validaNumEntero(document.forms["asociacion_existente"].elements["CMX_N_MESINGRESO*1"].value))
				 {
					 alert("El campo mes debe ser númerico");
						return;
				 }
			}
			if(document.forms["asociacion_existente"].elements["CMX_N_ANOINGRESO*1"].value!="")
			{
				 if(!validaNumEntero(document.forms["asociacion_existente"].elements["CMX_N_ANOINGRESO*1"].value))
				 {
					 alert("El campo Año debe ser númerico");
						return;
				 }
			}
			var asociacion_nulo = comprobar_espacios("asociacion_existente","CMX_N_ASOCIACION*1")
			if (asociacion_nulo == 0)
			{
			alert(msg_63);
			document.forms["asociacion_existente"].elements["CMX_N_ASOCIACION*1"].focus();
			return;			
			}
		    if (document.forms["asociacion_existente"].elements["CMX_N_MESINGRESO*1"].value > 12)
		    {
		        alert("El mes no puede ser mayor de 12");
			    document.forms["asociacion_existente"].elements["CMX_N_MESINGRESO*1"].focus();
			    return;				
		    }			
		}//cierre if accion = 1
	comprobar_asociacion(accion_bbdd);

}//cierre funcion

//***************************
//Funciones de la pagina de habilidades js_habilidades.jsp
//FECHA: 04/04/2005
//***************************
	function comprobar_habilidades(accion_a_tomar)
	{
		var action = accion_a_tomar;
		var informacion = "";
	 
   	    if (informacion != "")
   	    { informacion += "{";}  

		var n_nodo = "_NODO*" + "0";
		var n_registro = "_REGISTRO*" + "0";

		if (action == 2)
		{
		informacion = "_NODO=CMX_X_CURR_HABILIDADES{_ACCION=DELETE{_REGISTRO=";
		informacion += document.forms["habilidades_existente"].elements["_REGISTRO*1"].value;
	    document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		forma_parametro.submit();
		return;
		}

//		var id_habilidad = "CMX_ID_HABILIDAD*" +"0";
//		var n_habilidad = "CMX_N_HABILIDAD*" +"0";

		var id_nivel = "CMX_ID_NIVELCONOCIMIENTO*" +"0";
		var n_nivel = "CMX_N_NIVELCONOCIMIENTO*" +"0";

//		var id_area = "CFP_ID_AREA_GENERAL*" +"0";
//		var n_area = "CFP_N_AREA_GENERAL*" +"0";

		var otra_habilidad = "CMX_N_OTRAHABILIDAD*" +"0";

		var v_nodo = document.forms["habilidades"].elements["_NODO*0"].value;
		var formulario = "";

		if (action == 1)
		{
			var v_registro = document.forms["habilidades_existente"].elements["_REGISTRO*1"].value;
			var v_accion_canal = "UPDATE";
			formulario = "habilidades_existente";

//			var v_id_habilidad = document.forms[formulario].elements["CMX_ID_HABILIDAD*1"].value;
//			var v_n_habilidad = document.forms[formulario].elements["CMX_ID_HABILIDAD*1"].options[document.forms[formulario].elements["CMX_ID_HABILIDAD*1"].selectedIndex].text;

			var v_id_nivel = document.forms[formulario].elements["CMX_ID_NIVELCONOCIMIENTO*1"].value;
			var v_n_nivel = document.forms[formulario].elements["CMX_ID_NIVELCONOCIMIENTO*1"].options[document.forms[formulario].elements["CMX_ID_NIVELCONOCIMIENTO*1"].selectedIndex].text;

//			var v_id_area = document.forms[formulario].elements["CFP_ID_AREA_GENERAL*1"].value;
//			var v_n_area = document.forms[formulario].elements["CFP_N_AREA_GENERAL*1"].value;

			var v_otra_habilidad = document.forms[formulario].elements["CMX_N_OTRAHABILIDAD*1"].value;

		}

		if (action == 3)
		{
			var v_registro =0
			var v_accion_canal = "INSERT";
			formulario = "habilidades";

//			var v_id_habilidad = document.forms[formulario].elements["CMX_ID_HABILIDAD"].value;
//			var v_n_habilidad = document.forms[formulario].elements["CMX_ID_HABILIDAD"].options[document.forms[formulario].elements["CMX_ID_HABILIDAD"].selectedIndex].text;

			var v_id_nivel = document.forms[formulario].elements["CMX_ID_NIVELCONOCIMIENTO"].value;
			var v_n_nivel = document.forms[formulario].elements["CMX_ID_NIVELCONOCIMIENTO"].options[document.forms[formulario].elements["CMX_ID_NIVELCONOCIMIENTO"].selectedIndex].text;

//			var v_id_area = document.forms[formulario].elements["CFP_ID_AREA_GENERAL"].value;
//			var v_n_area = document.forms[formulario].elements["CFP_N_AREA_GENERAL"].value;

			var v_otra_habilidad = document.forms[formulario].elements["CMX_N_OTRAHABILIDAD"].value;
	
		}

		 informacion += n_nodo + "=" + v_nodo + "{_ACCION*0="+ v_accion_canal + "{";
		 informacion += n_registro + "=" + v_registro + "{";

//		 informacion += id_habilidad + "=" + v_id_habilidad +  "{"; 
//		 informacion += n_habilidad + "=" + v_n_habilidad +  "{"; 

		 informacion += id_nivel + "=" + v_id_nivel +  "{"; 
		 informacion += n_nivel + "=" + v_n_nivel +  "{"; 

//		 informacion += id_area + "=" + v_id_area +  "{"; 
//		 informacion += n_area + "=" + v_n_area +  "{"; 
		 
		 informacion += otra_habilidad + "=" + v_otra_habilidad + "{"; 		

		 document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		 forma_parametro.submit();
   	}

	function validar_datos_habilidades(accion){

		var accion_bbdd = accion;
		var numeros = "0123456789";

		if (accion == 3)
		{					
//			var habilidad_nulo = comprobar_espacios("habilidades","CMX_ID_HABILIDAD")
			var habilidad_nulo = comprobar_espacios("habilidades","CMX_N_OTRAHABILIDAD")
			if (habilidad_nulo == 0)
			{
//			alert(msg_64);
			alert("El campo de Habilidad no puede ir en blanco");
			document.forms["habilidades"].elements["CMX_N_OTRAHABILIDAD"].focus();
			return;
			}
//			if (document.forms["habilidades"].elements["CFP_ID_AREA_GENERAL"].value == "")
//			{	alert("El campo de área de conocimieto es obligatorio");
//				return;
//			}
			
		}//cierre if accion =3 

		if (accion == 1)
		{
//           if (document.forms["habilidades_existente"].elements["CFP_ID_AREA_GENERAL*1"].value == "")
//			{	alert("El campo de área de conocimieto es obligatorio");
//				return;
//			}
//			var habilidad_nulo = comprobar_espacios("habilidades_existente","CMX_ID_HABILIDAD*1")
			var habilidad_nulo = comprobar_espacios("habilidades_existente","CMX_N_OTRAHABILIDAD*1")
			if (habilidad_nulo == 0)
			{
//			alert(msg_63);
//			document.forms["habilidades_existente"].elements["CMX_ID_HABILIDAD*1"].focus();
			alert("El campo de Habilidad no puede ir en blanco");
			document.forms["habilidades_existente"].elements["CMX_N_OTRAHABILIDAD"].focus();
			return;
			}
		}//cierre if accion = 1

	comprobar_habilidades(accion_bbdd);

}//cierre funcion

//***************************
//Funciones de la pagina de pasatiempos js_pasatiempos.jsp
//FECHA: 04/04/2005
//***************************
	function comprobar_pasatiempos(accion_a_tomar)
	{
		var action = accion_a_tomar;
		var informacion = "";
	 
   	    if (informacion != "")
   	    { informacion += "{";}  

		var n_nodo = "_NODO*" + "0";
		var n_registro = "_REGISTRO*" + "0";

		if (action == 2)
		{
		informacion = "_NODO=CMX_X_PASATIEMPOS{_ACCION=DELETE{_REGISTRO=";
		informacion += document.forms["pasatiempos_existente"].elements["_REGISTRO*1"].value;
	    document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		forma_parametro.submit();
		return;
		}

		var id_frecuencia = "CMX_ID_FRECUENCIA*" +"0";
		var n_frecuencia = "CMX_N_FRECUENCIA*" +"0";

		var pasatiempo = "CMX_N_PASATIEMPO*" +"0";
		var unidad = "CMX_N_UNIDADESPASATIEMPO*" +"0";

		var v_nodo = document.forms["pasatiempos"].elements["_NODO*0"].value;
		var formulario = "";

		if (action == 1)
		{
			var v_registro = document.forms["pasatiempos_existente"].elements["_REGISTRO*1"].value;
			var v_accion_canal = "UPDATE";
			formulario = "pasatiempos_existente";

			var v_id_frecuencia = document.forms[formulario].elements["CMX_ID_FRECUENCIA*1"].value;
			var v_n_frecuencia = document.forms[formulario].elements["CMX_ID_FRECUENCIA*1"].options[document.forms[formulario].elements["CMX_ID_FRECUENCIA*1"].selectedIndex].text;

			var v_pasatiempo = document.forms[formulario].elements["CMX_N_PASATIEMPO*1"].value;
			var v_unidad = document.forms[formulario].elements["CMX_N_UNIDADESPASATIEMPO*1"].value;

		}

		if (action == 3)
		{
			var v_registro =0
			var v_accion_canal = "INSERT";
			formulario = "pasatiempos";

			var v_id_frecuencia = document.forms[formulario].elements["CMX_ID_FRECUENCIA"].value;
			var v_n_frecuencia = document.forms[formulario].elements["CMX_ID_FRECUENCIA"].options[document.forms[formulario].elements["CMX_ID_FRECUENCIA"].selectedIndex].text;

			var v_pasatiempo = document.forms[formulario].elements["CMX_N_PASATIEMPO"].value;
			var v_unidad = document.forms[formulario].elements["CMX_N_UNIDADESPASATIEMPO"].value;
	
		}

		 informacion += n_nodo + "=" + v_nodo + "{_ACCION*0="+ v_accion_canal + "{";
		 informacion += n_registro + "=" + v_registro + "{";

		 informacion += id_frecuencia + "=" + v_id_frecuencia +  "{"; 
		 informacion += n_frecuencia + "=" + v_n_frecuencia +  "{"; 
		 
		 informacion += pasatiempo + "=" + v_pasatiempo + "{"; 		
		 informacion += unidad + "=" + v_unidad + "{"; 		

		 document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		 forma_parametro.submit();
   	}

	function validar_datos_pasatiempos(accion){

		var accion_bbdd = accion;
		var numeros = "0123456789";

		if (accion == 3)
		{					

			var pasatiempo_nulo = comprobar_espacios("pasatiempos","CMX_ID_FRECUENCIA")
			if (pasatiempo_nulo == 0)
			{
			alert(msg_64);
			document.forms["pasatiempos"].elements["CMX_ID_FRECUENCIA"].focus();
			return;
			}

		}//cierre if accion =3 

		if (accion == 1)
		{

			var pasatiempo_nulo = comprobar_espacios("pasatiempos_existente","CMX_ID_FRECUENCIA*1")
			if (pasatiempo_nulo == 0)
			{
			alert(msg_63);
			document.forms["pasatiempos_existente"].elements["CMX_ID_FRECUENCIA*1"].focus();
			return;
			}
		}//cierre if accion = 1

	comprobar_pasatiempos(accion_bbdd);

}//cierre funcion

//***************************
//Funciones de la pagina de publicaciones js_publicaciones.jsp
//FECHA: 04/04/2005
//***************************
	function comprobar_publicaciones(accion_a_tomar)
	{
		var action = accion_a_tomar;
		var informacion = "";
	 
   	    if (informacion != "")
   	    { informacion += "{";}  

		var n_nodo = "_NODO*" + "0";
		var n_registro = "_REGISTRO*" + "0";

		if (action == 2)
		{
		informacion = "_NODO=CMX_X_PUBLICACIONES{_ACCION=DELETE{_REGISTRO=";
		informacion += document.forms["publicaciones_existente"].elements["_REGISTRO*1"].value;
	    document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		forma_parametro.submit();
		return;
		}

		var id_country = "STD_ID_COUNTRY*" +"0";
		var n_country = "STD_N_COUNTRY*" +"0";
		var id_estado = "STD_ID_GEO_DIV*" +"0";
		var n_estado = "STD_N_GEO_DIV*" +"0";
		var id_area = "CFP_ID_AREA_GENERAL*" +"0";
		var n_area = "CFP_N_AREA_GENERAL*" +"0";
		var publicacion = "CMX_N_PUBLICACION*" +"0";
		var editorial = "CMX_N_EDITORIAL*" +"0";
		var mes = "CMX_N_MES*" +"0";
		var ano = "CMX_N_ANO*" +"0";
		var id_diploma = "STD_ID_DIPLOMA*" +"0";
		var n_diploma = "STD_N_DIPLOMA*" +"0";

		var v_nodo = document.forms["publicaciones"].elements["_NODO*0"].value;
		var formulario = "";

		if (action == 1)
		{
			var v_registro = document.forms["publicaciones_existente"].elements["_REGISTRO*1"].value;
			var v_accion_canal = "UPDATE";
			formulario = "publicaciones_existente";

//			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY*1"].value;
//			var v_n_country = document.forms[formulario].elements["STD_ID_COUNTRY*1"].options[document.forms[formulario].elements["STD_ID_COUNTRY*1"].selectedIndex].text;
			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY_1"].value;
			var v_n_country = document.forms[formulario].elements["STD_N_COUNTRY_1"].value;

//			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV*1"].value;
//			var v_n_estado = document.forms[formulario].elements["STD_ID_GEO_DIV*1"].options[document.forms[formulario].elements["STD_ID_GEO_DIV*1"].selectedIndex].text;
			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV_1"].value;
			var v_n_estado = document.forms[formulario].elements["STD_N_GEO_DIV_1"].value;

			var v_id_area = document.forms[formulario].elements["CFP_ID_AREA_GENERAL*1"].value;
			var v_n_area = document.forms[formulario].elements["CFP_N_AREA_GENERAL*1"].value;

			var v_publicacion = document.forms[formulario].elements["CMX_N_PUBLICACION*1"].value;
			var v_editorial = document.forms[formulario].elements["CMX_N_EDITORIAL*1"].value;
			var v_mes = document.forms[formulario].elements["CMX_N_MES*1"].value;
			var v_ano = document.forms[formulario].elements["CMX_N_ANO*1"].value;

			var v_id_diploma = document.forms[formulario].elements["STD_ID_DIPLOMA*1"].value;
			var v_n_diploma = document.forms[formulario].elements["STD_ID_DIPLOMA*1"].options[document.forms[formulario].elements["STD_ID_DIPLOMA*1"].selectedIndex].text;

		}

		if (action == 3)
		{
			var v_registro =0
			var v_accion_canal = "INSERT";
			formulario = "publicaciones";

			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY"].value;
//			var v_n_country = document.forms[formulario].elements["STD_ID_COUNTRY"].options[document.forms[formulario].elements["STD_ID_COUNTRY"].selectedIndex].text;
			var v_n_country = document.forms[formulario].elements["STD_N_COUNTRY"].value;


			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV"].value;
//			var v_n_estado = document.forms[formulario].elements["STD_ID_GEO_DIV"].options[document.forms[formulario].elements["STD_ID_GEO_DIV"].selectedIndex].text;
			var v_n_estado = document.forms[formulario].elements["STD_N_GEO_DIV"].value;

			var v_id_area = document.forms[formulario].elements["CFP_ID_AREA_GENERAL"].value;
			var v_n_area = document.forms[formulario].elements["CFP_N_AREA_GENERAL"].value;

			var v_publicacion = document.forms[formulario].elements["CMX_N_PUBLICACION"].value;
			var v_editorial = document.forms[formulario].elements["CMX_N_EDITORIAL"].value;
			var v_mes = document.forms[formulario].elements["CMX_N_MES"].value;
			var v_ano = document.forms[formulario].elements["CMX_N_ANO"].value;

			var v_id_diploma = document.forms[formulario].elements["STD_ID_DIPLOMA"].value;
			var v_n_diploma = document.forms[formulario].elements["STD_ID_DIPLOMA"].options[document.forms[formulario].elements["STD_ID_DIPLOMA"].selectedIndex].text;
	
		}

		 informacion += n_nodo + "=" + v_nodo + "{_ACCION*0="+ v_accion_canal + "{";
		 informacion += n_registro + "=" + v_registro + "{";

		 informacion += id_country + "=" + v_id_country +  "{"; 
		 informacion += n_country + "=" + v_n_country +  "{"; 

		 informacion += id_estado + "=" + v_id_estado +  "{"; 
		 informacion += n_estado + "=" + v_n_estado +  "{"; 
		 informacion += id_area + "=" + v_id_area +  "{"; 
		 informacion += n_area + "=" + v_n_area +  "{"; 

		 informacion += publicacion + "=" + v_publicacion + "{"; 
		 informacion += editorial + "=" + v_editorial + "{"; 
		 informacion += mes + "=" + v_mes + "{"; 
		 informacion += ano + "=" + v_ano + "{"; 
		 informacion += id_diploma + "=" + v_id_diploma +  "{"; 
		 informacion += n_diploma + "=" + v_n_diploma +  "{"; 

//alert(informacion);		 
		 document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		 forma_parametro.submit();
   	}

	function validar_datos_publicaciones(accion){

		var accion_bbdd = accion;
		var numeros = "0123456789";

		if (accion == 3)
		{	
		   if(!validaNumEntero(document.forms["publicaciones"].elements["CMX_N_MES"].value))
		    { alert("El campo mes debe ser númerico");
		     return;
		    }
		   if(!validaNumEntero(document.forms["publicaciones"].elements["CMX_N_ANO"].value))
			 {
			   alert("El campo año debe ser númerico");
				return;
			}
		   if (document.forms["publicaciones"].elements["CFP_ID_AREA_GENERAL"].value=="")
			{
			alert("el campo área general obligatorio");
			return;
			}
			if (document.forms["publicaciones"].elements["STD_ID_COUNTRY"].value=="")
			{
			alert("el campo país general obligatorio");
			return;
			}	
			if (document.forms["publicaciones"].elements["STD_ID_GEO_DIV"].value=="")
			{
			alert("el campo estado general obligatorio");
			return;
			}		
						
			var publicacion_nulo = comprobar_espacios("publicaciones","CMX_N_PUBLICACION")
			if (publicacion_nulo == 0)
			{
			alert(msg_97);
			document.forms["publicaciones"].elements["CMX_N_PUBLICACION"].focus();
			return;
			}
			
		}//cierre if accion =3 

		if (accion == 1)
		{  if(!validaNumEntero(document.forms["publicaciones_existente"].elements["CMX_N_MES*1"].value))
			 {
			   alert("El campo mes debe ser númerico");
				return;
			}
			  if(!validaNumEntero(document.forms["publicaciones_existente"].elements["CMX_N_ANO*1"].value))
			 {
			   alert("El campo año debe ser númerico");
				return;
			}
            if (document.forms["publicaciones_existente"].elements["STD_ID_COUNTRY_1"].value=="")
			{
			alert("el campo país general obligatorio");
			return;
			}	
			if (document.forms["publicaciones_existente"].elements["STD_ID_GEO_DIV_1"].value=="")
			{
			alert("el campo estado general obligatorio");
			return;
			}		
			if (document.forms["publicaciones_existente"].elements["CFP_ID_AREA_GENERAL*1"].value=="")
			{
			alert("el campos área general es obligatorio");
			return;
			}
			var publicacion_nulo = comprobar_espacios("publicaciones_existente","CMX_N_PUBLICACION*1")
			if (publicacion_nulo == 0)
			{
			alert(msg_63);
			document.forms["publicaciones_existente"].elements["CMX_N_PUBLICACION*1"].focus();
			return;
			}
		}//cierre if accion = 1

	comprobar_publicaciones(accion_bbdd);

}//cierre funcion

//***************************
//Funciones de la pagina de reconocimientos js_reconocimientos.jsp
//FECHA: 04/04/2005
//***************************
	function comprobar_reconocimientos(accion_a_tomar)
	{
		var action = accion_a_tomar;
		var informacion = "";
	 
   	    if (informacion != "")
   	    { informacion += "{";}  

		var n_nodo = "_NODO*" + "0";
		var n_registro = "_REGISTRO*" + "0";

		if (action == 2)
		{
		informacion = "_NODO=CMX_X_RECONOCIMIENTOS{_ACCION=DELETE{_REGISTRO=";
		informacion += document.forms["reconocimientos_existente"].elements["_REGISTRO*1"].value;
	    document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		forma_parametro.submit();
		return;
		}

		var id_country = "STD_ID_COUNTRY*" +"0";
		var n_country = "STD_N_COUNTRY*" +"0";
		var id_estado = "STD_ID_GEO_DIV*" +"0";
		var n_estado = "STD_N_GEO_DIV*" +"0";
		var reconocimiento = "CMX_N_RECONOCIMIENTO*" +"0";
		var otorgado = "CMX_N_OTORGADO*" +"0";
		var mes = "CMX_N_MES*" +"0";
		var ano = "CMX_N_ANO*" +"0";
		var docto = "CMX_DOCTO_SOPORTE*" +"0";

		var v_nodo = document.forms["reconocimientos"].elements["_NODO*0"].value;
		var formulario = "";

		if (action == 1)
		{
			var v_registro = document.forms["reconocimientos_existente"].elements["_REGISTRO*1"].value;
			var v_accion_canal = "UPDATE";
			formulario = "reconocimientos_existente";

//			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY*1"].value;
//			var v_n_country = document.forms[formulario].elements["STD_ID_COUNTRY*1"].options[document.forms[formulario].elements["STD_ID_COUNTRY*1"].selectedIndex].text;
			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY_1"].value;
			var v_n_country = document.forms[formulario].elements["STD_N_COUNTRY_1"].value;

//			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV*1"].value;
//			var v_n_estado = document.forms[formulario].elements["STD_ID_GEO_DIV*1"].options[document.forms[formulario].elements["STD_ID_GEO_DIV*1"].selectedIndex].text;
			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV_1"].value;
			var v_n_estado = document.forms[formulario].elements["STD_N_GEO_DIV_1"].value;

			var v_reconocimiento = document.forms[formulario].elements["CMX_N_RECONOCIMIENTO*1"].value;
			var v_docto = document.forms[formulario].elements["CMX_DOCTO_SOPORTE*1"].value;
			var v_otorgado = document.forms[formulario].elements["CMX_N_OTORGADO*1"].value;
			var v_mes = document.forms[formulario].elements["CMX_N_MES*1"].value;
			var v_ano = document.forms[formulario].elements["CMX_N_ANO*1"].value;

		}

		if (action == 3)
		{
			var v_registro =0
			var v_accion_canal = "INSERT";
			formulario = "reconocimientos";

			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY"].value;
//			var v_n_country = document.forms[formulario].elements["STD_ID_COUNTRY"].options[document.forms[formulario].elements["STD_ID_COUNTRY"].selectedIndex].text;
			var v_n_country = document.forms[formulario].elements["STD_N_COUNTRY"].value;

			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV"].value;
//			var v_n_estado = document.forms[formulario].elements["STD_ID_GEO_DIV"].options[document.forms[formulario].elements["STD_ID_GEO_DIV"].selectedIndex].text;
			var v_n_estado = document.forms[formulario].elements["STD_N_GEO_DIV"].value;

			var v_reconocimiento = document.forms[formulario].elements["CMX_N_RECONOCIMIENTO"].value;
			var v_otorgado = document.forms[formulario].elements["CMX_N_OTORGADO"].value;
			var v_docto = document.forms[formulario].elements["CMX_DOCTO_SOPORTE"].value;
			var v_mes = document.forms[formulario].elements["CMX_N_MES"].value;
			var v_ano = document.forms[formulario].elements["CMX_N_ANO"].value;
	
		}

		 informacion += n_nodo + "=" + v_nodo + "{_ACCION*0="+ v_accion_canal + "{";
		 informacion += n_registro + "=" + v_registro + "{";

		 informacion += id_country + "=" + v_id_country +  "{"; 
		 informacion += n_country + "=" + v_n_country +  "{"; 

		 informacion += id_estado + "=" + v_id_estado +  "{"; 
		 informacion += n_estado + "=" + v_n_estado +  "{"; 

		 informacion += reconocimiento + "=" + v_reconocimiento + "{"; 
		 informacion += docto + "=" + v_docto + "{"; 
		 informacion += otorgado + "=" + v_otorgado + "{"; 
		 informacion += mes + "=" + v_mes + "{"; 
		 informacion += ano + "=" + v_ano + "{"; 

		 document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		 forma_parametro.submit();
   	}

	function validar_datos_reconocimientos(accion){

		var accion_bbdd = accion;
		var numeros = "0123456789";

		if (accion == 3)
		{					
			if (document.forms["reconocimientos"].elements["STD_ID_COUNTRY"].value == "")
			{
			alert("El campo país es  obligatorio");
			return;
			}
			if (document.forms["reconocimientos"].elements["STD_ID_GEO_DIV"].value == "")
			{
			alert("El campo estado es  obligatorio");
			return;
			}
			var reconocimiento_nulo = comprobar_espacios("reconocimientos","CMX_N_RECONOCIMIENTO")
			if (reconocimiento_nulo == 0)
			{
			alert(msg_98);
			document.forms["reconocimientos"].elements["CMX_N_RECONOCIMIENTO"].focus();
			return;
			}
			if(!validaNumEntero(document.forms["reconocimientos"].elements["CMX_N_MES"].value))
			 { alert("El campo mes debe ser númerico");
				return;
			 }
			 
			if(!validaNumEntero(document.forms["reconocimientos"].elements["CMX_N_ANO"].value))
			 { alert("El campo año debe ser númerico");
				return;
			 }
			
		}//cierre if accion =3 

		if (accion == 1)
		{
           if (document.forms["reconocimientos_existente"].elements["STD_ID_COUNTRY_1"].value == "")
			{
			alert("El campo país es  obligatorio");
			return;
			}
			if (document.forms["reconocimientos_existente"].elements["STD_ID_GEO_DIV_1"].value == "")
			{
			alert("El campo estado es  obligatorio");
			return;
			}
			var reconocimiento_nulo = comprobar_espacios("reconocimientos_existente","CMX_N_RECONOCIMIENTO*1")
			if (reconocimiento_nulo == 0)
			{
			alert(msg_63);
			document.forms["reconocimientos_existente"].elements["CMX_N_RECONOCIMIENTO*1"].focus();
			return;
			}
			if(!validaNumEntero(document.forms["reconocimientos_existente"].elements["CMX_N_MES*1"].value))
			 { alert("El campo mes debe ser númerico");
				return;
			 }
			 
			if(!validaNumEntero(document.forms["reconocimientos_existente"].elements["CMX_N_ANO*1"].value))
			 { alert("El campo año debe ser númerico");
				return;
			 }
		}//cierre if accion = 1

	comprobar_reconocimientos(accion_bbdd);

}//cierre funcion

//***************************
//Funciones de la pagina de logros js_logros.jsp
//FECHA: 27/03/2009
//***************************
	function comprobar_logros(accion_a_tomar)
	{
		var action = accion_a_tomar;
		var informacion = "";
	 
   	    if (informacion != "")
   	    { informacion += "{";}  

		var n_nodo = "_NODO*" + "0";
		var n_registro = "_REGISTRO*" + "0";

		if (action == 2)
		{
		informacion = "_NODO=CMX_X_LOGROS{_ACCION=DELETE{_REGISTRO=";
		informacion += document.forms["logros_existente"].elements["_REGISTRO*1"].value;
	    document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		forma_parametro.submit();
		return;
		}

		var id_country = "STD_ID_COUNTRY*" +"0";
		var n_country = "STD_N_COUNTRY*" +"0";
		var id_estado = "STD_ID_GEO_DIV*" +"0";
		var n_estado = "STD_N_GEO_DIV*" +"0";
		var logro = "CMX_N_LOGRO*" +"0";
		var docto = "CMX_DOCTO_SOPORTE*" +"0";
		var ano = "CMX_N_ANOINGRESO*" +"0";

		var v_nodo = document.forms["logros"].elements["_NODO*0"].value;
		var formulario = "";

		if (action == 1)
		{
			var v_registro = document.forms["logros_existente"].elements["_REGISTRO*1"].value;
			var v_accion_canal = "UPDATE";
			formulario = "logros_existente";

//			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY*1"].value;
//			var v_n_country = document.forms[formulario].elements["STD_ID_COUNTRY*1"].options[document.forms[formulario].elements["STD_ID_COUNTRY*1"].selectedIndex].text;
			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY_1"].value;
			var v_n_country = document.forms[formulario].elements["STD_N_COUNTRY_1"].value;

//			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV*1"].value;
//			var v_n_estado = document.forms[formulario].elements["STD_ID_GEO_DIV*1"].options[document.forms[formulario].elements["STD_ID_GEO_DIV*1"].selectedIndex].text;
			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV_1"].value;
			var v_n_estado = document.forms[formulario].elements["STD_N_GEO_DIV_1"].value;

			var v_logro = document.forms[formulario].elements["CMX_N_LOGRO*1"].value;
			var v_docto = document.forms[formulario].elements["CMX_DOCTO_SOPORTE*1"].value;
			var v_ano = document.forms[formulario].elements["CMX_N_ANOINGRESO*1"].value;

		}

		if (action == 3)
		{
			var v_registro =0
			var v_accion_canal = "INSERT";
			formulario = "logros";

			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY"].value;
			var v_n_country = document.forms[formulario].elements["STD_N_COUNTRY"].value;
			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV"].value;
			var v_n_estado = document.forms[formulario].elements["STD_N_GEO_DIV"].value;
			var v_logro = document.forms[formulario].elements["CMX_N_LOGRO"].value;
			var v_docto = document.forms[formulario].elements["CMX_DOCTO_SOPORTE"].value;
			var v_ano = document.forms[formulario].elements["CMX_N_ANOINGRESO"].value;
	
		}

		 informacion += n_nodo + "=" + v_nodo + "{_ACCION*0="+ v_accion_canal + "{";
		 informacion += n_registro + "=" + v_registro + "{";

		 informacion += id_country + "=" + v_id_country +  "{"; 
		 informacion += n_country + "=" + v_n_country +  "{"; 

		 informacion += id_estado + "=" + v_id_estado +  "{"; 
		 informacion += n_estado + "=" + v_n_estado +  "{"; 

		 informacion += logro + "=" + v_logro + "{"; 
		 informacion += docto + "=" + v_docto + "{"; 
		 informacion += ano + "=" + v_ano + "{"; 

		 document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		 //alert(informacion);
		 forma_parametro.submit();
   	}

	function validar_datos_logros(accion){

		var accion_bbdd = accion;
		var numeros = "0123456789";

		if (accion == 3)
		{					
			if (document.forms["logros"].elements["STD_ID_COUNTRY"].value == "")
			{
			alert("El campo país es  obligatorio");
			return;
			}
			if (document.forms["logros"].elements["STD_ID_GEO_DIV"].value == "")
			{
			alert("El campo estado es  obligatorio");
			return;
			}
			var logro_nulo = comprobar_espacios("logros","CMX_N_LOGRO")
			if (logro_nulo == 0)
			{
			alert(msg_98_2);
			document.forms["logros"].elements["CMX_N_LOGRO"].focus();
			return;
			}
			 
			if(!validaNumEntero(document.forms["logros"].elements["CMX_N_ANOINGRESO"].value))
			 { alert("El campo año debe ser númerico");
				return;
			 }
			
		}//cierre if accion =3 

		if (accion == 1)
		{
           if (document.forms["logros_existente"].elements["STD_ID_COUNTRY_1"].value == "")
			{
			alert("El campo país es  obligatorio");
			return;
			}
			if (document.forms["logros_existente"].elements["STD_ID_GEO_DIV_1"].value == "")
			{
			alert("El campo estado es  obligatorio");
			return;
			}
			var logro_nulo = comprobar_espacios("logros_existente","CMX_N_LOGRO*1")
			if (logro_nulo == 0)
			{
			alert(msg_63);
			document.forms["logros_existente"].elements["CMX_N_LOGRO*1"].focus();
			return;
			}
			 
			if(!validaNumEntero(document.forms["logros_existente"].elements["CMX_N_ANOINGRESO*1"].value))
			 { alert("El campo año debe ser númerico");
				return;
			 }
		}//cierre if accion = 1

	comprobar_logros(accion_bbdd);

}//cierre funcion

//***************************
//Funciones de la pagina de distinciones js_distinciones.jsp
//FECHA: 27/03/2009
//***************************
	function comprobar_distinciones(accion_a_tomar)
	{
		var action = accion_a_tomar;
		var informacion = "";
	 
   	    if (informacion != "")
   	    { informacion += "{";}  

		var n_nodo = "_NODO*" + "0";
		var n_registro = "_REGISTRO*" + "0";

		if (action == 2)
		{
		informacion = "_NODO=CMX_X_DISTINCION{_ACCION=DELETE{_REGISTRO=";
		informacion += document.forms["distinciones_existente"].elements["_REGISTRO*1"].value;
	    document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		forma_parametro.submit();
		return;
		}

		var id_country = "STD_ID_COUNTRY*" +"0";
		var n_country = "STD_N_COUNTRY*" +"0";
		var id_estado = "STD_ID_GEO_DIV*" +"0";
		var n_estado = "STD_N_GEO_DIV*" +"0";
		var distincion = "CMX_N_DISTINCION*" +"0";
		var docto = "CMX_DOCTO_SOPORTE*" +"0";
		var ano = "CMX_N_ANOINGRESO*" +"0";

		var v_nodo = document.forms["distinciones"].elements["_NODO*0"].value;
		var formulario = "";

		if (action == 1)
		{
			var v_registro = document.forms["distinciones_existente"].elements["_REGISTRO*1"].value;
			var v_accion_canal = "UPDATE";
			formulario = "distinciones_existente";

//			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY*1"].value;
//			var v_n_country = document.forms[formulario].elements["STD_ID_COUNTRY*1"].options[document.forms[formulario].elements["STD_ID_COUNTRY*1"].selectedIndex].text;
			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY_1"].value;
			var v_n_country = document.forms[formulario].elements["STD_N_COUNTRY_1"].value;

//			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV*1"].value;
//			var v_n_estado = document.forms[formulario].elements["STD_ID_GEO_DIV*1"].options[document.forms[formulario].elements["STD_ID_GEO_DIV*1"].selectedIndex].text;
			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV_1"].value;
			var v_n_estado = document.forms[formulario].elements["STD_N_GEO_DIV_1"].value;

			var v_distincion = document.forms[formulario].elements["CMX_N_DISTINCION*1"].value;
			var v_docto = document.forms[formulario].elements["CMX_DOCTO_SOPORTE*1"].value;
			var v_ano = document.forms[formulario].elements["CMX_N_ANOINGRESO*1"].value;

		}

		if (action == 3)
		{
			var v_registro =0
			var v_accion_canal = "INSERT";
			formulario = "distinciones";

			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY"].value;
			var v_n_country = document.forms[formulario].elements["STD_N_COUNTRY"].value;
			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV"].value;
			var v_n_estado = document.forms[formulario].elements["STD_N_GEO_DIV"].value;
			var v_distincion = document.forms[formulario].elements["CMX_N_DISTINCION"].value;
			var v_docto = document.forms[formulario].elements["CMX_DOCTO_SOPORTE"].value;
			var v_ano = document.forms[formulario].elements["CMX_N_ANOINGRESO"].value;
	
		}

		 informacion += n_nodo + "=" + v_nodo + "{_ACCION*0="+ v_accion_canal + "{";
		 informacion += n_registro + "=" + v_registro + "{";

		 informacion += id_country + "=" + v_id_country +  "{"; 
		 informacion += n_country + "=" + v_n_country +  "{"; 

		 informacion += id_estado + "=" + v_id_estado +  "{"; 
		 informacion += n_estado + "=" + v_n_estado +  "{"; 

		 informacion += distincion + "=" + v_distincion + "{"; 
		 informacion += docto + "=" + v_docto + "{"; 
		 informacion += ano + "=" + v_ano + "{"; 

		 document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		 //alert(informacion);
		 forma_parametro.submit();
   	}

	function validar_datos_distinciones(accion){

		var accion_bbdd = accion;
		var numeros = "0123456789";

		if (accion == 3)
		{					
			if (document.forms["distinciones"].elements["STD_ID_COUNTRY"].value == "")
			{
			alert("El campo país es  obligatorio");
			return;
			}
			if (document.forms["distinciones"].elements["STD_ID_GEO_DIV"].value == "")
			{
			alert("El campo estado es  obligatorio");
			return;
			}
			var distincion_nulo = comprobar_espacios("distinciones","CMX_N_DISTINCION")
			if (distincion_nulo == 0)
			{
			alert(msg_98_3);
			document.forms["distinciones"].elements["CMX_N_DISTINCION"].focus();
			return;
			}
			 
			if(!validaNumEntero(document.forms["distinciones"].elements["CMX_N_ANOINGRESO"].value))
			 { alert("El campo año debe ser númerico");
				return;
			 }
			
		}//cierre if accion =3 

		if (accion == 1)
		{
           if (document.forms["distinciones_existente"].elements["STD_ID_COUNTRY_1"].value == "")
			{
			alert("El campo país es  obligatorio");
			return;
			}
			if (document.forms["distinciones_existente"].elements["STD_ID_GEO_DIV_1"].value == "")
			{
			alert("El campo estado es  obligatorio");
			return;
			}
			var distincion_nulo = comprobar_espacios("distinciones_existente","CMX_N_DISTINCION*1")
			if (distincion_nulo == 0)
			{
			alert(msg_63);
			document.forms["distinciones_existente"].elements["CMX_N_LOGRO*1"].focus();
			return;
			}
			 
			if(!validaNumEntero(document.forms["distinciones_existente"].elements["CMX_N_ANOINGRESO*1"].value))
			 { alert("El campo año debe ser númerico");
				return;
			 }
		}//cierre if accion = 1

	comprobar_distinciones(accion_bbdd);

}//cierre funcion

//***************************
//Funciones de la pagina de actividades js_actividades.jsp
//FECHA: 27/03/2009
//***************************
	function comprobar_actividades(accion_a_tomar)
	{
		var action = accion_a_tomar;
		var informacion = "";
	 
   	    if (informacion != "")
   	    { informacion += "{";}  

		var n_nodo = "_NODO*" + "0";
		var n_registro = "_REGISTRO*" + "0";

		if (action == 2)
		{
		informacion = "_NODO=CMX_X_ACT_DESTAC{_ACCION=DELETE{_REGISTRO=";
		informacion += document.forms["actividades_existente"].elements["_REGISTRO*1"].value;
	    document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		forma_parametro.submit();
		return;
		}

		var id_country = "STD_ID_COUNTRY*" +"0";
		var n_country = "STD_N_COUNTRY*" +"0";
		var id_estado = "STD_ID_GEO_DIV*" +"0";
		var n_estado = "STD_N_GEO_DIV*" +"0";
		var actividad = "CMX_N_ACT_DESTAC*" +"0";
		var docto = "CMX_DOCTO_SOPORTE*" +"0";
		var ano = "CMX_N_ANOINGRESO*" +"0";

		var v_nodo = document.forms["actividades"].elements["_NODO*0"].value;
		var formulario = "";

		if (action == 1)
		{
			var v_registro = document.forms["actividades_existente"].elements["_REGISTRO*1"].value;
			var v_accion_canal = "UPDATE";
			formulario = "actividades_existente";

//			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY*1"].value;
//			var v_n_country = document.forms[formulario].elements["STD_ID_COUNTRY*1"].options[document.forms[formulario].elements["STD_ID_COUNTRY*1"].selectedIndex].text;
			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY_1"].value;
			var v_n_country = document.forms[formulario].elements["STD_N_COUNTRY_1"].value;

//			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV*1"].value;
//			var v_n_estado = document.forms[formulario].elements["STD_ID_GEO_DIV*1"].options[document.forms[formulario].elements["STD_ID_GEO_DIV*1"].selectedIndex].text;
			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV_1"].value;
			var v_n_estado = document.forms[formulario].elements["STD_N_GEO_DIV_1"].value;

			var v_actividad = document.forms[formulario].elements["CMX_N_ACT_DESTAC*1"].value;
			var v_docto = document.forms[formulario].elements["CMX_DOCTO_SOPORTE*1"].value;
			var v_ano = document.forms[formulario].elements["CMX_N_ANOINGRESO*1"].value;

		}

		if (action == 3)
		{
			var v_registro =0
			var v_accion_canal = "INSERT";
			formulario = "actividades";

			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY"].value;
			var v_n_country = document.forms[formulario].elements["STD_N_COUNTRY"].value;
			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV"].value;
			var v_n_estado = document.forms[formulario].elements["STD_N_GEO_DIV"].value;
			var v_actividad = document.forms[formulario].elements["CMX_N_ACT_DESTAC"].value;
			var v_docto = document.forms[formulario].elements["CMX_DOCTO_SOPORTE"].value;
			var v_ano = document.forms[formulario].elements["CMX_N_ANOINGRESO"].value;
	
		}

		 informacion += n_nodo + "=" + v_nodo + "{_ACCION*0="+ v_accion_canal + "{";
		 informacion += n_registro + "=" + v_registro + "{";

		 informacion += id_country + "=" + v_id_country +  "{"; 
		 informacion += n_country + "=" + v_n_country +  "{"; 

		 informacion += id_estado + "=" + v_id_estado +  "{"; 
		 informacion += n_estado + "=" + v_n_estado +  "{"; 

		 informacion += actividad + "=" + v_actividad + "{"; 
		 informacion += docto + "=" + v_docto + "{"; 
		 informacion += ano + "=" + v_ano + "{"; 

		 document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		 //alert(informacion);
		 forma_parametro.submit();
   	}

	function validar_datos_actividades(accion){

		var accion_bbdd = accion;
		var numeros = "0123456789";

		if (accion == 3)
		{					
			if (document.forms["actividades"].elements["STD_ID_COUNTRY"].value == "")
			{
			alert("El campo país es  obligatorio");
			return;
			}
			if (document.forms["actividades"].elements["STD_ID_GEO_DIV"].value == "")
			{
			alert("El campo estado es  obligatorio");
			return;
			}
			var actividad_nulo = comprobar_espacios("actividades","CMX_N_ACT_DESTAC")
			if (actividad_nulo == 0)
			{
			alert(msg_98_2);
			document.forms["actividades"].elements["CMX_N_ACT_DESTAC"].focus();
			return;
			}
			 
			if(!validaNumEntero(document.forms["actividades"].elements["CMX_N_ANOINGRESO"].value))
			 { alert("El campo año debe ser númerico");
				return;
			 }
			
		}//cierre if accion =3 

		if (accion == 1)
		{
           if (document.forms["actividades_existente"].elements["STD_ID_COUNTRY_1"].value == "")
			{
			alert("El campo país es  obligatorio");
			return;
			}
			if (document.forms["actividades_existente"].elements["STD_ID_GEO_DIV_1"].value == "")
			{
			alert("El campo estado es  obligatorio");
			return;
			}
			var actividad_nulo = comprobar_espacios("actividades_existente","CMX_N_ACT_DESTAC*1")
			if (actividad_nulo == 0)
			{
			alert(msg_63);
			document.forms["actividades_existente"].elements["CMX_N_ACT_DESTAC*1"].focus();
			return;
			}
			 
			if(!validaNumEntero(document.forms["actividades_existente"].elements["CMX_N_ANOINGRESO*1"].value))
			 { alert("El campo año debe ser númerico");
				return;
			 }
		}//cierre if accion = 1

	comprobar_actividades(accion_bbdd);

}//cierre funcion


//***************************

//Funciones de la pagina de familiares js_familares.jsp

//FECHA: 28/11/2000

//***************************

	function comprobar_familiares(accion_a_tomar)
	{

		var action = accion_a_tomar;
		var informacion = "";

   	    if (informacion != "")
   	    { informacion += "{";}
  
		var n_nodo = "_NODO*" + "0";
		var n_registro = "_REGISTRO*" + "0";		

		if (action == 2)

		{

		informacion = "_NODO=CMX_X_FAMILIARES{_ACCION=DELETE{_REGISTRO*1=";
		informacion += document.forms["fam_existentes"].elements["_REGISTRO*1"].value;
	    document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		forma_parametro.submit();
		return;
		}
		var id_parentesco = "ID_PARENTESCO_ST";
		var n_parentesco = "N_PARENTESCO"  ;
		var n_nombres = "CMX_N_NOMBRES"  ;
		var n_apellido1 = "CMX_N_APELLIDO1" ;
		var n_apellido2 = "CMX_N_APELLIDO2" ;
		var n_funcionario = "CMX_CK_FUNSIONARIO"  ;
		var v_nodo = document.forms["familiares"].elements["_NODO*0"].value;

		var formulario = "";

		if (action == 1)

		{
			var v_registro = document.forms["fam_existentes"].elements["_REGISTRO*1"].value;
			var v_accion_canal = "UPDATE";
			formulario = "fam_existentes";

			var v_id_parentesco = document.forms[formulario].elements["ID_PARENTESCO_ST"].value;
			var v_n_parentesco = document.forms[formulario].elements["ID_PARENTESCO_ST"].options[document.forms[formulario].elements["ID_PARENTESCO_ST"].selectedIndex].text;
			var v_n_nombres = 	document.forms[formulario].elements["CMX_N_NOMBRES"].value;
			var v_n_apellido1 = document.forms[formulario].elements["CMX_N_APELLIDO1"].value;
			var v_n_apellido2 = document.forms[formulario].elements["CMX_N_APELLIDO2"].value;
			var v_funcionario = document.forms[formulario].elements["CMX_CK_FUNSIONARIO_V"].value;

		}

		if (action == 3)

		{

			var v_registro =0
			var v_accion_canal = "INSERT";
			formulario = "familiares";
		
			var v_id_parentesco = document.forms[formulario].elements["ID_PARENTESCO_ST*1"].value;
			var v_n_parentesco = document.forms[formulario].elements["ID_PARENTESCO_ST*1"].options[document.forms[formulario].elements["ID_PARENTESCO_ST*1"].selectedIndex].text;
			var v_n_nombres = 	document.forms[formulario].elements["CMX_N_NOMBRES*1"].value;
			var v_n_apellido1 = document.forms[formulario].elements["CMX_N_APELLIDO1*1"].value;
			var v_n_apellido2 = document.forms[formulario].elements["CMX_N_APELLIDO2*1"].value;
			var v_funcionario = document.forms[formulario].elements["CMX_CK_FUNSIONARIO_V1"].value;

		}

		 informacion += n_nodo + "=" + v_nodo + "{_ACCION*0="+ v_accion_canal + "{";
		 informacion += n_registro + "=" + v_registro + "{";
		 informacion += id_parentesco + "=" + v_id_parentesco +  "{"; 
		 informacion += n_parentesco + "=" + v_n_parentesco +  "{"; 
		 informacion += n_nombres + "=" + v_n_nombres + "{"; 
		 informacion += n_apellido1 + "=" + v_n_apellido1 + "{"; 
		 informacion += n_apellido2 + "=" + v_n_apellido2 + "{"; 
		 informacion += n_funcionario + "=" + v_funcionario + "{";  

		 document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		 forma_parametro.submit();
   	}


	function validar_datos_familiares(accion){


		var accion_bbdd = accion;
		var numeros = "0123456789";

		if (accion == 3)
		{					
			var nombres = comprobar_espacios("familiares","CMX_N_NOMBRES*1")
			if (nombres == 0)
			{
			alert(msg_9);
			document.forms["familiares"].elements["CMX_N_NOMBRES*1"].focus();
			return;
			}
			
			var apellido1 = comprobar_espacios("familiares","CMX_N_APELLIDO1*1")
			if (apellido1 == 0)
			{
			alert(msg_10);
			document.forms["familiares"].elements["CMX_N_APELLIDO1*1"].focus();
			return;
			}
		} // cierre if accion 3	
		if (accion == 1)
		{

			var nombres = comprobar_espacios("fam_existentes","CMX_N_NOMBRES")
			if (nombres == 0)
			{
			alert(msg_9);
			document.forms["fam_existentes"].elements["CMX_N_NOMBRES"].focus();
			return;
			}		

			var apellido1 = comprobar_espacios("fam_existentes","CMX_N_APELLIDO1")
			if (apellido1 == 0)

			{
			alert(msg_10);
			document.forms["fam_existentes"].elements["CMX_N_APELLIDO1"].focus();
			return;
			}
		}//cierre if accion = 1
	comprobar_familiares(accion_bbdd);
}//cierre funcion

	function comprobar_ref_personales(accion_a_tomar)
	{
		var action = accion_a_tomar;
		var informacion = "";

   	    if (informacion != "")
   	    { informacion += "{";}
  
		var n_nodo = "_NODO*" + "0";
		var n_registro = "_REGISTRO*" + "0";		

		if (action == 2)
		{
		informacion = "_NODO=CMX_X_REFPERSON{_ACCION=DELETE{_REGISTRO=";
		informacion += document.forms["ref_per_existentes"].elements["_REGISTRO*1"].value;
	    document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		forma_parametro.submit();

		return;
		}
		var n_nombres = "CMX_N_NOMBRESREF"  ;
		var n_apellido1 = "CMX_N_APELLIDO1REF" ;
		var n_apellido2 = "CMX_N_APELLIDO2REF" ;
		var n_telefono = "CMX_N_TELEFONOREF" ;
		var n_tiempo = "CMX_N_TIEMPOREF" ;
		var v_nodo = document.forms["referencias_per"].elements["_NODO*0"].value;

		var formulario = "";

		if (action == 1)

		{
			var v_registro = 0;
			var v_registro = document.forms["ref_per_existentes"].elements["_REGISTRO*1"].value;
			var v_accion_canal = "UPDATE";
			formulario = "ref_per_existentes";

			var v_n_nombres = 	document.forms[formulario].elements["CMX_N_NOMBRESREF"].value;
			var v_n_apellido1 = document.forms[formulario].elements["CMX_N_APELLIDO1REF"].value;
			var v_n_apellido2 = document.forms[formulario].elements["CMX_N_APELLIDO2REF"].value;
			var v_n_telefono = document.forms[formulario].elements["CMX_N_TELEFONOREF"].value;
			var v_n_tiempo = document.forms[formulario].elements["CMX_N_TIEMPOREF"].value;

		}

		if (action == 3)

		{

			var v_registro =0
			var v_accion_canal = "INSERT";
			formulario = "referencias_per";
		

			var v_n_nombres = document.forms[formulario].elements["CMX_N_NOMBRESREF*1"].value;
			var v_n_apellido1 = document.forms[formulario].elements["CMX_N_APELLIDO1REF*1"].value;
			var v_n_apellido2 = document.forms[formulario].elements["CMX_N_APELLIDO2REF*1"].value;
			var v_n_telefono = document.forms[formulario].elements["CMX_N_TELEFONOREF*1"].value;
			var v_n_tiempo = document.forms[formulario].elements["CMX_N_TIEMPOREF*1"].value;
		}

		 informacion += n_nodo + "=" + v_nodo + "{_ACCION*0="+ v_accion_canal + "{";
		 informacion += n_registro + "=" + v_registro + "{";
		 informacion += n_nombres + "=" + v_n_nombres + "{"; 
		 informacion += n_apellido1 + "=" + v_n_apellido1 + "{"; 
		 informacion += n_apellido2 + "=" + v_n_apellido2 + "{"; 
		 informacion += n_telefono + "=" + v_n_telefono + "{"; 
		 informacion += n_tiempo + "=" + v_n_tiempo + "{"; 
		 
		 document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		 forma_parametro.submit();
   	}

	function validar_datos_ref_personales(accion){


		var accion_bbdd = accion;
		var numeros = "0123456789";

		if (accion == 3)
		{					
			var nombres = comprobar_espacios("referencias_per","CMX_N_NOMBRESREF*1")
			if (nombres == 0)
			{
			alert(msg_9);
			document.forms["referencias_per"].elements["CMX_N_NOMBRESREF*1"].focus();
			return;
			}
			if(document.forms["referencias_per"].elements["CMX_N_TIEMPOREF*1"].value!="")
			{
				 if(!validaNumEntero(document.forms["referencias_per"].elements["CMX_N_TIEMPOREF*1"].value))
				 {	
					 alert("El campo tiempo de conocerlo  debe ser númerico");
						return; }
				}
		if(document.forms["referencias_per"].elements["CMX_N_TELEFONOREF*1"].value!="")
		{
		 if(!validaNumEntero(document.forms["referencias_per"].elements["CMX_N_TELEFONOREF*1"].value))
		 {
		 alert("El campo teléfono  debe ser númerico");
				return; }
		}
			var apellido1 = comprobar_espacios("referencias_per","CMX_N_APELLIDO1REF*1")
			if (apellido1 == 0)
			{
			alert(msg_10);
			document.forms["referencias_per"].elements["CMX_N_APELLIDO1REF*1"].focus();
			return;
			}
		} // cierre if accion 3	
			if (accion == 1)
		{
			var nombres = comprobar_espacios("ref_per_existentes","CMX_N_NOMBRESREF")
			if (nombres == 0)
			{
			alert(msg_9);
			document.forms["ref_per_existentes"].elements["CMX_N_NOMBRESREF"].focus();
			return;
			}	
			if(document.forms["ref_per_existentes"].elements["CMX_N_TELEFONOREF"].value!="")
			{
				 if(!validaNumEntero(document.forms["ref_per_existentes"].elements["CMX_N_TELEFONOREF"].value))
				 {	
					 alert("El campo teléfono  debe ser númerico");
						return; }
				}
			if(document.forms["ref_per_existentes"].elements["CMX_N_TELEFONOREF"].value!="")
				{
				if(!validaNumEntero(document.forms["ref_per_existentes"].elements["CMX_N_TELEFONOREF"].value))
				 		{	
					 		alert("El campo tiempo de conocerlo  debe ser númerico");
								return; }
				}	
			if(document.forms["ref_per_existentes"].elements["CMX_N_TIEMPOREF"].value!="")
			{
				 if(!validaNumEntero(document.forms["ref_per_existentes"].elements["CMX_N_TIEMPOREF"].value))
				 {	
					 alert("El campo tiempo de conocerlo  debe ser númerico");
						return; }
				}
			var apellido1 = comprobar_espacios("ref_per_existentes","CMX_N_APELLIDO1REF")
			if (apellido1 == 0)
			{
			alert(msg_10);
			document.forms["ref_per_existentes"].elements["CMX_N_APELLIDO1REF"].focus();
			return;
			}
		}//cierre if accion = 1
	comprobar_ref_personales(accion_bbdd);
}//cierre funcion


//***************************
//Funciones de la pagina de js_idiomas.jsp
//FECHA: 09/01/2000
//***************************
function insertar(cadena)
	{
	var informacion = cadena;
	var formulario="idiomas";
		if (informacion != "")
			{informacion += "{";}
		var n_registro_new = "_REGISTRO";
		var id_language_new = "STD_ID_LANGUAGE";
		var n_language_new = "STD_N_LANGUAGE";
		var id_write_new = "STD_ID_WRITE_LEVEL";
		var n_write_new = "STD_N_WRITE_LEVEL";
		var id_speak_new = "STD_ID_SPEAK_LEVEL";
		var n_speak_new = "STD_N_SPEAK_LEVEL";
		var id_listen_new = "STD_ID_LISTEN_LEVEL";
		var n_listen_new = "STD_N_LISTEN_LEVEL";
		var prefered_new = "STD_PREFERED";
		var vn_registro_new = "";
		var vid_language_new = document.forms[formulario].elements["STD_ID_LANGUAGE"].value;
		var vn_language_new = document.forms[formulario].elements["STD_N_LANGUAGE"].value;
		var vid_write_new = document.forms[formulario].elements["STD_ID_WRITE_LEVEL"].value;
		var vn_write_new = document.forms[formulario].elements["STD_ID_WRITE_LEVEL"].options[document.forms[formulario].elements["STD_ID_WRITE_LEVEL"].selectedIndex].text;
		var vid_speak_new = document.forms[formulario].elements["STD_ID_SPEAK_LEVEL"].value;
		var vn_speak_new = document.forms[formulario].elements["STD_ID_SPEAK_LEVEL"].options[document.forms[formulario].elements["STD_ID_SPEAK_LEVEL"].selectedIndex].text;
		var vid_listen_new = document.forms[formulario].elements["STD_ID_LISTEN_LEVEL"].value;
		var vn_listen_new = document.forms[formulario].elements["STD_ID_LISTEN_LEVEL"].options[document.forms[formulario].elements["STD_ID_LISTEN_LEVEL"].selectedIndex].text;
		var vprefered_new = document.forms[formulario].elements["STD_PREFERED"].value;
		informacion += "_NODO" + "=SCO_CURR_LANGUAGE{_ACCION" + "=" + "INSERT" + "{";
		informacion += n_registro_new + "=" + vn_registro_new +"{" + id_language_new + "=" + vid_language_new + "{";
		informacion += n_language_new +"=" + vn_language_new + "{" + id_write_new + "=" + vid_write_new + "{";
		informacion += n_write_new + "=" + vn_write_new +"{" + id_speak_new + "=" + vid_speak_new + "{";
		informacion += n_speak_new +"=" + vn_speak_new + "{" + id_listen_new + "=" + vid_listen_new + "{";
		informacion += n_listen_new + "=" + vn_listen_new+ "{" + prefered_new + "=" + vprefered_new ;
	if (informacion == "")
  			 { alert(msg_68);}
	 else
		 {
		
	  document.forms["forma_parametro"].elements["parametro"].value = informacion;		
	  
	  forma_parametro.submit();
	 }
	}//cierro insertar



//***************************
//Funciones de la pagina de otros datos js_otros_datos.jsp
//FECHA: 28/11/2000
//***************************

	function selectedradio_otros_datos(argradio){
	if (argradio==0){
	
		var valorseleccionado_0="";
		var long=document.otros_datos_exist.SCO_CK_TRAVEL_DISPO.length;
		var i=0;
		for(i=0;i<long;i++)
		{
			if(document.otros_datos_exist.SCO_CK_TRAVEL_DISPO[i].checked)
			{
			valorseleccionado_0=document.otros_datos_exist.SCO_CK_TRAVEL_DISPO[i].value;
			document.otros_datos_exist.SCO_CK_TRAVEL_DISPO_V.value=valorseleccionado_0;
			return;
			
			}
		}
	}
	if (argradio==1){
		var valorseleccionado_1="";
		var long=document.otros_datos_exist.SCO_CK_MOV_NAC.length;
		var i=0;
		for(i=0;i<long;i++)
		{
			if(document.otros_datos_exist.SCO_CK_MOV_NAC[i].checked)
			{
			valorseleccionado_1=document.otros_datos_exist.SCO_CK_MOV_NAC[i].value;
			document.otros_datos_exist.SCO_CK_MOV_NAC_V.value=valorseleccionado_1;

			return;			
			}
		}
	}
	if (argradio==2){
		var valorseleccionado_2="";
		var long=document.otros_datos_exist.SCO_CK_MOV_INTER.length;
		var i=0;
		for(i=0;i<long;i++)
		{
			if(document.otros_datos_exist.SCO_CK_MOV_INTER[i].checked)
			{
			valorseleccionado_2=document.otros_datos_exist.SCO_CK_MOV_INTER[i].value;
			document.otros_datos_exist.SCO_CK_MOV_INTER_V.value=valorseleccionado_2;
			return;			
			}
		}

	}

}

//***************************
//Funciones de la pagina de otros datos js_familiares.jsp
//FECHA: 22/03/2005
//***************************

	function selectedradio_familiares(argradio){
	if (argradio==1){	

		var valorseleccionado_0="";
		var long=document.fam_existentes.CMX_CK_FUNSIONARIO_R.length;
		var i=0;
		for(i=0;i<long;i++)
		{
			if(document.fam_existentes.CMX_CK_FUNSIONARIO_R[i].checked)
			{
			valorseleccionado_0=document.fam_existentes.CMX_CK_FUNSIONARIO_R[i].value;
			document.fam_existentes.CMX_CK_FUNSIONARIO_V.value=valorseleccionado_0;
			return;
			} // Fin if documento 1
		} // Fin for 1
	} // Fin if argradio 1

	if (argradio==2){

		var valorseleccionado_1="";
		var long = document.familiares.CMX_CK_FUNSIONARIO_A.length;

		var i=0;
		for(i=0;i<long;i++)
		{
			if(document.familiares.CMX_CK_FUNSIONARIO_A[i].checked)
			{
			valorseleccionado_1=document.familiares.CMX_CK_FUNSIONARIO_A[i].value;
			document.familiares.CMX_CK_FUNSIONARIO_V1.value=valorseleccionado_1;
			return;			
			}
		}
	}
} // Fin funcion
	


//***************************
//Funciones de la pagina de procesos de seleccion js_proces.jsp
//FECHA: 28/11/2000
//***************************

	function visible_procesos(indice)
	{
		if ((document.job_enrollment.indice_visible.value != "") && (document.job_enrollment.indice_visible.value != null))
		{
		   invisible_procesos(document.job_enrollment.indice_visible.value);
		}
		document.job_enrollment.indice_visible.value = indice;
		cadena = "CAPA"+indice;
		document.all[cadena].className="tablaestados";


	}
	function invisible_procesos(indice)
	{
		if (indice=="CAPA")
			document.all.CAPA.className="invisible2";
		else
		{
			cadena = "CAPA"+indice;
			document.all[cadena].className="invisible2";
		}
	}

	function borrar_valores_prosesos(indice,valor)
	{
	   var cadena = "SCO_OR_RECRUIT_PR*"+indice;	   
	   var valor_actual = document.all[cadena].value;	  
	   var cadena_acc = "_ACCION*"+indice;

	   if (valor_actual == "no_selected")
	   {
	    document.all[cadena].value = valor;
		document.all[cadena_acc].value = "INSERT";
	
	   }
       else
	   {
		document.all[cadena].value = "no_selected";
		document.all[cadena_acc].value = "no_aplica";
	   }

	}


	function comprobar_filtro(formulario)
	{
	    if (formulario == "filtro")
		{
			document.filtro.nombre_valor.value = 	document.filtro.valor.options[document.filtro.valor.selectedIndex].text;
			filtro.submit();
		}

	    if (formulario == "filtro_1")
		{
			document.filtro_1.nombre_valor.value = document.filtro_1.valor.options[document.filtro_1.valor.selectedIndex].text;
			filtro_1.submit();
		}
		
		if (formulario == "area")
		{
			document.area.nombre_valor.value = document.area.valor.options[document.area.valor.selectedIndex].text;
			area.submit();
		}

	}


	function deshacer_filtro(formulario)
	{
		filtro_deshacer.submit();		
	}

	function cambia_formulario(destino)
	{
	    if (destino == "STD_JOB_ACAD_BACKGROUND")
		{
		   document.filtro_1.className="";
		   document.filtro_1.nodo.value = "STD_JOB_ACAD_BACKGROUND"
		   document.filtro.className="invisible2";
		   document.area.className="invisible2";
		}

	    if (destino == "SCO_JOB_PREV_JOBS")
		{
		   document.filtro.className="";
		   document.filtro.nodo.value = "SCO_JOB_PREV_JOBS"
		   document.filtro_1.className="invisible2";
		   document.area.className="invisible2";
		
		}
	
	    if (destino == "SCO_RECRUIT_PRO")
		{
		   document.area.className="";
		   document.area.nodo.value = "SCO_RECRUIT_PRO"
   		   document.filtro.className="invisible2";
		   document.filtro_1.className="invisible2";
		}
	}

	function comprobar_procesos()
	{

	 var num_reg = document.job_enrollment.NUM_MAX_REG.value
	 var num_reg_ent = 0;
	if (num_reg > 0)
	{
		 var resto = num_reg%10;
		 if (resto != 0)
			num_reg_ent = num_reg - resto
		 else
			num_reg_ent = num_reg - 10
	}

	 var informacion = "";

	 for (contador=num_reg_ent; contador<=num_reg; contador++)
	 {
   	    var tipo_accion = "_ACCION";
		tipo_accion = tipo_accion + "*" + contador;

		accion = document.job_enrollment[tipo_accion].value

		if (accion != "no_aplica")

		{
			if (informacion != "")
	  	       informacion += "{";

			 var n_nodo = "_NODO*" + contador;
			 var n_registro = "_REGISTRO*" + contador;
			 var or_recruit = "SCO_OR_RECRUIT_PR*" + contador;
			 var reg_number = "SCO_REF_NUMBER_REC*" + contador;
			 var id_source = "SCO_ID_SOURCE*" + contador;
			 var id_status = "SCO_ID_STATUS_REF*" + contador;
			 var nm_status = "SCO_NM_STATUS_REF*" + contador;
			 var organization = "ID_ORGANIZATION*" + contador;

			 var v_nodo = document.job_enrollment[n_nodo].value; 
			 var v_registro = document.job_enrollment[n_registro].value;
			 var v_or_recruit = document.job_enrollment[or_recruit].value;
			 var v_reg_number = document.job_enrollment[reg_number].value;
			 var v_id_source = document.job_enrollment[id_source].value;
			 var v_id_status = document.job_enrollment[id_status].value;
			 var v_nm_status =document.job_enrollment[nm_status].value;
			 var v_organization = document.job_enrollment[organization].value;

			 informacion += n_nodo + "=" + v_nodo + "{" + tipo_accion + "=" + accion + "{";
			 informacion += n_registro + "=" + v_registro + "{";
			 informacion += or_recruit + "=" + v_or_recruit + "{" + reg_number + "=" + v_reg_number + 	"{"; 
			 informacion += id_source + "=" + v_id_source + "{" + id_status + "=" + v_id_status + "{"; 
			 informacion += organization + "=" + v_organization +"{" + nm_status + "=" + v_nm_status ; 
		}

	}

       if (informacion == "")
  		  alert(msg_69);
	   else
	   {
	  	   document.forma_parametro.parametro.value = informacion;
		   forma_parametro.submit();
	   }
	}




//***************************
//Funciones de la pagina de experiencia profesiona js_experiencia_profesional.jsp
//FECHA: 28/11/2000
//***************************


	function comprobar_experiencia(accion_a_tomar)
	{
		var action = accion_a_tomar;
		var informacion = "";
	 
   	    if (informacion != "")
   	    { informacion += "{";}
  
		var n_nodo = "_NODO*" + "0";
		var n_registro = "_REGISTRO*" + "0";
		
		if (action == 2)
		{
		informacion = "_NODO=SCO_CURR_PREV_JOBS{_ACCION=DELETE{_REGISTRO=";
		informacion += document.forms["experiencia_existente"].elements["_REGISTRO*1"].value;
	    document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		forma_parametro.submit();
		return;
		}

		var dt_start = "STD_DT_START*" + "0";
		var dt_end = "STD_DT_END*" +"0";
		var employer = "STD_EMPLOYER*" +"0";
		var id_country = "STD_ID_COUNTRY*" +"0";
		var n_country = "STD_N_COUNTRY*" +"0";
		var id_sector = "STD_ID_SECTOR*" +"0";
		var n_sector = "STD_N_SECTOR*" +"0";
		var initial_job = "STD_INITIAL_JOB*" +"0";

		var id_puesto = "CMX_ID_PUESTO*" + "0";
		var n_puesto = "CMX_N_PUESTO*" + "0";

		var id_estado = "STD_ID_GEO_DIV*" + "0";
		var n_estado = "STD_N_GEO_DIV*" + "0";

		var id_regimen = "CMX_ID_REGIMEN*" + "0";
		var n_regimen = "CMX_N_REGIMEN*" + "0";

		//var empleados_dir = "CMX_N_EMPDIREC*" +"0";
		//var empleados_indir = "CMX_N_EMPINDIREC*" +"0";
		var logros = "CMX_N_LOGROS*" +"0";

		var id_grupo_exp = "CFP_ID_GPO_EXP*" + "0";
		var n_grupo_exp = "CFP_N_GPO_EXPER*" + "0";		
		var id_area_gral_exp = "CFP_ID_AREA_GRAL_EXP*" + "0";
		var n_area_gral_exp = "CFP_N_AREA_GRAL_EXP*" + "0";		
				
    	var id_grupo_exp1 = "CFP_ID_GPO_EXP1*" + "0";
		var n_grupo_exp1 = "CFP_N_GPO_EXPER1*" + "0";
		var id_area_gral_exp1 = "CFP_ID_AREA_GRAL_EXP1*" + "0";
		var n_area_gral_exp1 = "CFP_N_AREA_GRAL_EXP1*" + "0";			
		
		var id_grupo_exp2 = "CFP_ID_GPO_EXP2*" + "0";
		var n_grupo_exp2 = "CFP_N_GPO_EXPER2*" + "0";		
		var id_area_gral_exp2 = "CFP_ID_AREA_GRAL_EXP2*" + "0";
		var n_area_gral_exp2 = "CFP_N_AREA_GRAL_EXP2*" + "0";			
				
		var nombre_jefe = "CMX_N_NOMBRE_JEFE*" + "0";				
		var apellido1_jefe = "CMX_N_APELLIDO1_JEFE*" + "0";				
		var apellido2_jefe = "CMX_N_APELLIDO2_JEFE*" + "0";				
		var telefono_jefe = "CMX_N_TELEFONO_JEFE*" + "0";																

		/*
		var rama_cargo="CFP_ID_TIP_FUNCION1*1" + "0";
		var n_rama_cargo="CFP_N_TIPO_FUNCION*1" + "0";
		
		var rama_cargo2="CFP_ID_TIP_FUNCION2*1" + "0";
		var n_rama_cargo2="CFP_N_TIPO_FUNCION_1*1" + "0";

		var id_remuneracion="SMX_ID_NIVEL_SALARIAL*1" + "0";				
		var nm_remuneracion="SMX_NM_NIVEL_SALARIAL*1" + "0";
		*/
		
//INICIO AGREGADO SALVAR RAMAS Y $

		var z_id_tipo_funcion_1 = "CFP_ID_TIP_FUNCION1*" + "0";
		var z_n_tipo_funcion = "CFP_N_TIPO_FUNCION*" + "0";
		var z_id_tipo_funcion_2 = "CFP_ID_TIP_FUNCION2*" + "0";
		var z_n_tipo_funcion_1 = "CFP_N_TIPO_FUNCION_1*" + "0";
		var z_id_nivel_salarial = "SMX_ID_NIVEL_SALARIAL*" + "0";
		var z_nm_nivel_salarial = "CME_NM_REMUNERA*" + "0";
		var z_id_sector1 = "CMX_ID_SECTOR*" + "0";
		var z_nm_sector1 = "CMX_N_SECTOR*" + "0";
//FIN AGREGADO		
				
				
				
		var v_nodo = document.forms["exp_profesional"].elements["_NODO*0"].value; 
		var formulario = "";

		if (action == 1)
		{
			var v_registro = document.forms["experiencia_existente"].elements["_REGISTRO*1"].value;
			var v_accion_canal = "UPDATE";
			formulario = "experiencia_existente";

			var v_dt_start = document.forms[formulario].elements["STD_DT_START*1"].value;
			var v_dt_end = document.forms[formulario].elements["STD_DT_END*1"].value;
			var v_employer = document.forms[formulario].elements["STD_EMPLOYER*1"].value;
//			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY*1"].value;
//			var v_n_country = document.forms[formulario].elements["STD_ID_COUNTRY*1"].options[document.forms[formulario].elements["STD_ID_COUNTRY*1"].selectedIndex].text;
			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY_1"].value;
			var v_n_country = document.forms[formulario].elements["STD_N_COUNTRY_1"].value;

			var v_id_sector = document.forms[formulario].elements["STD_ID_SECTOR*1"].value;
			var v_n_sector = document.forms[formulario].elements["STD_ID_SECTOR*1"].options[document.forms[formulario].elements["STD_ID_SECTOR*1"].selectedIndex].text;

			var v_initial_job = document.forms[formulario].elements["STD_INITIAL_JOB*1"].value;

			var v_id_puesto = document.forms[formulario].elements["CMX_ID_PUESTO*1"].value;
			var v_n_puesto = document.forms[formulario].elements["CMX_ID_PUESTO*1"].options[document.forms[formulario].elements["CMX_ID_PUESTO*1"].selectedIndex].text;

//			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV*1"].value;
//			var v_n_estado = document.forms[formulario].elements["STD_ID_GEO_DIV*1"].options[document.forms[formulario].elements["STD_ID_GEO_DIV*1"].selectedIndex].text;
			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV_1"].value;
			var v_n_estado = document.forms[formulario].elements["STD_N_GEO_DIV_1"].value;

			var v_id_regimen = document.forms[formulario].elements["CMX_ID_REGIMEN*1"].value;
			var v_n_regimen = document.forms[formulario].elements["CMX_ID_REGIMEN*1"].options[document.forms[formulario].elements["CMX_ID_REGIMEN*1"].selectedIndex].text;

			//var v_empleados_dir =document.forms[formulario].elements["CMX_N_EMPDIREC*1"].value;
			//var v_empleados_indir =document.forms[formulario].elements["CMX_N_EMPINDIREC*1"].value;
			var v_logros =document.forms[formulario].elements["CMX_N_LOGROS*1"].value;

			var v_id_grupo_exp = document.forms[formulario].elements["CFP_ID_GPO_EXP*1"].value;
			var v_n_grupo_exp = document.forms[formulario].elements["CFP_N_GPO_EXP*1"].value;
			var v_id_area_gral_exp = document.forms[formulario].elements["CFP_ID_AREA_GRAL_EXP*1"].value;
			var v_n_area_gral_exp = document.forms[formulario].elements["CFP_N_AREA_GRAL_EXP*1"].value;						
			
			var v_id_grupo_exp1 = document.forms[formulario].elements["CFP_ID_GPO_EXP1*1"].value;
			var v_n_grupo_exp1 = document.forms[formulario].elements["CFP_N_GPO_EXP1*1"].value;
			var v_id_area_gral_exp1 = document.forms[formulario].elements["CFP_ID_AREA_GRAL_EXP1*1"].value;
			var v_n_area_gral_exp1 = document.forms[formulario].elements["CFP_N_AREA_GRAL_EXP1*1"].value;
			
			var v_id_grupo_exp2 = document.forms[formulario].elements["CFP_ID_GPO_EXP2*1"].value;
			var v_n_grupo_exp2 = document.forms[formulario].elements["CFP_N_GPO_EXP2*1"].value;
			var v_id_area_gral_exp2 = document.forms[formulario].elements["CFP_ID_AREA_GRAL_EXP2*1"].value;
			var v_n_area_gral_exp2 = document.forms[formulario].elements["CFP_N_AREA_GRAL_EXP2*1"].value;
												
			var v_nombre_jefe = document.forms[formulario].elements["CMX_N_NOMBRE_JEFE*1"].value;			
			var v_apellido1_jefe = document.forms[formulario].elements["CMX_N_APELLIDO1_JEFE*1"].value;						
			var v_apellido2_jefe = document.forms[formulario].elements["CMX_N_APELLIDO2_JEFE*1"].value;
			var v_telefono_jefe = document.forms[formulario].elements["CMX_N_TELEFONO_JEFE*1"].value;
//INICIO AGREGADO SALVAR RAMAS Y $

		var v_id_tipo_funcion_1 = document.forms[formulario].elements["CFP_ID_TIP_FUNCION1*1"].value;//= "" + "0";	
		var v_n_tipo_funcion = document.forms[formulario].elements["CFP_N_TIPO_FUNCION*1"].value;//= "" + "0";
		var v_id_tipo_funcion_2 = document.forms[formulario].elements["CFP_ID_TIP_FUNCION2*1"].value;//= "" + "0";
		var v_n_tipo_funcion_1 = document.forms[formulario].elements["CFP_N_TIPO_FUNCION_1*1"].value;//= "" + "0";
		var v_id_nivel_salarial = document.forms[formulario].elements["SMX_ID_NIVEL_SALARIAL*1"].value;//= "" + "0";
		var v_nm_nivel_salarial = document.forms[formulario].elements["CME_NM_REMUNERA*1"].value;//= "" + "0";
		var v_id_sector1 = document.forms[formulario].elements["CMX_ID_SECTOR*1"].value;//= "" + "0";
		var v_nm_sector1 = document.forms[formulario].elements["CMX_N_SECTOR*1"].value;//= "" + "0"; 

//FIN AGREGADO				
									
		}

		if (action == 3)
		{
			var v_registro =0
			var v_accion_canal = "INSERT";
			formulario = "exp_profesional";
		
			var v_dt_start = document.forms[formulario].elements["STD_DT_START"].value;
			var v_dt_end = document.forms[formulario].elements["STD_DT_END"].value;
			var v_employer = document.forms[formulario].elements["STD_EMPLOYER"].value;
			var v_id_country = document.forms[formulario].elements["STD_ID_COUNTRY"].value;
//			var v_n_country = document.forms[formulario].STD_ID_COUNTRY.options[document.forms[formulario].STD_ID_COUNTRY.selectedIndex].text;
			var v_n_country = document.forms[formulario].STD_N_COUNTRY.value;

			var v_id_sector = document.forms[formulario].elements["STD_ID_SECTOR"].value;
			var v_n_sector = document.forms[formulario].STD_ID_SECTOR.options[document.forms[formulario].STD_ID_SECTOR.selectedIndex].text;

			var v_initial_job = document.forms[formulario].elements["STD_INITIAL_JOB"].value;

			var v_id_puesto = document.forms[formulario].elements["CMX_ID_PUESTO"].value;
			var v_n_puesto = document.forms[formulario].CMX_ID_PUESTO.options[document.forms[formulario].CMX_ID_PUESTO.selectedIndex].text;

			var v_id_estado = document.forms[formulario].elements["STD_ID_GEO_DIV"].value;
//			var v_n_estado = document.forms[formulario].STD_ID_GEO_DIV.options[document.forms[formulario].STD_ID_GEO_DIV.selectedIndex].text;
			var v_n_estado = document.forms[formulario].STD_N_GEO_DIV.value;

			var v_id_regimen = document.forms[formulario].elements["CMX_ID_REGIMEN"].value;
			var v_n_regimen = document.forms[formulario].CMX_ID_REGIMEN.options[document.forms[formulario].CMX_ID_REGIMEN.selectedIndex].text;

			//var v_empleados_dir =document.forms[formulario].elements["CMX_N_EMPDIREC"].value;
			//var v_empleados_indir =document.forms[formulario].elements["CMX_N_EMPINDIREC"].value;
			var v_logros =document.forms[formulario].elements["CMX_N_LOGROS"].value;

			var v_id_grupo_exp = document.forms[formulario].elements["CFP_ID_GPO_EXP"].value;
			var v_n_grupo_exp = document.forms[formulario].elements["CFP_N_GPO_EXP"].value;
			var v_id_area_gral_exp = document.forms[formulario].elements["CFP_ID_AREA_GRAL_EXP"].value;
			var v_n_area_gral_exp = document.forms[formulario].elements["CFP_N_AREA_GRAL_EXP"].value;						
			
			var v_id_grupo_exp1 = document.forms[formulario].elements["CFP_ID_GPO_EXP1"].value;
			var v_n_grupo_exp1 = document.forms[formulario].elements["CFP_N_GPO_EXP1"].value;
			var v_id_area_gral_exp1 = document.forms[formulario].elements["CFP_ID_AREA_GRAL_EXP1"].value;
			var v_n_area_gral_exp1 = document.forms[formulario].elements["CFP_N_AREA_GRAL_EXP1"].value;
			
			var v_id_grupo_exp2 = document.forms[formulario].elements["CFP_ID_GPO_EXP2"].value;
			var v_n_grupo_exp2 = document.forms[formulario].elements["CFP_N_GPO_EXP2"].value;
			var v_id_area_gral_exp2 = document.forms[formulario].elements["CFP_ID_AREA_GRAL_EXP2"].value;
			var v_n_area_gral_exp2 = document.forms[formulario].elements["CFP_N_AREA_GRAL_EXP2"].value;
			
			var v_nombre_jefe =document.forms[formulario].elements["CMX_N_NOMBRE_JEFE"].value;			
			var v_apellido1_jefe =document.forms[formulario].elements["CMX_N_APELLIDO1_JEFE"].value;			
			var v_apellido2_jefe =document.forms[formulario].elements["CMX_N_APELLIDO2_JEFE"].value;			
			var v_telefono_jefe =document.forms[formulario].elements["CMX_N_TELEFONO_JEFE"].value;		
			
//INICIO parece que esto tiene error
/*
		var v_id_tipo_funcion_1 = document.forms[formulario].elements["CFP_ID_TIP_FUNCION1*1"].value;//= "" + "0";
		var v_n_tipo_funcion = document.forms[formulario].elements["CFP_N_TIPO_FUNCION*1"].value;//= "" + "0";
		var v_id_tipo_funcion_2 = document.forms[formulario].elements["CFP_ID_TIP_FUNCION2*1"].value;//= "" + "0";
		var v_n_tipo_funcion_1 = document.forms[formulario].elements["CFP_N_TIPO_FUNCION_1*1"].value;//= "" + "0";
		var v_id_nivel_salarial = document.forms[formulario].elements["SMX_ID_NIVEL_SALARIAL*1"].value;//= "" + "0";
		var v_nm_nivel_salarial = document.forms[formulario].elements["SMX_NM_NIVEL_SALARIAL*1"].value;//= "" + "0";
*/
//FIN parece que esto tiene error		

//INICIO parece que esto CORRIGE error

		var v_id_tipo_funcion_1 = document.forms[formulario].elements["CFP_ID_TIP_FUNCION1"].value;//= "" + "0";

		var v_n_tipo_funcion = document.forms[formulario].elements["CFP_N_TIPO_FUNCION"].value;//= "" + "0";
		var v_id_tipo_funcion_2 = document.forms[formulario].elements["CFP_ID_TIP_FUNCION2"].value;//= "" + "0";
		var v_n_tipo_funcion_1 = document.forms[formulario].elements["CFP_N_TIPO_FUNCION_1"].value;//= "" + "0";
		var v_id_nivel_salarial = document.forms[formulario].elements["SMX_ID_NIVEL_SALARIAL"].value;//= "" + "0";
		var v_nm_nivel_salarial = document.forms[formulario].elements["CME_NM_REMUNERA"].value;//= "" + "0";
		var v_id_sector1 = document.forms[formulario].elements["CMX_ID_SECTOR"].value;//= "" + "0";
		var v_nm_sector1 = document.forms[formulario].elements["CMX_N_SECTOR"].value;//= "" + "0"; 

//FIN parece que esto CORRIGE error	
						
												
		}

		 informacion += n_nodo + "=" + v_nodo + "{_ACCION*0="+ v_accion_canal + "{";
		 informacion += n_registro + "=" + v_registro + "{";
		 informacion += dt_start + "=" + v_dt_start + "{" ; 
		 informacion += employer + "=" + v_employer + "{" + id_country + "=" + v_id_country + "{"; 
		 informacion += n_country + "=" + v_n_country + "{"; 
		 informacion += id_sector + "=" + v_id_sector + "{" + initial_job + "=" + v_initial_job + "{" ; 
		 informacion += n_sector + "=" + v_n_sector + "{"; 
	   
		 informacion += dt_end + "=" + v_dt_end + "{";

		 if (v_dt_end=="")
		 {
		    informacion += dt_end + "=4000-01-01" + "{";		 
		 }
		 else
		 {	
		    informacion += dt_end + "=" + v_dt_end + "{";		 		 
		 }

		 informacion += id_puesto + "=" + v_id_puesto +  "{"; 
		 informacion += n_puesto + "=" + v_n_puesto +  "{"; 
		 informacion += id_estado + "=" + v_id_estado +  "{"; 
		 informacion += n_estado + "=" + v_n_estado +  "{"; 
		 informacion += id_regimen + "=" + v_id_regimen +  "{"; 
		 informacion += n_regimen + "=" + v_n_regimen +  "{"; 

	     //informacion += empleados_dir + "=" + v_empleados_dir + "{";
	     //informacion += empleados_indir + "=" + v_empleados_indir + "{";				 

	     informacion += logros + "=" + v_logros + "{";
		 
		 informacion += id_grupo_exp + "=" + v_id_grupo_exp +  "{"; 
		 informacion += n_grupo_exp + "=" + v_n_grupo_exp +  "{"; 		 				 
		 informacion += id_area_gral_exp + "=" + v_id_area_gral_exp +  "{"; 
		 informacion += n_area_gral_exp + "=" + v_n_area_gral_exp +  "{";
		 
		 informacion += id_grupo_exp1 + "=" + v_id_grupo_exp1 +  "{"; 
		 informacion += n_grupo_exp1 + "=" + v_n_grupo_exp1 +  "{"; 		 				 
		 informacion += id_area_gral_exp1 + "=" + v_id_area_gral_exp1 +  "{"; 
		 informacion += n_area_gral_exp1 + "=" + v_n_area_gral_exp1 +  "{";		 
		 
		 informacion += id_grupo_exp2 + "=" + v_id_grupo_exp2 +  "{"; 
		 informacion += n_grupo_exp2 + "=" + v_n_grupo_exp2 +  "{"; 		 				 
		 informacion += id_area_gral_exp2 + "=" + v_id_area_gral_exp2 +  "{"; 
		 informacion += n_area_gral_exp2 + "=" + v_n_area_gral_exp2 +  "{";		 
		 
		 //SOS
		 informacion += z_id_tipo_funcion_1 + "=" + v_id_tipo_funcion_1 +  "{";		 
		 informacion += z_n_tipo_funcion + "=" + v_n_tipo_funcion +  "{";		 
		 informacion += z_id_tipo_funcion_2 + "=" + v_id_tipo_funcion_2 +  "{";		 
		 informacion += z_n_tipo_funcion_1 + "=" + v_n_tipo_funcion_1 +  "{";		 
		 informacion += z_id_nivel_salarial + "=" + v_id_nivel_salarial +  "{";		 
		 informacion += z_nm_nivel_salarial + "=" + v_nm_nivel_salarial +  "{";	

		 informacion += z_id_sector1 + "=" + v_id_sector1 +  "{";		 
		 informacion += z_nm_sector1 + "=" + v_nm_sector1 +  "{";	

		 
		 



		 
	     informacion += nombre_jefe + "=" + v_nombre_jefe + "{";				 		 
	     informacion += apellido1_jefe + "=" + v_apellido1_jefe + "{";				 		 
	     informacion += apellido2_jefe + "=" + v_apellido2_jefe + "{";				 		 
	     informacion += telefono_jefe + "=" + v_telefono_jefe + "{";				  		 		 		 		 		 		 		 
		 
		 if (v_id_area_gral_exp1!=null)
		 {		 
		     informacion += id_area_gral_exp1 + "=" + v_id_area_gral_exp1 +  "{"; 
		     informacion += n_area_gral_exp1 + "=" + v_n_area_gral_exp1 +  "{"; 	
		 }
		 if (v_id_area_gral_exp1!=null)
		 {		 
		     informacion += id_area_gral_exp2 + "=" + v_id_area_gral_exp2 +  "{"; 
		     informacion += n_area_gral_exp2 + "=" + v_n_area_gral_exp2 +  "{"; 			 		 		 
         }
//alert(informacion);		
   
		 if (informacion == "")
  			 { alert(msg_68);}
		 else
			 {
                 // Se debe validar que le area y experiencia general no se repitan
//                 if ((v_id_grupo_exp == v_id_grupo_exp1 && v_id_area_gral_exp == v_id_area_gral_exp1) || (v_id_grupo_exp == v_id_grupo_exp2 && v_id_area_gral_exp == v_id_area_gral_exp2) || (v_id_grupo_exp1 == v_id_grupo_exp2 && v_id_area_gral_exp1 == v_id_area_gral_exp2))  
				 				   				 
                 if ((v_id_grupo_exp != "" && v_id_grupo_exp1 != "") && (v_id_grupo_exp == v_id_grupo_exp1) && (v_id_area_gral_exp != "" && v_id_area_gral_exp1 != "") && (v_id_area_gral_exp == v_id_area_gral_exp1))
		         {
		             alert('No se debe repetir el Área y Experiencia General');
				 }	 
				 else	 
		         {		
	                 if ((v_id_grupo_exp != "" && v_id_grupo_exp2 != "") && (v_id_grupo_exp == v_id_grupo_exp2) && (v_id_area_gral_exp != "" && v_id_area_gral_exp2 != "") && (v_id_area_gral_exp == v_id_area_gral_exp2))
			         {
			             alert('No se debe repetir el Área y Experiencia General');
					 }	 
					 else	 
			         {
		                 if ((v_id_grupo_exp1 != "" && v_id_grupo_exp2 != "") && (v_id_grupo_exp1 == v_id_grupo_exp2) && (v_id_area_gral_exp1 != "" && v_id_area_gral_exp2 != "") && (v_id_area_gral_exp1 == v_id_area_gral_exp2))
				         {
				             alert('No se debe repetir el Área y Experiencia General');
						 }	 
						 else	 
				         {					 				    	 
    			             document.forms["forma_parametro"].elements["parametro"].value = informacion;		
		    	             forma_parametro.submit();
						 }	 
					 }	 
		         }
			 }
    	}

function validar_datos_experiencia(accion){

	var accion_bbdd = accion;
	var numeros = "0123456789";

	if (accion == 3)
	{
	var empresa_nulo = comprobar_espacios("exp_profesional","STD_EMPLOYER")
	if (empresa_nulo == 0)
	{
		alert(msg_70);
		document.forms["exp_profesional"].elements["STD_EMPLOYER"].focus();
		return;
	}
	var inicio_nulo = comprobar_espacios("exp_profesional","STD_DT_START")
	if (inicio_nulo == 0)
	{
	alert(msg_60);
	document.exp_profesional.STD_DT_START.focus();
	return;
	}else{
		var formato_fecha=comprobar_formato_fecha(document.forms["exp_profesional"].elements["STD_DT_START"].value);
		if(formato_fecha==0){
			alert(msg_67);
			document.forms["exp_profesional"].elements["STD_DT_START"].focus();
			return;
		}//cierre if (formato_fecha==0)
	}//cierre if (inicio_nulo == 0)
			
	fec_fin = document.forms["exp_profesional"].elements["STD_DT_END"].value;
	fec_ini = document.forms["exp_profesional"].elements["STD_DT_START"].value;

	if (fec_fin!="")
	{
		var formato_fecha=comprobar_formato_fecha(document.forms["exp_profesional"].elements["STD_DT_END"].value);
		if(formato_fecha==0)
		{
			alert(msg_67);
			document.forms["exp_profesional"].elements["STD_DT_END"].focus();
			return;
		}
		else
		{
			if (fec_ini>fec_fin)
			{
				alert(msg_58);
				document.forms["exp_profesional"].elements["STD_DT_END"].focus();
				return;
			}//cierre if (fec_ini>fec_fin)
		}//cierre if (formato_fecha==0)
	}//cierre if (fechafin_nula == 0)
	
/*			var puesto_nulo1 = comprobar_espacios("exp_profesional","CMX_N_EMPINDIREC")
			if (puesto_nulo == 0)
	{
		alert(msg_72);
		document.forms["exp_profesional"].elements["CMX_N_EMPINDIREC"].focus();
		return;
	}  */
	
		var puesto_nulo = comprobar_espacios("exp_profesional","STD_INITIAL_JOB")
   if (puesto_nulo == 0)
	{
		alert(msg_72);
		document.forms["exp_profesional"].elements["STD_INITIAL_JOB"].focus();
		return;
	}
	

   var puesto_nulo = comprobar_espacios("exp_profesional","STD_INITIAL_JOB")
   if (puesto_nulo == 0)
	{
		alert(msg_72);
		document.forms["exp_profesional"].elements["STD_INITIAL_JOB"].focus();
		return;
	}

}//cierre accion=3

if (accion == 1)
{
   
	var empresa_nulo = comprobar_espacios("experiencia_existente","STD_EMPLOYER*1")
	if (empresa_nulo == 0)
	{
	alert(msg_76);
	document.forms["experiencia_existente"].elements["STD_EMPLOYER*1"].focus();
	return;
	}

	var inicio_nulo = comprobar_espacios("experiencia_existente","STD_DT_START*1")
	if (inicio_nulo == 0)
	{
		alert(msg_60);
		document.forms["experiencia_existente"].elements["STD_DT_START*1"].focus();
		return;
	}else{
		var formato_fecha=comprobar_formato_fecha(document.forms["experiencia_existente"].elements["STD_DT_START*1"].value);
		if(formato_fecha==0){
			alert(msg_67);
			document.forms["experiencia_existente"].elements["STD_DT_START*1"].focus();
			return;
		}//cierre if (formato_fecha==0)
	}//cierre if (inicio_nulo == 0)

	var fec_fin = document.forms["experiencia_existente"].elements["STD_DT_END*1"].value;
	var fec_ini = document.forms["experiencia_existente"].elements["STD_DT_START*1"].value;

	if (fec_fin !="")
	{
		var formato_fecha=comprobar_formato_fecha(document.forms["experiencia_existente"].elements["STD_DT_END*1"].value);
		if(formato_fecha==0)
		{
			alert(msg_67);
			document.forms["experiencia_existente"].elements["STD_DT_END*1"].focus();
			return;
		}
		else
		{
			if (fec_ini>fec_fin)
			{
				alert(msg_58);
				document.forms["experiencia_existente"].elements["STD_DT_END*1"].focus();
				return;
			}//cierre if (fec_ini>fec_fin)
		}//cierre if (formato_fecha==0)
	}//cierre if (fec_fin !="")

		   var puesto_nulo = comprobar_espacios("experiencia_existente","STD_INITIAL_JOB*1")
			if (puesto_nulo == 0)
			{
			alert(msg_77);
			document.forms["experiencia_existente"].elements["STD_INITIAL_JOB*1"].focus();
			return;
			}

		}

	comprobar_experiencia(accion_bbdd);
	}


	function comprobar_datos_paqueteria(accion_a_tomar)
	{
		var action = accion_a_tomar;
		var informacion = "";
		 
   	    if (informacion != "")
   	    	{ 
			informacion += "{";
		}
	   		
		

		var n_nodo = "_NODO*" + "0";
		var n_registro = "_REGISTRO*" + "0";

		var porcentaje = "CMX_PORCENTAJE_EQUIPO*" +"0";

		var v_nodo = document.forms["paqueteria"].elements["_NODO*0"].value;
		var formulario = "";
	
		var v_porcentaje = document.forms["paqueteria"].elements["porcentaje"].value;
		
		if(v_porcentaje ==""){return;}  
		
		if (isNaN(v_porcentaje)){
		    alert("El porcentaje debe ser un valor numérico.");
			document.forms["paqueteria"].elements["porcentaje"].focus();			
			document.forms["paqueteria"].elements["porcentaje"].value="";
			return;
		}				
						
		if (v_porcentaje < 0){
		    alert("El porcentaje no puede ser menor a 0 % ");
			document.forms["paqueteria"].elements["porcentaje"].focus();
			return;
		}				
			
		if (v_porcentaje>100){
		    alert("El porcentaje no puede ser mayor a 100");
			document.forms["paqueteria"].elements["porcentaje"].focus();
			return;
		}	
		else	
		{		
			if (action == 1)
			{	//alert("El porcentaje no puede ser menor que cero");
				//var v_registro = document.forms["paqueteria"].elements["_REGISTRO*0"].value;
				var v_registro = 0;
				var v_accion_canal = "UPDATE";
				formulario = "paqueteria";
	
				//var v_paqueteria = document.forms[formulario].elements["CMX_MANEJAEQUIPOCOMPUTO*1"].value;
				//var v_porcentaje = document.forms[formulario].elements["CMX_PORCENTAJE_EQUIPO*1"].value;
				//var v_paqueteria = document.forms[formulario].elements["paqueteria_v"].value;
				var v_porcentaje = document.forms[formulario].elements["porcentaje"].value;
			}
	
			if (action == 0)
			{
				var v_registro =0
				var v_accion_canal = "INSERT";
				formulario = "paqueteria";
	
				//var v_paqueteria = document.forms[formulario].elements["paqueteria_v"].value;
				var v_porcentaje = document.forms[formulario].elements["porcentaje"].value;
	
			}
			
			 informacion += n_nodo + "=" + v_nodo + "{_ACCION*0="+ v_accion_canal + "{";
			 informacion += n_registro + "=" + v_registro + "{";
	
			//informacion += paqueteria + "=" + v_paqueteria + "{"; 
			 informacion += porcentaje + "=" + v_porcentaje + "{"; 
	
	
			 document.forms["forma_parametro"].elements["parametro"].value = informacion;		
			 forma_parametro.submit();
		}	 
   	}



