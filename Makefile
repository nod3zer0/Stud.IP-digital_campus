CODECEPT  = composer/bin/codecept
CATALOGS  = locale/en/LC_MESSAGES/studip.mo locale/en/LC_MESSAGES/js-resources.json
NPM_BIN   = $(shell npm bin)
RESOURCES = $(shell find resources -type f)

PHP_SOURCES = $(shell find app config lib public templates -name '*.php' \( ! -path 'public/plugins_packages/*' -o -path 'public/plugins_packages/core/*' \))
VUE_SOURCES = $(shell find resources -name '*.js' -o -name '*.vue')

# build all needed files
build: composer webpack-prod

# remove all generated files
clean: clean-composer clean-npm clean-webpack clean-doc

composer: composer/composer/installed.json

composer-dev: $(CODECEPT)

composer/composer/installed.json: composer.json composer.lock
	composer install --no-dev
	@touch $@

$(CODECEPT): composer.json composer.lock
	composer install
	@touch $@

clean-composer:
	rm -rf composer

npm: node_modules/.package-lock.json

node_modules/.package-lock.json: package.json package-lock.json
	npm install --no-save --no-audit --no-fund

clean-npm:
	rm -rf node_modules

webpack-dev: .webpack.dev

webpack-prod: .webpack.prod

webpack-watch: npm
	npm run webpack-watch

wds: npm
	npm run wds

.webpack.dev: node_modules/.package-lock.json $(RESOURCES)
	@rm -f .webpack.prod
	npm run webpack-dev
	@touch $@

.webpack.prod: node_modules/.package-lock.json $(RESOURCES)
	@rm -f .webpack.dev
	npm run webpack-prod
	@touch $@

clean-webpack:
	@rm -f .webpack.dev .webpack.prod
	rm -rf public/assets/javascripts/*.js
	rm -rf public/assets/javascripts/*.js.map
	rm -rf public/assets/stylesheets/*.css
	rm -rf public/assets/stylesheets/*.css.map

doc: force_update
	doxygen Doxyfile

clean-doc:
	rm -rf doc/html

test: test-unit

test-functional: $(CODECEPT)
	$(CODECEPT) run functional

test-jsonapi: $(CODECEPT)
	$(CODECEPT) run jsonapi

test-unit: $(CODECEPT)
	$(CODECEPT) run unit

catalogs: npm $(CATALOGS)

optimize-icons: npm
	find public/assets/images/icons -type f | xargs -P0 $(NPM_BIN)/svgo -q --config=config/svgo.config.js

# default rules for gettext handling
js-%.pot: $(VUE_SOURCES)
	$(NPM_BIN)/gettext-extract --attribute v-translate --output $@ $(VUE_SOURCES)

js-%.po: js-%.pot
	msgmerge -qU -C $(dir $@)studip.po $@ $<

js-%.json: js-%.po
	$(NPM_BIN)/gettext-compile --output $@ $<
	sed -i~ 's/^{[^{]*//;s/}$$//' $@

%.pot: $(PHP_SOURCES)
	xgettext -o $@ --from-code=UTF-8 $(PHP_SOURCES)

%.po: %.pot
	msgmerge -qU $@ $<

%.mo: %.po
	msgfmt -o $@ $<

# dummy target to force update of "doc" target
force_update:
