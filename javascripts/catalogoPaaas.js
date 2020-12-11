/**
 * @fileOverview Panel del modulo PAAAS
 * @author Luis Aguilar Sandoval
 * @version 0.1
 * @Fecha 21 de mayo del 2018
 */

//
$(document).ready(function() {
    window.onload = dondeEstanBotones;
    window.titulo='<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
    window.foliosCsv=0;
    dondeEstanBotones();
    initPanel();
    toogelSearch();
    /****************** COMPORTAMIENTO DE LA CARGA DE BOTONES *****************/
    $('#btn-search').on('click', toogelSearch);
    $('#nuevo').on('click', newSetting);
   // $('#Consumos').on('click', newSetting); //searchConsume);
    /************************* COMPORTAMIENTO GENERAL *************************/

    $("#confirmacionModalGeneral1").click(function(){
        changeStatusConfirmation();
        $("#ModalGeneral1").modal('hide');
    });
});

/**
 * Funcion primsipal para la inicializacion de todo el panel asi como la
 * aplicación de los datos y formatos
 * @return {[type]} [description]
 */
function initPanel() {
    console.log('inicio del panel');
    renderTable();
    startSettings();
    fnObtenerBotones_Funcion('areaBotones', nuFuncion);
};

/**
 * Funcion para la inicializacion de las variables que se utilizaran en el
 * programa para consutas y demas
 * @return {[type]} [description]
 */
function startSettings() {
    // cancelacion de submit en los botone del panel
    $(document).on('click', 'button', function(e) {
        e.preventDefault();
    });

    // aplicacion de estilos a las fechas
    $('#dateCaptura').parent('div').css({
        width: '100%'
    });

    // aplicacion de los formatos para el select de estatus
    fnFormatoSelectGeneral('#status');

    // obtencion de la url base para el programa
    var url = window.location.href.split('/');
    url.splice(url.length - 1);
    window.url = url.join('/');

    // definicion del modelo al que se apunta
    window.modelo = this.url + "/modelo/catalogoPaaasModelo.php";

    // obtencion de los estatus del sistema
    window.estatusRoot = getStatus();
    window.StatusNext=0;
    window.nombreBotonClik='';
};

/**
 * Funcion para el muestreo de los datos que se reciben de la consulta
 * quese hace en base de datos
 * @param  {[type]} data [description]
 * @return {[type]}      [description]
 */
function renderTable(data) {
    var data = data || [],
        el = 'datos',
        tabla = 'tabla',
        nameexcel = 'Reporte PAAAS',
        datafields = '',
        columns = '',
        visualcolumn = [0, 1, 2, 3, 4, 6, 7, 8,9,10,11,12],
        columntoexcel = [1, 2, 3,4, 5, 7, 8,9,10,11,12];
    // datos y comportamiento de los datos
    datafields = [{
            name: 'check',
            type: 'bool'
        }, // 0
        {
            name: 'ur',
            type: 'string'
        }, // 1
        {
            name: 'ue',
            type: 'string'
        }, // 2
         {
            name: 'asignado',
            type: 'string'
        }, // 3
        {
            name: 'gastado',
            type: 'string'
        },
        {
            name: 'iFolio',
            type: 'number'
        }, // 4
        {
            name: 'folio',
            type: 'string'
        }, // 5
        {
            name: 'oficio',
            type: 'string'
        }, // 6
        {
            name: 'fecha',
            type: 'string'
        }, // 7
        {
            name: 'fechaDesde',
            type: 'string'
        }, // 8
        {
            name: 'fechaHasta',
            type: 'string'
        }, // 9
        {
            name: 'sSta',
            type: 'string'
        }, // 10
        {
            name: 'anio',
            type: 'number'
        }, // 9
        {
            name: 'status',
            type: 'number'
        } // 11
    ];
    // titulos y estilos de las columnas
    // UR	UE	Fecha captura	Folio	Estatus
    columns = [{
            text: '',
            datafield: 'check',
            columntype: 'checkbox',
            width: '5%',
            cellsalign: 'center',
            align: 'center'
        }, // 0
        {
            text: 'UR',
            datafield: 'ur',
            editable: false,
            width: '12%',
            cellsalign: 'center',
            align: 'center'
        }, // 1
        {
            text: 'UE',
            datafield: 'ue',
            editable: false,
            width: '12%',
            cellsalign: 'center',
            align: 'center'
        }, // 2
        {
            text: 'Presupuesto por asignar',
            datafield: 'asignado',
            editable: false,
            width: '12%',
            cellsalign: 'center',
            align: 'center',
             type:'string',
             cellsformat: 'C2'
        }, // 3
         {
            text: 'Presupuesto asignado',
            datafield: 'gastado',
            editable: false,
            width: '12%',
            cellsalign: 'center',
            align: 'center',
            type:'string',
            cellsformat: 'C2'
        }, // 4
        {
            text: 'Folio',
            datafield: 'iFolio',
            editable: false,
            width: '11%',
            cellsalign: 'center',
            align: 'center',
            hidden: true
        }, // 5

        {
            text: 'Folio',
            datafield: 'folio',
            editable: false,
            width: '10%',
            cellsalign: 'center',
            align: 'center'
        }, // 6 
        {
            text: 'Oficio',
            datafield: 'oficio',
            editable: false,
            width: '10%',
            cellsalign: 'center',
            align: 'center'
        }, // 7
          {
            text: 'Estatus',
            datafield: 'sSta',
            editable: false,
            width: '9%',
            cellsalign: 'center',
            align: 'center'
        }, // 8
        {
            text: 'Fecha Inicio',
            datafield: 'fechaDesde',
            editable: false,
            width: '8%',
            cellsalign: 'center',
            align: 'center'
        }, // 9
        {
            text: 'Fecha Termino',
            datafield: 'fechaHasta',
            editable: false,
            width: '8%',
            cellsalign: 'center',
            align: 'center'
        }, // 10
        {
            text: 'Año',
            datafield: 'anio',
            editable: false,
            width: '7%',
            cellsalign: 'center',
            align: 'center'
        }, // 11
         {
            text: 'Fecha Captura',
            datafield: 'fecha',
            editable: false,
            width: '8%',
            cellsalign: 'center',
            align: 'center'
        }, // 12
        {
            text: 'status',
            datafield: 'status',
            editable: false,
            width: '9%',
            cellsalign: 'center',
            hidden: true,
            align: 'center'
        } // 13
    ];

    // llamado de limpiesa de la tabla
    fnLimpiarTabla(tabla, el);
    // renderisado de la tabla
    fnAgregarGrid_Detalle_nostring(
        data, datafields, columns, el, ' ', 1, columntoexcel, false, true, "",
        visualcolumn, nameexcel
    );
};

