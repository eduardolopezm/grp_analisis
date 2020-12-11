package com.jahepi.activemq.loader;

import java.io.File;
import java.util.HashMap;

import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;

import org.apache.log4j.Logger;
import org.w3c.dom.Document;
import org.w3c.dom.Element;

public class Config {
	
	final static Logger logger = Logger.getLogger(Config.class);

	private String file;
	private ConfigListener listener;
	
	public Config(String file, ConfigListener listener) {
		this.file = file;
		this.listener = listener;
	}

	public void load() {

		try {
			File fXmlFile = new File(this.file);
			DocumentBuilderFactory dbFactory = DocumentBuilderFactory
					.newInstance();
			DocumentBuilder dBuilder = dbFactory.newDocumentBuilder();
			Document doc = dBuilder.parse(fXmlFile);

			doc.getDocumentElement().normalize();

			Element config = doc.getDocumentElement();
			String server = config.getElementsByTagName("server").item(0)
					.getTextContent();
			String type = config.getElementsByTagName("type").item(0)
					.getTextContent();
			String port = config.getElementsByTagName("port").item(0)
					.getTextContent();
			String user = config.getElementsByTagName("user").item(0)
					.getTextContent();
			String pass = config.getElementsByTagName("pass").item(0)
					.getTextContent();
			String appid = config.getElementsByTagName("AppId").item(0)
					.getTextContent();
			String siteid = config.getElementsByTagName("SiteId").item(0)
					.getTextContent();
			String sitename = config.getElementsByTagName("SiteName").item(0)
					.getTextContent();
			String unidad = config.getElementsByTagName("unidad").item(0)
					.getTextContent();
			String log4j = config.getElementsByTagName("log4j").item(0)
					.getTextContent();
			String EnableHardBeep = config.getElementsByTagName("EnableHardBeep").item(0)
					.getTextContent();
			String threadSleepTimeHardBeep = config
					.getElementsByTagName("threadSleepTimeHardBeep").item(0)
					.getTextContent();
			String HardBeepQueueCS = config
					.getElementsByTagName("HardBeepQueueCS").item(0)
					.getTextContent();
			String HardBeepQueueSC = config.getElementsByTagName("HardBeepQueueSC").item(0)
					.getTextContent();
			String threadSleepTime = config
					.getElementsByTagName("threadSleepTime").item(0)
					.getTextContent();
			String Driver = config
					.getElementsByTagName("Driver").item(0)
					.getTextContent();
			String UrlDriver = config
					.getElementsByTagName("UrlDriver").item(0)
					.getTextContent();
			String msgtxtPath = config.getElementsByTagName("msgtxtPath").item(0)
					.getTextContent();
			String onErrorSaveFile = config.getElementsByTagName("onErrorSaveFile").item(0)
					.getTextContent();
			String consumerMaximumRedeliveries = config
					.getElementsByTagName("consumerMaximumRedeliveries")
					.item(0).getTextContent();

			ConfigData data = new ConfigData();
			data.set("server", server);
			data.set("type", type);
			data.set("port", port);
			data.set("user", user);
			data.set("pass", pass);
			data.set("appid", appid);
			data.set("siteid", siteid);
			data.set("sitename", sitename);
			data.set("HardBeepQueueCS", HardBeepQueueCS);
			data.set("EnableHardBeep", EnableHardBeep);
			data.set("HardBeepQueueSC", HardBeepQueueSC);
			data.set("threadSleepTimeHardBeep", threadSleepTimeHardBeep);
			data.set("threadSleepTime", threadSleepTime);
			data.set("Driver", Driver);
			data.set("UrlDriver", UrlDriver);
			data.set("msgtxtPath", msgtxtPath);
			data.set("onErrorSaveFile", onErrorSaveFile);
			data.set("consumerMaximumRedeliveries", consumerMaximumRedeliveries);
			data.set("version", "v2.9");
			data.set("unidad", unidad);
			data.set("log4j", log4j);

			this.listener.onLoad(data);

		} catch (Exception exp) {
			// logger.error("Error desconocido", exp);
			this.listener.onError();
		}
	}

	public class ConfigData {

		private HashMap<String, String> values;

		public ConfigData() {
			this.values = new HashMap<String, String>();
		}

		public void set(String key, String value) {
			this.values.put(key, value);
		}

		public String get(String key) {
			return this.values.get(key);
		}
	}

	public interface ConfigListener {
		public void onLoad(ConfigData data);

		public void onError();

		void onProducerDisconnect(String type);

		void onConsumerDisconnect(String type);
	}
}
