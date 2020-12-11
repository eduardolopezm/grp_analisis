
var url                 = "modelo/configuracionFirmas_V_Modelo.php";
var id                  = id;
var Advertencia         = false;
var idFirmante          ='';
var globalUe            = '';
var globlalLn_reporte   = '';

/* variables globales fin */
$(document).ready(function(){
   
    
    $('#selectUnidadEjecutora').on('change', function(){
      fnCrearTabla();  
    });

    if(funcionVer == "1"){
        $("#firmante").hide()
        $('#guardarProd').hide();
        $('#eliminar').hide();
        $('#cancelar').hide();
        fnDesabilitarCampos();
    }
    if ( idFirma != ''){
        fnMostrarDatos(idFirma);
    }else{
        fnCrearTabla(); 
    }
    $('#guardarProd').on('click', function(){
        fnSaveFirmas();
    });

    $( "#UnidadEjecutora" ).on('change',function() {
        obtenerEmpleados(); 
      });

    $( ".selectUsuarioFirma" ).change(function() {
        obtenerPuesto(); 
    });

    setTimeout(function(){$(".multiselect-container").width("300px");}, 1500);
   
});

$(document).on('click','#cancelar',function(){
    $("#btnCerrarModalGeneral").removeClass('cerrarModalCancelar');
    location.replace("./ClasificacionProgramatica.php?");
});

$(document).on('click','#eliminar',function(){
    $("#btnCerrarModalGeneral").addClass('cerrarModalCancelar');
});

function fnDesabilitarCampos(){
    
    $('#selectUnidadNegocio').multiselect('disable');
    $('#selectUnidadEjecutora').multiselect('disable');
}

function fnClave(){
    dataObj = { 
        option: 'mostrarClave'
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: url,
        data: dataObj
    })
    .done(function( data ) {
        if(data.result) {
            dataMbflag = data.contenido.datos;
            
            var    mbflagNew   =  ''; 
            for (var info in dataMbflag) {
                mbValue     = dataMbflag[info].value;
                mbTexto     = dataMbflag[info].texto;
               
            }
            
            $('#clave').val(mbTexto);
          
        }
    })
    .fail(function(result) {
        
    });
}
function fnPrograma(){

    dataObj = { 
        option: 'mostrarPrograma'
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: url,
        data: dataObj
    })
    .done(function( data ) {
        if(data.result) {
            dataMbflag = data.contenido.datos;
             
            var    mbflagNew   =  ''; 
            for (var info in dataMbflag) {
                mbValue     = dataMbflag[info].value;
                mbTexto     = dataMbflag[info].texto;
                mbflagNew   += "<option value="+mbValue+">"+mbTexto+"</option>";
            }
            
            $('#programa').empty();
            $('#programa').append("<option value='0'>Sin Selección ...</option>" + mbflagNew);
            $('#programa').multiselect('rebuild');
        }
    })
    .fail(function(result) {
        
    })
}


function fnValidarFirmanteRepetido(unidadNegocio, unidadEjecutora,selectUsuario){
    var firmanteRepetido = false; 
    dataObj = { 
        option: 'validarFirmanteRepetido',
        unidadNegocio: unidadNegocio,
        unidadEjecutora:unidadEjecutora, 
        selectUsuario:selectUsuario,
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: url,
        data: dataObj
    })
    .done(function( data ) {
        if(data.result) {
            $('#divMensajeOperacion').removeClass('hide');
            $('#ModalUR').modal('hide');
            var textUsuario =  $('#selectUsuario option:selected').html();
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            msg = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>El Usuario '+textUsuario+' ya esta registrado como firmante</p>';
            muestraModalGeneral(3, titulo, msg);
            firmanteRepetido = true;
        }else{
             
        }
    })
    .fail(function(result) {
    });
    return firmanteRepetido; 
}


