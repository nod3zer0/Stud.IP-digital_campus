import { mapActions, mapGetters } from 'vuex';

const containerMixin = {
    computed: {
        ...mapGetters({
            blockById: 'courseware-blocks/byId',
            containerById: 'courseware-containers/byId',
            pluginManager: 'pluginManager',
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
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
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
            // update block container id
            let block = this.blockById({id: data.blockId });
            block.relationships.container.data.id = data.targetContainerId;
            block.attributes.position = data.newPos;
            await this.lockObject({ id: data.blockId, type: 'courseware-blocks' });
            await this.updateBlock({
                block: block,
                containerId: data.targetContainerId,
            });
            await this.unlockObject({ id: data.blockId, type: 'courseware-blocks' });

            // update origin container
            let originContainer = this.containerById({ id: data.originContainerId});
            originContainer.attributes.payload.sections[data.originSectionId].blocks = data.originSectionBlockList;
            await this.lockObject({ id: data.originContainerId, type: 'courseware-containers' });
            await this.updateContainer({
                container: originContainer,
                structuralElementId: originContainer.relationships['structural-element'].data.id,
            });
            await this.unlockObject({ id: data.originContainerId, type: 'courseware-containers' });

            // update target container
            let targetContainer = this.containerById({ id: data.targetContainerId});
            targetContainer.attributes.payload.sections[data.targetSectionId].blocks = data.targetSectionBlockList;
            await this.lockObject({ id: data.targetContainerId, type: 'courseware-containers' });
            await this.updateContainer({
                container: targetContainer,
                structuralElementId: targetContainer.relationships['structural-element'].data.id,
            });
            await this.unlockObject({ id: data.targetContainerId, type: 'courseware-containers' });

            this.loadContainer({id : data.originContainerId });
            this.loadContainer({id : data.targetContainerId });
        },
    }
};

export default containerMixin;
