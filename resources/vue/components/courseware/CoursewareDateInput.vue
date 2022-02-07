<template>
    <input :value="formattedDate" @input="onInput" type="date" />
</template>

<script>
const fromISO8601 = (string) => new Date(string);
const toISO8601 = (date) => date.toISOString();
const pad = (what, length = 2) => `00000000${what}`.substr(-length);

export default {
    props: ['value'],
    data: () => ({
        date: new Date(),
    }),
    computed: {
        formattedDate() {
            return `${this.date.getFullYear()}-${pad(this.date.getMonth() + 1)}-${pad(this.date.getDate())}`;
        },
    },
    methods: {
        onInput({ target }) {
            const newValue = toISO8601(target.valueAsDate);
            if (newValue !== this.value) {
                this.$emit('input', newValue);
            }
        },
    },
    beforeMount() {
        if (this.value) {
            this.date = fromISO8601(this.value);
        }
    },
};
</script>
