<template>
    <form class="cw-tree-item-updater" @submit.prevent="">
        <input type="text" v-model="elementTitle" :placeholder="$gettext('Titel')" />
        <button class="button accept" :title="$gettext('Speichern')" @click="updateElement"></button>
        <button class="button cancel" :title="$gettext('Abbrechen')" @click="close"></button>
    </form>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-tree-item-adder',
    props: {
        structuralElement: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            elementTitle: '',
        };
    },
    computed: {
        ...mapGetters({
            structuralElementById: 'courseware-structural-elements/byId',
            userId: 'userId',
            userById: 'users/byId',
        }),
    },
    methods: {
        ...mapActions({
            loadStructuralElementById: 'courseware-structural-elements/loadById',
            companionError: 'companionError',
            companionInfo: 'companionInfo',

            updateStructuralElement: 'updateStructuralElement',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
            loadUser: 'users/loadById',
        }),
        close() {
            this.$emit('close');
        },
        async updateElement() {
            if (this.elementTitle === '') {
                this.companionInfo({
                    info: this.$gettext('Bitte geben Sie einen Titel für die Seite ein.'),
                });
                return;
            }
            await this.loadStructuralElementById({ id: this.structuralElement.id });
            let element = this.structuralElementById({ id: this.structuralElement.id });
            element.attributes.title = this.elementTitle;
            const blockerData = element?.relationships?.['edit-blocker']?.data;
            const blocked = blockerData !== null && blockerData !== '';
            const blockedByAnotherUser = blocked && blockerData.id !== this.userId;
            if (blockedByAnotherUser) {
                this.close();
                await this.loadUser({ id: blockerData.id });
                const blocker = this.userById({ id: blockerData.id });
                this.companionWarning({
                    info: this.$gettextInterpolate(
                        this.$gettext(
                            'Ihre Änderungen konnten nicht gespeichert werden, da %{blockingUserName} die Bearbeitung übernommen hat.'
                        ),
                        { blockingUserName: blocker.attributes['formatted-name'] }
                    ),
                });
                return;
            }
            if (!this.blocked) {
                await this.lockObject({ id: this.structuralElement.id, type: 'courseware-structural-elements' });
            }

            await this.lockObject({ id: this.structuralElement.id, type: 'courseware-structural-elements' });
            await this.updateStructuralElement({ element, id: this.structuralElement.id });
            await this.unlockObject({ id: this.structuralElement.id, type: 'courseware-structural-elements' });
            this.$emit('childrenUpdated');
            this.close();
        },
        setTitle() {
            this.elementTitle = this.structuralElement.attributes.title;
        },
    },
    mounted() {
        this.setTitle();
    },
};
</script>
