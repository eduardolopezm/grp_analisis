var React = require('react');
var update = require('react-addons-update');
var ReactBsTable = require('react-bootstrap-table');
var BootstrapTable = ReactBsTable.BootstrapTable;
var TableHeaderColumn = ReactBsTable.TableHeaderColumn;

var TablaTraspaso = React.createClass({

    onAfterSaveCell: function (row, cellName, cellValue) {
        
    },

    onBeforeSaveCell: function (row, cellName, cellValue) {
        var is_number = true;
        // No es un numero no lo guardes
        if(!isNaN(cellValue)) {
            if (Number(row.stock) < Number(cellValue)) {
                is_number = false;
                alert("La cantidad a solicitar debe ser menor a la disponible");
            }
        }else{
            alert("Ingresar solo numeros");
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
            <div>
                <BootstrapTable data={this.props.almacenesTraspaso} hover={true} bordered={false} height='340px' cellEdit={ cellEditProp } ref='table'>
                    <TableHeaderColumn dataField='stockid' width='50' editable={false}>Codigo</TableHeaderColumn>
                    <TableHeaderColumn width='100' isKey={ true } className='nowrap' dataField='locationname' editable={false}>{this.props.nomAlmacenVenta}</TableHeaderColumn>
                    <TableHeaderColumn dataField='stock' width='30' editable={false}>Disponible</TableHeaderColumn>                    
                    <TableHeaderColumn dataField='request' width='30'>Solicitar</TableHeaderColumn>
                </BootstrapTable>
            </div>
        );
    }
}); 

module.exports = TablaTraspaso; 