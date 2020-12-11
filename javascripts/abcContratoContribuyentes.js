// asignación de eventos


var isValidProduct = false;
var fechaIni;
var fechaFin;
var modal = 0;
var newWindow;
var idContrato;
var idconfContrato;
var contribuyente = '';
var configuracion;
var rutaUnique = '';
var isFirst = true;

$(document).ready(function() {	
	$("#isRango").change(function(){
		$('#importe').val('');
		$('#rangoFinal').val('');
		$('#rangoInicial').val('');
	});
	ConfiguracionDefault();
	checkPermiss();

	$('#contribuyenteFiltro').on('change', function (e) {
        e.preventDefault();
        if ($(this).val() == '') {
            $('#contribuyenteIDFiltro').val("");
        }
    });


	$('#selectContratos').change(ConfiguracionDefault);
	
	// llamado de función inicial
	inicioPanel();
	// comportamiento del botón nuevo
	$('#nuevo').on('click',function(){
		fbCamposABloquear(false);
		AlinearSelectsDerecha();
		$('#tituloModal').html('<i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Nuevo Contrato de Contribuyente');
		$('#selectUnidadNegocio').attr('readonly', true);
		// $('#selectUnidadNegocio').val(10);
		// $('#selectUnidadEjecutora').val('0505-00');
		// $("#selectUnidadNegocio").multiselect('rebuild');
		// $("#selectUnidadEjecutora").multiselect('rebuild');
		// $('#contribuyente').attr('readonly', false);
		$('#selectEstatus').val('Pendiente');
		$('#selectEstatus').multiselect('rebuild');
		$('#dtFechaInicio').val(fechaIni);
		$('#dtFechaVigencia').val(fechaFin);
		$('#nuPeriodicidad').val(1);
		$('#modalUsoGeneral').modal('show');

		
	});

	$('#btnBuscarContribuyente').click(function(){
		lookUpData($('#contribuyente'));
	});

	$('#btnBuscarContribuyenteFiltro').click(function(){
		lookUpData($('#contribuyenteFiltro'));
	});

	$('#contribuyente').keypress(function (e)
    {
       if (e.which == 13)
       {
          lookUpData($(this));
       }
       else
       {
           $("#contribuyente").autocomplete("disable");
       }
   	});

	$('#contribuyenteFiltro').keypress(function (e)
    {
       if (e.which == 13)
       {
          lookUpData($(this));
       }
       else
       {
           $('#contribuyenteFiltro').autocomplete("disable");
       }
   	});
   	function lookUpData(autocompleteField)
	{
		if(isFirst){
			fnBusquedaContribuyente();
			isFirst = false;
		}

		autocompleteField.autocomplete("enable");
		autocompleteField.autocomplete("search");
	}
	
	// comportamiento del botón de guardado
	$('#guardar').on('click',function(){
		
		var selectCont = $('#selectContratos').val();
		contribuyente = $('#contribuyenteID').val();
		var selectUnidadNegocio = $('#selectUnidadNegocio').val();
		var selectUnidadEjecutora = $('#selectUnidadEjecutora').val();
		var selectEstatus = $('#selectEstatus').val();
		var dtFechaInicio = $('#dtFechaInicio').val();
		var selectTipoPeriodo = $('#selectTipoPeriodo').val();
		var nuPeriodicidad = $('#nuPeriodicidad').val();

		
		if(nuPeriodicidad == "" || nuPeriodicidad == null || nuPeriodicidad == 0 || nuPeriodicidad == "0" || nuPeriodicidad === "undefined" || selectTipoPeriodo == -1 || selectTipoPeriodo == "" || selectTipoPeriodo == null || selectTipoPeriodo == 0 || selectTipoPeriodo == "0" || selectTipoPeriodo === "undefined" || dtFechaInicio == "" || dtFechaInicio == null || dtFechaInicio == 0 || dtFechaInicio == "0" || dtFechaInicio === "undefined"|| selectEstatus == "" || selectEstatus == null || selectEstatus == 0 || selectEstatus == "0" || selectEstatus === "undefined" || selectUnidadEjecutora == "" || selectUnidadEjecutora == null || selectUnidadEjecutora == 0 || selectUnidadEjecutora == "0" || selectUnidadEjecutora === "undefined" || selectUnidadEjecutora === -1 || selectUnidadNegocio == -1 || selectUnidadNegocio == "" || selectUnidadNegocio == null || selectUnidadNegocio == 0 || selectUnidadNegocio == "0" || selectUnidadNegocio === "undefined" || selectCont == -1 || selectCont == "" || selectCont == null || selectCont == 0 || selectCont == "0" || selectCont === "undefined" || contribuyente == "" || contribuyente == null || contribuyente == 0 || contribuyente == "0" || contribuyente === "undefined")
		{ 
				
			if(selectCont == -1 || selectCont == "" || selectCont == null || selectCont == 0 || selectCont == "0" || selectCont === "undefined" ){
			// Si no selecciono Unidad Responsable
			ocultaCargandoGeneral();
			// muestraMensaje('Seleccionar UR para continuar con el proceso', 3, 'ModalGeneral_Advertencia', 5000);
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo contrato no puede ir vacio.</p>';
			muestraModalGeneral(3, titulo, mensaje);
			return true;
			}
			if(contribuyente == "" || contribuyente == null || contribuyente == 0 || contribuyente == "0" || contribuyente === "undefined"){
			// Si no selecciono Unidad Responsable
			ocultaCargandoGeneral();
			// muestraMensaje('Seleccionar UR para continuar con el proceso', 3, 'ModalGeneral_Advertencia', 5000);
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo contribuyente no puede ir vacio.</p>';
			muestraModalGeneral(3, titulo, mensaje);
			return true;
			}
			if(selectUnidadNegocio == -1 || selectUnidadNegocio == "" || selectUnidadNegocio == null || selectUnidadNegocio == 0 || selectUnidadNegocio == "0" || selectUnidadNegocio === "undefined"){
				// Si no selecciono Unidad Responsable
			ocultaCargandoGeneral();
			// muestraMensaje('Seleccionar UR para continuar con el proceso', 3, 'ModalGeneral_Advertencia', 5000);
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo de unidad responsable no esta definido.</p>';
			muestraModalGeneral(3, titulo, mensaje);
			return true;
			}
			if(selectUnidadEjecutora === -1 || selectUnidadEjecutora == "" || selectUnidadEjecutora == null || selectUnidadEjecutora == 0 || selectUnidadEjecutora == "0" || selectUnidadEjecutora === "undefined" ){
						// Si no selecciono Unidad Responsable
			ocultaCargandoGeneral();
			// muestraMensaje('Seleccionar UR para continuar con el proceso', 3, 'ModalGeneral_Advertencia', 5000);
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo de unidad ejecutora no esta definido.</p>';
			muestraModalGeneral(3, titulo, mensaje);
			return true;
			}

			if(selectEstatus == "" || selectEstatus == null || selectEstatus == 0 || selectEstatus == "0" || selectEstatus === "undefined" ){
			ocultaCargandoGeneral();
			// muestraMensaje('Seleccionar UR para continuar con el proceso', 3, 'ModalGeneral_Advertencia', 5000);
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo estatus no puede ir vacio.</p>';
			muestraModalGeneral(3, titulo, mensaje);
			return true;

			}
			if(selectEstatus == "" || selectEstatus == null || selectEstatus == 0 || selectEstatus == "0" || selectEstatus === "undefined" ){
				ocultaCargandoGeneral();
				// muestraMensaje('Seleccionar UR para continuar con el proceso', 3, 'ModalGeneral_Advertencia', 5000);
				var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
				var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo estatus no puede ir vacio.</p>';
				muestraModalGeneral(3, titulo, mensaje);
				return true;
	
				}
			if(dtFechaInicio == "" || dtFechaInicio == null || dtFechaInicio == 0 || dtFechaInicio == "0" || dtFechaInicio === "undefined" ){
				ocultaCargandoGeneral();
				// muestraMensaje('Seleccionar UR para continuar con el proceso', 3, 'ModalGeneral_Advertencia', 5000);
				var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
				var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo fecha de inicio no puede ir vacio.</p>';
				muestraModalGeneral(3, titulo, mensaje);
				return true;
			}
			if(selectTipoPeriodo == "" || selectTipoPeriodo == null || selectTipoPeriodo == 0 || selectTipoPeriodo == "0" || selectTipoPeriodo === "undefined" ){
				ocultaCargandoGeneral();
				// muestraMensaje('Seleccionar UR para continuar con el proceso', 3, 'ModalGeneral_Advertencia', 5000);
				var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
				var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo tipo de periodo no puede ir vacio.</p>';
				muestraModalGeneral(3, titulo, mensaje);
				return true;
			}
			if(nuPeriodicidad == "" || nuPeriodicidad == null || nuPeriodicidad == 0 || nuPeriodicidad == "0" || nuPeriodicidad === "undefined" ){	
				ocultaCargandoGeneral();
				// muestraMensaje('Seleccionar UR para continuar con el proceso', 3, 'ModalGeneral_Advertencia', 5000);
				var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
				var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo perdiodicidad  no puede ir vacio.</p>';
				muestraModalGeneral(3, titulo, mensaje);
				return true;
			}
		}
	
			var params = getParams(idForma), campos = getMatchets(idForma), msg = '',
				nombreCampo = {
					'importe':'Valor',
					'rangoInicial':'Rango Inicial',
					'rangoFinal':'Rango Final'
				},
				method = params.hasOwnProperty('identificador')?'update':'store';
			$.extend(params,{method:method,'valid':1});
			// $.each(campos,function(index, el) {
			// 	////if(el=='ind_activo'&&params[el]!=-1){ return; } // Línea adicional para este módulo
			// 	// if($("#"+el).is(":disabled")){ return; }
			// 	if(el=="selectUnidadEjecutora"&&$("#"+el).val()=="00"){ return; } // Línea adicional para este módulo
			// 	if(!params.hasOwnProperty(el)){ return; }
			// 	// if(esepcion.indexOf(el)!==-1){ return; }
			// 	if(params[el]!=0&&params[el]!=-1){ return; }
			// 	if(nombreCampo[el]===undefined){ return; }
			// 	if($("#"+el).val()!= '' && $("#"+el).val()!= 0 ){ return; } // Línea adicional para este módulo
			// 	if($("#isRango").is(':checked')){
			// 		if([el] == 'importe'){ return;}
			// 	}else{
			// 		if([el] == 'rangoFinal'){ return;}
			// 		if([el] == 'rangoInicial'){ return;}

			// 	}
			// 	msg += 'El campo '+nombreCampo[el]+' no puede ir vacío y debe ser siempre mayor a 0.<br />';

				
			// });
			// if($("#isRango").is(':checked')){
			// 	if(parseFloat($("#rangoFinal").val()) < parseFloat($("#rangoInicial").val()))
			// 		msg += 'El rango final debe ser mayor al rango inicial.<br />';
			// }
			// if(msg!=''){ muestraModalGeneral(3,'Error de datos',msg); return;}
			$.post(modelo, params).then(function(res){
				var titulo=res.success?'Operación Exitosa':'Error de Datos';
				if(res.success){
					contt = 0;
					// llenaTabla(res.content);
					// $.each(res.content, function(index, val) {
					// 	console.log('val',val);
					// 	if(index == 'identificador'){
					// 		console.log(val);
					// 		if(val == "'"+res.contratoID+"'"){
					// 			var row = $('#tablaGrid').jqxGrid('getrowdata', contt);
					// 			console.log('chuchincin',row);
					// 			return;
					// 		}
					// 		contt++;
					// 	}
					// });
					idContrato = res.select.identificador;
					idconfContrato = res.select.selectContratos;
					contribuyente = res.select.contribuyente;
					configuracion = res.select.selectContratos;
					// fnLimpiarCamposFormConHidden(idForma);
					llenaTabla('');
					//openModalIframe(idContrato, idconfContrato,contribuyente,configuracion);
					// PopupCenter("abcPropiedadesAtributos.php?modal=true&id_contratos="+idContrato+"&id_configuracion="+idconfContrato+"&name="+contribuyente+"&conf="+configuracion,'Agregar Area','800','640'); 
					// nextModal(0);
					if(idconfContrato == 7){
						rutaUnique = "abcPropiedadesAtributos.php?modal=true&id_contratos="+idContrato+"&id_configuracion="+idconfContrato+"&name="+contribuyente+"&conf="+configuracion+"&isUnique=true&hiddenBack=true"; 
						modalUnique('Atributos Contrato');
						
					}else{
						rutaUnique =  "abcObjetosParcialesContrato.php?modal=true&id_contratos="+idContrato+"&id_configuracion="+idconfContrato+"&name="+contribuyente+"&isUnique=true";
						modalUnique('Detalle Cobro');
					}

				} else {
					var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
					muestraModalGeneral(3, titulo, res.msg);
				}
			});
			$('#modalUsoGeneral').modal('hide');
			$('#productDescription').text('Sin Producto/Servicio seleccionado');
			$('#productDescription').css('color', 'black');

		
	});
	// comportamiento de modificación de registro
	$(document).on('cellselect','#tablaGrid',function(e){
		var CamposABloquear = false; // Línea adicional para este módulo
		// declaración de variables
		var index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
		// confirmación de evento a lanzar
		if(campo != 'modificar'){ return false; }
		// declaración de variables secundarias para evitar carga innecesaria
		var row = $(this).jqxGrid('getrowdata', index);

		// se extrae la información de los datos a modificar de base de datos
		$.post(modelo, { method:'edit', identificador: row.identificador })
		.then(function(res){
			// declaración de variables
			var titulo = 'Error de Datos', $spanContent = $('<span>');
			// comprobación de éxito

			if(res.success){
				fnLimpiarCamposFormConHidden(idForma);
				$.each(res.content, function(index, val) {
					if(index == 'identificador'){
						$('#forma').append('<input type="text" name="identificador" id="identificador" value="'+val+'" class="hidden"/>');
						CamposABloquear = true; // Línea adicional para este módulo
					}else{
						if($('#'+index).is("select")){
							$('#'+index).multiselect('select', val);
							$('#'+index).multiselect('rebuild');
							$('#'+index).trigger('change')

							if(index == 'selectUnidadNegocio'){
								$('#'+index).val(val);
								$('#'+index).multiselect('rebuild');
								$('#'+index).trigger('change')
								$('#'+index).multiselect('disable');
							} 
							if(index == 'selectUnidadEjecutora'){
								$('#'+index).multiselect('disable');
							}
							if(index == 'selectContratos'){
								$('#'+index).val(val);
								$('#'+index).multiselect('rebuild');
								$('#'+index).multiselect('disable');
								
							}
						}else{
							if(index == 'contribuyente'){
								if (modContribuyente == 1) {
									// Permisos para editar el contribuyente
									$('#'+index).attr('readonly', false);
								} else {
									$('#'+index).attr('readonly', true);
								}
								$('#'+index).val(val);
							}

							$('#'+index).val(val);
						}

						// if(index == 'selectObjetosParciales'){
						// 	// var defaults = [2,5,6,4,3,6,8,9];

						// 	// $('#'+index).val(function(i) { return defaults[i]; });

						// 	res.content.objetosParciales.forEach(function(valToSelect) {
						// 		$('#'+index).find('option[value="' + valToSelect + '"]').prop('selected', true);
						// 	  });
						// 	  $('#'+index).multiselect('rebuild');

						// }
					}
				});
				fbCamposABloquear(CamposABloquear);
				AlinearSelectsDerecha();
				$('#modalUsoGeneral #tituloModal').html('<i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Edición de Modalidad - Contrato de Contribuyente');
				$('#modalUsoGeneral').modal('show');
				llenaTabla('');
			}
		});
	})
	// comportamiento de eliminación del registro
	.on('cellselect','#tablaGrid',function(e){
		// declaración de variables
		var index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
		// validación de evento a lanzar
		if(campo != 'eliminar'){ return false; }
		// declaración de variables secundarias para evitar carga innecesaria
		var row = $(this).jqxGrid('getrowdata', index), $spanContent = $('<span>'),
			content = '¿Realmente desea eliminar el elemento <strong>'+row.identificador+'</strong>?',
			btnElimina = $('<button>',{ class : 'btn btn-primary btn-sm bgc8', html : 'Aceptar',
				click : function(){
			
						$.post(modelo, {method:'destroy', identificador:row.identificador})
						.then(function(res){
							var titulo = 'Error de Datos';
							console.log(res);
							if(res.success){
								titulo = 'Operación Exitosa';
								llenaTabla('');
							}
							muestraModalGeneral(3, titulo, res.msg);
						});
				
				
				}
			});
		// agregado de los botones de acción necesarios
		$spanContent.append(btnElimina).append(btnCancel);
		// ejecución de render de Vue en caso de que se tengan componentes
		muestraModalGeneralConfirmacion(3, 'Confirmación', content, $spanContent);
	});
	// Estilo para que los elementos se desplieguen bien en vista colapsada, y que además los multi select sobresalagan de los modales en la vista completa
	$('html > head').append( '<style type="text/css"> .OverdeSelectsenModales{ overflow-y: auto; overflow-y: auto; } @media (min-width: 992px){ .OverdeSelectsenModales{ overflow-x: visible !important; overflow-y: visible !important; } } </style>' );

	$("#isRango").click(function() {  
		this.value = this.checked ? 1 : 0;
		if($("#isRango").is(':checked')) { 
			$('#rangoInicial').val('');
			$('#rangoFinal').val('');
			$('#rangoInicial').removeAttr('readonly');
			$('#rangoFinal').removeAttr('readonly');
			$('#importe').attr('readonly', true);
		} else {  
			$('#rangoInicial').attr('readonly', true);
			$('#rangoFinal').attr('readonly', true);
			$('#importe').removeAttr('readonly');
			$('#importe').val('');
		}  
   });

   	$('#printStatus').click(function(){
		var atributo = $('#txtAtributo').val();
		var fechaIni = $('#txtFechaInicial').val();
		var fechaFin = $('#txtFechaFinal').val();

		if(atributo != '')
			window.open(getUrl()+'/PDFEstado_contrato.php?placa='+atributo+'&fechaInicio='+fechaIni+'&fechaFin='+fechaFin+'&confContrato=7&typeAtributo=PLACA', '_blank');
		else{
			muestraModalGeneral(3, 'No se puede mostrar', '<p>No se ha ingresado ningún atributo</p>');

		}
	});
   

});

