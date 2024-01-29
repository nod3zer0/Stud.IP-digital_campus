<template>
    <select :name="name" v-model="selected_value">
        <option v-if="with_indeterminate" value=""
                :selected="!value">
            {{ $gettext('Bitte wählen') }}
        </option>
        <option value="1" :selected="value == '1'">
            {{ $gettext('Montag') }}
        </option>
        <option value="2" :selected="value == '2'">
            {{ $gettext('Dienstag') }}
        </option>
        <option value="3" :selected="value == '3'">
            {{ $gettext('Mittwoch') }}
        </option>
        <option value="4" :selected="value == '4'">
            {{ $gettext('Donnerstag') }}
        </option>
        <option value="5" :selected="value == '5'">
            {{ $gettext('Freitag') }}
        </option>
        <option value="6" :selected="value == '6'">
            {{ $gettext('Samstag') }}
        </option>
        <option value="7" :selected="value == '7'">
            {{ $gettext('Sonntag') }}
        </option>
    </select>
</template>

<script>
export default {
    name: "day-of-week-select",
    props: {
        name: {
            type: String,
            required: true
        },
        value: {
            type: String,
            required: false
        },
        with_indeterminate: {
            type: Boolean,
            required: false,
            default: false,
        }
    },
    data () {
        return {
            selected_value: this.value
        };
    },
    watch: {
        selected_value(new_value) {
            this.$emit('selected_value', new_value);
        }
    }
}
</script>
