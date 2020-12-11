package com.jahepi.activemq;

import java.awt.EventQueue;
import org.apache.log4j.Logger;
import java.sql.ResultSet;
import java.util.ArrayList;
import java.util.Iterator;
import java.util.UUID;

import javax.jms.Connection;
import javax.jms.DeliveryMode;
import javax.jms.Destination;
import javax.jms.JMSException;
import javax.jms.MessageProducer;
import javax.jms.ObjectMessage;
import javax.jms.Session;

import org.apache.activemq.ActiveMQConnectionFactory;

import com.jahepi.activemq.database.Database;
import com.jahepi.activemq.database.ProducerDBHelper;
import com.jahepi.activemq.database.ProducerLocstockDBHelper;
import com.jahepi.activemq.database.ProducerMaterialDBHelper;
import com.jahepi.activemq.database.ProducerReferenceDBHelper;
import com.jahepi.activemq.database.ProducerSalesDetailsDBHelper;
import com.jahepi.activemq.database.ProducerStockDBHelper;
import com.jahepi.activemq.database.ProducerStockmovesDBHelper;
import com.jahepi.activemq.database.ProducersalesordersDBHelper;
import com.jahepi.activemq.database.Database.DBResultSet;
import com.jahepi.activemq.database.ProducerAdjustmentDBHelper;
import com.jahepi.activemq.dto.QueueMessage;
import com.jahepi.activemq.dto.QueueMessageLockStock;
import com.jahepi.activemq.dto.QueueMessageMaterial;
import com.jahepi.activemq.dto.QueueMessageReference;
import com.jahepi.activemq.dto.QueueMessageSalesDetails;
import com.jahepi.activemq.dto.QueueMessageStockMoves;
import com.jahepi.activemq.dto.QueueMessageStocks;
import com.jahepi.activemq.dto.QueueMessagesalesorders;
import com.jahepi.activemq.loader.Config.ConfigData;
import com.jahepi.activemq.view.AppListener;

public class Producer extends Thread {
	
	final static Logger logger = Logger.getLogger(Producer.class);

	private ConfigData config;
	private Database database;
	private ProducerDBHelper databaseHelper;
	private ProducerMaterialDBHelper databaseHelperMaterial;
	private ProducerStockDBHelper databaseHelperStock;
	private ProducerReferenceDBHelper databaseHelperReference;
	private ProducerLocstockDBHelper databaseHelperLocstock;
	private ProducerAdjustmentDBHelper databaseHelperAdjustment;
	private ProducersalesordersDBHelper databasesalesordersDBHelper;
	private ProducerSalesDetailsDBHelper databaseHelperSalesDetails;
	private ProducerStockmovesDBHelper databaseHelperStockmoves;
	private AppListener listener;
	private ProducerListener producerListener;
	private static final boolean isTransacted = false;
	private boolean isRunning = true;
	private Connection connection;
	private Session session;
	MessageProducer messageProducerTransfer;
	MessageProducer messageProducerMaterial;
	MessageProducer messageProducerStock;
	MessageProducer messageProducerReference;
	MessageProducer messageProducerLocstock;
	MessageProducer messageProducerAdjustment;
	MessageProducer messageProducerSalesOrders;
	MessageProducer messageProducerSalesDetails;
	MessageProducer messageProducerStockmoves;
	Destination destinationTransfer = null;
	Destination destinationMaterial = null;
	Destination destinationStock = null;
	Destination destinationAdjustment = null;
	Destination SalesOrders = null;
	Destination destinationSalesDetails = null;
	Destination destinationStokmoves = null;
	private String stocks;
	// private QueueMessageStocks gmsg;
	
	

	public Producer(ConfigData config, Database database, AppListener listener) {
		this.config = config;
		this.databaseHelper = new ProducerDBHelper(database, config);
		this.databaseHelperMaterial = new ProducerMaterialDBHelper(database, config);
		this.databaseHelperStock = new ProducerStockDBHelper(database, config);
		this.databaseHelperReference = new ProducerReferenceDBHelper(database, config);
		this.databaseHelperLocstock = new ProducerLocstockDBHelper(database, config);
		this.databaseHelperAdjustment = new ProducerAdjustmentDBHelper(database, config);
		this.databasesalesordersDBHelper = new ProducersalesordersDBHelper(database, config);
		this.databaseHelperSalesDetails = new ProducerSalesDetailsDBHelper(database, config);
		this.databaseHelperStockmoves = new ProducerStockmovesDBHelper(database, config);
		this.listener = listener;
		this.database = database;
		this.stocks = "";
	}

