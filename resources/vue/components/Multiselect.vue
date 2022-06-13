<template>
    <v-select
        multiple
        v-model="selected"
        :options="transformed_options"
        :reduce="(option) => option.id"
        v-bind="$attrs"
        v-on="$listeners"
    >
        <div slot="no-options"><translate>Keine Auswahlmöglichkeiten</translate></div>
    </v-select>
</template>

<script>
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css'
export default {
    name: 'multiselect',
    components: {
        vSelect,
    },
    inheritAttrs: false,
    props: {
        name: {
            type: String,
            required: false
        },
        value: {
            required: false
        },
        options: {
            type: Object,
            required: true
        }
    },
    data () {
        return {
            selected: []
        };
    },
    computed: {
        transformed_options () {
            let output = [];
            Object.entries(this.options).forEach(obj => {
                output.push({
                    id: obj[0],
                    label: obj[1]
                });
            });
            return output;
        }
    },
    mounted () {
        this.selected = this.value;
    },
    watch: {
        selected: {
            handler(newValue, oldValue) {
                this.$emit('input', newValue);
            },
            deep: true
        }
    }
}
</script>
