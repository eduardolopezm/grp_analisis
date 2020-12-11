package com.jahepi.activemq.database;

import java.sql.PreparedStatement;

import org.apache.log4j.Logger;

import com.jahepi.activemq.Utils;
import com.jahepi.activemq.database.Database.DBPreparedStatement;
import com.jahepi.activemq.dto.QueueMessage;
import com.jahepi.activemq.loader.Config.ConfigData;

public class ConsumerDBHelper {
	
	final static Logger logger = Logger.getLogger(ConsumerDBHelper.class);

	private Database database;
	private ConfigData config;

	public ConsumerDBHelper(Database database, ConfigData config) {
		this.database = database;
		this.config = config;
	}

	public boolean saveMessage(QueueMessage msg) {
		String sql = "", sqlLog = "", reference = "", transferline = "";
		String finalLogSql = "";
		DBPreparedStatement dbPreparedStatement;
		PreparedStatement ps;
		boolean success = true;

		if(msg.getOperacion().equals("actualizado")) {
			sql = "UPDATE loctransfers SET " +
					"stockid = ?, " +
					"shipqty = ?, " +
					"recqty = ?, " +
					"shipdate = ?, " +
					"recdate = ?, " +
					"shiploc = ?, " +
					"shipsecloc = ?, " +
					"recloc = ?, " +
					"recsecloc = ?,	" +
					"comments = ?, " +
					"serialno = ?, " +
					"userregister = ?, " +
					"userrec = ?, " +
					"usercancel = ?, " +
					"statustransfer = ?, " +
					"qtycancel = ?, " +
					"requisitionno = ?, " +
					"debtorno = ?, " +
					"branchcode = ?, " +
					"cost = ?, " +
					"initialcost = ?, " +
					"quantity = ?, " +
					"NoContratoConsigAuthCust = ?, " +
					"wo = ?, " +
					"tipoconversion = ?, " +
					"identifytransfer = ?, " +
					"quantitysend = ?, " +
					"idestatus = ?,	" +
					"comentGeneral = ?, " +
					"canceldate = ?, " +
					"requisitiondate = ?, " +
					"elaborationdate = ?, " +
					"expirationdate = ?, " +
					"freezingdate = ?, " +
					"qty_excess = ?, " +
					"type = ?, " +
					"SAPBorrado = ?, " +
					"recnowqty = ?, " +
					"enviadaSAP = ?, " +
					"doctoSAP = ?, " +
					"errordescripcion = ?, " +
					"codigoMsg = ?, " +
					"tagref = ?, " +
					"SAPcentro = ?, " +
					"SAPActualizado = '0', " +
					"qty_missing = ?, " +
					"secondfactorconversion = ?, " +
					"transferenceshipdate = ?, " +
					"idtipotransferencia = ?, " +
					"folioentregaSAP = ?, " +
					"description = ?, " +
					"identifieramq = ?, " +
					"countline = ?, " +
					"FolioConfEntrega = ? " +
					" WHERE reference = ? " +
					"AND stockid = ? ";
			sqlLog = "UPDATE loctransfers SET " +
					"stockid = '%s', " +
					"shipqty = '%s', " +
					"recqty = '%s', " +
					"shipdate = '%s', " +
					"recdate = '%s', " +
					"shiploc = '%s', " +
					"shipsecloc = '%s', " +
					"recloc = '%s', " +
					"recsecloc = '%s',	" +
					"comments = '%s', " +
					"serialno = '%s', " +
					"userregister = '%s', " +
					"userrec = '%s', " +
					"usercancel = '%s', " +
					"statustransfer = '%s', " +
					"qtycancel = '%s', " +
					"requisitionno = '%s', " +
					"debtorno = '%s', " +
					"branchcode = '%s', " +
					"cost = '%s', " +
					"initialcost = '%s', " +
					"quantity = '%s', " +
					"NoContratoConsigAuthCust = '%s', " +
					"wo = '%s', " +
					"tipoconversion = '%s', " +
					"identifytransfer = '%s', " +
					"quantitysend = '%s', " +
					"idestatus = '%s',	" +
					"comentGeneral = '%s', " +
					"canceldate = '%s', " +
					"requisitiondate = '%s', " +
					"elaborationdate = '%s', " +
					"expirationdate = '%s', " +
					"freezingdate = '%s', " +
					"qty_excess = '%s', " +
					"type = '%s', " +
					"SAPBorrado = '%s', " +
					"recnowqty = '%s', " +
					"enviadaSAP = '%s', " +
					"doctoSAP = '%s', " +
					"errordescripcion = '%s', " +
					"codigoMsg = '%s', " +
					"tagref = '%s', " +
					"SAPcentro = '%s', " +
					"SAPActualizado = '0', " +
					"qty_missing = '%s', " +
					"secondfactorconversion = '%s', " +
					"transferenceshipdate = '%s', " +
					"idtipotransferencia = '%s', " +
					"folioentregaSAP = '%s', " +
					"description = '%s', " +
					"identifieramq = '%s', " +
					"countline = '%s', " +
					"FolioConfEntrega = '%s' " +
					" WHERE reference = '%s' " +
					"AND stockid = '%s' ";
		}
		else if( msg.getOperacion().equals("nuevo") || msg.getOperacion().equals("nuevosap") ) {
			
			int SapActualizado = 0;
			if(msg.getOperacion().equals("nuevo")) {
				SapActualizado = 1;
			}
			
			sql = "INSERT INTO loctransfers ("
					+ "reference, "
					+ "stockid, "
					+ "shipqty, "
					+ "recqty, "
					+ "shipdate, "
					+ "recdate, "
					+ "shiploc, "
					+ "shipsecloc, "
					+ "recloc, "
					+ "recsecloc, "
					+ "comments, "
					+ "serialno, "
					+ "userregister, "
					+ "userrec, "
					+ "usercancel, "
					+ "statustransfer, "
					+ "qtycancel, "
					+ "transferline, "
					+ "requisitionno, "
					+ "debtorno, "
					+ "branchcode, "
					+ "cost, "
					+ "initialcost, "
					+ "NoContratoConsigAuthCust, "
					+ "wo, "
					+ "quantity, "
					+ "tipoconversion, "
					+ "identifytransfer, "
					+ "quantitysend, "
					+ "idestatus, "
					+ "comentGeneral, "
					+ "canceldate, "
					+ "requisitiondate, "
					+ "elaborationdate, "
					+ "expirationdate, "
					+ "freezingdate, "
					+ "qty_excess, "
					+ "type, "
					+ "SAPBorrado, "
					+ "recnowqty, "
					+ "enviadaSAP, "
					+ "doctoSAP, "
					+ "errordescripcion, "
					+ "codigoMsg, "
					+ "tagref, "
					+ "SAPcentro, "
					+ "SAPActualizado, "
					+ "qty_missing, "
					+ "secondfactorconversion, "
					+ "transferenceshipdate, "
					+ "idtipotransferencia, "
					+ "description, "
					+ "identifieramq, "
					+ "countline, "
					+ "FolioConfEntrega, "
					+ "folioentregaSAP) "
					+ "VALUES ( "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "'" + SapActualizado + "', "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?, "
					+ "?)";
			sqlLog = "INSERT INTO loctransfers ("
					+ "reference, "
					+ "stockid, "
					+ "shipqty, "
					+ "recqty, "
					+ "shipdate, "
					+ "recdate, "
					+ "shiploc, "
					+ "shipsecloc, "
					+ "recloc, "
					+ "recsecloc, "
					+ "comments, "
					+ "serialno, "
					+ "userregister, "
					+ "userrec, "
					+ "usercancel, "
					+ "statustransfer, "
					+ "qtycancel, "
					+ "transferline, "
					+ "requisitionno, "
					+ "debtorno, "
					+ "branchcode, "
					+ "cost, "
					+ "initialcost, "
					+ "NoContratoConsigAuthCust, "
					+ "wo, "
					+ "quantity, "
					+ "tipoconversion, "
					+ "identifytransfer, "
					+ "quantitysend, "
					+ "idestatus, "
					+ "comentGeneral, "
					+ "canceldate, "
					+ "requisitiondate, "
					+ "elaborationdate, "
					+ "expirationdate, "
					+ "freezingdate, "
					+ "qty_excess, "
					+ "type, "
					+ "SAPBorrado, "
					+ "recnowqty, "
					+ "enviadaSAP, "
					+ "doctoSAP, "
					+ "errordescripcion, "
					+ "codigoMsg, "
					+ "tagref, "
					+ "SAPcentro, "
					+ "SAPActualizado, "
					+ "qty_missing, "
					+ "secondfactorconversion, "
					+ "transferenceshipdate, "
					+ "idtipotransferencia, "
					+ "description, "
					+ "identifieramq, "
					+ "countline, "
					+ "FolioConfEntrega, "
					+ "folioentregaSAP) "
					+ "VALUES ("
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'0', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s', "
					+ "'%s')";
		}
		else if(msg.getOperacion().equals("recibir") ) {
			sql = "UPDATE loctransfers SET recqty = ?, recnowqty = ?, idestatus=?, SAPActualizado = '4' WHERE reference = ? and stockid = ?";
			sqlLog = "UPDATE loctransfers SET  recqty = '%s' , recnowqty = '%s', idestatus='%s', SAPActualizado = '4'  WHERE reference = '%s' and stockid = '%s'";
		}
		
		dbPreparedStatement = this.database.getPreparedStatement(sql);
		ps = dbPreparedStatement.getPreparedStatement();
		if (ps != null) {
			try {
				
				
				String recdate = "";
				if(msg.getRecdate() == null) {
					recdate = "0000-00-00";
				}
				else {
					recdate = msg.getRecdate();
				}
				
				if(msg.getOperacion().equals("nuevo") || msg.getOperacion().equals("nuevosap") ) {
					ps.setString(1, msg.getReference());
					ps.setString(2, msg.getStockid());
					ps.setString(3, msg.getShipqty());
					ps.setString(4, msg.getRecqty());
					ps.setString(5, msg.getShipdate());
					ps.setString(6, recdate);
					ps.setString(7, msg.getShiploc());
					ps.setString(8, msg.getShipsecloc());
					ps.setString(9, msg.getRecloc());
					ps.setString(10, msg.getRecsecloc());
					ps.setString(11, msg.getComments());
					ps.setString(12, msg.getSerialno());
					ps.setString(13, msg.getUserregister());
					ps.setString(14, msg.getUserrec());
					ps.setString(15, msg.getUsercancel());
					ps.setString(16, msg.getStatustransfer());
					ps.setString(17, msg.getQtycancel());
					ps.setString(18, msg.getTransferline());
					ps.setString(19, msg.getRequisitionno());
					ps.setString(20, msg.getDebtorno());
					ps.setString(21, msg.getBranchcode());
					ps.setString(22, msg.getCost());
					ps.setString(23, msg.getInitialcost());
					ps.setString(24, msg.getNoContratoConsigAuthCust());
					ps.setString(25, msg.getWo());
					ps.setString(26, msg.getQuantity());
					ps.setString(27, msg.getTipoconversion());
					ps.setString(28, msg.getIdentifytransfer());
					ps.setString(29, msg.getQuantitysend());
					if(msg.getOperacion().equals("nuevosap")) {
						ps.setInt(30,  msg.getIdestatus());
					} else {
						ps.setInt(30,  msg.getNextstatus());
					}
					ps.setString(31, msg.getComentGeneral());
					ps.setString(32, msg.getCanceldate());
					ps.setString(33, msg.getRequisitiondate());	
					ps.setString(34, msg.getElaborationdate());
					ps.setString(35, msg.getExpirationdate());
					ps.setString(36, msg.getFreezingdate());
					ps.setString(37, msg.getQty_excess());
					ps.setString(38, msg.getType());
					ps.setString(39, msg.getSAPBorrado());
					ps.setString(40, msg.getRecnowqty());
					ps.setString(41, msg.getEnviadaSAP());
					ps.setString(42, msg.getDoctoSAP());
					ps.setString(43, msg.getErrordescripcion());
					ps.setString(44, msg.getCodigoMsg());
					ps.setString(45, msg.getTagref());
					ps.setString(46, msg.getSAPcentro());
					ps.setString(47, msg.getQty_missing());
					ps.setString(48, msg.getSecondfactorconversion());
					ps.setString(49, msg.getTransferenceshipdate());
					ps.setString(50, msg.getIdtipotransferencia());
					ps.setString(51, msg.getDescription());
					ps.setString(52, msg.getIdentifieramq());
					ps.setString(53, msg.getCountline());
					ps.setString(54, msg.getFolioConfEntrega());
					ps.setString(55, msg.getFolioentregaSAP());
					
					finalLogSql = String.format(
						sqlLog,
						msg.getReference(),
						msg.getStockid(),
						msg.getShipqty(),
						msg.getRecqty(),
						msg.getShipdate(),
						recdate,
						msg.getShiploc(),
						msg.getShipsecloc(),
						msg.getRecloc(),
						msg.getRecsecloc(),
						msg.getComments(),
						msg.getSerialno(),
						msg.getUserregister(),
						msg.getUserrec(),
						msg.getUsercancel(),
						msg.getStatustransfer(),
						msg.getQtycancel(),
						msg.getTransferline(),
						msg.getRequisitionno(),
						msg.getDebtorno(),
						msg.getBranchcode(),
						msg.getCost(),
						msg.getInitialcost(),
						msg.getNoContratoConsigAuthCust(),
						msg.getWo(),
						msg.getQuantity(),
						msg.getTipoconversion(),
						msg.getIdentifytransfer(),
						msg.getQuantitysend(),
						msg.getNextstatus(),
						msg.getComentGeneral(),
						msg.getCanceldate(),
						msg.getRequisitiondate(),	
						msg.getElaborationdate(),
						msg.getExpirationdate(),
						msg.getFreezingdate(),
						msg.getQty_excess(),
						msg.getType(),
						msg.getSAPBorrado(),
						msg.getRecnowqty(),
						msg.getEnviadaSAP(),
						msg.getDoctoSAP(),
						msg.getErrordescripcion(),
						msg.getCodigoMsg(),
						msg.getTagref(),
						msg.getSAPcentro(),
						msg.getQty_missing(),
						msg.getSecondfactorconversion(),
						msg.getTransferenceshipdate(),
						msg.getIdtipotransferencia(),
						msg.getDescription(),
						msg.getIdentifieramq(),
						msg.getCountline(),
						msg.getFolioConfEntrega(),
						msg.getFolioentregaSAP()
					);
				}
				else if(msg.getOperacion().equals("actualizado") ) {
					
					ps.setString(1, msg.getStockid());
					ps.setString(2, msg.getShipqty());
					ps.setString(3, msg.getRecqty());
					ps.setString(4, msg.getShipdate());
					ps.setString(5, recdate);
					ps.setString(6, msg.getShiploc());
					ps.setString(7, msg.getShipsecloc());
					ps.setString(8, msg.getRecloc());
					ps.setString(9, msg.getRecsecloc());
					ps.setString(10, msg.getComments());
					ps.setString(11, msg.getSerialno());
					ps.setString(12, msg.getUserregister());
					ps.setString(13, msg.getUserrec());
					ps.setString(14, msg.getUsercancel());
					ps.setString(15, msg.getStatustransfer());
					ps.setString(16, msg.getQtycancel());
					ps.setString(17, msg.getRequisitionno());
					ps.setString(18, msg.getDebtorno());
					ps.setString(19, msg.getBranchcode());
					ps.setString(20, msg.getCost());
					ps.setString(21, msg.getInitialcost());
					ps.setString(22, msg.getQuantity());
					ps.setString(23, msg.getNoContratoConsigAuthCust());
					ps.setString(24, msg.getWo());
					ps.setString(25, msg.getTipoconversion());
					ps.setString(26, msg.getIdentifytransfer());
					ps.setString(27, msg.getQuantitysend());
					ps.setInt(28,  msg.getIdestatus());
					ps.setString(29, msg.getComentGeneral());
					ps.setString(30, msg.getCanceldate());
					ps.setString(31, msg.getRequisitiondate());	
					ps.setString(32, msg.getElaborationdate());
					ps.setString(33, msg.getExpirationdate());
					ps.setString(34, msg.getFreezingdate());
					ps.setString(35, msg.getQty_excess());
					ps.setString(36, msg.getType());
					ps.setString(37, msg.getSAPBorrado());
					ps.setString(38, msg.getRecnowqty());
					ps.setString(39, msg.getEnviadaSAP());
					ps.setString(40, msg.getDoctoSAP());
					ps.setString(41, msg.getErrordescripcion());
					ps.setString(42, msg.getCodigoMsg());
					ps.setString(43, msg.getTagref());
					ps.setString(44, msg.getSAPcentro());
					ps.setString(45, msg.getQty_missing());
					ps.setString(46, msg.getSecondfactorconversion());
					ps.setString(47, msg.getTransferenceshipdate());
					ps.setString(48, msg.getIdtipotransferencia());
					ps.setString(49, msg.getFolioentregaSAP());
					ps.setString(50, msg.getDescription());
					ps.setString(51, msg.getIdentifieramq());
					ps.setString(52, msg.getCountline());
					ps.setString(53, msg.getFolioConfEntrega());
					ps.setString(54, msg.getReference());
					ps.setString(55, msg.getStockid());
					
					finalLogSql = String.format(
						sqlLog,
						msg.getStockid(),
						msg.getShipqty(),
						msg.getRecqty(),
						msg.getShipdate(),
						recdate,
						msg.getShiploc(),
						msg.getShipsecloc(),
						msg.getRecloc(),
						msg.getRecsecloc(),
						msg.getComments(),
						msg.getSerialno(),
						msg.getUserregister(),
						msg.getUserrec(),
						msg.getUsercancel(),
						msg.getStatustransfer(),
						msg.getQtycancel(),
						msg.getRequisitionno(),
						msg.getDebtorno(),
						msg.getBranchcode(),
						msg.getCost(),
						msg.getInitialcost(),
						msg.getNoContratoConsigAuthCust(),
						msg.getWo(),
						msg.getQuantity(),
						msg.getTipoconversion(),
						msg.getIdentifytransfer(),
						msg.getQuantitysend(),
						msg.getIdestatus(),
						msg.getComentGeneral(),
						msg.getCanceldate(),
						msg.getRequisitiondate(),	
						msg.getElaborationdate(),
						msg.getExpirationdate(),
						msg.getFreezingdate(),
						msg.getQty_excess(),
						msg.getType(),
						msg.getSAPBorrado(),
						msg.getRecnowqty(),
						msg.getEnviadaSAP(),
						msg.getDoctoSAP(),
						msg.getErrordescripcion(),
						msg.getCodigoMsg(),
						msg.getTagref(),
						msg.getSAPcentro(),
						msg.getQty_missing(),
						msg.getSecondfactorconversion(),
						msg.getTransferenceshipdate(),
						msg.getIdtipotransferencia(),
						msg.getFolioentregaSAP(),
						msg.getDescription(),
						msg.getIdentifieramq(),
						msg.getCountline(),
						msg.getFolioConfEntrega(),
						msg.getReference(),
						msg.getStockid()
					);
				}
				else if (msg.getOperacion().equals("recibir")) {
					ps.setString(1, msg.getRecqty());
					ps.setString(2, msg.getRecnowqty());
					ps.setLong(3, msg.getIdestatus());
					ps.setString(4, msg.getReference());
					ps.setString(5, msg.getStockid());
					
					finalLogSql = String.format(
						sqlLog,
						msg.getRecqty(),
						msg.getRecnowqty(),
						msg.getIdestatus(),
						msg.getReference(),
						msg.getStockid()
					);
				}
				
				reference = msg.getReference();
				transferline = msg.getTransferline();
				
				logger.debug(finalLogSql);

				ps.executeUpdate();

				// success = this.database.executeUpdate(msg.getSql());
				success = true;

			} catch (Exception e) {
				success = false;
				if (this.config.get("onErrorSaveFile").equals("1")) {
					success = Utils.saveFile(config, finalLogSql, "transferencia", reference, transferline);
				}
				
				logger.error("Error desconocido", e);
			} finally {
				dbPreparedStatement.close();
			}
		}

		return success;
	}
	

	public void disconnect() {
		if(this.database.isConnected()) {
			this.database.disconnect();
		}
	}
}
