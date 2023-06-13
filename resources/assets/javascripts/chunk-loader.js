export const loadScript = function (script_name) {
    return new Promise(function (resolve, reject) {
        let script = document.createElement('script');
        script.src = `${STUDIP.ASSETS_URL}${script_name}`;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
};

export const loadChunk = (function () {
    var mathjax_promise = null;

    return function (chunk) {
        var promise = null;
        switch (chunk) {

            case 'code-highlight':
                promise = import(
                    /* webpackChunkName: "code-highlight" */
                    './chunks/code-highlight'
                ).then(({default: hljs}) => {
                    return hljs;
                });
                break;

            case 'chartist':
                promise = import(
                    /* webpackChunkName: "chartist" */
                    './chunks/chartist'
                ).then(({ default: Chartist }) => Chartist);
                break;

            case 'fullcalendar':
                promise = import(
                    /* webpackChunkName: "fullcalendar" */
                    './chunks/fullcalendar'
                );
                break;

            case 'tablesorter':
                promise = import(
                    /* webpackChunkName: "tablesorter" */
                    './chunks/tablesorter'
                );
                break;

            case 'mathjax':
                if (mathjax_promise === null) {
                    mathjax_promise = STUDIP.loadScript(
                        'javascripts/mathjax/MathJax.js?config=TeX-AMS_HTML,default'
                    ).then(() => {
                        (function (origPrint) {
                            window.print = function () {
                                window.MathJax.Hub.Queue(
                                    ['Delay', window.MathJax.Callback, 700],
                                    origPrint
                                );
                            };
                        })(window.print);

                        return window.MathJax;
                    }).catch(() => {
                        console.log('Could not load mathjax')
                    });
                }
                promise = mathjax_promise;
                break;

            case 'vue':
                promise = import(
                    /* webpackChunkName: "vue.js" */
                    './chunks/vue'
                );
                break;

            case 'wysiwyg':
                promise = import(
                    /* webpackChunkName: "vue.js" */
                    './chunks/wysiwyg'
                ).then(({default: ClassicEditor}) => {
                    return ClassicEditor;
                });
                break;

            default:
                promise = Promise.reject(new Error(`Unknown chunk: ${chunk}`));
        }

        return promise.catch((error) => {
            console.error(`Could not load chunk ${chunk}`, error);
        });
    };
}());
