DROP TABLE IF EXISTS `setup`;
CREATE TABLE `setup` (
  password varchar(40) default '',
  version int unsigned default 0,
  logotext text default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

insert into setup (password) values (md5('321'));



DROP TABLE IF EXISTS `login_log`;
CREATE TABLE `login_log` (
  id int unsigned NOT NULL auto_increment,
  PRIMARY KEY(`id`),
  ip varchar(20) default '',
  count tinyint unsigned default 1,
  dtime_last timestamp default current_timestamp
) ENGINE=MyISAM DEFAULT CHARSET=utf8;




DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  id int unsigned NOT NULL auto_increment,
  PRIMARY KEY(`id`),
  name varchar(50) default '',
  galery tinyint unsigned default 0,
  place varchar(10) default '',
  txt text default NULL,
  sort smallint unsigned default 0,
  access tinyint unsigned default 1,
  dtime_add timestamp default current_timestamp
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# ALTER TABLE `pages` ADD `galery` tinyint unsigned default 0 after name;


DROP TABLE IF EXISTS `galery_catalogs`;
CREATE TABLE `galery_catalogs` (
  id int unsigned NOT NULL auto_increment,
  PRIMARY KEY(`id`),
  name varchar(50) default '',
  about text default NULL,
  image_count int unsigned default 0,
  image_access int unsigned default 0,
  cover text default NULL,
  sort smallint unsigned default 0,
  access tinyint unsigned default 0,
  dtime_add datetime default '0000-00-00 00:00:00',
  dtime_update timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;




DROP TABLE IF EXISTS `galery_images`;
CREATE TABLE `galery_images` (
  id int unsigned NOT NULL auto_increment,
  PRIMARY KEY(`id`),
  catalog_id int unsigned default 0,
  img text default NULL,
  name varchar(100) default '',
  about text default NULL,
  sort smallint unsigned default 0,
  access tinyint unsigned default 1,
  pageuse tinyint unsigned default 1,
  dtime_add timestamp default current_timestamp
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

