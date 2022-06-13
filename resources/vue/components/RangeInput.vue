<template>
    <div class="formpart range_input">
        <input type="range"
               :name="name"
               :min="min"
               :max="max"
               :step="step"
               :aria-valuemin="min"
               :aria-valuemax="max"
               :aria-valuenow="myValue"
               v-bind="$attrs"
               v-on="$listeners"
               v-model="myValue">
        <output for="fader"><translate :translate-params="{myValue: myValue ?? '1', max: max}">%{myValue} von %{max}</translate></output>
    </div>
</template>

<script>
export default {
    name: 'range-input',
    props: {
        name: {
            type: String,
            required: true
        },
        value: {
            required: false,
            default: 1
        },
        min: {
            type: Number,
            required: false,
            default: 1
        },
        max: {
            type: Number,
            required: false,
            default: 10
        },
        step: {
            type: Number,
            required: false,
            default: 1
        }
    },
    data () {
        return {
            myValue: 1
        };
    },
    mounted () {
        this.myValue = this.value > this.min ? this.value : this.min;
        if (this.myValue > this.max) {
            this.myValue = this.max;
        }
    },
    inheritAttrs: false,
    watch: {
        myValue: {
            handler(newValue, oldValue) {
                this.$emit('input', newValue);
            },
            deep: true
        }
    }
}
</script>
