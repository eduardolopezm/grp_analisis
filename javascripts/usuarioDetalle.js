var tituloGeneralOk = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
var tituloGeneralError = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';

$(document).ready(function (){
    if(UserId != ""){
    	fnObtenerInfoUsuario(UserId);
    	// validaciones
    }else{
    	fnObtenerInfoUsuario('');
    }

    $("#btn-regresar").click(function (){
    	window.location.href ="WWW_Users.php";
    });

    $(".inputCapitulos").click(function(){
		fnObtenerPartidas(fnObtenerCapitulo(),UserId);
    });

    $(".inputUE").click(function(){
		fnObtenerAlmacen(fnObtenerCheckedInput('panelUE','inputUE','ue'), UserId);
    });

	$("#btn-guardar").click(function (){
		var Mensaje = '<p> ¿Desea guardar al usuario? </p>';
		if(UserId != ""){
			Mensaje = '<p> ¿Desea modificar al usuario? </p>';
		}
    	muestraModalGeneralConfirmacion(3,tituloGeneralOk,Mensaje, '','fnGuardarInformacionUsuario(\''+UserId+'\')','');
    });

    $("#inputFile").change(function() {
        var file = this.files[0];
        var imagefile = file.type;
        var match= ["image/jpeg","image/png","image/jpg"];
        if(!((imagefile==match[0]) || (imagefile==match[1]) || (imagefile==match[2]))){
			muestraModalGeneral(3,tituloGeneralError,errMensaje);
            $("#inputFile").val('');
            return false;
        }
    });

    $(".checkTodo").click(function (){
    	$(this).hide();
    	panel = $(this).data('panel');
    	panelCheck = $(this).data('panelcheck');
    	$('#'+panel+ " > .uncheckTodo").show();
    	fnDesseleccionarTodo(panelCheck);

    	if(panelCheck == 'panelUE'){
    		fnObtenerAlmacen(fnObtenerCheckedInput('panelUE','inputUE','ue'), UserId);
    	}
    });

    $(".uncheckTodo").click(function (){
    	$(this).hide();
    	panel = $(this).data('panel');
    	panelCheck = $(this).data('panelcheck');
    	$('#'+panel+ " > .checkTodo").show();
    	fnSeleccionarTodo(panelCheck);

    	if(panelCheck == 'panelUE'){
    		fnObtenerAlmacen(fnObtenerCheckedInput('panelUE','inputUE','ue'), UserId);
    	}
    });

    $('.linkModulos').click(function(){
    	fnObtenerFuncionesModulo($(this).data('modulo'),UserId);
    });

    $('#btn-restablecer').click(function(){
    	$("#divContenedorCheck input[type=checkbox]").prop('checked', false);

    	fnObtenerAlmacen(fnObtenerCheckedInput('panelUE','inputUE','ue'), UserId);
    	fnObtenerPartidas(fnObtenerCapitulo(),UserId);

    	$('#divContenedorCheck .checkTodo').hide();
    	$('#divContenedorCheck .uncheckTodo').show();
    });

});

function fnObtenerCheckedInput(panel, clase, dato){
	inInputsCheck="";
	$("#"+panel).find("."+clase).each(function(index){
		if( $(this).prop('checked') ) {
		    inInputsCheck += "'"+$(this).data(''+dato)+"',";
		}
	});

	if(inInputsCheck !=""){
		inInputsCheck = inInputsCheck.slice(0,-1);
	}

	return inInputsCheck;
}

