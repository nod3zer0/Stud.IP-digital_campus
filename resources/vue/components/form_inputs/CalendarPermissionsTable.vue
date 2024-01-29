<template>
    <div class="formpart">
        <quicksearch v-if="searchtype" :searchtype="searchtype" name="qs" @input="addContact"
                     :placeholder="$gettext('Personen hinzufügen')"></quicksearch>
        <table class="default">
            <caption>{{ $gettext('Kontakte, mit denen der Kalender geteilt wird')}}</caption>
            <thead>
                <tr>
                    <th>{{ $gettext('Name') }}</th>
                    <th>{{ $gettext('Schreibzugriff') }}</th>
                    <th class="actions">{{ $gettext('Nicht mehr teilen') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="this.users.length === 0">
                    <td colspan="3">
                        <studip-message-box type="info">
                            {{ $gettext('Der Kalender wird mit keinem Kontakt geteilt.') }}
                        </studip-message-box>
                    </td>
                </tr>
                <tr v-for="user in this.users" :key="user.id">
                    <td>
                        <input type="hidden" :name="name + '_permissions[]'"
                               :value="user.id">
                        {{ user.name }}
                    </td>
                    <td>
                        <input type="checkbox" :name="name + '_write_permissions[]'" :value="user.id"
                               v-model="user.write_permissions"
                               :aria-label="$gettextInterpolate(
                                   $gettext('Schreibzugriff für %{name}'),
                                   {name: user.name}
                               )">
                    </td>
                    <td class="actions">
                        <studip-icon shape="trash" aria-role="button" @click="removeContact(user.id)"
                                     :title="$gettextInterpolate(
                                         $gettext('Kalender nicht mehr mit %{name} teilen'),
                                         {name: user.name}
                                     )"></studip-icon>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import StudipMessageBox from "../StudipMessageBox.vue";

export default {
    name: "calendar-permissions-table",
    components: {StudipMessageBox},
    props: {
        name: {
            type: String,
            required: true
        },
        selected_users: {
            type: Object,
            required: false,
            default: () => {},
        },
        searchtype: {
            type: String,
            required: true,
        }
    },
    data() {
        return {
            users: {...this.selected_users},
        }
    },
    methods: {
        addContact(user_id, name) {
            this.$set(this.users, user_id, {id: user_id, name: name, write_permissions: false});
        },
        removeContact(user_id) {
            if (this.users[user_id] !== undefined) {
                this.$delete(this.users, user_id);
            }
        }
    }
}
</script>
