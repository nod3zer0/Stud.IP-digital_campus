<template>
    <div class="cw-collapsible" :class="{ 'cw-collapsible-open': isOpen }">
        <a href="#" :aria-expanded="isOpen" @click.prevent="isOpen = !isOpen">
            <header :class="{ 'cw-collapsible-open': isOpen }" class="cw-collapsible-title">
                <studip-icon v-if="icon" :shape="icon" /> {{ title }}
            </header>
        </a>
        <div class="cw-collapsible-content" :class="{ 'cw-collapsible-content-open': isOpen }">
            <slot></slot>
        </div>
    </div>
</template>

<script>
import StudipIcon from '../../StudipIcon.vue';

export default {
    name: 'courseware-collapsible-box',
    components: {
        StudipIcon,
    },
    props: {
        title: String,
        icon: {
            type: String,
            default: '',
        },
        open: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            isOpen: this.open,
        };
    },
    mounted(){
        this.updateCollapsible();
    },
    updated() {
        this.updateCollapsible();
    },
    methods: {
        updateCollapsible() {
            if (this.isOpen) {
                STUDIP.eventBus.emit('courseware:update-collapsible', { 'uid': this._uid });
            }
        }
    },
    watch: {
        open(state) {
            this.isOpen = state;
        }
    }
};
</script>