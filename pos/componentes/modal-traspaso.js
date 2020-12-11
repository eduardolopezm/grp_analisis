var React = require('react')
var Modal = require('react-bootstrap').Modal
var Button = require('react-bootstrap').Button

import {FormattedNumber} from 'react-intl';
import ComboGeneral from './combo-general';
import TablaTraspaso from './tabla-traspaso';

var ModalTraspaso = React.createClass({

    render: function() {

        var transparent = "rgba(255,255,255,0.5)";

        return (

            <Modal show={this.props.show} onHide={this.props.hideModal} bsSize="large" backdrop={false} style={{backgroundColor: transparent}}>
                <Modal.Header closeButton>
                    <Modal.Title>Realizar Trapaso</Modal.Title>
                </Modal.Header>
                <Modal.Body className="cuerpo-modal-pago">
                    <div className="row">
                        <div className="col-md-12">
                            <div className="panel panel-default">
                                <div className="panel-body">
                                    <h6>{this.props.productoDescripcion}</h6>
                                    <TablaTraspaso almacenesTraspaso={this.props.almacenesTraspaso} onDeleteRow={this.onDeleteRow} abrirTraspaso={this.props.abrirTraspaso} nomAlmacenVenta={this.props.nomAlmacenVenta} parent={this}/>
                                    <h6><button className="btn btn-default info-button" onClick={this.props.guardarTraspaso} type="button">Traspasar</button></h6>
                                    <img src="assets/img/loading.gif" width="200" height="150" className="imgCargando" style={{"display": this.props.parent.state.imgCargandoMostrar}}/>
                                </div>
                            </div>
                        </div>
                    </div>
                </Modal.Body>
            </Modal>
        );
    }
});
 
module.exports = ModalTraspaso 