/**
 * Obtener atributos y mostrar para filtrar información
 * @return {[type]} [description]
 */
function fnObtFiltrosContrato() {
	// Limpiar filtros
	$("#divFiltrosContratos").empty();
	
	// Obtener filtros de contrato seleccionado
	var params = getParams('frmFiltroActivos');
	$.extend(params,{method:'fnFiltrosContrato','valid':1});
	$.post(modelo, params).then(function(res){
		console.log("res: "+JSON.stringify(res));
		if(res.success){
			var dataJson = res.content;
			$("#txtFiltrosJson").val(''+JSON.stringify(dataJson));

			var infoHtml = '';
			var num = 0;
			for (var key in dataJson) {
				if (num == 3) {
					num = 0;
					infoHtml += '<div class="row"></div>';
				}

				infoHtml += '<div class="col-md-4">';
					infoHtml += '<div class="form-inline row">';
						infoHtml += '<div class="col-md-3 col-xs-12" >';
							infoHtml += '<span><label>'+dataJson[key].nombre+':</label></span>';
						infoHtml += '</div>';
						infoHtml += '<div class="col-md-9 col-xs-12">';
							infoHtml += '<input type="text" id="txtAtributo_'+dataJson[key].id+'" name="txtAtributo_'+dataJson[key].id+'" placeholder="'+dataJson[key].nombre+'" title="'+dataJson[key].nombre+'" value="" class="form-control" style="width: 100%;" />';
						infoHtml += '</div>';
					infoHtml += '</div><br>';
				infoHtml += '</div>';

				num ++;

				// console.log("nombre: "+dataJson[key].nombre);
			}

			$("#divFiltrosContratos").append(''+infoHtml);
		} else {
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			muestraModalGeneral(3, titulo, res.msg);
		}
	});
}

