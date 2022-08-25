/* eslint-disable no-await-in-loop */
import { mapActions, mapGetters } from 'vuex';
import JSZip from 'jszip';
import FileSaver from 'file-saver';
import axios from 'axios';

export default {
    computed: {
        ...mapGetters({
            courseware: 'courseware',
            containerById: 'courseware-containers/byId',
            folderById: 'folders/byId',
            filesById: 'files/byId',
            structuralElementById: 'courseware-structural-elements/byId',
        }),
    },

    data() {
        return {
            exportFiles: {
                json: [],
                download: [],
            },
            elementCounter: 0,
            exportElementCounter: 0,
        };
    },

    methods: {
        initData() {
            this.exportFiles = { json: [], download: [] };
            this.elementCounter = 0;
            this.exportElementCounter = 0;
        },
        async sendExportZip(root_id = null, options) {
            this.initData();
            let view = this;
            let zip = await this.createExportFile(root_id, options);
            this.setExportState(this.$gettext('Erstelle Zip-Archiv'));
            this.setExportProgress(0);
            await zip.generateAsync({ type: 'blob' }, function updateCallback(metadata) {
                view.setExportProgress(metadata.percent.toFixed(0));
            }).then(function (content) {
                view.setExportState('');
                view.setExportProgress(0);
                FileSaver.saveAs(content, 'courseware-export-' + new Date().toISOString().slice(0, 10) + '.zip');
            });
        },

        async createExportFile(root_id = null, options) {
            let completeExport = false;

            if (!root_id) {
                root_id = this.courseware.relationships.root.data.id;
                completeExport = true;
            }
            this.setExportState(this.$gettext('Exportiere Elemente'));
            this.setExportProgress(0);
            let exportData = await this.exportCourseware(root_id, options);

            let zip = new JSZip();
            zip.file('courseware.json', JSON.stringify(exportData.json));
            zip.file('files.json', JSON.stringify(exportData.files.json));

            if (completeExport) {
                zip.file('settings.json', JSON.stringify(exportData.settings));
            }

            // add all additional files from blocks
            let i = 1;
            let filesCounter = Object.keys(exportData.files.download).length;
            this.setExportState(this.$gettext('Lade Dateien'));
            this.setExportProgress(0);
            for (let id in exportData.files.download) {
                zip.file(
                    id,
                    await fetch(exportData.files.download[id].url)
                        .then((response) => response.blob())
                        .then((textString) => {
                            return textString;
                        })
                );
                this.setExportProgress(parseInt(i / filesCounter * 100));
                i++;
            }

            return zip;
        },

        async exportCourseware(root_id, options) {
            let withChildren = false;

            if (options && options.withChildren === true) {
                withChildren = true;
            }

            let root_element = await this.structuralElementById({id: root_id});

            //prevent loss of data
            root_element = JSON.parse(JSON.stringify(root_element));

            // load whole courseware nonetheless, only export relevant elements
            let elements = await this.$store.getters['courseware-structural-elements/all'];
            this.exportElementCounter = 0;
            if (withChildren) {
                this.elementCounter = this.countElements(elements);
            } else {
                this.elementCounter = root_element.relationships.containers.length;
            }

            root_element.containers = [];
            if (root_element.relationships.containers?.data?.length) {
                for (var j = 0; j < root_element.relationships.containers.data.length; j++) {
                    root_element.containers.push(
                        await this.exportContainer(
                            this.containerById({
                                id: root_element.relationships.containers.data[j].id,
                            })
                        )
                    );
                    this.exportElementCounter++;
                }
            }

            if (withChildren && elements !== []) {
                let children = await this.exportStructuralElement(root_id, elements);

                if (children.length) {
                    root_element.children = children;
                }
            }
            root_element.imageId = await this.exportStructuralElementImage(root_element);

            delete root_element.relationships;
            delete root_element.links;

            let settings = {
                'editing-permission-level': this.courseware.attributes['editing-permission-level'],
                'sequential-progression': this.courseware.attributes['sequential-progression']
            };

            return {
                json: root_element,
                files: this.exportFiles,
                settings: settings
            };
        },

        countElements(elements) {
            let counter = 0;
            for (var i = 0; i < elements.length; i++) {
                counter++;
                counter += elements[i].relationships.containers.data.length;
            }

            return counter;
        },

        async exportToOER(element, options) {
            let formData = new FormData();

            let exportZip = await this.createExportFile(element.id, options);
            let zip = await exportZip.generateAsync({ type: 'blob' });

            let description = element.attributes.payload.description ? element.attributes.payload.description : '';
            let difficulty_start = element.attributes.payload.difficulty_start ? element.attributes.payload.difficulty_start : '1';
            let difficulty_end = element.attributes.payload.difficulty_end ? element.attributes.payload.difficulty_end : '12';

            if (element.relationships.image.data !== null) {
                let image = {};
                await axios.get(element.relationships.image.meta['download-url'] , {responseType: 'blob'}).then(response => { image = response.data });
                formData.append("image", image);
            }

            formData.append("data[name]", element.attributes.title);
            formData.append("tags[]", "Lernmaterial");
            formData.append("file", zip, (element.attributes.title).replace(/\s+/g, '_') + '.zip');
            formData.append("data[description]", description);
            formData.append("data[difficulty_start]", difficulty_start);
            formData.append("data[difficulty_end]", difficulty_end);
            formData.append("data[category]", 'elearning');

            axios({
                method: 'post',
                url: STUDIP.URLHelper.getURL('dispatch.php/oer/mymaterial/edit/'),
                data: formData,
                headers: { "Content-Type": "multipart/form-data"}
            }).then( () => {
                this.companionInfo({ info: this.$gettext('Die Seite wurde an den OER Campus gesendet.') });
            }).catch(error => {
                this.companionError({ info: this.$gettext('Beim Veröffentlichen der Seite ist ein Fehler aufgetreten.') });
                console.debug(error);
            });
        },

        async exportStructuralElement(parentId, data) {
            let children = [];

            for (var i = 0; i < data.length; i++) {
                if (data[i].relationships.parent.data?.id === parentId && data[i].attributes['can-edit']) {
                    let new_childs = await this.exportStructuralElement(data[i].id, data);
                    this.exportElementCounter++;
                    let content = { ...data[i] };
                    content.containers = [];

                    await this.loadStructuralElement(content.id);

                    let element = this.structuralElementById({ id: content.id });

                    // load containers, if there are any for this struct
                    if (element.relationships.containers?.data?.length) {
                        for (var j = 0; j < element.relationships.containers.data.length; j++) {
                            content.containers.push(
                                await this.exportContainer(
                                    this.containerById({
                                        id: element.relationships.containers.data[j].id,
                                    })
                                )
                            );
                            this.exportElementCounter++;
                        }
                    }

                    // export file data (if any)
                    content.imageId = await this.exportStructuralElementImage(element);

                    delete content.relationships;
                    content.children = new_childs;

                    children.push(content);
                }
            }

            return children;
        },

        async exportStructuralElementImage(element) {
            let fileId = element.relationships.image?.data?.id;
            if (fileId) {
                await this.$store.dispatch('file-refs/loadById', {id: fileId});
                let fileRef = this.$store.getters['file-refs/byId']({id: fileId});
                
                let fileRefData = {};
                fileRefData.id = fileRef.id;
                fileRefData.attributes = fileRef.attributes;
                fileRefData.related_element_id = element.id;
                fileRefData.folder = null;

                this.exportFiles.json.push(fileRefData);
                this.exportFiles.download[fileRef.id] = {
                    folder: null,
                    url: fileRef.meta['download-url']
                };
            }

            return fileId;
        },

        async exportContainer(container_ref) {
            // make a local copy of the container
            let container = { ...container_ref };

            container.blocks = [];

            let blocks = this.$store.getters['courseware-blocks/all'];

            // now, load the blocks for this container, if there are any
            if (blocks.length) {
                for (var k = 0; k < blocks.length; k++) {
                    if (blocks[k].relationships.container?.data.id === container.id) {
                        container.blocks.push(await this.exportBlock(blocks[k]));
                    }
                }
            }

            delete container.relationships;

            return container;
        },

        async exportBlock(block_ref) {
            // make a local copy of the block
            let block = { ...block_ref };

            // export file data (if any)
            if (block_ref.relationships['file-refs']?.links?.related) {
                await this.exportFileRefs(block_ref.id);
            }

            delete block.relationships;

            return block;
        },

        async exportFileRefs(block_id) {
            // load file-ref data
            let refs =  []
            try {
                refs = await this.loadFileRefs(block_id);
            } catch(e) {
                //TODO: Companion explains error
            }

            // add infos to exportFiles JSON
            for (let ref_id in refs) {
                let fileref = {};
                let folderId = refs[ref_id].relationships.parent.data.id;
                let folder = null;
                fileref.attributes = refs[ref_id].attributes;
                fileref.related_block_id = block_id;
                fileref.id = refs[ref_id].id;

                try {
                    await this.loadFolder(folderId);
                    folder = this.folderById({id: folderId});
                } catch(e) {
                    //TODO: Companion explains error
                }

                if (folder) {
                    fileref.folder = {
                        id: folder.id,
                        name: folder.attributes.name,
                        type: folder.attributes['folder-type']
                    }
                } else {
                    fileref.folder = {
                        id: folderId,
                        name: 'Unknown',
                        type: 'StandardFolder'
                    }
                }

                this.exportFiles.json.push(fileref);

                // prevent multiple downloads of the same file
                this.exportFiles.download[refs[ref_id].id] = {
                    folder: folderId,
                    url: refs[ref_id].meta['download-url']
                };
            }
        },

        ...mapActions([
            'loadStructuralElement',
            'loadFileRefs',
            'loadFolder',
            'companionInfo',
            'setExportState',
            'setExportProgress'
        ]),
    },
    watch: {
        exportElementCounter(counter) {
            if (this.elementCounter !== 0) {
                this.setExportProgress(parseInt(counter / this.elementCounter * 100));
            }
        }
    },
};
