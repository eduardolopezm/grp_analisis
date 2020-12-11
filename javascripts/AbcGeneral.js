(function($){
	// adjudicación de eventos
	$(document).ready(function() {
		// se dispara el inicio y se realiza la declaración de las variables para el programa
		inicioPanel();
		/************ VARIABLES DEL PROGRAMA ************/
			var $btnAdd = $('#btnAgregar');
		/************ VARIABLES DEL PROGRAMA ************/
		// comportamiento de botón agregar llamando la función de muestra de modal
		$btnAdd.on('click',llamaModalAdd);
		// eventos sobre el documento con sub querys
		// comportamiento de la modificación y eliminación de los elementos del grid
		$(this).on('cellselect','#tablaGrid',modificaEvent)// evento modificación
				.on('cellselect','#tablaGrid',eliminaEvent);// evento eliminación
	});
	/*********************************** FUNCIONES DE EJECUCCIÓN ***********************************/
	/**
	 * Función para cargar el modal con los datos a modificar, según la configuración que se cargó
	 * en base de datos
	 * @param	{object}	Objeto con la información del evento "click" lanzado por el grid
	 */
	function modificaEvent(e) {
		// declaración de variables principales
		var index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
		// confirmación de evento a lanzar
		if(campo != 'modificar'){ return false; }
		// declaración de variables secundarias para evitar carga inecesaria
		var row = $(this).jqxGrid('getrowdata', index);
		// se extrae la información de los datos a modificar de base de datos
		$.post(modelo, { method:'edit', urlSave:urlSave, identificador: row.identificador, multiidentificadorcampo: row.multiidentificadorcampo, multiidentificadorvalor: row.multiidentificadorvalor })
		.then(function(res){
			// declaración de variables
			var titulo = 'Error de Datos', msg = res.msg, $spanContent = $('<span>'),
				$btnUpdate = $('<button>',{ class: 'btn btn-primary btn-sm bgc8', html: 'Guardar', click: saveData });
			// comprobación de éxito
			if(res.success){
				titulo = 'Modificar '+window.title;
				window.dataModificar = res.content;
				msg = generaFormulario(dataModificar);
				$spanContent.append($btnUpdate);
			}
			// agregado de botones conforme al éxito de la consulta
			$spanContent.append(btnCancel);
			// envío de formulario al usuario
			muestraModalGeneral(3,titulo,msg,$spanContent);
			// ejecución de render de Vue en caso de que se tengan componentes
			aplicacionTimer(true);
		});
	}
	/**
	 * Función para cargar el modal de eliminación del elemento seleccionado
	 if(Object.keys(hijosDelSelect).length){
				$.each(hijosDelSelect, function(index){
					fnReconstruyeSelectsInferiores(index);
				});
			}
	 * @param	{object}	Objeto con la información del evento "click" lanzado por el grid
	 */
	function eliminaEvent(e) {
		// declaración de variables
		var index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
		// validación de evento a lanzar
		if(campo != 'eliminar'){ return false; }
		// declaración de variables secundarias para evitar carga inecesaria
		var row = $(this).jqxGrid('getrowdata', index), $spanContent = $('<span>'),
			content = '¿Realmente desea eliminar el elemento <strong>'+row.descripcion+'</strong>?',
			btnElimina = $('<button>',{ class: 'btn btn-primary btn-sm bgc8', html: 'Aceptar',
				click : function(){
					$.post(modelo, {method:'destroy', urlSave:urlSave, identificador:row.identificador, multiidentificadorcampo: row.multiidentificadorcampo, multiidentificadorvalor: row.multiidentificadorvalor})
					.then(function(res){
						var titulo = 'Error de Datos';
						if(res.success){
							titulo = 'Operación Exitosa';
							llenaTabla(res.content);
						}
						muestraModalGeneral(3, titulo, res.msg);
					});
				}
			 });
		// agregado de los botones de acción necesarios
		$spanContent.append(btnElimina).append(btnCancel);
		// ejecución de render de Vue en caso de que se tengan componentes
		muestraModalGeneralConfirmacion(3, 'Confirmación', content, $spanContent);
	}
	/**
	 * Función para la generación del modal con formulario de catpura de los elementos o ítems
	 * según la configuración colocada en base de datos
	 */
	function llamaModalAdd() {
		// declaración de variables principales
		var titulo = 'Agregar '+ window.title, forma = generaFormulario(), $spanContent = $('<span>'),
			btnSave = $('<button>',{ class: 'btn btn-primary btn-sm bgc8', html: 'Guardar', click: saveData });
		// agregado de los botones de acción necesarios
		$spanContent.append(btnSave).append(btnCancel);
		// se muestra el modal con la información del formulario
		muestraModalGeneral(3, titulo, forma, $spanContent);
		// ejecución de render de Vue en caso de que se tengan componentes
		aplicacionTimer();
	}
	/**
	 * Función para la generación del formulario conforme a la estructura indicada en base de Datos
	 * generando unicamente la estructura html con los componentes necesarios.
	 * @param	{Array} 	[data=[]] Datos que porsteriormente seran usados para la modificaión
	 * 						de los elementos según su configuración
	 * @return	{String}	Formulario en formato html.
	 */
	function generaFormulario(data) {
		// declaración de variables principales
		var data = data||[], $forma = $('<div>',{ class: 'form-horizontal' });
		// iteración sobre los elementos de la confirmación
		$.each(forForm, function(index, val) {
			// variables de entorno each
			if(val.tipo.toLowerCase()=="display"){
				return;
			}
			var	tag = obtenTag(val.tipo),
				valor = ' value="'+obtenValorItem(index, val.tipo, data)+'"',
				hidden = ( index=='identificador'||index=="multiidentificadorcampo"||index=="multiidentificadorvalor" ? 'hidden' : '' ),
				dataAttr = generaDataAttr(val);
			if(index=="multiidentificadorcampo"&&obtenValorItem(index, val.tipo, data)==""){
				valor = ' value="'+IdentificadoresMultiples+'"';
			}
			var componente = '<'+tag+' label="'+( !!Labels[index] ? Labels[index] : capitalize(index) )+':" id="'+index+'" name="'+index+'" '+valor+' '+dataAttr+' class="'+hidden+'"'+( Object.keys(data).length&&!!selectsLlave[index] ? " readonly" : "" )+'></'+tag+'>';
			// agregado del elemento al contenedor principal
			if(val.tipo=="select"){
				componente = ''+
				'<div class="form-inline row">\n'+
				'\t<div class="col-md-3 col-xs-12">\n'+
				'\t\t<label>'+( Labels[index] ? Labels[index] : capitalize(index) )+':</label>\n'+
				'\t</div>\n'+
				'\t<div class="col-md-9 col-xs-12">\n'+
				'\t\t<select id="'+index+'" name="'+index+'" class="form-control '+index+'"'+( index in hijosDelSelect ? ' onChange="fnReconstruyeSelectsInferiores(\''+index+'\');"' : "" )+'>\n'+
				'\t\t\t<option value="">Seleccionar...</option>\n'+
				fnCargaOpcionesSelect(index)+
				'\t\t</select>\n'+
				'\t</div>\n'+
				'</div>\n';
			}
			if(val.tipo=="porcentaje"){
				componente = ''+
				'<div class="form-inline row">\n'+
				'\t<div class="col-md-3 col-xs-12">\n'+
				'\t\t<label>'+( Labels[index] ? Labels[index] : capitalize(index) )+':</label>\n'+
				'\t</div>\n'+
				'\t<div class="col-md-9 col-xs-12">\n'+
				'\t\t<input type="text" id="'+index+'" name="'+index+'" placeholder="" title="" onkeyup="" onkeypress="return fnsoloDecimalesGeneral(event, this)" maxlength="100" onpaste="return false" class="form-control porcentaje '+index+'" autocomplete="off" style="width: 100%;"'+valor+'>\n'+
				'\t</div>\n'+
				'</div>\n';	
			}
			//console.log('Tipo ='+val.tipo+' Componente '+componente);
			$forma.append(componente+'<br class="'+hidden+'">\n');
			if(val.tipo=="select"){
				setTimeout(function(){ fnFormatoSelectGeneral("."+index); }, 500);
			}
		});
		// retorno de los datos obtenidos
		return $forma;
	}
	/**
	 * Función que genera el guardado de la información en base de datos corespodiente al elemento seleccionado
	 * @return	{[type]}	[description]
	 */
	function saveData(){
		// precarga de multiIdentificadorValor
		if(getParams(idForma).identificador==""){
			if(!(getParams(idForma).multiidentificadorcampo===undefined)){
				var multiIdentificadorCampo = getParams(idForma).multiidentificadorcampo.split("<=>"),
					forFormInverso = [],
					multiIdentificadorValor = [];
				$.each(forForm, function(index,val){
					if(forFormInverso[val.col]===undefined){
						forFormInverso[val.col] = index;
					}
				});
				$.each(multiIdentificadorCampo, function(index,val){
					if(val!=""){
						multiIdentificadorValor.push($("#"+forFormInverso[multiIdentificadorCampo[index]]).val());
					}
				});
				if(multiIdentificadorValor.length){
					$("#multiidentificadorvalor").val(multiIdentificadorValor.join("<=>"));
				}
			}
		}
		// declaración de variables principales
		var params = getParams(idForma), campos = getMatchets(idForma), msg = '';
		// se elimina el campo que no es necesario evaluar
		campos.splice(campos.indexOf('identificador'));
		// comprobación de elementos nulos o vacíos
		$.each(campos, function(index, val) {
			if($.isEmptyObject(params[val])){
				msg += 'El campo <strong>'+( Labels[val] ? Labels[val] : capitalize(val) )+'</strong> no puede ir vacío. <br />';
			}
		});
		// envio de error al usuario en caso de tenerlo
		if(msg.length != 0){
			muestraModalGeneral(3,'Error de Datos',msg);
			return;
		}
		// agregado de la información extra
		params.method = params.identificador==''?'store':'update';
		params.urlSave = urlSave;
		// envío a consolidación de la información en base de datos
		$.post(modelo, params).then(function(res){
			var titulo = 'Error de Datos';
			if(res.success){
				titulo = 'Operación Exitosa';
				// llenado de la tabla con la nueva información
				llenaTabla(res.content);
			}
			// envío de confirmación al usuario
			muestraModalGeneral(3,titulo, res.msg);
		});
	}
	/**
	 * Función de configuración inicial, donde se generarn e inician las variables que seran
	 * usadas en el programa
	 */
	function inicioPanel() {
		// variables globales del sistema
		this.root = window;
		this.url = getUrl();
		this.modelo = this.url+'/modelo/AbcGeneralModelo.php';
		this.idForma = 'ModalGeneral_Mensaje';
		this.btnCancel = $('<button>',{ class:'btn btn-primary btn-sm bgc8',html:'Cancelar','data-dismiss':'modal' });
		this.forForm = [];
		this.Labels = [];
		this.IdentificadoresMultiples = "";
		this.valoresSelect = [];
		this.padresDelSelect = [];
		this.hijosDelSelect = [];
		this.selectsLlave = [];
		this.root.rootSelect = [];
		this.tagsforForm = {
			'string' : 'component-text-label',
			'number' : 'component-number-label',
			'decimal' : 'component-decimales-label',
			'select' : 'component-select-label'
		};
		// funciones principales del sistema
		llenaTabla();
		cargaInicial();
		// colocación de estilos en mensaje de modal
		//$('#'+this.idForma).css({ 'max-height':'600px', 'min-height':'200px' });
		// mensaje de confirmación de inicio
		console.log('listo el panel '+title);
	}
	/**
	 * Función de búsqueda de datos inicial
	 */
	function cargaInicial() {
		// solicitud al servidor de la información para la configuración
		$.post(this.modelo, {method:'show',urlSave:this.urlSave})
		.then(function(res){
			forForm = res.forForm;
			Labels = res.Labels;
			IdentificadoresMultiples = res.IdentificadoresMultiples;
			valoresSelect = res.valoresSelect;
			padresDelSelect = res.padresDelSelect;
			hijosDelSelect = res.hijosDelSelect;
			selectsLlave = res.IMS;
			llenaTabla(res.content);
		});
	}

	/************************* HELPERS *************************/
	/**
	 * Función para el llenado y render de la tabla con las funcionalidad
	 * de jqxGrid conforme los datos proporcionados
	 * @param	{Array} data Arreglo con la información para llenar la tabla segun la estructura de base de datos
	 */
	/**
	 * Función puente para la plicacion de vue
	 * @return {[type]} [description]
	 */
	function aplicacionTimer(edit){
		var timeExe = 200, edit=edit||false;
		setTimeout(function(){ fnEjecutarVueGeneral(idForma); }, timeExe);
		setTimeout(function(){
			iniciaSelect(idForma);
			if(edit){ setTimeout(function(){ colocaValorSelect(); }, timeExe*3); }
		}, timeExe*2);
	}
	/**
	 * Función para el llenado de la tabla principal con los datos
	 * enviados como parametro
	 * @param	{Array} data Contenido que sera cargado en la tabla
	 */
	function llenaTabla(data) {
		// declaración de variables principales
		var data = data||[], el = 'contenedorTabla', tabla = 'tablaGrid', nameExcel = this.title;
		// llamado de limpieza de la tabla
		fnLimpiarTabla(el,tabla);
		// render de la tabla
		fnAgregarGrid_Detalle_nostring(data, this.tblObj, this.tblTitulo, tabla, ' ', 1, this.tblExcel, false, true, "", this.tblVisual, nameExcel);
	}
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
	/**
	 * Función de conversión de string dado
	 * @param	{String} str Cadena de texto que sera afectada
	 * @return {String}	 Cadena de text formateada
	 */
	function capitalize(str) {
		return str[0].toUpperCase()+str.slice(1);
	}
	/**
	 * Función para la obtención de tipos de eiquetas que se generaran
	 * @param	{String} tipo Cadena de texto con el tipo a obtener
	 * @return {String}		Cadena de text contenedora de la etiqueta sin atributos
	 */
	function obtenTag(tipo){
		if(tipo == ''){ return 'component-text-label'; }
		return tagsforForm[tipo];
	}
	/**
	 * Función para la generación de atributos data en los objetos
	 * @param	{String} prefix prefijo que sera aplicado al data atribite {data-prefix="content"}
	 * @param	{Object} data	objeto contenedor de estructura
	 * @return {String} atribitos a cargar
	 */
	function generaDataAttr() {
		var data=arguments[0],prefix=(arguments.length==2?arguments[1]:'row')
			,attr = (arguments.length==3?arguments[2]:'');
		if(typeof data === 'string' || data.tipo != 'select'){ return attr; }
		attr += ` ${prefix}="${data.row}" `;
		return attr;
	}
	/**
	 * Función para la obtencion del valor y asignación al entorno global
	 * @return {String||Number} Dato resultado de la busqueda
	 */
	function obtenValorItem() {
		if(confirmLeng(arguments, 3)){ error('Numero de parametros incorrecto'); }
		var index=arguments[0], data = arguments[2];
		// agregado al entorno global
		if(arguments[1]=='select'){ this.root.rootSelect[index] = data[index]; }
			// JX console.log(arguments[1]+', index='+index+' data='+data[index]);
		return (data[index]||'');
	}
	/**
	 * Función para la ejecucion de las llamadas y llemado automatico de los
	 * elementos select dentro de un formulario determinado
	 * @param	{String} forma	Identificador del contennedor de los elementos
	 */
	function iniciaSelect() {
		var forma = arguments[0], def = {urlSave:this.urlSave};
		if(typeof forma === 'undefine'){ return false; }
		$('#'+forma).find('select[id]').each(function(index, el) {
			var atrs = $(this).attr(); $.extend(atrs, def);
			//// Se eliminó porque sobreescribía los combos que existen actualmente
			////aplicaSelectVue('#'+atrs.id,modelo,atrs);
		});
	}
	/**
	 * Función para la colocación de los valores por defecto del
	 * elemento select proporcionado.
	 * @param	{String} Elemento Elemento select que se aplicara el valor
	 * @param	{String||Array} data Valor que sera colocado por defecto según la libreria multiselect
	 * @return {[type]} [description]
	 */
	function colocaValorSelect() {
		var data=this.rootSelect;
		// if(confirmLeng(arguments, 2)){ error('Numero de argumentos incorrecto'); }
		// $(arguments[el]).multiselect('select',arguments[index]);
		for (var index in data) {
			if(Object.keys(hijosDelSelect).length){
				$.each(hijosDelSelect, function(index){
					fnReconstruyeSelectsInferiores(index);
				});
			}
			if (!data.hasOwnProperty(index)){ return false; }
			$('#'+index).multiselect('select',data[index]);
			if(!!selectsLlave[index]){
				$('#'+index).multiselect('disable');
			}
		}
	}
	/**
	 * Función de error en consola con mensaje dado;
	 * @param	{String} msg Mensaje que sera enviado a la consola
	 */
	function error(msg) { throw new Error( msg ); }
	/**
	 * Función para la comparación de tamaños
	 * @param	{Array} arrs		 Arreglo a comparar
	 * @param	{String} len		 longitud
	 * @param	{String} comparison 	Elemento de comparación a usar
	 * @return {Boolean}			Retorna el resultado de la comparación
	 */
	function confirmLeng(arrg, len, comparison='<') {
		var nombre=arrg.callee.toString().match(/function ([^\(]+)/)[1];
		if($.isEmptyObject(arrg)){ error('El primer parametro tiene que ser un arreglo '+nombre); }
		if(typeof len === 'string'){ error('La longitud a comparar tiene que ser numerico '+nombre); }
		return eval(arrg.length+comparison+len);
	}
	/**
	 * [fnCargaOpcionesSelect description]
	 * @param	{Array}		elemento	El nombre del elemento del que se cargará el select
	 * @return	{String}				Las opciones del elemento en cuestión
	 */
	function fnCargaOpcionesSelect(elemento){
		var opcionesSelect = "";

		if( !(elemento in padresDelSelect) ){
			$.each(valoresSelect[elemento], function(index, val) {
				opcionesSelect += '\t\t\t<option value="'+val.value+'">'+val.label+'</option>\n';
			});
		}

		return opcionesSelect;
	}
})(jQuery);
// sobre escritura del metodo attr de jquery
!function(t){$.fn.attr=function(){if(0===arguments.length){if(0===this.length)return null;var n={};return $.each(this[0].attributes,function(){this.specified&&(n[this.name]=this.value)}),n}return t.apply(this,arguments)}}($.fn.attr);

function fnCargaOpcionesSelectMultinivel(elementoPadre,elementoHijo){
	if(elementoHijo in padresDelSelect){
		$.each(padresDelSelect[elementoHijo], function (index,val) {
			if(elementoHijo in valoresSelect){
				$.each(valoresSelect[elementoHijo], function(indexSel,valSelect){
					if(valSelect.padresSelectCampo0==elementoPadre&&valSelect.padresSelectValor0==$("#"+val).val()){
						$("#"+elementoHijo).append($("<option>",{value: valSelect.value,text: valSelect.label}));
                    }
				});
			}
		});
	}
}
function fnReconstruyeSelectsInferiores(elemento){
	if(elemento in hijosDelSelect){
		$.each(hijosDelSelect[elemento], function(index, val) {
			$("#"+val).empty();
			$("#"+val).append($("<option>",{value:"",text:"Seleccionar..."}));
			fnCargaOpcionesSelectMultinivel(elemento,val);
			$("#"+val).multiselect('rebuild');
		});
	}
}
