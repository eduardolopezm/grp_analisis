package com.jahepi.activemq;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;

import javax.jms.Connection;
import javax.jms.Destination;
import javax.jms.Message;
import javax.jms.MessageConsumer;
import javax.jms.MessageListener;
import javax.jms.ObjectMessage;
import javax.jms.Session;

import org.apache.log4j.Logger;

import com.google.gson.Gson;
import com.jahepi.activemq.dto.QueueMessage;
import com.jahepi.activemq.loader.Config.ConfigData;

public class DeadQueueNotifier implements MessageListener {
	
	final static Logger logger = Logger.getLogger(DeadQueueNotifier.class);

	private static String NAME = "ActiveMQ.DLQ";
	private ConfigData config;
	private MessageConsumer messageConsumer;
	private Session session;

	public DeadQueueNotifier(ConfigData config) {
		this.config = config;
	}

	public void run(Connection connection) {

		Destination destination = null;

		try {
			this.session = connection.createSession(false,
					Session.AUTO_ACKNOWLEDGE);
			destination = this.session.createQueue(NAME);
			this.messageConsumer = this.session.createConsumer(destination);
			this.messageConsumer.setMessageListener(this);

		} catch (Exception e) {
			logger.error("Error Desconocido", e);
			this.stopDeadQueueNotifier();
		}
	}

	public void stopDeadQueueNotifier() {
		try {
			this.messageConsumer.close();
			this.session.close();
		} catch (Exception e) {
			logger.error("Error Desconocido", e);
		}
	}

	@Override
	public void onMessage(Message message) {
		ObjectMessage msg = (ObjectMessage) message;
		try {
			QueueMessage queueMsg = (QueueMessage) msg.getObject();

			Gson json = new Gson();
			String postData = "invoiceJson="
					+ URLEncoder.encode(json.toJson(queueMsg), "UTF-8");

			URL url = new URL(this.config.get("mailNotifierPage"));
			HttpURLConnection connection = (HttpURLConnection) url
					.openConnection();
			connection.setDoOutput(true);
			connection.setRequestMethod("POST");
			connection.setRequestProperty("Content-Type",
					"application/x-www-form-urlencoded");
			connection.setRequestProperty("Content-Length",
					String.valueOf(postData.length()));
			OutputStream os = connection.getOutputStream();
			os.write(postData.getBytes());
			StringBuilder responseSB = new StringBuilder();
			BufferedReader br = new BufferedReader(new InputStreamReader(
					connection.getInputStream()));

			String line;
			while ((line = br.readLine()) != null) {
				responseSB.append(line);
			}
			br.close();
			os.close();
			connection.disconnect();

		} catch (Exception e) {
			logger.error("Error Desconocido", e);
		}
	}
}
