<template>
    <div class="cw-call-to-action">
        <button class="action-button" :title="unfold ? titleOpen : titleClosed" @click="buttonAction">
            <studip-icon :shape="unfold ? 'arr_1down' : iconShape" :size="24"/>
            {{ actionTitle }}
        </button>
        <div v-if="unfold" class="cw-call-to-action-content">
            <slot name="content"></slot>
        </div>
    </div>

</template>

<script>
import StudipIcon from '../../StudipIcon.vue';

export default {
    name: 'courseware-call-to-action-box',
    components: {
        StudipIcon
    },
    props: {
        iconShape: {
            type: String,
            default: 'arr_1right'
        },
        titleClosed: {
            type: String,
            required: true
        },
        titleOpen: {
            type: String,
            required: true
        },
        actionTitle: {
            type: String,
            required: true
        },
        foldable: {
            type: Boolean,
            default: false
        },
        open: {
            type: Boolean,
            default: true
        }
    },
    data() {
        return {
            unfold: true
        }
    },
    methods: {
        buttonAction() {
            this.$emit('click');
            if (this.foldable) {
                this.unfold = !this.unfold;
            }
        }
    },
    mounted() {
        this.unfold = this.open;
    },
    watch: {
        open(newState) {
            this.unfold = newState;
        }
    }
}
</script>