/*********************************** FUNCIONES DE EJECUCCIÓN ***********************************/
/**
 * Función de configuración inicial, donde se generan e inician las variables que serán
 * usadas en el programa
 */
function inicioPanel() {
	// variables globales del sistema
	this.root = window;
	this.rootFi = 0;
	this.rootFu = 0;
	this.url = getUrl();
	this.modelo = this.url+'/modelo/abcContratoContribuyentesModelo.php';
	this.modeloComponentes = this.url+'/modelo/componentes_modelo.php';
	this.idForma = 'forma';
	this.btnCancel = $('<button>',{ class:'btn btn-primary btn-sm bgc8',html:'Cancelar','data-dismiss':'modal' });
	this.baseOption = '<option value="0">Seleccione una opción</option>';
	llenaTabla();

	// funciones principales del sistema
	// cargaInicial();
	// cargaProductos();
	// colocación de estilos en mensaje de modal
	$('#'+this.idForma).css({ 'max-height':'600px', 'min-height':'200px' });
	// comportamiento de apertura y cierre de la modal de captura
	$('#modalUsoGeneral').on('hidden.bs.modal',function(){
		//var limpiarSelects = ['partida','tg','ff'];
		//$.each(limpiarSelects,function(index,el){ $('#'+el).multiselect('select',0).multiselect('rebuild'); });
		if($('#modalUsoGeneral').find('#identificador').size()){
			fnLimpiarCamposFormConHidden(idForma);
		}
		$('#modalUsoGeneral').find('#identificador').remove();
	});
	fechaIni =$('#dtFechaInicio').val();
	fechaFin =$('#dtFechaVigencia').val();


	// Líneas del archivo original
	// cargaSelects();
}
/**
 * Función para el llenado de la tabla principal con los datos
 * enviados como parámetro
 * @param {Array} data Contenido que será cargado en la tabla
 */