/**
 * Funciñon para la obtencion de información de los esenarios guerdados en
 * la base de datos ya sea para revicion o modificación
 * @return {[type]} [description]
 */
function toogelSearch() {
    var params = getParams('form-search');
    params.method = 'show';
    $.ajaxSetup({async: false, cache:false});
    $.post(modelo, params).then(function(res) {
        var content = res.content
        renderTable(res.content);
        window.permissions = res.permissions;
    });
};

/**
 * funcion para el envio a la captura del nuevo esenario
 * @return {[type]} [description]
 */
function newSetting() {
    // window.location.href = url + '/consumosFecha.php';
    window.location.href = url + '/paas.php';
    
};

function searchConsume() {

    $.ajaxSetup({async: false, cache:false});
    $.post(modelo, {method:'searchConsume'}, function(data) {
            window.open(url +"/"+ data, '_self');
        });
};
/**
 * funcion de obtencion de los permisos que tiene el usuario en caso de que se
 * quiera reflejar los nuevos permisos
 * @return {[type]} [description]
 
function getPermissions() {
    $.post(modelo, {
        method: 'getPermissions'
    }).then(function(res) {
        window.permissions = res.content;
    });
}

/**
 * Funcion para la obtencion de los estatus manejados en el panel
 * @return {[type]} [description]
 */
function getStatus() {
    var params = {
            method: 'getStatus',
            functionid: nuFuncion
        },
        options = [{
            label: 'Seleccione una opción',
            value: ''
        }];
    $.ajaxSetup({async: false, cache:false});
    $.post(modelo, params).then(function(res) {
        if (res.success) {
            options = res.content;
            $('#status').multiselect('dataprovider', options);
        }
    });
    return options;
}

function GetInfoGridByColumn(table,divData,check,column){
    var griddata = $('#'+table+' > #'+divData).jqxGrid('getdatainformation');
    var getValue=0;
    var values=[];
    var data=[];
    var estatus=0;
    for (var ad = 0; ad < griddata.rowscount; ad++) {
            isCheck = $('#'+table+' > #'+divData).jqxGrid('getcellvalue', ad,check);
            if(isCheck==true){
                getValue= $('#'+table+' > #'+divData).jqxGrid('getcellvalue',ad,column);
                estatus= $('#'+table+' > #'+divData).jqxGrid('getcellvalue',ad,"status");
                values.push(getValue);
            }
   
    }
    data.push(values); // folios
    data.push(estatus);
    return  data;
}



 function dondeEstanBotones() {
      document.getElementById("areaBotones").onclick = botones;
}
 function botones(e) {
      if (e.target.tagName == 'BUTTON') {
        window.nombreBotonClik=e.target.id;
        msg=window.nombreBotonClik;
        msg=msg.toLowerCase();
        $("#ModalGeneral1_Mensaje").append("¿Está seguro de <b>" +msg+ "</b> los escenarios seleccionado(s)?");
      }

}

function fnCambiarEstatus(estatus){
    if(estatus!=2373){
        dondeEstanBotones();
        window.StatusNext=estatus;
        $("#ModalGeneral1").modal('show');
        $("#ModalGeneral1_Mensaje").empty(); 
    }else{
       // fnGenerarCSV();
       obtenerFolios();
    }
}

