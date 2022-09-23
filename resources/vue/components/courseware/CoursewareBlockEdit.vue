<template>
    <section class="cw-block-edit">
        <header><translate>Bearbeiten</translate></header>
        <div class="cw-block-features-content">
            <div @click="deactivateToolbar(); exitHandler = true;">
                <slot name="edit" />
            </div>
            <div class="cw-button-box">
                <button class="button accept" @click="$emit('store'); exitHandler = false;"><translate>Speichern</translate></button>
                <button class="button cancel" @click="$emit('close'); exitHandler = false;"><translate>Abbrechen</translate></button>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: 'courseware-block-edit',
    props: {
        block: Object,
    },
    data() {
        return {
            originalBlock: Object,
            exitHandler: false
        };
    },
    beforeMount() {
        this.originalBlock = this.block;
    },
    methods: {
        deactivateToolbar() {
            this.$store.dispatch('coursewareBlockAdder', {});
            this.$store.dispatch('coursewareShowToolbar', false);
        },
    },
    beforeDestroy() {
        if (this.exitHandler) {
            console.log('autosave');
            this.$emit('store');
        }
    }
};
</script>
