class StudipUploadAdapter {
    constructor(loader) {
        this.loader = loader;
    }

    upload() {
        return this.loader.file.then(
            (file) =>
                new Promise((resolve, reject) => {
                    var data = new FormData();
                    data.append('files[]', file);

                    $.ajax({
                        url: STUDIP.URLHelper.getURL('dispatch.php/wysiwyg/upload'),
                        type: 'POST',
                        data: data,
                        contentType: false,
                        async: false,
                        processData: false,
                        cache: false,
                        dataType: 'json',
                        error: function (err) {
                            reject(err);
                        },
                        success: function (data) {
                            resolve({ default: data.files[0].url });
                        },
                    });
                })
        );
    }

    // Aborts the upload process.
    abort() {}
}

export default function StudipUpload(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
        return new StudipUploadAdapter(loader);
    };
}