function llenaTabla(data) {
	// declaración de variables principales
	var data = data||[], nameExcel = '', tblObj, tblTitulo, tblExcel=[], tblVisual=[];

	// Obtener campos tabla
	var params = getParams('frmFiltroActivos');
	$.extend(params,{method:'fnCamposTabla','valid':1});
	$.post(modelo, params).then(function(res){
		// console.log("res: "+JSON.stringify(res));
		if(res.success){
			tblObj = res.columnasNombres;
			tblTitulo = res.columnasNombresGrid;
			nameExcel = res.nameExcel;
			tblExcel = res.tblExcel;
			tblVisual = res.tblVisual;
		} else {
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			muestraModalGeneral(3, titulo, 'Ocurrio un problema al obtener los campos de la tabla');
		}
	});

	// llamado de limpieza de la tabla
	fnLimpiarTabla('contenedorTabla', 'tablaGrid');
	// render de la tabla
	fnAgregarGrid_Detalle(data, tblObj, tblTitulo, 'tablaGrid', ' ', 1, tblExcel, true, false, "", tblVisual, nameExcel);
}
/**
 * Función de búsqueda de datos inicial
 */
function cargaInicial() {
	// solicitud al servidor de la información para la configuración
	$.post(this.modelo, {method:'show'}).then(function(res){
		llenaTabla(res.content);
	});
}
/************************* HELPERS *************************/
/**
 * Función para obtener la base de la url sin importar
 * en donde se encuentra el sistema
 * @return {String} Url encontrada según el proceso de filtrado
 */
