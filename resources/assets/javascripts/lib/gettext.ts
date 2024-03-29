import { translate } from 'vue-gettext';
import * as defaultTranslations from '../../../../locale/de/LC_MESSAGES/js-resources.json';
import eventBus from './event-bus';

interface StringDict {
    [key: string]: string;
}

interface InstalledLanguage {
    name: string;
    selected: boolean;
}

interface InstalledLanguages {
    [key: string]: InstalledLanguage;
}

type TranslationDict = StringDict;

interface TranslationDicts {
    [key: string]: TranslationDict | null;
}

const DEFAULT_LANG = 'de_DE';
const DEFAULT_LANG_NAME = 'Deutsch';

const state = getInitialState();

const $gettext = translate.gettext.bind(translate);
const $ngettext = translate.ngettext.bind(translate);
const $gettextInterpolate = translate.gettextInterpolate.bind(translate);

export { $gettext, $ngettext, $gettextInterpolate, translate, getLocale, setLocale, getVueConfig };

function getLocale() {
    return state.locale;
}

async function setLocale(locale = getInitialLocale()) {
    if (!(locale in getInstalledLanguages())) {
        throw new Error('Invalid locale: ' + locale);
    }

    state.locale = locale;
    if (state.translations[state.locale] === null) {
        const translations: TranslationDict = await getTranslations(state.locale);
        state.translations[state.locale] = translations;
    }

    translate.initTranslations(state.translations, {
        getTextPluginMuteLanguages: [DEFAULT_LANG],
        getTextPluginSilent: false,
        language: state.locale,
        silent: false,
    });

    eventBus.emit('studip:set-locale', state.locale);
}

function getVueConfig() {
    const availableLanguages = Object.entries(getInstalledLanguages()).reduce((memo, [lang, { name }]) => {
        memo[lang] = name;

        return memo;
    }, {} as StringDict);

    return {
        availableLanguages,
        defaultLanguage: DEFAULT_LANG,
        muteLanguages: [DEFAULT_LANG],
        silent: false,
        translations: state.translations,
    };
}

function getInitialState() {
    const translations: TranslationDicts = Object.entries(getInstalledLanguages()).reduce((memo, [lang]) => {
        memo[lang] = lang === DEFAULT_LANG ? defaultTranslations : null;

        return memo;
    }, {} as TranslationDicts);

    return {
        locale: DEFAULT_LANG,
        translations,
    };
}

function getInitialLocale() {
    for (const [lang, { selected }] of Object.entries(getInstalledLanguages())) {
        if (selected) {
            return lang;
        }
    }

    return DEFAULT_LANG;
}

function getInstalledLanguages(): InstalledLanguages {
    return window?.STUDIP?.INSTALLED_LANGUAGES ?? { [DEFAULT_LANG]: { name: DEFAULT_LANG_NAME, selected: true } };
}

async function getTranslations(locale: string): Promise<TranslationDict> {
    try {
        const language = locale.split(/[_-]/)[0];
        const translation = await import(`../../../../locale/${language}/LC_MESSAGES/js-resources.json`);

        return translation;
    } catch (exception) {
        console.error('Could not load locale: "' + locale + '"', exception);

        return {};
    }
}
