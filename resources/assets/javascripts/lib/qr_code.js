import QRCodeGenerator from "../vendor/qrcode-04f46c6.js"
import { $gettext } from "./gettext.js";
import Dialog from "./dialog.js";

const QRCode = {
    defaultOptions: {
        title: false,
    },
    show(text, options = {}) {
        options = Object.assign({}, QRCode.defaultOptions, options);

        // Prepare content
        const content = $('<div class="qr-code-display"/>');
        $('<img/>').attr('src', STUDIP.ASSETS_URL + 'images/logos/logoklein.png').appendTo(content);
        if (options.title) {
            $('<h1>').text(options.title).appendTo(content);
        }

        const code = $('<div class="code"/>').appendTo(content);
        const url = $('<div class="url"/>').appendTo(content);
        $('<a/>', {
            href: text,
            target: '_blank'
        }).text(text).appendTo(url);

        if (options.description) {
            const description = $('<div class="description"/>').text(options.description).appendTo(content);
        }

        // Actually generate code
        new QRCodeGenerator(code[0], {
            text: text,
            width: 1280,
            height: 1280,
            correctLevel: 3
        });

        // Prepare dialog
        let buttons = {
            fullscreen: {
                text: $gettext('Vollbild'),
                'class': 'fullscreen',
                click () {
                    if (content[0].requestFullscreen) {
                        content[0].requestFullscreen();
                    } else if (content[0].msRequestFullscreen) {
                        content[0].msRequestFullscreen();
                    } else if (content[0].mozRequestFullScreen) {
                        content[0].mozRequestFullScreen();
                    } else if (content[0].webkitRequestFullscreen) {
                        content[0].webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                    }
                }
            },
            cancel: {
                text: $gettext('Schließen'),
                'class': 'cancel',
                click () {
                    Dialog.close();
                }
            },
        };

        if (options.print) {
            buttons = Object.assign({
                print: {
                    text: $gettext('Drucken'),
                    'class': 'print',
                    click () {
                        var openWindow = window.open("", '_blank');
                        openWindow.document.write(`<body style="text-align: center;">${content.html()}</body>`);
                        openWindow.document.close();
                        openWindow.focus();
                        openWindow.print();
                        openWindow.close();
                    }
                },
            }, buttons);
        }


        Dialog.show(content, {
            title: options.title ?? $gettext('QR-Code'),
            size: 'big',
            buttons
        });
    },
    generate: function (element, text, options = {}) {
        options.text = text;
        if (options.correctLevel === undefined) {
            options.correctLevel = 3;
        }

        var qrcode = new QRCodeGenerator(element, options);
    }
};

export default QRCode;
