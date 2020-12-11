package com.jahepi.activemq.database;

import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Random;

import org.apache.log4j.Logger;

import com.jahepi.activemq.database.Database.DBResultSet;
import com.jahepi.activemq.dto.QueueMessageAMQStatus;
import com.jahepi.activemq.loader.Config.ConfigData;

public class ProducerConnStatusDBHelper {
	
	final static Logger logger = Logger.getLogger(ProducerConnStatusDBHelper.class);
	
	private Database database;
	private ConfigData config;

	public ProducerConnStatusDBHelper(Database database, ConfigData config) {
		this.database = database;
		this.config = config;
	}

	public ArrayList<QueueMessageAMQStatus> getMessages() throws SQLException {
		ArrayList<QueueMessageAMQStatus> messages = new ArrayList<QueueMessageAMQStatus>();	
		String sql = "SELECT id, tagref, message FROM activemqstatus WHERE tagref = '" + this.config.get("unidad") + "' AND status = 0";
		logger.debug(sql);
		
		DBResultSet result = this.database.executeQuery(sql);
		ResultSet rs = result.getResultSet();
		if(rs.next()) {
			do {
				QueueMessageAMQStatus msg = new QueueMessageAMQStatus();
				msg.setId(rs.getInt("id"));
				msg.setTagref(rs.getString("tagref"));
				if(this.config.get("type").equals("satelite")) {
					// se genera nuevo mensaje aleatorio para despues verificar que es el mismo que viene desde central
					Random random = new Random();
					int mensaje = random.nextInt(1000) + 1;
					msg.setMessage(mensaje);
				} else {
					msg.setMessage(rs.getInt("message"));
				}
				messages.add(msg);
			} while (rs.next());
		}
		return messages;
	}

	public void updateMessageAsSent(QueueMessageAMQStatus msg) throws SQLException {
		
		String sql = "UPDATE activemqstatus SET status = '1', message = '" + msg.getMessage() + "', msgsendat = NOW(), lastupdate = NOW() WHERE tagref = '" + msg.getTagref() + "'";
		
		logger.debug(sql);

		this.database.executeUpdate(sql);
	}

	public void disconnect() {
		this.database.disconnect();
	}
}
