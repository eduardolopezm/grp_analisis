package com.jahepi.activemq.database;

import java.sql.ResultSet;
import java.util.ArrayList;
import java.util.EmptyStackException;

import org.apache.log4j.Logger;

import com.jahepi.activemq.database.Database.DBResultSet;
import com.jahepi.activemq.dto.QueueMessage;
import com.jahepi.activemq.loader.Config.ConfigData;

public class ProducerDBHelper {
	
	final static Logger logger = Logger.getLogger(ProducerDBHelper.class);

	private Database database;
	private ConfigData config;

	public ProducerDBHelper(Database database, ConfigData config) {
		this.database = database;
		this.config = config;
	}

	public ArrayList<QueueMessage> getMessages() {
		ArrayList<QueueMessage> messages = new ArrayList<QueueMessage>();
		String sql = "";
		DBResultSet result;
		ResultSet rs;
		Integer idestatusrec = 0;
		
		sql = "SELECT id " +
				"	FROM loctransferstatus " +
				"	WHERE flagrecibir = 1";
		result = this.database.executeQuery(sql);
		rs = result.getResultSet();
		try {
			while (rs.next()){
				idestatusrec = rs.getInt("id");
			}
		}catch (Exception e) {
			logger.error("Error desconocido", e);
		}
		
		String where = "";
		String location = "";
		switch (this.config.get("type")) {
		case "central":
			location = "recloc";
			where = "WHERE loctransfers.SAPActualizado in ('1','2') "
					+ " AND loctransfers.identifieramq LIKE '%" + this.config.get("unidad") + "%'"
					+ " -- AND tags_queue.type = '" + this.config.get("type") + "'";
			break;
		case "satelite":
			location = "shiploc";
			where = "WHERE ((loctransferstatus.flagenviar = 1 "
					+ "AND loctransfers.SAPActualizado in ('1','2')) "
					+ "OR loctransfers.SAPActualizado = '3') "
					+ "-- AND tags_queue.type = '" + this.config.get("type") + "'";
			break;
		default:
			throw new EmptyStackException();
		}
		
		sql = "SELECT "
				+ "loctransfers.reference, "
				+ "loctransfers.stockid, "
				+ "loctransfers.description, "
				+ "loctransfers.FolioConfEntrega, "
				+ "loctransfers.shipqty, "
				+ "loctransfers.recqty, "
				+ "loctransfers.shipdate, "
				+ "loctransfers.recdate, "
				+ "loctransfers.shiploc, "
				+ "loctransfers.shipsecloc, "
				+ "loctransfers.recloc, "
				+ "loctransfers.recsecloc, "
				+ "loctransfers.comments, "
				+ "loctransfers.serialno, "
				+ "loctransfers.userregister, "
				+ "loctransfers.userrec, "
				+ "loctransfers.usercancel, "
				+ "loctransfers.statustransfer, "
				+ "loctransfers.qtycancel, "
				+ "loctransfers.transferline, "
				+ "loctransfers.requisitionno, "
				+ "loctransfers.debtorno, "
				+ "loctransfers.branchcode, "
				+ "loctransfers.cost, "
				+ "loctransfers.initialcost, "
				+ "loctransfers.NoContratoConsigAuthCust, "
				+ "loctransfers.wo, "
				+ "loctransfers.quantity, "
				+ "loctransfers.tipoconversion, "
				+ "loctransfers.identifytransfer, "
				+ "loctransfers.quantitysend, "
				+ "loctransfers.idestatus, "
				+ "loctransfers.comentGeneral, "
				+ "loctransfers.canceldate, "
				+ "loctransfers.requisitiondate, "
				+ "loctransfers.elaborationdate, "
				+ "loctransfers.expirationdate, "
				+ "loctransfers.freezingdate, "
				+ "loctransfers.qty_excess, "
				+ "loctransfers.type, "
				+ "loctransfers.SAPBorrado, "
				+ "loctransfers.recnowqty, "
				+ "loctransfers.enviadaSAP, "
				+ "loctransfers.doctoSAP, "
				+ "loctransfers.errordescripcion, "
				+ "loctransfers.codigoMsg, "
				+ "loctransfers.tagref, "
				+ "loctransfers.SAPcentro, "
				+ "loctransfers.SAPActualizado, "
				+ "loctransfers.qty_missing, "
				+ "loctransfers.secondfactorconversion, "
				+ "loctransfers.transferenceshipdate, "
				+ "loctransfers.idtipotransferencia, "
				+ "loctransfers.folioentregaSAP, "
				+ "loctransfers.identifieramq, "
				+ "loctransfers.countline, "
				+ "if(loctransferstatus.flagenviar = 1, 1, 0) as nuevo, "
				+ "if(loctransfers.SAPActualizado = 1, 1, 0) as actualizado, "
				+ "if(loctransfers.SAPActualizado = 2, 1, 0) AS nuevosap, "
				+ "if(loctransfers.SAPActualizado = 3, 1, 0) as recibir "
				+ "FROM loctransfers "
				+ "INNER JOIN loctransferstatus ON loctransferstatus.id = loctransfers.idestatus "
				+ "INNER JOIN locations ON loctransfers." + location + " = locations.loccode ";
				
		sql = sql + where;
		
		logger.debug(sql);

		result = this.database.executeQuery(sql);
		rs = result.getResultSet();
		try {
			while (rs.next()) {
				
				QueueMessage msg = new QueueMessage();
				
				msg.setReference(rs.getString("reference"));
				msg.setStockid(rs.getString("stockid"));
				msg.setDescription(rs.getString("description"));
				msg.setFolioConfEntrega(rs.getString("FolioConfEntrega"));
				msg.setShipqty(rs.getString("shipqty"));
				msg.setRecqty(rs.getString("recqty"));
				msg.setShipdate(rs.getString("shipdate"));
				msg.setRecdate(rs.getString("recdate"));
				msg.setShiploc(rs.getString("shiploc"));
				msg.setShipsecloc(rs.getString("shipsecloc"));
				msg.setRecloc(rs.getString("recloc"));
				msg.setRecsecloc(rs.getString("recsecloc"));
				msg.setComments(rs.getString("comments"));
				msg.setSerialno(rs.getString("serialno"));
				msg.setUserregister(rs.getString("userregister"));
				msg.setUserrec(rs.getString("userrec"));
				msg.setUsercancel(rs.getString("usercancel"));
				msg.setStatustransfer(rs.getString("statustransfer"));
				msg.setQtycancel(rs.getString("qtycancel"));
				msg.setTransferline(rs.getString("transferline"));
				msg.setRequisitionno(rs.getString("requisitionno"));
				msg.setDebtorno(rs.getString("debtorno"));
				msg.setBranchcode(rs.getString("branchcode"));
				msg.setCost(rs.getString("cost"));
				msg.setInitialcost(rs.getString("initialcost"));
				msg.setNoContratoConsigAuthCust(rs.getString("NoContratoConsigAuthCust"));
				msg.setWo(rs.getString("wo"));
				msg.setQuantity(rs.getString("quantity"));
				msg.setTipoconversion(rs.getString("tipoconversion"));
				msg.setIdentifytransfer(rs.getString("identifytransfer"));
				msg.setQuantitysend(rs.getString("quantitysend"));
				msg.setIdestatus(rs.getInt("idestatus"));
				msg.setComentGeneral(rs.getString("comentGeneral"));
				msg.setCanceldate(rs.getString("canceldate"));
				msg.setRequisitiondate(rs.getString("requisitiondate"));
				msg.setElaborationdate(rs.getString("elaborationdate"));
				msg.setExpirationdate(rs.getString("expirationdate"));
				msg.setFreezingdate(rs.getString("freezingdate"));
				msg.setQty_excess(rs.getString("qty_excess"));
				msg.setType(rs.getString("type"));
				msg.setSAPBorrado(rs.getString("SAPBorrado"));
				msg.setRecnowqty(rs.getString("recnowqty"));
				msg.setEnviadaSAP(rs.getString("enviadaSAP"));
				msg.setDoctoSAP(rs.getString("doctoSAP"));
				msg.setErrordescripcion(rs.getString("errordescripcion"));
				msg.setCodigoMsg(rs.getString("codigoMsg"));
				msg.setTagref(rs.getString("tagref"));
				msg.setSAPcentro(rs.getString("SAPcentro"));
				msg.setSAPActualizado(rs.getString("SAPActualizado"));
				msg.setQty_missing(rs.getString("qty_missing"));
				msg.setSecondfactorconversion(rs.getString("secondfactorconversion"));
				msg.setTransferenceshipdate(rs.getString("transferenceshipdate"));
				msg.setIdtipotransferencia(rs.getString("idtipotransferencia"));
				msg.setFolioentregaSAP(rs.getString("folioentregaSAP"));
				msg.setIdentifieramq(rs.getString("identifieramq"));
				msg.setCountline(rs.getString("countline"));
				msg.setNextstatus(idestatusrec);
				
				if (rs.getInt("nuevo") == 1 ) {
					msg.setOperacion("nuevo");
				}
				else if (rs.getInt("actualizado") == 1) {
					msg.setOperacion("actualizado");
				}
				else if (rs.getInt("nuevosap") == 1) {
					msg.setOperacion("nuevosap");
				}
				else if(rs.getInt("recibir") == 1) {
					msg.setOperacion("recibir");
				}
				else {
					msg.setOperacion("error");
				}
				
				msg.setTipo("trasnfer");
				// msg.setQueue(rs.getString("transfers_producer"));
				
				messages.add(msg);
			}
		} catch (Exception e) {
			logger.error("Error desconocido", e);
		}
		result.close();
		return messages;
	}

	public void updateMessageAsSent(QueueMessage msg) {
		String sql = "";
		
		if(msg.getOperacion().equals("nuevo")) {
			// Se elimina la actualizacion del estatus hasta que se confirme la recepcion
			// en central idestatus ="+ msg.getNextstatus() +",
			sql = "UPDATE loctransfers SET SAPActualizado = 0 "
					+ "WHERE reference = '" + msg.getReference() + "' "
							+ "AND stockid = '" + msg.getStockid() + "'";
		}
		else if(msg.getOperacion().equals("actualizado") || msg.getOperacion().equals("nuevosap")) {
			sql = "UPDATE loctransfers SET SAPActualizado = 0 WHERE reference = '"
					+ msg.getReference() + "' AND stockid = '" + msg.getStockid() + "'";
		}
		else if(msg.getOperacion().equals("recibir")) {
			sql = "UPDATE loctransfers SET SAPActualizado = 0 WHERE reference = '"
					+ msg.getReference() + "' AND stockid = '" + msg.getStockid() + "'";
		}
		
		logger.debug(sql);

		this.database.executeUpdate(sql);
	}

	public void disconnect() {
		this.database.disconnect();
	}
}
