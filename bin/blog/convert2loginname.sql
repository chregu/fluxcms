#this script is only needed, if you used the old imported (before 22.10.2004) and have authorid in plogposts instead of loginname
ALTER TABLE `blogposts` ADD `post_authorid` INT NOT NULL AFTER `post_author` ;
update blogposts set post_authorid = post_author ;
ALTER TABLE `blogposts` CHANGE `post_author` `post_author` VARCHAR( 40 ) DEFAULT '' NOT NULL ;
ALTER TABLE `blogposts` ADD INDEX ( `post_author` ) ;
update blogposts, users set post_author = users.user_login where users.ID  = blogposts.post_authorid ;
ALTER TABLE `blogposts` DROP `post_authorid` ;

