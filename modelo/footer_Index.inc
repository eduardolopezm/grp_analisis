        </div>
      </div> 
    </div> 
    
    <footer id="divGeneralFooter" name="divGeneralFooter">
      <div class="linea-verde"></div>
      <div class="footer-container">
        <div class="container">
          <div class="text-right text-rate">
            <?php 

              $currentDate = date('d') . '/' . date('m') . '/' . date('Y');
              $currentDateDBFormat = date('Y') . '/' . date('m') . '/' . date('d');
              
              $rate = GetCurrencyRateByDate($currentDateDBFormat, 'USD', $db);
              if($rate == 0) {
                $rates = GetAllCurrencyRatesFromPeriodDLS($currentDate, $currentDate);
                $rate = number_format(1 / $rates[1]['rate'], 15);
                DB_query("INSERT INTO tipocambio VALUES(NULL, 'USD', '$rate', '$currentDateDBFormat')", $db);
                
                $rate = GetCurrencyRateByLastDateWRate($currentDateDBFormat, 'USD', $db);
              }
              
              $rate = 1 / $rate;
              
              echo '<span class="tipocambio">Tipo de cambio :</span>
                    <span class="tipocambiovalor"> $'. number_format($rate,4).'</span> 
                    <span class="tipocambio"> 
                        al '. strftime('%d de %B de %Y', strtotime($currentDateDBFormat) ).
                    '</span>';
            ?>
          </div>
        </div>
      </div>
    </footer>

    

    <script type="text/javascript" src="css/v3/librerias/bootstrap-multiselect/bootstrap-multiselect.js"></script>
    <link rel="stylesheet" type="text/css" href="css/v3/librerias/bootstrap-multiselect/bootstrap-multiselect.css">

    <script type="text/javascript" src="css/v3/librerias/bootstrap-multiselect/bootstrap-select.js"></script>
    <!-- <link rel="stylesheet" type="text/css" href="css/v3/librerias/bootstrap-multiselect/bootstrap-select.css"> -->
    <!-- Lenguaje Calendario -->

    <!--Librerias Calendario-->
    <script type="text/javascript" src="css/v2/js/validacion/moment.js"></script>
    <script type="text/javascript" src="css/v3/librerias/datepicker/js/bootstrap-datepicker.js"></script>

    <!-- ruta libreria en español que no funciona
     <script type="text/javascript" src="css/v3/librerias/datepicker/locales/bootstrap-datetimepicker.es.js"></script>-->
   
    <script src="css/v3/librerias/bootstrap-3.3.7/js/bootstrap.min.js"></script>
    
    <!--Archivo Funciones Generales Script-->
    <script type="text/javascript" src="javascripts/funciones_generales.js"></script>

    <!--Cargar Librerias de VUE Componentes-->
    <!-- <script src="https://unpkg.com/vue"></script>
    <script src="https://unpkg.com/vue/dist/vue.js"></script> -->
    <script src="css/vue/Vue.js"></script>
    <!--Cargar Librerias de VUE Componentes para el multiselect -->
    <!-- <script src="css/vue/multiselectComponent/vue-multiselect@2.0.0.js"></script>
    <link rel="stylesheet" href="css/vue/multiselectComponent/styleMultiSelect.css"> -->
    
    <!--Cargar Archivo de Componetes de VUE-->
    <script src="javascripts/componentesVue.js"></script>

    <script type="text/javascript">
        $('.sticky').css('min-width', '1100px');
        $('#wrap').css('min-width', '1100px');
        $('.content').css('min-width', '1100px');
        $('.footer-container').css('min-width', '1100px');
    </script>

    <script type="text/javascript">    
        muestraCargandoGeneral();

        // Inicio Json para Multiselect
        if (document.querySelector(".selectRazonSocial")) {
          // Inicio Razon Social
          //Opcion para operacion
          dataObj = { 
                  option: 'mostrarRazonSocial'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php?",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].legalid+"'>"+dataJson[info].legalname+"</option>";
                  }
                  $('.selectRazonSocial').append(contenido);
              }else{
                  //console.log("ERROR Modelo");
                  //console.log( JSON.stringify(data) ); 
              }
          })
          .fail(function(result) {
              //console.log("ERROR");
              //console.log( result );
          });
          // Fin Razon Social
        }

        if (document.querySelector(".selectUnidadNegocio")) {
          // Inicio URG
          var seleccionado= "";
          var seltodos= false;
          var unidad= "";

          if (!$(".selectUnidadNegocio").prop("multiple") && $(".selectUnidadNegocio").data("todos")) {
            seltodos= true;
          }

          if ($(".selectUnidadNegocio").data("unidad") != "" &&  typeof $(".selectUnidadNegocio").data("unidad") != "undefined") {
            unidad= $(".selectUnidadNegocio").data("unidad");
          }

          //Opcion para operacion
          dataObj = { 
                  option: 'mostrarUnidadNegocio'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion                  
                  dataJson = data.contenido.datos;
                  var contenido = "";
                  //console.log("datos: "+dataJson.length);
                  
                  if (dataJson.length == 1) {
                    seleccionado= " selected ";
                  } else if (seltodos) {
                    contenido += "<option value='-1' selected>Sin seleccion</option>";
                  }
                  // console.log("seleccionado: "+seleccionado);
                  // console.log("unidad: "+unidad);
                  for (var info in dataJson) {
                    if (unidad != "" && unidad != "undefined") {
                      if (dataJson[info].tagref == unidad) {
                        contenido += "<option value='"+dataJson[info].tagref+"' selected>"+dataJson[info].tagdescription+"</option>";
                      } else {
                        contenido += "<option value='"+dataJson[info].tagref+"'>"+dataJson[info].tagdescription+"</option>";
                      }
                    } else {
                      contenido += "<option value='"+dataJson[info].tagref+"'"+seleccionado+">"+dataJson[info].tagdescription+"</option>";
                    }
                  }

                  $('.selectUnidadNegocio').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin URG
        }

        // Componente de tipo combo para mostrar los estatus de requisiciones y ordenes de compra
        if (document.querySelector(".selectEstatusRequisiciones")) {
          var idfuncion= 0;

          // validar el numero de funcion que regresa los estatus que le corresponden
          if ($(".selectEstatusRequisiciones").data("funcion")!= "" && $(".selectEstatusRequisiciones").data("funcion")!=0) {
            idfuncion= $(".selectEstatusRequisiciones").data("funcion");
          }

          //Parametros de seleccion de datos
          dataObj = { 
            option: 'traeEstatusRequisiciones',
            filtro: "requisiciones",
            numeroFuncion: idfuncion
          };

          //Obtener datos 
          $.ajax({
                async: false,
                cache: false,
                method: "POST",
                dataType: "json",
                url: "modelo/componentes_modelo.php",
                data: dataObj
            })

          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion                  
                  dataJson = data.contenido.datos;
                  var contenido = "";

                  //contenido += "<option value='-1' selected>Todos los Estatus...</option>";

                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].estatus+"'>"+dataJson[info].descripcion+"</option>";
                  }
                  $('.selectEstatusRequisiciones').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
          })

          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin URG
        }


        // Componente de tipo combo para mostrar los estatus de requisiciones y ordenes de compra
        if (document.querySelector(".selectAnio")) {
          //Parametros de seleccion de datos
          dataObj = { 
            option: 'mostrarAnios'
          };

          //Obtener datos 
          $.ajax({
                async: false,
                cache: false,
                method: "POST",
                dataType: "json",
                url: "modelo/componentes_modelo.php",
                data: dataObj
            })

          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion                  
                  dataJson = data.contenido.datos;
                  var contenido = "";

                  for (var info in dataJson) {

                    //si es el año actual lo selecciona
                    if ((new Date()).getFullYear() == dataJson[info].id)
                      contenido += "<option selected value='"+dataJson[info].id+"'>"+dataJson[info].descripcion+"</option>";
                    else
                      contenido += "<option value='"+dataJson[info].id+"'>"+dataJson[info].descripcion+"</option>";
                  }
                  $('.selectAnio').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
          })

          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin URG
        }

        if (document.querySelector(".selectmostrarProgramaPresupuestario")) {
          // Inicio URG
          //Opcion para operacion
          dataObj = { 
                  option: 'mostrarProgramasPresupuestario'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].cppt+"'>"+dataJson[info].descripcion+"</option>";
                  }
                  $('.selectmostrarProgramaPresupuestario').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin URG
        }

        if (document.querySelector(".selectRegiones")) {
          // Inicio Regiones
          //Opcion para operacion
          dataObj = { 
                  option: 'mostrarRegion'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  var contenido = "<option value='0'>Seleccionar...</option>";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].regioncode+"'>"+dataJson[info].regionname+"</option>";
                  }
                  $('.selectRegiones').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Regiones
        }

        if (document.querySelector(".selectArea")) {
          // Inicio Areas
          var dataArea = new Array();
          //Opcion para operacion
          dataObj = { 
                  option: 'mostrarArea'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataArea: " + JSON.stringify(dataArea) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].areacode+"'>"+dataJson[info].areaname+"</option>";
                  }
                  $('.selectArea').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Areas
        }

        if (document.querySelector(".selectAlmacen")) {
          // Inicio Almacen
          var dataAlmacen = new Array();
          //Opcion para operacion
          dataObj = { 
                  option: 'mostrarAlmacen'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataAlmacen: " + JSON.stringify(dataAlmacen) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].loccode+"'>"+dataJson[info].locationname+"</option>";
                  }
                  $('.selectAlmacen').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Almacen
        }

        if (document.querySelector(".selectDepartamentos")) {
          // Inicio Departamento
          var dataDepartamento = new Array();
          //Opcion para operacion
          dataObj = { 
                  option: 'mostrarDepartamentos'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataDepartamento: " + JSON.stringify(dataDepartamento) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].u_department+"'>"+dataJson[info].departmentname+"</option>";
                  }
                  $('.selectDepartamentos').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Departamento
        }

        if (document.querySelector(".selectCapitulos")) {
          // Inicio Capitulo  Partida presupuestal
          var dataCapitulo = new Array();
          //Opcion para operacion
          dataObj = { 
                  option: 'mostrarCapitulos'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataJson: " + JSON.stringify(dataJson) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].capituloid+"'>"+dataJson[info].capituloname+"</option>";
                  }
                  $('.selectCapitulos').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Capitulo  Partida presupuestal
        }

        if (document.querySelector(".selectConceptos")) {
          // Inicio Capitulo  Partida presupuestal
          var dataConceptos = new Array();
          //Opcion para operacion
          dataObj = { 
                  option: 'mostrarConceptos'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataJson: " + JSON.stringify(dataJson) );
                  var contenido = "";
                  
                  
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].conceptoid+"'>"+dataJson[info].conceptoname+"</option>";
                    var valueToPush = new Array();
                    valueToPush[0] =dataJson[info].conceptoid;
                    valueToPush[1] =dataJson[info].conceptoname;
                    dataConceptos.push(valueToPush);
                  }
                  $('.selectConceptos').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Capitulo  Partida presupuestal
        }

        if (document.querySelector(".selectGeografico")) {
          // Inicio Capitulo  Partida presupuestal
          var dataCapitulo = new Array();
          //Opcion para operacion
          dataObj = { 
                  option: 'mostrarGeografico'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataJson: " + JSON.stringify(dataJson) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].cg+"'>"+dataJson[info].descripcion+"</option>";
                  }
                  $('.selectGeografico').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Capitulo  Partida presupuestal
        }

        if (document.querySelector(".selectPartidasGenericas")) {
          // Inicio Capitulo  Partida presupuestal
          var dataPartidaGenerica = new Array();
          //Opcion para operacion
          dataObj = { 
                  option: 'mostrarPartidasGenericas'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataJson: " + JSON.stringify(dataJson) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].partidagenericaid+"'>"+dataJson[info].partidagenericaname+"</option>";
                    var valueToPush = new Array();
                    valueToPush[0] =dataJson[info].partidagenericaid;
                    valueToPush[1] =dataJson[info].partidagenericaname;
                    dataPartidaGenerica.push(valueToPush);                     
                  }
                  $('.selectPartidasGenericas').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
            })
            .fail(function(result) {
                // console.log("ERROR");
                // console.log( result );
          });
        }

        if (document.querySelector(".selectProveedor")) {
          // Inicio Finalidad
          
          //Opcion para operacion
          dataObj = { 
                  option: 'mostrarProveedor'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataJson: " + JSON.stringify(dataJson) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].supplierid+"'>"+dataJson[info].proveedordescription+"</option>";
                  }
                  $('.selectProveedor').append(contenido);
                  fnFuncionSelect();
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Finalidad
        }
        
        if (document.querySelector(".selectFinalidad")) {
          // Inicio Finalidad
          var dataFinalidad = new Array();
          //Opcion para operacion
          dataObj = { 
                  option: 'mostrarFinalidad'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataJson: " + JSON.stringify(dataJson) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].id_finalidad+"'>"+dataJson[info].finalidaddescription+"</option>";
                  }
                  //$('.selectFinalidad').empty();
                  $('.selectFinalidad').append(contenido);
                  //$('.selectFinalidad').multiselect('rebuild');
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Finalidad
        }

        // codigo agreado por Arturo Lopez Peña
        if (document.querySelector(".selectTipoDocumentoPagosaProveedores")) {
         
          //Opcion para operacion
          dataObj = { 
                  option: 'tipoDocumentoParaPagosaProveedores'
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
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].id+"'>"+dataJson[info].id+dataJson[info].nombre+"</option>";
                  }
                    contenido += "<option value='otros'>"+"Otros"+"</option>";
                  $('.selectTipoDocumentoPagosaProveedores').append(contenido);
                 
                
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
        
        }

        if (document.querySelector(".selectTipoPagoTesoreria")) {
         
          //Opcion para operacion
          dataObj = { 
                  option: 'tipoPagoTesoreria'
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
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.TiposPago;
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].id+"'>"+dataJson[info].nombre+"</option>";
                  }
                   
                 $('.selectTipoPagoTesoreria').append(contenido);
                
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
        
        }


          if (document.querySelector(".selectMeses")) {
              var meses={'01':'Enero','02':'Febrero','03':'Marzo',
                  '04':'Abril','05':'Mayo','06':'Junio',
                  '07':'Julio','08':'Agosto','09':'Septiembre',
                  '10':'Octubre','11':'Noviembre','12':'Diciembre' };

            for (var i in meses)
                  {
                    if (meses.hasOwnProperty(i)) 
                    {
                      
                    contenido += "<option value='"+i+"'>"+meses[i]+"</option>";
                    }

                  }
                   
                 $('.selectMeses').append(contenido);
          /*dataObj = { 
                  option: 'mostrarMeses'
                };
          $.ajax({
               
                method: "POST",
                dataType:"json",
                async:false,
                cache:false,
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              if(data.result)
              {
                
                  datos = data.contenido.Meses;
                  //alert(datos);
                  var contenido = "";
                  for (var i in datos)
                  {
                    if (datos.hasOwnProperty(i)) 
                    {
                      
                    contenido += "<option value='"+i+"'>"+datos[i]+"</option>";
                    }

                  }
                   
                 $('.selectMeses').append(contenido);
                
              }
          })
          .fail(function(result) {
               console.log("ERROR");
               console.log( result );
          }); */
        
        }
        // fin codigo agregado por Arturo Lopez Peña */

        if (document.querySelector(".selectFuncion")) {
          // Inicio Función
          var dataFuncion = new Array();
          //Opcion para operacion
          dataObj = { 
                  option: 'mostrarFuncion'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataJson: " + JSON.stringify(dataJson) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].id_funcion+"'>"+dataJson[info].funciondescription+"</option>";
                  }
                  $('.selectFuncion').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Función
        }

        

        function fnCargarPartidaProducto(periodo,tagref){
          //Opcion para operacion
          dataObj = { 
                  option: 'mostrarPartidaEspecificaProductos',
                  tagref: tagref,
                  periodo: periodo
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataJson: " + JSON.stringify(dataJson) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].partidacalculada+"'>"+dataJson[info].partidacalculada+"</option>";
                    contenidosCvePartidaEspecifica += "<option value='"+dataJson[info].partidacalculada+"'>"+dataJson[info].partidacalculada+"</option>";
                    contenidoDescPartidaEspecifica += "<option value='"+dataJson[info].partidadescripcion+"'>"+dataJson[info].partidadescripcion+"</option>";
                    contenidoPartidaEspecificaTagref = dataJson[info].tagref;
                    contenidoPartidaEspecificaCvePresupuestal += "<option value='"+dataJson[info].clavePresupuestal+"'>"+dataJson[info].clavePresupuestal+"</option>";
                  }
                  $('.selectCvePartidaEspecifica').append(contenidosCvePartidaEspecifica);
                  $('.selectDescPartidaEspecifica').append(contenidoDescPartidaEspecifica);
                  $('.selectPartidaEspecificaCvePresupuestal').append(contenidoPartidaEspecificaCvePresupuestal);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
        }

        var contenidoDescPartidaEspecifica = "";
        var contenidosCvePartidaEspecifica = "";
        var contenidoPartidaEspecificaTagref = "";
        var contenidoPartidaEspecificaCvePresupuestal = "";
        if ((document.querySelector(".selectCvePartidaEspecifica")) || (document.querySelector(".selectDescPartidaEspecifica")) || (document.querySelector(".selectPartidaEspecificaCvePresupuestal"))) {
          // Inicio Finalidad
          fnCargarCvePartida();
          // Fin Finalidad
        }

        function fnCargarPartidaServicio(periodo,tagref){

          //Opcion para operacion
          dataObj = { 
                  option: 'mostrarPartidaEspecificaServicios',
                  tagref: tagref,
                  periodo: periodo
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataJson: " + JSON.stringify(dataJson) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].partidacalculada+"'>"+dataJson[info].partidacalculada+"</option>";
                    contenidosCvePartidaEspecificaServ += "<option value='"+dataJson[info].partidacalculada+"'>"+dataJson[info].partidacalculada+"</option>";
                    contenidoDescPartidaEspecificaServ += "<option value='"+dataJson[info].partidacalculada+"'>"+dataJson[info].partidadescripcion+"</option>";
                    contenidoPartidaEspecificaTagrefServ = dataJson[info].tagref;
                    contenidoPartidaEspecificaCvePresupuestalServ += "<option value='"+dataJson[info].clavePresupuestal+"'>"+dataJson[info].clavePresupuestal+"</option>";
                  }
                  $('.selectCvePartidaEspecificaServ').append(contenidosCvePartidaEspecificaServ);
                  $('.selectDescPartidaEspecificaServ').append(contenidoDescPartidaEspecificaServ);
                  $('.selectPartidaEspecificaCvePresupuestalServ').append(contenidoPartidaEspecificaCvePresupuestalServ);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
        }

        var contenidoDescPartidaEspecificaServ = "";
        var contenidosCvePartidaEspecificaServ = "";
        var contenidoPartidaEspecificaTagrefServ = "";
        var contenidoPartidaEspecificaCvePresupuestalServ = "";
        if ((document.querySelector(".selectCvePartidaEspecificaServ")) || (document.querySelector(".selectDescPartidaEspecificaServ")) || (document.querySelector(".selectPartidaEspecificaCvePresupuestalServ"))) {
          // Inicio Finalidad
          fnCargarCvePartida();
          // Fin Finalidad
        }


        function fnCargarCvePartidaCveArticulo(){

          //Opcion para operacion
          dataObj = { 
                  option: 'mostrarPartidaEspecificaCveArticulo'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataJson: " + JSON.stringify(dataJson) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].idPartidaEspecifica+"'>"+dataJson[info].idPartidaEspecifica+"</option>";
                    contenidosCvePartidaEspecificaArt += "<option value='"+dataJson[info].idPartidaEspecifica+"'>"+dataJson[info].idPartidaEspecifica+"</option>";
                    contenidosCvePartidaEspecificaCveArt += "<option value='"+dataJson[info].idProducto+"'>"+dataJson[info].idProducto+"</option>";
                    contenidosCvePartidaEspecificaDescArt += "<option value='"+dataJson[info].descripcionProducto+"'>"+dataJson[info].descripcionProducto+"</option>";

                    
                  }
                  $('.selectCvePartidaEspecifica').append(contenidosCvePartidaEspecificaArt);
                  $('.selectCveArticulo').append(contenidosCvePartidaEspecificaCveArt);
                  $('.selectArticulo').append(contenidosCvePartidaEspecificaDescArt);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
        }

        var contenidosCvePartidaEspecificaArt = "";
        var contenidosCvePartidaEspecificaCveArt = "";
        var contenidosCvePartidaEspecificaDescArt = "";
        if (document.querySelector(".selectCvePartidaEspecifica")){
          // Inicio 
          fnCargarCvePartidaCveArticulo();
          // Fin 
        }

        function fnCargarServicios() {
          //Opcion para operacion
          dataObj = { 
                  option: 'mostrarServicio'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataJson: " + JSON.stringify(dataJson) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].stockid+"'>"+dataJson[info].serviciodescription+"</option>";
                    contenidoDescServi += "<option value='"+dataJson[info].serviciodescription+"'>"+dataJson[info].serviciodescription+"</option>";
                    contenidosCveServi += "<option value='"+dataJson[info].stockid+"'>"+dataJson[info].stockid+"</option>";
                    contenidoPEServi= dataJson[info].precioservicioestimado;
                  }
                  $('.selectServicio').append(contenido);
                  $('.selectCveServicio').append(contenidosCveServi);
                  $('.selectDescServicio').append(contenidoDescServi);
                  $('.addedPEServicio').append(contenidoPEServi);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) ); 
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          
        }

        var contenidoDescServi = "";
        var contenidosCveServi = "";
        var contenidoPEServi = "";
        if (document.querySelector(".selectServicio")){
          // Inicio Función
          fnCargarServicios();
          // Fin Función
        }

        if (document.querySelector(".selectActivoInactivo")) {
          // Inicio Función
          var contenido = "<option value='1'>Activo</option>";
          contenido += "<option value='0'>Inactivo</option>";
          $('.selectActivoInactivo').append(contenido);
          // Fin Función
        }
        // Fin Json para Multiselect

        // Funcion para validar entrada del RFC
        $(".fnRFC").on("input", function(){
          var rfc= this.value.trim().toUpperCase();

          if (rfc.length>=12){
            var rfcCorrecto = rfcValido(rfc);   // se comprueba el RFC

            if (rfcCorrecto) {
              //muestraMensaje(rfcCorrecto, 1, "lblMensajeRFC");
            } else {
              muestraMensaje("El RFC es incorrecto... Verifique !!!", 3, "lblMensajeRFC");
            }
          }
        });

        function fnFuncionSelect(){
          $('.selectRazonSocial, .selectUnidadNegocio, .selectRegiones, .selectArea, .selectAlmacen, .selectDepartamentos, .selectCapitulos, .selectConceptos, .selectGeografico, .selectPartidasGenericas, .selectCvePartidaEspecifica, selectDescPartidaEspecifica, .selectPartidaEspecificaCvePresupuestal, .selectCvePartidaEspecificaServ, selectDescPartidaEspecificaServ, .selectPartidaEspecificaCvePresupuestalServ, .selectFinalidad, .selectFuncion, .selectServicio, .selectDescServicio, .selectCveArticulo, .selectArticulo, .selectProveedor, .selectTipoDocumentoPagosaProveedores,.selectTipoPagoTesoreria, .selectMeses, .selectTipoDocumentoAdecuaciones, .selectActivoInactivo, .selectmostrarProgramaPresupuestario, .selectEstatusAdecuaciones, .selectTipoAdecuacionReduccion, .selectEstatusRequisiciones, selectAnio, .selectConfiguracionClave, .selectRamo, .selectTipoSolicitud, .selectGeneral').multiselect({
             enableFiltering: true,
             filterBehavior: 'value',
             buttonWidth: '100%',
             numberDisplayed: 1,
             includeSelectAllOption: true
          });

          // Estilos para el diseño del select
          $('.multiselect-container').css({ 'max-height': "200px" });
          $('.multiselect-container').css({ 'overflow-y': "scroll" });
        }

        // Confiruación Multiselect
        //setTimeout(function (){
          fnFuncionSelect();
          ocultaCargandoGeneral();
        //}, 2000);

        $("#ModalGeneral").draggable({
          handle: ".modal-dialog"
        });
        $("#ModalUR").draggable({
          handle: ".modal-dialog"
        });
        $("#ModalUREliminar").draggable({
          handle: ".modal-dialog"
        });
    </script>
    
  </body>
</html>