	public void setProducerListener(ProducerListener producerListener) {
		this.producerListener = producerListener;
	}

	@Override
	public void run() {

		try {
			
			String url = "failover://(tcp://" + this.config.get("server") + ":"
					+ this.config.get("port") +")";
			/* String url = "tcp://" + this.config.get("server") + ":"
					+ this.config.get("port"); */
			ActiveMQConnectionFactory connectionFactory = new ActiveMQConnectionFactory(
					this.config.get("user"), this.config.get("pass"), url);
		
			this.connection = connectionFactory.createConnection();
			// this.connection.setClientID("producer_" + this.config.get("appid"));
			this.connection.start();

			if (this.listener != null) {
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						Producer.this.listener.onQueueConnect();
					}
				});
			}

			this.session = this.connection.createSession(isTransacted,
					Session.AUTO_ACKNOWLEDGE);
			
			try {
				
				
				String sqlq = "SELECT "
						+ "tags_queue.transfers_producer, "
						+ "tags_queue.stocks_queue, "
						+ "tags_queue.material_producer, "
						+ "tags_queue.reference_producer, "
						+ "tags_queue.locstock_producer, "
						+ "tags_queue.inventoryadjustment_queue, "
						+ "tags_queue.salesorderdetails_queue, "
						+ "tags_queue.stockmoves_queue, "
						+ "tags_queue.salesorders_queu "
						+ "FROM tags_queue WHERE tags_queue.type = '" + this.config.get("type") + "' AND "
								+ "tags_queue.tags = '" + this.config.get("unidad") + "'";
				
				logger.debug(sqlq);
				
				DBResultSet result = this.database.executeQuery(sqlq);
				ResultSet rs = result.getResultSet();
				
				this.stocks = "";
				if(rs.next()) {
					do {
						if(this.config.get("type").equals("central")){
							destinationTransfer = this.session.createQueue(rs.getString("transfers_producer"));
							this.messageProducerTransfer = this.session.createProducer(destinationTransfer);
							this.messageProducerTransfer.setDeliveryMode(DeliveryMode.PERSISTENT);
							
							destinationMaterial = this.session.createQueue(rs.getString("material_producer"));
							this.messageProducerMaterial = this.session.createProducer(destinationMaterial);
							this.messageProducerMaterial.setDeliveryMode(DeliveryMode.PERSISTENT);
							
							/*destinationReference = this.session.createQueue(rs.getString("reference_producer"));
							this.messageProducerReference = this.session.createProducer(destinationReference);
							this.messageProducerReference.setDeliveryMode(DeliveryMode.PERSISTENT);
							
							destinationLocstock = this.session.createQueue(rs.getString("locstock_producer"));
							this.messageProducerLocstock = this.session.createProducer(destinationLocstock);
							this.messageProducerLocstock.setDeliveryMode(DeliveryMode.PERSISTENT);*/
							
							String sqlproducto = "SELECT stocks_queue FROM tags_queue WHERE type = 'central'";
							
							logger.debug(sqlproducto);
							
							DBResultSet result_producto = this.database.executeQuery(sqlproducto);
							ResultSet rs_producto = result_producto.getResultSet();
	 						
							String stocks_queues = "";
							while (rs_producto.next()) {
								stocks_queues = stocks_queues + rs_producto.getString("stocks_queue") + ",";
							}
							
							stocks_queues = stocks_queues.substring(0, stocks_queues.length()-1);
							
							destinationStock = this.session.createQueue(stocks_queues);
							this.messageProducerStock = this.session.createProducer(destinationStock);
							this.messageProducerStock.setDeliveryMode(DeliveryMode.PERSISTENT);
							
							
							logger.info("[QUEUE] TRANSFERENCIAS PRODUCER > " + rs.getString("transfers_producer"));
							logger.info("[QUEUE] ENTREGAS PRODUCER > " + rs.getString("material_producer"));
							logger.info("[QUEUE] PRODUCTOS PRODUCER > " + stocks_queues);
							
						
						}else if(this.config.get("type").equals("satelite")){
							destinationTransfer = this.session.createQueue(rs.getString("transfers_producer"));
							this.messageProducerTransfer = this.session.createProducer(destinationTransfer);
							this.messageProducerTransfer.setDeliveryMode(DeliveryMode.PERSISTENT);
						
							destinationMaterial = this.session.createQueue(rs.getString("material_producer"));
							this.messageProducerMaterial = this.session.createProducer(destinationMaterial);
							this.messageProducerMaterial.setDeliveryMode(DeliveryMode.PERSISTENT);
							
							/*	destinationReference = this.session.createQueue(rs.getString("reference_producer"));
							this.messageProducerReference = this.session.createProducer(destinationReference);
							this.messageProducerReference.setDeliveryMode(DeliveryMode.PERSISTENT);
							
							destinationLocstock = this.session.createQueue(rs.getString("locstock_producer"));
							this.messageProducerLocstock = this.session.createProducer(destinationLocstock);
							this.messageProducerLocstock.setDeliveryMode(DeliveryMode.PERSISTENT);*/
							
							
							destinationAdjustment = this.session.createQueue(rs.getString("inventoryadjustment_queue"));
							this.messageProducerAdjustment = this.session.createProducer(destinationAdjustment);
							this.messageProducerAdjustment.setDeliveryMode(DeliveryMode.PERSISTENT);
							
							SalesOrders = this.session.createQueue(rs.getString("salesorders_queu"));
							this.messageProducerSalesOrders = this.session.createProducer(SalesOrders);
							this.messageProducerSalesOrders.setDeliveryMode(DeliveryMode.PERSISTENT);
							
							destinationSalesDetails = this.session.createQueue(rs.getString("salesorderdetails_queue"));
							this.messageProducerSalesDetails = this.session.createProducer(destinationSalesDetails);
							this.messageProducerSalesDetails.setDeliveryMode(DeliveryMode.PERSISTENT);
								
							destinationSalesDetails = this.session.createQueue(rs.getString("stockmoves_queue"));
							this.messageProducerStockmoves = this.session.createProducer(destinationStokmoves);
							this.messageProducerStockmoves.setDeliveryMode(DeliveryMode.PERSISTENT);
							
							logger.info("[QUEUE] TRANSFERENCIAS PRODUCER > " + rs.getString("transfers_producer"));
							logger.info("[QUEUE] ENTREGAS PRODUCER > " + rs.getString("material_producer"));
							logger.info("[QUEUE] AJUSTES PRODUCER > " + rs.getString("inventoryadjustment_queue"));
							logger.info("[QUEUE] SALESORDERS PRODUCER > " + rs.getString("salesorders_queu"));
							logger.info("[QUEUE] SALESORDERDETAILS PRODUCER > " + rs.getString("salesorderdetails_queue"));
							logger.info("[QUEUE] STOCKMOVES PRODUCER > " + rs.getString("stockmoves_queue"));
							
						}
					
						
						
					}while (rs.next());
				}
				else {
					destinationTransfer = this.session.createQueue(this.config.get("queuet1"));
					this.messageProducerTransfer = this.session.createProducer(destinationTransfer);
					this.messageProducerTransfer.setDeliveryMode(DeliveryMode.PERSISTENT);
					
					destinationMaterial = this.session.createQueue(this.config.get("queueconsumermaterial1"));
					this.messageProducerMaterial = this.session.createProducer(destinationMaterial);
					this.messageProducerMaterial.setDeliveryMode(DeliveryMode.PERSISTENT);
					
					destinationStock = this.session.createQueue(this.config.get("queueconsumerstock"));
					this.messageProducerStock = this.session.createProducer(destinationStock);
					this.messageProducerStock.setDeliveryMode(DeliveryMode.PERSISTENT);
					
					logger.info("[QUEUE] TRANSFERENCIA PRODUCER > " + rs.getString("queuet1"));
					logger.info("[QUEUE] ENTREGAS PRODUCER > " + rs.getString("queueconsumerstock"));
					logger.info("[QUEUE] PRODUCTOS PRODUCER > " + rs.getString("queuet1"));
				}
				
			} catch (JMSException e) {
				logger.error("Error ActiveMQ", e);
			} catch (Exception e) {
				logger.error("Error Desconocido", e);
			}

	
			long sleep = Long.parseLong(this.config.get("threadSleepTime"));

			while (this.isRunning) {
				switch (this.config.get("type")) {
					case "central":
						processTransferencias();
						processEntregaMaterial();
						processStock();
						
						break;
					case "satelite":
							processTransferencias();
							processEntregaMaterial();
							/*processReference();
							processLocstock();*/
							processAdjustements();
							processSalesOrders();
							processSalesDetails();
							// processStockmoves();
						break;
					default:
						break;
				}
				
				Thread.sleep(sleep);
			}
			
		}  catch(javax.jms.IllegalStateException e) {
			logger.error("Error jms", e);
			
			if (this.listener != null) {
				final String msg = e.getMessage();
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						Producer.this.listener.onExceptionError(msg);
					}
				});
			}
			
			this.stopProducer();
			this.start();
			
		} catch (Exception e) {
			logger.error("Error Desconocido", e);
			
			if (this.listener != null) {
				final String msg = e.getMessage();
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						Producer.this.listener.onExceptionError(msg);
					}
				});
			}
		} finally {
			this.stopProducer();
			if (this.listener != null) {
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						Producer.this.listener.onQueueDisconnet();
					}
				});
			}
			if (this.producerListener != null) {
				this.producerListener.onProducerDisconnect();
			}
		}
	}
	
	public void processTransferencias() throws JMSException, InterruptedException {
		ObjectMessage message = null;
		try {
			ArrayList<QueueMessage> messages = databaseHelper.getMessages();
			
			Iterator<QueueMessage> iterator = messages.iterator();
			while (iterator.hasNext()) {
				QueueMessage queueMsg = iterator.next();
				
				final String correlationID = UUID.randomUUID().toString();
				message = session.createObjectMessage(queueMsg);
				message.setJMSCorrelationID(correlationID);
				message.setJMSReplyTo(destinationTransfer);
				
				messageProducerTransfer.send(message);
				final String messageStr = "reference: " + queueMsg.getReference() + " stockid: " + queueMsg.getStockid();
				
				logger.info(" Transferencia mensage enviado > " + messageStr);
				
				if (this.listener != null) {
					EventQueue.invokeLater(new Runnable() {
						@Override
						public void run() {
							Producer.this.listener
									.onQueueMessage(messageStr);
						}
					});
				}
				this.databaseHelper.updateMessageAsSent(queueMsg);
				// Utils.setReplica(this.config, this.database, "producer");
				if (isTransacted) {
					session.commit();
				}
			}
		} catch (Exception e) {
			logger.error("Error Desconocido", e);
		}
	}
	
	public void processEntregaMaterial() throws JMSException, InterruptedException {
		ObjectMessage message = null;
		
		try {
			ArrayList<QueueMessageMaterial> messages = databaseHelperMaterial.getMessages();
			Iterator<QueueMessageMaterial> iterator = messages.iterator();
			while (iterator.hasNext()) {
				QueueMessageMaterial queueMsg = iterator.next();
				/*if(this.config.get("type").equals("central")) {
					Destination destinationMaterial = null;
					destinationMaterial = this.session.createQueue(queueMsg.getQueue());
					this.messageProducerMaterial = this.session.createProducer(destinationMaterial);
					this.messageProducerMaterial.setDeliveryMode(DeliveryMode.PERSISTENT);
				}*/
				
				message = session.createObjectMessage(queueMsg);
				messageProducerMaterial.send(message);
				final String messageStr = "reference: " + queueMsg.getReference() + " stockid: " + queueMsg.getStockid();
				
				logger.info(" Entrega mensage enviado > " + messageStr);
					
				if (this.listener != null) {
					EventQueue.invokeLater(new Runnable() {
						@Override
						public void run() {
							Producer.this.listener
									.onQueueMessage(messageStr);
						}
					});
				}
				this.databaseHelperMaterial.updateMessageAsSent(queueMsg);
				// Utils.setReplica(this.config, this.database, "producer");
				if (isTransacted) {
					session.commit();
				}
			}
		} catch (Exception e) {
			logger.error("Error Desconocido", e);
		}
		
	}
	
	public void processStock() throws JMSException, InterruptedException {
		ObjectMessage message = null;
		
		try {
			ArrayList<QueueMessageStocks> messages = databaseHelperStock.getMessages();
			Iterator<QueueMessageStocks> iterator = messages.iterator();
			while (iterator.hasNext()) {
				QueueMessageStocks queueMsg = iterator.next();
				message = session.createObjectMessage(queueMsg);
				messageProducerStock.send(message);
				final String messageStr = "stockid: " + queueMsg.getStockid();
				
				logger.info(" Productos mensage enviado > " + messageStr);
					
				this.stocks = this.stocks + queueMsg.getStockid() + ",";
				if (this.listener != null) {
					EventQueue.invokeLater(new Runnable() {
						@Override
						public void run() {
							Producer.this.listener
									.onQueueMessage(messageStr);
						}
					});
				}
				
				this.databaseHelperStock.updateMessagesAsSent(queueMsg);
				// Utils.setReplica(this.config, this.database, "producer");
				if (isTransacted) {
					session.commit();
				}
			}
		} catch (Exception e) {
			logger.error("Error Desconocido", e);
		}	
		
	}
	
	public void processReference() throws JMSException, InterruptedException {
		ObjectMessage message = null;
		
		try {
			ArrayList<QueueMessageReference> messages = databaseHelperReference.getMessages();
			Iterator<QueueMessageReference> iterator = messages.iterator();
			while(iterator.hasNext()) {
				QueueMessageReference queueMsg = iterator.next();
				message = session.createObjectMessage(queueMsg);
				messageProducerReference.send(message);
				final String messageStr = "Id: " + queueMsg.getAnio() + "-" + queueMsg.getLoccode() + "-" + queueMsg.getType();
				logger.info(" Referencias mensage enviado > " + messageStr);
				
				if( this.listener != null) {
					EventQueue.invokeLater(new Runnable() {
						@Override
						public void run() {
							Producer.this.listener.onQueueMessage(messageStr);
						}
					});
				}
				
				this.databaseHelperReference.updateMessageAsSent(queueMsg);
				// Utils.setReplica(this.config, this.database, "producer");
				if(isTransacted) {
					session.commit();
				}
			}
		} catch (Exception e) {
			logger.error("Error Desconocido", e);
		}
	}
	
	public void processLocstock() throws JMSException, InterruptedException {
		ObjectMessage message = null;
		
		try {
			ArrayList<QueueMessageLockStock> messages = databaseHelperLocstock.getMessages();
			Iterator<QueueMessageLockStock> iterator = messages.iterator();
			while(iterator.hasNext()) {
				QueueMessageLockStock queueMsg = iterator.next();
				message = session.createObjectMessage(queueMsg);
				messageProducerReference.send(message);
				final String messageStr = "Id: " + queueMsg.getLoccode() + "-" + queueMsg.getStockid() + "-" + queueMsg.getLocalidad();
				logger.info(" LockStock mensage enviado > " + messageStr);
				
				if( this.listener != null) {
					EventQueue.invokeLater(new Runnable() {
						@Override
						public void run() {
							Producer.this.listener.onQueueMessage(messageStr);
						}
					});
				}
				
				this.databaseHelperLocstock.updateMessageAsSent(queueMsg);
				// Utils.setReplica(this.config, this.database, "producer");
				if(isTransacted) {
					session.commit();
				}
			}
		} catch (Exception e) {
			logger.error("Error Desconocido", e);
		}
	}
	
	public void processAdjustements() throws JMSException, InterruptedException {
		ObjectMessage message = null;
		try {
			ArrayList<QueueMessageStockMoves> messages = databaseHelperStockmoves.getMessages();
			
			Iterator<QueueMessageStockMoves> iterator = messages.iterator();
			while (iterator.hasNext()) {
				QueueMessageStockMoves queueMsg = iterator.next();
				
				message = session.createObjectMessage(queueMsg);
				messageProducerAdjustment.send(message);
				final String messageStr = "Id: " + queueMsg.getStkmoveno();
				
				logger.info(" Ajustes mensage enviado > " + messageStr);
				
				if (this.listener != null) {
					EventQueue.invokeLater(new Runnable() {
						@Override
						public void run() {
							Producer.this.listener
									.onQueueMessage(messageStr);
						}
					});
				}
				this.databaseHelperStockmoves.updateMessageAsSent(queueMsg);
				// Utils.setReplica(this.config, this.database, "producer");
				if (isTransacted) {
					session.commit();
					
				}
			}
		} catch (Exception e) {
			logger.error("Error Desconocido", e);
		}
	}
	
	public void processSalesOrders() throws JMSException, InterruptedException {
		ObjectMessage message = null;
		
		try {
			ArrayList<QueueMessagesalesorders> messages = databasesalesordersDBHelper.getMessages();
			Iterator<QueueMessagesalesorders> iterator = messages.iterator();
			while (iterator.hasNext()) {
				QueueMessagesalesorders queueMsg = iterator.next();
				message = session.createObjectMessage(queueMsg);
				messageProducerSalesOrders.send(message);
				final String messageStr = "orderno: " + queueMsg.getOrderno();
				
				logger.info(" Ventas mensage enviado > " + messageStr);
					
				// this.stocks = this.stocks + queueMsg.getOrderno() + ",";
				if (this.listener != null) {
					EventQueue.invokeLater(new Runnable() {
						@Override
						public void run() {
							Producer.this.listener
									.onQueueMessage(messageStr);
						}
					});
				}
				
				this.databasesalesordersDBHelper.updateMessageAsSent(queueMsg);
				// Utils.setReplica(this.config, this.database, "producer");
				if (isTransacted) {
					session.commit();
				}
			}
		} catch (Exception e) {
			logger.error("Error Desconocido", e);
		}	
		
	}
	
	public void processSalesDetails() throws JMSException, InterruptedException {
		ObjectMessage message = null;
		
		try {
			ArrayList<QueueMessageSalesDetails> messages = this.databaseHelperSalesDetails.getMessages();
			Iterator<QueueMessageSalesDetails> iterator = messages.iterator();
			while (iterator.hasNext()) {
				QueueMessageSalesDetails queueMsg = iterator.next();
				
				message = this.session.createObjectMessage(queueMsg);
				this.messageProducerSalesDetails.send(message);
				final String messageStr = "orderno: " + queueMsg.getOrderno() + " orderlineno: " + queueMsg.getOrderlineno();
				
				logger.info(" Detalles de venta mensage enviado > " + messageStr);
					
				if (this.listener != null) {
					EventQueue.invokeLater(new Runnable() {
						@Override
						public void run() {
							Producer.this.listener
									.onQueueMessage(messageStr);
						}
					});
				}
				this.databaseHelperSalesDetails.updateMessageAsSent(queueMsg);
				// Utils.setReplica(this.config, this.database, "producer");
				if (isTransacted) {
					this.session.commit();
				}
			}
		} catch (Exception e) {
			logger.error("Error Desconocido", e);
		}
		
	}
	
	
	public void processStockmoves() throws JMSException, InterruptedException {
		ObjectMessage message = null;
		
		try {
			ArrayList<QueueMessageSalesDetails> messages = this.databaseHelperSalesDetails.getMessages();
			Iterator<QueueMessageSalesDetails> iterator = messages.iterator();
			while (iterator.hasNext()) {
				QueueMessageSalesDetails queueMsg = iterator.next();
				
				message = this.session.createObjectMessage(queueMsg);
				this.messageProducerSalesDetails.send(message);
				final String messageStr = "Id: " + queueMsg.getOrderno();
				
				logger.info(" Stockmoves de venta mensage enviado > " + messageStr);
					
				if (this.listener != null) {
					EventQueue.invokeLater(new Runnable() {
						@Override
						public void run() {
							Producer.this.listener
									.onQueueMessage(messageStr);
						}
					});
				}
				this.databaseHelperSalesDetails.updateMessageAsSent(queueMsg);
				// Utils.setReplica(this.config, this.database, "producer");
				if (isTransacted) {
					this.session.commit();
				}
			}
		} catch (Exception e) {
			logger.error("Error Desconocido", e);
		}
		
	}
	
	public void endThread() {
		this.isRunning = false;
	}

	public void stopProducer() {
		this.isRunning = false;
		try {
			this.messageProducerTransfer.close();
			this.messageProducerMaterial.close();
			this.messageProducerStock.close();
			this.messageProducerReference.close();
			this.messageProducerLocstock.close();
			this.messageProducerAdjustment.close();
			this.messageProducerSalesOrders.close();
			this.messageProducerSalesDetails.close();
			this.messageProducerStockmoves.close();
			// this.session.close();
		} catch (Exception e) {
			logger.error("Error al cerrar producers", e);
		}
		try {
			this.connection.close();
		} catch (Throwable ignore) {
			logger.error("Error al cerrar conecccion", ignore);
			
			if (this.listener != null) {
				final String msg = ignore.getMessage();
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						Producer.this.listener.onExceptionError(msg);
					}
				});
			}
		}
		this.databaseHelper.disconnect();
		this.databaseHelperMaterial.disconnect();
		//this.databaseHelperStock.disconnect();
		//this.databaseHelperReference.disconnect();
		//this.databaseHelperLocstock.disconnect();
		this.databaseHelperAdjustment.disconnect();
		this.databasesalesordersDBHelper.disconnect();
		this.databaseHelperSalesDetails.disconnect();
		this.databaseHelperStockmoves.disconnect();
	}

	public interface ProducerListener {
		public void onProducerDisconnect();
	}
}
