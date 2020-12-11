package com.jahepi.activemq.database;

import java.sql.PreparedStatement;
import java.sql.SQLException;

import org.apache.log4j.Logger;

import com.jahepi.activemq.Utils;
import com.jahepi.activemq.database.Database.DBPreparedStatement;
import com.jahepi.activemq.dto.QueueMessageLockStock;
import com.jahepi.activemq.loader.Config.ConfigData;

public class ConsumerLockstockDBHelper {
	
	final static Logger logger = Logger.getLogger(ConsumerLockstockDBHelper.class);
	
	private Database database;
	private ConfigData config;

	public ConsumerLockstockDBHelper(Database database, ConfigData config) {
		this.database = database;
		this.config = config;
	}

	public boolean saveMessage(QueueMessageLockStock msg) {
		String sql = "", sqlLog = "";
		String stockid = null;
		String loccode = null;
		String finalLogSql = "";
		DBPreparedStatement dbPreparedStatement;
		PreparedStatement ps;
		boolean success = true;
		
		switch(msg.getActivemq()) {
			case "1":
				sql = "UPDATE locstock SET "
						+ "quantity = ?,"
						+ " reorderlevel = ?, "
						+ "ontransit = ?, "
						+ "quantityv2 = ?, "
						+ "minimumlevel = ?, "
						+ "timefactor = ?, "
						+ "delay = ?, "
						+ "qtybysend = ?, "
						+ "quantityprod = ?, "
						+ "loccode_aux = ?, "
						+ "secondfactorconversion = ?, "
						+ "activemq = 0 "
						+ "WHERE loccode = ? AND stockid = ? AND localidad = ?";
				sqlLog = "UPDATE locstock SET "
						+ "quantity = '%s',"
						+ " reorderlevel = '%s', "
						+ "ontransit = '%s', "
						+ "quantityv2 = '%s', "
						+ "minimumlevel = '%s', "
						+ "timefactor = '%s', "
						+ "delay = '%s', "
						+ "qtybysend = '%s', "
						+ "quantityprod = '%s', "
						+ "loccode_aux = '%s', "
						+ "secondfactorconversion = '%s', "
						+ "activemq = 0 "
						+ "WHERE loccode = '%s' AND stockid = '%s' AND localidad = '%s'";
				break;
			case "2":
				sql = "INSERT INTO `locstock` ("
						+ "`loccode`, "
						+ "`stockid`, "
						+ "`quantity`, "
						+ "`reorderlevel`, "
						+ "`ontransit`, "
						+ "`quantityv2`, "
						+ "`localidad`, "
						+ "`minimumlevel`, "
						+ "`timefactor`, "
						+ "`delay`, "
						+ "`qtybysend`, "
						+ "`quantityprod`, "
						+ "`loccode_aux`, "
						+ "`secondfactorconversion`, "
						+ "`activemq`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";
				sqlLog = "INSERT INTO `locstock` ("
						+ "`loccode`, "
						+ "`stockid`, "
						+ "`quantity`, "
						+ "`reorderlevel`, "
						+ "`ontransit`, "
						+ "`quantityv2`, "
						+ "`localidad`, "
						+ "`minimumlevel`, "
						+ "`timefactor`, "
						+ "`delay`, "
						+ "`qtybysend`, "
						+ "`quantityprod`, "
						+ "`loccode_aux`, "
						+ "`secondfactorconversion`, "
						+ "`activemq`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', 0)";
				break;
		}
		
		
		dbPreparedStatement = this.database.getPreparedStatement(sql);
		ps = dbPreparedStatement.getPreparedStatement();
		if (ps != null) {
			try {
				
				switch(msg.getActivemq()) {
					case "1":
						ps.setString(1, msg.getQuantity());
						ps.setString(2, msg.getReorderlevel());
						ps.setString(3, msg.getOntransit());
						ps.setString(4, msg.getQuantityv2());
						ps.setString(5, msg.getMinimumlevel());
						ps.setString(6, msg.getTimefactor());
						ps.setString(7, msg.getDelay());
						ps.setString(8, msg.getQtybysend());
						ps.setString(9, msg.getQuantityprod());
						ps.setString(10, msg.getLoccode_aux());
						ps.setString(11, msg.getSecondfactorconversion());
						// ps.setString(12, msg.getActivemq());
						ps.setString(12, msg.getLoccode());
						ps.setString(13, msg.getStockid());
						ps.setString(14, msg.getLocalidad());
						
						finalLogSql = String.format(
							sqlLog,
							msg.getQuantity(),
							msg.getReorderlevel(),
							msg.getOntransit(),
							msg.getQuantityv2(),
							msg.getMinimumlevel(),
							msg.getTimefactor(),
							msg.getDelay(),
							msg.getQtybysend(),
							msg.getQuantityprod(),
							msg.getLoccode_aux(),
							msg.getSecondfactorconversion(),
							// msg.getActivemq(),
							msg.getLoccode(),
							msg.getStockid(),
							msg.getLocalidad()
						);
						break;
					case "2":
						ps.setString(1, msg.getLoccode());
						ps.setString(2, msg.getStockid());
						ps.setString(3, msg.getQuantity());
						ps.setString(4, msg.getReorderlevel());
						ps.setString(5, msg.getOntransit());
						ps.setString(6, msg.getQuantityv2());
						ps.setString(7, msg.getLocalidad());
						ps.setString(8, msg.getMinimumlevel());
						ps.setString(9, msg.getTimefactor());
						ps.setString(10, msg.getDelay());
						ps.setString(11, msg.getQtybysend());
						ps.setString(12, msg.getQuantityprod());
						ps.setString(13, msg.getLoccode_aux());
						ps.setString(14, msg.getSecondfactorconversion());
						
						finalLogSql = String.format(
							sqlLog,
							msg.getLoccode(),
							msg.getStockid(),
							msg.getQuantity(),
							msg.getReorderlevel(),
							msg.getOntransit(),
							msg.getQuantityv2(),
							msg.getLocalidad(),
							msg.getMinimumlevel(),
							msg.getTimefactor(),
							msg.getDelay(),
							msg.getQtybysend(),
							msg.getQuantityprod(),
							msg.getLoccode_aux(),
							msg.getSecondfactorconversion()
						);
						break;
				}				

				
				logger.debug(finalLogSql);

				ps.executeUpdate();
				
				loccode = msg.getLoccode();
				stockid = msg.getStockid();

				// success = this.database.executeUpdate(msg.getSql());
				success = true;

			} catch (SQLException e) {

				success = false;
				if (this.config.get("onErrorSaveFile").equals("1")) {
					success = Utils.saveFile(config, finalLogSql, "lockstock", loccode, stockid);
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