function fnValidarFirmante (unidadNegocio, unidadEjecutora,selectUsuario,selectPuesto,titulo){
    var msg     = "";
    var validar = false;
    if (unidadNegocio =="-1"){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar UR.</p>';
    }
    if (unidadEjecutora =="-1"){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar UE.</p>';
    }
    if (selectUsuario ==0){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar Usuario.</p>';
    }
    if (selectPuesto ==0){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar el Puesto.</p>';
    }
    if (titulo.trim() ==''){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar Titulo.</p>';
    }

    if(msg == ''){
        validar = true;
   
    }else{
        $('#divMensajeOperacion').removeClass('hide');
        $('#ModalUR').modal('hide');

        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, msg);
    }
    return validar;

}
function fnValidarDatos(){
   
    var selectUnidadNegocio   = $('#selectUnidadNegocio').val();
    var selectUnidadEjecutora = $('#selectUnidadEjecutora').val();
    var selectReportes        = $("#selectReportes").val();

    var msg         = ""; 
    var datos       = [];
    var arrayFirmantes = []; 
    var selections = getSelects('divContenidoTabla','agregados');
    var count      = 0;
    $.each(selections, function(ind, row) { 
        arrayFirmantes[count]= row.idRow; 
        count++; 
    });

    var totalFirmas = arrayFirmantes.length; 

    if (selectUnidadNegocio ==''){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar UR.</p>';
    }

   
    if(selectUnidadEjecutora == "-1"){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar el UE.</p>';
    }

    if(selectReportes == 0){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar el reporte.</p>';
    }

    
    if  (totalFirmas == 0){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta seleccionar firmantes en el reporte.</p>';
    }
    if(msg == ''){
        validar = true;
    var selectUnidadEjecutora = $('#selectUnidadEjecutora').val();
        datos = {'selectUnidadNegocio':selectUnidadNegocio,'selectUnidadEjecutora': selectUnidadEjecutora,'selectReportes':selectReportes,'firmas':arrayFirmantes, 'totalFirmas':totalFirmas}
    }else{
        
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, msg);
    }
    return datos;
}


function fnSaveWithWarning(){
    Advertencia = false;
    fnSaveFirmas();
}

function fnSaveFirmas(){
    var arrayFirmas = fnValidarDatos(); 
    
    if(idFirma == ''){
        if(arrayFirmas != ''){
            dataObj = { 
                option: 'guardarFirmaReporte',
                arrayFirmas: arrayFirmas
            };
            $.ajax({
              async:false,
              cache:false,
              method: "POST",
              dataType:"json",
              url: url,
              data: dataObj
            })
            .done(function( data ) {

                if(data.result) {
                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                    muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-success" aria-hidden="true"></i> Se agregaron los firmantes al reporte</p>');
                }else{
                    $("#btnCerrarModalGeneral").removeClass('cerrarModalCancelar');
                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                    muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Ya existen firmantes en este reporte.</p>');
                }
            })
            .fail(function(result) {
                
            });
        }
    }else{
        if(arrayFirmas != ''){
            fnModificar(arrayFirmas);
        }
    }
}

function fnModificar(arrayFirmas){
    
    dataObj = { 
        option: 'modificarFirmantes',
        arrayFirmas: arrayFirmas,
        idFirma:idFirma
    };
    $.ajax({
        method: "POST",
        dataType:"json",
        url: url,
        data:dataObj
    })
    .done(function( data ) {
        if(data.result){
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-danger" aria-hidden="true"></i> Se actualizaron los firmantes al reporte </p>');
        }
        else{
            if (data.contenido = "presupuestario"){
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-danger" aria-hidden="true"></i> No se pudieron actualizar los registros </p>');
            
            }
        }
    })
    .fail(function(result) {
    });
}

function fnMostrarDatos(idFirma){
   
    fnObtenerDatos(idFirma);
   
}

function fnPartidaEspecifica(mbflag){
    dataObj = { 
            option: 'mostrarPartida',
            mbflag: mbflag
    };
    $.ajax({
          async:false,
          cache:false,
          method: "POST",
          dataType:"json",
          url: url,
          data: dataObj
    })
    .done(function( data ) {
        if(data.result) {
            datosPartidaEsp = data.contenido.datos;

            var partidaID = "";
            var partidaDesc = "";
            var partidaNew = "";

            for (var info in datosPartidaEsp) {
                partidaID = datosPartidaEsp[info].value;
                partidaDesc = datosPartidaEsp[info].texto;
                partidaNew += "<option value="+partidaID+">"+partidaDesc+"</option>";
            }
            $('#selectPartidaEspecifica').empty();
            $('#selectPartidaEspecifica').append("<option value='0'>Sin Selección ...</option>" + partidaNew);
            $('#selectPartidaEspecifica').multiselect('rebuild');
            fnCabms(0, mbflag)
        }
    })
    .fail(function(result) {
    });
}

function fnCabms(partidaid, mbflag){
    dataObj = { 
            option: 'mostrarCabms',
            partidaid: partidaid,
            mbflag: mbflag
    };
    $.ajax({
          async:false,
          cache:false,
          method: "POST",
          dataType:"json",
          url: url,
          data: dataObj
    })
    .done(function( data ) {
        if(data.result) {
            datosCabms = data.contenido.datos;
            var cabmsID = "";
            var cabmsDesc = "";
            var cabmsNew = "";

            for (var info in datosCabms) {
                cabmsID = datosCabms[info].value;
                cabmsDesc = datosCabms[info].texto;
                cabmsNew += "<option value="+cabmsID+">"+cabmsDesc+"</option>";
            }
            $('#selectCabms').empty();
            $('#selectCabms').append("<option value='0'>Sin Selección ...</option>" + cabmsNew);
            $('#selectCabms').multiselect('rebuild');
        }
    })
    .fail(function(result) {
    });
}

function fnUnits(mbflag){
    dataObj = { 
            option: 'mostrarUnits',
            mbflag: mbflag
    };
    $.ajax({
          async:false,
          cache:false,
          method: "POST",
          dataType:"json",
          url: url,
          data: dataObj
    })
    .done(function( data ) {
        if(data.result) {
            datosUnits = data.contenido.datos;

            var unitsID = "";
            var unitsDesc = "";
            var unitsNew = "";

            for (var info in datosUnits) {
                unitsID = datosUnits[info].value;
                unitsDesc = datosUnits[info].texto;
                unitsNew += "<option value='"+unitsDesc+"'>"+unitsDesc+"</option>";
            }
            $('#units').empty();
            $('#units').append("<option value='0'>Sin Selección...</option>" + unitsNew);
            $('#units').multiselect('rebuild');
        }
    })
    .fail(function(result) {
    });
}

function fnObtenerDatos(idFirma){
    var datosFirma = [];

    dataObj = { 
            option: 'obtenerDatos',
            idFirma: idFirma
    };
    $.ajax({
          async:false,
          cache:false,
          method: "POST",
          dataType:"json",
          url: url,
          data: dataObj
    })
    .done(function( data ) {
        if(data.result) {
            datosFirma = data.contenido.datos;

            var ur             = "";
            var ue             = "";
            var ln_reporte     = "";

            for (var info in datosFirma) {
                ur             = datosFirma[info].ur;
                ue             = datosFirma[info].ue;
                ln_reporte     = datosFirma[info].ln_reporte;
            } 
            globlalLn_reporte   = ln_reporte; 
            globalUe            = ue;
            $('#selectUnidadNegocio > option[value="'+ur+'"]').attr('selected', 'selected');
            $('#selectUnidadNegocio').multiselect('rebuild');
            $("#selectUnidadNegocio").multiselect('disable'); 

            if(funcionVer == "1"){
                fnCreartablaVista (ue, ln_reporte); 
            }else{
                
                fnCreartablaModificar (ue, ln_reporte); 
            }
        }
    })
    .fail(function(result) {
    });
}


function obtenerEmpleados(){
    var datos = [];

    dataObj = { 
            option: 'obtenerEmpleados',
            ur: $("#UnidadNegocio").val(),
            ue: $("#UnidadEjecutora").val()
    };
    $.ajax({
          async:false,
          cache:false,
          method: "POST",
          dataType:"json",
          url: url,
          data: dataObj
    })
    .done(function( data ) {
        if(data.result) {
            datos = data.contenido.datos;

            var id_nu_empleado      = "";
            var ln_nombre           = "";
            var sn_primer_apellido  = "";
            var sn_segundo_apellido = "";
            var id_nu_puesto      = ''; 
            var mbflagNew           =  ''; 

            for (var info in datos) {
                id_nu_empleado         = datos[info].id_nu_empleado;
                ln_nombre              = datos[info].ln_nombre;
                sn_primer_apellido     = datos[info].sn_primer_apellido;
                sn_segundo_apellido    = datos[info].sn_segundo_apellido;
                id_nu_puesto           = datos[info].id_nu_puesto;

                mbflagNew   += "<option value="+id_nu_empleado+"_"+id_nu_puesto+">"+ln_nombre+" "+sn_primer_apellido+" "+sn_segundo_apellido+"</option>";
                
            }

            
            $('#selectUsuario').empty();
            $('#selectUsuario').append("<option value='0'>Sin Selección ...</option>" + mbflagNew);
            $('#selectUsuario').multiselect('rebuild');
         
        }
    })
    .fail(function(result) {
    });
}

