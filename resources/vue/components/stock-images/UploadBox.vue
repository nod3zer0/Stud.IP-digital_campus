<template>
    <label id="stock-images-upload-box-drag-area">
        <div class="holder">
            <div class="box-centered">
                <div class="icon-upload">
                    <studip-icon shape="upload" :size="100" alt="" />
                </div>
                <strong>{{ $gettext('Bild auswählen oder per Drag & Drop hierher ziehen') }}</strong>
                <div class="upload-button-holder">
                    <input type="file" name="file" tabindex="-1" accept="image/*" ref="upload" @change="onUpload" />
                </div>
            </div>
        </div>
    </label>
</template>

<script>
export default {
    data: () => ({}),
    methods: {
        onUpload() {
            const files = this.$refs.upload.files;
            const file = files[0];
            this.$emit('upload', { file });
        },
    },
};
</script>

<style scoped>
#stock-images-upload-box-drag-area {
    background-color: var(--content-color-20);
    height: 100%;
    margin: -15px;
    padding: 18px 15px 10px;
    text-align: center;
}
.holder {
    align-items: center;
    border-color: var(--content-color-60);
    border-radius: 0.5em;
    border-style: dashed;
    border-width: 1px;
    box-sizing: border-box;
    display: flex;
    height: 100%;
    justify-content: center;
    padding-bottom: 4px;
    padding: 0;
}

.box-centered {
    height: auto;
    width: 100%;
    max-height: 100%;
}

.icon-upload + strong {
    color: var(--base-color);
    font-size: 1.5em;
    line-height: 1.2;
    display: block;
    font-weight: 500;
    margin: 0 0 14px;
    text-align: center;
    margin-left: 2em;
    margin-right: 2em;
}

.upload-button-holder input[type='file'] {
    opacity: 0;
    width: 100%;
    height: 100%;
    padding: 0;
}
</style>
