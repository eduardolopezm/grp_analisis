var React = require('react');
var update = require('react-addons-update');
var ReactBsTable = require('react-bootstrap-table');
var BootstrapTable = ReactBsTable.BootstrapTable;
var TableHeaderColumn = ReactBsTable.TableHeaderColumn;
var Tooltip = require('react-bootstrap').Tooltip;
var OverlayTrigger = require('react-bootstrap').OverlayTrigger;
//
import {FormattedNumber} from 'react-intl';
import axios from 'axios';

var TablaAnticipo = React.createClass({

    priceFormatter: function (cell, row){
        return <FormattedNumber value={cell} style="currency" currency="USD" />;
    },

    onAfterSaveCell: function (row, cellName, cellValue) {
        var row_index = 0;

        var obj = this.props.anticipoCliente.filter(function(element, index, array) {
            if(element.id === row.id) {
                row_index = index;
            }
            return element;
        });

        var updated_row = update(this.props.anticipoCliente[row_index], {
            $cellName: {
                $set: Number(Math.abs(cellValue))
            }
        }); 

        var newRowData = update(this.props.anticipoCliente, {
            $splice: [[row_index, 1, updated_row]]
        });
        // console.log("****************");
        // console.log("anticipoCliente antes: "+JSON.stringify(this.props.parent.state.anticipoCliente));
        this.props.parent.setState({
            anticipoCliente: newRowData 
        });
        // console.log("anticipoCliente despues: "+JSON.stringify(this.props.parent.state.anticipoCliente));
    },

    onBeforeSaveCell: function (row, cellName, cellValue) {
        var is_number = true;
        // No es un numero no lo guardes
        if(!isNaN(cellValue)) {
            if (Number(row.pendiente) < Number(cellValue)) {
                is_number = false;
                alert("La cantidad a solicitar debe ser menor a la pendiente");
            }
        }else{
            alert("Ingresar solo numeros");
            is_number = false;
        }
        if (cellValue < 0) {
            //si no es positivo
            is_number = false;
        }
        return is_number;
    },

    render: function() {

        const cellEditProp = {
            mode: "click",
            blurToSave: true,
            beforeSaveCell: this.onBeforeSaveCell,
            afterSaveCell: this.onAfterSaveCell
        };        

        return (
            <div className="col-md-12" style={{"display": "none"}}>
                <div className="panel panel-default">
                    <div className="panel-heading">
                        Anticipos
                    </div>
                    <div className="panel-body panel-productos" id="panel-productos">
                        <div className="row">
                            <div className="col-md-12">

                                <div>
                                    <BootstrapTable data={this.props.anticipoCliente} hover={true} bordered={false} height='340px' cellEdit={ cellEditProp } ref='table'>
                                        <TableHeaderColumn dataField='typename' width='50' editable={false}>Documento</TableHeaderColumn>
                                        <TableHeaderColumn width='100' isKey={ true } className='nowrap' dataField='folio' editable={false}>Folio</TableHeaderColumn>
                                        <TableHeaderColumn dataField='monto' width='30' dataFormat={this.priceFormatter} editable={false}>Monto</TableHeaderColumn>
                                        <TableHeaderColumn dataField='pendiente' width='30' dataFormat={this.priceFormatter} editable={false}>Pendiente</TableHeaderColumn>
                                        <TableHeaderColumn dataField='MontoAnticipo' width='30' dataFormat={this.priceFormatter}>Aplicando</TableHeaderColumn>
                                    </BootstrapTable>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}); 

module.exports = TablaAnticipo; 