/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50553
Source Host           : 127.0.0.1:3306
Source Database       : test

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2018-05-21 11:39:06
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `chat_kefu`
-- ----------------------------
DROP TABLE IF EXISTS `chat_kefu`;
CREATE TABLE `chat_kefu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kefu` varchar(50) DEFAULT NULL,
  `pass` varchar(30) DEFAULT NULL,
  `clients_id` varchar(250) DEFAULT '' COMMENT '客服client_ic,多個以逗號隔開',
  `recordtime` int(11) NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未使用，1已使用',
  `truename` varchar(20) DEFAULT NULL,
  `isonline` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0為不在綫，1為在綫',
  `usernum` int(11) NOT NULL DEFAULT '0' COMMENT '對接用戶數',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of chat_kefu
-- ----------------------------
INSERT INTO `chat_kefu` VALUES ('1', '客服小姐姐', 'aa', '', '0', '0', '', '0', '2');
INSERT INTO `chat_kefu` VALUES ('2', '客服大美女', 'bb', '', '0', '0', null, '0', '8');

-- ----------------------------
-- Table structure for `chat_message`
-- ----------------------------
DROP TABLE IF EXISTS `chat_message`;
CREATE TABLE `chat_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(50) NOT NULL,
  `kefu` varchar(50) NOT NULL,
  `to` tinyint(1) DEFAULT '1' COMMENT '1為給用戶，2為給客服 發送消息',
  `msg` text NOT NULL,
  `recordtime` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `recordtime` (`recordtime`)
) ENGINE=MyISAM AUTO_INCREMENT=172 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of chat_message
-- ----------------------------
INSERT INTO `chat_message` VALUES ('170', 'l7t5mb', '客服小姐姐', '1', 'ccccccccccc', '1526873804');
INSERT INTO `chat_message` VALUES ('171', 'l7t5mb', '客服小姐姐', '2', 'vvvvvvvvvvvvvvv', '1526873808');

-- ----------------------------
-- Table structure for `chat_user`
-- ----------------------------
DROP TABLE IF EXISTS `chat_user`;
CREATE TABLE `chat_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(50) CHARACTER SET utf8 NOT NULL,
  `ip` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  `kefu` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '對接客服',
  `clients_id` varchar(250) CHARACTER SET utf8 DEFAULT '' COMMENT '客服client_ic,多個以逗號隔開',
  `recordtime` int(11) NOT NULL,
  `lastlogin` int(11) NOT NULL COMMENT '最後一次',
  `isonline` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0為不在綫，1為在綫',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`user`) USING BTREE,
  KEY `recordtime` (`recordtime`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of chat_user
-- ----------------------------
INSERT INTO `chat_user` VALUES ('42', 's2bijg', '127.0.0.1', '客服小姐姐', '', '1526871965', '1526873246', '0');
INSERT INTO `chat_user` VALUES ('43', 'l7t5mb', '127.0.0.1', '客服小姐姐', '', '1526873700', '1526873700', '0');