function getUrl() {
	// declaración de variables principales
	var url = this.location.href.split('/');
	url.splice(url.length - 1);
	// retorno de información
	return url.join('/');
}

/* Funciones del archivo original */
function fbCamposABloquear(tipoAccion){
	/*
			'selectUnidadNegocio':'UR',
			'selectUnidadEjecutora':'UE',
	*/
	var	CamposABloquear = [{
			'LocCode':'Código de Almacén'
		}];

	$.each(CamposABloquear[0],function(index, el) {
		if($('#'+index).is("select")){
			$('#'+index).
			multiselect( tipoAccion ? "disable" : "enable" );
		}else{
			//$('#'+index).prop('readonly', tipoAccion);
			$('#'+index).attr('readonly', tipoAccion);
			if(!tipoAccion){
				$('#'+index).removeAttr("readonly");
			}
		}
	});
}

$('#btnBuscar').click(function(){
	fnMostrarDatos();
});

function fnMostrarDatos(){
	if ($("#selectContratosFiltro").val() == '-1' || $("#selectContratosFiltro").val() == '' || $("#selectContratosFiltro").val() == 'undefined') {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(3, titulo, 'Seleccionar el tipo de contrato');
		return false;
	}
	muestraCargandoGeneral();
	$.post(this.modelo, {method:'show', info: getParams('frmFiltroActivos')}).then(function(res){
		llenaTabla(res.content);
		ocultaCargandoGeneral();
	});
}

