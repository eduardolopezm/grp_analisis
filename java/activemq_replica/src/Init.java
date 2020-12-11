import java.awt.EventQueue;

import org.apache.log4j.Logger;
import org.apache.log4j.xml.DOMConfigurator;

import com.jahepi.activemq.Consumer;
import com.jahepi.activemq.Consumer.ConsumerListener;
import com.jahepi.activemq.ConsumerBeep;
import com.jahepi.activemq.Producer;
import com.jahepi.activemq.Producer.ProducerListener;
import com.jahepi.activemq.ProducerBeep;
import com.jahepi.activemq.database.Database;
import com.jahepi.activemq.loader.Config;
import com.jahepi.activemq.loader.Config.ConfigData;
import com.jahepi.activemq.loader.Config.ConfigListener;
import com.jahepi.activemq.view.AppListener;
import com.jahepi.activemq.view.ViewListener;

public class Init implements ConfigListener, ConsumerListener,
		ProducerListener, ViewListener, com.jahepi.activemq.ProducerBeep.ProducerListener, com.jahepi.activemq.ConsumerBeep.ConsumerListener {
	
	static final Logger logger = Logger.getLogger(Init.class);

	private static String CENTRAL_TYPE = "central";
	private static String SATELITE_TYPE = "satelite";
	private static String BR = "\n";

	private AppListener viewListener = null;
	private Database database;
	private ConfigData config;
	private Producer producer;
	private ProducerBeep producerHB;
	private Consumer consumer;
	private ConsumerBeep consumerHB;

	public Init() {
	}

	public void run(String configFile) {
		Config config = new Config(configFile, this);
		config.load();
	}

	@Override
	public void onLoad(final ConfigData config) {

		this.config = config;

		if (this.viewListener != null) {
			EventQueue.invokeLater(new Runnable() {
				@Override
				public void run() {
					Init.this.viewListener.onConfigSuccess();
				}
			});
		}
		
		DOMConfigurator.configure(config.get("log4j"));

		String info = "";
		info += "###########################" + BR;
		info += "|| DATOS DE CONFIGURACION" + BR;
		info += " Version " + config.get("version") + BR;
		info += " Tipo: " + config.get("type") + BR;
		info += " Servidor: " + config.get("server") + BR;
		info += " Puerto: " + config.get("port") + BR;
		info += " Usuario: " + config.get("user") + BR;
		info += " Clave: " + config.get("pass") + BR;
		info += " Tiempo Thread: " + config.get("threadSleepTime") + BR;
		info += "###########################" + BR;
		// System.out.print(info);
		
		logger.info(info);
	
		if (config.get("type").equals(CENTRAL_TYPE)) {
			database = new Database(config.get("Driver"),
					config.get("UrlDriver"));
			if (database.connect()) {
				if (this.viewListener != null) {
					EventQueue.invokeLater(new Runnable() {
						@Override
						public void run() {
							Init.this.viewListener.onDBSuccess();
						}
					});
				}
				this.startProducer();
				this.startConsumer();
			} else {
				logger.error("No se pudo conectar a la base de datos!");
				if (this.viewListener != null) {
					EventQueue.invokeLater(new Runnable() {
						@Override
						public void run() {
							Init.this.viewListener.onDBError();
						}
					});
				}
			}
		} else if (config.get("type").equals(SATELITE_TYPE)) {
			database = new Database(config.get("Driver"),
					config.get("UrlDriver"));
			if (database.connect()) {
				if (this.viewListener != null) {
					EventQueue.invokeLater(new Runnable() {
						@Override
						public void run() {
							Init.this.viewListener.onDBSuccess();
						}
					});
				}
				this.startProducer();
				this.startConsumer();
			} else {
				logger.error("No se pudo conectar a la base de datos!");
				if (this.viewListener != null) {
					EventQueue.invokeLater(new Runnable() {
						@Override
						public void run() {
							Init.this.viewListener.onDBError();
						}
					});
				}
			}
		} else {
			final String error = "Error en el tipo declarado, debe ser: satelite | central";
			logger.error(error);
			if (this.viewListener != null) {
				EventQueue.invokeLater(new Runnable() {
					@Override
					public void run() {
						Init.this.viewListener.onConfigError(error);
					}
				});
			}
		}
	}

	@Override
	public void onError() {
		final String error = "Error en lectura de archivo de configuracion!";
		logger.error(error);
		if (this.viewListener != null) {
			EventQueue.invokeLater(new Runnable() {
				@Override
				public void run() {
					Init.this.viewListener.onConfigError(error);
				}
			});
		}
	}

	public static void main(String[] args) throws Exception {
		if (args.length > 0) {
			Init init = new Init();
			init.run(args[0]);
		} else {
			Exception exp = new Exception(
					"Argumento no valido, debe pasar el nombre del archivo de configuracion!");
			throw exp;
		}
	}

	private void startConsumer() {
		this.consumer = new Consumer(this.config, this.database,
				this.viewListener);
		this.consumer.setConsumerListener(this);
		this.consumer.start();
		if(this.config.get("EnableHardBeep").equals("true")) {
			this.consumerHB = new ConsumerBeep(this.config, this.database,
					this.viewListener);
			this.consumerHB.setConsumerListener(this);
			this.consumerHB.start();
		}
	}

	private void startProducer() {
		this.producer = new Producer(this.config, this.database,
				this.viewListener);
		this.producer.setProducerListener(this);
		this.producer.start();
		
		if(this.config.get("EnableHardBeep").equals("true")) {
			this.producerHB = new ProducerBeep(this.config, this.database,
					this.viewListener);
			this.producerHB.setProducerListener(this);
			this.producerHB.start();
		}
	}

	@Override
	public void onProducerDisconnect() {
		this.database.restart();
		this.startProducer();
	}

	@Override
	public void onConsumerDisconnect() {
		this.database.restart();
		this.startConsumer();
	}

	@Override
	public void onExit() {
		if (this.consumer != null) {
			this.consumer.stopConsumer();
		}
		if (this.consumerHB != null) {
			this.consumerHB.stopConsumer();
		}
		if (this.producer != null) {
			this.producer.stopProducer();
		}
		if (this.producerHB != null) {
			this.producerHB.stopProducer();
		}
		System.exit(0);
	}

	@Override
	public void onRestart() {
		if (this.consumer != null) {
			this.consumer.endThread();
		}
		if (this.consumerHB != null) {
			this.consumerHB.endThread();
		}
		if (this.producer != null) {
			this.producer.endThread();
		}
		if (this.producerHB != null) {
			this.producerHB.endThread();
		}
	}


	@Override
	public void onProducerDisconnect(String type) {
		// TODO Auto-generated method stub
		
	}

	@Override
	public void onConsumerDisconnect(String type) {
		// TODO Auto-generated method stub
		
	}
}
