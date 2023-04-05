import { Emitter } from 'mitt';
import { URLHelper } from './assets/javascripts/lib/url_helper.d.ts';
export {};

declare global {
    interface Window {
        STUDIP: {
            wysiwyg_enabled: boolean;
            INSTALLED_LANGUAGES: { [name: string]: InstalledLanguage };
            ABSOLUTE_URI_STUDIP: string;
            ASSETS_URL: string;
            CSRF_TOKEN: { name: string; value: string };
            URLHelper: URLHelper;
            eventBus: Emitter;
        };
    }
}

export interface InstalledLanguage {
    name: string;
    path: string;
    picture: string;
    selected: boolean;
}
