<template>
    <time :datetime="date.toISOString()">{{ formatted }}</time>
</template>

<script>
function formatDate(date) {
    return pad(date.getDate()) + '.' + pad(date.getMonth() + 1) + '.' + date.getFullYear();
}

function pad(what) {
    return what.toString().padStart(2, '0');
}

export default {
    props: {
        date: {
            type: Date,
            required: true,
        },
    },
    computed: {
        formatted() {
            return formatDate(this.date);
        },
    },
};
</script>
