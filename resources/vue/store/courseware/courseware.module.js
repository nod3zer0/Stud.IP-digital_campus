import axios from 'axios';

const getDefaultState = () => {
    return {
        blockAdder: {},
        containerAdder: false,
        consumeMode: false,
        context: {},
        courseware: {},
        currentElement: {},
        oerEnabled: null,
        oerTitle: null,
        licenses: null, // we need a route for License SORM
        httpClient: null,
        lastElement: null,
        msg: 'Dehydrated',
        msgCompanionOverlay:
            'Hallo! Ich bin Ihr persönlicher Companion. Wussten Sie schon, dass Courseware jetzt noch einfacher zu bedienen ist?',
        styleCompanionOverlay: 'default',
        pluginManager: null,
        showCompanionOverlay: false,
        showToolbar: false,
        selectedToolbarItem: 'contents',
        urlHelper: null,
        userId: null,
        viewMode: 'read',
        dashboardViewMode: 'default',
        filingData: {},
        userIsTeacher: false,
        teacherStatusLoaded: false,

        showStructuralElementEditDialog: false,
        showStructuralElementAddDialog: false,
        showStructuralElementExportDialog: false,
        showStructuralElementInfoDialog: false,
        showStructuralElementDeleteDialog: false,
        showStructuralElementOerDialog: false,

        structuralElementSortMode: false,

        importFilesState: '',
        importFilesProgress: 0,
        importStructuresState: '',
        importStructuresProgress: 0,
        importErrors: [],

        exportState: '',
        exportProgress: 0,

        purposeFilter: 'all',
        showOverviewElementAddDialog: false,

        bookmarkFilter: 'all',
    };
};

const initialState = getDefaultState();

