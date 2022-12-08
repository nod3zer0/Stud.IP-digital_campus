$(document).on('click', 'a[data-qr-code]', function (event) {
    const data = $(this).data();
    STUDIP.QRCode.show(this.href, {
        print: data.qrCodePrint ?? false,
        title: data.qrTitle ?? null,
        description: data.qrCode || null,
    });

    event.preventDefault();
});

STUDIP.ready(event => {
    $('code.qr', event.target).each(function () {
        const text = $(this).text().trim();
        if ($(this).hasClass('hide-text')) {
            $(this).text('');
        }

        $(this).qrcode({
            text: text,
            width: 1280,
            height: 1280,
            correctLevel: 3
        }).addClass('has-qr-code');
    });
});