function cargaProductos(){
	$.post(this.modelo, {method:'cargaProductos'}).then(function(res){

            console.log(res.content);
            if(res.success)
            {
               
                $( "#txtNombre").autocomplete({
                    source: res.content,
                    select: function( event, ui ) {
                        
                        $( this ).val( ui.item.value + " (" + ui.item.label + ")");
                        $( "#stockID" ).val( ui.item.value );

                        return false;
                    }
                })
                .autocomplete( "instance" )._renderItem = function( ul, item ) {

					return $( "<li>" )
					.append( "<a>" + item.value + " | "+ item.label+"</a>" )
					.appendTo( ul );

                };  
            }
           
       
	});
}

function cargaSelects(){
	var Elemento = "";
	$.post(this.modelo, {method:'datosselectImpuestos'}).then(function(res){
		$.each(res.content, function(index, val) {
			$('#TaxProvince').append(new Option(val.label, val.value));
		});
		fnFormatoSelectGeneral(".TaxProvince");
	});
}



function AlinearSelectsDerecha(){
	if($("#ColumnaDerecha").find("ul[class='multiselect-container dropdown-menu']").size()){
		$("#ColumnaDerecha").find("ul[class='multiselect-container dropdown-menu']").addClass("pull-right");
	}
}

function fnValidaDatos() {
	var mensaje= "";
	var notifica= false;

	if ($("#CategoryID").val() == "" || $("#CategoryID").val() == "0") {
		mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp; Debe capturar código de categoria para poder continuar.</p>'; 

		notifica= true;
	}

	if ($("#CategoryDescription").val() == "" || $("#CategoryDescription").val() == "0") {
		mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp; Debe capturar descripción de categoria para poder continuar.</p>'; 
		notifica= true;
	}

	if ($("#StockAct").val() == $("#accountegreso").val()) {
		mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp; Las cuentas de cargo y abono no pueden ser iguales.</p>'; 

		notifica= true;
	}

	if (notifica) {
		muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', mensaje);	

		return false;
	} else {
		return true;
	}
}


function fnTraeInformacionProducto() {
	// Obtiene la descripcion de la partida generica
	var idProducto = $('#txtNombre').val();
	var	descripcionPartida = $('#productDescription');
	dataObj = { 
		option: 'muestraDescripcionProducto',
		idProducto: idProducto
	};
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/componentes_modelo.php",
		data:dataObj
	})
	.done(function( data ) {
		if(data.result){
			$('#productDescription').css('color', 'black');
			descripcionPartida.text( data.contenido);
			isValidProduct = true;
			
		}else{
			descripcionPartida.text('Producto o Servicio no valido');
			isValidProduct = false;
		}
	})
	.fail(function(result) {
		//console.log("ERROR");
		//console.log( result );
	});

}

