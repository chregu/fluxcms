BEGIN TRANSACTION;
create table address_address (
	parenturi varchar(200),
	name varchar(200),
	street varchar(200),
	city varchar(200),
	email varchar(200)
);
INSERT INTO address_address VALUES('/zurich/','Christian Stocker',NULL,'Zurich',NULL);
create table address_collection ( uri varchar(200) unique primary key, parenturi varchar(200) );
INSERT INTO address_collection VALUES('/zurich/','/');
CREATE TABLE locks (
  token varchar(255) NOT NULL default '',
  path varchar(200) NOT NULL default '',
  expires int(11) NOT NULL default '0',
  owner varchar(200) default NULL,
  recursive int(11) default '0',
  writelock int(11) default '0',
  exclusivelock int(11) NOT NULL default '0'
 
);
CREATE TABLE properties (
  path varchar(255) NOT NULL default '',
  name varchar(120) NOT NULL default '',
  ns varchar(120) NOT NULL default 'DAV:',
  value text);
INSERT INTO properties VALUES('/index.xhtml','mimetype','bx:','text/html');
INSERT INTO properties VALUES('/index.xhtml','output-mimetype','bx:','text/html');
INSERT INTO properties VALUES('/index.xhtml','parent-uri','bx:','/');
INSERT INTO properties VALUES('/la.wiki','mimetype','bx:','text/wiki');
INSERT INTO properties VALUES('/la.wiki','output-mimetype','bx:','text/html');
INSERT INTO properties VALUES('/la.wiki','parent-uri','bx:','/');
INSERT INTO properties VALUES('/','xslt','bx-pipeline:','static.xsl');
INSERT INTO properties VALUES('/portraet/','mimetype','bx:','httpd/unix-directory');
INSERT INTO properties VALUES('/portraet/','output-mimetype','bx:','httpd/unix-directory');
INSERT INTO properties VALUES('/portraet/','parent-uri','bx:','/');
INSERT INTO properties VALUES('/portraet/index.xhtml','mimetype','bx:','text/html');
INSERT INTO properties VALUES('/portraet/index.xhtml','output-mimetype','bx:','text/html');
INSERT INTO properties VALUES('/portraet/index.xhtml','parent-uri','bx:','/portraet/');
INSERT INTO properties VALUES('/aktuell/','mimetype','bx:','httpd/unix-directory');
INSERT INTO properties VALUES('/aktuell/','output-mimetype','bx:','httpd/unix-directory');
INSERT INTO properties VALUES('/aktuell/','parent-uri','bx:','/');
INSERT INTO properties VALUES('/projekte/','mimetype','bx:','httpd/unix-directory');
INSERT INTO properties VALUES('/projekte/','output-mimetype','bx:','httpd/unix-directory');
INSERT INTO properties VALUES('/projekte/','parent-uri','bx:','/');
INSERT INTO properties VALUES('/spenden/','mimetype','bx:','httpd/unix-directory');
INSERT INTO properties VALUES('/spenden/','output-mimetype','bx:','httpd/unix-directory');
INSERT INTO properties VALUES('/spenden/','parent-uri','bx:','/');
INSERT INTO properties VALUES('/medien/','mimetype','bx:','httpd/unix-directory');
INSERT INTO properties VALUES('/medien/','output-mimetype','bx:','httpd/unix-directory');
INSERT INTO properties VALUES('/medien/','parent-uri','bx:','/');
INSERT INTO properties VALUES('/aktuell/index.xhtml','mimetype','bx:','text/html');
INSERT INTO properties VALUES('/aktuell/index.xhtml','output-mimetype','bx:','text/html');
INSERT INTO properties VALUES('/aktuell/index.xhtml','parent-uri','bx:','/aktuell/');
INSERT INTO properties VALUES('/aktuell/','xslt','bx-pipeline:','dreispalt.xsl');
INSERT INTO properties VALUES('/aktuell/','displayname','bx:','Aktuell');
INSERT INTO properties VALUES('/images/','mimetype','bx:','httpd/unix-directory');
INSERT INTO properties VALUES('/images/','output-mimetype','bx:','httpd/unix-directory');
INSERT INTO properties VALUES('/images/','parent-uri','bx:','/');
INSERT INTO properties VALUES('/portraet','navi','bx:',1);
INSERT INTO properties VALUES('/aktuell','navi','bx:',1);
INSERT INTO properties VALUES('/projekte','navi','bx:',1);
INSERT INTO properties VALUES('/spenden','navi','bx:',1);
INSERT INTO properties VALUES('/medien','navi','bx:',1);
INSERT INTO properties VALUES('/images','navi','bx:',0);
INSERT INTO properties VALUES('/images/','navi','bx:',0);
INSERT INTO properties VALUES('/projekte','displayname','bx:','Projekte');
INSERT INTO properties VALUES('/projekte/','displayname','bx:','Projekte');
INSERT INTO properties VALUES('/portraet/','displayname','bx:','Portraet');
INSERT INTO properties VALUES('/medien/index.xhtml','mimetype','bx:','text/html');
INSERT INTO properties VALUES('/medien/index.xhtml','output-mimetype','bx:','text/html');
INSERT INTO properties VALUES('/medien/index.xhtml','parent-uri','bx:','/medien/');
INSERT INTO properties VALUES('/projekte/index.xhtml','mimetype','bx:','text/html');
INSERT INTO properties VALUES('/projekte/index.xhtml','output-mimetype','bx:','text/html');
INSERT INTO properties VALUES('/projekte/index.xhtml','parent-uri','bx:','/projekte/');
INSERT INTO properties VALUES('/spenden/index.xhtml','mimetype','bx:','text/html');
INSERT INTO properties VALUES('/spenden/index.xhtml','output-mimetype','bx:','text/html');
INSERT INTO properties VALUES('/spenden/index.xhtml','parent-uri','bx:','/spenden/');
INSERT INTO properties VALUES('/spenden/','xslt','bx-pipeline:','dreispalt.xsl');
INSERT INTO properties VALUES('/portraet/','xslt','bx-pipeline:','dreispalt.xsl');
INSERT INTO properties VALUES('/projekte/','xslt','bx-pipeline:','dreispalt.xsl');
INSERT INTO properties VALUES('/medien/','xslt','bx-pipeline:','dreispalt.xsl');
INSERT INTO properties VALUES('/kontakt/','mimetype','bx:','httpd/unix-directory');
INSERT INTO properties VALUES('/kontakt/','output-mimetype','bx:','httpd/unix-directory');
INSERT INTO properties VALUES('/kontakt/','parent-uri','bx:','/');
INSERT INTO properties VALUES('/kontakt/index.xhtml','mimetype','bx:','text/html');
INSERT INTO properties VALUES('/kontakt/index.xhtml','output-mimetype','bx:','text/html');
INSERT INTO properties VALUES('/kontakt/index.xhtml','parent-uri','bx:','/kontakt/');
INSERT INTO properties VALUES('/kontakt/','xslt','bx-pipeline:','dreispalt.xsl');
INSERT INTO properties VALUES('/kontakt/thankyou.xhtml','mimetype','bx:','text/html');
INSERT INTO properties VALUES('/kontakt/thankyou.xhtml','output-mimetype','bx:','text/html');
INSERT INTO properties VALUES('/kontakt/thankyou.xhtml','parent-uri','bx:','/kontakt/');
create  index expires on locks (expires);
create  index path on properties (path);
create  index path_2 on locks (path);
create  index path_3 on locks (path,token);
create unique index prim on properties (path,name,ns);
create unique index token on locks (token);
COMMIT;
