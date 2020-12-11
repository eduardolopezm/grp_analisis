package com.jahepi.activemq.database;

import java.sql.ResultSet;
import java.util.ArrayList;

import org.apache.log4j.Logger;
import com.jahepi.activemq.database.Database.DBResultSet;
import com.jahepi.activemq.dto.QueueMessageMaterial;
import com.jahepi.activemq.loader.Config.ConfigData;

public class ProducerMaterialDBHelper {
	
	final static Logger logger = Logger.getLogger(ProducerMaterialDBHelper.class);
	
	private Database database;
	private ConfigData config;

	public ProducerMaterialDBHelper(Database database, ConfigData config) {
		this.database = database;
		this.config = config;
	}

	public ArrayList<QueueMessageMaterial> getMessages() {
		ArrayList<QueueMessageMaterial> messages = new ArrayList<QueueMessageMaterial>();
		String sql = "";
		DBResultSet result;
		ResultSet rs;
		String activemq = "0";
		String queuestr = "";
		
		sql = "SELECT "
				+ "loctransfersmreceived.id, "
				+ "loctransfersmreceived.type, "
				+ "loctransfersmreceived.reference, "
				+ "loctransfersmreceived.entry, "
				+ "loctransfersmreceived.stockid, "
				+ "loctransfersmreceived.quantity, "
				+ "loctransfersmreceived.unit, "
				+ "loctransfersmreceived.deliverydate, "
				+ "loctransfersmreceived.userid, "
				+ "loctransfersmreceived.iddelivery, "
				+ "loctransfersmreceived.userregister, "
				+ "loctransfersmreceived.registerdate, "
				+ "loctransfersmreceived.folioconf, "
				+ "loctransfersmreceived.folioconfdate, "
				+ "loctransfersmreceived.erptransno, "
				+ "loctransfersmreceived.movementsapplied, "
				+ "loctransfersmreceived.identifieramq, "
				+ "loctransfersmreceived.indicadorSAP "
				+ " FROM loctransfersmreceived INNER JOIN loctransfers ON loctransfersmreceived.reference = loctransfers.reference "
				+ "	AND loctransfers.stockid = loctransfersmreceived.stockid "
				+ "INNER JOIN locations ON loctransfers.recloc = locations.loccode ";
		switch(this.config.get("type")) {
			case "central":
				activemq = "2";
				queuestr = "satelite";
				break;
			case "satelite":
				activemq = "1";
				queuestr = "central";
				break;
		}
		sql = sql + "WHERE loctransfersmreceived.activemq = " + activemq + " AND loctransfersmreceived.identifieramq like '%" + this.config.get("unidad") + "%' -- AND tags_queue.type = '" + queuestr + "'";
		
		
		logger.debug(sql);
		
		result = this.database.executeQuery(sql);
		rs = result.getResultSet();
		try {
			while (rs.next()) {
				
				QueueMessageMaterial msg = new QueueMessageMaterial();
				msg.setId(rs.getString("id"));
				msg.setType(rs.getString("type"));
				msg.setReference(rs.getString("reference"));
				msg.setEntry(rs.getString("entry"));
				msg.setStockid(rs.getString("stockid"));
				msg.setQuantity(rs.getString("quantity"));
				msg.setUnit(rs.getString("unit"));
				msg.setDeliverydate(rs.getString("deliverydate"));
				msg.setUserid(rs.getString("userid"));
				msg.setUserregister(rs.getString("userregister"));
				msg.setRegisterdate(rs.getString("registerdate"));
				msg.setFolioconf(rs.getString("folioconf"));
				msg.setFolioconfdate(rs.getString("folioconfdate"));
				msg.setErptransno(rs.getString("erptransno"));
				msg.setMovementtsapplied(rs.getString("movementsapplied"));
				msg.setIddelivery(rs.getString("iddelivery"));
				msg.setIdentifieramq(rs.getString("identifieramq"));
				msg.setTipo("material");
				// msg.setQueue(rs.getString("material_producer"));
				msg.setIndicadorSAP(rs.getString("indicadorSAP"));
				
				messages.add(msg);
			}
		} catch (Exception e) {
			logger.error("Error desconocido", e);
		} 
		result.close();
		return messages;
	}

	public void updateMessageAsSent(QueueMessageMaterial msg) {
		String sql = "";
		String sqlindicadorSAP = "";
		if (!msg.getIndicadorSAP().equals("NULL") || !msg.getIndicadorSAP().equals("")) {
			sqlindicadorSAP = "indicadorSAP = " + msg.getIndicadorSAP() + ",";
		}
		sql = "UPDATE loctransfersmreceived SET " + sqlindicadorSAP + " activemq = 0 WHERE id = '"
			+ msg.getId() + "'";
		
		this.database.executeUpdate(sql);
		
		logger.debug(sql);
	}

	public void disconnect() {
		this.database.disconnect();
	}
	
}
