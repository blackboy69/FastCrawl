
-- schema file for scraping data

-- raw data that has not been parsed. 
-- easy place to store the data.
-- DROP TABLE raw_data
CREATE TABLE raw_data (
	id INT NOT NULL  PRIMARY KEY AUTO_INCREMENT,
	type varchar(50) NOT NULL,
	parsed TINYINT NOT NULL DEFAULT 0,
	timeactionmade TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	url LONGTEXT NOT NULL ,
	html LONGTEXT  NOT NULL,
	response_headers TEXT,
	UNIQUE KEY(url(255),type),
	INDEX(type, parsed, timeactionmade)
-- ) ENGINE=MYISAM;
)ENGINE=InnoDB;

-- urls will be placed in here, then while loading the loading bit will be set, and delete from this table when done.
-- DROP TABLE load_queue
CREATE TABLE load_queue (
	id INT NOT NULL PRIMARY KEY AUTO_INCREMENT ,
	type varchar(50) NOT NULL,
	processing TINYINT NOT NULL DEFAULT 0,
	timeactionmade TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	url LONGTEXT NOT NULL ,
	post_data LONGTEXT,
	priority int NOT NULL DEFAULT 100,
	method varchar(10) NOT NULL,
	headers LONGTEXT,
	UNIQUE KEY(url(255),type),
	INDEX(type,processing,timeactionmade,priority)	
)ENGINE=InnoDB;


-- this needs to persist between wipes of the demandforce db
-- DROP DATABASE proxy;
CREATE DATABASE proxy;
USE proxy;

create table http_proxies
(
	id INT NOT NULL PRIMARY KEY AUTO_INCREMENT ,
	proxy_host varchar(100) NOT NULL UNIQUE,	
	active TINYINT NOT NULL DEFAULT 1,
	last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	quality int,
	speed int,
	private tinyint NOT NULL DEFAULT 0,
	INDEX(active,last_updated)
)ENGINE=InnoDB;

create table proxy.proxy_request_log
(
	proxy_host varchar(100) NOT NULL,
	request_domain VARCHAR(200) NOT NULL ,
	last_request TIMESTAMP  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	request_count bigint NOT NULL DEFAULT 0,
	total_request_count bigint DEFAULT 0,
	is_blocked tinyint NOT NULL DEFAULT 0,
	PRIMARY KEY(proxy_host, request_domain)
)