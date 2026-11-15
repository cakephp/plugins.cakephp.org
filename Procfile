web: frankenphp run --config /etc/frankenphp/Caddyfile --adapter caddyfile
release: sh -c 'bin/cake cache clear catalogue && bin/cake cache clear _cake_model_ && bin/cake migrations migrate --no-lock -p Tags && bin/cake migrations migrate --no-lock && bin/cake migrations migrate --no-lock -p ADmad/SocialAuth && bin/cake schema_cache clear && bin/cake cache clear _cake_attributes_'