function obtenerFolios(){
  dameCSV=false;
  if(window.necesitaUE==true){
        ue=$("#selectUnidadEjecutora").val();
        if(ue==-1){
            dameCSV=false;
             muestraModalGeneral(3, titulo,"<i class='glyphicon glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i>Necesita seleccionar UE",'');
        }else{
            dameCSV=true;
        }

   }else{
     dameCSV=true;
   }
   if(dameCSV==true){

    var params = {
        method: 'obtenerFolios',
        ue: $("#selectUnidadEjecutora").val(),
        ur: $("#selectUnidadNegocio").val()
    };

    $.ajaxSetup({async: false, cache:false});
    $.post(modelo, params).then(function(res) {
    if (res.success) {
           // console.log(folios," folio");
           //muestraModalGeneral(4,titulo,res.content);
            //window.open('catalogoPaaas.php',"_self");
            //window.foliosCsv=res.content;
            //
    data=res.content;

    if(res.folios){

    var obj = new Object();
    var jsonData = new Array();
    obj.transno = res.content; //transnoTransfer; 
    jsonData.push(obj);
   
    liga= fnGenerarArchivoLayoutSinModal('2373','285',jsonData,2,' csv para compranet',' csv para compranet');
    //liga=
    muestraModalGeneral(3, titulo,liga,'');

    }else{
        if(data!=""){
         muestraModalGeneral(3, titulo,data,''); 
         console.log(res.prueba);  
        }
     }
    
    fnGenerarCsvGeneralPaaas(res.csvLeyenda,"Auxiliar de trabajo",true);
    
    }
    });
   }
}

function changeStatusConfirmation(){
     data=GetInfoGridByColumn("tabla","datos","check","iFolio");
     folios=data[0];
    
   
    // if(data[1]=="5"){
    //     flag=false;
    // }
    var flag=false;
    if(folios.length==1){
      //console.log("",flag,"<--");
    flag=validarEstatus(data[1],window.StatusNext);
     //console.log("",flag,"|--|---|");
    }
     if(StatusNext==6){
       flag=true;
    }
   
    if(flag==true){
        aux=0;
        if(window.estatusSig==1){
            aux=estatusSig;
        }else{
            aux=window.StatusNext;
        }
     var params = {
            method: 'changeStatus',
            status:aux,
            name:window.nombreBotonClik,
            folios:folios

        };

    $.ajaxSetup({async: false, cache:false});
    $.post(modelo, params).then(function(res) {
        if (res.success) {
           // console.log(folios," folio");
          // muestraModalGeneral(4,titulo,res.content+"421");
        if(res.content!=""){
            muestraModalGeneral(4,titulo,res.content);
            if(res.gestionado){
                   toogelSearch(); 
            }
          }else{
            toogelSearch();
        }
            
        }
    });
          
    }else{
         muestraModalGeneral(4,titulo,"<i class='glyphicon glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i>Seleccione un folio valido");
    }
}
function remplazaComa(cadena){
var busca = "\\|@";
var reemplaza = new RegExp(busca, 'g');  // replace(new RegExp(find, 'g'), replace);
cadena = cadena.replace(reemplaza, ',');
return cadena;
// var Re = new RegExp("\\.","g");
// st = st.replace(Re," ");
}
function fnGenerarCsvGeneralPaaas(objArray, name = 'csv') {
    // console.log("objArray: "+objArray);
    var csv = fnGeneraCsvJsonGeneralPaaas(objArray);
    var downloadLink = document.createElement("a");
    var blob = new Blob(["\ufeff", csv]);
    var url = URL.createObjectURL(blob);
    downloadLink.href = url;
    downloadLink.download = name+".csv";

    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

/**
 * Función para generar estructura csv
 * @param  {[type]} objArray Informacion a recorrer
 * @return {[type]}          [description]
 */
function fnGeneraCsvJsonGeneralPaaas(objArray) {
    // console.log("objArray: "+JSON.stringify(objArray));
    var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;
    var str = '';
    var line = '';
    // console.log("array: "+JSON.stringify(array));
    if ($("#labels").is(':checked')) {
        var head = array[0];
        if ($("#quote").is(':checked')) {
            for (var index in array[0]) {
                var value = index + "";
                line += '"' + value.replace(/"/g, '""') + '",';
            }
        } else {
            for (var index in array[0]) {
                line += index + ',';
            }
        }

        line = line.slice(0, -1);
        str += line + '\r\n';
    }

    for (var i = 0; i < array.length; i++) {
        var line = '';

        if ($("#quote").is(':checked')) {
            for (var index in array[i]) {
                var value = array[i][index] + "";
                
                if(index=='comentarios'){
                    aux=value.replace(/"/g, '""');
                    line += '"' +remplazaComa(aux)  + '",';
                }else{
                  line += '"' + value.replace(/"/g, '""') + '",';  
                }
            }
        } else {
            for (var index in array[i]) {
                //line += array[i][index] + ',';

                if(index=='comentarios'){
                    aux= array[i][index];
                    line += '"' +remplazaComa(aux)  + '",';
                }else{
                   line += array[i][index] + ','; 
                }
            }
        }

        line = line.slice(0, -1);
        str += line + '\r\n';
    }
    return str;
}