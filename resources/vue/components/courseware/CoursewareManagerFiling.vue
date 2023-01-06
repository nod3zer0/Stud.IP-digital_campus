<template>
    <button
        class="cw-manager-filing"
        :class="{ 'cw-manager-filing-active': active, 'cw-manager-filing-disabled': disabled }"
        :aria-pressed="active"
        @click="toggleFiling"
    >
        <span v-if="itemType === 'element'"><translate>Seite an dieser Stelle einfügen</translate> </span>
        <span v-if="itemType === 'container'"><translate>Abschnitt an dieser Stelle einfügen</translate> </span>
        <span v-if="itemType === 'block'"><translate>Block an dieser Stelle einfügen</translate> </span>
    </button>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-manager-filing',
    props: {
        parentId: String,
        parentItem: Object,
        itemType: String, // element || container || block
    },
    data() {
        return {
            active: false,
            disabled: false,
            data: {},
        };
    },
    computed: {
        ...mapGetters({
            filingData: 'filingData',
        }),
    },
    methods: {
        ...mapActions({
            cwManagerFilingData: 'cwManagerFilingData'
        }),
        toggleFiling() {
            if (this.disabled) {
                return false;
            }
            if (this.active) {
                this.cwManagerFilingData({});
            } else {
                this.cwManagerFilingData({ parentId: this.parentId, itemType: this.itemType, parentItem: this.parentItem });
            }
        },
    },
    watch: {
        filingData(newValue, oldValue) {
            if (Object.keys(newValue).length !== 0) {
                if (newValue.parentId === this.parentId && newValue.itemType === this.itemType) {
                    this.active = true;
                } else {
                    this.disabled = true;
                }
            } else {
                this.active = false;
                this.disabled = false;
                if (Object.keys(oldValue).length !== 0) {
                    if (oldValue.parentId === this.parentId && oldValue.itemType === this.itemType) {
                        this.$emit('deactivated');
                    }
                }
            }
        },
    },
};
</script>
