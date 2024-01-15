import { mapActions, mapGetters } from 'vuex';

const containerMixin = {
    computed: {
        ...mapGetters({
            blockAdder: 'blockAdder',
            blockById: 'courseware-blocks/byId',
            containerById: 'courseware-containers/byId',
            pluginManager: 'pluginManager',
            lastCreatedBlock: 'courseware-blocks/lastCreated',
            lastCreatedContainers: 'courseware-containers/lastCreated',
            blockedByAnotherUser: 'currentElementBlockedByAnotherUser',
            currentStructuralElement: 'currentStructuralElement',
        }),
    },
    created: function () {
        this.pluginManager.registerComponentsLocally(this);
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlock',
            updateContainer: 'updateContainer',
            loadContainer: 'courseware-containers/loadById',
            loadBlock: 'courseware-blocks/loadById',
            loadStructuralElement: 'loadStructuralElement',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
            createBlock: 'createBlockInContainer',
            createContainer: 'createContainer',
            companionInfo: 'companionInfo',
            companionSuccess: 'companionSuccess',
            companionWarning: 'companionWarning',
            sortContainersInStructualElements: 'sortContainersInStructualElements',
            setAdderStorage: 'coursewareBlockAdder',
            setProcessing: 'setProcessing',
            containerUpdate: 'courseware-containers/update'
        }),
        dropBlock(e) {
            this.isDragging = false; // implemented bei echt container type
            let data = {};
            data.originContainerId = e.from.__vue__.$attrs.containerId;
            data.targetContainerId = e.to.__vue__.$attrs.containerId;
            if (data.originContainerId === data.targetContainerId) {
                this.storeSort(); // implemented bei echt container type
            } else {
                data.originSectionId = e.from.__vue__.$attrs.sectionId;
                data.originSectionBlockList = e.from.__vue__.$children.map(b => { return b.$attrs.blockId; });
                data.targetSectionId = e.to.__vue__.$attrs.sectionId;
                data.targetSectionBlockList = e.to.__vue__.$children.map(b => { return b.$attrs.blockId; });
                data.blockId = e.item._underlying_vm_.id;
                data.newPos = e.newIndex;
                const indexInBlockList = data.targetSectionBlockList.findIndex(b => b === data.blockId);
                data.targetSectionBlockList.splice(data.newPos, 0, data.targetSectionBlockList.splice(indexInBlockList,1));
                this.storeInAnotherContainer(data);
            }
        },
        async storeInAnotherContainer(data) {
            this.setProcessing(true);
            // update origin container
            if (data.originContainerId) {
                await this.lockObject({ id: data.originContainerId, type: 'courseware-containers' });
                await this.loadContainer({ id : data.originContainerId });
                let originContainer = this.containerById({ id: data.originContainerId});
                originContainer.attributes.payload.sections[data.originSectionId].blocks = data.originSectionBlockList;
                await this.containerUpdate(
                    originContainer,
                );
                await this.unlockObject({ id: data.originContainerId, type: 'courseware-containers' });
            }
            // update target container
            await this.lockObject({ id: data.targetContainerId, type: 'courseware-containers' });
            await this.loadContainer({ id : data.targetContainerId });
            let targetContainer = this.containerById({ id: data.targetContainerId});
            targetContainer.attributes.payload.sections[data.targetSectionId].blocks = data.targetSectionBlockList;
            await this.containerUpdate(
                targetContainer,
            );
            await this.unlockObject({ id: data.targetContainerId, type: 'courseware-containers' });
         
            // update block container id
            let block = this.blockById({id: data.blockId });
            block.relationships.container.data.id = data.targetContainerId;
            block.attributes.position = data.newPos;
            await this.lockObject({ id: block.id, type: 'courseware-blocks' });
            await this.updateBlock({
                block: block,
                containerId: data.targetContainerId,
            });
            await this.unlockObject({ id: block.id, type: 'courseware-blocks' });
            await this.loadBlock({ id: block.id });
            await this.loadContainer({ id : data.originContainerId });
            this.setProcessing(false);
        },
        checkSimpleArrayEquality(firstSet, secondSet) {
            return Array.isArray(firstSet) && Array.isArray(secondSet) &&
                firstSet.length === secondSet.length &&
                firstSet.every((val, index) => val === secondSet[index]);
        },
        async addNewBlock() {
            if (this.blockAdder.container) {
                const targetContainer = this.blockAdder.container;
                const section = this.blockAdder.section;
                const type = this.blockAdder.type;
                const position = this.blockAdder.position;

                try {
                    await this.lockObject({ id: targetContainer.id, type: 'courseware-containers' });
                } catch (error) {
                    if (error.status === 409) {
                        this.companionInfo({ info: this.$gettext('Diese Seite wird bereits bearbeitet.') });
                    } else {
                        console.log(error);
                    }
                }
                
                await this.createBlock({
                    container: targetContainer,
                    section: section,
                    blockType: type,
                });
                // get the just created block to add it to a container and adjust its position if applicable
                const newBlock = this.lastCreatedBlock;

                // if the block is dropped to a specific position, save it at the correct position
                if (position !== false) {
                    targetContainer.attributes.payload.sections[section].blocks.splice(
                        position, 0, newBlock.id);
                // otherwise put it in the last position of the last container
                } else {
                    targetContainer.attributes.payload.sections[section].blocks.push(newBlock.id);
                }

                const structuralElementId = targetContainer.relationships['structural-element'].data.id;

                await this.updateContainer({ container: targetContainer, structuralElementId: structuralElementId });
                await this.unlockObject({ id: targetContainer.id, type: 'courseware-containers' });
                this.companionSuccess({
                    info: this.$gettext('Der Block wurde erfolgreich eingefügt.'),
                });
            } else {
                // companion action
                this.companionWarning({
                    info: this.$gettext('Bitte fügen Sie der Seite einen Abschnitt hinzu, damit der Block eingefügt werden kann.'),
                });
            }
        },
        async sortClipboardBlock() {
            const targetContainer = this.blockAdder.container;
            const position = this.blockAdder.position;

            try {
                await this.lockObject({ id: targetContainer.id, type: 'courseware-containers' });
            } catch (error) {
                if (error.status === 409) {
                    this.companionInfo({ info: this.$gettext('Diese Seite wird bereits bearbeitet.') });
                } else {
                    console.log(error);
                }
            }
            const containerBlocks = targetContainer.attributes.payload.sections[this.blockAdder.section].blocks;
            containerBlocks.splice(position, 0, containerBlocks.pop());

            const structuralElementId = targetContainer.relationships['structural-element'].data.id;
            try {
                await this.updateContainer({ container: targetContainer, structuralElementId: structuralElementId });
                await this.unlockObject({ id: targetContainer.id, type: 'courseware-containers' });
            } catch (error) {
                this.companionWarning({
                    info: this.$gettext('Der Block konnte nicht hinzugefügt werden, bitte versuchen Sie es erneut.'),
                });
                console.log(error);
            }
        },
        async addContainer(data) {
            const type = data.type;
            const colspan = data.colspan;
            const firstSection = data.sections.firstSection;
            const secondSection = data.sections.secondSection;

            let attributes = {};
            attributes["container-type"] = type;
            let sections = [];
            if (type === 'list') {
                sections = [{ name: firstSection, icon: '', blocks: [] }];
            } else {
                sections = [{ name: firstSection, icon: '', blocks: [] },{ name: secondSection, icon: '', blocks: [] }];
            }
            attributes.payload = {
                colspan: colspan,
                sections: sections,
            };
            await this.createContainer({ structuralElementId: this.$route.params.id, attributes: attributes });
            this.companionSuccess({
                info: this.$gettext('Der Abschnitt wurde erfolgreich eingefügt.'),
            });

            // if the container was dropped to a specific position, sort it and update the structural element
            if (data.newPosition != null) {
                this.sortContainer(data.newPosition);
            }
        },
        async sortContainer(newContainerPos) {
            if (this.blockedByAnotherUser) {
                this.companionInfo({ info: this.$gettext('Diese Seite wird bereits bearbeitet.') });
                return false;
            }
            try {
                await this.lockObject({ id: this.currentStructuralElement.id, type: 'courseware-structural-elements' });
            } catch (error) {
                if (error.status === 409) {
                    this.companionInfo({ info: this.$gettext('Diese Seite wird bereits bearbeitet.') });
                } else {
                    console.log(error);
                }

                return false;
            }
            // insert the newly created container at the correct position
            let containerList = [];
            this.currentStructuralElement.relationships.containers.data.forEach(container => {
                containerList.push(container);
            });

            if (newContainerPos != null) {
                // find the container with the highest index (= latest addition) because it isn't
                // added at the bottom when it is a clipboard
                const highestIndexContainer = containerList.reduce((previous, current) => {
                    return (previous && parseInt(previous.id) > parseInt(current.id)) ? previous : current;
                }, 0);

                // get the last created container if a new container is added, or
                // the highest index container in the case of a clipboard
                const newestContainer = this.lastCreatedContainers?.id || highestIndexContainer.id;
                const tempPosition = containerList.findIndex(x => x.id === newestContainer);
                const newContainer = containerList.splice(tempPosition, 1)[0];

                if (newContainerPos === 'last') {
                    newContainerPos = containerList.length;
                }
                containerList.splice(newContainerPos, 0, newContainer);
            }
            await this.sortContainersInStructualElements({
                structuralElement: this.currentStructuralElement,
                containers: containerList,
            });
            await this.loadStructuralElement(this.currentStructuralElement.id);

            this.$emit('select', this.currentStructuralElement.id);

            return false;
        },
        resetAdderStorage() {
            // choose the last container and its last section as the default adder slot
            // for adding blocks and containers via click
            if (this.containers) {
                this.setAdderStorage({
                    container: this.containers[this.containers.length - 1],
                    section: this.containers[this.containers.length - 1].attributes.payload.sections.length - 1
                });
            }
        },

    }
};

export default containerMixin;
