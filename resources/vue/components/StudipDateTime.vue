<template>
    <time :datetime="datetime" v-if="timestamp !== 0" :title="title">
        {{ formatted_date() }}
    </time>
</template>

<script>

    export default {
        name: 'studip-date-time',
        props: {
            timestamp: Number,
            relative: {
                type: Boolean,
                required: false,
                default: false
            },
            date_only: {
                type: Boolean,
                required: false,
                default: false
            }
        },
        computed: {
            datetime () {
                if (!Number.isInteger(this.timestamp)) {
                    return '';
                }
                let date = new Date(this.timestamp * 1000);
                return date.toISOString();
            },
            title () {
                return this.display_relative() ? this.formatted_date(true) : false;
            }
        },
        methods: {
            display_relative: function () {
                return Date.now() - this.timestamp * 1000 < 12 * 60 * 60 * 1000;
            },
            formatted_date: function (force_absolute = false) {
                if (!Number.isInteger(this.timestamp)) {
                    return `Should be integer: ${this.timestamp}`;
                }
                let date = new Date(this.timestamp * 1000);
                let relative_value = !force_absolute && this.relative && this.display_relative();
                return STUDIP.DateTime.getStudipDate(date, relative_value, this.date_only);
            }
        },
        mounted: function () {
            window.setInterval(() => {
                this.$forceUpdate();
            }, 1000);
        }
    }
</script>
