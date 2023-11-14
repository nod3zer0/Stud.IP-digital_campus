<template>
    <li class="cw-tree-item cw-tree-item-adder">
        <div class="cw-tree-item-wrapper">
            <form v-if="showForm" class="cw-tree-item-adder-form" @submit.prevent="">
                <input type="text" v-model="elementTitle" :placeholder="$gettext('Titel')" />
                <button class="button accept" :title="$gettext('Seite erstellen')" @click="createElement"></button>
                <button class="button cancel" :title="$gettext('Abbrechen')" @click="closeForm"></button>
            </form>
            <button class="add-element" v-else :title="$gettext('Seite hinzufügen')" @click="showForm = true">
                <studip-icon shape="add" />
            </button>
        </div>
    </li>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-tree-item-adder',
    props: {
        parentId: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            showForm: false,
            elementTitle: '',
        };
    },
    computed: {
        ...mapGetters({
            lastCreatedStructuralElement: 'courseware-structural-elements/lastCreated',
            structuralElementById: 'courseware-structural-elements/byId',
            currentElement: 'currentElement',
        }),
    },
    methods: {
        ...mapActions({
            createStructuralElementWithTemplate: 'createStructuralElementWithTemplate',
            loadStructuralElementById: 'courseware-structural-elements/loadById',
            companionError: 'companionError',
            companionInfo: 'companionInfo',
        }),
        closeForm() {
            this.showForm = false;
            this.elementTitle = '';
        },
        async createElement() {
            this.elementTitle = this.elementTitle.trim();
            if (this.elementTitle === '') {
                this.companionInfo({ info: this.$gettext('Bitte geben Sie einen Titel für die neue Seite ein.') });

                return;
            }

            const element = {
                attributes: {
                    title: this.elementTitle,
                    purpose: 'content',
                    payload: {
                        description: '',
                        color: 'studip-blue',
                        license_type: '',
                        required_time: '',
                        difficulty_start: '',
                        difficulty_end: '',
                    },
                },
                templateId: null,
                parentId: this.parentId,
                currentId: this.currentElement,
            };

            this.closeForm();

            try {
                await this.createStructuralElementWithTemplate(element);
            } catch (e) {
                let errorMessage = this.$gettext(
                    'Es ist ein Fehler aufgetreten. Die Seite konnte nicht erstellt werden.'
                );
                if (e.status === 403) {
                    errorMessage = this.$gettext(
                        'Die Seite konnte nicht erstellt werden. Sie haben nicht die notwendigen Schreibrechte.'
                    );
                }

                this.companionError({ info: errorMessage });
                return;
            }

            const newCreated = this.lastCreatedStructuralElement;
            await this.loadStructuralElementById({ id: newCreated.id });
            const newElement = this.structuralElementById({ id: newCreated.id });

            this.$router.push(newElement.id);
        },
    },
};
</script>

<style scoped lang="scss">
.cw-tree-root-list > .cw-tree-item.cw-tree-item-adder > .cw-tree-item-wrapper {
    border-bottom: none;
}
.cw-tree-item-adder {
    .add-element {
        border: none;
        cursor: pointer;
        background-color: transparent;
        height: 28px;
        img {
            vertical-align: middle;
        }
    }
}
</style>