const getters = {
    msg(state) {
        return state.msg;
    },
    lastElement(state) {
        return state.lastElement;
    },
    courseware(state) {
        return state.courseware;
    },
    currentElement(state) {
        return state.currentElement;
    },
    oerEnabled(state) {
        return state.oerEnabled;
    },
    oerTitle(state) {
        return state.oerTitle;
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
    filingData(state) {
        return state.filingData;
    },
    showStructuralElementEditDialog(state) {
        return state.showStructuralElementEditDialog;
    },
    showStructuralElementAddDialog(state) {
        return state.showStructuralElementAddDialog;
    },
    showStructuralElementExportDialog(state) {
        return state.showStructuralElementExportDialog;
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
    showOverviewElementAddDialog(state) {
        return state.showOverviewElementAddDialog;
    },
    structuralElementSortMode(state) {
        return state.structuralElementSortMode;
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
    purposeFilter(state) {
        return state.purposeFilter;
    },
    bookmarkFilter(state) {
        return state.bookmarkFilter;
    },
};

export const state = { ...initialState };

export const actions = {
    loadContainer({ dispatch }, containerId) {
        const options = {
            include: 'blocks',
        };

        return dispatch('courseware-containers/loadById', { id: containerId, options }, { root: true });
    },

    loadStructuralElement({ dispatch }, structuralElementId) {
        const options = {
            include:
                'containers,containers.blocks,containers.blocks.editor,containers.blocks.owner,containers.blocks.user-data-field,containers.blocks.user-progress,editor,owner',
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

        for (const activity of activities) {
            //load parents for breadcrumb
            if (activity.type == 'activities') {
                await this.dispatch('courseware-structural-elements/loadById', {
                    id: activity.relationships.object.meta['object-id'],
                });
            }
        }

        return activities;
    },

    async createFile(context, { file, filedata, folder }) {
        const formData = new FormData();
        formData.append('file', filedata, file.attributes.name);

        const url = `folders/${folder.id}/file-refs`;
        let request = await state.httpClient.post(url, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        return state.httpClient.get(request.headers.location).then((response) => {
            return response.data.data;
        });
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

    copyBlock({ getters }, { parentId, block }) {
        const copy = {
            data: {
                block: block,
                parent_id: parentId,
            },
        };

        return state.httpClient.post(`courseware-blocks/${block.id}/copy`, copy).then((resp) => {
            // console.log(resp);
        });
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
    async copyStructuralElement({ dispatch, getters, rootGetters }, { parentId, element, removePurpose }) {
        const copy = { data: { parent_id: parentId, remove_purpose: removePurpose } };

        const result = await state.httpClient.post(`courseware-structural-elements/${element.id}/copy`, copy);
        const id = result.data.data.id;
        await dispatch('loadStructuralElement', id);

        const newElement = rootGetters['courseware-structural-elements/byId']({ id });

        return dispatch('courseware-structure/loadDescendants', { root: newElement });
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

    async storeCoursewareSettings({ dispatch, getters }, { permission, progression }) {
        const courseware = getters.courseware;
        courseware.attributes['editing-permission-level'] = permission;
        courseware.attributes['sequential-progression'] = progression;

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

    oerEnabled(context, enabled) {
        context.commit('oerEnabledSet', enabled);
    },

    oerTitle(context, title) {
        context.commit('oerTitleSet', title);
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

    showElementExportDialog(context, bool) {
        context.commit('setShowStructuralElementExportDialog', bool);
    },

    showElementInfoDialog(context, bool) {
        context.commit('setShowStructuralElementInfoDialog', bool);
    },

    showElementOerDialog(context, bool) {
        context.commit('setShowStructuralElementOerDialog', bool);
    },

    showElementDeleteDialog(context, bool) {
        context.commit('setShowStructuralElementDeleteDialog', bool);
    },

    setShowOverviewElementAddDialog(context, bool) {
        context.commit('setShowOverviewElementAddDialog', bool);
    },

    setStructuralElementSortMode({ commit }, bool) {
        commit('setStructuralElementSortMode', bool);
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

    async deleteImageForStructuralElement({ dispatch, state }, structuralElement) {
        const url = `courseware-structural-elements/${structuralElement.id}/image`;
        await state.httpClient.delete(url);

        return dispatch('loadStructuralElement', structuralElement.id);
    },

    cwManagerFilingData(context, msg) {
        context.commit('cwManagerFilingDataSet', msg);
    },

    async loadRelatedPaginated({ dispatch, rootGetters }, { type, parent, relationship, options }) {
        const limit = 100;
        let offset = 0;

        do {
            const optionsWithPages = {
                ...options,
                'page[offset]': offset,
                'page[limit]': limit,
            };
            await dispatch(
                `${type}/loadRelated`,
                {
                    parent,
                    relationship,
                    options: optionsWithPages,
                    resetRelated: false,
                },
                { root: true }
            );
            offset += limit;
        } while (rootGetters[`${type}/all`].length < rootGetters[`${type}/lastMeta`].page.total);
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

        let courses = [];
        for (let membership of memberships) {
            if (
                (membership.attributes.permission === 'dozent' || membership.attributes.permission === 'tutor') &&
                state.context.id !== membership.relationships.course.data.id
            ) {
                const course = rootGetters['courses/related']({ parent: membership, relationship: 'course' });
                if (!withCourseware) {
                    courses.push(course);
                    continue;
                }
                const coursewareInstance = await dispatch('loadRemoteCoursewareStructure', {
                    rangeId: course.id,
                    rangeType: course.type
                });
                if (coursewareInstance?.relationships?.root) {
                    if (membership.attributes.permission === 'dozent' ||
                        coursewareInstance.attributes['editing-permission-level'] === 'tutor') {
                        courses.push(course);
                    }
                }
            }
        }
        return courses;
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

    loadTeacherStatus({ dispatch, rootGetters, state, commit, getters }, userId) {
        const user = rootGetters['users/byId']({ id: userId });

        if (user.attributes.permission === 'root') {
            commit('setUserIsTeacher', true);
            return;
        }

        const membershipId = `${state.context.id}_${userId}`;

        return dispatch('course-memberships/loadById', { id: membershipId })
            .then(() => {
                const membership = rootGetters['course-memberships/byId']({ id: membershipId });
                const editingLevel = getters.courseware.attributes['editing-permission-level'];
                const membershipPermission = membership.attributes.permission;

                let isTeacher = false;
                if (editingLevel === 'dozent') {
                    isTeacher = membershipPermission === 'dozent';
                } else if (editingLevel === 'tutor') {
                    isTeacher = membershipPermission === 'dozent' || membershipPermission === 'tutor';
                }

                commit('setUserIsTeacher', isTeacher);
            })
            .catch((error) => {
                console.error(`Could not find course membership for ${membershipId}.`);
                commit('setUserIsTeacher', false);
            });
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

    setPurposeFilter({ commit }, purpose) {
        commit('setPurposeFilter', purpose);
    },
    setBookmarkFilter({ commit }, course) {
        commit('setBookmarkFilter', course);
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

    oerEnabledSet(state, data) {
        state.oerEnabled = data;
    },

    oerTitleSet(state, data) {
        state.oerTitle = data;
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

    cwManagerFilingDataSet(state, data) {
        state.filingData = data;
    },

    setShowStructuralElementEditDialog(state, showEdit) {
        state.showStructuralElementEditDialog = showEdit;
    },

    setShowStructuralElementAddDialog(state, showAdd) {
        state.showStructuralElementAddDialog = showAdd;
    },

    setShowStructuralElementExportDialog(state, showExport) {
        state.showStructuralElementExportDialog = showExport;
    },

    setShowStructuralElementInfoDialog(state, showInfo) {
        state.showStructuralElementInfoDialog = showInfo;
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

    setStructuralElementSortMode(state, mode) {
        state.structuralElementSortMode = mode;
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
    setPurposeFilter(state, purpose) {
        state.purposeFilter = purpose;
    },
    setBookmarkFilter(state, course) {
        state.bookmarkFilter = course;
    },
};

export default {
    state,
    actions,
    mutations,
    getters,
};
