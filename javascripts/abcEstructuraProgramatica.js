// asiganacion de eventos
$(document).ready(function() {
    // llamado de funcion inicial
    inicioPanel();
    // comportamiento del boton nuevo
    $('#nuevo').on('click',function(){
        $('#tituloModal').html('Nueva Estructura Programatica');
        $('#modalProgramatica').modal('show');
    });
    // comportamiento del boton de guardado
    $('#guardar').on('click',function(){
        var params = getParams(idForma), campos=getMatchets(idForma), msg=''
            ,nombreCampo={'ur':'UR','fi':'FI','fu':'FU','sf':'SF','rg':'RG','ai':'AI','pp':'PP','aux':'Auxiliar 3'}
            ,esepcion=['rg'], method=params.hasOwnProperty('identificador')?'update':'store';
        $.extend(params,{method:method,'valid':1});
        $.each(campos,function(index, el) {
           if(!params.hasOwnProperty(el)){ return; }
           // if(esepcion.indexOf(el)!==-1){ return; }
           if(params[el]!="0"){ return; }
           msg += 'El campo '+nombreCampo[el]+' no puede ir vacio.<br />';
        });
        if(msg!=''){ muestraModalGeneral(3,'Error de datos',msg); return;}
        $.post(modelo, params).then(function(res){
            var titulo=res.success?'Operación Exitosa':'Error de Datos';
            llenaTabla(res.content);
            muestraModalGeneral(3,titulo,res.msg);
        });
    });
    // comportamiento cambio de finalidad
    $('#fi').on('change',function(){
        if($(this).val() == 0){ return; }
        rootFi = $(this).val();
        $.post(modelo, {method:'obtenFuncionProgramatica',identificador:rootFi}).then(function(res){
            if(!res.succes){ return; }
            $('#fu').multiselect('dataprovider',res.content).multiselect('select',0).trigger('change');
        });
    });
    // comportamiento de elemento funcion
    $('#fu').on('change',function(){
        if($(this).val() == 0){ return; }
        rootFu = $(this).val();
        $.post(modeloComponentes, {option:'muestraSubFuncion',fi:rootFi,fu:rootFu}).then(function(res){
            res = JSON.parse(res);
            if(!res.result){ return; }
            var options=''+baseOption;
            $.each(res.contenido,function(index, el) {
                options += `<option value="${el.val}">${el.text}</option>`;
            });
            $('#sf').html(options).val(0).trigger('change').multiselect('rebuild');
        });
    });
    // comportamiento de eliminacion de registro
    $(document).on('cellselect','#tablaGrid',function(e){
        var index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
        // confirmación de evento a lanzar
        if(campo != 'modificar'){ return false; }
        // declaración de variables secundarias para evitar carga inecesaria
        var row = $(this).jqxGrid('getrowdata', index);
        // se extrae la información de los datos a modificar de base de datos
        $.post(modelo, { method:'edit', identificador: row.identificador })
        .then(function(res){
            // declaración d evariables
            var titulo = 'Error de Datos', $spanContent = $('<span>');
            // comprobacion de exito
            if(res.success){
                titulo = 'Operación Exitosa';
                $.each(res.content, function(index, val) {
                    if(index == 'identificador'){
                        $('#forma').append('<input type="text" name="identificador" id="identificador" value="'+val+'" class="hidden"/>');
                    }else{
                        $('#'+index).multiselect('select', val).trigger('change');
                    }
                });
                setTimeout(function(){
                    var relacion = ['fu','sf'];
                    $.each(relacion, function(index, val) {
                        $('#'+val).multiselect('select',res.content[val]);
                    });
                },500);
                $('#modalProgramatica #tituloModal').html("Edición Estructura Programatica");
                $('#modalProgramatica').modal('show');
            }
        });
    })
    // comportamienteo de eliminacion del registro
    .on('cellselect','#tablaGrid',function(e){
        // declaración de variables
        var index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
        // validación de evento a lanzar
        if(campo != 'eliminar'){ return false; }
        // declaración de variables secundarias para evitar carga inecesaria
        var row = $(this).jqxGrid('getrowdata', index), $spanContent = $('<span>'),
            content = '¿Realmente desea eliminar el elemento <strong>'+row.descripcion+'</strong>?',
            btnElimina = $('<button>',{ class : 'btn btn-primary btn-sm bgc8', html  : 'Aceptar',
                click : function(){
                    $.post(modelo, {method:'destroy', identificador:row.identificador})
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
            console.log(row);
        // agregado de los botones de acción necesarios
        $spanContent.append(btnElimina).append(btnCancel);
        // ejecución de render de Vue en caso de que se tengan componentes
        muestraModalGeneralConfirmacion(3, 'Confirmación', content, $spanContent);
    });
});
/*********************************** FUNCIONES DE EJECUCCIÓN ***********************************/
/**
 * Función de configuración inicial, donde se generarn e inician las variables que seran
 * usadas en el programa
 */
function inicioPanel() {
	// variables globales del sistema
	this.root = window;
    this.rootFi = 0;
    this.rootFu = 0;
	this.url = getUrl();
	this.modelo = this.url+'/modelo/abcEstructuraProgramaticaModelo.php';
	this.modeloComponentes = this.url+'/modelo/componentes_modelo.php';
	this.idForma = 'forma';
	this.btnCancel = $('<button>',{ class:'btn btn-primary btn-sm bgc8',html:'Cancelar','data-dismiss':'modal' });
    this.baseOption = '<option value="0">Seleccione una opcion</option>';
	// funciones principales del sistema
	llenaTabla();
	cargaInicial();
	// colocación de estilos en mensaje de modal
	$('#'+this.idForma).css({ 'max-height':'600px', 'min-height':'200px', 'overflow-y': 'auto' });
    // comportamiento de apertura y sierre de la modal de captura
    $('#modalProgramatica').on('shown.bs.modal',function(){
    }).on('hidden.bs.modal',function(){
        var limpiarSelects = ['fi','fu','sf','rg','ai','pp','aux'];
        $.each(limpiarSelects,function(index,el){ $('#'+el).multiselect('select',0).multiselect('rebuild'); });
        $('#modalProgramatica').find('#identificador').remove();
    });
	// mensaje de confirmación de inicio
	console.log('listo el panel estructura programatica');
}
/**
 * Funcion para el llenado de la tabla prinsipal con los datos
 * enviados como parametro
 * @param  {Array} data Contenido que sera cargado en la tabla
 */
function llenaTabla(data) {
	// declaración de variables prinsipales
	var data = data||[], el = 'contenedorTabla', tabla = 'tablaGrid', nameExcel = 'Estructura Programatica'
        , tblObj, tblTitulo, tblExcel=[0,1,2,3,4,5,6,7], tblVisual=[0,1,2,3,4,5,6,7,8];//,9
    tblObj = [
        { name: 'ur', type: 'string'},// 0
        { name: 'fi', type: 'string'},// 1
        { name: 'fu', type: 'string'},// 2
        { name: 'sf', type: 'string'},// 3
        { name: 'rg', type: 'string'},// 4
        { name: 'ai', type: 'string'},// 5
        { name: 'pp', type: 'string'},// 6
        { name: 'aux', type: 'string'},// 7
        { name: 'descripcion', type: 'string'},// 7
        //{ name: 'modificar', type: 'string'},// 8
        { name: 'eliminar', type: 'string'},// 9
        { name: 'identificador', type: 'string'},// 10
    ];
    tblTitulo = [
        { text: 'UR', datafield: 'ur', editable: false, width: '13%', cellsalign: 'center', align: 'center' },// 0
        { text: 'FI', datafield: 'fi', editable: false, width: '13%', cellsalign: 'center', align: 'center' },// 1
        { text: 'FU', datafield: 'fu', editable: false, width: '13%', cellsalign: 'center', align: 'center' },// 2
        { text: 'SF', datafield: 'sf', editable: false, width: '13%', cellsalign: 'center', align: 'center' },// 3
        { text: 'RG', datafield: 'rg', editable: false, width: '13%', cellsalign: 'center', align: 'center' },// 4
        { text: 'AI', datafield: 'ai', editable: false, width: '13%', cellsalign: 'center', align: 'center' },// 5
        { text: 'PP', datafield: 'pp', editable: false, width: '13%', cellsalign: 'center', align: 'center' },// 6
        { text: 'Auxiliar 3', datafield: 'aux', editable: false, width: '13%', cellsalign: 'center', align: 'center' },// 7
        //{ text: 'Modificar', datafield: 'modificar', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 8
        { text: 'Eliminar', datafield: 'eliminar', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 9
    ];
	// llamado de limpiesa de la tabla
	fnLimpiarTabla(el,tabla);
	// render de la tabla
	fnAgregarGrid_Detalle_nostring(data, tblObj, tblTitulo, tabla, ' ', 1, tblExcel, false, true, "", tblVisual, nameExcel);
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
	// declaración de variables prinsipales
	var url = this.location.href.split('/');
    url.splice(url.length - 1);
	// retorno de información
    return url.join('/');
}
