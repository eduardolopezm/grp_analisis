package com.jahepi.activemq;

import java.awt.EventQueue;
import java.util.ArrayList;
import java.util.Iterator;

import javax.jms.Connection;
import javax.jms.DeliveryMode;
import javax.jms.Destination;
import javax.jms.JMSException;
import javax.jms.MessageProducer;
import javax.jms.ObjectMessage;
import javax.jms.Session;

import org.apache.activemq.ActiveMQConnectionFactory;
import org.apache.log4j.Logger;

import com.jahepi.activemq.database.Database;
import com.jahepi.activemq.database.ProducerConnStatusDBHelper;
import com.jahepi.activemq.dto.QueueMessageAMQStatus;
import com.jahepi.activemq.loader.Config.ConfigData;
import com.jahepi.activemq.view.AppListener;

public class ProducerBeep extends Thread {
	
	final static Logger logger = Logger.getLogger(ProducerBeep.class);

	private ConfigData config;
	private ProducerConnStatusDBHelper databaseHelper;
	private AppListener listener;
	private ProducerListener producerListener;
	private static final boolean isTransacted = false;
	private boolean isRunning = true;
	private Connection connection;
	private Session session;
	MessageProducer messageProducer;

	
	

	public ProducerBeep(ConfigData config, Database database, AppListener listener) {
		this.config = config;
		this.databaseHelper = new ProducerConnStatusDBHelper(database, config);
		this.listener = listener;
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
			// this.connection.setClientID("producerBeep_" + this.config.get("appid"));
			this.connection.start();

			if (this.listener != null) {
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						ProducerBeep.this.listener.onQueueConnect();
					}
				});
			}

			this.session = this.connection.createSession(isTransacted,
					Session.AUTO_ACKNOWLEDGE);
			
			try {
				Destination destination = null;
				String Queue = "";
				switch (this.config.get("type")) {
					case "central":
						Queue = this.config.get("HardBeepQueueSC");
						break;
					case "satelite":
						Queue = this.config.get("HardBeepQueueCS");
						break;
				}

				destination = this.session.createQueue(Queue);
				this.messageProducer = this.session.createProducer(destination);
				this.messageProducer.setDeliveryMode(DeliveryMode.PERSISTENT);
				logger.info("[QUEUE] HARDBEEP PRODUCER > " + Queue);
				
			} catch (JMSException e) {
				logger.error("Error JMS", e);
			} catch (Exception e) {
				logger.error("Error Desconocido", e);
			}

	
			long sleep = Long.parseLong(this.config.get("threadSleepTimeHardBeep"));

			while (this.isRunning) {
				
				process();
				
				Thread.sleep(sleep);
			}
			
		}  catch(javax.jms.IllegalStateException e) {
			logger.error("Error IlegalState", e);
			
			
			if (this.listener != null) {
				final String msg = e.getMessage();
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						ProducerBeep.this.listener.onExceptionError(msg);
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
						ProducerBeep.this.listener.onExceptionError(msg);
					}
				});
			}
		} finally {
			this.stopProducer();
			if (this.listener != null) {
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						ProducerBeep.this.listener.onQueueDisconnet();
					}
				});
			}
			if (this.producerListener != null) {
				this.producerListener.onProducerDisconnect();
			}
		}
	}
	
	public void process() throws JMSException, InterruptedException {
		ObjectMessage message = null;
		try {
			ArrayList<QueueMessageAMQStatus> messages = databaseHelper.getMessages();
			
			Iterator<QueueMessageAMQStatus> iterator = messages.iterator();
			while (iterator.hasNext()) {
				QueueMessageAMQStatus queueMsg = iterator.next();
				message = session.createObjectMessage(queueMsg);
				messageProducer.send(message);
				final String messageStr = "Id: " + queueMsg.getMessage();
				
				logger.info(" Mensage enviado > " + messageStr);
				
				if (this.listener != null) {
					EventQueue.invokeLater(new Runnable() {
						@Override
						public void run() {
							ProducerBeep.this.listener
									.onQueueMessage(messageStr);
						}
					});
				}
				this.databaseHelper.updateMessageAsSent(queueMsg);
				
				if (isTransacted) {
					session.commit();
					
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
			this.messageProducer.close();
		} catch (Exception e) {
			logger.error("Error estado ", e);
		}
		try {
			this.connection.close();
		} catch (Throwable ignore) {
			logger.error("Error al cerrar producer", ignore);
			
			if (this.listener != null) {
				final String msg = ignore.getMessage();
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						ProducerBeep.this.listener.onExceptionError(msg);
					}
				});
			}
		}
		this.databaseHelper.disconnect();
		
	}

	public interface ProducerListener {
		public void onProducerDisconnect();
	}
}
