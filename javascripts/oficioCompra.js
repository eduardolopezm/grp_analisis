// objetos de configuración
	window.listaProveedores = new Array();

	window.listadoHabilitado = true;

	window.buscadores = [ "proveedor" ];
	window.buscadoresConfiguracion = {};
	$.each(window.buscadores,function(index,valor){
		window.buscadoresConfiguracion[valor] = {};
	});
	window.buscadoresConfiguracion.proveedor.origenDatos = "listaProveedores";

	window.buscadoresConfiguracion.proveedor.valoresAlfanumericos = true;
	window.buscadoresConfiguracion.proveedor.valorAMostrar = "concatenado";
	window.buscadoresConfiguracion.proveedor.tipoBusqueda = "incluye";
	window.buscadoresConfiguracion.proveedor.sinHREF = true;

	window.buscadoresConfiguracion.proveedor.arregloEstatico = true;

	window.propiedadesResize = {
									widthsEspecificos: {
										descargar:		"7%",
										eliminar:		"7%"
									},
									encabezadosADosRenglones: {
										tipoAnexo:		"Tipo de<br />Anexo"
									},
									camposConWidthAdicional: [ "nombreArchivo" ]
								};

// asignación de eventos
$(document).ready(function() {
	$("#fechaConvocatoria").parent().on('dp.change', fnCambioDeFecha);
	$("#fechaInicio").parent().on('dp.change', fnCambioDeFecha);
	$("#fechaEstimada").parent().on('dp.change', fnCambioDeFecha);
	$("#fechaIni").parent().on('dp.change', fnCambioDeFecha);
	$("#fechaFin").parent().on('dp.change', fnCambioDeFecha);
	$("#fechaFirma").parent().on('dp.change', fnCambioDeFecha);

	inicioPanel();

	// comportamiento de guardado
	$('#guardar').on('click', function(){
		actualizaRegistro();
	});
	// comportamiento de regresar
	$('#regresar').on('click', function(){
		location.replace("./procesoDeCompra.php");
	});

	// comportamiento de la pre carga de archivos
	$('#inpt-upload').on('change',fnMuestraArchivosASubir);

	// comportamiento de la seleccion de archivos
	$('#label-upload').on('click',cleanTblFiles);

	// comportamiento de carga final de archivos
	$('#btn-upload').on('click',fnSubirArchivos);

	// Evento encargado de cargar los distintos grids al momento de hacer clic en sus respectivas pestañas
	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		var tabla = $(e.target).attr("tabla");
		if(!window.tablasRenderizadas[tabla]){
			$.each($('a[data-toggle$="tab"]'),function(index,value){
				$(this).click(function () {return false;});
			});
			setTimeout(function(){
				fnLlenaTabla(tabla,window.tablasDeArchivos[tabla]);
			}, 500);
			window.tablasRenderizadas[tabla] = true;
		}
	});

	$(document)
	// comportamiento de la eliminación de un archivo para su carga
	.on('click','.rm-file',function(){
		var $linea = $(this).parents('tr[id]').eq(0), $spanContent = $('<span>'), $tabla = $(this).parents('table[id]'),
			$btnCancel = $('<button>',{ class:'btn botonVerde',html:'Cancelar','data-dismiss':'modal' });
		var $pie = $('<button>',{ class:'btn botonVerde',html:'Aceptar','data-dismiss':'modal',click:function(){
				var id = $linea.attr('id'), idTabla = $tabla.attr('id');
				$linea.remove();
				if(idTabla == 'tbl-filesToUp'){ delete(window.filesRoot[id]); }
			}});
		$spanContent.append($pie).append($btnCancel);
		muestraModalGeneralConfirmacion(3,window.tituloGeneral,'¿Realmente desea retirar el documento?',$spanContent);
	})
	// comportamiento de eliminación de documentos del expediente electrónico
	.on('cellselect','#files-adm, #files-seg, #files-otr',function(e){
		var index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
		if(campo != 'eliminar'){ return false; }
		var row = $(this).jqxGrid('getrowdata', index);
		if(!$("#guardar").length&&row.postConcluido==0){ return false; }
		muestraModalGeneralConfirmacion(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', "¿Está seguro de que desea eliminar el archivo <strong>"+row.nombreArchivo+"</strong>?",'','fnEliminarDocumentos','\''+row.identificador+'\'');
	});

	// Comportamiento del monto de compra
	$("#montoCompra").focusout(function(event){
		if(!(parseFloat($("#montoCompra").val())<=montoRequisicion)){
			muestraModalGeneral(3,window.tituloGeneral, "El monto del contrato excede el monto de $"+montoRequisicionTexto+" para la requisición <strong>#"+$("#folioRequisicion").html()+"</strong>.");
			$("#montoCompra").val("");
		}
	});

	$('#observaciones').css({
		resize: 'none',
		'width': '100%'
	});
	$('#observaciones').parent('div').removeClass('input-group');

	//fnValidaPeriodicidad();

	// Bloquear campos si no hay botón de guardar
	if(!$("#guardar").length){
		$.each($("input[type='text'],input[type='number'],textarea"),function(index,value){
			if(!($(value).attr('id')===undefined)){
				$(value).attr('readonly', true);
			}
		});
		$.each($("select"),function(index,value){
			$(value).multiselect('disable');
		});
	}
});

