<template>
    <div class="cw-welcome-screen">
        <div class="cw-welcome-screen-keyvisual"></div>
        <header>
            {{ $gettext('Willkommen bei Courseware') }}
        </header>
        <div class="cw-welcome-screen-actions">
            <a href="https://hilfe.studip.de/help/5.0/de/Basis.Courseware" target="_blank" class="button">
                {{ $gettext('Mehr über Courseware erfahren') }}
            </a>
            <button class="button" :title="$gettext('Fügt einen Standard-Abschnitt mit einem Text-Block hinzu')" @click="addDefault">
                {{ $gettext('Ersten Inhalt erstellen') }}
            </button>
            <button class="button" @click="addContainer">
                {{ $gettext('Einen Abschnitt auswählen') }}
            </button>
        </div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-welcome-screen',
    computed: {
        ...mapGetters({
            consumeMode: 'consumeMode',
            lastCreatedBlocks: 'courseware-blocks/lastCreated',
            lastCreatedContainers: 'courseware-containers/lastCreated'
        }),
    },
    methods: {
        ...mapActions({
            createContainer: 'createContainer',
            createBlock: 'createBlockInContainer',
            coursewareBlockAdder: 'coursewareBlockAdder',
            companionSuccess: 'companionSuccess',
            updateContainer: 'updateContainer',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',

            coursewareConsumeMode: 'coursewareConsumeMode',
            coursewareContainerAdder: 'coursewareContainerAdder',
            coursewareShowToolbar: 'coursewareShowToolbar'

        }),
        addContainer() {
            this.coursewareConsumeMode(false);
            this.coursewareShowToolbar(true);
            this.$nextTick(() => {
                this.coursewareContainerAdder(true);
            });
        },
        async addDefault() {
            let attributes = {};
            attributes["container-type"] = 'list';
            attributes.payload = {
                colspan: 'full',
                sections: [{ name: 'Liste', icon: '', blocks: [] }],
            };
            await this.createContainer({ structuralElementId: this.$route.params.id, attributes: attributes });
            let newContainer = this.lastCreatedContainers;
            await this.lockObject({ id: newContainer.id, type: 'courseware-containers' });
            await this.createBlock({
                container: newContainer,
                section: 0,
                blockType: 'text',
            });
            this.coursewareConsumeMode(false);
            this.companionSuccess({
                info: this.$gettext('Das Elemente für Ihren ersten Inhalt wurde angelegt.'),
            });
            const newBlock = this.lastCreatedBlocks;
            newContainer.attributes.payload.sections[0].blocks.push(newBlock.id);
            const structuralElementId = this.$route.params.id
            await this.updateContainer({ container: newContainer, structuralElementId: structuralElementId });
            await this.unlockObject({ id: newContainer.id, type: 'courseware-containers' });
            

        }
    }

}
</script>
