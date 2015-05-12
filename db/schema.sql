# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.5.34)
# Database: abrasce
# Generation Time: 2014-12-30 15:54:15 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table evento
# ------------------------------------------------------------

DROP TABLE IF EXISTS `evento`;

CREATE TABLE `evento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `descricao` text NOT NULL,
  `imagem` varchar(200) DEFAULT NULL,
  `tipo` enum('Evento','Curso') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '',
  `fullname` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL DEFAULT '',
  `roles` varchar(255) NOT NULL DEFAULT '',
  `status` enum('active','inactive') DEFAULT 'inactive',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;

INSERT INTO `user` (`id`, `username`, `fullname`, `email`, `password`, `roles`, `status`)
VALUES
	(1,'alberto','Alberto Vieira','alberto.lima@crmall.com','D+VKFeJbNFFcktBb59Y8VeZhpwLCkzo533/MdLJQ8oBPbBT7+3jjw1Aj9xET+RapnmbSOQTH4wAyf52BoscGyw==','ROLE_USER','active'),
	(2,'victor','Victor Hugo','victor.hugo@crmall.com','iOgyhdY1gJJPj7y7mMN8obgqMQZH2fLDuQuXfqZesC1Iqxo6iHxRuAA9m8E1ZUz76OIiPGTann7uJ3BNhPDoEA==','ROLE_USER','active'),
	(3,'arcostasi','Anderson Costa','anderson.costa@crmall.com','iOgyhdY1gJJPj7y7mMN8obgqMQZH2fLDuQuXfqZesC1Iqxo6iHxRuAA9m8E1ZUz76OIiPGTann7uJ3BNhPDoEA==','ROLE_USER','inactive');

/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table SHOPPING
# ------------------------------------------------------------

CREATE TABLE `shopping` (
  `id_shopping` int(11) NOT NULL,
  `fantasia` varchar(255) NOT NULL DEFAULT '',
  `logradouro` varchar(255) NOT NULL DEFAULT '',
  `numero` varchar(10) NOT NULL DEFAULT '',
  `bairro` varchar(50) NOT NULL DEFAULT '',
  `localidade` varchar(50) NOT NULL DEFAULT '',
  `cep` varchar(10) NOT NULL DEFAULT '',
  `estado` varchar(2) NOT NULL DEFAULT '',
  `telefone` varchar(15) NOT NULL DEFAULT '',
  `site` varchar(50) NOT NULL DEFAULT '',
  `area_terreno` int(11) NOT NULL DEFAULT 0,
  `area_construida` int(11) NOT NULL DEFAULT 0,
  `abl` int(11) NOT NULL DEFAULT 0,
  `perfil_a` int(3) NOT NULL DEFAULT 0,
  `perfil_b` int(3) NOT NULL DEFAULT 0,
  `perfil_c` int(3) NOT NULL DEFAULT 0,
  `pisos_lojas` int(11) NOT NULL DEFAULT 0,
  `lojas_ancoras` int(11) NOT NULL DEFAULT 0,
  `total_lojas` int(11) NOT NULL DEFAULT 0,
  `salas_cinema` int(11) NOT NULL DEFAULT 0,
  `vagas_estacionamento` int(11) NOT NULL DEFAULT 0,
  `filiacao` int(11) NOT NULL DEFAULT 0,
  `banner` varchar(64) NOT NULL DEFAULT '',
  `logo` varchar(64) NOT NULL DEFAULT ''
);

CREATE TABLE `supplier` (
  `id_supplier` int(11) NOT NULL,
  `fantasia` varchar(255) NOT NULL DEFAULT '',
  `localidade` varchar(50) NOT NULL DEFAULT '',
  `estado` varchar(2) NOT NULL DEFAULT '',
  `telefone` varchar(15) NOT NULL DEFAULT '',
  `site` varchar(50) NOT NULL DEFAULT '',
  `contato` varchar(50) NOT NULL DEFAULT '',
  `info` text,
  `logo` varchar(64) NOT NULL DEFAULT ''
);

CREATE TABLE `supplier_category` (
  `id_category` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fk_supplier` int(11) NOT NULL,
  `descricao` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `supplier_contact` (
  `id_contato` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fk_cliente` int(11) NOT NULL,
  `contato` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_entertainment`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;


CREATE TABLE `shopping_administrator` (
  `id_administrator` int(11) NOT NULL,
  `fk_shopping` int(11) NOT NULL,
  `administradora` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_administrator`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `shopping_entertainment` (
  `id_entertainment` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fk_shopping` int(11) NOT NULL,
  `descricao` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_entertainment`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;


INSERT INTO supplier_category (descricao) VALUES ('Advocacia');
INSERT INTO supplier_category (descricao) VALUES ('Arquitetura');
INSERT INTO supplier_category (descricao) VALUES ('Comunicação');
INSERT INTO supplier_category (descricao) VALUES ('Construtora e Engenharia');
INSERT INTO supplier_category (descricao) VALUES ('Consultoria');
INSERT INTO supplier_category (descricao) VALUES ('Energia');
INSERT INTO supplier_category (descricao) VALUES ('Gestão e Administração');
INSERT INTO supplier_category (descricao) VALUES ('Investimentos');
INSERT INTO supplier_category (descricao) VALUES ('Lazer e Entretenimento');
INSERT INTO supplier_category (descricao) VALUES ('Lojista');
INSERT INTO supplier_category (descricao) VALUES ('Planejamento');
INSERT INTO supplier_category (descricao) VALUES ('Produtos e Serviços');
INSERT INTO supplier_category (descricao) VALUES ('Tecnologia de Informação');


/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