function inicioPanel(){
	window.modelo = 'modelo/oficioCompraModelo.php';
	window.home = 'procesoDeCompra.php'
	window.tituloGeneral = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';

	window.tablasDeArchivos = [];
	window.tablasDeArchivos['files-adm'] = [];
	window.tablasDeArchivos['files-seg'] = [];
	window.tablasDeArchivos['files-otr'] = [];

	window.filesRoot = [];
	window.selectAnexo = [];
	window.selectAnexoTipoCarpeta = [];

	window.tablasRenderizadas = {
									"files-adm":			true,
									"files-seg":			false,
									"files-otr":			false
								};

	cargaSelects();

	fnLlenaTabla('files-adm',window.tablasDeArchivos['files-adm']);
	if(identificador){
		fnObtenerInformacionRegistro();
		fnObtenerInformacionTablas();

		$('#selectUnidadNegocio').multiselect('disable');
		$('#selectUnidadEjecutora').multiselect('disable');
	}
}

function cargaSelects(){
	$.ajaxSetup({async: false});
	$.post(window.modelo, {method:'cargaSelects'}).then(function(res){
		window.selectAnexo = res.selectAnexo;
		if(!!res.selectAnexoTipoCarpeta){
			$.each(res.selectAnexoTipoCarpeta,function(index,value){
				selectAnexoTipoCarpeta[value.value] = value.label;
			});
		}
		if(!!res.selectExpediente){
			$.each(res.selectExpediente, function(index, val) {
				$('#tipoExpediente').append(new Option(val.label, val.value));
			});
		}
		if(!!res.selectContratacion){
			$.each(res.selectContratacion, function(index, val) {
				$('#tipoContratacion').append(new Option(val.label, val.value));
			});
		}
		if(!!res.selectPeriodoContrato){
			$.each(res.selectPeriodoContrato, function(index, val) {
				$('#periodoContrato').append(new Option(val.label, val.value));
			});
		}
		fnFormatoSelectGeneral(".tipoExpediente");
		fnFormatoSelectGeneral(".tipoContratacion");
		fnFormatoSelectGeneral(".periodoContrato");
	});
	if(!!window.buscadores){
		if(window.buscadores.length){
			if(!window.listaProveedores.length){
				$.ajaxSetup({async: false});
				$.post(window.modelo, {method:'datosListaProveedores'}).then(function(res){
	                window.listaProveedores = res.registrosEncontrados;
	                window.listaProveedores.sort(ordenamientoDinamico("valor"));
				});
			}
		}
	}
	fnFormatoSelectGeneral(".partidaEspecifica");
	fnFormatoSelectGeneral(".articulo");
	fnFormatoSelectGeneral(".periodoContrato");
	fnFormatoSelectGeneral(".contratoDuracionUnidad");
}

