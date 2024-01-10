<template>
    <div class="studip-five-stars">
        <studip-icon v-for="index in fullStars" :key="index+'full'" shape="star" :role="role" :size="size" /><studip-icon v-if="halfStar" shape="star-halffull" :role="role" :size="size" /><studip-icon v-for="index in emptyStars" :key="index+'empty'" shape="star-empty" :role="role" :size="size" />
    </div>
</template>

<script>
import StudipIcon from './../StudipIcon.vue';
export default {
    name: 'studip-five-stars',
    components: {
        StudipIcon
    },
    props: {
        amount: {
            type: Number,
            required: true,
            validator(value) {
                return value <= 5 && value >= 0
            }
        },
        role: {
            type: String,
            required: false,
            default: 'status-yellow',
        },
        size: {
            type: Number,
            required: false,
            default: 24,
        }
    },
    computed: {
        fullStars() {
            return Math.floor(this.amount);
        },
        halfStar() {
            return this.amount - this.fullStars >= 0.5
        },
        emptyStars() {
            const half = this.halfStar ? 1 : 0;
            return 5 - this.fullStars - half;
        }
    }
}
</script>