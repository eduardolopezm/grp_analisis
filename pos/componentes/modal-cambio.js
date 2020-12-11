var React = require('react')
var Modal = require('react-bootstrap').Modal
var Button = require('react-bootstrap').Button

import {FormattedNumber} from 'react-intl';
import Moneda from './moneda';
import ContenedorMonedas from './contenedor-monedas';
// import ComboGeneral from './combo-general';

// import Multiselect from './multi-select';

var contenedorMonedas;

var ModalCambio = React.createClass({

    getInitialState: function() {
        return {
            payment: 0,
            cambio: 0,
        };
    },

    seleccionarCambio: function(event) {
        // console.log("seleccionarCambio value: "+event.target.value);
        // this.props.parent.state.paymentReferencia = event.target.value;
        if (isNaN(event.target.value)) {
            // console.log("no es numero");
        } else {
            // console.log("es numero");
            this.setState({
                payment: event.target.value,
                cambio: event.target.value - this.props.totalGeneralIngreso
            });
        }
        
    },

    // fnProcesarCambio: function () {
    //     console.log("fnProcesarCambio 12345");
    //     // this.crearDocumento('factura', factura, 1);
    // },

	render: function() { 

        var transparent = "rgba(255,255,255,0.5)"; 

		return (
			<Modal show={this.props.show} onHide={this.props.hideModal} bsSize="sm" backdrop={false} style={{backgroundColor: transparent}}>
          		<Modal.Header closeButton>
            		<Modal.Title>Cambio</Modal.Title>
          		</Modal.Header>
          		<Modal.Body className="cuerpo-modal-pago">
                    <div className="row">
                        <div className="col-md-12">
                            <input type="text" className="form-control" title="Cantidad Recibida" placeholder="Cantidad Recibida" id="txtCambioPago" name="txtCambioPago" onChange={this.seleccionarCambio} />
                            <br/>
                            <table className="table tabla-totales">
                                <tbody>
                                    <tr>
                                        <td className="izquierda">Total a Pagar</td>
                                        <td className="numero"><FormattedNumber value={this.props.totalGeneralIngreso} style="currency" currency="USD" /></td>
                                    </tr>
                                    <tr>
                                        <td className="izquierda border_bottom">Cantidad Recibida</td>
                                        <td className="numero border_bottom"><FormattedNumber value={this.state.payment} style="currency" currency="USD" /></td>
                                    </tr>
                                    <tr>
                                        <td className="izquierda total-carrito">Cambio a Devolver</td>
                                        <td className="numero total-carrito"><FormattedNumber value={this.state.cambio} style="currency" currency="USD" /></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <img src="assets/img/loading.gif" width="200" height="150" className="imgCargando" style={{"display": this.props.parent.state.imgCargandoMostrar}}/>
                    
          		</Modal.Body>
                <Modal.Footer>
                    <button className="btn btn-primary info-button" onClick={this.props.fnProcesarCambio} type="button" disabled={this.props.btnFacturaDisabled}>Procesar</button>
                </Modal.Footer>
        	</Modal>
		);
	}
})

module.exports = ModalCambio
