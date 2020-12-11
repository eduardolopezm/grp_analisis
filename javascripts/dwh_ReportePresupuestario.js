var datospivot = "";
var filterdata = "";
var renglonespivot = [ { dataField: 'AnioFiscal' }];
var columnaspivot = [{ dataField: 'PeriodoFiscal'}];

function fnFiltar(){
    alert('filtro');
}

function fnAbrirReporte() {

     $.ajax({
          method: "POST",
          dataType:"json",
          url: "modelo/dwh_ReportePresupuesto_modelo.php",
          data: {
                control: 'clavepresupuestaluno', 
                valor: 'ramo',
                FromDia: $('#FromDia').val(),
                FromMes: $('#FromMes').val(), 
                FromYear: $('#FromYear').val(), 
                ToDia: $('#ToDia').val(), 
                ToMes: $('#ToMes').val(), 
                ToYear: $('#ToYear').val()}
      })
    .done(function( data ) {
        //console.log(data);
        
        //var as = JSON.parse(data);

        datospivot = data;

        fnCargarPivot(data);

        
        
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });


}



function DeleteFilter(){
    if (document.FDatosB.filterlist.value!=""){
    
        if (confirm('Esta seguro de eliminar el filtro seleccionado ?')){
            document.FDatosB.opcdelfilter.value="1";
            document.FDatosB.submit();
        }
    }
    else
        alert('Debe seleccionar un filtro para utilizar esta opcion');
}

function HideWhereSelect(){
        document.getElementById("idwherecond").style.display="none";
}




