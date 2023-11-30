<template>
    <button class="square-button" @click="$emit('click')">
        <studip-icon :shape="icon" :size="50" />
        <span>{{ title }}</span>
    </button>
</template>

<script>
export default {
    name: 'studip-square-button',
    props: {
        icon: {
            type: String,
            default: 'seminar',
        },
        title: {
            type: String,
            required: true,
            validator: (value) => {
                return value !== '';
            },
        },
    },
};
</script>
<style scoped lang="scss">
$size: 130px;
.square-button {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    max-height: $size;
    max-width: $size;
    min-width: $size;
    min-height: $size;
    margin: 10px;
    padding: 10px;
    background-color: transparent;
    border: solid thin var(--content-color-40);
    cursor: pointer;

    img {
        width: 100%;
        height: 50px;
        margin-bottom: 8px;
    }
    span {
        color: var(--base-color);
    }
    &:hover span {
        color: var(--red);
    }
}
</style>