function fnObtenerAlmacen(almacen,userid){
	muestraCargandoGeneral();
	if(almacen ==""){
		$("#panelAlmacen").empty();
		ocultaCargandoGeneral();
		return false;
	}

	var fd = new FormData();
	fd.append("option","obtenerAlmacenes");
	fd.append("userid",userid);
	fd.append("almacen",almacen);
	
	$.ajax({
	    async:false,
	    url:"modelo/usuarioDetalleModelo.php",
	    type:'POST',
	    data: fd, 
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: 'json',
	    success: function (data) {
	    	if(data.result){
	    		var strHTML="";
	    		var valorChecked="";

	    		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		//!!                   Almacenes.                  !!
	    		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		
	    		strHTML="";
	    		datosAlmacenes = data.contenido.almacenes;
	    		strEncabezado="";
		    	valorChecked="";
		    	valorDefaultChecked="";

	    		if(datosAlmacenes!== null){
		    		$.each(datosAlmacenes,function(index, el) {
		    			valorChecked="";
		    			valorDefaultChecked="";

		    			if(el.valor == "1"){valorChecked="checked";}

		    			strHTML += '<div class="col-md-12">';

		    			if(strEncabezado != el.ln_ue){
		    				strEncabezado = el.ln_ue;
		    				strHTML += '<label>'+el.desc_ue+'</label>';
		    			}
		    			if(el.valDefault == "1"){valorDefaultChecked="checked";}

		    			strHTML += '<div class="form-inline row">\
								        <div class="col-md-8 col-xs-10">\
								            <p>'+el.locationname+'</p>\
								        </div>\
								        <div class="col-md-2 col-xs-1">\
								            <input class="inputAlmacen" type="checkbox" id="almacen'+el.loccode+'" data-ue="'+el.ln_ue+'" data-almacen="'+el.loccode+'" name="almacen'+el.loccode+'" '+valorChecked+'  style="width: 20px; height:20px;"/> \
								        </div>\
								    </div>';
		    			strHTML += '</div>';

		    		});
	    		}
	    		$("#panelAlmacen").empty();
	    		$("#panelAlmacen").append('<div class="col-xs-2 col-xs-offset-10 text-center lbAlmacenDefalut"><p>Almacén Default</p></div>'+strHTML);

	    		ocultaCargandoGeneral();
	        }else{
	        	ocultaCargandoGeneral();
	        }
	    }
	}); 
	ocultaCargandoGeneral();
}

function fnObtenerFuncionesModulo(idModulo,userid){
	//
	if($('#panel'+idModulo).find('.input'+idModulo).length >0){
		return false;
	}

	muestraCargandoGeneral();

	var fd = new FormData();
	fd.append("option","obtenerFunciones");
	fd.append("userid",userid);
	fd.append("modulo",idModulo);
	
	$.ajax({
	    async:false,
	    url:"modelo/usuarioDetalleModelo.php",
	    type:'POST',
	    data: fd, 
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: 'json',
	    success: function (data) {
	    	if(data.result){

	    		strHTML="";
	    		datosFunciones = data.contenido.funciones;
	    		strEncabezado="";
	    		var intFuncionChecked=0;
	    		$.each(datosFunciones,function(index, el) {
	    			valorChecked="";
	    			if(el.valor == "1"){valorChecked="checked";intFuncionChecked++;}

	    			strHTML += '<div class="col-md-12">';

	    			if(strEncabezado != el.name){
	    				strEncabezado = el.name;
	    				strHTML += '<label>'+el.name+'</label>';
	    			}

	    			strHTML += '<div class="form-inline row">\
							        <div class="col-md-10 col-xs-10">\
							            <p>'+el.title+'</p>\
							        </div>\
							        <div class="col-md-2 col-xs-2">\
							            <input class="inputfunciones input'+idModulo+'" type="checkbox" id="funcion'+el.functionid+'" data-modulofuncion="'+idModulo+'" data-funcion="'+el.functionid+'" name="funcion'+el.functionid+'" '+valorChecked+'  style="width: 20px; height:20px;"/>\
							        </div>\
							    </div>';
	    			strHTML += '</div>';

	    		});
	    		$("#panel"+ idModulo).empty();
	    		$("#panel"+ idModulo).append(strHTML);
	    		if(intFuncionChecked == datosFunciones.length){
	    			fnCheckedAllGeneral('check'+idModulo);
	    		}else{
	    			fnUnCheckedAllGeneral('check'+idModulo)
	    		}
	    		
	    		ocultaCargandoGeneral();
	        }else{
	        	ocultaCargandoGeneral();
	        }
	    }
	}); 
	ocultaCargandoGeneral();
}

function fnObtenerCapitulo(){
	inCapitulos="";
	$("#panelCapitulos").find(".inputCapitulos").each(function(index){
		if( $(this).prop('checked') ) {
		    inCapitulos += "'"+$(this).data('capitulo')+"',";
		}
	});

	if(inCapitulos !=""){
		inCapitulos = inCapitulos.slice(0,-1);
	}

	return inCapitulos;
}

