/**
 * This class helps to handle URLs of hyperlinks and change their parameters.
 * For example a javascript-page may open an item and the user expects other links
 * on the same page to "know" that this item is now open. But because we don't use
 * PHP session-variables here, this is difficult to use. This class can help. You
 * can overwrite the href-attribute of the link by:
 *
 *  [code]
 *  link.href = STUDIP.URLHelper.getURL("adresse.php?hello=world#anchor");
 *  [/code]
 * Returns something like:
 * "http://uni-adresse.de/studip/adresse.php?hello=world&mandatory=parameter#anchor"
 */

class URLHelper {
    base_url: string;
    parameters: Record<string, string>;

    constructor(base_url = "", parameters = {}) {
        //the base url for all links
        this.base_url = base_url;

        // bound link parameters
        this.parameters = parameters;
    }

    /**
     * returns a readily encoded URL with the mandatory parameters and additionally passed
     * parameters.
     *
     * @param url string: any url-string
     * @param param_object map: associative object for extra values
     * @param ignore_params boolean: ignore previously bound parameters
     * @return: url with all necessary and additional parameters, encoded
     */
    getURL(url: string, param_object: any, ignore_params: boolean): string {
        let result;

        if (url === '' || url.match(/^[?#]/)) {
            result = new URL(url, location.href.replace(/\?.*/, ''));
        } else {
            result = new URL(url, this.base_url);
        }

        if (!ignore_params) {
            for (const key in this.parameters) {
                if (!result.searchParams.has(key)) {
                    result.searchParams.set(key, this.parameters[key]);
                }
            }
        }

        for (const key in param_object) {
            if (param_object[key] !== null) {
                result.searchParams.set(key, param_object[key]);
            } else {
                result.searchParams.delete(key);
            }
        }

        return result.href;
    }
}

export function createURLHelper(config: { base_url?: string, parameters?: Record<string, string>}): URLHelper {
    return new URLHelper(config?.base_url, config?.parameters);
}
