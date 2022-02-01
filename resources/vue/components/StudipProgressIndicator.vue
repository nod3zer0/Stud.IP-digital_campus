<template>
    <div class="progress-indicator-wrapper">
        <div class="progress-indicator" :style="indicatorStyle"></div>
        <p v-if="description !== ''" class="progress-indicator-description">
            {{ description }}
        </p>
        <p v-else class="progress-indicator-description-default">
            <translate>Lade...</translate>
        </p>
    </div>
</template>

<script>
export default {
    name: 'studip-progress-indicator',
    props: {
        description: {
            type: String,
            required: false
        },
        width: {
            type: Number,
            default: 32
        }
    },
    computed: {
        indicatorStyle() {
            let style = 'background-size: ' + this.width + 'px;';
            style += 'height: ' + this.width + 'px;';

            return style;
        }
    }
}
</script>