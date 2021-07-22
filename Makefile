CODECEPT  = composer/bin/codecept
SVGO = node_modules/svgo/bin/svgo
RESOURCES = $(shell find resources -type f)

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
	npm install --no-save

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

optimize-icons: npm
	$(SVGO) --config=config/svgo.config.js -f public/assets/images/icons/black -r
	$(SVGO) --config=config/svgo.config.js -f public/assets/images/icons/blue -r
	$(SVGO) --config=config/svgo.config.js -f public/assets/images/icons/green -r
	$(SVGO) --config=config/svgo.config.js -f public/assets/images/icons/grey -r
	$(SVGO) --config=config/svgo.config.js -f public/assets/images/icons/red -r
	$(SVGO) --config=config/svgo.config.js -f public/assets/images/icons/white -r
	$(SVGO) --config=config/svgo.config.js -f public/assets/images/icons/yellow -r

# dummy target to force update of "doc" target
force_update:


LOCALES = en
NPM_BIN = $(shell npm bin)
VUE_GETTEXT_SOURCES = $(shell find ./resources -name '*.js' -o -name '*.vue' 2> /dev/null)
VUE_PO_FILE = js-resources.po
VUE_POT_FILE = ./locale/js-ressources.pot
VUE_TRANSLATIONS = ./resources/locales/translations.json
VUE_TRANSLATION_FILES ?= $(patsubst %,./resources/locales/%.json,$(LOCALES))
VUE_LOCALE_FILES ?= $(patsubst %,./locale/%/LC_MESSAGES/$(VUE_PO_FILE),$(LOCALES))

vue-gettext-extract: $(VUE_POT_FILE)
vue-gettext-compile: $(VUE_TRANSLATION_FILES)
vue-gettext-clean:;	rm -f $(VUE_POT_FILE) $(VUE_TRANSLATIONS) $(VUE_TRANSLATIONS)

$(VUE_POT_FILE): $(VUE_GETTEXT_SOURCES)
	$(NPM_BIN)/gettext-extract --quiet --attribute v-translate \
		--output $@ $(VUE_GETTEXT_SOURCES)

	@for lang in $(LOCALES); do \
		export PO_FILE=./locale/$$lang/LC_MESSAGES/$(VUE_PO_FILE); \
		if [ -f $$PO_FILE ]; then  \
			msgmerge --lang=$$lang \
				-o $$PO_FILE \
				-C $$PO_FILE \
				./locale/$$lang/LC_MESSAGES/studip.po  $@ || break ;\
			msgattrib --set-obsolete --ignore-file=$(VUE_POT_FILE) -o $$PO_FILE $$PO_FILE; \
			msgattrib --no-obsolete -o $$PO_FILE $$PO_FILE; \
		else \
			msginit --no-translator --locale=$$lang --input=$@ --output-file=$$PO_FILE || break ; \
			msgattrib --no-wrap --no-obsolete -o $$PO_FILE $$PO_FILE || break; \
		fi; \
	done;

$(VUE_TRANSLATIONS): $(VUE_LOCALE_FILES)
	$(NPM_BIN)/gettext-compile --output $@ $(VUE_LOCALE_FILES)

$(VUE_TRANSLATION_FILES): $(VUE_TRANSLATIONS)
	php cli/vue-gettext-split-translations.php