function obtenerPuesto(){
    var datos = [];

    dataObj = { 
            option: 'obtenerPuesto',
            usuario: $("#selectUsuario").val()
    };
    $.ajax({
          async:false,
          cache:false,
          method: "POST",
          dataType:"json",
          url: url,
          data: dataObj
    })
    .done(function( data ) {
        if(data.result) {
            datos = data.contenido.datos;

            var id_nu_puesto      = "";
            var ln_descripcion           = "";
            var mbflagNew           =  ''; 

            for (var info in datos) {
                id_nu_puesto     = datos[info].id_nu_puesto;
                ln_descripcion = datos[info].ln_descripcion;


                mbflagNew   += "<option value="+id_nu_puesto+" selected >"+ln_descripcion+"</option>";
                
            }  
            $('#selectPuesto').empty();
            $('#selectPuesto').append(mbflagNew);
            $('#selectPuesto').append("<option value='0'>Sin Selección ...</option>" + mbflagNew);
            $('#selectPuesto').multiselect('rebuild');
         
        }
    })
    .fail(function(result) {
    });

}
function mayus(e) {

    e.value = e.value.toUpperCase();
}

function fnAgregarCatalogoModal(){
    var selectReset   = 0; 
    var valUR         = $("#selectUnidadNegocio").val(); 
    var valUE         = $('#selectUnidadEjecutora').val();

    $('#divMensajeOperacion').addClass('hide');
    
    $('#UnidadNegocio').multiselect('enable');
    $('#selectUsuario').multiselect('enable');
    $('#UnidadEjecutora').multiselect('enable');
    $('#selectPuesto').multiselect('enable');

    fnTraeUnidadesEjecutoras(valUR, 'UnidadEjecutora')

    $('#UnidadNegocio > option[value="'+valUR+'"]').attr('selected', 'selected');
    $('#UnidadNegocio').multiselect('rebuild');

    $('#UnidadEjecutora > option[value="'+valUE+'"]').attr('selected', 'selected');
    $('#UnidadEjecutora').multiselect('rebuild');


    $('#selectUsuario > option[value="'+selectReset+'"]').attr('selected', 'selected');
    $('#selectUsuario').multiselect('rebuild');

    $('#selectPuesto > option[value="'+selectReset+'"]').attr('selected', 'selected');
    $('#selectPuesto').multiselect('rebuild');
    if( valUE != -1){
        obtenerEmpleados();
    }

	$('#titulo').val("");
	$('#informacion').val("");

	var titulo = '<h3><span class="glyphicon glyphicon-info-sign text-success"></span> Agregar firmante</h3>';
	$('#ModalUR_Titulo').empty();
    $('#ModalUR_Titulo').append(titulo);
	$('#ModalUR').modal('show');
    proceso = "Agregar";
    
    if ( idFirma != ''){
        $('#UnidadNegocio').multiselect('disable');
        $('#UnidadEjecutora').multiselect('disable');
    }
}

