package com.jahepi.activemq.database;

import java.net.InetAddress;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;

import org.apache.log4j.Logger;

public class Database {
	
	final static Logger logger = Logger.getLogger(Database.class);

	private Connection connection;
	private Boolean connected;
	private String connectionUrl;
	private String driver;

	public Database(String driver, String connectionUrl) {
		this.driver = driver;
		this.connectionUrl = connectionUrl;
	}

	public boolean connect() {
		try {
			Class.forName(this.driver);
			connection = DriverManager.getConnection(this.connectionUrl);
			String thisIp = InetAddress.getLocalHost().getHostAddress();
			System.out.println("IP:"+thisIp);
			System.out.println(this.connectionUrl);
		} catch (Exception exp) {
			logger.error("Database.connect(): Error de base de datos: " + exp.getMessage(), exp);
			connected = false;
			return false;
		}
		connected = true;
		return true;
	}

	public boolean isConnected() {
		return this.connected;
	}

	public boolean executeUpdate(String sql) {
		if (this.isConnected()) {
			try {
				Statement statement = connection.createStatement();
				statement.executeUpdate(sql);
				statement.close();
				return true;
			} catch (SQLException e) {
				logger.error("1.- Database.executeUpdate(String sql): Error de base de datos: " + e.getMessage(), e);
			} catch (Exception exp) {
				logger.error("2.- Database.executeUpdate(String sql): Error de base de datos: " + exp.getMessage() , exp);
			}
		}
		return false;
	}

	public DBPreparedStatement getPreparedStatement(String sql) {
		DBPreparedStatement ps = new DBPreparedStatement();
		try {
			PreparedStatement pst = connection.prepareStatement(sql,
					Statement.RETURN_GENERATED_KEYS);
			ps.setPreparedStatement(pst);
		} catch (Exception e) {
			logger.error("Database.getPreparedStatement(String sql): Error de base de datos: " + e.getMessage(), e);
		}
		return ps;
	}

	public DBResultSet executeQuery(String sql) {
		DBResultSet result = new DBResultSet();
		if (this.isConnected()) {
			try {
				Statement statement = connection.createStatement();
				ResultSet resultSet = statement.executeQuery(sql);
				result.setStatement(statement);
				result.setResultSet(resultSet);
				return result;
			} catch (Exception exp) {
				logger.error("Database.executeQuery(String sql): Error de base de datos: " + exp.getMessage(), exp);
			}
		}
		return result;
	}

	public boolean disconnect() {
		this.connected = false;
		try {
			this.connection.close();
			this.connection = null;
		} catch (SQLException exp) {
			logger.error("1.- Database.disconnect(): Error de base de datos: " + exp.getMessage(), exp);
			return false;
		} catch (Exception exp) {
			logger.error("2.- Database.disconnect(): Error de base de datos: " + exp.getMessage(), exp);
			return false;
		}
		return true;
	}

	public void restart() {
		this.disconnect();
		this.connect();
	}

	public class DBPreparedStatement {

		private PreparedStatement preparedStatement;

		public PreparedStatement getPreparedStatement() {
			return preparedStatement;
		}

		public void setPreparedStatement(PreparedStatement preparedStatement) {
			this.preparedStatement = preparedStatement;
		}

		public String toString() {
			if (this.preparedStatement != null) {
				return this.preparedStatement.toString();
			}
			return "";
		}

		public long getInsertedId() {
			long id = 0;
			if (this.preparedStatement != null) {
				try {
					ResultSet rsKeys = this.preparedStatement
							.getGeneratedKeys();
					if (rsKeys != null) {
						if (rsKeys.next()) {
							id = rsKeys.getLong(1);
						}
						rsKeys.close();
					}
				} catch (Exception e) {
					logger.error("DBPreparedStatement.getInsertedId(): Error de base de datos: " + e.getMessage(), e);
				}
			}
			return id;
		}

		public void close() {
			if (this.preparedStatement != null) {
				try {
					preparedStatement.close();
				} catch (Exception e) {
					logger.error("DBPreparedStatement.close(): Error de base de datos: " + e.getMessage(), e);
				}
			}
		}
	}

	public class DBResultSet {

		private Statement statement;
		private ResultSet resultSet;

		public Statement getStatement() {
			return statement;
		}

		public void setStatement(Statement statement) {
			this.statement = statement;
		}

		public ResultSet getResultSet() {
			return resultSet;
		}

		public void setResultSet(ResultSet resultSet) {
			this.resultSet = resultSet;
		}

		public void close() {
			if (this.statement != null) {
				try {
					this.statement.close();
				} catch (Exception e) {
					logger.error("1.- DBResult.close(): Error de base de datos: " + e.getMessage(), e);
				}
			}
			if (this.resultSet != null) {
				try {
					this.resultSet.close();
				} catch (Exception e) {
					logger.error("2.- DBResult.close(): Error de base de datos: " + e.getMessage(), e);
				}
			}
		}
	}
}