function fnSeleccionarTodo(panel){
	$("#"+panel+" input[type=checkbox]").prop('checked', true);
	if(panel=="panelCapitulos"){
		fnObtenerPartidas(fnObtenerCapitulo(),UserId);
	}
}

function fnDesseleccionarTodo(panel){
	$("#"+panel+" input[type=checkbox]").prop('checked', false);
	if(panel=="panelCapitulos"){
		fnObtenerPartidas(fnObtenerCapitulo(),UserId);
	}
}

function fnGuardarInformacionUsuario(userid){

	if( fnValidarCamposObligatorios(userid) == false){
		return false;
	}

	var fd = new FormData(document.getElementById('frmDatosUsuarios'));
	fd.append("option","guardarInformacionUsuario");
	fd.append("userid",userid);

	muestraCargandoGeneral();

	intIndex=0;
	$('#panelUR').find('.inputUR').each(function(index){
		if($(this).prop('checked')){
			fd.append("inputUR" + intIndex ,$(this).data('ur'));
			intIndex++;
		}
	});

	fd.append("totalUR",intIndex);

	intIndex=0;
	$('#panelUE').find('.inputUE').each(function(index){
		if($(this).prop('checked')){
			fd.append("inputUE" + intIndex ,$(this).data('ue'));
			intIndex++;
		}
	});
	fd.append("totalUE",intIndex);

	intIndex=0;
	$('#panelPerfiles').find('.inputPerfil').each(function(index){
		if($(this).prop('checked')){
			fd.append("inputPerfil" + intIndex ,$(this).data('perfil'));
			intIndex++;
		}
	});
	fd.append("totalPerfil",intIndex);

	intIndex=0;
	$('#panelAlmacen').find('.inputAlmacen').each(function(index){
		if($(this).prop('checked')){
			fd.append("inputAlmacen" + intIndex ,$(this).data('almacen'));
			fd.append("inputAlmacenUE" + intIndex ,$(this).data('ue'));
			intIndex++;
		}
	});
	fd.append("totalAlmacen",intIndex);

	intIndex=0;
	$('#panelCapitulos').find('.inputCapitulos').each(function(index){
		if($(this).prop('checked')){
			fd.append("inputCapitulo" + intIndex ,$(this).data('capitulomiles'));
			intIndex++;
		}
	});
	fd.append("totalCapitulo",intIndex);

	intIndex=0;
	$('#panelPartidasEspecificas').find('.inputPartida').each(function(index){
		if($(this).prop('checked')){
			fd.append("inputPartida" + intIndex ,$(this).data('partida'));
			intIndex++;
		}
	});
	fd.append("totalPartidaEspecifica",intIndex);

	almcenDefault=-1;
	$('#panelAlmacen').find('.radioAlmacenDefault').each(function(index){
		if($(this).prop('checked')){
			almcenDefault = $(this).data('almacen');
		}
	});

	fd.append("almacenDefault",almcenDefault);

	urDefault=-1;
	$('#panelUR').find('.radioUrDefault').each(function(index){
		if($(this).prop('checked')){
			urDefault = $(this).data('ur');
		}
	});

	fd.append("urDefault",urDefault);

	ueDefault=-1;
	$('#panelUE').find('.radioUeDefault').each(function(index){
		if($(this).prop('checked')){
			ueDefault = $(this).data('ue');
		}
	});

	fd.append("ueDefault",ueDefault);

	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	//!!                                               !!
	//!!            FUNCIONES Y PERMISOS.              !!
	//!!                                               !!
	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

	intIndex=0;
	$('#panelesFunciones').find('.inputfunciones').each(function(index){
		fd.append("inputFuncion" + intIndex ,$(this).data('funcion'));
		fd.append("inputModuloFuncion" + intIndex ,$(this).data('modulofuncion'));
		if($(this).prop('checked')){
			fd.append("inputValFuncion" + intIndex ,'1');
		}else{
			fd.append("inputValFuncion" + intIndex ,'0');
		}
		intIndex++;
	});
	fd.append("totalFunciones",intIndex);

	$.ajax({
	    async:false,
	    url:"modelo/usuarioDetalleModelo.php",
	    type:'POST',
	    data: fd, 
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: 'json',
	    success: function (data) {
	    	if(data.result){
	    		if(userid != ""){
	    			$("#txtUsuario").val(userid);
	    		}else{
	    			UserId = $("#txtUsuario").val();
	    		}
	    		$("#txtUsuario").prop('readonly',true);

	    		muestraModalGeneral(3,tituloGeneralOk,data.Mensaje);
	    		ocultaCargandoGeneral();
	        }else{
	        	muestraModalGeneral(3,tituloGeneralError,data.Mensaje);
	        	ocultaCargandoGeneral();
	        }
	    }
	});
	ocultaCargandoGeneral(); 
}

