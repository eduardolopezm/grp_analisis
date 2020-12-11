package com.jahepi.activemq.database;

import java.sql.PreparedStatement;
import java.sql.SQLException;

import org.apache.log4j.Logger;

import com.jahepi.activemq.Utils;
import com.jahepi.activemq.database.Database.DBPreparedStatement;
import com.jahepi.activemq.dto.QueueMessageReference;
import com.jahepi.activemq.loader.Config.ConfigData;

public class ConsumerReferenceDBHelper {
	
	final static Logger logger = Logger.getLogger(ConsumerReferenceDBHelper.class);
	
	private Database database;
	private ConfigData config;
	
	public ConsumerReferenceDBHelper(Database database, ConfigData config) {
		this.database = database;
		this.config = config;
	}
	
	public boolean saveMessage(QueueMessageReference msg) {
		String sql = "", sqlLog = "";
		String finalLogSql = "";
		DBPreparedStatement dbPreparedStatement;
		PreparedStatement ps;
		boolean success = true;
		
		switch (msg.getActivemq()) {
			case "1":
				sql = "INSERT INTO `systypeloctransfer` (`anio`, `loccode`, `sequence`, `type`, `activemq`) "
						+ "VALUES (?, ?, ?, ?, 0)";
				sqlLog = "INSERT INTO `systypeloctransfer` (`anio`, `loccode`, `sequence`, `type`, `activemq`) "
						+ "VALUES ('%s', '%s', '%s' '%s', '0')";
				break;
			case "2":
				sql = "UPDATE systypeloctransfer SET sequence = ? WHERE anio = ? and loccode = ? and TYPE = ?";
				sqlLog = "UPDATE systypeloctransfer SET sequence = '%s', activemq = 0 WHERE anio = '%s' and loccode = '%s' and TYPE = '%s'";
				break;
			default:
				success = false;
				break;
		}
		
		dbPreparedStatement = this.database.getPreparedStatement(sql);
		ps = dbPreparedStatement.getPreparedStatement();
		if (ps != null) {
			try {
				
				switch(msg.getActivemq()) {
					case "1":
						ps.setString(1, msg.getAnio());
						ps.setString(2, msg.getLoccode());
						ps.setString(3, msg.getSequence());
						ps.setString(4, msg.getSequence());
						
						finalLogSql = String.format(
							sqlLog,
							msg.getAnio(),
							msg.getLoccode(),
							msg.getSequence(),
							msg.getType()
						);
						break;
					case "2":
						ps.setString(1, msg.getSequence());
						ps.setString(2, msg.getAnio());
						ps.setString(3, msg.getLoccode());
						ps.setString(4, msg.getType());
						
						finalLogSql = String.format(
							sqlLog,
							msg.getSequence(),
							msg.getAnio(),
							msg.getLoccode(),
							msg.getType()
						);
						break;
					default:
						success = false;
						break;
				}				

				
				logger.debug(finalLogSql);

				ps.executeUpdate();

				// success = this.database.executeUpdate(msg.getSql());
				success = true;

			} catch (SQLException e) {

				success = false;
				if (this.config.get("onErrorSaveFile").equals("1")) {
					success = Utils.saveFile(config, finalLogSql, "reference", msg.getAnio() + "-" + msg.getLoccode() + "-" + msg.getType(), "1");
				}
				
				logger.error("Error de base de datos", e);
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
