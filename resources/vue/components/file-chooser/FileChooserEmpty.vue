<template>
    <div class="file-chooser-empty">
        <studip-icon shape="folder-empty" :size="128" role="inactive" />
        {{ $gettext('Dieser Ordner ist leer') }}
    </div>
</template>

<script>
export default {
    name: 'file-chooser-empty',
};
</script>
<style lang="scss">
.file-chooser-empty {
    display: flex;
    flex-direction: column;
    margin: 1em auto;
}
</style>