function fnCargarPivot(data){

    var source =
            {
                localdata: data,
                datatype: "array",
                datafields:
                [
                    { name: 'AnioFiscal', type: 'string' },
                    { name: 'PeriodoFiscal', type: 'string' },
                    { name: 'ramo', type: 'string' },
                    { name: 'ejetematico', type: 'string'},
                    { name: 'clavepresupuestal', type: 'string'},
                    { name: 'cuenta', type: 'string' },
                    { name: 'objetodelgasto', type: 'string' },
                    { name: 'organosuperior', type: 'string' },
                    { name: 'subfuncion', type: 'string' },
                    { name: 'grupo', type: 'string' },
                    { name: 'unidadpresupuestal', type: 'string' },
                    { name: 'tipodegasto', type: 'string' },
                    { name: 'sector', type: 'string' },
                    { name: 'programa', type: 'string' },
                    { name: 'subprograma', type: 'string' },
                    { name: 'genero', type: 'string' },
                    { name: 'objetivos', type: 'string' },
                    { name: 'subcuenta', type: 'string' },
                    { name: 'sscuenta', type: 'string' },
                    { name: 'rubro', type: 'string' },
                    { name: 'mes', type: 'string' },
                    
                    
                    
                    
                    
                    
                    { name: 'presupuestoaprobado', type: 'number' },
                    { name: 'presupuestoamplreduc', type: 'number' },
                    { name: 'presupuestomodificado', type: 'number' },
                    { name: 'presupuestoporejercer', type: 'number' },
                    { name: 'gastodeinversion', type: 'number' },
                    { name: 'historicocomprometido', type: 'number' },
                    { name: 'historicodevengado', type: 'number' },

                    { name: 'historicoejercido', type: 'number' },
                    { name: 'subejercicio', type: 'number' },
                    { name: 'deudaejercicio', type: 'number' },
                    { name: 'ejercido', type: 'number' },
                    { name: 'devengado', type: 'number' },
                    { name: 'pagado', type: 'number' },
                    { name: 'disponibleparacomprometer', type: 'number' },
                    
                    { name: 'comprometido', type: 'number' },
                    { name: 'gastodeoperacion', type: 'number' },
                    
                ]
            };
            var dataAdapter = new $.jqx.dataAdapter(source);
            dataAdapter.dataBind();
            // create a pivot data source from the dataAdapter
            var pivotDataSource = new $.jqx.pivot(
                dataAdapter,
                {
                    pivotValuesOnRows: false,
                    rows: renglonespivot,
                    columns: columnaspivot,
                   
                    values: [
                        { dataField: 'presupuestoaprobado', 'function': 'sum', text: 'Presupuesto Aprobado' },
                        { dataField: 'presupuestoporejercer', 'function': 'sum', text: 'Presupuesto Por Ejercer' }
                        /* { dataField: 'presupuestoamplreduc', 'function': 'sum', text: 'Presupuesto Ampl/Reduc' },
                        { dataField: 'presupuestomodificado', 'function': 'sum', text: 'Presupuesto Modificado' },
                        { dataField: 'historicocomprometido', 'function': 'sum', text: 'Historico Comprometido'},
                        { dataField: 'porchistoricocomprometido', 'function': 'sum', text: '% Historico Comprometido'},
                        { dataField: 'historicodevengado', 'function': 'sum', text: 'Historico Devengado'},
                        { dataField: 'porchistoricodevengado', 'function': 'sum', text: '% Historico Devengado'},
                        { dataField: 'historicoejercido', 'function': 'sum', text: 'Historico Ejercido'},
                        { dataField: 'porchistoricoejercido', 'function': 'sum', text: '% Historico Ejercido'},
                        { dataField: 'pagado', 'function': 'sum', text: 'Pagado'},
                        { dataField: 'subejercicio', 'function': 'sum', text: 'subejercicio'},
                        { dataField: 'porcpagado', 'function': 'sum', text: 'porcpagado'},
                        { dataField: 'comprometido', 'function': 'sum', text: 'comprometido'},
                        { dataField: 'disponibleparacomprometer', 'function': 'sum', text: 'disponibleparacomprometer'},
                        { dataField: 'porcdisponibleparacomprometer', 'function': 'sum', text: 'porcdisponibleparacomprometer'},
                        { dataField: 'devengado', 'function': 'sum', text: 'devengado'},
                        { dataField: 'ejercido', 'function': 'sum', text: 'ejercido'},
                        { dataField: 'deudaejercicio', 'function': 'sum', text: 'deudaejercicio'},
                        { dataField: 'gastodeinversion', 'function': 'sum', text: 'gastodeinversion'},
                        { dataField: 'gastodeoperacion', 'function': 'sum', text: 'gastodeoperacion'} */
                     
                    ],
                    filters: [
                        {
                            dataField: 'objetodelgasto',
                            text: 'Objeto del gasto',
                            filterFunction: function (value) {
                                //alert(filterdata);
                                if (filterdata == "") 
                                    return false;

                                else {
                                    if (value == filterdata)
                                        return false;
                                    else 
                                        return true;
                                }
                            }
                        }
                    ]
                });
            // create a pivot grid
            $('#tabladinamica').jqxPivotGrid(
            {
                source: pivotDataSource,
                treeStyleRows: false,
                
                multipleSelectionEnabled: true
            });


            if (data.length>0) {


            var pivotGridInstance = $('#tabladinamica').jqxPivotGrid('getInstance');
            pivotGridInstance.getPivotRows().items[0].expand();
            pivotGridInstance.refresh();


            // create a pivot grid
            /*$('#divPivotGridDesigner').jqxPivotDesigner(
            {
                type: 'pivotGrid',
                target: pivotGridInstance
            });*/

            $('#tabladinamica').on('pivotitemdblclick', function (event) {
     alert('Pivot item double click: ' + event.args.pivotItem.text + ' , mouse button:' + event.args.mousebutton );

     
});

            
        }   else alert('No hay datos para mostrar dentro de estas fechas');



            

}


function actualizaGroupBy(obj){
    

 
    //obj.stopPropagation();
    
    
       
    //pivotDataSource.dataBind();

        /*
    $.ajax({
          method: "POST",
          dataType:"json",
          url: "modelo/dwh_ReportePresupuesto_modelo.php",
          data: {
                control: obj.name, 
                valor: obj.value,
                FromDia: $('#FromDia').val(),
                FromMes: $('#FromMes').val(), 
                FromYear: $('#FromYear').val(), 
                ToDia: $('#ToDia').val(), 
                ToMes: $('#ToMes').val(), 
                ToYear: $('#ToYear').val()}
      })
    .done(function( data ) {
        console.log(data.contenido.datos);
        
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });*/

    
    //alert(obj.name);

    //if (obj.value == 'Ramo')
    if (obj.name.includes('uno')) renglonespivot = [{ dataField: obj.value}];
    if (obj.name.includes('dos')) columnaspivot = [{ dataField: obj.value}];



//    $('#divPivotGridDesigner').innerHTML ='';
//    $('#tabladinamica').innerHTML = '';


    fnCargarPivot(datospivot);
    

    var contenido = "<option value='0'>Seleccionar...</option>";
          //for (var info in dataJson) {
            contenido += "<option value='"+obj.value+"'>"+obj.value+"</option>";
          //}
        //$('#select1D').empty();
        //$('#select1D').append(contenido);
        //$('#select1D').multiselect('refresh');

        //$('#select2D').empty();
        //$('#select2D').append(contenido);
        //$('#select2D').multiselect('refresh');

    
    
}