function fnObtenerInformacionTablas(){
	var forma = new FormData();

	forma.append('method', 'listaDeDocumentos');
	forma.append('identificador', identificador);
	$.ajax({
		url: window.modelo,
		dataType: 'json',
		cache: false,
		contentType: false,
		processData: false,
		data: forma,
		type: 'post',
		async: false
	})
	.then(function(res){
		$.each($('a[data-toggle$="tab"]'),function(index,value){
			if(res.hasOwnProperty($(this).attr('tabla'))){
				if(res[$(this).attr('tabla')].length!=window.tablasDeArchivos[$(this).attr('tabla')].length){
					window.tablasDeArchivos[$(this).attr('tabla')] = res[$(this).attr('tabla')];
					window.tablasRenderizadas[$(this).attr('tabla')] = false;
				}
			}
		});
		tablaActiva = $('ul[class$="nav-tabs"]').find('[class$="active"]').find('a').attr('tabla');
		if(!window.tablasRenderizadas[tablaActiva]){
			fnLlenaTabla(tablaActiva,window.tablasDeArchivos[tablaActiva]);
			window.tablasRenderizadas[tablaActiva] = true;
		}
	});
}

function fnObtenerInformacionRegistro(){
	var forma = new FormData();

	forma.append('method', 'informacionProcesoCompra');
	forma.append('identificador', identificador);
	$.ajax({
		url: window.modelo,
		dataType: 'json',
		cache: false,
		contentType: false,
		processData: false,
		data: forma,
		type: 'post',
		async: false
	})
	.then(function(res){
		if(res.success){
			var	triggerFechaC = false,
				triggerFechaI = false;
			$.each(res.content,function(index,value){
				if($('#'+index).is("label")){
					$('#'+index).html(value);
				}else if($('#'+index).is("select")){
					if($("#"+index+" option[value='"+value+"']").length){
						$('#'+index).multiselect('select', value);
						$('#'+index).multiselect('rebuild');
					}
				}else{
					if(index.substr(0,5)!="fecha"){
						$('#'+index).val(value);
					}else{
						triggerFechaC = ( index=="fechaConvocatoria"&&(value==""||value=="00-00-0000") ? true : triggerFechaC);
						triggerFechaI = ( index=="fechaIni"&&(value==""||value=="00-00-0000") ? true : triggerFechaI);
						value = ( index=="fechaConvocatoria"&&(value==""||value=="00-00-0000") ? fnDiaHabilAnterior() : value );
						value = ( index=="fechaIni"&&(value==""||value=="00-00-0000") ? fnDiaHabilSiguiente() : value );
						value = ( index=="fechaFirma"&&(value==""||value=="00-00-0000") ? $("#fechaIni").val() : value );
						if(!(value==""||value=="00-00-0000")){
							$('#'+index).val(value).trigger('change');
							if(index=="fechaConvocatoria"&&triggerFechaC){
								$('#'+index).val(fnHoy()).trigger('change');
							}
							if(index=="fechaIni"&&triggerFechaI){
								$('#'+index).val($("#fechaConvocatoria").val()).trigger('change');
							}
						}
						if(index=="fechaConvocatoria"){
							$('#'+index).parent().data('DateTimePicker').minDate(res.content.dtm_fecha_creacion);
						}
					}
				}
			});
			$.each($(".componenteFeriadoAtras").find("input[type='text']"),function(index,value){
				if(!$(value).val()||$(value).val()=="00-00-0000"){
					$(value).val( new Date().toISOString().split("T")[0].split("-").reverse().join("-") ).trigger('change');
				}
			});
			var	proveedorVisible = new Array();
			if($('#proveedor').val()){
				proveedorVisible.push($('#proveedor').val());
			}
			if($('#textoOculto__proveedor').val()){
				proveedorVisible.push($('#textoOculto__proveedor').val());
			}
			$('#textoVisible__proveedor').val( proveedorVisible.join(" - ") );
		}
	});
}

