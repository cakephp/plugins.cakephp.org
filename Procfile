web: frankenphp run --config /etc/frankenphp/Caddyfile --adapter caddyfile
release: sh -c 'bin/cake migrations migrate --no-lock -p Tags && bin/cake migrations migrate --no-lock && bin/cake migrations migrate --no-lock -p ADmad/SocialAuth'
