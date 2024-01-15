import axios from 'axios';

const getDefaultState = () => {
    return {
        blockAdder: {},
        containerAdder: false,
        consumeMode: false,
        context: {},
        courseware: {},
        currentElement: {},
        licenses: null, // we need a route for License SORM
        httpClient: null,
        lastElement: null,
        msg: 'Dehydrated',
        msgCompanionOverlay: '',
        styleCompanionOverlay: 'default',
        pluginManager: null,
        showCompanionOverlay: false,
        showToolbar: false,
        selectedToolbarItem: 'contents',
        urlHelper: null,
        userId: null,
        viewMode: 'read',
        dashboardViewMode: 'default',
        userIsTeacher: false,
        teacherStatusLoaded: false,

        showStructuralElementEditDialog: false,
        showStructuralElementAddDialog: false,
        showStructuralElementAddChooserDialog: false,
        showStructuralElementImportDialog: false,
        showStructuralElementCopyDialog: false,
        showStructuralElementLinkDialog: false,
        showStructuralElementExportDialog: false,
        showStructuralElementExportChooserDialog: false,
        showStructuralElementPdfExportDialog: false,
        showStructuralElementInfoDialog: false,
        showStructuralElementDeleteDialog: false,
        showStructuralElementOerDialog: false,
        showStructuralElementPublicLinkDialog: false,
        showStructuralElementRemoveLockDialog: false,
        showStructuralElementFeedbackDialog: false,
        showStructuralElementFeedbackCreateDialog: false,

        showSuggestOerDialog: false,

        importFilesState: '',
        importFilesProgress: 0,
        importStructuresState: '',
        importStructuresProgress: 0,
        importErrors: [],

        exportState: '',
        exportProgress: 0,

        permissionFilter: 'read',
        purposeFilter: 'all',
        sourceFilter: 'all',
        showOverviewElementAddDialog: false,

        bookmarkFilter: 'all',

        showSearchResults: false,
        searchResults: [],

        assistiveLiveContents: '',
        progresses: null,

        toolbarActive: true,
        feedbackSettings: null,
        processing: false,
    };
};

const initialState = getDefaultState();

const getters = {
    msg(state) {
        return state.msg;
    },
    currentUser(state, getters, rootState, rootGetters) {
        const id = getters.userId;
        return rootGetters['users/byId']({ id });
    },
    lastElement(state) {
        return state.lastElement;
    },
    courseware(state) {
        return state.courseware;
    },
    rootLayout(state, getters) {
        return getters.courseware.attributes['root-layout'];
    },
    showRootElement(state, getters) {
        return getters.rootLayout !== 'none';
    },
    rootId(state, getters) {
        return getters.courseware?.relationships?.root?.data?.id;
    },
    currentElement(state) {
        return state.currentElement;
    },
    currentStructuralElement(state, getters, rootState, rootGetters) {
        const id = getters.currentElement;
        return rootGetters['courseware-structural-elements/byId']({ id });
    },
    currentElementBlocked(state, getters, rootState, rootGetters) {
        const elemData = getters.currentStructuralElement?.relationships?.['edit-blocker']?.data;
        return elemData !== null && elemData !== '';
    },
    currentElementBlockerId(state, getters) {
        return getters.currentElementBlocked ? getters.currentStructuralElement?.relationships?.['edit-blocker']?.data?.id : null;
    },
    currentElementBlockedByThisUser(state, getters) {
        return getters.currentElementBlocked && getters.userId === getters.currentElementBlockerId;
    },
    currentElementBlockedByAnotherUser(state, getters) {
        return getters.currentElementBlocked && getters.userId !== getters.currentElementBlockerId;
    },
    currentElementisLink(state, getters, rootState, rootGetters) {
        return getters.currentStructuralElement?.attributes?.['is-link'] === 1;
    },
    currentStructuralElementImageURL(state, getters) {
        return getters.currentStructuralElement?.relationships?.image?.meta?.['download-url'];
    },
    licenses(state) {
        return state.licenses;
    },
    context(state) {
        return state.context;
    },
    blockTypes(state) {
        return state.courseware?.attributes?.['block-types'] ?? [];
    },
    containerTypes(state) {
        return state.courseware?.attributes?.['container-types'] ?? [];
    },
    favoriteBlockTypes(state) {
        const allBlockTypes = state.courseware?.attributes?.['block-types'] ?? [];
        const favorites = state.courseware?.attributes?.['favorite-block-types'] ?? [];

        return allBlockTypes.filter(({ type }) => favorites.includes(type));
    },
    viewMode(state) {
        return state.viewMode;
    },
    dashboardViewMode(state) {
        return state.dashboardViewMode;
    },
    showToolbar(state) {
        return state.showToolbar;
    },
    selectedToolbarItem(state) {
        return state.selectedToolbarItem;
    },
    blockAdder(state) {
        return state.blockAdder;
    },
    containerAdder(state) {
        return state.containerAdder;
    },
    showCompanionOverlay(state) {
        return state.showCompanionOverlay;
    },
    msgCompanionOverlay(state) {
        return state.msgCompanionOverlay;
    },
    styleCompanionOverlay(state) {
        return state.styleCompanionOverlay;
    },
    consumeMode(state) {
        return state.consumeMode;
    },
    httpClient(state) {
        return state.httpClient;
    },
    urlHelper(state) {
        return state.urlHelper;
    },
    userId(state) {
        return state.userId;
    },
    userIsTeacher(state) {
        return state.userIsTeacher;
    },
    teacherStatusLoaded(state) {
        return state.teacherStatusLoaded;
    },
    pluginManager(state) {
        return state.pluginManager;
    },
    showStructuralElementEditDialog(state) {
        return state.showStructuralElementEditDialog;
    },
    showStructuralElementAddDialog(state) {
        return state.showStructuralElementAddDialog;
    },
    showStructuralElementAddChooserDialog(state) {
        return state.showStructuralElementAddChooserDialog;
    },
    showStructuralElementCopyDialog(state) {
        return state.showStructuralElementCopyDialog;
    },
    showStructuralElementLinkDialog(state) {
        return state.showStructuralElementLinkDialog;
    },
    showStructuralElementImportDialog(state) {
        return state.showStructuralElementImportDialog;
    },
    showStructuralElementExportDialog(state) {
        return state.showStructuralElementExportDialog;
    },
    showStructuralElementExportChooserDialog(state) {
        return state.showStructuralElementExportChooserDialog;
    },
    showStructuralElementPdfExportDialog(state) {
        return state.showStructuralElementPdfExportDialog;
    },
    showStructuralElementInfoDialog(state) {
        return state.showStructuralElementInfoDialog;
    },
    showStructuralElementOerDialog(state) {
        return state.showStructuralElementOerDialog;
    },
    showStructuralElementDeleteDialog(state) {
        return state.showStructuralElementDeleteDialog;
    },
    showStructuralElementPublicLinkDialog(state) {
        return state.showStructuralElementPublicLinkDialog;
    },
    showStructuralElementRemoveLockDialog(state) {
        return state.showStructuralElementRemoveLockDialog;
    },
    showStructuralElementFeedbackDialog(state) {
        return state.showStructuralElementFeedbackDialog;
    },
    showStructuralElementFeedbackCreateDialog(state) {
        return state.showStructuralElementFeedbackCreateDialog;
    },
    showOverviewElementAddDialog(state) {
        return state.showOverviewElementAddDialog;
    },
    showSuggestOerDialog(state) {
        return state.showSuggestOerDialog;
    },
    importFilesState(state) {
        return state.importFilesState;
    },
    importFilesProgress(state) {
        return state.importFilesProgress;
    },
    importStructuresState(state) {
        return state.importStructuresState;
    },
    importStructuresProgress(state) {
        return state.importStructuresProgress;
    },
    importErrors(state) {
        return state.importErrors;
    },
    exportState(state) {
        return state.exportState;
    },
    exportProgress(state) {
        return state.exportProgress;
    },
    permissionFilter(state) {
        return state.permissionFilter;
    },
    purposeFilter(state) {
        return state.purposeFilter;
    },
    sourceFilter(state) {
        return state.sourceFilter;
    },
    bookmarkFilter(state) {
        return state.bookmarkFilter;
    },
    showSearchResults(state) {
        return state.showSearchResults;
    },
    searchResults(state) {
        return state.searchResults;
    },
    assistiveLiveContents(state) {
        return state.assistiveLiveContents;
    },
    progresses(state) {
        return state.progresses;
    },
    processing(state) {
        return state.processing;
    },

    oerCampusEnabled(state, getters, rootState, rootGetters) {
        return rootGetters['studip-properties/byId']({ id: 'oer-campus-enabled'}).attributes?.value;
    },
    oerEnableSuggestions(state, getters, rootState, rootGetters) {
        return getters.oerCampusEnabled && rootGetters['studip-properties/byId']({ id: 'oer-enable-suggestions'}).attributes?.value;
    },

    toolbarActive(state) {
        return state.toolbarActive;
    },
    feedbackSettings(state) {
        return state.feedbackSettings;
    },
    isFeedbackActivated(state, getters) {
        return getters.feedbackSettings?.activated ?? false;
    },
    canCreateFeedbackElement(state, getters) {
        return getters.feedbackSettings?.createPerm ?? false;
    },
    canEditFeedbackElement(state, getters) {
        return getters.feedbackSettings?.adminPerm ?? false;
    },
};