function fnLlenaTabla(tabla='files-adm',datos=[]){
	// declaración de variables principales
	var contenedor = 'content-'+tabla, nombreExcel = 'Documentos administrativos',
		tblObj, tblTitulo, tblExcel=[0,1,2,3,4], tblVisual=[0,1,2,3,4,5,6];
	tblObj = [
		{ name: 'nombreArchivo', type: 'string'},// 0
		{ name: 'extension', type: 'string'},// 1
		{ name: 'tipoAnexo', type: 'string'},// 2
		{ name: 'usuario', type: 'string'},// 3
		{ name: 'fecha', type: 'string'},// 4
		{ name: 'descargar', type: 'string'},// 5
		{ name: 'eliminar', type: 'string'},// 6
		{ name: 'postConcluido', type: 'string'},// 7
		{ name: 'identificador', type: 'string'},// 8
	];
	tblTitulo = [
		{ text: 'Nombre Archivo', datafield: 'nombreArchivo', editable: false, width: '29%', cellsalign: 'center', align: 'center' },// 0
		{ text: 'Extensión', datafield: 'extension', editable: false, width: '11%', cellsalign: 'center', align: 'center' },// 1
		{ text: 'Tipo de Anexo', datafield: 'tipoAnexo', editable: false, width: '19%', cellsalign: 'left', align: 'center' },// 2
		{ text: 'Usuario', datafield: 'usuario', editable: false, width: '14%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Fecha', datafield: 'fecha', editable: false, width: '11%', cellsalign: 'center', align: 'center' },// 4
		{ text: 'Descargar', datafield: 'descargar', editable: false, width: '5%', cellsalign: 'center', align: 'center' },// 5
		{ text: 'Eliminar', datafield: 'eliminar', editable: false, width: '5%', cellsalign: 'center', align: 'center' },// 6
	];
	// llamado de limpieza de la tabla
	fnLimpiarTabla(contenedor,tabla);
	// render de la tabla
	fnAgregarGrid_Detalle_nostring(datos, tblObj, tblTitulo, tabla, "", 1, tblExcel, false, true, "", tblVisual, nombreExcel);

	// resize del ancho de las columnas del Grid cada vez que hay cambio de página
	$("#"+tabla).bind("pagechanged", function (event) {
		if(typeof window.propiedadesResize != "undefined"){
			fnGridResize(tabla,window.propiedadesResize);
		}
	});
	if(typeof window.propiedadesResize != "undefined"){
		fnGridResize(tabla,window.propiedadesResize);
	}

	setTimeout(function(){
		$.each($('a[data-toggle$="tab"]'),function(index,value){
			$(this).unbind('click');
		});
	}, 500);
}

/**
 * Funcion para el guardado de la solicitud de comisión
 * @return	{[type]}		[description]
 */
function actualizaRegistro() {
	var	params = $.extend({}, getParams('form-info'), ( !$("#procesoContratacion").is(":hidden") ? getParams('form-info-contrato') : {} ) ),
		validacionFechas = [];

	if(fnDiasEntreFechas($('#fechaConvocatoria').val(),$('#fechaInicio').val())<1){
		validacionFechas.push("La Fecha Inicio es menor a la Fecha Convocatoria");
	}
	if(fnDiasEntreFechas($('#fechaInicio').val(),$('#fechaEstimada').val())<1){
		validacionFechas.push("La Fecha Estimada es menor a la Fecha Inicio");
	}
	if(estatus>1){
		if(fnDiasEntreFechas($('#fechaConvocatoria').val(),$('#fechaIni').val())<1){
			validacionFechas.push("La Fecha Inicio de Contrato es menor a la Fecha Convocatoria");
		}
		if(fnDiasEntreFechas($('#fechaIni').val(),$('#fechaFin').val())<1){
			validacionFechas.push("La Fecha Fin de Contrato es menor a la Fecha Inicio de Contrato");
		}
		if(fnDiasEntreFechas($('#fechaIni').val(),$('#fechaFirma').val())<1||fnDiasEntreFechas($('#fechaFirma').val(),$('#fechaFin').val())<1){
			validacionFechas.push("La Fecha Firma no está comprendida entre la Fecha Inicio de Contrato y Fecha Fin de Contrato");
		}
	}

	if(validacionFechas.length){
		muestraModalGeneral(3, window.tituloGeneral, validacionFechas.join("<br>"));
		return false;
	}

	$.each(params,function(index,value){
		if($('#'+index).is("select")&&(value==-1||value===null)){
			params[index] = "";
		}
	});

	params.method = 'update';
	params.identificador = identificador;

	var common = {
		html: 'Aceptar',
		class: 'btn btn-danger', 'data-dismiss': 'modal'
	};

	$.ajax({
		type:			'POST',
		dataType:		'JSON',
		url:			window.modelo,
		data:			params,
		async:			false
	}).done(function(res) {
		common.class = 'btn btn-danger';
		if (res.success) {
			common.click = function() {
				window.location.href = window.home;
			};
			common.class = 'btn botonVerde';
		}
		var pie = $('<button>', common);
		muestraModalGeneral(3, window.tituloGeneral, res.msg, pie);
	}).fail(function(res) {
		opt = 3;
		muestraMensajeTiempo(res.msg, opt, 'mensajes', msgTime);
	});
}

