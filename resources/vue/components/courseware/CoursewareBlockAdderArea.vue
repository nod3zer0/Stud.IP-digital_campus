<template>
    <button
        class="cw-block-adder-area"
        :class="{ 'cw-block-adder-active': adderActive }"
        :aria-pressed="adderActive"
        @click="selectBlockAdder"
    >
        <studip-icon v-show="!adderActive" shape="add" />
        <studip-icon v-show="adderActive" shape="add" role="info_alt"/>
        <span v-show="!adderActive"><translate>Block zu diesem Abschnitt hinzufügen</translate></span>
        <span v-show="adderActive"><translate>Abschnitt aktiv - Blöcke werden hier eingefügt</translate></span>
    </button>
</template>

<script>
import StudipIcon from '../StudipIcon.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
  components: { StudipIcon },
    name: 'courseware-block-adder-area',
    props: {
        container: Object,
        section: Number,
    },
    data() {
        return {
            adderActive: false,
        };
    },
    computed: {
        ...mapGetters({
            adderStorage: 'blockAdder',
        }),
        adderDisable() {
            return Object.keys(this.adderStorage).length !== 0 && !this.adderActive;
        },
    },
    methods: {
        ...mapActions({
            coursewareBlockAdder: 'coursewareBlockAdder',
            coursewareSelectedToolbarItem: 'coursewareSelectedToolbarItem',
            coursewareShowToolbar: 'coursewareShowToolbar'
        }),
        selectBlockAdder() {
            if (this.adderActive) {
                this.adderActive = false;
                this.coursewareBlockAdder({});
            } else {
                this.adderActive = true;
                this.coursewareBlockAdder({ container: this.container, section: this.section });
                this.coursewareSelectedToolbarItem('blockadder');
                this.coursewareShowToolbar(true);
            }
        },
    },
    watch: {
        adderStorage(newValue, oldValue) {
            if (Object.keys(newValue).length === 0) {
                this.adderActive = false;
                this.$emit('updateContainerContent', oldValue);
            } else {
                if (newValue.container && newValue.container.id === this.container.id && newValue.section === this.section) {
                    this.adderActive = true;
                } else {
                    this.adderActive = false;
                }
            }
        },
    },
};
</script>
