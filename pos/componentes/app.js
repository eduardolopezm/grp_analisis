// prueba subida por problema con gitlab eliminar despues
import React from 'react';
import update from 'react-addons-update';
import TablaProductos from './tabla-productos';
import TablaAnticipo from './tabla-anticipo';
import BuscarProductos from './buscar-productos';
import Carrito from './carrito';
import InformacionGeneral from './informacion-general';
import CustomAlert from './custom-alert';
import ModalPago from './modal-pago';
import ModalCambio from './modal-cambio';
import ModalTraspaso from './modal-traspaso';
// import ModalAnticipo from './modal-anticipo';
import ModalCotizadorRapido from './modal-cotizador-rapido';
import axios from 'axios';
import ReactTimeout from 'react-timeout'
////
import ComboGeneral from './combo-general';

var Tooltip = require('react-bootstrap').Tooltip;
var OverlayTrigger = require('react-bootstrap').OverlayTrigger;

var Checkbox = require('react-bootstrap').Checkbox;

const tooltipCambiarCliente = (
  <Tooltip id="tooltip">Cambiar Contribuyente</Tooltip>
);

const tooltipAgregarCliente = (
    <Tooltip id="tooltip">Agregar Contribuyente</Tooltip>
);

const tooltipModificarCliente = (
    <Tooltip id="tooltip">Modificar Contribuyente</Tooltip>
);

const factura = 110,
    remision = 119,
    cotizacion = 0;

