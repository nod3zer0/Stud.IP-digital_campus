/* ------------------------------------------------------------------------
 * Javascript-spezifisches Markup
 * ------------------------------------------------------------------------ */

const Markup = {
    element: function (selector) {
        var elements;
        if (typeof selector === 'string' && document.getElementById(selector)) {
            elements = $('#' + selector);
        } else {
            elements = $(selector);
        }
        elements.each((index, element) => {
            $.each(Markup.callbacks, (index, func) => {
                if (index !== 'element' || typeof func === 'function') {
                    func(element);
                }
            });
        });
    },
    callbacks: {
        math_jax: function (element) {
            $('span.math-tex:not(:has(.MathJax)),.formatted-content:contains("[tex]")', element).each((index, block) => {
                STUDIP.loadChunk('mathjax').then((MathJax) => {
                    if (typeof MathJax.Hub.Typeset === "function") {
                        MathJax.Hub.Typeset(block);
                    }
                });
            });
        },
        codehighlight: function (element) {
            $('pre.usercode:not(.hljs)', element).each(function (index, block) {
                STUDIP.loadChunk('code-highlight').then((hljs) => {
                    hljs.highlightBlock(block);
                });
            });
        }
    }
};

export default Markup;
