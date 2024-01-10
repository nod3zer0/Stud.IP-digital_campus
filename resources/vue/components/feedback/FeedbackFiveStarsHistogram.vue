<template>
    <div class="five-stars-histogram" :class="{ vertical: vertical }">
        <div class="five-stars-histogram-average">
            <p class="fraction">
                <span class="average">{{ average.toFixed(1) }}</span
                >/5
            </p>
            <studip-five-stars :amount="average" />
            <p class="total">
                {{
                    $gettextInterpolate($ngettext('%{n} Bewertung', '%{n} Bewertungen', entries.length), {
                        n: entries.length,
                    })
                }}
            </p>
        </div>
        <div class="five-stars-histogram-chart" v-if="ratings">
            <div v-for="i in [5, 4, 3, 2, 1]" :key="'chart-' + i">
                <span>{{ i }} <studip-icon shape="star" role="info" /></span>
                <div class="percentage">
                    <div class="percentage-bar" :style="{ width: getRatePercentage(ratings[i]) }">
                        {{ getRatePercentage(ratings[i]) }}
                    </div>
                </div>
                <span>{{ ratings[i] ?? 0 }}</span>
            </div>
        </div>
    </div>
</template>
<script>
import StudipFiveStars from './StudipFiveStars.vue';

export default {
    name: 'feedback-five-stars-histogram',
    components: {
        StudipFiveStars,
    },
    props: {
        entries: Array,
        vertical: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            ratings: null,
        };
    },
    computed: {
        average() {
            if (this.entries.length === 0) {
                return 0;
            }
            let sum = this.entries.reduce((acc, entry) => acc + parseInt(entry.attributes.rating), 0);

            return sum / this.entries.length;
        },
    },
    methods: {
        getCountOfRatings() {
            this.ratings = [];
            this.entries.forEach((entry) => {
                const rating = entry.attributes.rating;
                if (this.ratings[rating]) {
                    this.ratings[rating] += 1;
                } else {
                    this.ratings[rating] = 1;
                }
            });
        },
        getRatePercentage(rate) {
            if (rate === undefined) {
                return '0%';
            }
            return parseInt((rate / this.entries.length) * 100, 10) + '%';
        },
    },
    mounted() {
        this.getCountOfRatings();
    },
    watch: {
        entries: {
            handler() {
                this.getCountOfRatings();
            },
            deep: true,
        },
    },
};
</script>