var App = React.createClass ({

    getInitialState: function() {

        return {
            alerta: {
                mostrar: false,
                title: '',
                body: ''
            },
            bsSize: 'sm',
            btnAlertaAceptar: false,
            mostrarPago: false,
            mostrarCambio: false,
            mostrarTraspaso: false,
            mostrarAnticipo: false,
            mostrarCotizadorRapido: false,
            mostrarInfo: true,
            mostrarBusquedaCliente: false,
            mostrarBusquedaCotizacion: false,
            orderno: '',
            tagref: '1',
            tagrefname: '',
            location: '',
            locationname: '',
            currency: '',
            username: '',
            userid: '',
            paymentterm: '',
            paymentmethod: '',
            paymentReferencia: '',
            salesman: '',
            salesmanname: '',
            typeabbrev: '',
            sales_type: '',
            //Cliente generico
            cliente: {
                branchcode: '1846',
                name: 'CLIENTE MOSTRADOR',
                taxid: 'XEXX010101000',
                email: 'correo',
                address: '5  DE FEBRERO N.100 COL. LAS CAMPANAS, QUERETARO, QUERETARO'
            },
            modal: {
                titulo: '',
            },
            productos: [], //Productos del pedido
            productosCotizadorRapido: [], //Productos del cotizador rapido
            productosCotizadorRapidoSeleccion: [], //Productos seleccionados del cotizador rapido
            totales: {
                subtotal: 0.00,
                iva: 0.00,
                total: 0.00
            },
            totalGeneralIngreso: 0,
            totalAnticipo: 0,
            productoDescripcion: "",
            almacenesTraspaso: [],
            referenciasTraspaso: [],
            anticipoCliente: [],
            permisoPrecio: true,
            permisoDescuento: true,
            descuentoMaximo: 0,
            currencydenominations: [],
            imgCargandoMostrar: "none",
            modificarclienteB: false,
            modificarclienteBPro: false,
            modificarclienteBNum: 0,
            mensajeUnnomCotizacionVentaidadAlmacen: 0,
            numiframeRecibos: 0,
            btnFacturaDisabled: false,
            btnCotizacionDisabled: false,
            version: '1.0.9', //Version del bundle
            nomVentanaPuntoVenta: 'Punto de Venta',
            nombreEmpresa: 'Empresa',
            nomClienteVenta: 'Cliente',
            operacionIva: 0.16,
            nomAlmacenVenta: 'Almacén',
            nomAlmacenVentaPlural: 'Almacenes',
            nomArticuloVenta: 'Artículo',
            nomArticuloVentaPlural: 'Artículos',
            nomUniNegVenta: 'Unidad de Negocio',
            nomUniNegVentaPlural: 'Unidades de Negocio',
            nomCotizacionVenta: 'Cotización',
            versionmsj: "", //Mensaje de la version actual
            validacionemailmsj: "",
            disabledCheckCorreo: false,
            banderaEnviarCorreo: false, //1 enviar correo , 0 no envia
            transnoTraspaso: "",
            RutaImpresionValeTraspaso: "#",
            disabledImpresionValeTraspaso: true,
            mensajeImprimirValeTraspaso: "",
            //Enviar cotizacion
            envCor_Msj: "",
            envCor_UrlSimple: "",
            envCor_MsjSimple: "",
            envCor_Url: "",
            envCor_MsjUrl: "",
            envCor_UrlEnvio: "",
            //Enviar Factura
            envFac_Url: "",
            comments: "",
            txtPagadorGen: "",
            labelSelectVendedor : "Vendedor",
            labelSelectUnidadNegocio : "Unidades de Negocio",
            labelSelectAlmacen : "Almacenes",
            NumRow: 1,
            TablaCotizadorRapido: "", // Tabla del los productos del cotizador rapido,
            DescripcionCotizadorRapido: "",
            SelectListaPrecios: "",
            tipoComprobante: "",
            tipoComprobanteName: "",
            usoCFDI: "",
            usoCFDIName: "",
            metodoPago: "",
            metodoPagoName: "",
            claveConfirmacion: "",
            versionFactura: "3.3",
            valBtnPagar: false,
            valBtnCotizacion: false,
            valBloquearOperacion: false,
            valBloquearOperacion2: true,
            dataBase: ""
        };
    },

    componentDidMount: function() {

        var self = this;

        //Obtener si existe sesion iniciada
        axios.get('./../mvc/api/v1/getsession')
        .then(function (response) {
            if(response.data.success) {
                //si hay sesion realiza operacion
                //console.log("Tiene sesion");

                axios.get('./../mvc/api/v1/getdefaultsalesorders')
                .then(function (response) {
                    if(response.data.success) {
                        var datosIniciales = response.data.data[0];
                        self.setState({
                            tagref: datosIniciales.tagref,
                            //tagrefname: datosIniciales.tagrefname,
                            location: datosIniciales.location,
                            //locationname: datosIniciales.locationname,
                            currency: datosIniciales.currency,
                            username: datosIniciales.username,
                            userid: datosIniciales.userid,
                            cliente:{
                                branchcode: datosIniciales.branchcode,
                                name: datosIniciales.name,
                                taxid: datosIniciales.taxid,
                                email: datosIniciales.email,
                                address: datosIniciales.address,
                            },
                            permisoPrecio: datosIniciales.permisoPrecio,
                            permisoDescuento: datosIniciales.permisoDescuento,
                            descuentoMaximo: datosIniciales.descuentoMaximo,
                            //version: datosIniciales.version
                            salesman: datosIniciales.salesmancode,
                            salesmanname: datosIniciales.salesmanname,
                            versionFactura: datosIniciales.versionFactura,
                            nomVentanaPuntoVenta: datosIniciales.nomVentanaPuntoVenta,
                            nombreEmpresa: datosIniciales.nombreEmpresa,
                            nomClienteVenta: datosIniciales.nomClienteVenta,
                            operacionIva: datosIniciales.operacionIva,
                            nomAlmacenVenta: datosIniciales.nomAlmacenVenta,
                            nomAlmacenVentaPlural: datosIniciales.nomAlmacenVentaPlural,
                            labelSelectAlmacen: datosIniciales.nomAlmacenVentaPlural,
                            nomArticuloVenta: datosIniciales.nomArticuloVenta,
                            nomArticuloVentaPlural: datosIniciales.nomArticuloVentaPlural,
                            nomUniNegVenta: datosIniciales.nomUniNegVenta,
                            nomUniNegVentaPlural: datosIniciales.nomUniNegVentaPlural,
                            labelSelectUnidadNegocio: datosIniciales.nomUniNegVentaPlural,
                            nomCotizacionVenta: datosIniciales.nomCotizacionVenta,
                            tipoComprobante: datosIniciales.tipoComprobante,
                            tipoComprobanteName: datosIniciales.tipoComprobanteName,
                            valBtnPagar: datosIniciales.valBtnPagar,
                            valBtnCotizacion: datosIniciales.valBtnCotizacion,
                            dataBase: datosIniciales.dataBase
                        });

                        if (self.state.version != datosIniciales.version) {
                            //si no es misma version muestra mensaje
                            self.setState({
                                versionmsj: datosIniciales.versionmsj
                            });
                        }

                        var tester = /^[-!#$%&'*+\/0-9=?A-Z^_a-z{|}~](\.?[-!#$%&'*+\/0-9=?A-Z^_a-z`{|}~])*@[a-zA-Z0-9](-?\.?[a-zA-Z0-9])*\.[a-zA-Z](-?[a-zA-Z0-9])+$/;
                        if (!tester.test(datosIniciales.email)) {
                            self.setState({
                                validacionemailmsj: "E-mail Invalido"
                            });
                        } else {
                            self.setState({
                                validacionemailmsj: ""
                            });
                        }

                        if (datosIniciales.taxid == "XEXX010101000" || datosIniciales.taxid == "XAXX010101000") {
                            //Deshabilitar enviar por correo
                            self.setState({
                                disabledCheckCorreo: true
                            });
                        }

                        if (datosIniciales.branchcode == '') {
                            // Si no tiene configurado una area, se agrega para obtener lista de precios por default
                            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                            var body = ''+datosIniciales.msjAreaConfigurada;
                            // self.abrirAlerta(title, body);
                            datosIniciales.branchcode = 'SinCliente';
                        }
                        
                        self.ObtenerListasPrecios(datosIniciales.branchcode);

                        // console.log("tagref: "+datosIniciales.tagref);
                        // console.log("tagrefname: "+datosIniciales.tagrefname);
                        // console.log("location: "+datosIniciales.location);
                        // console.log("locationname: "+datosIniciales.locationname);
                        // console.log("currency: "+datosIniciales.currency);
                        // console.log("username: "+datosIniciales.username);
                        // console.log("userid: "+datosIniciales.userid);
                        // console.log("branchcode: "+datosIniciales.branchcode);
                        // console.log("name: "+datosIniciales.name);
                        // console.log("taxid: "+datosIniciales.taxid);
                        // console.log("email: "+datosIniciales.email);
                        // console.log("permisoPrecio: "+datosIniciales.permisoPrecio);
                        // console.lgo("permisoDescuento: "+datosIniciales.permisoDescuento);
                        // console.log("descuentoMaximo: "+datosIniciales.descuentoMaximo);
                        // console.log("version bd: "+datosIniciales.version);
                        // console.log("salesman: "+datosIniciales.salesmancode);
                        // console.log("salesmanname: "+datosIniciales.salesmanname);
                    } else {
                        var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                        var body = "Error al cargar la informacion por default, intenta recargar la pagina.";
                        self.abrirAlerta(title, body);
                    }
                })
                .catch(function (error) {
                    var err = '';
                    if (error.response) {
                        err = error.response.status;
                    } else {
                        err = error.message;
                    }
                    var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                    var body = "Ocurrio un error en la peticion: " + err;
                    self.abrirAlerta(title, body);
                });

                return true;
            } else {
                //si no hay sesion no realiza la operacion
                //console.log("No tiene sesion");
                var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                var body = <div>
                                <p>Sesion actual terminada, iniciar sesion nuevamente</p>
                                <p>Al iniciar sesion regresa a esta pestaña y da click en aceptar</p>
                                <p><a href="../../ap_grp_demo/" target="_blank">Iniciar Sesion</a></p>
                            </div>;
                self.abrirAlerta(title, body);
                return false;
            }
        })
        .catch(function (error) {
            console.log("Error Obtener Sesion");
            //si sale error no realiza la operacion
            var err = '';
            if (error.response) {
                err = error.response.status;
            } else {
                err = error.message;
            }
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = "Ocurrio un error en la peticion: " + err;
            self.abrirAlerta(title, body);
            return false;
        });
    },

    cerrarAlerta: function() {
        this.setState({
            alerta: {
                mostrar: false,
                title: '',
                body: ''
            },
            bsSize: 'sm'
        });

        if (this.state.envFac_Url != "") {
            // console.log("Enviar Correo Factura");
            //Cargar datos con iframe para envio de correo, envFac_Url
            var ruta = this.state.envFac_Url;
            var title = <p><i className="fa fa-check-circle text-success" aria-hidden="true"></i> Exito</p>;
            var body = <div>
                    <iframe className="" src={ruta} width="100%" height="40" frameBorder="0"></iframe>
                </div>;
            this.setState({
                alerta: {
                    mostrar: false,
                    title: title,
                    body: body,
                    bsSize: 'sm'
                },
                envFac_Url: ""
            });
        }
    },

    abrirAlerta: function(title, body) {
        this.setState({
            alerta: {
                mostrar: true,
                title: title,
                body: body,
                bsSize: this.state.bsSize
            }
        });
    },

    abrirPago: function() {

        var self = this;

        if(this.state.cliente.branchcode == ""){
            //si no selecciono cliente
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = "Seleccionar un "+this.state.nomClienteVenta+" para poder ralizar la "+documento;
            this.abrirAlerta(title, body);

            return false;
        }

        if (this.state.productos.length > 0) {

            //Obtener si existe sesion iniciada
            axios.get('./../mvc/api/v1/getsession')
            .then(function (response) {
                if(response.data.success) {
                    //si hay sesion realiza operacion
                    //console.log("Tiene sesion");

                    // Obtener metodo de pago
                    axios.get('./../mvc/api/v1/getlocationpayment/'+self.state.location)
                    .then(function (response) {
                        if(response.data.success) {
                            // console.log("info: "+JSON.stringify(response.data.data));
                            // console.log("metodoPago: "+response.data.data[0].metodoPago);
                            // console.log("metodoPagoName: "+response.data.data[0].metodoPagoName);
                            self.setState({
                                mostrarPago: true,
                                metodoPago: response.data.data[0].metodoPago,
                                metodoPagoName: response.data.data[0].metodoPagoName
                            });
                            
                            return true;
                        } else {
                            //si no hay sesion no realiza la operacion 
                            //console.log("No tiene sesion");
                            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                            var body = "Ocurrio un error al obtener el Método de Pago del "+self.state.nomAlmacenVenta;
                            self.abrirAlerta(title, body);
                            return false;

                        }
                    })
                    .catch(function (error) {
                        console.log("Error Obtener Metod de Pago");
                        //si sale error no realiza la operacion
                        var err = '';
                        if (error.response) {
                            err = error.response.status;
                        } else {
                            err = error.message;
                        }
                        var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                        var body = "Ocurrio un error en la peticion: " + err;
                        self.abrirAlerta(title, body);
                        return false;
                    });

                    return true;
                } else {
                    //si no hay sesion no realiza la operacion 
                    //console.log("No tiene sesion");
                    var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                    var body = <div>
                                    <p>Sesion actual terminada, iniciar sesion nuevamente</p>
                                    <p>Al iniciar sesion regresa a esta pestaña y da click en aceptar</p>
                                    <p><a href="../../ap_grp_demo/" target="_blank">Iniciar Sesion</a></p>
                                </div>;
                    self.abrirAlerta(title, body);
                    return false;
                }
            })
            .catch(function (error) {
                console.log("Error Obtener Sesion");
                //si sale error no realiza la operacion
                var err = '';
                if (error.response) {
                    err = error.response.status;
                } else {
                    err = error.message;
                }
                var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                var body = "Ocurrio un error en la peticion: " + err;
                self.abrirAlerta(title, body);
                return false;
            });

        } else {
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = "No tienes "+self.state.nomArticuloVentaPlural+" agregados.";
            self.abrirAlerta(title, body);
        }
    },

    abrirTraspaso: function (stockidTraspaso){     
        //Recibe numero de renglon
        var self = this;

        if (this.state.productos.length > 0) {
            //Descripcion articulo traspaso
            var descriptionTraspaso = "";
            var obj = this.state.productos.filter(function(element, index, array) {
                if(element.NumRow === stockidTraspaso) {
                    descriptionTraspaso = element.stockid+" "+element.description+" $"+element.price;
                    stockidTraspaso = element.stockid;
                }
                return element;
            });

            //Obtener si existe sesion iniciada
            axios.get('./../mvc/api/v1/getsession')
            .then(function (response) {
                if(response.data.success) {
                    //si hay sesion realiza operacion
                    //console.log("Tiene sesion");

                    axios.get('./../mvc/api/v1/getstockbylocation/'+stockidTraspaso+'/'+self.state.location+'/1')
                    .then(function (response) {

                        if(response.data.success) {
                            //mostrar modal traspaso con articulos
                            self.setState({
                                mostrarTraspaso: true,
                                productoDescripcion: descriptionTraspaso,
                                almacenesTraspaso: response.data.data
                            }); 
                        } else {
                            //si no trae informacion mostrar mensaje
                            self.setState({
                                productoDescripcion: "",
                                almacenesTraspaso: []
                            });
                            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                            var body = self.state.nomArticuloVenta+" "+stockidTraspaso+" no disponible en otro "+self.state.nomAlmacenVenta;
                            self.abrirAlerta(title, body);
                        }
                    })
                    .catch(function (error) {
                        //Mostrar error
                        var err = '';
                        if (error.response) {
                            err = error.response.status;
                        } else {
                            err = error.message;
                        }
                        var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                        var body = "Ocurrio un error en la peticion: " + err;
                        self.abrirAlerta(title, body);
                    });

                    return true;
                } else {
                    //si no hay sesion no realiza la operacion
                    //console.log("No tiene sesion");
                    var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                    var body = <div>
                                    <p>Sesion actual terminada, iniciar sesion nuevamente</p>
                                    <p>Al iniciar sesion regresa a esta pestaña y da click en aceptar</p>
                                    <p><a href="../../ap_grp_demo/" target="_blank">Iniciar Sesion</a></p>
                                </div>;
                    self.abrirAlerta(title, body);
                    return false;
                }
            })
            .catch(function (error) {
                console.log("Error Obtener Sesion");
                //si sale error no realiza la operacion
                var err = '';
                if (error.response) {
                    err = error.response.status;
                } else {
                    err = error.message;
                }
                var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                var body = "Ocurrio un error en la peticion: " + err;
                self.abrirAlerta(title, body);
                return false;
            });

        } else {
            //si seleccion de articulos
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = "No tienes "+this.state.nomArticuloVentaPlural+" agregados.";
            this.abrirAlerta(title, body);
        }
    },

    abrirAnticipo: function (){

        if(this.state.tagref == "") {
            // No realizar el proceso si no se ha seleccionado
            return false;
        }

        this.setState({
            mostrarAnticipo: true,
        });

        // Obtener anticipos
        var self = this;

        //Obtener si existe sesion iniciada
        axios.get('./../mvc/api/v1/getsession')
        .then(function (response) {
            if(response.data.success) {
                //si hay sesion realiza operacion
                //console.log("Tiene sesion");
                axios.get('./../mvc/api/v1/getAnticiposCliente/'+self.state.cliente.branchcode+'/'+self.state.tagref+'/'+self.state.currency)
                .then(function (response) {

                    if(response.data.success) {
                        //mostrar modal traspaso con articulos
                        console.log("anticipoCliente: "+JSON.stringify(response.data.data));
                        if (response.data.data.length > 0) {
                            self.setState({
                                mostrarAnticipo: true,
                                anticipoCliente: response.data.data
                            }); 
                        } else {
                            self.setState({
                                anticipoCliente: []
                            });
                        }
                    } else {
                        //si no trae informacion mostrar mensaje
                        self.setState({
                            anticipoCliente: []
                        });
                        var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                        var body = "Ocurrio un error al obtener Anticipos del "+self.state.nomClienteVenta;
                        self.abrirAlerta(title, body);
                    }
                })
                .catch(function (error) {
                    //Mostrar error
                    var err = '';
                    if (error.response) {
                        err = error.response.status;
                    } else {
                        err = error.message;
                    }
                    var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                    var body = "Ocurrio un error en la peticion: " + err;
                    self.abrirAlerta(title, body);
                });

                return true;
            } else {
                //si no hay sesion no realiza la operacion
                //console.log("No tiene sesion");
                var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                var body = <div>
                                <p>Sesion actual terminada, iniciar sesion nuevamente</p>
                                <p>Al iniciar sesion regresa a esta pestaña y da click en aceptar</p>
                                <p><a href="../../ap_grp_demo/" target="_blank">Iniciar Sesion</a></p>
                            </div>;
                self.abrirAlerta(title, body);
                return false;
            }
        })
        .catch(function (error) {
            console.log("Error Obtener Sesion");
            //si sale error no realiza la operacion
            var err = '';
            if (error.response) {
                err = error.response.status;
            } else {
                err = error.message;
            }
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = "Ocurrio un error en la peticion: " + err;
            self.abrirAlerta(title, body);
            return false;
        });
    },

    cerrarPago: function() {
        this.setState({
            mostrarPago: false
        });
    },

    cerrarCambio: function() {
        this.setState({
            mostrarCambio: false
        });
    },

    cerrarTraspaso: function() {
        //cerrar modal traspaso
        this.setState({
            mostrarTraspaso: false
        });
    },

    cerrarAnticipo: function() {
        //cerrar modal traspaso
        this.setState({
            mostrarAnticipo: false
        });
    },

    cerrarCotizadorRapido: function() {
        //cerrar modal traspaso
        this.setState({
            mostrarCotizadorRapido: false
        });
    },

    mostrarBusquedaClientes: function() {
        this.setState({
            mostrarInfo: this.state.mostrarBusquedaCliente,
            mostrarBusquedaCliente: !this.state.mostrarBusquedaCliente,
            mostrarBusquedaCotizacion: false,
        });
    },

    mostrarAgregarClientes: function(){
        //Enlace a agregar cliente
        this.setState({
            mostrarInfo: this.state.mostrarBusquedaCliente,
            mostrarBusquedaCliente: !this.state.mostrarBusquedaCliente,
            mostrarBusquedaCotizacion: false,
        });

        var agregarcliente = document.getElementById("agregarcliente");
        agregarcliente.click();
    },

    modificarClientes: function(){ 
        //Enlace a modificar el cliente
        var modificarcliente = document.getElementById("modificarcliente");
        var DebtorNo = this.state.cliente.branchcode;
        modificarcliente.href = "../abcContribuyente.php?DebtorNo="+DebtorNo;
        modificarcliente.click();

        this.setState({
            modificarclienteB: true,
            modificarclienteBPro: false,
            modificarclienteBNum: 0
        });
    },

    mostrarBusquedaCotizaciones: function() {
        this.setState({
            mostrarInfo: false,
            mostrarBusquedaCotizacion: !this.state.mostrarBusquedaCotizacion,
            mostrarBusquedaCliente: false,
        });
    },

    ocultarBusquedaClientes: function () {
        this.setState({
            mostrarInfo: !this.state.mostrarInfo,
            mostrarBusquedaCliente: !this.state.mostrarBusquedaCliente,
        });
    },

    ocultarBusquedaCotizaciones: function () {
        this.setState({
            mostrarInfo: !this.state.mostrarInfo,
            mostrarBusquedaCotizacion: !this.state.mostrarBusquedaCotizacion,
        });
    },

    compararRecibos: function(){
        //obtener el veces recargo el iframe de recibos
        var num = this.state.numiframeRecibos;
        console.log("numiframeRecibos 1: "+this.state.numiframeRecibos);
        num = Number(num) + 1;
        this.setState({
            numiframeRecibos: num
        });

        console.log("numiframeRecibos 2: "+this.state.numiframeRecibos);

        if (this.state.numiframeRecibos > 1) {
            //Ya agrego recibos
            this.setState({
                numiframeRecibos: 1,
                btnAlertaAceptar: false
            });
        }

        // this.abrirAnticipo();
    },

    crearDocumento: function (documento, documentono, realizarProceso = 0) {

        var etiquetaProceso = documento;
        if (etiquetaProceso == 'cotizacion') {
            etiquetaProceso = this.state.nomCotizacionVenta;
        }
        
        // console.log("tagref: "+this.state.tagref);
        var datoInfo = this.state.tagref.indexOf("_99_"); // returns -1
        // console.log("datoInfo: "+datoInfo);
        
        if(this.state.tagref == "" || this.state.location == "" || datoInfo == '-1'){
            //si no selecciono unidad de negocio o almacen
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            
            var body = "Seleccionar "+this.state.nomUniNegVenta+" y/o "+this.state.nomAlmacenVenta+" para poder ralizar "+etiquetaProceso;
            this.abrirAlerta(title, body);

            return false;
        }

        // if (this.state.salesman == "") {
        //     //si no selecciono unidad de negocio o almacen
        //     var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
        //     var body = "Seleccionar vendedor para poder ralizar "+documento;
        //     this.abrirAlerta(title, body);

        //     return false;
        // }

        if(this.state.imgCargandoMostrar == "block"){
            return false;
        }

        if(this.state.cliente.branchcode == ""){
            //si no selecciono cliente
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = "Seleccionar un "+this.state.nomClienteVenta+" para poder ralizar "+etiquetaProceso;
            this.abrirAlerta(title, body);

            return false;
        }

        var valCantPrecio = 0;
        // console.log("valCantPrecio: "+valCantPrecio);
        for (var info in this.state.productos) {
            console.log(this.state.productos[info].quantity+" - "+this.state.productos[info].price);
            if(Number(this.state.productos[info].quantity) <= 0 || this.state.productos[info].quantity == null){
                if (this.state.productos[info].disminuye_ingreso == Number(0)) {
                    valCantPrecio = 1;
                }
            }
            if(Number(this.state.productos[info].price) <= 0 || this.state.productos[info].price == null){
                valCantPrecio = 1;
            }
            
            if (this.state.productos[info].stockid == 'TRAN_0058') {
                valCantPrecio = 0;
            }
        }
        // console.log("valCantPrecio antes if: "+valCantPrecio);
        if(valCantPrecio == 1){
            //si no selecciono cliente
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = "La Cantidad y/o Precio no pueden ir en 0 y/o vacío";
            this.abrirAlerta(title, body);

            return false;
        }

        // validar rangos de precio en lista
        var valCantPrecioRango = 0;
        // console.log("valCantPrecio: "+valCantPrecio);
        var numReglon = 1;
        for (var info in this.state.productos) {
            // console.log(this.state.productos[info].quantity+" - "+Number(this.state.productos[info].price));

            var listasPrecio2 = this.state.productos[info].listasPrecio;
            // console.log("listasPrecio: "+JSON.stringify(listasPrecio2));

            if ((Number(this.state.productos[info].idcontrato) == Number(0)) && (this.state.productos[info].stockid != 'TRAN_0058')) {
                for (var info2 in listasPrecio2) {
                    if (listasPrecio2[info2].typeabbrev == this.state.productos[info].salestype) {
                        // console.log("validar "+this.state.productos[info].salestype);
                        // console.log("norango_inicial "+listasPrecio2[info2].norango_inicial);
                        // console.log("norango_final "+listasPrecio2[info2].norango_final);

                        if (listasPrecio2[info2].isRango == 1) {
                            // Validar por rango
                            if(
                                (Number(this.state.productos[info].price) < Number(listasPrecio2[info2].norango_inicial))
                                || 
                                (Number(this.state.productos[info].price) > Number(listasPrecio2[info2].norango_final))
                                ){
                                // Si el precio es fuera de rango
                                var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                                var body = "En el renglon "+numReglon+" el precio "+this.state.productos[info].price+" esta fuera de rango. Rango Inicial "+listasPrecio2[info2].norango_inicial+", Rango Final "+listasPrecio2[info2].norango_final;
                                this.abrirAlerta(title, body);

                                return false;
                            }
                        } else {
                            // Validar por lista de precio
                            if((Number(this.state.productos[info].price) != Number(listasPrecio2[info2].price))){
                                // Si el precio es fuera de rango
                                var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                                var body = "En el renglon "+numReglon+" el precio "+this.state.productos[info].price+" es diferente a la Tarifa "+listasPrecio2[info2].price;
                                this.abrirAlerta(title, body);

                                return false;
                            }
                        }
                    }
                }
            }

            numReglon = Number(numReglon) + Number(1);
        }
        // console.log("terminar proceso");
        // return false;

        var self = this;

        if(this.state.productos.length > 0) {
            console.log("paymentterm: "+JSON.stringify(self.state.paymentterm));            
            if (
                (documentono == 110 || documentono == 119) 
                && (self.state.paymentterm == '01') 
                && (realizarProceso == 0)
                ) {
                // Es efectivo
                self.setState({
                    mostrarCambio: true
                });

                return false;
            }

            //deshabilitar boton factura
            self.setState({
                btnFacturaDisabled: true,
                btnCotizacionDisabled: true
            });

            //Obtener url para archivos
            var URLdomain = window.location.host;
            var rootpath = "../";

            //Obtener si existe sesion iniciada
            axios.get('./../mvc/api/v1/getsession')
            .then(function (response) {
                if(response.data.success) {
                    //si hay sesion realiza operacion
                    //console.log("Tiene sesion");
                    // console.log(" - orderno: "+self.state.orderno+" - tagref: "+self.state.tagref
                    //     +" - branchcode: "+self.state.cliente.branchcode+" - location: "+self.state.location
                    //     +" - salesman: "+self.state.salesman+" - paymentterm: "+self.state.paymentterm
                    //     +" - paymentmethod: "+self.state.paymentmethod+" - paymentReferencia: "+self.state.paymentReferencia
                    //     +" - type: "+documentono+" - products: "+JSON.stringify(self.state.productos)
                    //     +" - referenciasTraspaso: "+JSON.stringify(self.state.referenciasTraspaso)
                    //     +" - tipoComprobante: "+self.state.tipoComprobante
                    //     +" - usoCFDI: "+self.state.usoCFDI
                    //     +" - metodoPago: "+self.state.metodoPago
                    //     +" - claveConfirmacion: "+self.state.claveConfirmacion
                    //     );
                    self.mostrarCargando("mostrar");
                    axios.post('./../mvc/api/v1/setsalesorders', {
                        "orderno": self.state.orderno,
                        "tagref": self.state.tagref,
                        "branchcode": self.state.cliente.branchcode,
                        "location": self.state.location,
                        "currency": "MXN",
                        "salesman": self.state.salesman,
                        "paymentterm": self.state.paymentterm,
                        "paymentmethod": self.state.paymentmethod,
                        "paymentReferencia": self.state.paymentReferencia,
                        "type": documentono,
                        "products": self.state.productos,
                        "referenciasTraspaso": self.state.referenciasTraspaso,
                        "comments": self.state.comments,
                        "txtPagadorGen": self.state.txtPagadorGen,
                        "typeabbrev": self.state.typeabbrev,
                        "tipoComprobante": self.state.tipoComprobante,
                        "usoCFDI": self.state.usoCFDI,
                        "metodoPago": self.state.metodoPago,
                        "claveConfirmacion": self.state.claveConfirmacion,
                        "versionFactura": self.state.versionFactura,
                        "anticipoCliente": self.state.anticipoCliente,
                        "totalAnticipo": self.state.totalAnticipo,
                        "nomArticuloVenta": self.state.nomArticuloVenta
                    })
                    .then(function (response) {
                        var title = "";
                        var body = "";
                        if(response.data.success) {
                            var datos = response.data.data;
                            //console.log("response: "+datos);
                            //console.log("facturacion: "+response.data.facturacion);
                            documentono = response.data.tipoDocumento;
                            documento = response.data.nombreDocumento;

                            if (response.data.facturacion == 1) {
                                //Si ya esta en proceso o facturado
                                self.cerrarPago();
                                self.cerrarCambio();
                                self.mostrarCargando("cerrar");
                                
                                title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                                body = <div><p>{response.data.message}</p></div>;

                                self.abrirAlerta(title, body);
                                self.setState({
                                    productos: [],
                                    orderno: '',
                                    paymentterm: '',
                                    paymentmethod: '',
                                    paymentReferencia: '',
                                    comments: '',
                                    txtPagadorGen: '',
                                    NumRow: 1
                                });
                                //habilitar boton factura
                                self.setState({
                                    btnFacturaDisabled: false,
                                    btnCotizacionDisabled: false
                                });

                                return true;
                            }

                            if (documentono == 110 || documentono == 119) {
                                //si es factura
                                var OrderNo = "";
                                var transnofac = "";
                                var typeinvoice = "";
                                var debtorno = "";
                                var branchcode = "";
                                var tag = "";
                                var id = "";
                                var shippinglogid = "";
                                var shippingno = "";
                                var serie = "";
                                var folio = "";
                                var tipo = "";
                                var FromDia = "";
                                var FromMes = "";
                                var FromYear = "";
                                var ToDia = "";
                                var ToMes = "";
                                var ToYear = "";
                                var FolioFiscal = "";
                                var totalFac = "";
                                var paymentmethodname = "";
                                var generarRecibo = 1;
                                
                                for (var info in datos) {
                                    OrderNo = datos[info].OrderNo;
                                    transnofac = datos[info].transnofac;
                                    typeinvoice = datos[info].typeinvoice;
                                    debtorno = datos[info].debtorno;
                                    branchcode = datos[info].branchcode;
                                    tag = datos[info].tag;
                                    id = datos[info].id;
                                    shippinglogid = datos[info].shippinglogid;
                                    shippingno = datos[info].shippingno;
                                    serie = datos[info].serie;
                                    folio = datos[info].folio;
                                    tipo = datos[info].tipo;
                                    /*FromDia = datos[info].FromDia;
                                    FromMes = datos[info].FromMes;
                                    FromYear = datos[info].FromYear;
                                    ToDia = datos[info].ToDia;
                                    ToMes = datos[info].ToMes;
                                    ToYear = datos[info].ToYear;*/
                                    FolioFiscal = datos[info].FolioFiscal;
                                    totalFac = datos[info].totalFac;
                                    paymentmethodname = datos[info].paymentmethodname;
                                    generarRecibo = datos[info].generarRecibo;
                                    break;
                                }

                                self.setState({
                                    bsSize: 'large'
                                });

                                //si es factura deshabilitar boton aceptar
                                self.setState({
                                    btnAlertaAceptar: false
                                });

                                //ruta para reimpresion de la factura
                                var rutaReimpresion = rootpath+"SelectSalesOrderV6_0.php?orderno="+OrderNo+"&tagrefen="+tag+
                                    "&iddocto="+id+"&serie="+serie+"&folio="+folio+"&debtorno="+debtorno+
                                    "&tipo="+typeinvoice+"&transno="+transnofac+"&FromDia="+FromDia+"&FromMes="+FromMes+
                                    "&FromYear="+FromYear+"&ToDia="+ToDia+"&ToMes="+ToMes+"&ToYear="+ToYear+
                                    "&action=reimpresion&SearchOrders=SearchOrders&FolioFiscal="+FolioFiscal+"&pv=pv1";

                                console.log("URL impresion");
                                console.log(rutaReimpresion);

                                //ruta para generar los recibos
                                var rutaRecibos = rootpath+'CustomerReceiptFacturaContado.php?transnofac='+transnofac+'&typeinvoice='+typeinvoice+
                                    '&debtorno='+debtorno+'&branchcode='+branchcode+'&tag='+tag+'&id='+id+'&shippinglogid='+shippinglogid+'&shippingno='+shippingno+'&pv=pv1';

                                var mensajeNoRecibo = '';
                                if (generarRecibo != 1) {
                                    // Si aplica anticipos y es igual al total de la factura, no genera recibo
                                    rutaRecibos = '';
                                    mensajeNoRecibo = 'No genero recibo, por aplicación total de anticipo';
                                    self.setState({
                                        btnAlertaAceptar: false
                                    });
                                }
                                console.log("rutaRecibos: "+rutaRecibos);
                                console.log("generarRecibo: "+generarRecibo);

                                //Ruta para enviar por correo la factura
                                var rutaCorreo = rootpath+'SendInvoiceByMail.php?id='+id+'&debtorno='+debtorno+'&emails='+self.state.cliente.email+'&Enviar=true';
                                // console.log("URL correo");
                                // console.log(rutaCorreo);

                                //Ruta para impresion de la factura
                                var rutaPdf = rootpath+"PDFInvoice.php?OrderNo="+OrderNo+"&TransNo="+transnofac+"&Type="+typeinvoice+"&Tagref="+tag;

                                self.cerrarPago();
                                self.cerrarCambio();
                                self.mostrarCargando("cerrar");
                                // <iframe className="ocultar" src={rutaReimpresion} width="100%" height="600" frameBorder="0"></iframe>
                                title = <p><i className="fa fa-check-circle text-success" aria-hidden="true"></i> Exito</p>;
                                //console.log("banderaEnviarCorreo: "+self.state.banderaEnviarCorreo+" - disabledCheckCorreo: "+self.state.disabledCheckCorreo);
                                //<iframe className="ocultar" src={rutaCorreo} width="100%" height="360" frameBorder="0"></iframe>
                                //Agregar iframe de enviar correo
                                // <iframe className="ocultar" src={rutaReimpresion} width="100%" height="600" frameBorder="0"></iframe>
                                var body = <div></div>;
                                if (documentono == 110) {
                                    body = <div>
                                        <p>
                                            <span>Se creó {etiquetaProceso} No: {OrderNo}, correctamente</span>
                                            <span className="pull-right">{self.state.cliente.name}</span>
                                        </p>
                                        <p>
                                            <i className="fa fa-print text-success" aria-hidden="true"></i><a target="_blank" href={rutaPdf}> Imprimir {etiquetaProceso} No. {OrderNo}</a>
                                            <mark><font size="4"><span className="pull-right">Total $ {totalFac}</span></font></mark>
                                        </p>
                                        <p align="center"><span>Forma de Pago: {paymentmethodname}</span></p>
                                        <p align="center"><span>{mensajeNoRecibo}</span></p>
                                        <iframe name="iframeRecibos" id="iframeRecibos" className="" src={rutaRecibos} onLoad={self.compararRecibos} width="100%" height="360" frameBorder="0"></iframe>
                                        <iframe className="ocultar" src={rutaReimpresion} width="100%" height="600" frameBorder="0"></iframe>
                                    </div>;
                                } else {
                                    body = <div>
                                        <p>
                                            <span className="pull-left">{self.state.cliente.name}</span>
                                            <font size="4"><span className="pull-right">Total $ {totalFac}</span></font>
                                        </p>
                                        <br></br>
                                        <p align="center"><span><b>Forma de Pago:</b> {paymentmethodname}</span></p>
                                        <p align="center"><span>{mensajeNoRecibo}</span></p>
                                        <iframe name="iframeRecibos" id="iframeRecibos" className="" src={rutaRecibos} onLoad={self.compararRecibos} width="100%" height="360" frameBorder="0"></iframe>
                                        <iframe className="ocultar" src={rutaReimpresion} width="100%" height="600" frameBorder="0"></iframe>
                                    </div>;
                                }

                                self.abrirAlerta(title, body);
                                self.setState({
                                    productos: [],
                                    referenciasTraspaso: [],
                                    orderno: '',
                                    paymentterm: '',
                                    paymentmethod: '',
                                    paymentReferencia: '',
                                    envFac_Url: rutaCorreo,
                                    comments: '',
                                    txtPagadorGen: '',
                                    NumRow: 1
                                });
                                //habilitar boton factura
                                self.setState({
                                    btnFacturaDisabled: false,
                                    btnCotizacionDisabled: false
                                });
                                //Quitar boton de traspaso
                                self.setState({
                                    RutaImpresionValeTraspaso: "",
                                    disabledImpresionValeTraspaso: true,
                                    mensajeImprimirValeTraspaso: ""
                                });

                            }else if (documentono == 0) {
                                var TransNo = ""; //orderno
                                var Tagref = "";
                                var legalid = "";
                                var branchcode = "";
                                var orderno = "";
                                for (var info in datos) {
                                    TransNo = datos[info].TransNo;
                                    Tagref = datos[info].Tagref;
                                    legalid = datos[info].legalid;
                                    branchcode = datos[info].branchcode;
                                    orderno = datos[info].orderno;
                                    break;
                                }
                                
                                var ruta1 = rootpath+"PDFCotizacionTemplateV2.php?tipodocto=2&TransNo="+TransNo+"&Tagref="+Tagref+"&legalid="+legalid;
                                var ruta2 = rootpath+"PDFCotizacionTemplateV2.php?tipodocto=3&TransNo="+TransNo+"&Tagref="+Tagref+"&legalid="+legalid;

                                //Ruta para enviar por correo la cotizacion
                                var rutaCorreo = rootpath+'SendEmailV2_0.php?tagref='+Tagref+'&transno='+orderno+'&debtorno='+branchcode+'&legalid='+legalid+'&tipodocto=3&tipocotizacion=3&emails='+self.state.cliente.email+'&PV=1&Enviar=true';
                                // console.log("URL correo");
                                // console.log(rutaCorreo);

                                var mensaje = "Se creo "+etiquetaProceso+" no: "+response.data.orderno+", correctamente. "+response.data.message;
                                var mensajeURLSimple = "Imprimir "+self.state.nomCotizacionVenta+" No. "+TransNo;
                                var mensajeURL = "Imprimir "+self.state.nomCotizacionVenta+" No. "+TransNo;

                                self.setState({
                                    envCor_Msj: mensaje,
                                    envCor_UrlSimple: ruta1,
                                    envCor_MsjSimple: mensajeURLSimple,
                                    envCor_Url: ruta2,
                                    envCor_MsjUrl: mensajeURL,
                                    envCor_UrlEnvio: rutaCorreo
                                });

                                self.cerrarPago();
                                self.cerrarCambio();
                                self.mostrarCargando("cerrar");
                                
                                //console.log("banderaEnviarCorreo: "+self.state.banderaEnviarCorreo+" - disabledCheckCorreo: "+self.state.disabledCheckCorreo);
                                //<iframe className="" src={self.state.envCor_UrlEnvio} width="100%" height="100" frameBorder="0"></iframe>

                                title = <p><i className="fa fa-check-circle text-success" aria-hidden="true"></i> Exito</p>;
                                body = <div>
                                        <p>{self.state.envCor_Msj}</p>
                                        <p><i className="fa fa-print text-success" aria-hidden="true"></i><a target="_blank" href={self.state.envCor_UrlSimple}> {self.state.envCor_MsjSimple}</a></p>
                                        <button className="btn btn-success info-button" type="button" onClick={self.EnviarCorreoCotizacion}>Enviar Por Correo</button>
                                    </div>;
                                // <p><i className="fa fa-print text-success" aria-hidden="true"></i><a target="_blank" href={self.state.envCor_Url}> {self.state.envCor_MsjUrl}</a></p>
                                
                                self.abrirAlerta(title, body);
                                self.setState({
                                    productos: [],
                                    referenciasTraspaso: [],
                                    orderno: '',
                                    paymentterm: '',
                                    paymentmethod: '',
                                    paymentReferencia: '',
                                    comments: '',
                                    txtPagadorGen: '',
                                    NumRow: 1
                                });
                                //habilitar boton factura
                                self.setState({
                                    btnFacturaDisabled: false,
                                    btnCotizacionDisabled: false
                                });
                                //Quitar boton de traspaso
                                self.setState({
                                    RutaImpresionValeTraspaso: "",
                                    disabledImpresionValeTraspaso: true,
                                    mensajeImprimirValeTraspaso: ""
                                });
                            }

                            // self.abrirAnticipo();

                            // Habilitar operaciones
                            self.fnDesbloquearProcesos();

                            self.setState({
                                cliente: {
                                    branchcode: '',
                                    name: '',
                                    taxid: '',
                                    email: '',
                                    address: ''
                                }
                            });
                        }else{
                            //habilitar boton factura
                            self.setState({
                                btnFacturaDisabled: false,
                                btnCotizacionDisabled: false
                            });

                            self.cerrarPago();
                            self.cerrarCambio();
                            self.mostrarCargando("cerrar");

                            title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                            if (response.data.message != "") {
                                body = response.data.message;
                            }else{
                                body = "Ocurrio un error al crear " + etiquetaProceso;
                            }
                            
                            self.abrirAlerta(title, body);
                        }

                        // Actulizar version para factura
                        self.setState({
                            versionFactura: response.data.versionFactura
                        });
                    })
                    .catch(function (error) {
                        self.cerrarPago();
                        self.cerrarCambio();
                        self.mostrarCargando("cerrar");
                        var err = '';
                        if (error.response) {
                            err = error.response.status;
                        } else {
                            err = error.message;
                        }
                        
                        // Habilitar operaciones
                        self.fnDesbloquearProcesos();

                        //habilitar boton factura
                        self.setState({
                            btnFacturaDisabled: false,
                            btnCotizacionDisabled: false,
                            btnAlertaAceptar: false
                        });

                        var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                        var body = "Ocurrio un error en la peticion: " + err;
                        self.abrirAlerta(title, body);
                    });

                    return true;
                } else {
                    //habilitar boton factura
                    self.setState({
                        btnFacturaDisabled: false,
                        btnCotizacionDisabled: false
                    });
                    //si no hay sesion no realiza la operacion
                    //console.log("No tiene sesion");
                    var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                    var body = <div>
                                    <p>Sesion actual terminada, iniciar sesion nuevamente</p>
                                    <p>Al iniciar sesion regresa a esta pestaña y da click en aceptar</p>
                                    <p><a href="../../ap_grp_demo/" target="_blank">Iniciar Sesion</a></p>
                                </div>;
                    self.abrirAlerta(title, body);
                    return false;
                }
            })
            .catch(function (error) {
                console.log("Error Obtener Sesion");
                //si sale error no realiza la operacion
                var err = '';
                if (error.response) {
                    err = error.response.status;
                } else {
                    err = error.message;
                }
                
                // Habilitar operaciones
                self.fnDesbloquearProcesos();

                //habilitar boton factura
                self.setState({
                    btnFacturaDisabled: false,
                    btnCotizacionDisabled: false
                });
                var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                var body = "Ocurrio un error en la peticion: " + err;
                self.abrirAlerta(title, body);
                return false;
            });
        } else {
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = "No tienes "+self.state.nomArticuloVentaPlural+" agregados.";
            self.abrirAlerta(title, body);
        }
    },

    EnviarCorreoCotizacion: function () {
        // console.log("EnviarCorreoCotizacion");
        
        //Cargar datos con iframe para envio de correo
        var self = this;

        var title = <p><i className="fa fa-check-circle text-success" aria-hidden="true"></i> Exito</p>;
        var body = <div>
                <p>{self.state.envCor_Msj}</p>
                <p><i className="fa fa-print text-success" aria-hidden="true"></i><a target="_blank" href={self.state.envCor_UrlSimple}> {self.state.envCor_MsjSimple}</a></p>
                <p><i className="fa fa-print text-success" aria-hidden="true"></i><a target="_blank" href={self.state.envCor_Url}> {self.state.envCor_MsjUrl}</a></p>
                <button className="btn btn-success info-button" type="button" onClick={self.EnviarCorreoCotizacion}>Enviar Por Correo</button>
                <iframe className="" src={self.state.envCor_UrlEnvio} width="100%" height="40" frameBorder="0"></iframe>
            </div>;
        this.abrirAlerta(title, body);
    },

    guardarRemision: function () {
        this.fnValidacionesProceso("documento de pago", remision);
    },

    guardarFactura: function () {
        this.fnValidacionesProceso("factura", factura);
    },

    fnProcesarCambio: function () {
        this.crearDocumento('documento de pago', remision, 1);
    },

    fnValidacionesProceso: function (nombreDocumento, typeidDocumento) {
        // console.log("nombreDocumento: "+nombreDocumento+" - typeidDocumento: "+typeidDocumento);

        //Revisar seleccion del metodo de pago
        if(this.state.paymentterm == "" && this.state.paymentmethod == ""){
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = <div>
                            <p>Seleccionar Forma de Pago, para poder realizar la Factura</p>
                        </div>;
            this.abrirAlerta(title, body);
            return false;
        }

        if (this.state.versionFactura == "3.3" && typeidDocumento == "110") {
            // Validaciones 3.3
            var contenido = [];
            var error = 0;

            contenido.push( <p>Seleccionar</p> );

            if (this.state.tipoComprobante == "") {
                error = 1;
                contenido.push( <p><i className="fa fa-certificate text-danger" aria-hidden="true"></i> Tipo de Comprobante</p> );
            }

            if (this.state.usoCFDI == "") {
                error = 1;
                contenido.push( <p><i className="fa fa-certificate text-danger" aria-hidden="true"></i> Uso de CFDI</p> );
            }

            if (this.state.metodoPago == "") {
                error = 1;
                contenido.push( <p><i className="fa fa-certificate text-danger" aria-hidden="true"></i> Método de Pago</p> );
            }

            if (error == 1) {
                contenido.push( <p>Para poder realizar la Factura</p> );

                var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                var body = <div>
                        {contenido}
                    </div>;

                this.abrirAlerta(title, body);

                return false;
            }
        }

        var subtotal = 0;
        var totalAnticipo = 0;
        if (this.state.anticipoCliente.length > 0) {
            // Validar subtotal con anticipos
            for (var producto in this.state.productos) {
                if(this.state.productos.hasOwnProperty(producto)) {
                    var sub = this.state.productos[producto].price * this.state.productos[producto].quantity;
                    var sub_dis = (sub - (sub * (this.state.productos[producto].discount / 100)));
                    subtotal += sub_dis;
                }
            }
            
            for (var anticipo in this.state.anticipoCliente) {
                if(this.state.anticipoCliente.hasOwnProperty(anticipo)) {
                    totalAnticipo += Number(this.state.anticipoCliente[anticipo].MontoAnticipo);
                }
            }
            
            if (totalAnticipo > 0) {                
                if (subtotal < totalAnticipo) {
                    var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                    var body = "La suma total de anticipos seleccionados es mayor al subtotal de la factura( Total anticipos: "+totalAnticipo+" > Subtotal factura: "+subtotal+" ), verifique los anticipos seleccionados";
                    this.abrirAlerta(title, body);
                    return false;
                }
            }
        }

        this.setState({
            totalAnticipo: totalAnticipo
        });

        // No valir existencias ya que se manejan en este tipo de ingresos
        // this.crearDocumento("factura", factura);
        this.crearDocumento(nombreDocumento, typeidDocumento);
        return true;

        //Revisar productos sin existencias
        var sinexistencia = 1;
        var sindisponible = 1;

        for (var info in this.state.productos) {
            console.log(this.state.productos[info].stockid+" - "+this.state.productos[info].available);
            if(Number(this.state.productos[info].available) < 1 || this.state.productos[info].available == null){
                sinexistencia = 0;
            }
            if(Number(this.state.productos[info].available) < Number(this.state.productos[info].quantity)){
                sindisponible = 0;
            }
        }
        // console.log("bandera existencia: "+sinexistencia);
        // console.log("bandera disponible: "+sindisponible);

        if(sinexistencia == 0 && sindisponible == 1){
            //selecciono productos sin existencia
            //console.log("productos sin existencia");
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = <div>
                            <p>Existen productos sin existencia</p>
                            <p>No se puede realizar la operacion</p>
                        </div>;
            this.abrirAlerta(title, body);
        }else if(sinexistencia == 1 && sindisponible == 0){
            //selecciono mas cantidad de la disponible
            //console.log("productos con mas cantidad solicitada que existencia");
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = <div>
                            <p>Existen productos con cantidad solicitada mayor a la existencia</p>
                            <p>No se puede realizar la operacion</p>
                        </div>;
            this.abrirAlerta(title, body);
        }else if(sinexistencia == 0 && sindisponible == 0){
            //selecciono productos sin existencia y mas cantidad de la disponible
            //console.log("productos con mas cantidad solicitada que existencia");
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = <div>
                            <p>Existen productos sin existencia</p>
                            <p>Existen productos con cantidad solicitada mayor a la existencia</p>
                            <p>No se puede realizar la operacion</p>
                        </div>;
            this.abrirAlerta(title, body);
        }else if(sinexistencia == 1 && sindisponible == 1){
            //permite facturar
            //console.log("realiza factura");
            //si hay existencias, factura
            this.crearDocumento("factura", factura);
        }
    },

    guardarTraspaso: function () {

        var self = this;

        if(self.state.imgCargandoMostrar == "block"){
            return false;
        }

        self.mostrarCargando("mostrar");

        //obtener informacion del traspaso
        var datos = this.state.almacenesTraspaso;
        var arrayProductosTraspaso = new Array();
        var loccodenew = this.state.location;
        var stockidTraspaso = "";
        var cantidadTraspaso = 0;
        var num = 0;
        var objProTraspaso = new Object();
        for (var info in datos) {
            stockidTraspaso = datos[info].stockid;
            if (datos[info].request > 0) {
                cantidadTraspaso = Number(cantidadTraspaso) + Number(datos[info].request);

                objProTraspaso.stockid = datos[info].stockid;
                objProTraspaso.quantity = datos[info].request
                objProTraspaso.loccodeold = datos[info].loccode

                arrayProductosTraspaso.push(objProTraspaso);
            }
            num = num + 1;
        }

        if (cantidadTraspaso > 0) {
            //selecciono articulos de otro almacen

            //Obtener si existe sesion iniciada
            axios.get('./../mvc/api/v1/getsession')
            .then(function (response) {
                if(response.data.success) {
                    //si hay sesion realiza operacion
                    //console.log("Tiene sesion");

                    //var myString = JSON.stringify(miObjeto);

                    axios.post('./../mvc/api/v1/settransfer', {
                        "loccodenew": loccodenew,
                        "debtorno": self.state.cliente.branchcode,
                        "products": arrayProductosTraspaso,
                        "referenciasTraspaso": self.state.referenciasTraspaso
                    })
                    .then(function (response) {
                        if(response.data.success) {
                            //Si realiza traspaso actualizar existencia
                            var row_index = 0;

                            var available = 0;
                            var obj = self.state.productos.filter(function(element, index, array) {
                                if(element.stockid === stockidTraspaso) {
                                    row_index = index;
                                    available = element.available;
                                }
                                return element;
                            });

                            available = Number(available) + Number(cantidadTraspaso);

                            var updated_row = update(self.state.productos[row_index], {
                                available: {
                                    $set: Number(available)
                                }
                            });

                            var newRowData = update(self.state.productos, {
                                $splice: [[row_index, 1, updated_row]]
                            });

                            //guardar referencia traspaso
                            var objReferenciaTraspaso = new Object();
                            objReferenciaTraspaso.reference = response.data.data.reference;
                            self.state.referenciasTraspaso.push(objReferenciaTraspaso);
                            //console.log("referenciasTraspaso: "+self.state.referenciasTraspaso);

                            self.setState({
                                productos: newRowData

                            });
                            
                            //Obtener url para archivos
                            var rootpath = "../";
                            var ruta = rootpath+"PDFStockLocTransfer.php?TransferNo="+response.data.data.reference;

                            //Vinculo de impresion  RutaImpresionValeTraspaso  disabledImpresionValeTraspaso
                            self.setState({
                                RutaImpresionValeTraspaso: ruta,
                                disabledImpresionValeTraspaso: false,
                                mensajeImprimirValeTraspaso: "Imprimir Vale antes de Cotizar y/o Facturar"

                            });

                            self.cerrarTraspaso();
                            self.mostrarCargando("cerrar");
                            //var ruta = "../../ap_grp_demo/PDFStockLocTransfer.php?TransferNo="+response.data.data.reference;
                            var title = <p><i className="fa fa-check-circle text-success" aria-hidden="true"></i> Exito</p>;
                            var body = <div><p>El traspaso {response.data.data.reference} se realizo correctamente</p>
                                        <p><i className="fa fa-print text-success" aria-hidden="true"></i><a target="_blank" href={ruta}> Imprimir Vale</a></p></div>;
                            self.abrirAlerta(title, body);
                        } else {
                            //sin realizar traspaso
                            self.cerrarTraspaso();
                            self.mostrarCargando("cerrar");
                            self.setState({
                                productoDescripcion: "",
                                almacenesTraspaso: []
                            });
                            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                            var body = "El traspaso no pudo ser generado";
                            self.abrirAlerta(title, body);
                        }
                    })
                    .catch(function (error) {
                        self.cerrarTraspaso();
                        self.mostrarCargando("cerrar");
                        //mostrar error
                        var err = '';
                        if (error.response) {
                            err = error.response.status;
                        } else {
                            err = error.message;
                        }
                        var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                        var body = "Ocurrio un error en la peticion: " + err;
                        self.abrirAlerta(title, body);
                    });

                    return true;
                } else {
                    //si no hay sesion no realiza la operacion
                    //console.log("No tiene sesion");
                    var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                    var body = <div>
                                    <p>Sesion actual terminada, iniciar sesion nuevamente</p>
                                    <p>Al iniciar sesion regresa a esta pestaña y da click en aceptar</p>
                                    <p><a href="../../ap_grp_demo/" target="_blank">Iniciar Sesion</a></p>
                                </div>;
                    self.abrirAlerta(title, body);
                    return false;
                }
            })
            .catch(function (error) {
                console.log("Error Obtener Sesion");
                //si sale error no realiza la operacion
                var err = '';
                if (error.response) {
                    err = error.response.status;
                } else {
                    err = error.message;
                }
                var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                var body = "Ocurrio un error en la peticion: " + err;
                self.abrirAlerta(title, body);
                return false;
            });

        }else{
            //sin seleccion de articulos de otro almacen
            self.cerrarTraspaso();
            self.mostrarCargando("cerrar");
            self.setState({
                productoDescripcion: "",
                almacenesTraspaso: []
            });
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = "Sin cantidad seleccionada del "+self.state.nomArticuloVenta+" "+stockidTraspaso;
            self.abrirAlerta(title, body);
        }
    },

    guardarCotizacion: function() {
        this.crearDocumento("cotizacion", cotizacion);
    },

    buscarCotizacion: function(orderno) {

        var self = this;

        //Obtener si existe sesion iniciada
        axios.get('./../mvc/api/v1/getsession')
        .then(function (response) {
            if(response.data.success) {
                //si hay sesion realiza operacion
                //console.log("Tiene sesion");

                //buscar cotizacion
                axios.get('./../mvc/api/v1/getquotation/' + orderno)
                .then(function (response) {
                    if(response.data.success) {
                        //proceso correcto
                        var prods = response.data.products.data;

                        self.setState({
                            NumRow: 1
                        });

                        var tieneContrato = 0;

                        for (var producto in prods) {
                            if(prods.hasOwnProperty(producto)) {
                                (!prods[producto].quantity) ? 1 : Number(prods[producto].quantity);
                                (!prods[producto].price) ? 0 : Number(prods[producto].price);
                                (!prods[producto].discount) ? 0 : Number(prods[producto].discount);
                                var sub = prods[producto].price * prods[producto].quantity;
                                prods[producto].total = sub - (sub * (prods[producto].discount / 100));
                                prods[producto].NumRow = self.state.NumRow;

                                if (prods[producto].idcontrato != 0) {
                                    tieneContrato = 1;
                                }

                                self.state.NumRow ++;
                            }
                        }

                        if (tieneContrato == 1) {
                            self.fnBloquearProcesos();
                        }

                        self.setState({
                            mostrarInfo: !self.state.mostrarInfo,
                            mostrarBusquedaCotizacion: !self.state.mostrarBusquedaCotizacion,
                            productos: prods
                        });
                    } else {
                        //no encontro cotizacion
                        var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                        var body = "No se encontro "+self.state.nomCotizacionVenta+".";
                        self.abrirAlerta(title, body);
                    }
                })
                .catch(function (error) {
                    //Error
                    var err = '';
                    if (error.response) {
                        err = error.response.status;
                    } else {
                        err = error.message;
                    }

                    var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                    var body = "Ocurrio un error en la peticion: " + err;
                    self.abrirAlerta(title, body);
                });

                return true;
            } else {
                //si no hay sesion no realiza la operacion
                //console.log("No tiene sesion");
                var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                var body = <div>
                                <p>Sesion actual terminada, iniciar sesion nuevamente</p>
                                <p>Al iniciar sesion regresa a esta pestaña y da click en aceptar</p>
                                <p><a href="../../ap_grp_demo/" target="_blank">Iniciar Sesion</a></p>
                            </div>;
                self.abrirAlerta(title, body);
                return false;
            }
        })
        .catch(function (error) {
            console.log("Error Obtener Sesion");
            //si sale error no realiza la operacion
            var err = '';
            if (error.response) {
                err = error.response.status;
            } else {
                err = error.message;
            }
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = "Ocurrio un error en la peticion: " + err;
            self.abrirAlerta(title, body);
            return false;
        });
    },

    agregarArticulo: function (objeto, ArrayAgregar) {
        //console.log( "agregarArticulo: " + JSON.stringify(objeto) );
        //console.log( "ArrayAgregar: " + ArrayAgregar );
        const stockid = objeto.stockid;

        var exist = -1;

        /*var obj = this.state.productos.filter(function(element, index, array) {
            if(element.stockid === stockid) {
                exist = index;
            }
            return element;
        });*/

        var iva = this.state.operacionIva;
        
        /*if(exist >= 0) {
            var new_quantity = Number(this.state.productos[exist].quantity) + 1;
            var row_subtotal = new_quantity * this.state.productos[exist].price;
            var row_discount = row_subtotal * (this.state.productos[exist].discount / 100)
            row_subtotal = row_subtotal - row_discount;
            var row_iva = row_subtotal * Number(iva);
            var row_total = Number(row_subtotal) + Number(row_iva);
            console.log(row_subtotal+" - "+row_iva+" - "+row_total);
            var updatedProduct = update(this.state.productos[exist], {
                quantity: {
                    $set: new_quantity
                },
                subtotal: {
                    $set: row_subtotal
                },
                iva: {
                    $set: row_iva
                },
                total: {
                    $set: row_total
                }
            }); 

            var newProductData = update(this.state.productos, {
                $splice: [[exist, 1, updatedProduct]]
            });

            this.setState({
                productos: newProductData
            });

        } else {*/
            var row_subtotal = objeto.price * objeto.quantity;
            var row_discount = row_subtotal * (((!objeto.discount) ? 0 : objeto.discount) / 100);
            row_subtotal = Number(row_subtotal) - Number(row_discount);
            var row_iva = Number(row_subtotal) * Number(iva);
            var row_total = Number(row_subtotal) + Number(row_iva);

            objeto.NumRow = this.state.NumRow;
            objeto.quantity = (!objeto.quantity) ? 1 : objeto.quantity;
            objeto.price = (!objeto.price) ? 0 : objeto.price;
            objeto.discount = (!objeto.discount) ? 0 : objeto.discount;
            objeto.subtotal = row_subtotal;
            objeto.iva = row_iva;
            objeto.total = row_total;

            if (ArrayAgregar == 'productos') {
                //console.log("productos");
                this.setState({
                    productos: update(this.state.productos, {
                        $push: [objeto]
                    }),
                });

                this.state.NumRow ++;
            }else if (ArrayAgregar == 'productosCotizadorRapido') {
                //console.log("productosCotizadorRapido");
                this.setState({
                    productosCotizadorRapido: update(this.state.productosCotizadorRapido, {
                        $push: [objeto]
                    }),
                });
            }else if (ArrayAgregar == "productosCotizadorRapidoSeleccion") {
                //console.log("productosCotizadorRapidoSeleccion");
                this.setState({
                    productosCotizadorRapidoSeleccion: update(this.state.productosCotizadorRapidoSeleccion, {
                        $push: [objeto]
                    }),
                });

                this.state.NumRow ++;
            }
            
        //}

        //console.log( "Productos: " + JSON.stringify(this.state.productos) );
    },

    onDeleteRow: function (row) {
        var productos = this.state.productos.filter((producto) => {
            return row.indexOf(producto.NumRow) === -1;
        });

        this.state.NumRow = 1;

        for (var producto in productos) {
            if(productos.hasOwnProperty(producto)) {

                productos[producto].NumRow = this.state.NumRow;

                this.state.NumRow ++;
            }
        }

        this.setState({
            productos: productos,
        });
    },

    onDeleteRowCotizadorRapidoSeleccion: function (row) {
        var productos = this.state.productosCotizadorRapidoSeleccion.filter((producto) => {
            return row.indexOf(producto.NumRow) === -1;
        });

        this.state.NumRow = 1;

        for (var producto in productos) {
            if(productos.hasOwnProperty(producto)) {

                productos[producto].NumRow = this.state.NumRow;

                this.state.NumRow ++;
            }
        }

        this.setState({
            productosCotizadorRapidoSeleccion: productos,
        });
    },

    mostrarCargando: function (operacion){
        if(operacion == "mostrar"){
            //mostrar imagen cargando
            this.setState({
                imgCargandoMostrar: "block"
            });
        }else if(operacion == "cerrar"){
            //ocultar imagen cargando
            this.setState({
                imgCargandoMostrar: "none"
            });
        }
    },

    obtenerSession: function(){
        //obtener si existe session activa
        var self = this;

        //Obtener si existe sesion iniciada
        axios.get('./../mvc/api/v1/getsession')
        .then(function (response) {
            if(response.data.success) {
                //si hay sesion realiza operacion
                //console.log("Tiene sesion");
                return true;
            } else {
                //si no hay sesion no realiza la operacion
                console.log("No tiene sesion");
                var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                var body = <div>
                                <p>Sesion actual terminada, iniciar sesion nuevamente</p>
                                <p>Al iniciar sesion regresa a esta pestaña y da click en aceptar</p>
                                <p><a href="../../ap_grp_demo/" target="_blank">Iniciar Sesion</a></p>
                            </div>;
                self.abrirAlerta(title, body);
                return false;
            }
        })
        .catch(function (error) {
            console.log("Error Obtener Sesion");
            //si sale error no realiza la operacion
            var err = '';
            if (error.response) {
                err = error.response.status;
            } else {
                err = error.message;
            }
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = "Ocurrio un error en la peticion: " + err;
            self.abrirAlerta(title, body);
            return false;
        });
    },

    obtenerDatosCliente: function(){
        //obtener informacion cliente que modifico
        if(this.state.modificarclienteB == true){
            //si presiono modificar cliente
            if(this.state.modificarclienteBPro == false && Number(this.state.modificarclienteBNum) < 5){
                //si no esta haciendo una actualizacion de datos
                this.setState({
                    modificarclienteBPro: true,
                    modificarclienteBNum: Number(this.state.modificarclienteBNum) + Number("1")
                });

                var self = this;

                axios.get('./../mvc/api/v1/getdebtors/' + self.state.cliente.branchcode)
                .then(function (response) {
                    if(response.data.success) {
                        self.setState({
                            cliente: {
                                branchcode: response.data.data[0].debtorno,
                                name: response.data.data[0].name,
                                taxid: response.data.data[0].taxid,
                                email: response.data.data[0].email,
                                address: response.data.data[0].address
                            }
                        });

                        self.setState({
                            modificarclienteBPro: false
                        });
                    } else {
                        //error al traer informacion
                        self.setState({
                            modificarclienteBPro: false
                        });
                    }
                })
                .catch(function (error) {
                    // console.log(error);
                    self.setState({
                        modificarclienteBPro: false
                    });
                });
            }
        }
    },

    seleccionarVendedor: function (value, label){
        //si cambia vendedor
        this.setState({
            salesman: value,
            salesmanname: label,
            labelSelectVendedor: label
        });
    },

    onChangeCheck: function (e) {
        //console.log('antes banderaEnviarCorreo: ' + this.state.banderaEnviarCorreo);
        this.setState({
            banderaEnviarCorreo: e.target.checked
        });
        //console.log('despues banderaEnviarCorreo: ' + this.state.banderaEnviarCorreo);
    },

    onChangeComentarios: function (event) {
        this.setState({
            comments: event.target.value
        });
        //console.log('comments: ' + this.state.comments);
    },

    onChangePagador: function (event) {
        this.setState({
            txtPagadorGen: event.target.value
        });
        //console.log('txtPagadorGen: ' + this.state.txtPagadorGen);
    },

    abrirCotizadorRapido: function () {
        //Validar
        if(this.state.tagref == "" || this.state.location == ""){
            //si no selecciono unidad de negocio o almacen
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = "Seleccionar "+this.state.nomUniNegVenta+" y/o "+this.state.nomAlmacenVenta+" para poder ralizar "+documento;
            this.abrirAlerta(title, body);

            return false;
        }

        //Mostrar modal de cotizador
        this.setState({
            NumRow: 1,
            productos: [],
            productosCotizadorRapido: [],
            productosCotizadorRapidoSeleccion: [],
            mostrarCotizadorRapido: true,
            TablaCotizadorRapido: "",
            DescripcionCotizadorRapido: ""
        });
    },

    agregarProductosCotizadorRapido: function() {
        //console.log("agregarProductosCotizadorRapido");
        this.state.productos = this.state.productosCotizadorRapidoSeleccion;

        this.cerrarCotizadorRapido();
    },

    ObtenerListasPrecios: function (branchcode) {
        var self = this;

        axios.get('./../mvc/api/v1/salestypes/' + branchcode)
        .then(function (response) {
            if(response.data.success) {
                var arrayObj = response.data.data;
                var Body = [];
                var num = 1;

                self.setState({
                    SelectListaPrecios: ""
                });

                //console.log("Listas: "+JSON.stringify(arrayObj));

                for (var lista in arrayObj) {
                    var typeabbrev = arrayObj[lista].typeabbrev;
                    var sales_type = arrayObj[lista].sales_type;

                    if (num == 1 && self.state.typeabbrev == "") {
                        self.setState({
                            typeabbrev: typeabbrev.trim(),
                            sales_type: sales_type.trim()
                        });
                    }

                    var mensaje = arrayObj[lista].typeabbrev + " - " + arrayObj[lista].sales_type;

                    if (self.state.typeabbrev == arrayObj[lista].typeabbrev) {
                        Body.push( 
                            <option selected>{mensaje}</option>
                        );
                    }else{
                        Body.push( 
                            <option>{mensaje}</option>
                        );
                    }

                    num ++;
                }

                var datos = Body;

                self.setState({
                    SelectListaPrecios: datos
                });
            } else {
                var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                var body = "Ocurrio un error en la peticion: " + response.data.data.message;
                self.abrirAlerta(title, body);
            }
        })
        .catch(function (error) {
            console.log("Error Obtener Sesion");
            //si sale error no realiza la operacion
            var err = '';
            if (error.response) {
                err = error.response.status;
            } else {
                err = error.message;
            }
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = "Ocurrio un error en la peticion: " + err;
            self.abrirAlerta(title, body);
            return false;
        });
    },

    CambioListaPrecio: function (event) {
        //console.log("CambioListaPrecio: "+event.target.value);
        if (this.state.productos.length > 0) {
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = "Tienes "+this.state.nomArticuloVentaPlural+" agregados con la lista " + this.state.typeabbrev + " - " + this.state.sales_type
                + ". Para cambiar de lista se deben eliminar los productos seleccionados";
            this.abrirAlerta(title, body);

            this.ObtenerListasPrecios(this.state.cliente.branchcode);

            return false;
        }else{
            var value = event.target.value;
            var datos = value.split("-");

            var typeabbrev = datos[0];
            var sales_type = datos[1];

            this.setState({
                typeabbrev: typeabbrev.trim(),
                sales_type: sales_type.trim()
            });
            //console.log("typeabbrev: "+this.state.typeabbrev+" - sales_type: "+this.state.sales_type);
        }
    },

    fnBloquearProcesos: function () {
        this.state.valBloquearOperacion = true;
        this.state.valBloquearOperacion2 = false;
    },

    fnDesbloquearProcesos: function () {
        this.state.valBloquearOperacion = false;
        this.state.valBloquearOperacion2 = true;
    },

    render: function () {

        const toolTipTextVendedor = 'Cambiar Vendedor';
        const apiVendedor = {
            name: 'Vendedor',
            func: 'getsalesman',
            value: 'salesmancode',
            label: 'salesmanname',
            selection: 'seleccion'
        };

        return (
        <div onClick={this.obtenerDatosCliente}>
        
            <nav className="navbar navbar-default navbar-fixed-top header">
                <p style={{
                    "position": "absolute", 
                    "width": "100%", 
                    "left": "0", 
                    "top": "5px", 
                    "text-align": "center",
                    "font-size": "20px",
                    "font-style": "bold",
                    "color": "white"
                    }}>Pase de Cobro {this.state.orderno}</p>
                <div className="container-fluid">
                    <div className="collapse navbar-collapse">
                        <ul className="nav navbar-nav">
                            <li><p className="navbar-text">{this.state.nomVentanaPuntoVenta} - {this.state.version} - Empresa: {this.state.dataBase} <font color='red'> {this.state.versionmsj} </font> </p></li>
                        </ul>
                        <ul className="nav navbar-nav navbar-right">
                            <li><p className="navbar-text">{this.state.username}</p></li>
                            <li><p className="navbar-text"><img src="assets/img/avatar.png" alt="Vendedor" className="img-circle imagen-usuario" /></p></li>
                            <li><p className="navbar-text"><a href="../" className="btn-primary"><i className="fa fa-home fa-2x" aria-hidden="true"></i></a></p></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div className="container-fluid">

                <ModalCambio 
                    show={this.state.mostrarCambio} 
                    hideModal={this.cerrarCambio} 
                    productos={this.state.productos} 
                    totalGeneralIngreso={this.state.totalGeneralIngreso}
                    guardarRemision={this.guardarRemision} 
                    guardarFactura={this.guardarFactura} 
                    fnProcesarCambio={this.fnProcesarCambio}
                    btnFacturaDisabled={this.state.btnFacturaDisabled} 
                    parent={this} />

                <ModalPago 
                    show={this.state.mostrarPago} 
                    hideModal={this.cerrarPago} 
                    productos={this.state.productos} 
                    totalGeneralIngreso={this.state.totalGeneralIngreso}
                    paymentterm={this.state.paymentterm} 
                    paymentmethod={this.state.paymentmethod} 
                    paymentReferencia={this.state.paymentReferencia} 
                    guardarRemision={this.guardarRemision} 
                    guardarFactura={this.guardarFactura} 
                    btnFacturaDisabled={this.state.btnFacturaDisabled} 
                    currencydenominations={this.state.currencydenominations} 
                    imgCargandoMostrar={this.state.imgCargandoMostrar} 
                    obtenerSession={this.obtenerSession} 
                    tipoComprobante={this.state.tipoComprobante} 
                    tipoComprobanteName={this.state.tipoComprobanteName}
                    usoCFDI={this.state.usoCFDI} 
                    usoCFDIName={this.state.usoCFDIName}
                    metodoPago={this.state.metodoPago} 
                    metodoPagoName={this.state.metodoPagoName}
                    claveConfirmacion={this.state.claveConfirmacion} 
                    operacionIva={this.state.operacionIva}
                    parent={this} />
                
                <ModalTraspaso 
                    show={this.state.mostrarTraspaso} 
                    hideModal={this.cerrarTraspaso} 
                    almacenesTraspaso={this.state.almacenesTraspaso} 
                    productoDescripcion={this.state.productoDescripcion} 
                    guardarTraspaso={this.guardarTraspaso} 
                    imgCargandoMostrar={this.state.imgCargandoMostrar} 
                    nomAlmacenVenta={this.state.nomAlmacenVenta}
                    parent={this} />

                <ModalCotizadorRapido 
                    show={this.state.mostrarCotizadorRapido} 
                    hideModal={this.cerrarCotizadorRapido} 
                    agregarProductosCotizadorRapido={this.agregarProductosCotizadorRapido} 
                    productos={this.state.productos}
                    productosCotizadorRapido={this.state.productosCotizadorRapido}
                    productosCotizadorRapidoSeleccion={this.state.productosCotizadorRapidoSeleccion}
                    NumRow={this.state.NumRow}
                    TablaCotizadorRapido={this.state.TablaCotizadorRapido}
                    onDeleteRowCotizadorRapidoSeleccion={this.onDeleteRowCotizadorRapidoSeleccion}
                    DescripcionCotizadorRapido={this.state.DescripcionCotizadorRapido}

                    agregarProducto={this.agregarArticulo} 
                    tagref={this.state.tagref} 
                    tagrefname={this.state.tagrefname} 
                    locationname={this.state.locationname} 
                    location={this.state.location} 
                    mensajeUnidadAlmacen={this.state.mensajeUnidadAlmacen} 
                    abrirAlerta={this.abrirAlerta} 
                    obtenerSession={this.obtenerSession} 

                    permisoPrecio={this.state.permisoPrecio} 
                    permisoDescuento={this.state.permisoDescuento}
                    descuentoMaximo={this.state.descuentoMaximo}

                    typeabbrev={this.state.typeabbrev}

                    nomAlmacenVenta={this.state.nomAlmacenVenta}
                    nomUniNegVenta={this.state.nomUniNegVenta}
                    nomArticuloVenta={this.state.nomArticuloVenta}
                    nomArticuloVentaPlural={this.state.nomArticuloVentaPlural}
                    
                    imgCargandoMostrar={this.state.imgCargandoMostrar} 
                    parent={this} />                

                <CustomAlert show={this.state.alerta.mostrar} title={this.state.alerta.title} body={this.state.alerta.body} btnAlertaAceptar={this.state.btnAlertaAceptar} hideModal={this.cerrarAlerta} bsSize={this.state.bsSize} />

                <img src="assets/img/loading.gif" width="200" height="150" className="imgCargando" style={{"display": this.state.imgCargandoMostrar}} />

                <div className="">
                    <div className="col-md-9">  
                        <div className="row">
                            <div className="col-md-12">
                                <div className="panel panel-default">
                                    <div className="panel-body">
                                        
                                        <InformacionGeneral 
                                            tagref={this.state.tagref} 
                                            tagrefname={this.state.tagrefname} 
                                            location={this.state.location} 
                                            locationname={this.state.locationname} 
                                            orderno={this.state.orderno} 
                                            disabledCheckCorreo={this.state.disabledCheckCorreo} 
                                            obtenerSession={this.obtenerSession} 
                                            ObtenerListasPrecios={this.ObtenerListasPrecios}
                                            abrirAnticipo={this.abrirAnticipo}
                                            nomClienteVenta={this.state.nomClienteVenta}
                                            nomAlmacenVenta={this.state.nomAlmacenVenta}
                                            nomUniNegVenta={this.state.nomUniNegVenta}
                                            nomCotizacionVenta={this.state.nomCotizacionVenta}
                                            usoCFDI={this.state.usoCFDI}
                                            usoCFDIName={this.state.usoCFDIName}
                                            metodoPago={this.state.metodoPago}
                                            metodoPagoName={this.state.metodoPagoName}
                                            valBloquearOperacion={this.state.valBloquearOperacion}
                                            fnBloquearProcesos={this.fnBloquearProcesos}
                                            parent={this} />

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-12 ocultar">
                                <div className="panel panel-default">
                                    <div className="panel-body">
                                        <div className="col-md-4 centrar-contenido">
                                            <select className="form-control" onChange={this.CambioListaPrecio}>
                                                {this.state.SelectListaPrecios}
                                            </select>
                                        </div>
                                        <div className="col-md-4 centrar-contenido">
                                            <button className="btn btn-info info-button" onClick={this.abrirCotizadorRapido} type="button">Cotizador Rápido</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-12">
                                <div className="panel panel-default">
                                    <div className="panel-body" hidden={this.state.valBloquearOperacion}>
                                        <div className="input-group">
                                            <span className="input-group-btn"><button className="btn btn-primary" type="button">{this.state.nomArticuloVentaPlural}:</button></span>
                                            <BuscarProductos 
                                            agregarProducto={this.agregarArticulo} 
                                            tagref={this.state.tagref} 
                                            tagrefname={this.state.tagrefname} 
                                            locationname={this.state.locationname} 
                                            location={this.state.location} 
                                            mensajeUnidadAlmacen={this.state.mensajeUnidadAlmacen} 
                                            abrirAlerta={this.abrirAlerta} 
                                            obtenerSession={this.obtenerSession} 
                                            parent={this} 
                                            CotizadorRapido="0" 
                                            typeabbrev={this.state.typeabbrev}
                                            nomAlmacenVenta={this.state.nomAlmacenVenta}
                                            nomArticuloVenta={this.state.nomArticuloVenta}
                                            nomArticuloVentaPlural={this.state.nomArticuloVentaPlural} />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="row">
                            <TablaProductos 
                                productos={this.state.productos} 
                                tagref={this.state.tagref} 
                                location={this.state.location} 
                                typeabbrev={this.state.typeabbrev}
                                mostrarCargando={this.mostrarCargando}
                                onDeleteRow={this.onDeleteRow} 
                                abrirTraspaso={this.abrirTraspaso} 
                                abrirAnticipo={this.abrirAnticipo}
                                abrirAlerta={this.abrirAlerta} 
                                permisoPrecio={this.state.permisoPrecio} 
                                permisoDescuento={this.state.permisoDescuento}
                                descuentoMaximo={this.state.descuentoMaximo} 
                                RutaImpresionValeTraspaso={this.state.RutaImpresionValeTraspaso} 
                                disabledImpresionValeTraspaso={this.state.disabledImpresionValeTraspaso} 
                                mensajeImprimirValeTraspaso={this.state.mensajeImprimirValeTraspaso} 
                                nomArticuloVenta={this.state.nomArticuloVenta}
                                nomArticuloVentaPlural={this.state.nomArticuloVentaPlural}
                                nomUniNegVenta={this.state.nomUniNegVenta}
                                valBloquearOperacion={this.state.valBloquearOperacion}
                                parent={this} />

                            <TablaAnticipo 
                                anticipoCliente={this.state.anticipoCliente} 
                                onDeleteRow={this.onDeleteRow} 
                                parent={this} />
                        </div>
                    </div>

                    <div className="col-md-3">

                        <div className="row">
                            <div className="col-md-12">
                                <div className="panel panel-default">
                                    <div className="panel-heading">
                                        <div hidden={this.state.valBloquearOperacion}>
                                            Información del {this.state.nomClienteVenta}
                                            <OverlayTrigger placement="top" overlay={tooltipAgregarCliente}>
                                                <span onClick={this.mostrarAgregarClientes} className="badge pull-right cursor"><a><i aria-hidden="true" className="fa fa-plus"></i></a></span>
                                            </OverlayTrigger>
                                            <OverlayTrigger placement="top" overlay={tooltipModificarCliente}>
                                                <span onClick={this.modificarClientes} className="badge pull-right cursor espacio"><a><i aria-hidden="true" className="fa fa-pencil"></i></a></span>
                                            </OverlayTrigger>
                                            <OverlayTrigger placement="top" overlay={tooltipCambiarCliente}>
                                                <span onClick={this.mostrarBusquedaClientes} className="badge pull-right cursor espacio"><a><i aria-hidden="true" className="fa fa-search"></i></a></span>
                                            </OverlayTrigger>
                                        </div>
                                        <div hidden={this.state.valBloquearOperacion2}>
                                            Información del {this.state.nomClienteVenta}
                                        </div>
                                        <a href="../abcContribuyente.php" target="_blank" id="agregarcliente"></a>
                                        <a href="" target="_blank" id="modificarcliente"></a>
                                    </div>
                                    
                                        <ul className="list-group">
                                            <li className="list-group-item">{this.state.cliente.taxid}</li>
                                            <li className="list-group-item">{this.state.cliente.branchcode} - {this.state.cliente.name}</li>
                                            <li className="list-group-item">{this.state.cliente.email} <br/> <font color='red'> {this.state.validacionemailmsj}</font></li>
                                            <li className="list-group-item">{this.state.cliente.address}</li>
                                        </ul>
                                    
                                </div>
                            </div>
                        </div>

                        <div className="row">
                            <div className="col-md-12">
                                <Carrito 
                                productos={this.state.productos} 
                                anticipoCliente={this.state.anticipoCliente} 
                                subtotal={this.state.totales.subtotal} 
                                iva={this.state.totales.iva} 
                                total={this.state.totales.total}
                                operacionIva={this.state.operacionIva}
                                nomArticuloVentaPlural={this.state.nomArticuloVentaPlural}
                                parent={this} />
                            </div>
                        </div>

                        <div className="row">
                            <div className="col-md-12">
                                <div className="panel panel-default">
                                    <div className="panel-body">
                                        <div className="row">
                                            <div className="col-md-12 centrar-contenido">
                                                <form>
                                                    <div className="form-group">
                                                        <label htmlFor="comentarios" className="control-label">Comentarios</label>
                                                        <textarea id="comentarios" className="form-control" rows="3" onChange={this.onChangeComentarios} value={this.state.comments}></textarea>
                                                    </div>
                                                    <div className="form-group">
                                                        <label htmlFor="txtPagadorGen" className="control-label">Pagador</label>
                                                        <input id="txtPagadorGen" className="form-control" onChange={this.onChangePagador} value={this.state.txtPagadorGen}></input>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <div className="row">
                                            <div className="col-md-12 centrar-contenido" style={{"display": "none"}}>
                                                <label htmlFor="vendedor" className="control-label">Vendedor</label>
                                                <ComboGeneral selectValue={this.state.salesman} selectLabel={this.state.labelSelectVendedor} toolTipText={toolTipTextVendedor} seleccionarVendedor={this.seleccionarVendedor} obtenerSession={this.obtenerSession} api={apiVendedor} />
                                                <br/>
                                            </div>
                                            <div className="col-md-12 centrar-contenido" hidden={this.state.valBtnCotizacion}>
                                                <button className="btn btn-info info-button btn-block" onClick={this.guardarCotizacion} type="button">{this.state.nomCotizacionVenta}</button>
                                            </div>
                                            <div className="col-md-12 centrar-contenido" hidden={this.state.valBtnPagar}>
                                                <button className="btn btn-success info-button btn-block" type="button" onClick={this.abrirPago}>Pagar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <footer id="footer" className="navbar-default navbar-fixed-bottom footer">
                <div className="container-fluid">
                    <div className="collapse navbar-collapse">
                        <ul className="nav navbar-nav">
                            <li><p className="navbar-text"><span className="label label-default">{this.state.nombreEmpresa}</span></p></li>
                        </ul>
                        <ul className="nav navbar-nav navbar-right">
                            <li><p className="navbar-text"><span className="label label-default">OnAxis</span> 2016, todos los derechos reservados.</p></li>
                        </ul>
                    </div>
                </div>
            </footer>

        </div>
        )
    }
})

module.exports = App 

/*
<ModalAnticipo 
                    show={this.state.mostrarAnticipo} 
                    hideModal={this.cerrarAnticipo} 
                    branchcode={this.state.cliente.branchcode} 
                    name={this.state.cliente.name}
                    
                    anticipoCliente={this.state.anticipoCliente} 
                    
                    imgCargandoMostrar={this.state.imgCargandoMostrar} 
                    parent={this} />
 */

/*
                            <div className="col-md-12">
                                <div className="panel panel-default">
                                    <div className="panel-heading">
                                        Artículos
                                    </div>
                                    <div className="panel-body panel-productos" id="panel-productos">
                                        <div className="row">
                                            <div className="col-md-12">
                                                <TablaProductos productos={this.state.productos} onDeleteRow={this.onDeleteRow} abrirTraspaso={this.abrirTraspaso} abrirAlerta={this.abrirAlerta} permisoPrecio={this.state.permisoPrecio} permisoDescuento={this.state.permisoDescuento} descuentoMaximo={this.state.descuentoMaximo} RutaImpresionValeTraspaso={this.state.RutaImpresionValeTraspaso} disabledImpresionValeTraspaso={this.state.disabledImpresionValeTraspaso} mensajeImprimirValeTraspaso={this.state.mensajeImprimirValeTraspaso} parent={this}/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
 */

/*<div className="panel-body">
</div>*/

/*
<div className="col-md-12">
    <label><input defaultChecked type="checkbox" onChange={this.onChangeCheck} disabled={this.state.disabledCheckCorreo} /> Enviar E-mail </label>
</div>
*/

/*
<div className="navbar-header">
<i className="fa fa-shopping-cart fa-3x" aria-hidden="true"></i>
</div>
 */