function fnAgregar(){
   
    var unidadNegocio       = $("#UnidadNegocio").val();
    var unidadEjecutora     = $("#UnidadEjecutora").val();
    var selectUsuario       = $("#selectUsuario").val();
    var selectPuesto        = $("#selectPuesto").val();
    var titulo              = $("#titulo").val();
    var informacion         = $("#informacion").val();
    var validarFirmante     = '';

    var validarFirmanteRepetido     = '';
    validarFirmante     = fnValidarFirmante (unidadNegocio, unidadEjecutora,selectUsuario,selectPuesto,titulo);
    
    if (validarFirmante) {
        if (idFirmante == ''){
            validarFirmanteRepetido   =  fnValidarFirmanteRepetido(unidadNegocio, unidadEjecutora,selectUsuario); 
            if(!validarFirmanteRepetido){ 
                dataObj = { 
                    option: 'guardarFirma',
                    unidadNegocio: unidadNegocio,
                    unidadEjecutora:unidadEjecutora, 
                    selectUsuario:selectUsuario,
                    titulo:titulo,
                    informacion:informacion
                };
                $.ajax({
                    async:false,
                    cache:false,
                    method: "POST",
                    dataType:"json",
                    url: url,
                    data: dataObj
                })
                .done(function( data ) {
                    if(data.result) {
                        $('#divMensajeOperacion').removeClass('hide');
                        $('#ModalUR').modal('hide');
                        var textUsuario =  $('#selectUsuario option:selected').html();
                        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                            muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-success" aria-hidden="true"></i> Se agregó como firmante a '+textUsuario+' con éxito</p>');
                        if ( idFirma != ''){
                            fnCreartablaModificar (globalUe, globlalLn_reporte);
                        }else{
                            fnCrearTabla();
                        }
                    }
                })
                .fail(function(result) {
                });
            }
        }else{
            dataObj = { 
                option: 'actualizarFirmante',
                idFirmante: idFirmante,
                titulo:titulo,
                informacion:informacion
            };
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: url,
                data: dataObj
            })
            .done(function( data ) {
                if(data.result) {
                    $('#divMensajeOperacion').removeClass('hide');
                    $('#ModalUR').modal('hide');
                    var textUsuario =  $('#selectUsuario option:selected').html();
                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                        muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-success" aria-hidden="true"></i> Se actualizo el usuario '+textUsuario+'</p>');
                    if ( idFirma != ''){
                        fnCreartablaModificar (globalUe, globlalLn_reporte);
                    }else{
                        fnCrearTabla();
                    }
                }
            })
            .fail(function(result) {
            });
            idFirmante = ''; 
        }
    }
}
function fnSelectReportes(ln_reporte){
    $('#selectReportes').val(ln_reporte); 
    $('#selectReportes').multiselect('rebuild');
    $('#selectReportes').multiselect('disable');
}
function fnCreartablaModificar(ue, ln_reporte){
    muestraCargandoGeneral();
    
    dataObj = { 
        option: 'obtenerFirmantesModificar',
        idFirma: idFirma,
        ue:ue
      };
  
    $.ajax({
        method: "POST",
        dataType:"json",
        url: url,
        data:dataObj
    })
    .done(function( data ) {
        if(data.result){
            setTimeout(function(){ fnSelectReportes(ln_reporte); }, 1000);

            $('#selectUnidadEjecutora > option[value="'+ue+'"]').attr('selected', 'selected');
            $('#selectUnidadEjecutora').multiselect('rebuild');
            $('#selectUnidadEjecutora').multiselect('disable'); 
            //Si trae informacion
            ocultaCargandoGeneral();

            dataJson = data.contenido.datos;
            columnasNombres = data.contenido.columnasNombres;
            columnasNombresGrid = data.contenido.columnasNombresGrid;
            dataJsonNoCaptura = data.contenido.datos;
            
            fnLimpiarTabla('divTabla', 'divContenidoTabla');
            //fnAgregarGrid(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', '', 1);

            var nombreExcel = data.contenido.nombreExcel;
            var columnasExcel= [ 3,4,5];
            var columnasVisuales= [ 0, 3,4,5,6];
            fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
            
        }else{
            ocultaCargandoGeneral();
            muestraMensaje('No se obtuvo la información', 3, 'divMensajeOperacion', 5000); 
        }
    }).fail(function(result) {
        ocultaCargandoGeneral();
    });
}


function getSelects(tbl,filedata='agregados') {
    var $tbl = $('#'+tbl), rows = [], len = i=0, infTbl;
    infTbl =  $tbl.jqxGrid('getdatainformation');
    len  = infTbl.rowscount;
    for (;i<len;i++) {
        var data = $tbl.jqxGrid('getrowdata',i);
        if(data[filedata]){ rows.push(data); }
    }
    return rows;
}


function fnCreartablaVista(ue, ln_reporte){
    muestraCargandoGeneral();
    dataObj = { 
        option: 'obtenerFirmantesVista',
        idFirma: idFirma,
      };
  
    $.ajax({
        method: "POST",
        dataType:"json",
        url: url,
        data:dataObj
    })
    .done(function( data ) {
        if(data.result){
            setTimeout(function(){ fnSelectReportes(ln_reporte); }, 1000);

            $('#selectUnidadEjecutora > option[value="'+ue+'"]').attr('selected', 'selected');
            $('#selectUnidadEjecutora').multiselect('rebuild');
            $('#selectUnidadEjecutora').multiselect('disable'); 
            //Si trae informacion
            ocultaCargandoGeneral();

            dataJson = data.contenido.datos;
            columnasNombres = data.contenido.columnasNombres;
            columnasNombresGrid = data.contenido.columnasNombresGrid;
            dataJsonNoCaptura = data.contenido.datos;
            
            fnLimpiarTabla('divTabla', 'divContenidoTabla');
            //fnAgregarGrid(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', '', 1);

            var nombreExcel = data.contenido.nombreExcel;
            var columnasExcel= [ 1, 2,3];
            var columnasVisuales= [ 1, 2, 3];
            fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
            
        }else{
            ocultaCargandoGeneral();
            muestraMensaje('No se obtuvo la información', 3, 'divMensajeOperacion', 5000); 
        }
    }).fail(function(result) {
        ocultaCargandoGeneral();
    });

}

