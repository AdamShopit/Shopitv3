/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table accounts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `accounts`;

CREATE TABLE `accounts` (
  `account_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_user` varchar(250) DEFAULT NULL,
  `account_pass` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `account_title` varchar(250) DEFAULT NULL,
  `account_firstname` varchar(250) DEFAULT NULL,
  `account_surname` varchar(250) DEFAULT NULL,
  `account_company` varchar(250) DEFAULT NULL,
  `account_address1` varchar(250) DEFAULT NULL,
  `account_address2` varchar(250) DEFAULT NULL,
  `account_city` varchar(250) DEFAULT NULL,
  `account_postcode` varchar(250) DEFAULT NULL,
  `account_country` varchar(250) DEFAULT NULL,
  `account_phone` varchar(250) DEFAULT NULL,
  `pref_newsletter` int(1) DEFAULT '0',
  PRIMARY KEY (`account_id`),
  UNIQUE KEY `account_user` (`account_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table api_keys
# ------------------------------------------------------------

DROP TABLE IF EXISTS `api_keys`;

CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `label` varchar(250) DEFAULT NULL,
  `key` text,
  `status` int(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table attribute_set_templates
# ------------------------------------------------------------

DROP TABLE IF EXISTS `attribute_set_templates`;

CREATE TABLE `attribute_set_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_set_id` int(11) NOT NULL DEFAULT '0',
  `attribute_name` varchar(255) DEFAULT NULL,
  `attribute_value` text,
  `attribute_order` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table attribute_sets
# ------------------------------------------------------------

DROP TABLE IF EXISTS `attribute_sets`;

CREATE TABLE `attribute_sets` (
  `attribute_set_id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_set_label` varchar(128) NOT NULL DEFAULT '',
  `attribute_set_desc` text,
  PRIMARY KEY (`attribute_set_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table attributes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `attributes`;

CREATE TABLE `attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `attribute_name` varchar(255) DEFAULT '',
  `attribute_value` text,
  `attribute_order` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  FULLTEXT KEY `attribute` (`attribute_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table basket
# ------------------------------------------------------------

DROP TABLE IF EXISTS `basket`;

CREATE TABLE `basket` (
  `basket_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` varchar(250) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `product_no` varchar(250) DEFAULT NULL,
  `product_name` varchar(250) NOT NULL,
  `product_options` varchar(250) DEFAULT NULL,
  `product_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `product_saleprice` decimal(10,2) DEFAULT NULL,
  `product_qty` int(5) DEFAULT NULL,
  `basket_date` datetime DEFAULT NULL,
  `product_weight` decimal(10,3) NOT NULL DEFAULT '0.000',
  `site` varchar(250) DEFAULT 'website',
  PRIMARY KEY (`basket_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table category
# ------------------------------------------------------------

DROP TABLE IF EXISTS `category`;

CREATE TABLE `category` (
  `cat_id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(100) NOT NULL,
  `cat_desc` text,
  `cat_excerpt` text,
  `cat_father_id` int(11) NOT NULL DEFAULT '0',
  `cat_slug` varchar(100) NOT NULL,
  `cat_meta_title` text,
  `cat_custom_heading` text,
  `cat_meta_description` text,
  `cat_meta_keywords` text,
  `cat_meta_custom` text,
  `cat_hide` int(1) DEFAULT '0',
  `cat_image` varchar(250) DEFAULT NULL,
  `cat_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cat_id`),
  KEY `cat_father_id` (`cat_father_id`),
  KEY `cat_id` (`cat_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `category` WRITE;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;

INSERT INTO `category` (`cat_id`, `cat_name`, `cat_desc`, `cat_excerpt`, `cat_father_id`, `cat_slug`, `cat_meta_title`, `cat_custom_heading`, `cat_meta_description`, `cat_meta_keywords`, `cat_meta_custom`, `cat_hide`, `cat_image`, `cat_order`)
VALUES
	(1,'Test Category',NULL,NULL,0,'test-category',NULL,NULL,NULL,NULL,NULL,0,NULL,0);

/*!40000 ALTER TABLE `category` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table collection_groups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `collection_groups`;

CREATE TABLE `collection_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_label` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table collection_items
# ------------------------------------------------------------

DROP TABLE IF EXISTS `collection_items`;

CREATE TABLE `collection_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `collection_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `collection_id` (`collection_id`) USING BTREE,
  KEY `product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table collections
# ------------------------------------------------------------

DROP TABLE IF EXISTS `collections`;

CREATE TABLE `collections` (
  `collection_id` int(11) NOT NULL AUTO_INCREMENT,
  `collection_name` varchar(100) NOT NULL DEFAULT '',
  `collection_desc` text,
  `collection_slug` varchar(100) NOT NULL,
  `collection_meta_title` varchar(250) DEFAULT NULL,
  `collection_custom_heading` text,
  `collection_meta_description` text,
  `collection_meta_keywords` text,
  `collection_meta_custom` text,
  `collection_lock` int(1) NOT NULL DEFAULT '0',
  `collection_group` int(11) DEFAULT '0',
  `collection_image` varchar(250) DEFAULT NULL,
  `collection_order` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`collection_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `collections` WRITE;
/*!40000 ALTER TABLE `collections` DISABLE KEYS */;

INSERT INTO `collections` (`collection_id`, `collection_name`, `collection_desc`, `collection_slug`, `collection_meta_title`, `collection_custom_heading`, `collection_meta_description`, `collection_meta_keywords`, `collection_meta_custom`, `collection_lock`, `collection_group`, `collection_image`, `collection_order`)
VALUES
	(1,'Featured Products',NULL,'featured-products',NULL,NULL,NULL,NULL,NULL,1,0,NULL,0),
	(2,'Popular Products',NULL,'popular-products',NULL,NULL,NULL,NULL,NULL,1,0,NULL,0);

/*!40000 ALTER TABLE `collections` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table countries
# ------------------------------------------------------------

DROP TABLE IF EXISTS `countries`;

CREATE TABLE `countries` (
  `country_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_name` varchar(25) NOT NULL,
  `is_home` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;

INSERT INTO `countries` (`country_id`, `country_name`, `is_home`)
VALUES
	(1,'United Kingdom',1);

/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table coupons
# ------------------------------------------------------------

DROP TABLE IF EXISTS `coupons`;

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(250) DEFAULT NULL,
  `code` varchar(10) DEFAULT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table coupons_codes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `coupon_codes`;

CREATE TABLE `coupon_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) DEFAULT NULL,
  `label` varchar(250) DEFAULT NULL,
  `discount` varchar(5) NOT NULL DEFAULT '0.00',
  `expires` date DEFAULT NULL,
  `max_spend` decimal(10,2) NOT NULL DEFAULT '0.00',
  `max_uses` int(11) DEFAULT '99999',
  `counter` int(11) DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table custom_field_templates
# ------------------------------------------------------------

DROP TABLE IF EXISTS `custom_field_templates`;

CREATE TABLE `custom_field_templates` (
  `custom_field_id` int(11) NOT NULL AUTO_INCREMENT,
  `custom_field_for` varchar(50) NOT NULL DEFAULT '',
  `custom_field_label` varchar(50) NOT NULL DEFAULT '',
  `custom_field_title` varchar(250) NOT NULL DEFAULT '',
  `custom_field_type` varchar(250) NOT NULL DEFAULT '',
  `custom_field_default` text,
  `template_tag` tinyint(1) DEFAULT '0',
  `variants` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`custom_field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table custom_field_values
# ------------------------------------------------------------

DROP TABLE IF EXISTS `custom_field_values`;

CREATE TABLE `custom_field_values` (
  `custom_field_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) NOT NULL DEFAULT '0',
  `custom_field_label` varchar(50) NOT NULL DEFAULT '',
  `custom_field_data` text,
  PRIMARY KEY (`custom_field_id`),
  KEY `product_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table dashboard
# ------------------------------------------------------------

DROP TABLE IF EXISTS `dashboard`;

CREATE TABLE `dashboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `widget` varchar(128) DEFAULT NULL,
  `settings` text,
  `order` int(2) NOT NULL DEFAULT '0',
  `active` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table filter_definitions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `filter_definitions`;

CREATE TABLE `filter_definitions` (
  `filter_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) DEFAULT NULL,
  `label` varchar(250) DEFAULT NULL,
  `colour` varchar(128) DEFAULT NULL,
  `filter_order` int(11) DEFAULT '0',
  PRIMARY KEY (`filter_id`),
  KEY `group_id` (`group_id`) USING BTREE,
  KEY `filter_id` (`filter_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table filter_groups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `filter_groups`;

CREATE TABLE `filter_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_id` int(11) DEFAULT NULL,
  `label` varchar(250) DEFAULT NULL,
  `type` varchar(250) DEFAULT NULL,
  `disabled` int(1) DEFAULT '0',
  `group_order` int(11) DEFAULT '0',
  PRIMARY KEY (`group_id`),
  KEY `group_id` (`group_id`) USING BTREE,
  KEY `cat_id` (`cat_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table inventory
# ------------------------------------------------------------

DROP TABLE IF EXISTS `inventory`;

CREATE TABLE `inventory` (
  `product_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `product_type` varchar(250) DEFAULT 'single',
  `cat_id` int(11) NOT NULL,
  `product_order` int(11) DEFAULT '0',
  `product_name` text,
  `product_brand` varchar(250) DEFAULT '',
  `product_brand_slug` varchar(250) DEFAULT NULL,
  `product_description` longtext,
  `product_excerpt` text,
  `product_file` text,
  `product_no` varchar(250) DEFAULT NULL,
  `product_ean` varchar(55) DEFAULT NULL,
  `product_mpn` varchar(55) DEFAULT NULL,
  `product_upc` varchar(55) DEFAULT NULL,
  `supplier_code` varchar(25) DEFAULT NULL,
  `product_costprice` decimal(10,2) DEFAULT NULL,
  `product_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `product_saleprice` decimal(10,2) DEFAULT NULL,
  `product_weight` decimal(10,3) NOT NULL DEFAULT '0.000',
  `product_slug` varchar(250) NOT NULL,
  `product_image` text,
  `product_tags` text,
  `product_views` int(11) NOT NULL DEFAULT '0',
  `product_meta_title` text,
  `product_custom_heading` text,
  `product_meta_description` text,
  `product_meta_keywords` text,
  `product_meta_custom` text,
  `product_disabled` int(1) NOT NULL DEFAULT '0',
  `product_condition` varchar(250) NOT NULL DEFAULT 'new',
  `featured` int(1) DEFAULT '0',
  `priority` int(3) NOT NULL DEFAULT '0',
  `date_added` datetime DEFAULT NULL,
  `archived` int(1) DEFAULT '0',
  `location_1` int(10) DEFAULT '0' COMMENT 'Is default product_qty',
  `channel_1` int(1) DEFAULT '1' COMMENT 'Is default sales channel (website)',
  PRIMARY KEY (`product_id`),
  KEY `cat_id` (`cat_id`),
  KEY `parent_id` (`parent_id`),
  FULLTEXT KEY `product` (`product_name`,`product_brand`,`product_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `inventory` WRITE;
/*!40000 ALTER TABLE `inventory` DISABLE KEYS */;

INSERT INTO `inventory` (`product_id`, `parent_id`, `product_type`, `cat_id`, `product_order`, `product_name`, `product_brand`, `product_brand_slug`, `product_description`, `product_excerpt`, `product_file`, `product_no`, `product_ean`, `product_mpn`, `product_upc`, `supplier_code`, `product_costprice`, `product_price`, `product_saleprice`, `product_weight`, `product_slug`, `product_image`, `product_tags`, `product_views`, `product_meta_title`, `product_custom_heading`, `product_meta_description`, `product_meta_keywords`, `product_meta_custom`, `product_disabled`, `product_condition`, `featured`, `priority`, `date_added`, `archived`, `location_1`, `channel_1`)
VALUES
	(1,0,'single',1,0,'Test Product','',NULL,NULL,NULL,NULL,'TEST001',NULL,NULL,NULL,NULL,NULL,1.99,0.00,0.000,'test-product',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,0,'new',0,0,NULL,0,50,1);

/*!40000 ALTER TABLE `inventory` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table iso_countries
# ------------------------------------------------------------

DROP TABLE IF EXISTS `iso_countries`;

CREATE TABLE `iso_countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `iso` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `country_name` varchar(80) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `iso3` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `numcode` smallint(6) DEFAULT NULL,
  `vat_exempt` int(1) DEFAULT '0',
  PRIMARY KEY (`iso`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `iso_countries` WRITE;
/*!40000 ALTER TABLE `iso_countries` DISABLE KEYS */;

INSERT INTO `iso_countries` (`id`, `iso`, `country_name`, `iso3`, `numcode`, `vat_exempt`)
VALUES
	(1,'AF','Afghanistan','AFG',4,0),
	(2,'AL','Albania','ALB',8,0),
	(3,'DZ','Algeria','DZA',12,0),
	(4,'AS','American Samoa','ASM',16,0),
	(5,'AD','Andorra','AND',20,0),
	(6,'AO','Angola','AGO',24,0),
	(7,'AI','Anguilla','AIA',660,0),
	(8,'AQ','Antarctica',NULL,NULL,0),
	(9,'AG','Antigua and Barbuda','ATG',28,0),
	(10,'AR','Argentina','ARG',32,0),
	(11,'AM','Armenia','ARM',51,0),
	(12,'AW','Aruba','ABW',533,0),
	(13,'AU','Australia','AUS',36,0),
	(14,'AT','Austria','AUT',40,0),
	(15,'AZ','Azerbaijan','AZE',31,0),
	(16,'BS','Bahamas','BHS',44,0),
	(17,'BH','Bahrain','BHR',48,0),
	(18,'BD','Bangladesh','BGD',50,0),
	(19,'BB','Barbados','BRB',52,0),
	(20,'BY','Belarus','BLR',112,0),
	(21,'BE','Belgium','BEL',56,0),
	(22,'BZ','Belize','BLZ',84,0),
	(23,'BJ','Benin','BEN',204,0),
	(24,'BM','Bermuda','BMU',60,0),
	(25,'BT','Bhutan','BTN',64,0),
	(26,'BO','Bolivia','BOL',68,0),
	(27,'BA','Bosnia and Herzegovina','BIH',70,0),
	(28,'BW','Botswana','BWA',72,0),
	(29,'BV','Bouvet Island',NULL,NULL,0),
	(30,'BR','Brazil','BRA',76,0),
	(31,'IO','British Indian Ocean Territory',NULL,NULL,0),
	(32,'BN','Brunei Darussalam','BRN',96,0),
	(33,'BG','Bulgaria','BGR',100,0),
	(34,'BF','Burkina Faso','BFA',854,0),
	(35,'BI','Burundi','BDI',108,0),
	(36,'KH','Cambodia','KHM',116,0),
	(37,'CM','Cameroon','CMR',120,0),
	(38,'CA','Canada','CAN',124,0),
	(39,'CV','Cape Verde','CPV',132,0),
	(40,'KY','Cayman Islands','CYM',136,0),
	(41,'CF','Central African Republic','CAF',140,0),
	(42,'TD','Chad','TCD',148,0),
	(43,'CL','Chile','CHL',152,0),
	(44,'CN','China','CHN',156,0),
	(45,'CX','Christmas Island',NULL,NULL,0),
	(46,'CC','Cocos (Keeling) Islands',NULL,NULL,0),
	(47,'CO','Colombia','COL',170,0),
	(48,'KM','Comoros','COM',174,0),
	(49,'CG','Congo','COG',178,0),
	(50,'CD','Congo, the Democratic Republic of the','COD',180,0),
	(51,'CK','Cook Islands','COK',184,0),
	(52,'CR','Costa Rica','CRI',188,0),
	(53,'CI','Cote D\'Ivoire','CIV',384,0),
	(54,'HR','Croatia','HRV',191,0),
	(55,'CU','Cuba','CUB',192,0),
	(56,'CY','Cyprus','CYP',196,0),
	(57,'CZ','Czech Republic','CZE',203,0),
	(58,'DK','Denmark','DNK',208,0),
	(59,'DJ','Djibouti','DJI',262,0),
	(60,'DM','Dominica','DMA',212,0),
	(61,'DO','Dominican Republic','DOM',214,0),
	(62,'EC','Ecuador','ECU',218,0),
	(63,'EG','Egypt','EGY',818,0),
	(64,'SV','El Salvador','SLV',222,0),
	(65,'GQ','Equatorial Guinea','GNQ',226,0),
	(66,'ER','Eritrea','ERI',232,0),
	(67,'EE','Estonia','EST',233,0),
	(68,'ET','Ethiopia','ETH',231,0),
	(69,'FK','Falkland Islands (Malvinas)','FLK',238,0),
	(70,'FO','Faroe Islands','FRO',234,0),
	(71,'FJ','Fiji','FJI',242,0),
	(72,'FI','Finland','FIN',246,0),
	(73,'FR','France','FRA',250,0),
	(74,'GF','French Guiana','GUF',254,0),
	(75,'PF','French Polynesia','PYF',258,0),
	(76,'TF','French Southern Territories',NULL,NULL,0),
	(77,'GA','Gabon','GAB',266,0),
	(78,'GM','Gambia','GMB',270,0),
	(79,'GE','Georgia','GEO',268,0),
	(80,'DE','Germany','DEU',276,0),
	(81,'GH','Ghana','GHA',288,0),
	(82,'GI','Gibraltar','GIB',292,0),
	(83,'GR','Greece','GRC',300,0),
	(84,'GL','Greenland','GRL',304,0),
	(85,'GD','Grenada','GRD',308,0),
	(86,'GP','Guadeloupe','GLP',312,0),
	(87,'GU','Guam','GUM',316,0),
	(88,'GT','Guatemala','GTM',320,0),
	(89,'GN','Guinea','GIN',324,0),
	(90,'GW','Guinea-Bissau','GNB',624,0),
	(91,'GY','Guyana','GUY',328,0),
	(92,'HT','Haiti','HTI',332,0),
	(93,'HM','Heard Island and Mcdonald Islands',NULL,NULL,0),
	(94,'VA','Holy See (Vatican City State)','VAT',336,0),
	(95,'HN','Honduras','HND',340,0),
	(96,'HK','Hong Kong','HKG',344,0),
	(97,'HU','Hungary','HUN',348,0),
	(98,'IS','Iceland','ISL',352,0),
	(99,'IN','India','IND',356,0),
	(100,'ID','Indonesia','IDN',360,0),
	(101,'IR','Iran, Islamic Republic of','IRN',364,0),
	(102,'IQ','Iraq','IRQ',368,0),
	(103,'IE','Ireland','IRL',372,0),
	(104,'IL','Israel','ISR',376,0),
	(105,'IT','Italy','ITA',380,0),
	(106,'JM','Jamaica','JAM',388,0),
	(107,'JP','Japan','JPN',392,0),
	(108,'JO','Jordan','JOR',400,0),
	(109,'KZ','Kazakhstan','KAZ',398,0),
	(110,'KE','Kenya','KEN',404,0),
	(111,'KI','Kiribati','KIR',296,0),
	(112,'KP','Korea, Democratic People\'s Republic of','PRK',408,0),
	(113,'KR','Korea, Republic of','KOR',410,0),
	(114,'KW','Kuwait','KWT',414,0),
	(115,'KG','Kyrgyzstan','KGZ',417,0),
	(116,'LA','Lao People\'s Democratic Republic','LAO',418,0),
	(117,'LV','Latvia','LVA',428,0),
	(118,'LB','Lebanon','LBN',422,0),
	(119,'LS','Lesotho','LSO',426,0),
	(120,'LR','Liberia','LBR',430,0),
	(121,'LY','Libyan Arab Jamahiriya','LBY',434,0),
	(122,'LI','Liechtenstein','LIE',438,0),
	(123,'LT','Lithuania','LTU',440,0),
	(124,'LU','Luxembourg','LUX',442,0),
	(125,'MO','Macao','MAC',446,0),
	(126,'MK','Macedonia, the Former Yugoslav Republic of','MKD',807,0),
	(127,'MG','Madagascar','MDG',450,0),
	(128,'MW','Malawi','MWI',454,0),
	(129,'MY','Malaysia','MYS',458,0),
	(130,'MV','Maldives','MDV',462,0),
	(131,'ML','Mali','MLI',466,0),
	(132,'MT','Malta','MLT',470,0),
	(133,'MH','Marshall Islands','MHL',584,0),
	(134,'MQ','Martinique','MTQ',474,0),
	(135,'MR','Mauritania','MRT',478,0),
	(136,'MU','Mauritius','MUS',480,0),
	(137,'YT','Mayotte',NULL,NULL,0),
	(138,'MX','Mexico','MEX',484,0),
	(139,'FM','Micronesia, Federated States of','FSM',583,0),
	(140,'MD','Moldova, Republic of','MDA',498,0),
	(141,'MC','Monaco','MCO',492,0),
	(142,'MN','Mongolia','MNG',496,0),
	(143,'MS','Montserrat','MSR',500,0),
	(144,'MA','Morocco','MAR',504,0),
	(145,'MZ','Mozambique','MOZ',508,0),
	(146,'MM','Myanmar','MMR',104,0),
	(147,'NA','Namibia','NAM',516,0),
	(148,'NR','Nauru','NRU',520,0),
	(149,'NP','Nepal','NPL',524,0),
	(150,'NL','Netherlands','NLD',528,0),
	(151,'AN','Netherlands Antilles','ANT',530,0),
	(152,'NC','New Caledonia','NCL',540,0),
	(153,'NZ','New Zealand','NZL',554,0),
	(154,'NI','Nicaragua','NIC',558,0),
	(155,'NE','Niger','NER',562,0),
	(156,'NG','Nigeria','NGA',566,0),
	(157,'NU','Niue','NIU',570,0),
	(158,'NF','Norfolk Island','NFK',574,0),
	(159,'MP','Northern Mariana Islands','MNP',580,0),
	(160,'NO','Norway','NOR',578,0),
	(161,'OM','Oman','OMN',512,0),
	(162,'PK','Pakistan','PAK',586,0),
	(163,'PW','Palau','PLW',585,0),
	(164,'PS','Palestinian Territory, Occupied',NULL,NULL,0),
	(165,'PA','Panama','PAN',591,0),
	(166,'PG','Papua New Guinea','PNG',598,0),
	(167,'PY','Paraguay','PRY',600,0),
	(168,'PE','Peru','PER',604,0),
	(169,'PH','Philippines','PHL',608,0),
	(170,'PN','Pitcairn','PCN',612,0),
	(171,'PL','Poland','POL',616,0),
	(172,'PT','Portugal','PRT',620,0),
	(173,'PR','Puerto Rico','PRI',630,0),
	(174,'QA','Qatar','QAT',634,0),
	(175,'RE','Reunion','REU',638,0),
	(176,'RO','Romania','ROM',642,0),
	(177,'RU','Russian Federation','RUS',643,0),
	(178,'RW','Rwanda','RWA',646,0),
	(179,'SH','Saint Helena','SHN',654,0),
	(180,'KN','Saint Kitts and Nevis','KNA',659,0),
	(181,'LC','Saint Lucia','LCA',662,0),
	(182,'PM','Saint Pierre and Miquelon','SPM',666,0),
	(183,'VC','Saint Vincent and the Grenadines','VCT',670,0),
	(184,'WS','Samoa','WSM',882,0),
	(185,'SM','San Marino','SMR',674,0),
	(186,'ST','Sao Tome and Principe','STP',678,0),
	(187,'SA','Saudi Arabia','SAU',682,0),
	(188,'SN','Senegal','SEN',686,0),
	(189,'RS','Serbia',NULL,NULL,0),
	(190,'SC','Seychelles','SYC',690,0),
	(191,'SL','Sierra Leone','SLE',694,0),
	(192,'SG','Singapore','SGP',702,0),
	(193,'SK','Slovakia','SVK',703,0),
	(194,'SI','Slovenia','SVN',705,0),
	(195,'SB','Solomon Islands','SLB',90,0),
	(196,'SO','Somalia','SOM',706,0),
	(197,'ZA','South Africa','ZAF',710,0),
	(198,'GS','South Georgia and the South Sandwich Islands',NULL,NULL,0),
	(199,'ES','Spain','ESP',724,0),
	(200,'LK','Sri Lanka','LKA',144,0),
	(201,'SD','Sudan','SDN',736,0),
	(202,'SR','Suriname','SUR',740,0),
	(203,'SJ','Svalbard and Jan Mayen','SJM',744,0),
	(204,'SZ','Swaziland','SWZ',748,0),
	(205,'SE','Sweden','SWE',752,0),
	(206,'CH','Switzerland','CHE',756,0),
	(207,'SY','Syrian Arab Republic','SYR',760,0),
	(208,'TW','Taiwan','TWN',158,0),
	(209,'TJ','Tajikistan','TJK',762,0),
	(210,'TZ','Tanzania, United Republic of','TZA',834,0),
	(211,'TH','Thailand','THA',764,0),
	(212,'TL','Timor-Leste',NULL,NULL,0),
	(213,'TG','Togo','TGO',768,0),
	(214,'TK','Tokelau','TKL',772,0),
	(215,'TO','Tonga','TON',776,0),
	(216,'TT','Trinidad and Tobago','TTO',780,0),
	(217,'TN','Tunisia','TUN',788,0),
	(218,'TR','Turkey','TUR',792,0),
	(219,'TM','Turkmenistan','TKM',795,0),
	(220,'TC','Turks and Caicos Islands','TCA',796,0),
	(221,'TV','Tuvalu','TUV',798,0),
	(222,'UG','Uganda','UGA',800,0),
	(223,'UA','Ukraine','UKR',804,0),
	(224,'AE','United Arab Emirates','ARE',784,0),
	(225,'GB','United Kingdom','GBR',826,0),
	(226,'US','United States','USA',840,0),
	(227,'UM','United States Minor Outlying Islands',NULL,NULL,0),
	(228,'UY','Uruguay','URY',858,0),
	(229,'UZ','Uzbekistan','UZB',860,0),
	(230,'VU','Vanuatu','VUT',548,0),
	(231,'VE','Venezuela','VEN',862,0),
	(232,'VN','Viet Nam','VNM',704,0),
	(233,'VG','Virgin Islands, British','VGB',92,0),
	(234,'VI','Virgin Islands, U.s.','VIR',850,0),
	(235,'WF','Wallis and Futuna','WLF',876,0),
	(236,'EH','Western Sahara','ESH',732,0),
	(237,'YE','Yemen','YEM',887,0),
	(238,'ZM','Zambia','ZMB',894,0),
	(239,'ZW','Zimbabwe','ZWE',716,0),
	(240,'ME','Montenegro',NULL,NULL,0),
	(241,'XX','Rest of World',NULL,NULL,0);

/*!40000 ALTER TABLE `iso_countries` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table locations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `locations`;

CREATE TABLE `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT NULL,
  `shortname` varchar(250) DEFAULT NULL,
  `note` varchar(250) DEFAULT NULL,
  `type` varchar(250) DEFAULT NULL,
  `locked` int(1) DEFAULT '0',
  `use_global_stock` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `locations` WRITE;
/*!40000 ALTER TABLE `locations` DISABLE KEYS */;

INSERT INTO `locations` (`id`, `name`, `shortname`, `note`, `type`, `locked`, `use_global_stock`)
VALUES
	(1,'Website','website','Default location (global stock)','default',1,0);

/*!40000 ALTER TABLE `locations` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table order_notes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `order_notes`;

CREATE TABLE `order_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0',
  `author` varchar(128) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `note` text,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_notifications
# ------------------------------------------------------------

DROP TABLE IF EXISTS `order_notifications`;

CREATE TABLE `order_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status_id` int(11) DEFAULT '0',
  `subject` varchar(250) DEFAULT NULL,
  `body` text,
  `enabled` int(1) DEFAULT '1',
  `note` varchar(250) DEFAULT NULL,
  `customer` int(1) DEFAULT '0',
  `admin` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `order_notifications` WRITE;
/*!40000 ALTER TABLE `order_notifications` DISABLE KEYS */;

INSERT INTO `order_notifications` (`id`, `status_id`, `subject`, `body`, `enabled`, `note`, `customer`, `admin`)
VALUES
	(1,6,'Your item has dispatched -  we thought you\'d like to know!','<p style=\"font-family:Arial;font-size:14px;\">Dear {billing_firstname}</p>\n\n<p style=\"font-family:Arial;font-size:14px;\"><strong>Order No: {order_ref}</strong></p>\n\n<p style=\"font-family:Arial;font-size:14px;\">We are pleased to confirm your recent order has now been dispatched and will be with you shortly.</p>\n\n<ul>\n{items}\n	 <li>{product_qty} x {product_name} @ {product_price} = {linetotal}</li>\n{/items}\n</ul>\n \n<p style=\"font-family:Arial;font-size:14px;\">Thanks for shopping at {store_name}!</p>',1,NULL,1,0),
	(2,5,'Thank you for your order','<h2>Thank you for your order, {billing_firstname}</h2>\n\n<p>Thank you for purchasing from {store_name}. Please save this email as a receipt of your transaction.</p>\n\n<h3>Order Details</h3>\n\n<p><strong>Order Ref:</strong> {order_ref}</p>\n<p><strong>Order Date:</strong> {order_date}</p>\n\n<table cellpadding=\"0\" cellspacing=\"3\" border=\"0\" width=\"100%\">\n	<thead>\n		<tr>\n			<td width=\"50%\"><h3>Billing Address</h3></td>\n			<td width=\"50%\"><h3>Delivery Address</h3></td>\n		</tr>\n	</thead>\n\n	<tbody>\n		<tr>\n			<td valign=\"top\">\n				{billing_title} {billing_firstname} {billing_surname}<br/>\n	 	 	 	{billing_company}<br/>\n				{billing_address1}<br/>\n				{billing_address2}<br/>\n				{billing_city}<br/>\n				{billing_postcode}<br/>\n				{billing_country}<br/><br/>\n				<strong>Email:</strong> {customer_email}<br/>\n				<strong>Phone:</strong> {customer_phone}<br/>\n			</td>\n			<td valign=\"top\">\n				{delivery_title} {delivery_firstname} {delivery_surname}<br/>\n				{delivery_company}<br/>\n				{delivery_address1}<br/>\n				{delivery_address2}<br/>\n				{delivery_city}<br/>\n				{delivery_postcode}<br/>\n				{delivery_country}<br/>\n			</td>\n		</tr>\n	</tbody>\n</table>\n\n<hr/>\n\n<table cellpadding=\"0\" cellspacing=\"3\" border=\"0\" width=\"100%\">\n	<thead>\n		<tr>\n			<td colspan=\"5\"><h3>Order Inventory</h3></td>\n		</tr>\n	</thead>\n	\n	<tbody>\n		<tr>\n			<th width=\"15%\" align=\"left\">Product No</th>\n			<th width=\"40%\" align=\"left\">Product</th>\n			<th width=\"15%\" align=\"left\">Qty</th>\n			<th width=\"15%\" align=\"left\">Price</th>\n			<th width=\"15%\" align=\"left\">Total</th>\n		</tr>\n		{items}\n		<tr class=\"product\">\n			<td>{product_no}</td>\n			<td>{product_name}<br/><small class=\"lightgrey\">{product_options}</small></td>\n			<td>{product_qty}</td>\n			<td>{currency}{product_price}</td>\n			<td>{currency}{linetotal}</td>\n		</tr>\n		{/items}\n		<tr>\n			<td>&nbsp;</td>\n			<td>&nbsp;</td>\n			<td>&nbsp;</td>\n			<td>Discount:</td>\n			<td>{currency}{order_discount}</td>\n		</tr>		\n		\n		<tr>\n			<td>&nbsp;</td>\n			<td>&nbsp;</td>\n			<td>&nbsp;</td>\n			<td>Shipping:</td>\n			<td>{currency}{order_shipping}</td>\n		</tr>\n\n		<tr id=\"vat_normal\">\n			<td>&nbsp;</td>\n			<td>&nbsp;</td>\n			<td>&nbsp;</td>\n			<td>VAT:</td>\n			<td>{currency}{order_vat}</td>\n		</tr>\n\n		<tr>\n			<td>&nbsp;</td>\n			<td>&nbsp;</td>\n			<td>&nbsp;</td>\n			<td><strong>Total:</strong></td>\n			<td><strong>{currency}{total}</strong></td>\n		</tr>\n			\n	</tbody>\n	\n</table>\n\n<h3 align=\"center\"><strong>Thank you for shopping with us!</strong></h3>\n<p>\n	<small>\n	<strong>Return address:</strong><br/>\n	{store_name},\n	{company_address}<br/>\n	Tel: {company_tel} | Fax: {company_fax} | Email: {company_email}\n	<br/>\n	Website: \n	</small>\n</p>\n\n<p><small>{company_name}. {company_reg}</small></p>\n\n<div id=\"instructions\">\n	<h3>Additional Instructions</h3>\n	<p>{instructions}</p>\n</div>',1,NULL,1,0),
	(3,5,'New Order Alert','<h2>You have received a new order!</h2>\n\n<p>Order Date: {order_date}</p>\n\n<p>Customer Name: {billing_title} {billing_firstname} {billing_surname}</p>\n\n<p>Order Ref: <strong>{order_ref}</strong></p>\n\n<p>Order Total: <strong>{currency}{total}</strong></p>\n\n<p>Login to your admin to view this order.</p>',1,NULL,0,1);

/*!40000 ALTER TABLE `order_notifications` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table order_statuses
# ------------------------------------------------------------

DROP TABLE IF EXISTS `order_statuses`;

CREATE TABLE `order_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(250) DEFAULT NULL,
  `value` varchar(250) DEFAULT NULL,
  `type` int(11) DEFAULT '0' COMMENT 'Is either 0/1/2 (original status id)',
  `color` varchar(7) DEFAULT NULL,
  `position` int(2) DEFAULT '0',
  `locked` int(1) DEFAULT '0',
  `flow` int(1) DEFAULT '0' COMMENT 'Used for the flow chart on orders view page',
  `flag_unprocessed` int(1) DEFAULT '0',
  `flag_completed` int(1) DEFAULT '0',
  `flag_refunded` int(1) DEFAULT '0',
  `flag_cancelled` int(1) DEFAULT '0',
  `flag_failed` int(1) DEFAULT '0',
  `flag_dispatched` int(1) DEFAULT '0',
  `flag_pending` int(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `order_statuses` WRITE;
/*!40000 ALTER TABLE `order_statuses` DISABLE KEYS */;

INSERT INTO `order_statuses` (`id`, `label`, `value`, `type`, `color`, `position`, `locked`, `flow`, `flag_unprocessed`, `flag_completed`, `flag_refunded`, `flag_cancelled`, `flag_failed`, `flag_dispatched`, `flag_pending`)
VALUES
	(1,'Unprocessed',NULL,0,'#d7d7d7',1,1,1,1,0,0,0,0,0,0),
	(2,'Refunded','Refunded',1,'#ff5454',4,1,0,0,0,1,0,0,0,0),
	(3,'Cancelled','Cancelled',1,'#ff5454',2,1,0,0,0,0,1,0,0,0),
	(4,'Pending','Payment Pending',2,'#f7941d',2,1,0,0,0,0,0,0,0,1),
	(5,'Paid','Completed',2,'#7fbfbf',5,1,1,0,1,0,0,0,0,0),
	(6,'Dispatched','Dispatched',2,'#83bf42',7,1,1,0,0,0,0,0,1,0),
	(7,'Failed','Payment Failed',1,'#ff5454',3,1,0,0,0,0,0,1,0,0);

/*!40000 ALTER TABLE `order_statuses` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table orders
# ------------------------------------------------------------

DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_ref` varchar(250) DEFAULT NULL,
  `session_id` text NOT NULL,
  `order_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `account_id` int(11) DEFAULT NULL,
  `billing_title` varchar(250) DEFAULT NULL,
  `billing_firstname` varchar(250) NOT NULL,
  `billing_surname` varchar(250) NOT NULL,
  `billing_company` varchar(250) NOT NULL,
  `billing_address1` varchar(250) NOT NULL,
  `billing_address2` varchar(250) DEFAULT NULL,
  `billing_city` varchar(250) NOT NULL,
  `billing_postcode` varchar(250) NOT NULL,
  `billing_country` varchar(250) NOT NULL DEFAULT '',
  `delivery_title` varchar(250) DEFAULT '',
  `delivery_firstname` varchar(250) NOT NULL,
  `delivery_surname` varchar(250) NOT NULL,
  `delivery_company` varchar(250) NOT NULL,
  `delivery_address1` varchar(250) NOT NULL,
  `delivery_address2` varchar(250) DEFAULT NULL,
  `delivery_city` varchar(250) NOT NULL DEFAULT '',
  `delivery_postcode` varchar(250) NOT NULL,
  `delivery_country` varchar(250) NOT NULL,
  `customer_email` varchar(250) NOT NULL DEFAULT '',
  `customer_phone` varchar(250) DEFAULT NULL,
  `inventory` text NOT NULL,
  `order_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `order_vat` decimal(10,2) NOT NULL DEFAULT '0.00',
  `order_shipping` decimal(10,2) NOT NULL DEFAULT '0.00',
  `order_discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `shipping_method` varchar(250) DEFAULT NULL,
  `order_status` text,
  `order_status_id` int(1) NOT NULL DEFAULT '0',
  `dispatch_date` date DEFAULT NULL,
  `dispatch_email` date DEFAULT NULL,
  `transaction_type` varchar(250) DEFAULT NULL,
  `transaction_id` text,
  `transaction_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `transaction_data` text,
  `instructions` text,
  `site` varchar(128) NOT NULL DEFAULT 'website',
  `vat_rate` decimal(10,3) NOT NULL DEFAULT '0.000',
  `refund` int(1) DEFAULT '0',
  `pref_newsletter` int(1) DEFAULT '0',
  PRIMARY KEY (`order_id`),
  KEY `billing_firstname` (`billing_firstname`) USING BTREE,
  KEY `billing_surname` (`billing_surname`) USING BTREE,
  KEY `billing_city` (`billing_city`) USING BTREE,
  KEY `customer_email` (`customer_email`) USING BTREE,
  KEY `billing_address1` (`billing_address1`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `order_ref` (`order_ref`) USING BTREE,
  KEY `order_status_id` (`order_status_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table orders_inventory
# ------------------------------------------------------------

DROP TABLE IF EXISTS `orders_inventory`;

CREATE TABLE `orders_inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT '0',
  `product_id` int(11) DEFAULT '0',
  `product_no` varchar(250) DEFAULT NULL,
  `product_name` varchar(250) DEFAULT NULL,
  `product_qty` int(11) DEFAULT '0',
  `product_price` decimal(10,2) DEFAULT '0.00',
  `product_options` text,
  `free_qty` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table pages
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pages`;

CREATE TABLE `pages` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `page_name` varchar(255) NOT NULL DEFAULT '',
  `page_content` text,
  `page_order` int(11) NOT NULL DEFAULT '0',
  `page_meta_title` varchar(255) DEFAULT NULL,
  `page_custom_heading` text,
  `page_meta_description` text,
  `page_meta_keywords` text,
  `page_meta_custom` text,
  `page_slug` varchar(255) NOT NULL,
  `page_visible` int(1) NOT NULL DEFAULT '1',
  `page_redirect` text,
  `page_sitemap` int(1) NOT NULL DEFAULT '1',
  `page_template` varchar(255) DEFAULT NULL,
  `page_lock` int(1) NOT NULL DEFAULT '0',
  `page_type` varchar(50) NOT NULL DEFAULT 'page',
  `page_date` datetime DEFAULT NULL,
  `page_author` varchar(128) DEFAULT NULL,
  `site` varchar(250) DEFAULT 'website',
  PRIMARY KEY (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;

INSERT INTO `pages` (`page_id`, `page_name`, `page_content`, `page_order`, `page_meta_title`, `page_custom_heading`, `page_meta_description`, `page_meta_keywords`, `page_meta_custom`, `page_slug`, `page_visible`, `page_redirect`, `page_sitemap`, `page_template`, `page_lock`, `page_type`, `page_date`, `page_author`, `site`)
VALUES
	(1,'Home','<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>\n<p>Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has <strong>survived</strong> not only five centuries, but also the leap into electronic typesetting, remaining essentially <strong>unchanged</strong>.</p>',0,'Welcome to our store!',NULL,'This is test meta description for homepage','This is test meta keywords for homepage',NULL,'home',1,NULL,1,NULL,1,'page',NULL,NULL,'website'),
	(2,'Terms and Conditions','<p>Content goes here</p>',1,'Terms & Conditions',NULL,'','',NULL,'terms',1,NULL,1,NULL,1,'page',NULL,NULL,'website');

/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table product_options
# ------------------------------------------------------------

DROP TABLE IF EXISTS `product_options`;

CREATE TABLE `product_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `option_label` varchar(255) DEFAULT NULL,
  `option_criteria` varchar(255) DEFAULT NULL,
  `option_price` decimal(10,2) DEFAULT '0.00',
  `option_order` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  FULLTEXT KEY `product_option` (`option_label`,`option_criteria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table productoption_set_templates
# ------------------------------------------------------------

DROP TABLE IF EXISTS `productoption_set_templates`;

CREATE TABLE `productoption_set_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option_set_id` int(11) NOT NULL DEFAULT '0',
  `option_label` varchar(255) DEFAULT NULL,
  `option_criteria` varchar(255) DEFAULT NULL,
  `option_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `option_order` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table productoption_sets
# ------------------------------------------------------------

DROP TABLE IF EXISTS `productoption_sets`;

CREATE TABLE `productoption_sets` (
  `option_set_id` int(11) NOT NULL AUTO_INCREMENT,
  `option_set_label` varchar(128) NOT NULL DEFAULT '',
  `option_set_desc` text,
  PRIMARY KEY (`option_set_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table redirection
# ------------------------------------------------------------

DROP TABLE IF EXISTS `redirection`;

CREATE TABLE `redirection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `old_url` varchar(255) NOT NULL DEFAULT '' COMMENT '	',
  `new_url` varchar(255) NOT NULL DEFAULT '',
  `status_code` int(3) NOT NULL DEFAULT '301',
  PRIMARY KEY (`id`),
  KEY `old_url` (`old_url`) USING BTREE,
  KEY `new_url` (`new_url`) USING BTREE,
  FULLTEXT KEY `paths` (`old_url`,`new_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table searches
# ------------------------------------------------------------

DROP TABLE IF EXISTS `searches`;

CREATE TABLE `searches` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `term` varchar(255) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `term` (`term`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table shipping
# ------------------------------------------------------------

DROP TABLE IF EXISTS `shipping`;

CREATE TABLE `shipping` (
  `rule_id` int(11) NOT NULL AUTO_INCREMENT,
  `country` varchar(250) NOT NULL DEFAULT '',
  `criteria` varchar(25) NOT NULL,
  `operation` varchar(25) NOT NULL,
  `value` decimal(10,3) NOT NULL DEFAULT '0.000',
  `value2` decimal(10,3) NOT NULL DEFAULT '0.000',
  `shipping` decimal(10,2) NOT NULL DEFAULT '0.00',
  `rule_name` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table snippet_groups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `snippet_groups`;

CREATE TABLE `snippet_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table snippets
# ------------------------------------------------------------

DROP TABLE IF EXISTS `snippets`;

CREATE TABLE `snippets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) DEFAULT NULL,
  `label` varchar(128) DEFAULT NULL,
  `content` text,
  `notes` text,
  `group_id` int(11) DEFAULT '0',
  `early_parsing` int(1) DEFAULT '0',
  `widget` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table templates
# ------------------------------------------------------------

DROP TABLE IF EXISTS `templates`;

CREATE TABLE `templates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(250) DEFAULT NULL,
  `content` mediumblob,
  `site` varchar(250) DEFAULT 'website',
  `type` varchar(250) DEFAULT 'packing',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `templates` WRITE;
/*!40000 ALTER TABLE `templates` DISABLE KEYS */;

INSERT INTO `templates` (`id`, `title`, `content`, `site`, `type`)
VALUES
	(1,'Packing slip',X'3C68333E4F726465722044657461696C733C2F68333E0A0A0909093C703E3C7374726F6E673E4F72646572205265663A3C2F7374726F6E673E207B6F726465725F7265667D3C2F703E0A0909093C703E3C7374726F6E673E4F7264657220446174653A3C2F7374726F6E673E207B6F726465725F646174657D3C2F703E0A0909090A0909093C7461626C652063656C6C70616464696E673D2230222063656C6C73706163696E673D22332220626F726465723D2230222077696474683D2231303025223E0A090909093C74686561643E0A09090909093C74723E0A0909090909093C74642077696474683D22353025223E3C68333E42696C6C696E6720416464726573733C2F68333E3C2F74643E0A0909090909093C74642077696474683D22353025223E3C68333E44656C697665727920416464726573733C2F68333E3C2F74643E0A09090909093C2F74723E0A090909093C2F74686561643E0A0909090A090909093C74626F64793E0A09090909093C74723E0A0909090909093C74642076616C69676E3D22746F70223E0A090909090909097B62696C6C696E675F7469746C657D207B62696C6C696E675F66697273746E616D657D207B62696C6C696E675F7375726E616D657D3C62722F3E0A090909092009200920097B62696C6C696E675F636F6D70616E797D3C62722F3E0A090909090909097B62696C6C696E675F61646472657373317D3C62722F3E0A090909090909097B62696C6C696E675F61646472657373327D3C62722F3E0A090909090909097B62696C6C696E675F636974797D3C62722F3E0A090909090909097B62696C6C696E675F706F7374636F64657D3C62722F3E0A090909090909097B62696C6C696E675F636F756E7472797D3C62722F3E3C62722F3E0A090909090909093C7374726F6E673E456D61696C3A3C2F7374726F6E673E207B637573746F6D65725F656D61696C7D3C62722F3E0A090909090909093C7374726F6E673E50686F6E653A3C2F7374726F6E673E207B637573746F6D65725F70686F6E657D3C62722F3E0A0909090909093C2F74643E0A0909090909093C74642076616C69676E3D22746F70223E0A090909090909097B64656C69766572795F7469746C657D207B64656C69766572795F66697273746E616D657D207B64656C69766572795F7375726E616D657D3C62722F3E0A090909090909097B64656C69766572795F636F6D70616E797D3C62722F3E0A090909090909097B64656C69766572795F61646472657373317D3C62722F3E0A090909090909097B64656C69766572795F61646472657373327D3C62722F3E0A090909090909097B64656C69766572795F636974797D3C62722F3E0A090909090909097B64656C69766572795F706F7374636F64657D3C62722F3E0A090909090909097B64656C69766572795F636F756E7472797D3C62722F3E0A0909090909093C2F74643E0A09090909093C2F74723E0A090909093C2F74626F64793E0A0909093C2F7461626C653E0A0909090A0909093C68722F3E0A0909090A0909093C7461626C652063656C6C70616464696E673D2230222063656C6C73706163696E673D22332220626F726465723D2230222077696474683D2231303025223E0A090909093C74686561643E0A09090909093C74723E0A0909090909093C746420636F6C7370616E3D2235223E3C68333E4F7264657220496E76656E746F72793C2F68333E3C2F74643E0A09090909093C2F74723E0A090909093C2F74686561643E0A090909090A090909093C74626F64793E0A09090909093C74723E0A0909090909093C74682077696474683D223135252220616C69676E3D226C656674223E50726F64756374204E6F3C2F74683E0A0909090909093C74682077696474683D223430252220616C69676E3D226C656674223E50726F647563743C2F74683E0A0909090909093C74682077696474683D223135252220616C69676E3D226C656674223E5174793C2F74683E0A0909090909093C74682077696474683D223135252220616C69676E3D226C656674223E50726963653C2F74683E0A0909090909093C74682077696474683D223135252220616C69676E3D226C656674223E546F74616C3C2F74683E0A09090909093C2F74723E0A09090909097B6974656D737D0A09090909093C747220636C6173733D2270726F64756374223E0A0909090909093C74643E7B70726F647563745F6E6F7D3C2F74643E0A0909090909093C74643E7B70726F647563745F6E616D657D3C62722F3E3C736D616C6C20636C6173733D226C6967687467726579223E7B70726F647563745F6F7074696F6E737D3C2F736D616C6C3E3C2F74643E0A0909090909093C74643E7B70726F647563745F7174797D3C2F74643E0A0909090909093C74643E7B63757272656E63797D7B70726F647563745F70726963657D3C2F74643E0A0909090909093C74643E7B63757272656E63797D7B6C696E65746F74616C7D3C2F74643E0A09090909093C2F74723E0A09090909097B2F6974656D737D0A09090909093C74723E0A0909090909093C74643E266E6273703B3C2F74643E0A0909090909093C74643E266E6273703B3C2F74643E0A0909090909093C74643E266E6273703B3C2F74643E0A0909090909093C74643E446973636F756E743A3C2F74643E0A0909090909093C74643E7B63757272656E63797D7B6F726465725F646973636F756E747D3C2F74643E0A09090909093C2F74723E09090A09090909090A09090909093C74723E0A0909090909093C74643E266E6273703B3C2F74643E0A0909090909093C74643E266E6273703B3C2F74643E0A0909090909093C74643E266E6273703B3C2F74643E0A0909090909093C74643E5368697070696E673A3C2F74643E0A0909090909093C74643E7B63757272656E63797D7B6F726465725F7368697070696E677D3C2F74643E0A09090909093C2F74723E0A0909090A09090909093C74722069643D227661745F6E6F726D616C223E0A0909090909093C74643E266E6273703B3C2F74643E0A0909090909093C74643E266E6273703B3C2F74643E0A0909090909093C74643E266E6273703B3C2F74643E0A0909090909093C74643E5641543A3C2F74643E0A0909090909093C74643E7B63757272656E63797D7B6F726465725F7661747D3C2F74643E0A09090909093C2F74723E0A0909090A09090909093C74723E0A0909090909093C74643E266E6273703B3C2F74643E0A0909090909093C74643E266E6273703B3C2F74643E0A0909090909093C74643E266E6273703B3C2F74643E0A0909090909093C74643E3C7374726F6E673E546F74616C3A3C2F7374726F6E673E3C2F74643E0A0909090909093C74643E3C7374726F6E673E7B63757272656E63797D7B746F74616C7D3C2F7374726F6E673E3C2F74643E0A09090909093C2F74723E0A0909090909090A090909093C2F74626F64793E0A090909090A0909093C2F7461626C653E0A0909090A0909093C683320616C69676E3D2263656E746572223E3C7374726F6E673E5468616E6B20796F7520666F722073686F7070696E672077697468207573213C2F7374726F6E673E3C2F68333E0A0909093C703E0A090909093C736D616C6C3E0A090909093C7374726F6E673E52657475726E20616464726573733A3C2F7374726F6E673E3C62722F3E0A090909097B73746F72655F6E616D657D2C0A090909097B636F6D70616E795F616464726573737D3C62722F3E0A0909090954656C3A207B636F6D70616E795F74656C7D207C204661783A207B636F6D70616E795F6661787D207C20456D61696C3A207B636F6D70616E795F656D61696C7D0A090909093C62722F3E0A09090909576562736974653A2064756262656463726561746976652E636F6D0A090909093C2F736D616C6C3E0A0909093C2F703E0A0909090A0909093C703E3C736D616C6C3E7B636F6D70616E795F6E616D657D2E207B636F6D70616E795F7265677D3C2F736D616C6C3E3C2F703E0A0909090A0909093C6469762069643D22696E737472756374696F6E73223E0A090909093C68333E4164646974696F6E616C20496E737472756374696F6E733C2F68333E0A090909093C703E7B696E737472756374696F6E737D3C2F703E0A0909093C2F6469763E','website','packing');

/*!40000 ALTER TABLE `templates` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table version_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `version_log`;

CREATE TABLE `version_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(20) DEFAULT '',
  `message` varchar(250) DEFAULT '',
  `timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table xcat
# ------------------------------------------------------------

DROP TABLE IF EXISTS `xcat`;

CREATE TABLE `xcat` (
  `xcat_id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_slug` text NOT NULL,
  PRIMARY KEY (`xcat_id`),
  KEY `cat_id` (`cat_id`),
  KEY `product_id` (`product_id`),
  KEY `xcat_id` (`xcat_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table xitem_groups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `xitem_groups`;

CREATE TABLE `xitem_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(250) DEFAULT '',
  `type` varchar(30) DEFAULT '',
  `group_order` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table xitems
# ------------------------------------------------------------

DROP TABLE IF EXISTS `xitems`;

CREATE TABLE `xitems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `xitem_id` int(11) NOT NULL DEFAULT '0',
  `type` varchar(30) NOT NULL DEFAULT 'R',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `xitem_id` (`xitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
