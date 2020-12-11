package com.jahepi.activemq.database;

import java.sql.ResultSet;
import java.util.ArrayList;

import org.apache.log4j.Logger;

import com.jahepi.activemq.database.Database.DBResultSet;
import com.jahepi.activemq.dto.QueueMessageLockStock;
import com.jahepi.activemq.loader.Config.ConfigData;

public class ProducerLocstockDBHelper {
	
	final static Logger logger = Logger.getLogger(ProducerLocstockDBHelper.class);
	
	private Database database;
	private ConfigData config;

	public ProducerLocstockDBHelper(Database database, ConfigData config) {
		this.database = database;
		this.config = config;
	}

	public ArrayList<QueueMessageLockStock> getMessages() {
		ArrayList<QueueMessageLockStock> messages = new ArrayList<QueueMessageLockStock>();
		String sql = "";
		DBResultSet result;
		ResultSet rs;
		
		sql = "SELECT "
				+ "locstock.loccode, "
				+ "locstock.stockid, "
				+ "locstock.quantity, "
				+ "locstock.reorderlevel, "
				+ "locstock.ontransit, "
				+ "locstock.quantityv2, "
				+ "locstock.localidad, "
				+ "locstock.minimumlevel, "
				+ "locstock.timefactor, "
				+ "locstock.delay, "
				+ "locstock.qtybysend, "
				+ "locstock.quantityprod, "
				+ "locstock.loccode_aux, "
				+ "locstock.secondfactorconversion, "
				+ "locstock.activemq "
				+ "FROM locstock INNER JOIN locations ON locations.loccode = locstock.loccode ";
		
		
		switch(this.config.get("type")) {
			case "central":
				sql += "WHERE locations.tagref LIKE '%" + this.config.get("unidad") + "%' AND activemq in (1,2)";
				break;
			case "satelite":
				sql += "WHERE activemq in (1,2)";
				break;
		}
		
		
		logger.debug(sql);
		
		result = this.database.executeQuery(sql);
		rs = result.getResultSet();
		try {
			while (rs.next()) {
				
				QueueMessageLockStock msg = new QueueMessageLockStock();
				msg.setLoccode(rs.getString("loccode"));
				msg.setStockid(rs.getString("stockid"));
				msg.setQuantity(rs.getString("quantity"));
				msg.setReorderlevel(rs.getString("reorderlevel"));
				msg.setOntransit(rs.getString("ontransit"));
				msg.setQuantityv2(rs.getString("quantityv2"));
				msg.setLocalidad(rs.getString("localidad"));
				msg.setMinimumlevel(rs.getString("minimumlevel"));
				msg.setTimefactor(rs.getString("timefactor"));
				msg.setDelay(rs.getString("delay"));
				msg.setQtybysend(rs.getString("qtybysend"));
				msg.setQuantityprod(rs.getString("quantityprod"));
				msg.setLoccode_aux(rs.getString("loccode_aux"));
				msg.setSecondfactorconversion(rs.getString("secondfactorconversion"));
				msg.setActivemq(rs.getString("activemq"));
				
				messages.add(msg);
			}
		} catch (Exception e) {
			logger.error("Error desconocido", e);
		} 
		result.close();
		return messages;
	}

	public void updateMessageAsSent(QueueMessageLockStock msg) {
		String sql = "";
		sql = "UPDATE locstock SET  activemq = 0 "
				+ "WHERE loccode = '" + msg.getLoccode() + "' "
					+ "AND stockid = '" + msg.getStockid() + "' "
					+ "AND localidad = '" + msg.getLocalidad() +"'";
		
		this.database.executeUpdate(sql);
		
		logger.debug(sql);
	}

	public void disconnect() {
		this.database.disconnect();
	}
}
