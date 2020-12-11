package com.jahepi.activemq;

import java.awt.EventQueue;
import java.sql.ResultSet;

import javax.jms.Connection;
import javax.jms.Destination;
import javax.jms.JMSException;
import javax.jms.MessageConsumer;
import javax.jms.ObjectMessage;
import javax.jms.Session;

import org.apache.activemq.ActiveMQConnectionFactory;
import org.apache.activemq.RedeliveryPolicy;
import org.apache.log4j.Logger;

import com.jahepi.activemq.database.ConsumerAdjustmentDBHelper;
import com.jahepi.activemq.database.ConsumerDBHelper;
import com.jahepi.activemq.database.ConsumerLockstockDBHelper;
import com.jahepi.activemq.database.ConsumerMaterialDBHelper;
import com.jahepi.activemq.database.ConsumerReferenceDBHelper;
import com.jahepi.activemq.database.ConsumerSalesDetailsDBHelper;
import com.jahepi.activemq.database.ConsumerStockDBHelper;
import com.jahepi.activemq.database.ConsumerStockmovesDBHelper;
import com.jahepi.activemq.database.ConsumersalesordersDBHelper;
import com.jahepi.activemq.database.Database;
import com.jahepi.activemq.database.Database.DBResultSet;
import com.jahepi.activemq.dto.QueueMessage;
import com.jahepi.activemq.dto.QueueMessageAdjustment;
import com.jahepi.activemq.dto.QueueMessageLockStock;
import com.jahepi.activemq.dto.QueueMessageMaterial;
import com.jahepi.activemq.dto.QueueMessageReference;
import com.jahepi.activemq.dto.QueueMessageSalesDetails;
import com.jahepi.activemq.dto.QueueMessageStockMoves;
import com.jahepi.activemq.dto.QueueMessageStocks;
import com.jahepi.activemq.dto.QueueMessagesalesorders;
import com.jahepi.activemq.loader.Config.ConfigData;
import com.jahepi.activemq.view.AppListener;


public class Consumer extends Thread {

	private static final int TIMEOUT = 1000;
	
	final static Logger logger = Logger.getLogger(Consumer.class);

	private ConfigData config;
	private ConsumerDBHelper databaseHelperTransfer;
	private ConsumerMaterialDBHelper databaseHelperMaterial;
	private ConsumerStockDBHelper databaseHelperStock;
	private ConsumerReferenceDBHelper databaseHelperReference;
	private ConsumerLockstockDBHelper databaseHelperLocstock;
	private ConsumerAdjustmentDBHelper databaseHelperAdjustment;
	private ConsumersalesordersDBHelper databasesalesordersDBHelper;
	private ConsumerSalesDetailsDBHelper databaseHelperSalesDetails;
	private ConsumerStockmovesDBHelper databaseHelperStockmoves;
	private AppListener listener;
	private ConsumerListener consumerListener;
	// private static final boolean isTransacted = true;
	private boolean isRunning = true;
	private Connection connection;
	private Session session;
	private DeadQueueNotifier deadQueueNotifier;
	private MessageConsumer messageConsumerTrans;
	private MessageConsumer messageConsumerStock;
	private MessageConsumer messageConsumerMaterial;
	private MessageConsumer messageConsumerReference;
	private MessageConsumer messageConsumerLocstock;
	private MessageConsumer messageConsumerAdjustment;
	private MessageConsumer messageConsumerSalesOrders;
	private MessageConsumer messageConsumerSalesDetails;
	private MessageConsumer messageConsumerStockmoves;
	private Database database;
	private Destination destinationTrans;

