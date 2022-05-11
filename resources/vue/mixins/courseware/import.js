/* eslint-disable no-await-in-loop */
import { mapActions, mapGetters } from 'vuex';

export default {
    data() {
        return {
            importFolder: null,
            file_mapping: {},
            elementCounter: 0,
            importElementCounter: 0,
            currentImportErrors: [],
        };
    },

    computed: {
        ...mapGetters({
            context: 'context',
            courseware: 'courseware-instances/all',
            structuralElementById: 'courseware-structural-elements/byId',
        }),
    },

    methods: {
        ...mapActions({
            loadStructuralElementById: 'courseware-structural-elements/loadById',
            updateStructuralElement: 'updateStructuralElement'
        }),

        async importCourseware(element, rootId, files, importBehavior)
        {
            // import all files
            await this.uploadAllFiles(files);

            this.elementCounter = await this.countImportElements([element]);
            this.setImportStructuresState('');
            this.importElementCounter = 0;
            this.setImportErrors([]);

            if (importBehavior === 'default') {
                await this.importStructuralElement([element], rootId, files);
            }
            if (importBehavior === 'migrate') {
                await this.migrateCourseware(element, rootId, files);
            }
        },

        countImportElements(element) {
            let counter = 0;
            if (element.length) {
                for (var i = 0; i < element.length; i++) {
                    counter++;
                    if (element[i].children?.length > 0) {
                        counter += this.countImportElements(element[i].children);
                    }

                    if (element[i].containers?.length > 0) {
                        for (var j = 0; j < element[i].containers.length; j++) {
                            counter++;
                            let container = element[i].containers[j];
                            if (container.blocks?.length) {
                                for (var k = 0; k < container.blocks.length; k++) {
                                    counter++;
                                }
                            }
                        }
                    }
                }
            }

            return counter;
        },

        async migrateCourseware(element, rootId, files) {
            let root = this.structuralElementById({ id: rootId });
            // add containers and blocks
            if (element.containers?.length > 0) {
                await Promise.all(element.containers.map((container) => this.importContainer(container, root, files)));
            }
            //compare payload
            let changedData = false;
            if (root.attributes.title === 'neue Seite') {
                root.attributes.title = element.attributes.title;
                changedData = true;
            }
            if (root.attributes.purpose === '') {
                root.attributes.purpose = element.attributes.purpose;
                changedData = true;
            }
            if (element.attributes.payload) {
                if (!root.attributes.payload.color && element.attributes.payload.color) {
                    root.attributes.payload.color = element.attributes.payload.color;
                    changedData = true;
                }
                if (!root.attributes.payload.description && element.attributes.payload.description) {
                    root.attributes.payload.description = element.attributes.payload.description;
                    changedData = true;
                }
                if (!root.attributes.payload.license_type && element.attributes.payload.license_type) {
                    root.attributes.payload.license_type = element.attributes.payload.license_type;
                    changedData = true;
                }
                if (!root.attributes.payload.required_time && element.attributes.payload.required_time) {
                    root.attributes.payload.required_time = element.attributes.payload.required_time;
                    changedData = true;
                }
                if (!root.attributes.payload.difficulty_start && element.attributes.payload.difficulty_start) {
                    root.attributes.payload.difficulty_start = element.attributes.payload.difficulty_start;
                    changedData = true;
                }
                if (!root.attributes.payload.difficulty_end && element.attributes.payload.difficulty_end) {
                    root.attributes.payload.difficulty_end = element.attributes.payload.difficulty_end;
                    changedData = true;
                }
            }
            if (changedData) {
                await this.lockObject({ id: root.id, type: 'courseware-structural-elements' });
                await this.updateStructuralElement({
                    element: root,
                    id: root.id,
                });
                await this.unlockObject({ id: root.id, type: 'courseware-structural-elements' });
            }
            // compare image
            if (element.imageId && root.relationships.image.data === null) {
                await this.setStructuralElementImage(root, element.imageId, files);
            }

            // add children
            if (element.children) {
                await this.importStructuralElement(element.children, rootId, files);
            }

            this.setImportStructuresProgress(100);
        },

        async importStructuralElement(element, parent_id, files) {
            if (element.length) {
                for (var i = 0; i < element.length; i++) {
                    this.setImportStructuresState(this.$gettext('Lege Seite an:') + ' ' + element[i].attributes.title);
                    try {
                        await this.createStructuralElement({
                            attributes: element[i].attributes,
                            parentId: parent_id,
                            currentId: parent_id,
                        });
                    } catch(error) {
                        this.currentImportErrors.push(this.$gettext('Seite konnte nicht erstellt werden') + ': '
                        + element.attributes.title);

                        continue;
                    }

                    this.importElementCounter++;

                    let new_element = this.$store.getters['courseware-structural-elements/lastCreated'];

                    if (element[i].imageId) {
                        await this.setStructuralElementImage(new_element, element[i].imageId, files);
                    }


                    if (element[i].children?.length > 0) {
                        await this.importStructuralElement(element[i].children, new_element.id, files);
                    }

                    if (element[i].containers?.length > 0) {
                        for (var j = 0; j < element[i].containers.length; j++) {
                            let container = element[i].containers[j];
                            await this.importContainer(container, new_element, files);
                        }
                    }
                }
            }
        },

        async setStructuralElementImage(new_element, imageId, files) {
            let imageFile = files.find((file) => { return file.id === imageId});
            let zip_filedata = await this.zip.file(imageFile.id).async('blob');
            // create new blob with correct type
            let filedata = zip_filedata.slice(0, zip_filedata.size, imageFile.attributes['mime-type']);
            this.setImportStructuresState(this.$gettext('Lade Vorschaubild hoch'));
            this.uploadImageForStructuralElement({
                structuralElement: new_element,
                file: filedata,
            }).catch((error) => {
                console.error(error);
                this.currentImportErrors.push(this.$gettext('Fehler beim Hochladen des Vorschaubildes.'));
            });
        },

        async importContainer(container, structuralElement, files) {
            this.setImportStructuresState(this.$gettext('Lege Abschnitt an:') + ' ' + container.attributes.title);
            try {
                await this.createContainer({
                    attributes: container.attributes,
                    structuralElementId: structuralElement.id,
                });

            } catch(error) {
                this.currentImportErrors.push(this.$gettext('Abschnitt konnte nicht erstellt werden') + ': '
                + structuralElement.attributes.title + '→'
                + container.attributes.title);

                return null;
            }

            this.importElementCounter++;
            let new_container = this.$store.getters['courseware-containers/lastCreated'];
            await this.unlockObject({ id: new_container.id, type: 'courseware-containers' });

            if (container.blocks?.length) {
                let new_block = null;
                for (var k = 0; k < container.blocks.length; k++) {
                    new_block = await this.importBlock(container.blocks[k], new_container, files, structuralElement);
                    if (new_block !== null) {
                        this.importElementCounter++;
                        try {
                            await this.updateContainerPayload(new_container, structuralElement.id, container.blocks[k].id, new_block.id);
                        } catch(error) {
                            this.currentImportErrors.push(this.$gettext('Abschnittdaten sind beschädigt. Möglicherweise werden nicht alle Blöcke dargestellt') + ': '
                            + structuralElement.attributes.title + '→'
                            + container.attributes.title);
                        }
                    }
                }
            }
        },

        async importBlock(block, block_container, files, element) {
            this.setImportStructuresState('Lege neuen Block an: ' + block.attributes.title);
            try {
                await this.createBlockInContainer({
                    container: {type: block_container.type, id: block_container.id},
                    blockType: block.attributes['block-type'],
                });
            } catch(error) {
                this.currentImportErrors.push(this.$gettext('Block konnte nicht erstellt werden') + ': '
                    + element.attributes.title + '→'
                    + block_container.attributes.title + '→'
                    + block.attributes.title);

                return null;
            }

            let new_block = this.$store.getters['courseware-blocks/lastCreated'];

            // update old id ids in payload part
            for (var i = 0; i < files.length; i++) {
                if (files[i].related_block_id === block.id) {
                    let old_file = this.file_mapping[files[i].id].old;
                    let new_file = this.file_mapping[files[i].id].new;
                    let payload = JSON.stringify(block.attributes.payload);

                    payload = payload.replaceAll(old_file.id, new_file.id);
                    payload = payload.replaceAll(old_file.folder.id, new_file.relationships.parent.data.id);

                    block.attributes.payload = JSON.parse(payload);
                }
            }
            this.setImportStructuresState('Aktualisiere neuen Block: ' + block.attributes.title);
            try {
                await this.updateBlockInContainer({
                    attributes: block.attributes,
                    blockId: new_block.id,
                    containerId: block_container.id,
                });
            } catch(error) {

                this.currentImportErrors.push(this.$gettext('Blockdaten sind beschädigt. Es werden die Standardwerte eingesetzt') + ': '
                    + element.attributes.title + '→'
                    + block_container.attributes.title + '→'
                    + block.attributes.title);
                this.unlockObject({ id: new_block.id, type: 'courseware-blocks' });
            }


            return new_block;
        },

        async updateContainerPayload(container, structuralElementId, oldBlockId, newBlockId) {

            container.attributes.payload.sections.forEach((section, index) => {
                let blockIndex = section.blocks.findIndex(blockID => blockID === oldBlockId);

                if(blockIndex > -1) {
                    container.attributes.payload.sections[index].blocks[blockIndex] = newBlockId;
                }
            });

            await this.lockObject({ id: container.id, type: 'courseware-containers' });
            await this.updateContainer({
                container: container,
                structuralElementId: structuralElementId
            });
            await this.unlockObject({ id: container.id, type: 'courseware-containers' });
        },

        async uploadAllFiles(files) {
            let createFolder = false;
            for (let i = 0; i < files.length; i++) {
                if (files[i].folder !== null) {
                    createFolder = true;
                }
            }
            if (files.length === 0 || !createFolder) {
                this.setImportFilesProgress(100);
                this.setImportFilesState('');
                return true;
            }
            // create folder for importing the files into
            this.setImportFilesProgress(0);
            this.setImportFilesState('');
            let now = new Date();
            this.setImportFilesState(this.$gettext('Lege Import Ordner an...'));
            let main_folder = null;
            try {
                main_folder = await this.createRootFolder({
                    context: this.context,
                    folder: {
                        type: 'StandardFolder',
                        name: ' CoursewareImport '
                            + now.toLocaleString('de-DE', { timeZone: 'UTC' })
                            + ' ' + now.getMilliseconds(),
                    }
                });
            } catch(error) {
                this.currentImportErrors.push(this.$gettext('Anlegen des Import-Ordners fehlgeschlagen.'));
            }


            let folders = {};

            // upload all files to the newly created folder
            if (main_folder) {
                for (var i = 0; i < files.length; i++) {
                    // if the subfolder with the referenced id does not exist yet, create it
                    if (!files[i].folder) {
                        continue;
                    }
                    if (!folders[files[i].folder.id]) {
                        this.setImportFilesState(this.$gettext('Lege Ordner an') + ': ' + files[i].folder.name);
                        folders[files[i].folder.id] = await this.createFolder({
                            context: this.context,
                            parent: {
                                data: {
                                    id: main_folder.id,
                                    type: 'folders'
                                }
                            },
                            folder: {
                                type: 'StandardFolder',
                                name: files[i].folder.name
                            }
                        });
                    }

                    // only upload files with the same id once
                    if (this.file_mapping[files[i].id] === undefined) {
                        let zip_filedata = await this.zip.file(files[i].id).async('blob');

                        // create new blob with correct type
                        let filedata = zip_filedata.slice(0, zip_filedata.size, files[i].attributes['mime-type']);

                        let file = await this.createFile({
                            file: files[i],
                            filedata: filedata,
                            folder: folders[files[i].folder.id]
                        });
                        this.setImportFilesState(this.$gettext('Erzeuge Datei') + ': ' + files[i].attributes.name);
                        this.setImportFilesProgress(parseInt(i / files.length * 100));

                        //file mapping
                        this.file_mapping[files[i].id] = {
                            old: files[i],
                            new: file
                        };
                    }
                }
            } else {
                return false;
            }
            this.setImportFilesProgress(100);
            this.setImportFilesState('');

            return true;
        },

        ...mapActions([
            'createBlockInContainer',
            'createContainer',
            'createStructuralElement',
            'updateContainer',
            'updateBlockInContainer',
            'createFolder',
            'createRootFolder',
            'createFile',
            'lockObject',
            'unlockObject',
            'setImportFilesState',
            'setImportFilesProgress',
            'setImportStructuresState',
            'setImportStructuresProgress',
            'setImportErrors',
            'uploadImageForStructuralElement'
        ]),
    },
    watch: {
        importElementCounter(counter) {
            if (this.elementCounter !== 0) {
                this.setImportStructuresProgress(parseInt(counter / this.elementCounter * 100));
            } else {
                this.setImportStructuresProgress(100);
            }
        },
        currentImportErrors(errors) {
            if(errors.length > 0) {
                this.setImportErrors(errors);
            }
        }
    },
};