function fnValidarCamposObligatorios(userid){
	var blnValidacion = false;

	var msjError="";

	if($("#txtUsuario").val() ==""){
		msjError+='<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo Usuario es obligatorio.</p>';
	}

	if(userid == ""){
		if($("#txtContrasena").val() == ""){
			msjError+='<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo Contraseña es obligatorio.</p>';
		}
	}

	if($("#txtNombreUsuario").val() ==""){
		msjError+='<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo Nombre Completo es obligatorio.</p>';
	}

	if($("#selectEstatusUsuario").val() ==""){
		msjError+='<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo Estatus es obligatorio.</p>';
	}

	intIndex=0;
	$('#panelUR').find('.inputUR').each(function(index){
		if($(this).prop('checked')){			
			intIndex++;
		}
	});

	if(intIndex == 0){
		msjError+='<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Es necesario seleccionar una Unidad Responsable.</p>';
	}


	if(msjError == ""){
		blnValidacion= true;
	}else{
		blnValidacion= false;
		muestraModalGeneral(3,tituloGeneralError,msjError);
	}


	return blnValidacion;
}

function fnObtenerPartidas(capitulos,userid){
	muestraCargandoGeneral();
	if(capitulos ==""){
		$("#panelPartidasEspecificas").empty();
		ocultaCargandoGeneral();
		return false;
	}

	var fd = new FormData();
	fd.append("option","obtenerPartidasEspecificas");
	fd.append("userid",userid);
	fd.append("capitulos",capitulos);
	
	$.ajax({
	    async:false,
	    url:"modelo/usuarioDetalleModelo.php",
	    type:'POST',
	    data: fd, 
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: 'json',
	    success: function (data) {
	    	if(data.result){
	    		var strHTML="";
	    		var valorChecked="";

	    		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		//!!          PARTIDAS ESPECFICAS.                 !!
	    		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		
	    		strHTML="";
	    		datosPartidas = data.contenido.partidas;
	    		strEncabezado="";

	    		$.each(datosPartidas,function(index, el) {
	    			valorChecked="";
	    			if(el.valor == "1"){valorChecked="checked";}

	    			strHTML += '<div class="col-md-12">';

	    			if(strEncabezado != el.ccap){
	    				strEncabezado = el.ccap;
	    				strHTML += '<label>'+el.descripcion+'</label>';
	    			}

	    			strHTML += '<div class="form-inline row">\
							        <div class="col-md-10 col-xs-10">\
							            <p>'+el.descripcion_partida+'</p>\
							        </div>\
							        <div class="col-md-2 col-xs-2">\
							            <input class="inputPartida" type="checkbox" id="partida'+el.partidacalculada+'" data-partida="'+el.partidacalculada+'" name="partida'+el.partidacalculada+'" '+valorChecked+'  style="width: 20px; height:20px;"/>\
							        </div>\
							    </div>';
	    			strHTML += '</div>';

	    		});
	    		$("#panelPartidasEspecificas").empty();
	    		$("#panelPartidasEspecificas").append(strHTML);
	    		ocultaCargandoGeneral();
	        }else{
	        	ocultaCargandoGeneral();
	        }
	    }
	}); 
	ocultaCargandoGeneral();
}

function fnCheckedAllGeneral(panel){
	$('#'+panel+ " > .checkTodo").show();
	$('#'+panel+ " > .uncheckTodo").hide();
}

function fnUnCheckedAllGeneral(panel){
	$('#'+panel+ " > .uncheckTodo").show();
	$('#'+panel+ " > .checkTodo").hide();
}

