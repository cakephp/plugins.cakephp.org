#!/bin/sh

rm tmp/cakephp.db
bin/cake migrations migrate
bin/cake migrations migrate -p Tags
