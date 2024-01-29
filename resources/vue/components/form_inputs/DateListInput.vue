<template>
    <div class="formpart">
        <div class="sr-only" aria-live="polite" ref="list_message_field"></div>
        <ul>
            <li v-for="date in selected_date_list" v-bind="selected_date_list" :key="date">
                <input type="hidden" :name="input_name + '[]'" :value="getISODate(date)">
                <studip-date-time :timestamp="Math.floor(date.getTime() / 1000)" :date_only="true"></studip-date-time>
                <studip-icon shape="trash" :title="$gettext('Löschen')" @click="removeDate"
                             class="enter-accessible" aria-role="button" tabindex="0"></studip-icon>
            </li>
        </ul>
        <label>
            {{ $gettext('Datum') }}
            <div class="flex-row input-with-icon">
                <input type="text" v-model="selected_date_value" ref="date_select_input">
                <studip-icon shape="add" :title="$gettext('Hinzufügen')" @click="addDate"
                             class="icon enter-accessible button undecorated" aria-role="button" tabindex="0"></studip-icon>
            </div>
        </label>
    </div>
</template>

<script>
import StudipDateTime from "../StudipDateTime.vue";
import {$gettext, $gettextInterpolate} from "@/assets/javascripts/lib/gettext";

export default {
    name: "date-list-input",
    components: {StudipDateTime},
    props: {
        name: {
            type: String,
            required: true
        },
        selected_dates: {
            type: Array,
            required: false,
            default: () => [],
        }
    },
    data () {
        return {
            selected_date_value: STUDIP.DateTime.getStudipDate(new Date(), false, true),
            selected_date_list: this.selected_dates.map(date => new Date(date)),
            input_name: this.name,
        };
    },
    mounted() {

        //Set up the datepicker for the date selector input:
        let v = this;
        jQuery(this.$refs.date_select_input).datepicker({
            onSelect: () => {
                this.selected_date_value = this.$refs.date_select_input.value;
                this.addDate();
            },
        });
    },
    watch: {
        selected_date_value(new_value) {
            this.$emit('selected_date_value', new_value);
        },
        selected_date_list: {
            handler (new_value) {
                this.$emit('selected_date_list', new_value);
            },
            deep: true
        }
    },
    methods: {
        addDate() {
            if (this.selected_date_value.length < 8) {
                //Input too short.
                return;
            }
            let date_parts = this.selected_date_value.split('.');
            if (date_parts.length !== 3) {
                //Incorrect input formatting.
                return;
            }
            let reformatted_date = date_parts[2] + '-' + date_parts[1] + '-' + date_parts[0];
            this.selected_date_list.push(new Date(reformatted_date));
            this.$refs.list_message_field.innerText = $gettextInterpolate($gettext('Datum %{date} hinzugefügt'), {date: this.selected_date_value});
        },
        removeDate(date_key) {
            if (date_key) {
                let date = this.selected_date_list.at(date_key);
                let formatted_date = STUDIP.DateTime.getStudipDate(date, false, true);
                this.selected_date_list.splice(date_key, 1);
                this.$refs.list_message_field.innerText = $gettextInterpolate($gettext('Datum %{date} entfernt'), {date: formatted_date});
            }
        },
        getISODate(date) {
            return STUDIP.DateTime.getISODate(date);
        }
    }
}
</script>
