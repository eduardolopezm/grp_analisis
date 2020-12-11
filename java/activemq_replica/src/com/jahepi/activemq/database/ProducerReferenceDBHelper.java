package com.jahepi.activemq.database;

import java.sql.ResultSet;
import java.util.ArrayList;

import org.apache.log4j.Logger;

import com.jahepi.activemq.database.Database.DBResultSet;
import com.jahepi.activemq.dto.QueueMessageReference;
import com.jahepi.activemq.loader.Config.ConfigData;

public class ProducerReferenceDBHelper {
	
	final static Logger logger = Logger.getLogger(ProducerReferenceDBHelper.class);
	
	private Database database;
	private ConfigData config;
	
	public ProducerReferenceDBHelper(Database database, ConfigData config) {
		this.database = database;
		this.config = config;
	}
	
	public ArrayList<QueueMessageReference> getMessages() {
		ArrayList<QueueMessageReference> messages = new ArrayList<QueueMessageReference>();
		String sql = "";
		DBResultSet result;
		ResultSet rs;
		
		sql = "SELECT "
				+ "systypeloctransfer.anio, "
				+ "systypeloctransfer.loccode, "
				+ "systypeloctransfer.sequence, "
				+ "systypeloctransfer.type, "
				+ "systypeloctransfer.activemq "
				+ "FROM systypeloctransfer "
				+ "WHERE systypeloctransfer.activemq in (1,2)  "
				+ "AND loccode like '%" + this.config.get("unidad") + "%'";
		
		
		logger.debug(sql);
		
		result = this.database.executeQuery(sql);
		rs = result.getResultSet();
		try {
			while (rs.next()) {
				
				QueueMessageReference msg = new QueueMessageReference();
				msg.setAnio(rs.getString("anio"));
				msg.setLoccode(rs.getString("loccode"));
				msg.setSequence(rs.getString("sequence"));
				msg.setType(rs.getString("type"));
				msg.setActivemq(rs.getString("activemq"));
				
				messages.add(msg);
			}
		} catch (Exception e) {
			logger.error("Error desconocido", e);
		}
		result.close();
		return messages;
	}

	public void updateMessageAsSent(QueueMessageReference msg) {
		String sql = "";
		sql = "UPDATE systypeloctransfer SET `activemq` = '0' WHERE anio = '" + msg.getAnio() + "' "
				+ "and loccode = '" + msg.getLoccode() + "' and TYPE = '" + msg.getType() + "'";
		
		this.database.executeUpdate(sql);
		
		logger.debug(sql);
	}

	public void disconnect() {
		this.database.disconnect();
	}
	
}
