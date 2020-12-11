package com.jahepi.activemq.database;

import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;

import org.apache.log4j.Logger;

import com.jahepi.activemq.database.Database.DBResultSet;
import com.jahepi.activemq.dto.QueueMessageAdjustment;
import com.jahepi.activemq.loader.Config.ConfigData;

public class ProducerAdjustmentDBHelper {
	
	final static Logger logger = Logger.getLogger(ProducerAdjustmentDBHelper.class);
	
	private Database database;
	public ProducerAdjustmentDBHelper(Database database, ConfigData config) {
		this.database = database;
	}
	
	public ArrayList<QueueMessageAdjustment> getMessages() {
		ArrayList<QueueMessageAdjustment> messages = new ArrayList<QueueMessageAdjustment>();
		String sql = "";
		DBResultSet result;
		ResultSet rs;
		
		sql = "SELECT "
				+ "orderno, "
				+ "descripcion, "
				+ "stockid, "
				+ "loccode, "
				+ "origtrandate, "
				+ "trandate, "
				+ "fechaconsumo, "
				+ "narrative, "
				+ "qty, "
				+ "quotation, "
				+ "url, "
				+ "userregister, "
				+ "userprocess, "
				+ "userauthorized, "
				+ "type, "
				+ "reasonid, "
				+ "service, "
				+ "factor, "
				+ "massiveadjustment "
				+ "FROM inventoryadjustmentorders "
				+ "WHERE activemq = 1";
		
		logger.debug(sql);

		result = this.database.executeQuery(sql);
		rs = result.getResultSet();
		try {
			while (rs.next()) {
				
				QueueMessageAdjustment msg = new QueueMessageAdjustment();
				
				msg.setOrderno(rs.getString("orderno"));
				msg.setDescripcion(rs.getString("descripcion"));
				msg.setStockid(rs.getString("stockid"));
				msg.setLoccode(rs.getString("loccode"));
				msg.setOrigtrandate(rs.getString("origtrandate"));
				msg.setTrandate(rs.getString("trandate"));
				msg.setFechaconsumo(rs.getString("fechaconsumo"));
				msg.setNarrative(rs.getString("narrative"));
				msg.setQty(rs.getString("qty"));
				msg.setQuotation(rs.getString("quotation"));
				msg.setUrl(rs.getString("url"));
				msg.setUserregister(rs.getString("userregister"));
				msg.setUserprocess(rs.getString("userprocess"));
				msg.setUserauthorized(rs.getString("userauthorized"));
				msg.setType(rs.getString("type"));
				msg.setReasonid(rs.getString("reasonid"));
				msg.setService(rs.getString("service"));
				msg.setFactor(rs.getString("factor"));
				msg.setMassiveadjustment(rs.getString("massiveadjustment"));
				
				messages.add(msg);
			}
		} catch (SQLException e) {
			logger.error("Error de base de datos", e);
		}
		result.close();
		return messages;
	}
	
	public void updateMessageAsSent(QueueMessageAdjustment msg) {
		String sql = "";
		
		sql = "UPDATE `inventoryadjustmentorders` SET `activemq` = '0' WHERE `orderno` = '" + msg.getOrderno() + "'";
		this.database.executeUpdate(sql);

		logger.debug(sql);
	}

	public void disconnect() {
		this.database.disconnect();
	}

}
