<template>
    <section class="cw-block-info">
        <header>{{ $gettext('Informationen') }}</header>
        <div class="cw-block-features-content cw-block-info-content">
            <table class="cw-block-info-table">
                <tr>
                    <td>{{ $gettext('Blockbeschreibung') }}</td>
                    <td><slot name="info" /></td>
                </tr>
                <tr>
                    <td>{{ $gettext('Block wurde erstellt von') }}</td>
                    <td>{{ owner }}</td>
                </tr>
                <tr>
                    <td>{{ $gettext('Block wurde erstellt am') }}:</td>
                    <td><iso-date :date="block.attributes.mkdate" /></td>
                </tr>
                <tr>
                    <td>{{ $gettext('Zuletzt bearbeitet von') }}:</td>
                    <td>{{ editor }}</td>
                </tr>
                <tr>
                    <td>{{ $gettext('Zuletzt bearbeitet am') }}:</td>
                    <td><iso-date :date="block.attributes.chdate" /></td>
                </tr>
            </table>
            <button class="button" @click="$emit('close')">{{ $gettext('Schließen') }}</button>
        </div>
    </section>
</template>

<script>
import IsoDate from './IsoDate.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-block-info',
    components: { IsoDate },
    props: {
        block: Object,
    },
    computed: {
        ...mapGetters({
            relatedUsers: 'users/related',
        }),
        owner() {
            const owner = this.relatedUsers({
                parent: this.block,
                relationship: 'owner',
            });

            return owner?.attributes['formatted-name'] ?? '';
        },

        editor() {
            const editor = this.relatedUsers({
                parent: this.block,
                relationship: 'editor',
            });

            return editor?.attributes['formatted-name'] ?? '';
        },
    },
};
</script>