	public Consumer(ConfigData config, Database database, AppListener listener) {
		this.config = config;
		this.databaseHelperTransfer = new ConsumerDBHelper(database, config);
		this.databaseHelperMaterial = new ConsumerMaterialDBHelper(database, config);
		this.databaseHelperStock = new ConsumerStockDBHelper(database, config);
		this.databaseHelperReference = new ConsumerReferenceDBHelper(database, config);
		this.databaseHelperLocstock = new ConsumerLockstockDBHelper(database, config);
		this.databaseHelperAdjustment = new ConsumerAdjustmentDBHelper(database, config);
		this.databasesalesordersDBHelper = new ConsumersalesordersDBHelper(database, config);
		this.databaseHelperSalesDetails = new ConsumerSalesDetailsDBHelper(database, config);
		this.databaseHelperStockmoves = new ConsumerStockmovesDBHelper(database, config);
		this.listener = listener;
		this.database = database;
	}

	public void setConsumerListener(ConsumerListener consumerListener) {
		this.consumerListener = consumerListener;
	}

	public void run() {

		try {
			
			String url = "failover://(tcp://" + this.config.get("server") + ":"
					+ this.config.get("port") +")";
			
			/* String url = "tcp://" + this.config.get("server") + ":"
					+ this.config.get("port"); */ 
			ActiveMQConnectionFactory connectionFactory = new ActiveMQConnectionFactory(
					this.config.get("user"), this.config.get("pass"), url);
			RedeliveryPolicy policy = connectionFactory.getRedeliveryPolicy();
			policy.setMaximumRedeliveries(Utils.convertToInt(this.config
					.get("consumerMaximumRedeliveries")));
			this.connection = connectionFactory.createConnection();
			// this.connection.setClientID("consumer_" + this.config.get("appid"));
			this.connection.start();
			
			this.deadQueueNotifier = new DeadQueueNotifier(this.config);
			deadQueueNotifier.run(this.connection);

			if (this.listener != null) {
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						Consumer.this.listener.onQueueConnect();
					}
				});
			}

			this.session = this.connection.createSession(false, Session.CLIENT_ACKNOWLEDGE);
			// this.session = this.connection.createSession(isTransacted, -1);
			
			try {
				this.destinationTrans = null;
				Destination destinationMaterial = null;
				Destination destinationStock = null;
				Destination destinationAdjustment = null;
				Destination destinationSalesOrders = null;
				Destination destinationSalesDetails = null;
				Destination destinationStockmoves = null;
				
				String sqlq = "SELECT "
						+ "tags_queue.transfers_consumer, "
						+ "tags_queue.stocks_queue, "
						+ "tags_queue.material_consumer, "
						+ "tags_queue.reference_consumer, "
						+ "tags_queue.locstock_consumer, "
						+ "tags_queue.inventoryadjustment_queue, "
						+ "tags_queue.salesorderdetails_queue, "
						+ "tags_queue.stockmoves_queue, "
						+ "tags_queue.salesorders_queu "
						+ "FROM tags_queue WHERE tags_queue.type = '" + this.config.get("type") + "' AND "
								+ "tags_queue.tags = '" + this.config.get("unidad") + "'";
				
				logger.debug(sqlq);
				
				DBResultSet result = this.database.executeQuery(sqlq);
				ResultSet rs = result.getResultSet();
				if(rs.next()) {
					do {
						
						if(this.config.get("type").equals("central")){
								this.destinationTrans = this.session.createQueue(rs.getString("transfers_consumer"));
								this.messageConsumerTrans = this.session.createConsumer(this.destinationTrans);
									
								destinationMaterial = this.session.createQueue(rs.getString("material_consumer"));
								this.messageConsumerMaterial = this.session.createConsumer(destinationMaterial);
								
								/*destinationReference = this.session.createQueue(rs.getString("reference_consumer"));
								this.messageConsumerReference = this.session.createConsumer(destinationReference);
								
								destinationLocstock= this.session.createQueue(rs.getString("locstock_consumer"));
								this.messageConsumerLocstock = this.session.createConsumer(destinationLocstock);*/
								
								destinationAdjustment= this.session.createQueue(rs.getString("inventoryadjustment_queue"));
								this.messageConsumerAdjustment = this.session.createConsumer(destinationAdjustment);
									
								destinationSalesOrders= this.session.createQueue(rs.getString("salesorders_queu"));
								this.messageConsumerSalesOrders = this.session.createConsumer(destinationSalesOrders);
									
								destinationSalesDetails= this.session.createQueue(rs.getString("salesorderdetails_queue"));
								this.messageConsumerSalesDetails = this.session.createConsumer(destinationSalesDetails);
								
								destinationStockmoves= this.session.createQueue(rs.getString("stockmoves_queue"));
								this.messageConsumerSalesDetails = this.session.createConsumer(destinationStockmoves);
								
								logger.info("[QUEUE] TRANSFERENCIAS CONSUMER > " + rs.getString("transfers_consumer"));
								logger.info("[QUEUE] ENTREGAS CONSUMER > " + rs.getString("material_consumer"));
								logger.info("[QUEUE] AJUSTES CONSUMER > " + rs.getString("inventoryadjustment_queue"));
								logger.info("[QUEUE] SALESORDERS CONSUMER > " + rs.getString("salesorders_queu"));
								logger.info("[QUEUE] SALESORDERDETAILS CONSUMER > " + rs.getString("salesorderdetails_queue"));
								logger.info("[QUEUE] STOCKMOVES CONSUMER > " + rs.getString("stockmoves_queue"));
							
						} else if(this.config.get("type").equals("satelite")){
								this.destinationTrans = this.session.createQueue(rs.getString("transfers_consumer"));
								this.messageConsumerTrans = this.session.createConsumer(this.destinationTrans);
							
								destinationMaterial = this.session.createQueue(rs.getString("material_consumer"));
								this.messageConsumerMaterial = this.session.createConsumer(destinationMaterial);
								
								destinationStock = this.session.createQueue(rs.getString("stocks_queue"));
								this.messageConsumerStock = this.session.createConsumer(destinationStock);
								
								/*destinationReference = this.session.createQueue(rs.getString("reference_consumer"));
								this.messageConsumerReference = this.session.createConsumer(destinationReference);
							
								destinationLocstock= this.session.createQueue(rs.getString("locstock_consumer"));
								this.messageConsumerLocstock = this.session.createConsumer(destinationLocstock);*/
								
								
								logger.info("[QUEUE] TRANSFERENCIAS CONSUMER > " + rs.getString("transfers_consumer"));
								logger.info("[QUEUE] ENTREGAS CONSUMER > " + rs.getString("material_consumer"));
								logger.info("[QUEUE] PRODUCTOS CONSUMER > " + rs.getString("stocks_queue"));
						}
						
					} while (rs.next());
				}
				else {
					this.destinationTrans = this.session.createQueue(this.config.get("queuet2"));
					this.messageConsumerTrans = this.session.createConsumer(this.destinationTrans);
					
					destinationMaterial = this.session.createQueue(this.config.get("queueconsumermaterial2"));
					this.messageConsumerStock = this.session.createConsumer(destinationMaterial);
					
					if(this.config.get("type").equals("satelite"))  {
						destinationStock = this.session.createQueue(this.config.get("queueconsumerstock"));
						this.messageConsumerMaterial = this.session.createConsumer(destinationStock);
					}
				}
				
			}catch (Exception e) {
				logger.error("Error Desconocido", e);
			}
			
			long sleep = Long.parseLong(this.config.get("threadSleepTime"));

			while (this.isRunning) {
				
				switch (this.config.get("type")) {
				
				case "central":
				
					processTranfer();
					processMaterial();
					/*processReference();
					processLocstock();*/
					processAdjustment();
					processSalesOrders();
					processSalesDetails();
					// processStockmoves();
					break;
				case "satelite":
					processTranfer();
					processMaterial();
					processStock();
					/*processReference();
					processLocstock();*/
					break;
					
				}
				
			
				Thread.sleep(sleep);
			}

		} catch(javax.jms.IllegalStateException e) {
			logger.error("Error estado ", e);
			
			
			if (this.listener != null) {
				final String msg = e.getMessage();
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						Consumer.this.listener.onExceptionError(msg);
					}
				});
			}
			
			this.stopConsumer();
			this.start();
			
		} catch (Exception e) {
			logger.error("Error Desconocido", e);
			
			if (this.listener != null) {
				final String msg = e.getMessage();
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						Consumer.this.listener.onExceptionError(msg);
					}
				});
			}
		} finally {
			this.stopConsumer();
			if (this.listener != null) {
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						Consumer.this.listener.onQueueDisconnet();
					}
				});
			}
			if (this.consumerListener != null) {
				this.consumerListener.onConsumerDisconnect();
			}
		}
	}
	
	public void processTranfer() throws JMSException {
		ObjectMessage message = (ObjectMessage) this.messageConsumerTrans.receive(TIMEOUT);
		
		if (message != null && message instanceof ObjectMessage) {
			
			QueueMessage queueMsg = (QueueMessage) message.getObject();
			final String messageStr;
			if (this.databaseHelperTransfer.saveMessage(queueMsg)) {
				messageStr = "reference: " + queueMsg.getReference() + " stockid: " + queueMsg.getStockid();
				// session.commit();
				message.acknowledge();
			} else {
				messageStr = "[ERROR] reference: " + queueMsg.getReference() + " stockid: " + queueMsg.getStockid();
				// session.rollback();
				// message.acknowledge();
			}
			
			logger.info(" Transferencia mensage recibido > " + messageStr + " | Mensaje > " + message);
			
			if (this.listener != null) {
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						Consumer.this.listener
								.onQueueMessage(messageStr);
					}
				});
			}
			// Utils.setReplica(this.config, this.database, "consumer");
			// if (isTransacted) {
			//	session.commit();
			// }
		}
		
	}
	
	
	public void processMaterial() throws JMSException {
		
		ObjectMessage message = (ObjectMessage) this.messageConsumerMaterial.receive(TIMEOUT);
		
		if (message != null && message instanceof ObjectMessage) {
			QueueMessageMaterial queueMsg = (QueueMessageMaterial) message.getObject();
			final String messageStr;
			if (this.databaseHelperMaterial.saveMessage(queueMsg)) {
				messageStr = "reference: " + queueMsg.getReference() + " stockid: " + queueMsg.getStockid();
				// session.commit();
				message.acknowledge();
			} else {
				messageStr = "[ERROR] reference: " + queueMsg.getReference() + " stockid: " + queueMsg.getStockid();
				// session.rollback();
			}
			
			logger.info(" Entrega mensage recibido > " + messageStr + " | Mensaje > " + message);
			
			if (this.listener != null) {
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						Consumer.this.listener
								.onQueueMessage(messageStr);
					}
				});
			}
			
			// Utils.setReplica(this.config, this.database, "consumer");
			// if (isTransacted) {
			// 	session.commit();
			// }
		}
	}
	
	public void processStock() throws JMSException {
		ObjectMessage message = (ObjectMessage) messageConsumerStock.receive(TIMEOUT);
		
		if (message != null && message instanceof ObjectMessage) {
		
			QueueMessageStocks queueMsg = (QueueMessageStocks) message.getObject();
			final String messageStr;
			if (this.databaseHelperStock.saveMessage(queueMsg)) {
				messageStr = "Id: " + queueMsg.getStockid();
				// session.commit();
				message.acknowledge();
			} else {
				messageStr = "[ERROR] Id: " + queueMsg.getStockid();
				// session.rollback();
			}
			logger.info(" Productos mensage recibido > " + messageStr + " | Mensaje > " + message);
			if (this.listener != null) {
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						Consumer.this.listener
								.onQueueMessage(messageStr);
					}
				});
			}
			// Utils.setReplica(this.config, this.database, "consumer");
			// if (isTransacted) {
			// 	session.commit();
			// }
		}
	}
	
	
	public void processReference() throws JMSException {
		ObjectMessage message = (ObjectMessage) messageConsumerReference.receive(TIMEOUT);
		
		if(message != null && message instanceof ObjectMessage) {
			
			QueueMessageReference queueMsg = (QueueMessageReference) message.getObject();
			final String messageStr;
			if (this.databaseHelperReference.saveMessage(queueMsg)) {
				messageStr = "Id: " + queueMsg.getAnio() + "-" + queueMsg.getLoccode() + "-" + queueMsg.getType();
				message.acknowledge();
			} else {
				messageStr = "Error Id: " + queueMsg.getAnio() + "-" + queueMsg.getLoccode() + "-" + queueMsg.getType();
			}
			
			if(this.listener != null) {
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						Consumer.this.listener.onQueueMessage(messageStr);
					}
				});
			}
			
			// Utils.setReplica(this.config, this.database, "consumer");
		}
	}
	
	
	public void processLocstock() throws JMSException {
		ObjectMessage message = (ObjectMessage) messageConsumerLocstock.receive(TIMEOUT);
		
		if(message != null && message instanceof ObjectMessage) {
			
			QueueMessageLockStock queueMsg = (QueueMessageLockStock) message.getObject();
			final String messageStr;
			if (this.databaseHelperLocstock.saveMessage(queueMsg)) {
				messageStr = "Id: " + queueMsg.getLoccode() + "-" + queueMsg.getStockid() + "-" + queueMsg.getLocalidad();
				message.acknowledge();
			} else {
				messageStr = "Error Id: " + queueMsg.getLoccode() + "-" + queueMsg.getStockid() + "-" + queueMsg.getLocalidad();
			}
			
			if(this.listener != null) {
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						Consumer.this.listener.onQueueMessage(messageStr);
					}
				});
			}
			
			// Utils.setReplica(this.config, this.database, "consumer");
		}
	}
	
	public void processAdjustment() throws JMSException {
		ObjectMessage message = (ObjectMessage) this.messageConsumerAdjustment.receive(TIMEOUT);
		
		if (message != null && message instanceof ObjectMessage) {
			
			QueueMessageAdjustment queueMsg = (QueueMessageAdjustment) message.getObject();
			final String messageStr;
			if (this.databaseHelperAdjustment.saveMessage(queueMsg)) {
				messageStr = "orderno: " + queueMsg.getOrderno();
				// session.commit();
				message.acknowledge();
			} else {
				messageStr = "[ERROR] orderno: " + queueMsg.getOrderno();
				// session.rollback();
				// message.acknowledge();
			}
			
			logger.info(" Ajustes mensage recibido > " + messageStr + " | Mensaje > " + message);
			
			if (this.listener != null) {
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						Consumer.this.listener
								.onQueueMessage(messageStr);
					}
				});
			}
			// Utils.setReplica(this.config, this.database, "consumer");
			// if (isTransacted) {
			//	session.commit();
			// }
		}
		
	}	
	public void processSalesOrders() throws JMSException {
		ObjectMessage message = (ObjectMessage) this.messageConsumerSalesOrders.receive(TIMEOUT);
		
		if (message != null && message instanceof ObjectMessage) {
			
			QueueMessagesalesorders queueMsg = (QueueMessagesalesorders) message.getObject();
			final String messageStr;
			if (this.databasesalesordersDBHelper.saveMessage(queueMsg)) {
				messageStr = "orderno: " + queueMsg.getOrderno();
				// session.commit();
				message.acknowledge();
			} else {
				messageStr = "[ERROR] orderno: " + queueMsg.getOrderno();
				// session.rollback();
				// message.acknowledge();
			}
			
			logger.info(" Ventas mensage recibido > " + messageStr + " | Mensaje > " + message);
			
			if (this.listener != null) {
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						Consumer.this.listener
								.onQueueMessage(messageStr);
					}
				});
			}
			// Utils.setReplica(this.config, this.database, "consumer");
			// if (isTransacted) {
			//	session.commit();
			// }
		}
		
	}
	
	public void processSalesDetails() throws JMSException {
		
		ObjectMessage message = (ObjectMessage) messageConsumerSalesDetails.receive(TIMEOUT);
		
		if (message != null && message instanceof ObjectMessage) {
			QueueMessageSalesDetails queueMsg = (QueueMessageSalesDetails) message.getObject();
			final String messageStr;
			if (this.databaseHelperSalesDetails.saveMessage(queueMsg)) {
				messageStr = "orderno: " + queueMsg.getOrderno() + " orderlineno: " + queueMsg.getOrderlineno();
				message.acknowledge();
			} else {
				messageStr = "[ERROR] orderno: " + queueMsg.getOrderno() + " orderlineno: " + queueMsg.getOrderlineno();
			}
			
			logger.info(" Detalles de venta mensage recibido > " + messageStr + " | Mensaje > " + message);
			
			if (this.listener != null) {
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						Consumer.this.listener
								.onQueueMessage(messageStr);
					}
				});
			}
			
			// Utils.setReplica(this.config, this.database, "consumer");
		}
	}
	
	public void processStockmoves() throws JMSException {
		
		ObjectMessage message = (ObjectMessage) messageConsumerStockmoves.receive(TIMEOUT);
		
		if (message != null && message instanceof ObjectMessage) {
			QueueMessageStockMoves queueMsg = (QueueMessageStockMoves) message.getObject();
			final String messageStr;
			if (this.databaseHelperStockmoves.saveMessage(queueMsg)) {
				messageStr = "Id: " + queueMsg.getStkmoveno();
				message.acknowledge();
			} else {
				messageStr = "Error Id: " + queueMsg.getStkmoveno();
			}
			
			logger.info(" Stockmoves mensage recibido > " + messageStr + " | Mensaje > " + message);
			
			if (this.listener != null) {
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						Consumer.this.listener
								.onQueueMessage(messageStr);
					}
				});
			}
			
			// Utils.setReplica(this.config, this.database, "consumer");
		}
	}

	public void endThread() {
		this.isRunning = false;
	}

	public void stopConsumer() {
		this.isRunning = false;
		try {
			if(this.messageConsumerTrans != null) {
				this.messageConsumerTrans.close();
			}
			if(this.messageConsumerStock != null) {
				this.messageConsumerStock.close();
			}
			if(this.messageConsumerMaterial != null) {
				this.messageConsumerMaterial.close();
			}
			if(this.messageConsumerReference != null) {
				this.messageConsumerReference.close();
			}
			if(this.messageConsumerLocstock != null) {
				this.messageConsumerLocstock.close();
			}
			if(this.messageConsumerAdjustment != null) {
				this.messageConsumerAdjustment.close();
			}
			if(this.messageConsumerSalesOrders != null) {
				this.messageConsumerSalesOrders.close();
			}
			if(this.messageConsumerSalesDetails != null) {
				this.messageConsumerSalesDetails.close();
			}
			if(this.messageConsumerStockmoves != null) {
				this.messageConsumerStockmoves.close();
			}
			if(this.session != null) {
				this.session.close();
			}
		} catch (Exception e) {
			logger.error("Error al cerrar consumers", e);
		}
		if (this.deadQueueNotifier != null) {
			this.deadQueueNotifier.stopDeadQueueNotifier();
		}
		try {
			this.connection.close();
		} catch (Throwable ignore) {
			logger.error("Error al cerrar producers", ignore);
			if (this.listener != null) {
				final String msg = ignore.getMessage();
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						Consumer.this.listener.onExceptionError(msg);
					}
				});
			}
		}
		this.databaseHelperTransfer.disconnect();
		this.databaseHelperMaterial.disconnect();
		this.databaseHelperStock.disconnect();
		this.databaseHelperReference.disconnect();
		this.databaseHelperLocstock.disconnect();
		this.databaseHelperAdjustment.disconnect();
		this.databasesalesordersDBHelper.disconnect();
		this.databaseHelperSalesDetails.disconnect();
		this.databaseHelperStockmoves.disconnect();
	}

	public interface ConsumerListener {
		public void onConsumerDisconnect();
	}
}
