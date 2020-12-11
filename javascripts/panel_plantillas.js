/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña 
 * @version 0.1
 */

function fnGenerarLayoutCualquierLayout(funcion,tipo,nombre){

        var jsonData = new Array();
        var obj = new Object();
        obj.transno = '';
        jsonData.push(obj);
        fnGenerarArchivoLayout(funcion, tipo, jsonData,1,nombre,nombre,2);
    

}

/*function fnGenerarLayoutRequisicion(){
    var noReq = $("#idtxtRequisicion").val();
    if(Number(noReq) > 0){
        var jsonData = new Array();
        var obj = new Object();
        obj.transno = noReq;
        jsonData.push(obj);

        fnGenerarArchivoLayout(funcionGenerarLayout, typeGenerarLayout, jsonData, tipoLayout);
    }
}*/
function fnComboDeLayouts(){

    dataObj = {
        proceso: 'layoutsQueExisten'
    };
    muestraCargandoGeneral();
    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/panel_plantillas_modelo.php",
            async: false,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
            	
            	datosCombo = data.contenido.datos;

            	htmlSelect = fnCrearDatosSelect(datosCombo);
            	htmlCombo='<select id="selectLayout" name="selectLayout" class="comboLayout"  required>' + htmlSelect + '</select>';

            	$('#combo').append(htmlCombo);
            	
            	fnFormatoSelectGeneral('#selectLayout');
        
                ocultaCargandoGeneral();
            } else {
                ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
            ocultaCargandoGeneral();
        });

}

function fnTiposDocumentos(){

    dataObj = {
        proceso: 'documentosQueExisten'
    };
    muestraCargandoGeneral();
    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/panel_plantillas_modelo.php",
            async: false,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                
                datosCombo = data.contenido.datos;

                htmlSelect = fnCrearDatosSelect(datosCombo);
                htmlCombo='<select id="selectDocumentos" name="selectDocumentos" class="selectDocumentos"  required>' + htmlSelect + '</select>';

                $('#comboDocumentos').append(htmlCombo);
                
                fnFormatoSelectGeneral('#selectDocumentos');
        
                ocultaCargandoGeneral();
            } else {
                ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
            ocultaCargandoGeneral();
        });

}


function fnfiltrar(){
    muestraCargandoGeneral();
  
    var tipo=$("#selectDocumentos").val();
    //alert(tipo);
    var eslayout =$('#eslayout').is(':checked');
    // alert(eslayout);
    var dateDesde = $('#dateDesde').val();
    var dateHasta = $('#dateHasta').val();
    
    //alert(tipo);
    dataObj ={
        proceso: 'filtrar',
        tipo:tipo,
        eslayout:eslayout,
        dateDesde:dateDesde,
        dateHasta:dateHasta
    };
    //muestraCargandoGeneral();
    $.ajax({
        method: "post",
        dataType:"json",
        url: "modelo/panel_plantillas_modelo.php",
        data:dataObj
    })
        .done(function(data){
            if(data.result){
                datosArchivos=data.contenido.DatosArchivos;
               
                //fnLimpiarTabla('divTablaArchivos', 'divDatosArchivos');
                //fnAgregarGridv2(datosArchivos,'divDatosArchivos','b');
            fnLimpiarTabla('divTablaArchivos', 'divDatosArchivos');   

            columnasNombres = '';
            columnasNombres += "[";
            //columnasNombres += "{ name: 'id1', type: 'bool'},";
            columnasNombres += "{ name: 'cajacheckbox', type: 'bool' },";
            columnasNombres += "{ name: 'id', type: 'string' },";
            columnasNombres += "{ name: 'tipo', type: 'string' },";
            columnasNombres += "{ name: 'nombre',type:'string'},";
            columnasNombres += "{ name: 'funcion',type:'string'},";
            columnasNombres += "{ name: 'tipo_doc',type:'string'},";
            columnasNombres += "{ name: 'usuario', type: 'string' },";
            columnasNombres += "{ name: 'fecha', type: 'string' }";
           

            columnasNombres += "]";
            //Columnas para el GRID
            columnasNombresGrid = '';
            columnasNombresGrid += "[";
            columnasNombresGrid += " { text: '', datafield: 'cajacheckbox', width: '4%', cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
            columnasNombresGrid += " { text: 'id', datafield: 'id', width: '10%', cellsalign: 'center', align: 'center', hidden: true },";
            columnasNombresGrid += " { text: 'Extensión', datafield: 'tipo', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Nombre archivo',datafield: 'nombre', width: '19%', align: 'center',hidden: false,cellsalign: 'left' },";
            columnasNombresGrid += " { text: 'Función',datafield: 'funcion', width: '18%', align: 'center',hidden: false,cellsalign: 'left' },";
            columnasNombresGrid += " { text: 'Tipo documento',datafield: 'tipo_doc', width: '18%', align: 'center',hidden: false,cellsalign: 'left' },";
            columnasNombresGrid += " { text: 'Usuario',datafield: 'usuario', width: '18%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Fecha', datafield: 'fecha', width: '18%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += "]";

            var columnasExcel = [2,3,4,5];
            var columnasVisuales = [0,2,3,4,5];
            nombreExcel = data.contenido.nombreExcel;

            fnAgregarGrid_Detalle(data.contenido.DatosArchivos, columnasNombres, columnasNombresGrid, 'divDatosArchivos', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
            ocultaCargandoGeneral();
              
            }else{
                //alert();
                //fnLimpiarTabla('divTablaArchivos', 'divDatosArchivos');
                //fnAgregarGridv2(d,'divDatosArchivos','b');
               ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log( result );
            ocultaCargandoGeneral();
        });
}

$(document).ready(function() {
  

    var anio = new Date().getFullYear();
    var d = new Date();
    var n = d.getMonth();
    n += 1;

    for (i = 1; i <= 9; i++) {
        if (n == i) {
            n = "0" + i;
        }
    }

    mes = n;
    $("#dateDesde").val('01-' + mes + '-' + anio);
    $("#dateHasta").val('30-' + mes + '-' + anio);

    //fnComboDeLayouts();//descarga de plantillas sin contenido
    fnTiposDocumentos();
    fnfiltrar();

    $('#descargarLayout').click(function(){
        val=$('#selectLayout').val();
        datos=val.split("_");
        nombre=$('#selectLayout option:selected').text();
        nombre=nombre.split(":");

        fnGenerarLayoutCualquierLayout(datos[0],datos[1],nombre[1]);
    });
});
