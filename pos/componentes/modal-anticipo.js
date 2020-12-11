var React = require('react')
var Modal = require('react-bootstrap').Modal
var Button = require('react-bootstrap').Button

import {FormattedNumber} from 'react-intl';
import ComboGeneral from './combo-general';
import TablaAnticipo from './tabla-anticipo';

var ModalAnticipo = React.createClass({

    render: function() {

        var transparent = "rgba(255,255,255,0.5)";

        return (

            <Modal show={this.props.show} onHide={this.props.hideModal} bsSize="large" backdrop={false} style={{backgroundColor: transparent}}>
                <Modal.Header closeButton>
                    <Modal.Title>Anticipo</Modal.Title>
                </Modal.Header>
                <Modal.Body className="cuerpo-modal-pago">
                    <div className="row">
                        <div className="col-md-12">
                            <div className="panel panel-default">
                                <div className="panel-body">
                                    <h6>{this.props.branchcode} - {this.props.name}</h6>
                                    <TablaAnticipo 
                                    anticipoCliente={this.props.anticipoCliente} 
                                    onDeleteRow={this.onDeleteRow} 
                                    parent={this} />
                                </div>
                            </div>
                        </div>
                    </div>
                </Modal.Body>
            </Modal>
        );
    }
});
 
module.exports = ModalAnticipo 