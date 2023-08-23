<template>
    <section class="cw-block-edit">
        <header v-if="preview">{{ $gettext('Bearbeiten') }}</header>
        <div class="cw-block-features-content">
            <div @click="deactivateToolbar(); exitHandler = true;">
                <slot name="edit" />
            </div>
            <div class="cw-button-box">
                <button class="button accept" @click="$emit('store'); exitHandler = false;">{{ $gettext('Speichern') }}</button>
                <button class="button cancel" @click="$emit('close'); exitHandler = false;">{{ $gettext('Abbrechen') }}</button>
            </div>
        </div>
    </section>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-block-edit',
    props: {
        block: Object,
        preview: Boolean
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
        ...mapActions({
            coursewareBlockAdder: 'coursewareBlockAdder',
            coursewareShowToolbar: 'coursewareShowToolbar'
        }),
        deactivateToolbar() {
            this.coursewareBlockAdder({});
            this.coursewareShowToolbar(false);
        },
    },
    beforeDestroy() {
        if (this.exitHandler) {
            this.$emit('store');
        }
    }
};
</script>
