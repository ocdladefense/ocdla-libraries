<?php
require('lodlogin.php');
?>
<iframe width="200" height="200" src="https://libraryofdefense.org"></iframe>


+------------------+------------------+------+-----+---------+----------------+
| Field            | Type             | Null | Key | Default | Extra          |
+------------------+------------------+------+-----+---------+----------------+
| uid              | int(10) unsigned | NO   | PRI | NULL    | auto_increment |
| name             | varchar(60)      | NO   | UNI |         |                |
| pass             | varchar(32)      | NO   |     |         |                |
| mail             | varchar(64)      | YES  | MUL |         |                |
| mode             | tinyint(4)       | NO   |     | 0       |                |
| sort             | tinyint(4)       | YES  |     | 0       |                |
| threshold        | tinyint(4)       | YES  |     | 0       |                |
| theme            | varchar(255)     | NO   |     |         |                |
| signature        | varchar(255)     | NO   |     |         |                |
| signature_format | smallint(6)      | NO   |     | 0       |                |
| created          | int(11)          | NO   | MUL | 0       |                |
| access           | int(11)          | NO   | MUL | 0       |                |
| login            | int(11)          | NO   |     | 0       |                |
| status           | tinyint(4)       | NO   |     | 0       |                |
| timezone         | varchar(8)       | YES  |     | NULL    |                |
| language         | varchar(12)      | NO   |     |         |                |
| picture          | varchar(255)     | NO   |     |         |                |
| init             | varchar(64)      | YES  |     |         |                |
| data             | longtext         | YES  |     | NULL    |                |
+------------------+------------------+------+-----+---------+----------------+


echo "SELECT username, password, value, active INTO OUTFILE '/home/email/jbernal/sql/nightly_export/ocdla_members_to_lod_users.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n' FROM members LEFT JOIN member_contact_info ci ON(members.id=ci.contact_id AND type='email');" | mysql ocdla -B -ujbernal -pj0s3! > ocdla_members_to_lod_users.csv

