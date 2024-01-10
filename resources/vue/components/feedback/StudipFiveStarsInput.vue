<template>
    <div class="five-stars-input" :style="{ width: width + 'px' }">
        <button v-for="i in 5" :key="i" @click="setValue(i)">
            <studip-icon
                :shape="getShape(i)"
                :size="size"
                :alt="
                    $gettextInterpolate(
                        $ngettext(
                            'auswählen, um mit einem Stern zu bewerten.',
                            'auswählen, um mit %{i} Sternen zu bewerten.',
                            i
                        ),
                        { i: i }
                    )
                "
            />
        </button>
    </div>
</template>
<script>
import StudipIcon from './../StudipIcon.vue';
export default {
    name: 'studip-five-stars-input',
    components: {
        StudipIcon,
    },
    props: {
        value: {
            type: Number,
        },
        size: {
            type: Number,
            default: 24,
        },
    },
    computed: {
        width() {
            return (this.size + 2 * 14) * 5;
        },
    },
    methods: {
        setValue(val) {
            this.$emit('input', val);
        },
        getShape(pos) {
            return pos <= this.value ? 'star' : 'star-empty';
        },
    },
};
</script>