export const state = { ...initialState };

export const actions = {
    loadContainer({ dispatch }, containerId) {
        const options = {
            include: 'blocks,blocks.edit-blocker','fields[users]': 'formatted-name',
        };

        return dispatch('courseware-containers/loadById', { id: containerId, options }, { root: true });
    },

    loadStructuralElement({ dispatch }, structuralElementId) {
        const options = {
            include:
                'containers,containers.edit-blocker,containers.blocks,containers.blocks.editor,containers.blocks.owner,containers.blocks.user-data-field,containers.blocks.user-progress,containers.blocks.edit-blocker,editor,edit-blocker,owner',
            'fields[users]': 'formatted-name',
        };

        return dispatch(
            'courseware-structural-elements/loadById',
            { id: structuralElementId, options },
            { root: true }
        );
    },

    loadFileRefs({ dispatch, rootGetters }, block_id) {
        const parent = {
            type: 'courseware-blocks',
            id: block_id,
        };
        const relationship = 'file-refs';

        return dispatch('file-refs/loadRelated', { parent, relationship }, { root: true }).then(() =>
            rootGetters['file-refs/related']({
                parent,
                relationship,
            })
        );
    },

    async loadUserDataFields({ dispatch }, blockId) {
        const parent = { type: 'courseware-blocks', id: `${blockId}` };
        const relationship = 'user-data-field';
        const options = {
            include: 'user',
        };

        return dispatch('user-data-field/loadRelated', { parent, relationship, options }, { root: true });
    },

    async loadCoursewareActivities({ dispatch, rootGetters }, { userId, courseId }) {
        const parent = {
            type: 'users',
            id: userId,
        };
        const relationship = 'activitystream';

        const options = {
            'filter[context-type]': 'course',
            'filter[context-id]': courseId,
            'filter[object-type]': 'courseware',
            include: 'actor, context, object',
        };

        await dispatch('users/loadRelated', { parent, relationship, options }, { root: true });

        const activities = rootGetters['users/all'];

        return activities.filter(({ type }) => type === 'activities');
    },

    async createFile(context, { file, filedata, folder }) {
        const termId = file?.relationships?.['terms-of-use']?.data?.id ?? null;
        const formData = new FormData();
        formData.append('file', filedata, file.attributes.name);
        if (termId) {
            formData.append('term-id', termId);
        }
        const url = `folders/${folder.id}/file-refs`;
        let request = await state.httpClient.post(url, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        let response = null;
        try {
            response = await state.httpClient.get(request.headers.location);
        }
        catch(e) {
            console.debug(e);
            response = null;
        }

        return response ? response.data.data : response;
    },

    async updateFileContent(context, { file, filedata }) {
        const url = `file-refs/${file.id}/content`;
        const formData = new FormData();
        formData.append('file', filedata, file.attributes.name);
        let request = await state.httpClient.post(url, formData);
        let response = null;
        try {
            response = await state.httpClient.get(request.headers.location);
        }
        catch(e) {
            console.debug(e);
            response = null;
        }

        return response ? response.data.data : response;
    },

    async createRootFolder({ dispatch, rootGetters }, { context, folder }) {
        // get root folder for this context
        await dispatch(
            `${context.type}/loadRelated`,
            {
                parent: context,
                relationship: 'folders',
            },
            { root: true }
        );

        let folders = await rootGetters[`${context.type}/related`]({
            parent: context,
            relationship: 'folders',
        });

        let rootFolder = null;

        for (let i = 0; i < folders.length; i++) {
            if (folders[i].attributes['folder-type'] === 'RootFolder') {
                rootFolder = folders[i];
            }
        }

        const newFolder = {
            data: {
                type: 'folders',
                attributes: {
                    name: folder.name,
                    'folder-type': 'StandardFolder',
                },
                relationships: {
                    parent: {
                        data: {
                            type: 'folders',
                            id: rootFolder.id,
                        },
                    },
                },
            },
        };

        return state.httpClient.post(`${context.type}/${context.id}/folders`, newFolder).then((response) => {
            return response.data.data;
        });
    },

    async createFolder(store, { context, parent, folder }) {
        const newFolder = {
            data: {
                type: 'folders',
                attributes: {
                    name: folder.name,
                    'folder-type': folder.type,
                },
                relationships: {
                    parent: parent,
                },
            },
        };

        return state.httpClient.post(`${context.type}/${context.id}/folders`, newFolder).then((response) => {
            return response.data.data;
        });
    },

    loadFolder({ dispatch }, folderId) {
        const options = {};

        return dispatch('folders/loadById', { id: folderId, options }, { root: true });
    },

    copyBlock({ getters }, { parentId, block, section }) {
        const copy = {
            data: {
                block: block,
                parent_id: parentId,
                section: section
            },
        };

        return state.httpClient.post(`courseware-blocks/${block.id}/copy`, copy).then((resp) => {
            // console.log(resp);
        });
    },
    clipboardInsertBlock({ getters }, { parentId, clipboard, section }) {
        const insert = {
            data: {
                parent_id: parentId,
                section: section
            },
        };

        return state.httpClient.post(`courseware-clipboards/${clipboard.id}/insert`, insert);
    },
    copyContainer({ getters }, { parentId, container }) {
        const copy = {
            data: {
                container: container,
                parent_id: parentId,
            },
        };

        return state.httpClient.post(`courseware-containers/${container.id}/copy`, copy).then((resp) => {
            // console.log(resp);
        });
    },
    clipboardInsertContainer({ getters },{ parentId, clipboard }) {
        const insert = {
            data: {
                parent_id: parentId,
            },
        };

        return state.httpClient.post(`courseware-clipboards/${clipboard.id}/insert`, insert);
    },
    async copyStructuralElement({ dispatch, getters, rootGetters }, { parentId, elementId, removePurpose, migrate, modifications }) {
        const copy = { data: { parent_id: parentId, remove_purpose: removePurpose, migrate: migrate, modifications: modifications } };

        const result = await state.httpClient.post(`courseware-structural-elements/${elementId}/copy`, copy);
        const id = result.data.data.id;
        await dispatch('loadStructuralElement', id);

        const newElement = rootGetters['courseware-structural-elements/byId']({ id });

        return dispatch('courseware-structure/loadDescendants', { root: newElement });
    },

    async linkStructuralElement({ dispatch, getters, rootGetters }, { parentId, elementId }) {
        const link = { data: { parent_id: parentId } };

        const result = await state.httpClient.post(`courseware-structural-elements/${elementId}/link`, link);
        const id = result.data.data.id;
        await dispatch('loadStructuralElement', id);

        const newElement = rootGetters['courseware-structural-elements/byId']({ id });

        return dispatch('courseware-structure/loadDescendants', { root: newElement });

    },

    async activateStructuralElementComments({ dispatch }, { element }) {

        element.attributes.commentable = true;

        const updatedElement =  await dispatch('setStructuralElementComments', { element: element });
        
        return updatedElement;

    },
    async deactivateStructuralElementComments({ dispatch }, { element }) {

        element.attributes.commentable = false;

        const updatedElement =  await dispatch('setStructuralElementComments', { element: element });
        
        return updatedElement;
    },

    async setStructuralElementComments({ dispatch }, { element }) {
        await dispatch('lockObject', { id: element.id, type: 'courseware-structural-elements' });
        const updatedElement =  await dispatch('courseware-structural-elements/update', element, { root: true });
        await dispatch('unlockObject', { id: element.id, type: 'courseware-structural-elements' });

        return updatedElement;
    },

    async createBlockInContainer({ dispatch }, { container, blockType }) {
        const block = {
            attributes: {
                'block-type': blockType,
                payload: null,
            },
            relationships: {
                container: {
                    data: { type: container.type, id: container.id },
                },
            },
        };
        await dispatch('courseware-blocks/create', block, { root: true });

        return dispatch('loadContainer', container.id);
    },

    async deleteBlockInContainer({ dispatch }, { containerId, blockId }) {
        const data = {
            id: blockId,
        };
        await dispatch('courseware-blocks/delete', data, { root: true });
        //TODO throws TypeError: block is undefined after delete
        return dispatch('loadContainer', containerId);
    },

    async updateBlockInContainer({ dispatch }, { attributes, blockId, containerId }) {
        const container = {
            type: 'courseware-containers',
            id: containerId,
        };
        const block = {
            type: 'courseware-blocks',
            attributes: attributes,
            id: blockId,
            relationships: {
                container: {
                    data: { type: container.type, id: container.id },
                },
            },
        };

        await dispatch('courseware-blocks/update', block, { root: true });
        await dispatch('unlockObject', { id: blockId, type: 'courseware-blocks' });

        return dispatch('loadContainer', containerId);
    },

    async updateBlock({ dispatch }, { block, containerId }) {
        const container = {
            type: 'courseware-containers',
            id: containerId,
        };
        const updateBlock = {
            type: 'courseware-blocks',
            attributes: block.attributes,
            id: block.id,
            relationships: {
                container: {
                    data: { type: container.type, id: container.id },
                },
            },
        };
        await dispatch('courseware-blocks/update', updateBlock, { root: true });

        return dispatch('loadContainer', containerId);
    },

    async deleteBlock({ dispatch }, { containerId, blockId }) {
        const data = {
            id: blockId,
        };
        await dispatch('courseware-blocks/delete', data, { root: true });
        //TODO throws TypeError: block is undefined after delete
        return dispatch('loadContainer', containerId);
    },

    async activateBlockComments({ dispatch }, { block }) {

        block.attributes.commentable = true;

        const updatedBlock =  await dispatch('setBlockComments', { block: block });
        
        return updatedBlock;

    },
    async deactivateBlockComments({ dispatch }, { block }) {

        block.attributes.commentable = false;

        const updatedBlock =  await dispatch('setBlockComments', { block: block });
        
        return updatedBlock;
    },

    async setBlockComments({ dispatch }, { block }) {
        await dispatch('lockObject', { id: block.id, type: 'courseware-blocks' });
        const updatedBlock =  await dispatch('courseware-blocks/update', block, { root: true });
        await dispatch('unlockObject', { id: block.id, type: 'courseware-blocks' });

        return updatedBlock;
    },

    async storeCoursewareSettings({ dispatch, getters },
                                  { permission, progression, certificateSettings, reminderSettings,
                                      resetProgressSettings }) {
        const courseware = getters.courseware;
        courseware.attributes['editing-permission-level'] = permission;
        courseware.attributes['sequential-progression'] = progression;
        courseware.attributes['certificate-settings'] = certificateSettings;
        courseware.attributes['reminder-settings'] = reminderSettings;
        courseware.attributes['reset-progress-settings'] = resetProgressSettings;

        return dispatch('courseware-instances/update', courseware, { root: true });
    },

    sortChildrenInStructualElements({ dispatch }, { parent, children }) {
        const childrenResourceIdentifiers = children.map(({ type, id }) => ({ type, id }));

        return dispatch(
            `courseware-structural-elements/setRelated`,
            {
                parent: { type: parent.type, id: parent.id },
                relationship: 'children',
                data: childrenResourceIdentifiers,
            },
            { root: true }
        ).then(() => dispatch(
            `${parent.type}/loadRelated`,
            {
                parent: { type: parent.type, id: parent.id },
                relationship: 'children',
            },
            { root: true }
        )).then(() => dispatch('courseware-structure/build', null, { root: true }));
    },

    async createStructuralElement({ dispatch }, { attributes, parentId, currentId }) {
        const data = {
            attributes,
            relationships: {
                parent: {
                    data: {
                        type: 'courseware-structural-elements',
                        id: parentId,
                    },
                },
            },
        };
        await dispatch('courseware-structural-elements/create', data, { root: true });

        return dispatch('loadStructuralElement', currentId);
    },

    async createStructuralElementWithTemplate({ dispatch }, { attributes, parentId, currentId, templateId }) {
        const data = {
            attributes,
            relationships: {
                parent: {
                    data: {
                        type: 'courseware-structural-elements',
                        id: parentId,
                    },
                },
            },
            templateId: templateId,
        };
        await dispatch('courseware-structural-elements/create', data, { root: true });

        const options = {
            include: 'children',
        };

        return dispatch('courseware-structural-elements/loadById', { id: currentId, options }, { root: true });
    },

    async deleteStructuralElement({ dispatch }, { id, parentId }) {
        const data = {
            id: id,
        };
        await dispatch('courseware-structural-elements/delete', data, { root: true });
        return dispatch('loadStructuralElement', parentId);
    },

    async updateStructuralElement({ dispatch }, { element, id }) {
        await dispatch('courseware-structural-elements/update', element, { root: true });

        return dispatch('loadStructuralElement', id);
    },

    sortContainersInStructualElements({ dispatch }, { structuralElement, containers }) {
        const containerResourceIdentifiers = containers.map(({ type, id }) => ({ type, id }));

        return dispatch(
            `courseware-structural-elements/setRelated`,
            {
                parent: { type: structuralElement.type, id: structuralElement.id },
                relationship: 'containers',
                data: containerResourceIdentifiers,
            },
            { root: true }
        );
    },

    async createContainer({ dispatch }, { attributes, structuralElementId }) {
        const data = {
            attributes,
            relationships: {
                'structural-element': {
                    data: {
                        type: 'courseware-structural-elements',
                        id: structuralElementId,
                    },
                },
            },
        };
        await dispatch('courseware-containers/create', data, { root: true });

        return dispatch('loadStructuralElement', structuralElementId);
    },

    async deleteContainer({ dispatch }, { containerId, structuralElementId }) {
        const data = {
            id: containerId,
        };
        await dispatch('courseware-containers/delete', data, { root: true });
        //TODO throws TypeError: container is undefined after delete
        return dispatch('loadStructuralElement', structuralElementId);
    },

    async updateContainer({ dispatch }, { container, structuralElementId }) {
        await dispatch('courseware-containers/update', container, { root: true });

        return dispatch('loadStructuralElement', structuralElementId);
    },

    sortBlocksInContainer({ dispatch }, { container, sections }) {
        let blockResourceIdentifiers = [];

        sections.forEach((section) => {
            blockResourceIdentifiers.push(...section.blocks.map(({ type, id }) => ({ type, id })));
        });

        return dispatch(
            `courseware-containers/setRelated`,
            {
                parent: { type: container.type, id: container.id },
                relationship: 'blocks',
                data: blockResourceIdentifiers,
            },
            { root: true }
        );
    },

    lockObject({ dispatch, getters }, { id, type }) {
        return dispatch(`${type}/setRelated`, {
            parent: { id, type },
            relationship: 'edit-blocker',
            data: {
                type: 'users',
                id: getters.userId,
            },
        });
    },

    unlockObject({ dispatch }, { id, type }) {
        return dispatch(`${type}/setRelated`, {
            parent: { id, type },
            relationship: 'edit-blocker',
            data: null,
        });
    },

    async companionInfo({ dispatch }, { info }) {
        await dispatch('coursewareStyleCompanionOverlay', 'default');
        await dispatch('coursewareMsgCompanionOverlay', info);
        return dispatch('coursewareShowCompanionOverlay', true);
    },

    async companionSuccess({ dispatch }, { info }) {
        await dispatch('coursewareStyleCompanionOverlay', 'happy');
        await dispatch('coursewareMsgCompanionOverlay', info);
        return dispatch('coursewareShowCompanionOverlay', true);
    },

    async companionError({ dispatch }, { info }) {
        await dispatch('coursewareStyleCompanionOverlay', 'sad');
        await dispatch('coursewareMsgCompanionOverlay', info);
        return dispatch('coursewareShowCompanionOverlay', true);
    },

    async companionWarning({ dispatch }, { info }) {
        await dispatch('coursewareStyleCompanionOverlay', 'alert');
        await dispatch('coursewareMsgCompanionOverlay', info);
        return dispatch('coursewareShowCompanionOverlay', true);
    },

    async companionSpecial({ dispatch }, { info }) {
        await dispatch('coursewareStyleCompanionOverlay', 'special');
        await dispatch('coursewareMsgCompanionOverlay', info);
        return dispatch('coursewareShowCompanionOverlay', true);
    },

    // adds a favorite block type using the `type` of the BlockType
    async addFavoriteBlockType({ dispatch, getters }, blockType) {
        const blockTypes = new Set(getters.favoriteBlockTypes.map(({ type }) => type));
        blockTypes.add(blockType);

        return dispatch('storeFavoriteBlockTypes', [...blockTypes]);
    },

    // removes a favorite block type using the `type` of the BlockType
    async removeFavoriteBlockType({ dispatch, getters }, blockType) {
        const blockTypes = new Set(getters.favoriteBlockTypes.map(({ type }) => type));
        blockTypes.delete(blockType);

        return dispatch('storeFavoriteBlockTypes', [...blockTypes]);
    },

    // sets the favorite block types using an array of the `type`s of those BlockTypes
    async storeFavoriteBlockTypes({ dispatch, getters }, favoriteBlockTypes) {
        const courseware = getters.courseware;
        courseware.attributes['favorite-block-types'] = favoriteBlockTypes;

        return dispatch('courseware-instances/update', courseware, { root: true });
    },

    coursewareCurrentElement(context, id) {
        context.commit('coursewareCurrentElementSet', id);
    },

    coursewareContext(context, id) {
        context.commit('coursewareContextSet', id);
    },

    licenses(context, licenses) {
        context.commit('licensesSet', licenses);
    },

    coursewareViewMode(context, view) {
        context.commit('coursewareViewModeSet', view);
    },

    setDashboardViewMode(context, view) {
        context.commit('setDashboardViewMode', view);
    },

    coursewareShowToolbar(context, toolbar) {
        context.commit('coursewareShowToolbarSet', toolbar);
    },

    coursewareSelectedToolbarItem(context, item) {
        context.commit('coursewareSelectedToolbarItemSet', item);
    },

    coursewareBlockAdder(context, adder) {
        context.commit('coursewareBlockAdderSet', adder);
    },

    coursewareContainerAdder(context, adder) {
        context.commit('coursewareContainerAdderSet', adder);
    },

    coursewareShowCompanionOverlay(context, companion_overlay) {
        context.commit('coursewareShowCompanionOverlaySet', companion_overlay);
    },

    coursewareMsgCompanionOverlay(context, companion_overlay_msg) {
        context.commit('coursewareMsgCompanionOverlaySet', companion_overlay_msg);
    },

    coursewareStyleCompanionOverlay(context, companion_overlay_style) {
        context.commit('coursewareStyleCompanionOverlaySet', companion_overlay_style);
    },

    coursewareConsumeMode(context, mode) {
        context.commit('coursewareConsumeModeSet', mode);
    },

    setHttpClient({ commit }, httpClient) {
        commit('setHttpClient', httpClient);
    },

    setUrlHelper({ commit }, urlHelper) {
        commit('setUrlHelper', urlHelper);
    },

    setUserId({ commit }, userId) {
        commit('setUserId', userId);
    },

    showElementEditDialog(context, bool) {
        context.commit('setShowStructuralElementEditDialog', bool);
    },

    showElementAddDialog(context, bool) {
        context.commit('setShowStructuralElementAddDialog', bool);
    },

    showElementAddChooserDialog(context, bool) {
        context.commit('setShowStructuralElementAddChooserDialog', bool);
    },

    showElementImportDialog(context, bool) {
        context.commit('setShowStructuralElementImportDialog', bool);
    },

    showElementCopyDialog(context, bool) {
        context.commit('setShowStructuralElementCopyDialog', bool);
    },

    showElementLinkDialog(context, bool) {
        context.commit('setShowStructuralElementLinkDialog', bool);
    },

    showElementExportDialog(context, bool) {
        context.commit('setShowStructuralElementExportDialog', bool);
    },

    showElementExportChooserDialog(context, bool) {
        context.commit('setShowStructuralElementExportChooserDialog', bool);
    },

    showElementPdfExportDialog(context, bool) {
        context.commit('setShowStructuralElementPdfExportDialog', bool);
    },

    showElementInfoDialog(context, bool) {
        context.commit('setShowStructuralElementInfoDialog', bool);
    },

    showElementOerDialog(context, bool) {
        context.commit('setShowStructuralElementOerDialog', bool);
    },

    updateShowSuggestOerDialog(context, bool) {
        context.commit('setShowSuggestOerDialog', bool);
    },

    showElementDeleteDialog(context, bool) {
        context.commit('setShowStructuralElementDeleteDialog', bool);
    },

    showElementPublicLinkDialog(context, bool) {
        context.commit('setShowStructuralElementPublicLinkDialog', bool);
    },

    showElementRemoveLockDialog(context, bool) {
        context.commit('setShowStructuralElementRemoveLockDialog', bool);
    },

    showStructuralElementFeedbackDialog(context, bool) {
        context.commit('setShowStructuralElementFeedbackDialog', bool);
    },
    showStructuralElementFeedbackCreateDialog(context, bool) {
        context.commit('setShowStructuralElementFeedbackCreateDialog', bool);
    },

    setShowOverviewElementAddDialog(context, bool) {
        context.commit('setShowOverviewElementAddDialog', bool);
    },

    setImportFilesState({ commit }, state) {
        commit('setImportFilesState', state);
    },
    setImportFilesProgress({ commit }, percent) {
        commit('setImportFilesProgress', percent);
    },
    setImportStructuresState({ commit }, state) {
        commit('setImportStructuresState', state);
    },
    setImportStructuresProgress({ commit }, percent) {
        commit('setImportStructuresProgress', percent);
    },
    setImportErrors({ commit }, errors) {
        commit('setImportErrors', errors);
    },

    setExportState({ commit }, state) {
        commit('setExportState', state);
    },
    setExportProgress({ commit }, percent) {
        commit('setExportProgress', percent);
    },

    setShowSearchResults({ commit }, state) {
        commit('setShowSearchResults', state);
    },

    setSearchResults({ commit }, state) {
        commit('setSearchResults', state);
    },
    setAssistiveLiveContents({ commit }, state) {
        commit('setAssistiveLiveContents', state);
    },

    addBookmark({ dispatch, rootGetters }, structuralElement) {
        const cw = rootGetters['courseware'];

        // get existing bookmarks
        const bookmarks =
            rootGetters['courseware-structural-elements/related']({
                parent: cw,
                relationship: 'bookmarks',
            })?.map(({ type, id }) => ({ type, id })) ?? [];

        // add a new bookmark
        const data = [...bookmarks, { type: structuralElement.type, id: structuralElement.id }];

        // send them home
        return dispatch(
            `courseware-structural-elements/setRelated`,
            {
                parent: { type: cw.type, id: cw.id },
                relationship: 'bookmarks',
                data,
            },
            { root: true }
        );
    },

    removeBookmark({ dispatch, rootGetters }, structuralElement) {
        const cw = rootGetters['courseware'];

        // get existing bookmarks
        const bookmarks =
            rootGetters['courseware-structural-elements/related']({
                parent: cw,
                relationship: 'bookmarks',
            })?.map(({ type, id }) => ({ type, id })) ?? [];

        // filter bookmark that must be removed
        const data = bookmarks.filter(({ id }) => id !== structuralElement.id);

        // send them home
        return dispatch(
            `courseware-structural-elements/setRelated`,
            {
                parent: { type: cw.type, id: cw.id },
                relationship: 'bookmarks',
                data,
            },
            { root: true }
        );
    },

    setPluginManager({ commit }, pluginManager) {
        commit('setPluginManager', pluginManager);
    },

    uploadImageForStructuralElement({ dispatch, state }, { structuralElement, file }) {
        const formData = new FormData();
        formData.append('image', file);

        const url = `courseware-structural-elements/${structuralElement.id}/image`;
        return state.httpClient.post(url, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
    },

    setStockImageForStructuralElement({ dispatch, state }, { structuralElement, stockImage }) {
        const { id, type } = structuralElement;
        structuralElement.relationships.image = { data: { type: 'stock-images', id: stockImage.id } };

        return dispatch('lockObject', { id, type })
            .then(() => dispatch('updateStructuralElement', { element: structuralElement, id }))
            .then(() => dispatch('lockObject', { id, type }));
    },

    async deleteImageForStructuralElement({ dispatch, state }, structuralElement) {
        const url = `courseware-structural-elements/${structuralElement.id}/image`;
        await state.httpClient.delete(url);

        return dispatch('loadStructuralElement', structuralElement.id);
    },

    async loadRelatedPaginated({ dispatch, rootGetters }, { type, parent, relationship, options }) {
        const limit = 100;
        let offset = 0;

        await loadPage(offset, limit);
        const total = rootGetters[`${type}/lastMeta`].page.total;

        const pages = [];
        for (let page = 1; page * limit < total; page++) {
            pages.push(loadPage(page * limit, limit));
        }

        return Promise.all(pages);

        function loadPage(offset, limit) {
            return dispatch(
                `${type}/loadRelated`,
                {
                    parent,
                    relationship,
                    options: {
                        ...options,
                        'page[offset]': offset,
                        'page[limit]': limit,
                    },
                    resetRelated: false,
                },
                { root: true }
            )
        }
    },

    loadUsersBookmarks({ dispatch, rootGetters, state }, userId) {
        const parent = {
            type: 'users',
            id: userId,
        };
        const relationship = 'courseware-bookmarks';
        const options = {
            include: 'course',
        };

        return dispatch('loadRelatedPaginated', {
            type: 'courseware-structural-elements',
            parent,
            relationship,
            options,
        });
    },

    async loadUsersCourses({ dispatch, rootGetters, state }, { userId, withCourseware }) {
        const parent = {
            type: 'users',
            id: userId,
        };
        const relationship = 'course-memberships';
        const options = {
            include: 'course',
        };
        await dispatch('loadRelatedPaginated', {
            type: 'course-memberships',
            parent,
            relationship,
            options,
        });

        const memberships = rootGetters['course-memberships/related']({
            parent,
            relationship,
        });

        const otherMemberships = memberships.filter(({ attributes, relationships }) => {
            return ['dozent', 'tutor'].includes(attributes.permission);
        });

        if (!withCourseware) {
            return otherMemberships.map((membership) => {
                return getCourse(membership);
            });
        }

        const items = otherMemberships.map((membership) => {
            let course = getCourse(membership);
            course['userPermission'] = membership.attributes.permission;

            return { membership, course };
        });

         return items
            .filter(({ membership, course }) => {
                return course.relationships.courseware.data;
            })
            .map(({ course }) => course);

        function getCourse(membership) {
            return rootGetters['courses/related']({ parent: membership, relationship: 'course' });
        }
    },

    async loadRemoteCoursewareStructure({ dispatch, rootGetters }, { rangeId, rangeType }) {
        const parent = {
            id: rangeId,
            type: rangeType,
        };

        const relationship = 'courseware';

        return dispatch(`courseware-instances/loadRelated`, { parent, relationship }, { root: true }).then(
            (response) => {
                const instance = rootGetters['courseware-instances/related']({
                    parent: parent,
                    relationship: relationship,
                });

                return instance;
            },
            (error) => {
                return null;
            }
        );
    },

    async loadTeacherStatus({ dispatch, rootGetters, state, commit, getters }, userId) {
        const user = rootGetters['users/byId']({ id: userId });

        if (user.attributes.permission === 'root') {
            commit('setUserIsTeacher', true);
            return;
        }
        if (user.attributes.permission === 'admin') {
            await dispatch('courses/loadById', { id: state.context.id });
            const course = rootGetters['courses/byId']({id: state.context.id });
            const instituteId = course.relationships.institute.data.id;

            const parent = { type: 'users', id: `${userId}` };
            const relationship = 'institute-memberships';
            const options = {};
            await dispatch('institute-memberships/loadRelated', { parent, relationship, options }, { root: true });
            const instituteMemberships = rootGetters['institute-memberships/all'];
            const instituteMembership = instituteMemberships.filter(membership => membership.relationships.institute.data.id === instituteId);

            if (instituteMembership.length > 0 && instituteMembership[0].attributes.permission === 'admin') {
                commit('setUserIsTeacher', true);
                return;
            }
        }

        const membershipId = `${state.context.id}_${userId}`;
        try {
            await dispatch('course-memberships/loadById', { id: membershipId });
        } catch (error) {
            console.error(`Could not find course membership for ${membershipId}.`);
            commit('setUserIsTeacher', false);

            return false;
        }
        const membership = rootGetters['course-memberships/byId']({ id: membershipId });
        if (membership) {
            let editingLevel = 'tutor';
            if (getters.courseware.attributes) {
                editingLevel = getters.courseware.attributes['editing-permission-level'];
            }
            const membershipPermission = membership.attributes.permission;

            let isTeacher = false;
            if (editingLevel === 'dozent') {
                isTeacher = membershipPermission === 'dozent';
            } else if (editingLevel === 'tutor') {
                isTeacher = membershipPermission === 'dozent' || membershipPermission === 'tutor';
            }
            commit('setUserIsTeacher', isTeacher);

            return true;
        } else {
            console.error(`Could not find course membership for ${membershipId}.`);
            commit('setUserIsTeacher', false);

            return false;
        }
    },

    loadFeedback({ dispatch }, blockId) {
        const parent = { type: 'courseware-blocks', id: `${blockId}` };
        const relationship = 'feedback';
        const options = {
            include: 'user',
        };

        return dispatch('courseware-block-feedback/loadRelated', { parent, relationship, options }, { root: true });
    },

    async createFeedback({ dispatch }, { blockId, feedback }) {
        const data = {
            attributes: {
                feedback,
            },
            relationships: {
                block: {
                    data: {
                        type: 'courseware-blocks',
                        id: blockId,
                    },
                },
            },
        };
        await dispatch('courseware-block-feedback/create', data, { root: true });

        return dispatch('loadFeedback', blockId);
    },

    async createTaskGroup({ dispatch, rootGetters }, { taskGroup }) {
        await dispatch('courseware-task-groups/create', taskGroup, { root: true });

        const id = taskGroup.relationships.target.data.id;
        const target = rootGetters['courseware-structural-elements/byId']({ id });

        return dispatch('courseware-structure/loadDescendants', { root: target });
    },

    async loadTask({ dispatch }, { taskId }) {
        return dispatch(
            'courseware-tasks/loadById',
            {
                id: taskId,
                options: {
                    include: 'solver,task-group,task-group.lecturer',
                },
            },
            { root: true }
        ).catch(error => console.debug(error));
    },

    async updateTask({ dispatch }, { attributes, taskId }) {
        const task = {
            type: 'courseware-tasks',
            attributes: attributes,
            id: taskId,
        };
        await dispatch('courseware-tasks/update', task, { root: true });

        return dispatch('loadTask', { taskId: task.id });
    },

    async deleteTask({ dispatch }, { task }) {
        const data = {
            id: task.id,
        };
        await dispatch('courseware-tasks/delete', data, { root: true });
    },

    async createTaskFeedback({ dispatch }, { taskFeedback }) {
        await dispatch('courseware-task-feedback/create', taskFeedback, { root: true });

        return dispatch('loadTask', { taskId: taskFeedback.relationships.task.data.id });
    },

    async updateTaskFeedback({ dispatch }, { attributes, taskFeedbackId }) {
        const taskFeedback = {
            type: 'courseware-task-feedback',
            attributes: attributes,
            id: taskFeedbackId,
        };
        await dispatch('courseware-task-feedback/update', taskFeedback, { root: true });

        return dispatch('courseware-task-feedback/loadById', { id: taskFeedback.id }, { root: true });
    },

    async deleteTaskFeedback({ dispatch }, { taskFeedbackId }) {
        const data = {
            id: taskFeedbackId,
        };
        await dispatch('courseware-task-feedback/delete', data, { root: true });
    },

    setPermissionFilter({ commit }, permission) {
        commit('setPermissionFilter', permission);
    },
    setPurposeFilter({ commit }, purpose) {
        commit('setPurposeFilter', purpose);
    },
    setSourceFilter({ commit }, source) {
        commit('setSourceFilter', source);
    },
    setBookmarkFilter({ commit }, course) {
        commit('setBookmarkFilter', course);
    },

    setProcessing({ commit }, processing) {
        commit('setProcessing', processing);
    },

    createLink({ dispatch, rootGetters }, { publicLink }) {
        dispatch('courseware-public-links/create', publicLink, { root: true });
    },

    deleteLink({ dispatch }, { linkId }) {
        const data = {
            id: linkId,
        };
        dispatch('courseware-public-links/delete', data, { root: true });
    },

    async updateLink({ dispatch }, { attributes, linkId }) {
        const link = {
            type: 'courseware-public-links',
            attributes: attributes,
            id: linkId,
        };
        await dispatch('courseware-public-links/update', link, { root: true });

        return dispatch('courseware-public-links/loadById', { id: link.id }, { root: true });
    },

    loadCourseUnits({ dispatch }, cid) {
        const parent = { type: 'courses', id: cid };
        const relationship = 'courseware-units';
        const options = { include: 'structural-element' }

        return dispatch('loadRelatedPaginated', {
            type: 'courseware-units',
            parent,
            relationship,
            options,
        });
    },
    loadUserUnits({ dispatch }, uid) {
        const parent = { type: 'users', id: uid };
        const relationship = 'courseware-units';
        const options = { include: 'structural-element' }

        return dispatch('loadRelatedPaginated', {
            type: 'courseware-units',
            parent,
            relationship,
            options,
        });
    },
    async loadUnitProgresses({ getters }, { unitId }) {
        const response = await state.httpClient.get(`courseware-units/${unitId}/courseware-user-progresses`);
        if (response.status === 200) {
           return response.data;
        } else {
           return null;
        }
    },
    async loadProgresses({ dispatch, commit, getters }) {
        const progresses = await dispatch('loadUnitProgresses', { unitId: getters.context.unit });
        commit('setProgresses', progresses);
    },

    loadUserClipboards({ dispatch }, uid) {
        dispatch('courseware-clipboards/resetState');
        const parent = { type: 'users', id: uid };
        const relationship = 'courseware-clipboards';
        const options = {}

        return dispatch('loadRelatedPaginated', {
            type: 'courseware-clipboards',
            parent,
            relationship,
            options,
        });
    },

    async deleteUserClipboards({ dispatch, rootGetters }, { uid, type }) {
        await state.httpClient.delete(`users/${uid}/courseware-clipboards/${type}`);
        dispatch('loadUserClipboards', uid);
    },

    toggleToolbarActive({ commit, rootGetters }) {
        commit('setToolbarActive', !rootGetters['toolbarActive']);
    },
    setFeedbackSettings(context, feedbackSettings) {
        context.commit('setFeedbackSettings', feedbackSettings);
    },
};

/* eslint no-param-reassign: ["error", { "props": false }] */
export const mutations = {
    coursewareSet(state, data) {
        state.courseware = data;
    },

    coursewareCurrentElementSet(state, data) {
        state.lastElement = state.currentElement;
        state.currentElement = data;
    },

    coursewareContextSet(state, data) {
        state.context = data;
    },

    licensesSet(state, data) {
        state.licenses = data;
    },

    coursewareViewModeSet(state, data) {
        state.viewMode = data;
    },

    setDashboardViewMode(state, data) {
        state.dashboardViewMode = data;
    },

    coursewareShowToolbarSet(state, data) {
        state.showToolbar = data;
    },

    coursewareSelectedToolbarItemSet(state, data) {
        state.selectedToolbarItem = data;
    },

    coursewareBlockAdderSet(state, data) {
        state.blockAdder = data;
    },

    coursewareContainerAdderSet(state, data) {
        state.containerAdder = data;
    },

    coursewareShowCompanionOverlaySet(state, data) {
        state.showCompanionOverlay = data;
    },

    coursewareMsgCompanionOverlaySet(state, data) {
        state.msgCompanionOverlay = data;
    },

    coursewareStyleCompanionOverlaySet(state, data) {
        state.styleCompanionOverlay = data;
    },

    coursewareConsumeModeSet(state, data) {
        state.consumeMode = data;
    },

    setHttpClient(state, httpClient) {
        state.httpClient = httpClient;
    },

    setUrlHelper(state, urlHelper) {
        state.urlHelper = urlHelper;
    },

    setUserId(state, userId) {
        state.userId = userId;
    },

    setUserIsTeacher(state, isTeacher) {
        state.teacherStatusLoaded = true;
        state.userIsTeacher = isTeacher;
    },

    setPluginManager(state, pluginManager) {
        state.pluginManager = pluginManager;
    },

    setShowStructuralElementEditDialog(state, showEdit) {
        state.showStructuralElementEditDialog = showEdit;
    },

    setShowStructuralElementAddDialog(state, showAdd) {
        state.showStructuralElementAddDialog = showAdd;
    },

    setShowStructuralElementAddChooserDialog(state, showAddChooser) {
        state.showStructuralElementAddChooserDialog = showAddChooser;
    },

    setShowStructuralElementImportDialog(state, showImport) {
        state.showStructuralElementImportDialog = showImport;
    },

    setShowStructuralElementCopyDialog(state, showCopy) {
        state.showStructuralElementCopyDialog = showCopy;
    },

    setShowStructuralElementLinkDialog(state, showLink) {
        state.showStructuralElementLinkDialog = showLink;
    },

    setShowStructuralElementExportDialog(state, showExport) {
        state.showStructuralElementExportDialog = showExport;
    },

    setShowStructuralElementExportChooserDialog(state, showExportChooser) {
        state.showStructuralElementExportChooserDialog = showExportChooser;
    },

    setShowStructuralElementPdfExportDialog(state, showPdfExport) {
        state.showStructuralElementPdfExportDialog = showPdfExport;
    },

    setShowStructuralElementInfoDialog(state, showInfo) {
        state.showStructuralElementInfoDialog = showInfo;
    },

    setShowSuggestOerDialog(state, showSuggest) {
        state.showSuggestOerDialog = showSuggest;
    },

    setShowStructuralElementOerDialog(state, showOer) {
        state.showStructuralElementOerDialog = showOer;
    },

    setShowStructuralElementDeleteDialog(state, showDelete) {
        state.showStructuralElementDeleteDialog = showDelete;
    },

    setShowOverviewElementAddDialog(state, showAdd) {
        state.showOverviewElementAddDialog = showAdd;
    },

    setShowStructuralElementPublicLinkDialog(state, showPublicLink) {
        state.showStructuralElementPublicLinkDialog = showPublicLink;
    },

    setShowStructuralElementRemoveLockDialog(state, showRemoveLock) {
        state.showStructuralElementRemoveLockDialog = showRemoveLock;
    },

    setShowStructuralElementFeedbackDialog(state, showFeedback) {
        state.showStructuralElementFeedbackDialog = showFeedback;
    },
    setShowStructuralElementFeedbackCreateDialog(state, showFeedbackCreate) {
        state.showStructuralElementFeedbackCreateDialog = showFeedbackCreate;
    },

    setImportFilesState(state, importFilesState) {
        state.importFilesState = importFilesState;
    },

    setImportFilesProgress(state, importFilesProgress) {
        state.importFilesProgress = importFilesProgress;
    },
    setImportErrors(state, importErrors) {
        state.importErrors = importErrors;
    },

    setImportStructuresState(state, importStructuresState) {
        state.importStructuresState = importStructuresState;
    },

    setImportStructuresProgress(state, importStructuresProgress) {
        state.importStructuresProgress = importStructuresProgress;
    },

    setExportState(state, exportState) {
        state.exportState = exportState;
    },
    setExportProgress(state, exportProgress) {
        state.exportProgress = exportProgress;
    },
    setPermissionFilter(state, permission) {
        state.permissionFilter = permission;
    },
    setPurposeFilter(state, purpose) {
        state.purposeFilter = purpose;
    },
    setSourceFilter(state, source) {
        state.sourceFilter = source;
    },
    setBookmarkFilter(state, course) {
        state.bookmarkFilter = course;
    },
    setShowSearchResults(state, searchState) {
        state.showSearchResults = searchState;
    },
    setSearchResults(state, results) {
        state.searchResults = results;
    },
    setAssistiveLiveContents(state, text) {
        state.assistiveLiveContents = text;
    },
    setProgresses(state, data) {
        state.progresses = data;
    },
    setToolbarActive(state, active) {
        state.toolbarActive = active;
    },
    setFeedbackSettings(state, feedbackSettings) {
        state.feedbackSettings = feedbackSettings;
    },
    setProcessing(state, processing) {
        state.processing = processing;
    }
};

export default {
    state,
    actions,
    mutations,
    getters,
};
