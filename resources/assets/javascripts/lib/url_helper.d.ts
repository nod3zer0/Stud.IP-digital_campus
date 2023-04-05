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
declare class URLHelper {
    base_url: string;
    parameters: Record<string, string>;
    constructor(base_url?: string, parameters?: {});
    /**
     * returns a readily encoded URL with the mandatory parameters and additionally passed
     * parameters.
     *
     * @param url string: any url-string
     * @param param_object map: associative object for extra values
     * @param ignore_params boolean: ignore previously bound parameters
     * @return: url with all necessary and additional parameters, encoded
     */
    getURL(url: string, param_object: any, ignore_params: boolean): string;
}
export declare function createURLHelper(config: {
    base_url?: string;
    parameters?: Record<string, string>;
}): URLHelper;
export {};