function fnObtenerInfoUsuario(userid){
	var fd = new FormData();
	fd.append("option","obtenerInfoUsuario");
	fd.append("userid",userid);

	muestraCargandoGeneral();
	$.ajax({
	    async:false,
	    url:"modelo/usuarioDetalleModelo.php",
	    type:'POST',
	    data: fd, 
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: 'json',
	    success: function (data) {
	    	if(data.result){
	    		var strHTML="";
				var valorChecked="";
				

	    		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		//!!               DATOS GENERALES.                !!
	    		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

				datosGeneral = data.contenido.general;
	    		$.each(datosGeneral,function(index, el) {
    				$("#txtUsuario").val(el.userid);
    				if(userid !=""){
    					$("#txtUsuario").prop('readonly',true);
    				}else{
    					$("#txtUsuario").prop('readonly',false);
    				}
    				
    				$("#txtNombreUsuario").val(el.realname);
    				$("#txtTelefono").val(el.phone);
					$("#txtEmail").val(el.email);
					$("#nuCaja").val(el.obraid);
    				$("#txtDepartamento").val(el.department);
    				$("#selectEstatusUsuario").val(el.estatus);
					$("#selectEstatusUsuario").multiselect('rebuild');
					$("#imgFoto").attr("src",el.ImagenUsuario);
					
				});

				//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		//!!                  OBJETOS.                    !!
				//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				
				datosobjetos = data.contenido.objetosprincipales;
				if(datosobjetos != null){
					$.each(datosobjetos,function(index, el) {
							$("#selectObjetoPrincipalUsuarios").find("option[value="+el.loccode+"]").prop("selected", "selected");
							$("#selectObjetoPrincipalUsuarios").multiselect('rebuild');

					});
				}
				
				

	    		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		//!!                  PERFILES.                    !!
	    		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		var intPerfilChecked=0;
	    		datosPerfil = data.contenido.perfil;
	    		$.each(datosPerfil,function(index, el) {
	    			valorChecked="";

	    			if(el.profilevalue == "1"){valorChecked="checked";intPerfilChecked++;}

	    			strHTML += '<div class="col-md-12">';
	    			strHTML += '<div class="form-inline row">\
							        <div class="col-md-10 col-xs-10">\
							            <p>'+el.profilename+'</p>\
							        </div>\
							        <div class="col-md-2 col-xs-2">\
							            <input class="inputPerfil" type="checkbox" id="perfil'+el.profileid+'" data-perfil="'+el.profileid+'" name="perfil'+el.profileid+'" '+valorChecked+'  style="width: 20px; height:20px;"/>\
							        </div>\
							    </div>';
	    			strHTML += '</div>';

	    		});
	    		$("#panelPerfiles").empty();
	    		$("#panelPerfiles").append(strHTML);
	    		if(intPerfilChecked == datosPerfil.length){
	    			fnCheckedAllGeneral('checkPerfil');
				}
				
				//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		//!!        UNIDADES RESPONSABLES NUEVAS.          !!
	    		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		strHTML="";
				// datosAlmacenes = data.contenido.almacenes;
				datosUR = data.contenido.unidadResponsable;
	    		strEncabezado="";
		    	valorChecked="";
		    	valorDefaultChecked="";
				// var intAlmacenChecked = 0;
				var intURChecked=0;
	    		if(datosUR!== null){
		    		$.each(datosUR,function(index, el) {
		    			valorChecked="";
		    			valorDefaultChecked="";

		    			if(el.valor == "1"){valorChecked="checked";intURChecked++;}

		    			strHTML += '<div class="col-md-12">';

		    			if(strEncabezado != el.legalname){
		    				strEncabezado = el.legalname;
		    				strHTML += '<label>'+el.legalname+'</label>';
		    			}
		    			if(el.valDefault == "1"){valorDefaultChecked="checked";}

		    			strHTML += '<div class="form-inline row">\
								        <div class="col-md-8 col-xs-10">\
								            <p>'+el.tagdescription+'</p>\
								        </div>\
								        <div class="col-md-2 col-xs-1">\
								            <input class="inputUR" type="checkbox" id="ur'+el.tagref+'" data-ur="'+el.tagref+'" name="ur'+el.tagref+'" '+valorChecked+'  style="width: 20px; height:20px;"/> \
								        </div>\
								        <div class="col-md-2 col-xs-1 text-right">\
								        	<input class="radioUrDefault" type="radio" name="optionsRadiosUr" data-ur="'+el.tagref+'" id="radiour'+el.tagref+'" value="'+el.tagref+'" style="width: 20px; height:20px;" '+valorDefaultChecked+'>\
								        </div>\
								    </div>';
		    			strHTML += '</div>';

		    		});
 
		    		$("#panelUR").empty();
		    		$("#panelUR").append('<div class="col-xs-2 col-xs-offset-10 text-center lbUrDefalut"><p>UR Default</p></div>'+strHTML);
		    		if(intURChecked == datosUR.length){
		    			fnCheckedAllGeneral('checkUR');
		    		}
	    		}

	    		// //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		// //!!          UNIDADES RESPONSABLES.               !!
	    		// //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		// strHTML="";
	    		// datosUR = data.contenido.unidadResponsable;
	    		// strEncabezado="";
	    		// var intURChecked=0;

	    		// $.each(datosUR,function(index, el) {
	    		// 	valorChecked="";
	    		// 	if(el.valor == "1"){valorChecked="checked"; intURChecked++;}

	    		// 	strHTML += '<div class="col-md-12">';

	    		// 	if(strEncabezado != el.legalname){
	    		// 		strEncabezado = el.legalname;
	    		// 		strHTML += '<label>'+el.legalname+'</label>';
	    		// 	}

	    		// 	strHTML += '<div class="form-inline row">\
				// 			        <div class="col-md-10 col-xs-10">\
				// 			            <p>'+el.tagdescription+'</p>\
				// 			        </div>\
				// 			        <div class="col-md-2 col-xs-2">\
				// 			            <input class="inputUR" type="checkbox" id="ur'+el.tagref+'" data-ur="'+el.tagref+'" name="ur'+el.tagref+'" '+valorChecked+'  style="width: 20px; height:20px;"/>\
				// 					</div>\
				// 			    </div>';
	    		// 	strHTML += '</div>';

	    		// });
	    		// $("#panelUR").empty();
	    		// $("#panelUR").append(strHTML);
	    		// if(intURChecked == datosUR.length){
	    		// 	fnCheckedAllGeneral('checkUR');
	    		// }


				//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		//!!        UNIDADES EJECUTORAS NUEVAS.          !!
	    		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		strHTML="";
				datosUE = data.contenido.unidadEjecutora;
	    		strEncabezado="";
		    	valorChecked="";
		    	valorDefaultChecked="";
				// var intAlmacenChecked = 0;
				var intUEChecked=0;
	    		if(datosUE!== null){
		    		$.each(datosUE,function(index, el) {
		    			valorChecked="";
		    			valorDefaultChecked="";

		    			if(el.valor == "1"){valorChecked="checked";intUEChecked++;}

		    			strHTML += '<div class="col-md-12">';

		    			if(strEncabezado != el.tagdescription){
		    				strEncabezado = el.tagdescription;
		    				strHTML += '<label>'+el.tagdescription+'</label>';
		    			}
		    			if(el.valDefault == "1"){valorDefaultChecked="checked";}

		    			strHTML += '<div class="form-inline row">\
								        <div class="col-md-8 col-xs-10">\
								            <p>'+el.desc_ue+'</p>\
								        </div>\
								        <div class="col-md-2 col-xs-1">\
										<input class="inputUE" type="checkbox" id="ue'+el.ue+'" data-ue="'+el.ue+'" name="ue'+el.ue+'" '+valorChecked+'  style="width: 20px; height:20px;"/>\
								        </div>\
								        <div class="col-md-2 col-xs-1 text-right">\
								        	<input class="radioUeDefault" type="radio" name="optionsRadiosUe" data-ue="'+el.ue+'" id="radioue'+el.ue+'" value="'+el.ue+'" style="width: 20px; height:20px;" '+valorDefaultChecked+'>\
								        </div>\
								    </div>';
		    			strHTML += '</div>';

		    		});
 
		    		$("#panelUE").empty();
		    		$("#panelUE").append('<div class="col-xs-2 col-xs-offset-10 text-center lbUeDefalut"><p>UE Default</p></div>'+strHTML);
		    		if(intUEChecked == datosUE.length){
		    			fnCheckedAllGeneral('checkUE');
		    		}
	    		}
	    		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		//!!          UNIDADES EJECUTORAS.               !!
	    		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		// strHTML="";
	    		// datosUE = data.contenido.unidadEjecutora;
	    		// strEncabezado="";
	    		// var intUEChecked=0;

	    		// $.each(datosUE,function(index, el) {
	    		// 	valorChecked="";
	    		// 	if(el.valor == "1"){valorChecked="checked";intUEChecked++;}

	    		// 	strHTML += '<div class="col-md-12">';

	    		// 	if(strEncabezado != el.tagdescription){
	    		// 		strEncabezado = el.tagdescription;
	    		// 		strHTML += '<label>'+el.tagdescription+'</label>';
	    		// 	}

	    		// 	strHTML += '<div class="form-inline row">\
				// 			        <div class="col-md-10 col-xs-10">\
				// 			            <p>'+el.desc_ue+'</p>\
				// 			        </div>\
				// 			        <div class="col-md-2 col-xs-2">\
				// 			            <input class="inputUE" type="checkbox" id="ue'+el.ue+'" data-ue="'+el.ue+'" name="ue'+el.ue+'" '+valorChecked+'  style="width: 20px; height:20px;"/>\
				// 			        </div>\
				// 			    </div>';
	    		// 	strHTML += '</div>';

	    		// });
	    		// $("#panelUE").empty();
	    		// $("#panelUE").append(strHTML);
	    		// if(intUEChecked == datosUE.length){
	    		// 	fnCheckedAllGeneral('checkUE');
	    		// }

	    		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		//!!                ALMACENES.                     !!
	    		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		strHTML="";
	    		datosAlmacenes = data.contenido.almacenes;
	    		strEncabezado="";
		    	valorChecked="";
		    	valorDefaultChecked="";
		    	var intAlmacenChecked = 0;
	    		if(datosAlmacenes!== null){
		    		$.each(datosAlmacenes,function(index, el) {
		    			valorChecked="";
		    			valorDefaultChecked="";

		    			if(el.valor == "1"){valorChecked="checked";intAlmacenChecked++;}

		    			strHTML += '<div class="col-md-12">';

		    			if(strEncabezado != el.ln_ue){
		    				strEncabezado = el.ln_ue;
		    				strHTML += '<label>'+el.desc_ue+'</label>';
		    			}
		    			if(el.valDefault == "1"){valorDefaultChecked="checked";}

		    			strHTML += '<div class="form-inline row">\
								        <div class="col-md-8 col-xs-10">\
								            <p>'+el.locationname+'</p>\
								        </div>\
								        <div class="col-md-2 col-xs-1">\
								            <input class="inputAlmacen" type="checkbox" id="almacen'+el.loccode+'" data-ue="'+el.ln_ue+'" data-almacen="'+el.loccode+'" name="almacen'+el.loccode+'" '+valorChecked+'  style="width: 20px; height:20px;"/> \
								        </div>\
								        <div class="col-md-2 col-xs-1 text-right">\
								        	<input class="radioAlmacenDefault" type="radio" name="optionsRadiosAlmacen" data-almacen="'+el.loccode+'" id="radioalmacen'+el.loccode+'" value="'+el.loccode+'" style="width: 20px; height:20px;" '+valorDefaultChecked+'>\
								        </div>\
								    </div>';
		    			strHTML += '</div>';

		    		});

		    		$("#panelAlmacen").empty();
		    		$("#panelAlmacen").append('<div class="col-xs-2 col-xs-offset-10 text-center lbAlmacenDefalut"><p>Almacén Default</p></div>'+strHTML);
		    		if(intAlmacenChecked == datosAlmacenes.length){
		    			fnCheckedAllGeneral('checkAlmacen');
		    		}
	    		}
	    		

	    		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		//!!                 CAPITULOS.                    !!
	    		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		strHTML="";
	    		datosCapitulos = data.contenido.capitulos;

	    		blnCapitulo= 0;
	    		strCapitulo="";
	    		var intCapitulosChecked=0;
	    		$.each(datosCapitulos,function(index, el) {
	    			valorChecked="";
	    			if(el.valor == "1"){valorChecked="checked"; blnCapitulo=1; strCapitulo += "'"+el.ccap+"',";intCapitulosChecked++;}

	    			strHTML += '<div class="col-md-12">';
	    			strHTML += '<div class="form-inline row">\
							        <div class="col-md-10 col-xs-10">\
							            <p>'+el.descripcion+'</p>\
							        </div>\
							        <div class="col-md-2 col-xs-2">\
							            <input class="inputCapitulos" type="checkbox" id="capitulo'+el.ccapmiles+'" data-capitulo="'+el.ccap+'" data-capitulomiles="'+el.ccapmiles+'" name="capitulo'+el.ccapmiles+'" '+valorChecked+'  style="width: 20px; height:20px;"/>\
							        </div>\
							    </div>';
	    			strHTML += '</div>';
	    		});
	    		$("#panelCapitulos").empty();
	    		$("#panelCapitulos").append(strHTML);

	    		if(intCapitulosChecked == datosCapitulos.length){
	    			fnCheckedAllGeneral('checkCaitulo');
	    		}
	    		
	    		if(blnCapitulo==1){
	    			strCapitulo = strCapitulo.slice(0,-1);
	    			fnObtenerPartidas(strCapitulo,userid);
	    		}

	    		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		//!!               OBTENER MODULOS.                !!
	    		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    		strHTML="";
	    		datosModulos = data.contenido.modulos;

	    		$.each(datosModulos,function(index, el) {
		    		strHTML += '<div class="col-sm-12 col-md-6">\
		                            <div class="panel-group" id="accordion'+el.submoduleid+'" role="tablist">\
		                                <div class="panel panel-default">\
		                                    <div class="panel-heading h35" role="tab" id="headingOne">\
		                                        <h4 class="panel-title">\
		                                        <div class="fl text-left">\
		                                            <a class="linkModulos collapsed" data-modulo="'+el.submoduleid+'" role="button" data-toggle="collapse" data-parent="#accordion'+el.submoduleid+'" href="#collapseTwo'+el.submoduleid+'" aria-expanded="false" aria-controls="#collapseTwo'+el.submoduleid+'">\
		                                                <b>'+el.title+'</b>\
		                                            </a>\
		                                        </div>\
		                                        <div class="text-right" id="check'+el.submoduleid+'">\
		                                            <span style="cursor: pointer; display: none;"  class="glyphicon glyphicon-check checkTodo" data-panelcheck="panel'+el.submoduleid+'" data-panel="check'+el.submoduleid+'" aria-hidden="true" ></span>\
		                                            <span style="cursor: pointer; display: none;"  class="glyphicon glyphicon-unchecked uncheckTodo" data-panelcheck="panel'+el.submoduleid+'" data-panel="check'+el.submoduleid+'" aria-hidden="true"></span>\
		                                        </div>\
		                                        </h4>\
		                                    </div>\
		                                    <div id="collapseTwo'+el.submoduleid+'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">\
			                                    <div class="panel-body panelConfiguracion" id="panel'+el.submoduleid+'">\
			                                    </div>\
			                                </div>\
		                                </div>\
		                            </div><!-- .panel-group -->\
		                        </div>';
		        });

		        $("#panelesFunciones").empty();
	    		$("#panelesFunciones").append( ""+strHTML);

			

	    		ocultaCargandoGeneral();
	        }else{
	        	ocultaCargandoGeneral();
	        }
	    }
	}); 

			
	ocultaCargandoGeneral();
}

function fnObtenerOption(componenteSelect, intComillas = 0){
    var valores = "";
    var comillas="'";
    var select = document.getElementById(''+componenteSelect);
    for ( var i = 0; i < select.selectedOptions.length; i++) {
        if (select.selectedOptions[i].value != '-1') {
            if(intComillas == 1){
                comillas="";
            }
            if (i == 0) {
                valores = ""+comillas+select.selectedOptions[i].value+comillas+"";
            }else{
                valores = valores+", "+comillas+select.selectedOptions[i].value+comillas+"";
            }
        }
    }
    return valores;
}