function fnValidaPeriodicidad(){
	var	periodoContrato = document.getElementById("periodoContrato"),
		dias = fnDiasEntreFechas($("#fechaIni").val(),$("#fechaFin").val());

	periodoContrato.options[1].setAttribute("disabled", "disabled");
	periodoContrato.options[2].setAttribute("disabled", "disabled");

	if(dias>=365&&dias<730){
		periodoContrato.options[1].removeAttribute('disabled');
	}
	if(dias>=730){
		periodoContrato.options[2].removeAttribute('disabled');
	}

	$("#periodoContrato").multiselect('rebuild');
}

/**************************************** FUNCIONES DE ARCHIVOS ****************************************/

function fnMuestraArchivosASubir() {
	var $el = $(this), $tbl = $('#tbl-filesToUp'), len = 0, files = [], $tbody;
		files = $el.prop('files');
		len = files.length;
		$tbody = $tbl.find('tbody');
	$('#showFiles').removeClass('hidden');
	if(len){
		// agregado a variable global
		var flag = 0, msg = 'Los siguientes documentos no cumplen con el formato. <ul>';
		$.each(files, function(index, val) {
			var aceptedType = ['text/plain', 'application/octet-stream', 'application/pdf', 'text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'image/bmp', 'image/gif', 'image/jpeg', 'image/png'], type = val.type;
			// if(aceptedType.indexOf(val.type)!==-1){ type = 'xml'; }
			if(aceptedType.indexOf(val.type)===-1){
				flag++;
				msg += '<li>'+val.name+'</li>';
			}else{
				filesRoot[index] = val;
				var row = [
					generateItem('td',{html:val.name}),
					generateItem('td',{html:val.size}),
					generateItem('td',{html:type}),
					'<td><select id="anexo'+index+'" name="anexo'+index+'" class="anexo"><option value="-1">Seleccionar...</option></select></td>',
					generateItem('span',{class:'btn btn-sm btn-primary bgc8 rm-file'},generateItem('span',{class:'glyphicon glyphicon-remove'}))
				];
				$tbody.append(generateItem('tr',{id:index,class:"renglon"},row));
			}
		});
		msg += '</ul>';
		if(flag!=0){
			muestraModalGeneral(3,window.tituloGeneral,msg);
		}
		$.each(window.selectAnexo, function(index, val) {
			$('.anexo').append(new Option(val.label, val.value));
		});
		fnFormatoSelectGeneral(".anexo");
	}
}

function cleanTblFiles(){
	var $tbl = $('#tbl-filesToUp'), $tbody;
	$tbody = $tbl.find('tbody');
	$tbody.html('');
	window.filesRoot = [];
}

function fnSubirArchivos() {
	var $imptFiles = $('#inpt-upload'), forma = new FormData(), files, archivosSinTipoAnexo = false;
	// files = $imptFiles.prop('files');
	files = window.filesRoot;
	tiposDeAnexo = [];
	if(!files.length){
		muestraModalGeneral(3,window.tituloGeneral,'Es necesario selección algún documento para realizar la carga.');
		return false;
	}
	$(".renglon").each(function(index){
		if($("#anexo"+$(this).attr('id')).val()=="-1"){
			archivosSinTipoAnexo = true;
		}
		tiposDeAnexo.push($("#anexo"+$(this).attr('id')).val());
	});
	if(archivosSinTipoAnexo){
		muestraModalGeneral(3,window.tituloGeneral,'Es necesario seleccionar el tipo de anexo correspondiente a cada archivo.');
		return false;
	}
	muestraCargandoGeneral();
	// carga de la informacion de la solictud
	forma.append('method', 'uploadFiles');
	forma.append('identificador', identificador);
	// carga de archivos
	$.each(files, function(index, val) {
		forma.append('archivos[]',val);
		forma.append('tiposDeAnexo[]',tiposDeAnexo[index]);
		forma.append('tiposDeAnexoTipoCarpeta[]',selectAnexoTipoCarpeta[tiposDeAnexo[index]]);
	});
	$.ajax({
		url: window.modelo,
		dataType: 'json',
		cache: false,
		contentType: false,
		processData: false,
		data: forma,
		type: 'post',
		async: false
	})
	.then(function(res){
		ocultaCargandoGeneral();
		var titulo = window.tituloGeneral;
		if(res.success){
			titulo = 'Operación exitosa';
			fnObtenerInformacionTablas();
			cleanTblFiles();
		}
		//$('.modal-backdrop.fade').removeClass('modal-backdrop in');
		muestraModalGeneral(3,window.tituloGeneral,res.msg);
		//$('.modal-backdrop.fade').removeClass('modal-backdrop in');
	});
}

function fnEliminarDocumentos(parametros){
	//Opcion para operacion
	dataObj = { 
		method: "deleteFiles",
		idDocumento: parametros[0]
	};
	$.ajaxSetup({async: false});
	$.post(window.modelo, dataObj)
	.then(function(res){
		var titulo = window.tituloGeneral;
		if(res.success){
			titulo = 'Operación exitosa';
			window.tablasDeArchivos[$('ul[class$="nav-tabs"]').find('[class$="active"]').find('a').attr('tabla')] = [];
			window.tablasRenderizadas[$('ul[class$="nav-tabs"]').find('[class$="active"]').find('a').attr('tabla')] = false;
		}
		fnObtenerInformacionTablas();
		muestraModalGeneral(3,titulo,res.msg);
		//$('.modal-backdrop.fade').removeClass('modal-backdrop in');
	});
}

/**************************************** FUNCIONES GENERALES ****************************************/

function fnSeleccionaDesdeListado(idArreglo='vacio',idObjeto,enFocusOut=false) {
    var arregloBusqueda = window[window.buscadoresConfiguracion[idObjeto].origenDatos],
        textoVisible = "";

    textoVisible = ( idArreglo!='vacio'&&( !window.buscadoresConfiguracion[idObjeto].valorAMostrar||window.buscadoresConfiguracion[idObjeto].valorAMostrar=="id" ) ? arregloBusqueda[idArreglo].valor : textoVisible );
    textoVisible = ( idArreglo!='vacio'&&window.buscadoresConfiguracion[idObjeto].valorAMostrar=="etiqueta" ? arregloBusqueda[idArreglo].texto : textoVisible );
    textoVisible = ( idArreglo!='vacio'&&window.buscadoresConfiguracion[idObjeto].valorAMostrar=="concatenado" ? arregloBusqueda[idArreglo].valor+" - "+arregloBusqueda[idArreglo].texto : textoVisible );

    $("#textoVisible__"+idObjeto).val(( idArreglo!='vacio' ? textoVisible : "" ));
    $("#"+idObjeto).val(( idArreglo!='vacio' ? arregloBusqueda[idArreglo].valor : "" ));
    $("#textoOculto__"+idObjeto).val(( idArreglo!='vacio' ? arregloBusqueda[idArreglo].texto : "" ));

	$("#RFC").val(( idArreglo!='vacio' ? arregloBusqueda[idArreglo].RFC : "" ));
	$("#representanteLegal").val(( idArreglo!='vacio' ? arregloBusqueda[idArreglo].representanteLegal : "" ));

    setTimeout(function(){
        if(enFocusOut){
            $("#sugerencia-"+idObjeto).hide();
            $("#sugerencia-"+idObjeto).empty();
        }
    }, 500);
}

function AlinearSelectsDerecha(){
	if($(".ColumnaDerecha").find("ul[class='multiselect-container dropdown-menu']").size()){
		$(".ColumnaDerecha").find("ul[class='multiselect-container dropdown-menu']").addClass("pull-right");
	}
}

function fnCambioDeFecha(e){
	var campoActivo = $(this).find('input').prop("id");

	if(campoActivo=="fechaConvocatoria"||campoActivo=="fechaInicio"){
		// Se forza que la fecha mínima del campo fechaTermino sea la fecha del campo fechaInicio, sólo cuando fechaInicio tuvo cambios
		if(campoActivo=="fechaConvocatoria"){
			$('#fechaInicio').parent().data('DateTimePicker').minDate($('#'+campoActivo).val());
			if($('#fechaIni').is(":visible")){
				$('#fechaIni').parent().data('DateTimePicker').minDate($('#'+campoActivo).val());
			}
		}
		$('#fechaEstimada').parent().data('DateTimePicker').minDate($('#'+campoActivo).val());
	}
	if(campoActivo=="fechaIni"||campoActivo=="fechaFin"){
		if($('#'+campoActivo).is(":visible")){
			// Se forza que la fecha mínima del campo fechaTermino sea la fecha del campo fechaInicio, sólo cuando fechaInicio tuvo cambios
			if(campoActivo=="fechaIni"){
				if(fnDiasEntreFechas($("#fechaIni").val(),$("#fechaFin").val())<0){
					$('#fechaFin').val($("#fechaIni").val());
				}
				$('#fechaFin').parent().data('DateTimePicker').minDate($('#'+campoActivo).val());
			}
			if($('#fechaFirma').parent().data('DateTimePicker').minDate()._i===undefined){
				$('#fechaFirma').parent().data('DateTimePicker').minDate($('#fechaIni').val());
			}else{
				if(fnDiasEntreFechas($('#fechaIni').val(),$('#fechaFirma').parent().data('DateTimePicker').minDate()._i)){
					$('#fechaFirma').parent().data('DateTimePicker').minDate($('#fechaIni').val());
				}
			}
			if(fnDiasEntreFechas($('#fechaFirma').parent().data('DateTimePicker').minDate()._i,$('#fechaFin').val())){
				$('#fechaFirma').parent().data('DateTimePicker').maxDate($('#fechaFin').val());
			}
			//fnValidaPeriodicidad();
		}
	}
	/*if(fnFechaMenorQue($('#fechaTermino').val(),$('#fechaInicio').val())){
		// Se hacen los ajustes necesarios si el campo fechaTermino tiene una fecha menor a la indicada en fechaInicio
		$('#fechaTermino').val($('#fechaInicio').val());
	}*/
}

// Funciones de fechas
// Convierte strings dd-mm-YYYY, dd/mm/YYYY, dd mm YYYY, YYYY-mm-dd, YYYY/mm/dd y YYYY mm dd a variables Date
function fnCambiaFechaDeStringAVariableDate(fecha){
	fecha = ( fecha.split("-").length==3 ? fecha.split("-") : ( fecha.split("/").length==3 ? fecha.split("/") : fecha.split(" ") ) );
	if(fecha.length!=3){
		return false;
	}
	fecha = ( fecha[0].length==4 ? fecha.join("-") : ( fecha[2].length==4 ? fecha.reverse().join("-") : false ) );

	return ( fecha!=false ? new Date( fecha ) : fecha );
}

// Compara fechas en formato string, regresa true, false o -1 
function fnFechaMenorQue(fecha1,fecha2){
	fecha1 = fnCambiaFechaDeStringAVariableDate(fecha1);
	fecha2 = fnCambiaFechaDeStringAVariableDate(fecha2);
	if(fecha1==false||fecha2==false){
		return -1;
	}

	return fecha1<fecha2;
}

// Regresa los días naturales entre dos fechas dadas
function fnDiasEntreFechas(fechaIni,fechaFin){
	fechaIni = fnCambiaFechaDeStringAVariableDate(fechaIni);
	fechaFin = fnCambiaFechaDeStringAVariableDate(fechaFin);
	if(fechaIni==false||fechaFin==false){
		return false;
	}
	dias = ( (
				Date.UTC(fechaFin.getFullYear(), fechaFin.getMonth(), fechaFin.getDate()) -
				Date.UTC(fechaIni.getFullYear(), fechaIni.getMonth(), fechaIni.getDate())
			) / 86400000 ) + 1;

	return dias;
}

// Regresa la fecha del día de hoy en formato dd-mm-YYYY
function fnHoy(hoy=new Date()){
	var	dia = hoy.toString().substr(8,2),
		mes = (hoy.getMonth()+1),
		anio = hoy.getFullYear();

	return dia+'-'+( mes<10 ? "0" : "" )+mes+"-"+anio;
}

// Regresa la fecha del día de ayer en formato dd-mm-YYYY
function fnDiaHabilAnterior(){
	return fnHoy(new Date(Date.now() - 86400000*( new Date(Date.now()).toString().substr(0,3).toLowerCase()=="mon" ? 3 : 1 )));
}

// Regresa la fecha del día de ayer en formato dd-mm-YYYY
function fnDiaHabilSiguiente(){
	return fnHoy(new Date(Date.now() + 86400000*( new Date(Date.now()).toString().substr(0,3).toLowerCase()=="fri" ? 3 : 1 )));
}