function ExportaExcel(){
    window.open("dwh_ReportePresupuestosExcel.php");
}

function selAll(obj){
    var I = document.getElementById('totrows').value;

    alert("valor de :" + obj.name);

    for(i=1; i<=I; i++) {
        var concatenar = "chk" + i;
        chkobj = document.getElementById(concatenar);
        if(chkobj != null) {
            chkobj.checked = obj.checked;
        }
    }

    // alert(document.getElementById('selmultiple').value);
}

function invsel(obj){
    var I = document.getElementById('totrows').value;
    
    for(i=1; i<=I; i++) {
        var concatenar = "chk" + i;
        chkobj = document.getElementById(concatenar);
        if(chkobj != null) {
            chkobj.checked = !chkobj.checked; 
        }
    }
}


$( document ).ready(function() {

    //$("#filtro").html('<lu><li>escucharlo</li></lu>');


    //return;
    //Mostrar Catalogo
    // prepare sample data
           /* var data = new Array();
            var firstNames =
            [
                "Andrew", "Nancy", "Shelley", "Regina", "Yoshi", "Antoni", "Mayumi", "Ian", "Peter"
            ];
            var lastNames =
            [
                "Fuller", "Davolio", "Burke", "Murphy", "Nagase"
            ];
            var productNames =
            [
                "Black Tea", "Green Tea", "Caffe Espresso", "Doubleshot Espresso", "Caffe Latte", "White Chocolate Mocha", "Cramel Latte", "Caffe Americano", "Cappuccino", "Espresso Truffle", "Espresso con Panna", "Peppermint Mocha Twist"
            ];
            var priceValues =
            [
                "2.25", "1.5", "3.0", "3.3", "4.5", "3.6", "3.8", "2.5", "5.0", "1.75", "3.25", "4.0"
            ];
            for (var i = 0; i < 500; i++) {
                var row = {};
                var productindex = Math.floor(Math.random() * productNames.length);
                var price = parseFloat(priceValues[productindex]);
                var quantity = 1 + Math.round(Math.random() * 10);
                row["header"] = firstNames[Math.floor(Math.random() * firstNames.length)];
                row["lastname"] = lastNames[Math.floor(Math.random() * lastNames.length)];
                row["productname"] = productNames[productindex];
                row["price"] = price;
                row["quantity"] = quantity;
                row["total"] = price * quantity;
                data[i] = row;
            }

            console.log(JSON.stringify(data));*/ 
            // create a data source and data adapter
            /*
            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'header', type: 'string' },
                    { name: 'dia' , type: 'string'},
                    { name: 'presupuestoaprobado', type: 'number' },
                    
                ],
                
                url: 'modelo/dwh_ReportePresupuesto_modelo.php',
                data: {control: 'clavepresupuestaluno', 
                valor: 'ramo',
                FromDia: $('#FromDia').val(),
                FromMes: $('#FromMes').val(), 
                FromYear: $('#FromYear').val(), 
                ToDia: $('#ToDia').val(), 
                ToMes: $('#ToMes').val(), 
                ToYear: $('#ToYear').val()}
            }; */


            $.ajax({
          method: "POST",
          dataType:"json",
          url: "modelo/dwh_ReportePresupuesto_modelo.php",
          data: {
                control: 'clavepresupuestaluno', 
                valor: 'ramo',
                FromDia: $('#FromDia').val(),
                FromMes: $('#FromMes').val(), 
                FromYear: $('#FromYear').val(), 
                ToDia: $('#ToDia').val(), 
                ToMes: $('#ToMes').val(), 
                ToYear: $('#ToYear').val()}
      })
    .done(function( data ) {
        //console.log(data);
        
        //var as = JSON.parse(data);

        datospivot = data;

        fnCargarPivot(data);

        
        
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });

            
            
            // get the pivot grid instance
            
            /*
            $('#btnCheckRowsDisplayStyle').jqxToggleButton({ width: 200 });
            $('#btnCheckRowsDisplayStyle').on('click', function ()
            {
                if (pivotGridInstance.treeStyleRows)
                {
                    $('#btnCheckRowsDisplayStyle').jqxToggleButton('val', 'Change to Tree style display');
                    pivotGridInstance.treeStyleRows = false;
                }
                else
                {
                    $('#btnCheckRowsDisplayStyle').jqxToggleButton('val', 'Change to OLAP style display');
                    pivotGridInstance.treeStyleRows = true;
                }
                pivotGridInstance.refresh();
            });*/
        });
    

