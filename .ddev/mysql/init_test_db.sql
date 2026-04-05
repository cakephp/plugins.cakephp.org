# This file gets executed every time the db container gets started via ddev.
# See .ddev/config.yaml for more details.
DROP DATABASE IF EXISTS db_test;
CREATE DATABASE db_test;
GRANT ALL ON db_test.* TO 'db'@'%';
FLUSH PRIVILEGES;
