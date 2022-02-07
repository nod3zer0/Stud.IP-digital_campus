<template>
    <div class="cw-oblong" :class="['cw-oblong-' + oblongSize]">
        <div class="cw-oblong-value">
            <slot name="oblongValue"></slot>
        </div>
        <div class="cw-oblong-description">
            <studip-icon v-if="icon" :shape="icon" role="info" :size="iconSize"></studip-icon>{{ name }}
        </div>
    </div>
</template>

<script>
export default {
    name: 'courseware-oblong',
    props: {
        icon: String,
        name: String,
        color: String,
        size: String,
    },
    computed: {
        oblongSize() {
            switch (this.size) {
                case 'large':
                    return 'large';
                case 'small':
                    return 'small';
                default:
                    return 'small';
            }
        },
        iconSize() {
            switch (this.size) {
                case 'large':
                    return 48;
                case 'small':
                    return 24;
                default:
                    return 24;
            }
        },
    },
};
</script>