function fnFinalidadFuncion (type) {
	var id_identificacion = $("#selectObjetoPrincipal"+type).val();
	dataObj = {
		option: 'mostrarObjetoParcial',
		id_identificacion: id_identificacion
	};
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType: "json",
		url: "modelo/componentes_modelo.php",
		data: dataObj
	}).done(function(data) {
		if (data.result) {
			dataJson = data.contenido.datos;
			var contenido = "";
			for (var info in dataJson) {
				contenido += "<option value='" + dataJson[info].id_fuente + "'>" + dataJson[info].fuentedescription + "</option>";
			}
			$('#selectObjetoParcial'+type).empty();
			$('#selectObjetoParcial'+type).append('<option value="-1">Sin Selección...</option>'+contenido);
			$('#selectObjetoParcial'+type).multiselect('rebuild');
		} else {
			console.log("ERROR Modelo");
			console.log( JSON.stringify(data) ); 
		}
	}).fail(function(result) {
		console.log("ERROR");
		console.log( result );
	});
 }

 function ConfiguracionDefault() {
	var idConfigContratos = $('#selectContratos').val();
	console.log(idConfigContratos);
    dataObj = { 
			option: 'mostrarConfiguracionDefault',
			confContrato_id: idConfigContratos
          };
    $.ajax({
      async:false,
      cache:false,
      method: "POST",
      dataType:"json",
      url: "modelo/componentes_modelo.php",
      data: dataObj
    })
    .done(function( data ) {
        console.log('default:',data.contenido.datos);
        if(data.result) {
			if(data.contenido.datos.length > 0){
				
				var contri = $( "#contribuyente" ).val();

				if(contri == ''){
					$( "#contribuyente" ).val(data.contenido.datos[0].texto);
					$( "#contribuyenteID" ).val( data.contenido.datos[0].value);
				}

				$('#selectTipoPeriodo').val( data.contenido.datos[0].periodo);
				$('#selectTipoPeriodo').multiselect('rebuild');
				$('#selectUnidadNegocio').val( data.contenido.datos[0].tagref);
				$('#selectUnidadNegocio').multiselect('rebuild');
				$('#selectUnidadEjecutora').val( data.contenido.datos[0].ln_ue);
				$('#selectUnidadEjecutora').multiselect('rebuild');
				if (Number(data.contenido.datos[0].validarAtributo1) > Number(0)) {
					$("#divAtributo1Captura").css("display", "block");
					$("#txtAtributo1Val").attr("title", data.contenido.datos[0].validarAtributo1Label);
					$("#txtAtributo1Val").attr("placeholder", data.contenido.datos[0].validarAtributo1Label);
					$("#txtAtributo1Val").attr("placeholder", data.contenido.datos[0].validarAtributo1Label);
					$('#txtAtributo1ValId').val( data.contenido.datos[0].validarAtributo1Id );
				} else {
					$("#divAtributo1Captura").css("display", "none");
				}
			}else{
				$( "#contribuyente" ).val('');
				$( "#contribuyenteID" ).val('');
				$('#selectTipoPeriodo').val('Mes');
				$('#selectTipoPeriodo').multiselect('rebuild');
				$("#divAtributo1Captura").css("display", "none");
				$("#txtAtributo1Val" ).val('');
			}
			
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}

 function fnBusquedaContribuyente() {
    dataObj = { 
            option: 'mostrarContribuyentes'
          };
    $.ajax({
      async:false,
      cache:false,
      method: "POST",
      dataType:"json",
      url: "modelo/componentes_modelo.php",
      data: dataObj
    })
    .done(function( data ) {
        //console.log(data);
        if(data.result) {
			fnBusquedaFiltroFormato(data.contenido.datos);
        }else{
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se Obtuvieron los Contribuyentes</p>');
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}



function fnBusquedaFiltroFormato(jsonData) {
    $( "#contribuyente").autocomplete({
		minLength: 4,
		source: jsonData,
		disabled: true,
        select: function( event, ui ) {
            
            $( this ).val( ui.item.value + "");
			$( "#contribuyente" ).val( ui.item.value );
            $( "#contribuyenteID" ).val( ui.item.texto );
			

            return false;
        }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {

        return $( "<li>" )
        .append( "<a>"+ item.value + "</a>" )
        .appendTo( ul );

	};
	
	$( "#contribuyenteFiltro").autocomplete({
		minLength: 4,
		source: jsonData,
		disabled: true,
        select: function( event, ui ) {
            
            $( this ).val( ui.item.value + "");
			$( "#contribuyenteFiltro" ).val( ui.item.value );
            $( "#contribuyenteIDFiltro" ).val( ui.item.texto );
			

            return false;
        }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {

        return $( "<li>" )
        .append( "<a>" + item.value + "</a>" )
        .appendTo( ul );

    };
}

 function ChangeObjetosParciales(){
	var idConfigContratos = $('#selectContratos').val();
	

	dataObj = {
		option: 'mostrarObjetosParcialesContrato',
		id_configContratos: idConfigContratos
	};
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType: "json",
		url: "modelo/componentes_modelo.php",
		data: dataObj
	}).done(function(data) {
		if (data.result) {
			dataJson = data.contenido.datos;
			var contenido = "";

			for (var info in dataJson) {
				contenido += "<option value='" + dataJson[info].clave + "'>" + dataJson[info].nombre + "</option>";
			}
			$('#selectObjetosParciales').empty();
			$('#selectObjetosParciales').append(contenido);
			$('#selectObjetosParciales').multiselect('rebuild');
		} else {
			console.log("ERROR Modelo");
			console.log( JSON.stringify(data) ); 
		}
	}).fail(function(result) {
		console.log("ERROR");
		console.log( result );
	});
 } 

 function openModalIframe(idContrato, idconfContrato,contribuyente,configuracion){

	var ruta = "abcObjetosParcialesContrato.php?modal=true&id_contratos="+idContrato+"&id_configuracion="+idconfContrato+"&name="+contribuyente;
	var contenido = '<div style="width: 100%; height: 400;"> <iframe className="" src="'+ruta+'" width="100%" height="400" frameBorder="0"></iframe> </div>';
	
	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Detalle de cobro</p></h3>';
	muestraModalGeneral(4, titulo, contenido);
}

function openModalIframeDos(idContrato, idconfContrato, contribuyente){
	console.log(idconfContrato);
	var ruta = "abcPropiedadesAtributos.php?modal=true&id_contratos="+idContrato+"&id_configuracion="+idconfContrato+"&name="+contribuyente+"&conf="+idconfContrato;
	var contenido = '<div style="width: 100%; height: 400;"> <iframe className="" src="'+ruta+'" width="100%" height="400" frameBorder="0"></iframe> </div>';

	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Atributos del contrato</p></h3>';
	muestraModalGeneral(4, titulo, contenido);
}

function fnGenerarAdeudos(id_contratos){	
	var ruta = "infoPanelContratos.php?modal=true&id_contratos="+id_contratos;
	var contenido = '<div style="width: 100%; height: 400;"> <iframe className="" src="'+ruta+'" width="100%" height="400" frameBorder="0"></iframe> </div>';

	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Generar Adeudos</p></h3>';
	muestraModalGeneral(4, titulo, contenido);
    
}

function fnPaseDeCobro(id_contratos,contribuyente){
	   
	var ruta = "paseCobro.php?modal=true&id_contratos="+id_contratos;
	var contenido = '<div style="width: 100%; height: 400;"> <iframe className="" src="'+ruta+'" width="100%" height="400" frameBorder="0"></iframe> </div>';

	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i>Pase de Cobro</p></h3>';
	muestraModalGeneral(4, titulo, contenido);
    
}
function fnTodosPaseDeCobro(id_contratos,contribuyente){
	   
	var ruta = "paseCobroTodos.php?modal=true"; 
	var contenido = '<div style="width: 100%; height: 400;"> <iframe className="" src="'+ruta+'" width="100%" height="400" frameBorder="0"></iframe> </div>';

	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i>Generar Pase de Cobro Todos</p></h3>';
	muestraModalGeneral(4, titulo, contenido);
    
}

function fnHistorial(id_contratos){
	   
	var ruta = "historialPaseCobro.php?modal=true&id_contratos="+id_contratos;
	var contenido = '<div style="width: 100%; height: 400;"> <iframe className="" src="'+ruta+'" width="100%" height="400" frameBorder="0"></iframe> </div>';

	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i>Historial de Pases de Cobro</p></h3>';
	muestraModalGeneral(4, titulo, contenido);
    
}


function modalUnique(name = ""){	
	// var ruta = "infoPanelContratos.php?modal=true&id_contratos="+id_contratos;
	var contenido = '<div style="width: 100%; height: 400;"> <iframe id="modalUnique" className="" src="'+rutaUnique+'" width="100%" height="400" frameBorder="0"></iframe> </div>';
	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i><span id="titleUnique">'+name+'</span></p></h3>';
	muestraModalGeneral(4, titulo, contenido);
}

function reload(modal) {
	switch (modal) {
		case 0:
			rutaUnique = "abcObjetosParcialesContrato.php?modal=true&id_contratos="+idContrato+"&id_configuracion="+idconfContrato+"&name="+contribuyente+"&isUnique=true"; 
			break;
		case 1:
			$('#titleUnique').text('Atributos del Contrato');
			rutaUnique = "abcPropiedadesAtributos.php?modal=true&id_contratos="+idContrato+"&id_configuracion="+idconfContrato+"&name="+contribuyente+"&conf="+configuracion+"&isUnique=true"; 
			break;
		case 2:
			if(idconfContrato == 7){
				$('#titleUnique').text('Pase de Cobro');
				rutaUnique = "paseCobro.php?modal=true&id_contratos="+idContrato+"&name="+contribuyente+"&isUnique=true&hiddenBack=true";
			}else{
				$('#titleUnique').text('Generar Adeudo');
				rutaUnique = "infoPanelContratos.php?modal=true&id_contratos="+idContrato+"&isUnique=true";

			}

			break;
		case 3:
			$('#titleUnique').text('Pase de Cobro');
			rutaUnique = "paseCobro.php?modal=true&id_contratos="+idContrato+"&name="+contribuyente+"&isUnique=true";
			break;
		case 4:
			rutaUnique = "historialPaseCobro.php?modal=true&id_contratos="+idContrato+"&isUnique=true";
			break;
			
		default:
			break;
		}
		console.log('finalContribu',contribuyente);
	$('#modalUnique').attr('src',rutaUnique);
	// $('#modalUnique').contentWindow.location.reload(true);
}

function closeUnique(titulo, msg){
	$('#modalUsoGeneral').modal('hide');
	muestraModalGeneral(3,titulo,msg);

}

function checkPermiss(){
	dataObj = {
		option: 'checkPermissAll',
	};
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType: "json",
		url: "modelo/componentes_modelo.php",
		data: dataObj
	}).done(function(data) {
		if (data.result) {
			dataJson = data.contenido.datos;
			if(dataJson)
				if(dataJson[0].isPermiss){
					$('#allPase').removeClass('hidden');
				}
		} else {
			console.log("ERROR Modelo");
			console.log( JSON.stringify(data) ); 
		}
	}).fail(function(result) {
		console.log("ERROR");
		console.log( result );
	});
	
	
}