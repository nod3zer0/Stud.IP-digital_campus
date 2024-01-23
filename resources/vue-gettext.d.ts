declare module "vue-gettext" {
    import GettextPlugin from 'vue-gettext';

    declare namespace translate {
        function getTranslation(msgid: any, n?: number, context?: any, defaultPlural?: any, language?: string): any;
        function gettext(msgid: any, language?: string): any;
        function pgettext(context: any, msgid: any, language?: string): any;
        function ngettext(msgid: any, plural: any, n: any, language?: string): any;
        function npgettext(context: any, msgid: any, plural: any, n: any, language?: string): any;
        function initTranslations(translations: any, config: any): void;
        const gettextInterpolate: any;
    }

    export { translate };

    export default GettextPlugin;
}
