<template>
    <div class="cw-collapsible" :class="{ 'cw-collapsible-open': isOpen }">
        <a href="#" :aria-expanded="isOpen" @click="isOpen = !isOpen">
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
import StudipIcon from './../StudipIcon.vue';

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
    methods: {},
    watch: {
        open(state) {
            this.isOpen = state;
        }
    }
};
</script>
