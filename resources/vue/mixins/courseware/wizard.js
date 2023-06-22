const wizardMixin = {
    methods: {
        checkUploadImageFile(file) {
            let uploadFileError = '';
            if (file.size > 2097152) {
                uploadFileError = this.$gettext('Diese Datei ist zu groß. Bitte wählen Sie eine Datei aus, die kleiner als 2MB ist.');
            }
            if (!file.type.includes('image')) {
                uploadFileError = this.$gettext('Diese Datei ist kein Bild. Bitte wählen Sie ein Bild aus.');
            }

            return uploadFileError;
        },
    }
};

export default wizardMixin;