function fnCrearTabla(){
    var ur = $("#selectUnidadNegocio").val();;
	var ue = $("#selectUnidadEjecutora").val();
	
    muestraCargandoGeneral();

    dataObj = { 
        option: 'obtenerFirmantes',
        ur: ur,
        ue: ue
      };
  
    $.ajax({
        method: "POST",
        dataType:"json",
        url: url,
        data:dataObj
    })
    .done(function( data ) {
        if(data.result){
            //Si trae informacion
            ocultaCargandoGeneral();
            dataJson = data.contenido.datos;
            columnasNombres = data.contenido.columnasNombres;
            columnasNombresGrid = data.contenido.columnasNombresGrid;
            dataJsonNoCaptura = data.contenido.datos;
            
            fnLimpiarTabla('divTabla', 'divContenidoTabla');
            //fnAgregarGrid(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', '', 1);

            var nombreExcel = data.contenido.nombreExcel;
            var columnasExcel= [ 3,4,5];
            var columnasVisuales= [0,3,4,5,6];
            fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);

        }else{
            ocultaCargandoGeneral();
            muestraMensaje('No se obtuvo la información', 3, 'divMensajeOperacion', 5000); 
        }
    }).fail(function(result) {
        ocultaCargandoGeneral();
    });
}

function modificarFirmante(id_firmante){

    var datosFirmantes = []; 
    muestraCargandoGeneral();

    dataObj = { 
        option: 'obtenerDatosFirmantes',
        id_firmante: id_firmante
      };
  
    $.ajax({
        method: "POST",
        dataType:"json",
        url: url,
        data:dataObj
    })
    .done(function( data ) {
        if(data.result){
            //Si trae informacion
            datosFirmantes = data.contenido.datos;
            
            var id_nu_empleado          = ""; 
            var titulo                  = "";
            var informacion             = ""; 
            var ur                      = "";
            var ue                      = "";
            var id_nu_puesto            = "";
            var selectEmpleado          = "";
            idFirmante                  =id_firmante; 
            for (var info in datosFirmantes) {
                id_nu_empleado  = datosFirmantes[info].id_nu_empleado;
                titulo          = datosFirmantes[info].titulo;
                informacion     = datosFirmantes[info].informacion;
                ur              = datosFirmantes[info].ur;
                ue              = datosFirmantes[info].ue;
                id_nu_puesto    = datosFirmantes[info].id_nu_puesto;
            }
            selectEmpleado      = id_nu_empleado+"_"+id_nu_puesto;
            $('#UnidadNegocio > option[value="'+ur+'"]').attr('selected', 'selected');
            $('#UnidadNegocio').multiselect('rebuild');
            $('#UnidadNegocio').multiselect('disable');

            $('#UnidadEjecutora > option[value="'+ue+'"]').attr('selected', 'selected');
            $('#UnidadEjecutora').multiselect('rebuild');
            $('#UnidadEjecutora').multiselect('disable');

            obtenerEmpleados();  
            $('#selectUsuario > option[value="'+selectEmpleado+'"]').attr('selected', 'selected');
            $('#selectUsuario').multiselect('rebuild');
            $('#selectUsuario').multiselect('disable');

            obtenerPuesto(); 
            $('#selectPuesto > option[value="'+id_nu_puesto+'"]').attr('selected', 'selected');
            $('#selectPuesto').multiselect('rebuild');
            $('#selectPuesto').multiselect('disable');

            $('#titulo').val(titulo);
            $('#informacion').val(informacion);

            ocultaCargandoGeneral();
            $('#divMensajeOperacion').addClass('hide');

            var titulo = '<h3><span class="glyphicon glyphicon-info-sign text-success"></span> Modificar firmante</h3>';
			$('#ModalUR_Titulo').empty();
		    $('#ModalUR_Titulo').append(titulo);
            $('#ModalUR').modal('show');

            
        }else{
            ocultaCargandoGeneral();
            muestraMensaje('No se obtuvo la información', 3, 'divMensajeOperacion', 5000); 
        }
    }).fail(function(result) {
        ocultaCargandoGeneral();
    });

}


