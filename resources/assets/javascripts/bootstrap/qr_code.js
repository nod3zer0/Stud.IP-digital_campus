$(document).on('click', 'a[data-qr-code]', function (event) {
    const data = $(this).data();
    STUDIP.QRCode.show(this.href, {
        print: data.qrCodePrint ?? false,
        title: data.qrTitle ?? null,
        description: data.qrCode || null,
    });

    event.preventDefault();
});
