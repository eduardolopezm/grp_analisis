package com.jahepi.activemq;

import java.awt.EventQueue;
import javax.jms.Connection;
import javax.jms.Destination;
import javax.jms.JMSException;
import javax.jms.MessageConsumer;
import javax.jms.ObjectMessage;
import javax.jms.Session;

import org.apache.activemq.ActiveMQConnectionFactory;
import org.apache.activemq.RedeliveryPolicy;
import org.apache.log4j.Logger;

import com.jahepi.activemq.database.ConsumerConnStatusDBHelper;
import com.jahepi.activemq.database.Database;
import com.jahepi.activemq.dto.QueueMessageAMQStatus;
import com.jahepi.activemq.loader.Config.ConfigData;
import com.jahepi.activemq.view.AppListener;

public class ConsumerBeep extends Thread {
	
	final static Logger logger = Logger.getLogger(ConsumerBeep.class);
	
	private static final int TIMEOUT = 1000;

	private ConfigData config;
	private ConsumerConnStatusDBHelper databaseHelper;
	private AppListener listener;
	private ConsumerListener consumerListener;
	// private static final boolean isTransacted = true;
	private boolean isRunning = true;
	private Connection connection;
	private Session session;
	private DeadQueueNotifier deadQueueNotifier;
	private MessageConsumer messageConsumer;
	private Destination destination;

	public ConsumerBeep(ConfigData config, Database database, AppListener listener) {
		this.config = config;
		this.databaseHelper = new ConsumerConnStatusDBHelper(database, config);
		this.listener = listener;
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
			// this.connection.setClientID("consumerBeep_" + this.config.get("appid"));
			this.connection.start();
			
			this.deadQueueNotifier = new DeadQueueNotifier(this.config);
			deadQueueNotifier.run(this.connection);

			if (this.listener != null) {
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						ConsumerBeep.this.listener.onQueueConnect();
					}
				});
			}

			this.session = this.connection.createSession(false, Session.CLIENT_ACKNOWLEDGE);
			// this.session = this.connection.createSession(isTransacted, -1);
			
			try {
				this.destination = null;
				String Queue = "";
				switch (this.config.get("type")) {
					case "central":
						Queue = this.config.get("HardBeepQueueCS");
						break;
					case "satelite":
						Queue = this.config.get("HardBeepQueueSC");
						break;
				}
				
				this.destination = this.session.createQueue(Queue);
				this.messageConsumer = this.session.createConsumer(this.destination);
				
				logger.info("[QUEUE] HARDBEEP CONSUMER > " + Queue);
				
			}catch (Exception e) {
				logger.error("Error Desconocido", e);
			}
			
			long sleep = Long.parseLong(this.config.get("threadSleepTimeHardBeep"));

			while (this.isRunning) {
			
				process();
					
				Thread.sleep(sleep);
			}

		} catch(javax.jms.IllegalStateException e) {
			logger.error("Error ilegalstate", e);
			
			
			if (this.listener != null) {
				final String msg = e.getMessage();
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						ConsumerBeep.this.listener.onExceptionError(msg);
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
						ConsumerBeep.this.listener.onExceptionError(msg);
					}
				});
			}
		} finally {
			this.stopConsumer();
			if (this.listener != null) {
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						ConsumerBeep.this.listener.onQueueDisconnet();
					}
				});
			}
			if (this.consumerListener != null) {
				this.consumerListener.onConsumerDisconnect();
			}
		}
	}
	
	public void process() throws JMSException {
		ObjectMessage message = (ObjectMessage) this.messageConsumer.receive(TIMEOUT);
		
		if (message != null && message instanceof ObjectMessage) {
			
			QueueMessageAMQStatus queueMsg = (QueueMessageAMQStatus) message.getObject();
			final String messageStr;
			if (this.databaseHelper.saveMessage(queueMsg)) {
				messageStr = "Id: " + queueMsg.getMessage();
				message.acknowledge();
			} else {
				messageStr = "[ERROR] Id: " + queueMsg.getMessage();
			}
			
			logger.info(" Mensage recibido > " + messageStr + " | Mensaje > " + message);
			
			if (this.listener != null) {
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						ConsumerBeep.this.listener
								.onQueueMessage(messageStr);
					}
				});
			}
		}
		
	}

	public void endThread() {
		this.isRunning = false;
	}

	public void stopConsumer() {
		this.isRunning = false;
		try {
			this.messageConsumer.close();
		} catch (Exception e) {
			logger.error("Error desconocido ", e);
		}
		if (this.deadQueueNotifier != null) {
			this.deadQueueNotifier.stopDeadQueueNotifier();
		}
		try {
			this.connection.close();
		} catch (Throwable ignore) {
			logger.error("Error al cerrar consumer", ignore);
			if (this.listener != null) {
				final String msg = ignore.getMessage();
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						ConsumerBeep.this.listener.onExceptionError(msg);
					}
				});
			}
		}
		this.databaseHelper.disconnect();
	}

	public interface ConsumerListener {
		public void onConsumerDisconnect();
	}
}
