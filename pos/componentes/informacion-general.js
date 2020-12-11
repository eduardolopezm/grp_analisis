var React = require('react');
var Collapse = require('react-bootstrap').Collapse;
var Tooltip = require('react-bootstrap').Tooltip;
var OverlayTrigger = require('react-bootstrap').OverlayTrigger;
//
import ComboGeneral from './combo-general';
import BuscarClientes from './buscar-clientes';
import BuscarCotizaciones from './buscar-cotizacion';
import axios from 'axios';

const tooltipAlmacen = (
  <Tooltip id="tooltip">Cambiar Objeto Principal</Tooltip>
);

const tooltipCotizacion = (
  <Tooltip id="tooltip">Buscar Pase de Cobro</Tooltip>
);

var InformacionGeneral = React.createClass({

    getInitialState: function() {
        return {
            datosCotBus: {
                value: '',
                suggestions: []
            },
            buscarCotizacionValor: "",
            datosCliBus: {
                value: '',
                suggestions: []
            },
            buscarClienteValor: ""
        };
    },

    seleccionarCliente: function(cliente) {
    	this.props.parent.setState({
    		cliente: {
                branchcode: cliente.debtorno,
                name: cliente.name,
                taxid: cliente.taxid,
                email: cliente.email,
                address: cliente.address
            },
            currency: cliente.currcode,
            usoCFDI: cliente.usoCFDI,
            usoCFDIName: cliente.usoCFDIName
    	});

        // Funcion para obtener anticipos
        this.props.abrirAnticipo();

        if (cliente.taxid == "XEXX010101000" || cliente.taxid == "XAXX010101000") {
            //Deshabilitar enviar por correo
            this.props.parent.setState({
                disabledCheckCorreo: true
            });
        }else{
            //Habilitar enviar por correo
            this.props.parent.setState({
                disabledCheckCorreo: false
            });
        }

    	this.props.parent.ocultarBusquedaClientes();

        this.props.parent.ObtenerListasPrecios(cliente.debtorno);
    },

    seleccionarCotizaciones: function(cotizacion) {
    	this.props.parent.buscarCotizacion(cotizacion.orderno);
        //cambiar unidad de negocio, almacen de la cotizacion seleccionada y informacionn del cliente
        console.log("cambio orderno: "+cotizacion.orderno+" tagref: "+cotizacion.tagref+" tagname: "+cotizacion.tagname
            +" - location: "+cotizacion.fromstkloc+" - locationname: "+cotizacion.locationname
            +" - cliente branchcode: "+cotizacion.branchcode
            +" - salesman: "+cotizacion.salesmancode+" - salesmanname: "+cotizacion.salesmanname);
        this.props.parent.setState({
            // tagref: cotizacion.tagref,
            // tagrefname: cotizacion.tagname,
            location: cotizacion.fromstkloc,
            locationname: cotizacion.locationname,
            orderno: cotizacion.orderno,
            cliente: {
                branchcode: cotizacion.branchcode,
                name: cotizacion.namedebtor,
                taxid: cotizacion.taxid,
                email: cotizacion.email,
                address: cotizacion.address
            },
            salesman: cotizacion.salesmancode,
            salesmanname: cotizacion.salesmanname,
            comments: cotizacion.comments,
            txtPagadorGen: cotizacion.txt_pagador,
            labelSelectVendedor: cotizacion.salesmanname,
            // labelSelectUnidadNegocio: cotizacion.tagname,
            labelSelectAlmacen: cotizacion.locationname,
            typeabbrev: cotizacion.ordertype,
            usoCFDI: cotizacion.usoCFDI,
            usoCFDIName: cotizacion.usoCFDIName,
            metodoPago: cotizacion.metodoPago,
            metodoPagoName: cotizacion.metodoPagoName
        });

        this.props.parent.ObtenerListasPrecios(cotizacion.branchcode);

        // this.props.parent.fnBloquearProcesos();
    },

    seleccionarAlmacen: function (value, label){
        //si cambia el almacen
        this.props.parent.setState({
            location: value,
            locationname: label,
            labelSelectAlmacen: label
        });
    }, 

    seleccionarUnidadNegocio: function (value, label){
        //si cambia el almacen
        this.props.parent.setState({
            tagref: value,
            tagrefname: label,
            labelSelectUnidadNegocio: label
        });
    },

    buscarCotizacionValor: function (value){
        // Se asigna la info tecleada para despues hacer la busqueda
        if (this.state.buscarCotizacionValor != value) {
            this.state.buscarCotizacionValor = value;
            this.setState({
                datosCotBus: {
                    value: value,
                    suggestions: []
                }
            });
        }
        //console.log("buscarCotizacionValor");
    },

    buscarCotizacionLista: function (){
        console.log("buscarCotizacionLista");
        console.log("buscarCotizacionValor: "+this.state.buscarCotizacionValor);

        var self = this;

        self.props.obtenerSession();

        //obtener cotizaciones
        axios.get('./../mvc/api/v1/getquotationbydesc/' + self.state.buscarCotizacionValor)
        .then(function (response) {
            if(response.data.success) {
                self.setState({
                    datosCotBus: {
                        value: self.state.buscarCotizacionValor,
                        suggestions: response.data.data
                    }
                });
                //console.log("todo bien datos");
            } else {
                self.setState({
                    datosCotBus: {
                        value: self.state.buscarCotizacionValor,
                        suggestions: [
                            {
                                orderno: '',
                                namedebtor: 'No existen cotizaciones con esos parametros.',
                                orddate: ''
                            }
                        ]
                    }
                });
                //console.log("error datos");
            }
            //console.log("suggestions: "+JSON.stringify(response.data.data));
            document.getElementById('txtBusqedaCotizacion').focus();
            //console.log("focus axios");
        })
        .catch(function (error) {
            // console.log(error);
        });
    },

    buscarClienteValor: function (value){
        // Se asigna la info tecleada para despues hacer la busqueda
        if (this.state.buscarClienteValor != value) {
            this.state.buscarClienteValor = value;
            this.setState({
                datosCliBus: {
                    value: value,
                    suggestions: []
                }
            });
        }
        //console.log("buscarCotizacionValor");
    },

    buscarClienteLista: function (){
        console.log("buscarClienteLista");
        console.log("buscarClienteValor: "+this.state.buscarClienteValor);

        var self = this;

        self.props.obtenerSession();

        //obtener cotizaciones
        axios.get('./../mvc/api/v1/getdebtors/' + self.state.buscarClienteValor)
        .then(function (response) {
            if(response.data.success) {
                self.setState({
                    datosCliBus: {
                        value: self.state.buscarClienteValor,
                        suggestions: response.data.data
                    }
                });
                //console.log("todo bien datos");
            } else {
                self.setState({
                    datosCliBus: {
                        value: self.state.buscarClienteValor,
                        suggestions: [
                            {
                                orderno: '',
                                namedebtor: 'No existen cotizaciones con esos parametros.',
                                orddate: ''
                            }
                        ]
                    }
                });
                //console.log("error datos");
            }
            //console.log("suggestions: "+JSON.stringify(response.data.data));
            document.getElementById('txtBusqedaCliente').focus();
            //console.log("focus axios");
        })
        .catch(function (error) {
            // console.log(error);
        });
    },

	render: function() {

		const toolTipTextUnidadNegocio = 'Cambiar '+this.props.nomUniNegVenta;
		const apiUnidadNegocio = {
			name: 'Unidades de Negocio',
			func: 'gettags',
			value: 'tagref',
			label: 'tagdescriptionc',
		};

		const toolTipTextAlmacen = 'Cambiar '+this.props.nomAlmacenVenta;
		const apiAlmacen = {
			name: 'Almacenes',
			func: 'getlocations/' + this.props.tagref,
			value: 'loccode',
			label: 'locationname',
		};

		return (
			<div>
				<Collapse in={this.props.parent.state.mostrarInfo}>
					<div className="row">
                        <div className="col-md-4 centrar-contenido">
                        	<ComboGeneral selectValue={this.props.tagref} selectLabel={this.props.parent.state.labelSelectUnidadNegocio} toolTipText={toolTipTextUnidadNegocio} seleccionarUnidadNegocio={this.seleccionarUnidadNegocio} obtenerSession={this.props.obtenerSession} api={apiUnidadNegocio} />
                        </div>
                        <div className="col-md-4 centrar-contenido">
                            <ComboGeneral selectValue={this.props.location} selectLabel={this.props.parent.state.labelSelectAlmacen} toolTipText={toolTipTextAlmacen} seleccionarAlmacen={this.seleccionarAlmacen} obtenerSession={this.props.obtenerSession} api={apiAlmacen} />
                        </div>
                        <div className="col-md-4 centrar-contenido">
                        	<OverlayTrigger placement="top" overlay={tooltipCotizacion}>
                            	<button className="btn btn-default btn-sm info-button" type="button" onClick={this.props.parent.mostrarBusquedaCotizaciones} ><i aria-hidden="true" className="fa fa-search"></i> {this.props.nomCotizacionVenta}</button>
                            </OverlayTrigger>
                        </div>
                    </div>
				</Collapse>
				<Collapse in={this.props.parent.state.mostrarBusquedaCliente}>
					<div className="row">
                        <div className="col-md-12 centrar-contenido">
                        	<div className="input-group">
							  	<span className="input-group-btn"><button className="btn btn-primary" type="button">{this.props.parent.state.nomClienteVenta}:</button></span>
							  	<BuscarClientes 
                                    seleccionarCliente={this.seleccionarCliente} 
                                    obtenerSession={this.props.obtenerSession} 
                                    nomClienteVenta={this.props.nomClienteVenta} 
                                    datosCliBus={this.state.datosCliBus}
                                    buscarClienteValor={this.buscarClienteValor}
                                    buscarClienteLista={this.buscarClienteLista}
                                    parent={this}/>
                                    <span className="input-group-btn"><button type="button" className="btn btn-primary btn" onClick={this.buscarClienteLista}><i className="fa fa-search" aria-hidden="true"></i> Buscar</button></span>
							  	<span className="input-group-btn"><button type="button" className="btn btn-default btn" onClick={this.props.parent.ocultarBusquedaClientes}><i className="fa fa-chevron-down text-info" aria-hidden="true"></i></button></span>
							</div>
                        </div>
                    </div>
                </Collapse>
                <Collapse in={this.props.parent.state.mostrarBusquedaCotizacion}>
                	<div className="row">
                        <div className="col-md-12 centrar-contenido">
                        	<div className="input-group">
							  	<span className="input-group-btn"><button className="btn btn-primary" type="button">{this.props.nomCotizacionVenta}:</button></span>
							  	<BuscarCotizaciones 
                                    seleccionarCotizaciones={this.seleccionarCotizaciones} 
                                    obtenerSession={this.props.obtenerSession} 
                                    datosCotBus={this.state.datosCotBus}
                                    buscarCotizacionValor={this.buscarCotizacionValor}
                                    buscarCotizacionLista={this.buscarCotizacionLista}
                                    nomCotizacionVenta={this.props.nomCotizacionVenta}
                                    parent={this}/>
                                <span className="input-group-btn"><button type="button" className="btn btn-primary btn" onClick={this.buscarCotizacionLista}><i className="fa fa-search" aria-hidden="true"></i> Buscar</button></span>
							  	<span className="input-group-btn"><button type="button" className="btn btn-default btn" onClick={this.props.parent.ocultarBusquedaCotizaciones}><i className="fa fa-chevron-down text-info" aria-hidden="true"></i></button></span>
							</div>
                        </div>
                    </div>
                </Collapse>
			</div>
		);
	}

});

module.exports = InformacionGeneral;