.PHONY: web worker

.PHONY: all
all: help ## outputs the help message

.PHONY: help
help: ## this help message
	@grep -h -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-36s\033[0m %s\n", $$1, $$2}'

web: ## starts a web process
ifdef DYNO
	vendor/bin/heroku-php-nginx -C config/nginx.conf  -l log/debug.log  -l log/error.log  -l log/sql.log webroot/
else
	bin/cake server -p $(PORT)
endif

worker: ## starts the background worker
	bin/cake queuesadilla
