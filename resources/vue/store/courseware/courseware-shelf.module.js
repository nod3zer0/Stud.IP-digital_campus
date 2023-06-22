const getDefaultState = () => {
    return {
        context: null,
        httpClient: null,
        showCompanionOverlay: false,
        msgCompanionOverlay: '',
        styleCompanionOverlay: 'default',
        showToolbar: false,
        showUnitAddDialog: false,
        showUnitCopyDialog: false,
        showUnitImportDialog: false,
        showUnitLinkDialog: false,
        licenses: null,
        userId: null,
        exportState: '',
        exportProgress: 0,
        courseware: null,
        userIsTeacher: false,
        urlHelper: null,

        importFilesState: '',
        importFilesProgress: 0,
        importStructuresState: '',
        importStructuresProgress: 0,
        importErrors: [],
    };
};

const initialState = getDefaultState();

const getters = {
    context(state) {
        return state.context;
    },
    httpClient(state) {
        return state.httpClient;
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
    showToolbar(state) {
        return state.showToolbar;
    },
    showUnitAddDialog(state) {
        return state.showUnitAddDialog;
    },
    showUnitCopyDialog(state) {
        return state.showUnitCopyDialog;
    },
    showUnitImportDialog(state) {
        return state.showUnitImportDialog;
    },
    showUnitLinkDialog(state) {
        return state.showUnitLinkDialog;
    },
    licenses(state) {
        return state.licenses;
    },
    userId(state) {
        return state.userId;
    },
    courseware(state) {
        return state.courseware;
    },
    exportState(state) {
        return state.exportState;
    },
    exportProgress(state) {
        return state.exportProgress;
    },
    userIsTeacher(state) {
        return state.userIsTeacher;
    },
    urlHelper(state) {
        return state.urlHelper;
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
};

export const state = { ...initialState };

export const actions = {
    // setters
    setContext({ commit }, context) {
        commit('setContext', context);
    },
    setHttpClient({ commit }, httpClient) {
        commit('setHttpClient', httpClient);
    },
    setShowUnitAddDialog({ commit }, show) {
        commit('setShowUnitAddDialog', show);
    },
    setShowUnitCopyDialog({ commit }, show) {
        commit('setShowUnitCopyDialog', show);
    },
    setShowUnitImportDialog({ commit }, show) {
        commit('setShowUnitImportDialog', show);
    },
    setShowUnitLinkDialog({ commit }, show) {
        commit('setShowUnitLinkDialog', show);
    },
    setLicenses({ commit }, licenses) {
        commit('setLicenses', licenses);
    },
    setShowCompanionOverlay(context, companionOverlay) {
        context.commit('setShowCompanionOverlay', companionOverlay);
    },
    setMsgCompanionOverlay(context, companionOverlayMsg) {
        context.commit('setMsgCompanionOverlay', companionOverlayMsg);
    },
    setStyleCompanionOverlay(context, companionOverlayStyle) {
        context.commit('setStyleCompanionOverlay', companionOverlayStyle);
    },
    setUserId(context, id) {
        context.commit('setUserId', id);
    },
    setExportState(context, state) {
        context.commit('setExportState', state);
    },
    setExportProgress(context, percent) {
        context.commit('setExportProgress', percent);
    },
    setUrlHelper(context, urlHelper) {
        context.commit('setUrlHelper', urlHelper);
    },

    // other actions
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
    async companionInfo({ dispatch }, { info }) {
        await dispatch('setStyleCompanionOverlay', 'default');
        await dispatch('setMsgCompanionOverlay', info);
        return dispatch('setShowCompanionOverlay', true);
    },

    async companionSuccess({ dispatch }, { info }) {
        await dispatch('setStyleCompanionOverlay', 'happy');
        await dispatch('setMsgCompanionOverlay', info);
        return dispatch('setShowCompanionOverlay', true);
    },

    async companionError({ dispatch }, { info }) {
        await dispatch('setStyleCompanionOverlay', 'sad');
        await dispatch('setMsgCompanionOverlay', info);
        return dispatch('setShowCompanionOverlay', true);
    },

    async companionWarning({ dispatch }, { info }) {
        await dispatch('setStyleCompanionOverlay', 'alert');
        await dispatch('setMsgCompanionOverlay', info);
        return dispatch('setShowCompanionOverlay', true);
    },

    async companionSpecial({ dispatch }, { info }) {
        await dispatch('setStyleCompanionOverlay', 'special');
        await dispatch('setMsgCompanionOverlay', info);
        return dispatch('setShowCompanionOverlay', true);
    },
    coursewareShowCompanionOverlay({dispatch}, { data }) {
        return dispatch('setShowCompanionOverlay', data);
    },

    async deleteUnit({ dispatch, state }, data) {
        await dispatch('courseware-units/delete', data, { root: true });
        if (state.context.type === 'courses') {
            return dispatch('loadCourseUnits', state.context.id);
        }
        if (state.context.type === 'users') {
            return dispatch('loadUserUnits', state.context.id);
        }
    },

    async copyUnit({ dispatch, state }, { unitId, modified }) {
        let rangeType = null;
        let loadUnits = null;
        if (state.context.type === 'courses') {
            rangeType = 'course';
            loadUnits = 'loadCourseUnits';
        }
        if (state.context.type === 'users') {
            rangeType = 'user';
            loadUnits = 'loadUserUnits';
        }
        if(!rangeType)  {
            return false;
        }
        const copy = { data: { rangeId: state.context.id, rangeType: rangeType, modified: modified } };
        await state.httpClient.post(`courseware-units/${unitId}/copy`, copy);

        return dispatch(loadUnits, state.context.id);
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
                return course.relationships.courseware;
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
    loadInstance({ commit, dispatch, rootGetters }, context) {
        const parent = {
                type: context.type,
                id: context.id + '_' + context.unit
        }

        const relationship = 'courseware';
        const options = {};

        return dispatch(
            `courseware-instances/loadRelated`,
            {
                parent,
                relationship,
                options,
            },
            { root: true }
        ).then(() => {
            return rootGetters['courseware-instances/related']({ parent, relationship });
        });
    },

    loadStructuralElement({ dispatch }, structuralElementId) {
        const options = {
            include:
                'children,containers,containers.edit-blocker,containers.blocks,containers.blocks.editor,containers.blocks.owner,containers.blocks.edit-blocker,editor,edit-blocker,owner',
            'fields[users]': 'formatted-name',
        };

        return dispatch(
            'courseware-structural-elements/loadById',
            { id: structuralElementId, options },
            { root: true }
        );
    },

    loadContainer({ dispatch }, containerId) {
        const options = {
            include: 'blocks,blocks.edit-blocker','fields[users]': 'formatted-name',
        };

        return dispatch('courseware-containers/loadById', { id: containerId, options }, { root: true });
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

    storeCoursewareSettings({ dispatch }, { instance }) {
        return dispatch('courseware-instances/update', instance, { root: true });
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
            const membershipPermission = membership.attributes.permission;
            commit('setUserIsTeacher', membershipPermission === 'dozent' || membershipPermission === 'tutor');

            return true;
        } else {
            console.error(`Could not find course membership for ${membershipId}.`);
            commit('setUserIsTeacher', false);

            return false;
        }
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

    async createStructuralElement({ dispatch }, { attributes, parentId }) {
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
    },


    async updateStructuralElement({ dispatch }, { element, id }) {
        await dispatch('courseware-structural-elements/update', element, { root: true });

        return dispatch('loadStructuralElement', id);
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

    async updateContainer({ dispatch }, { container, structuralElementId }) {
        await dispatch('courseware-containers/update', container, { root: true });

        return dispatch('loadStructuralElement', structuralElementId);
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
};

export const mutations = {
    setContext(state, data){
        state.context = data;
    },
    setHttpClient(state, data){
        state.httpClient = data;
    },
    setShowCompanionOverlay(state, data) {
        state.showCompanionOverlay = data;
    },
    setStyleCompanionOverlay(state, data) {
        state.styleCompanionOverlay = data;
    },
    setMsgCompanionOverlay(state, data) {
        state.msgCompanionOverlay = data;
    },
    setShowUnitAddDialog(state, data) {
        state.showUnitAddDialog = data;
    },
    setShowUnitCopyDialog(state, data) {
        state.showUnitCopyDialog = data;
    },
    setShowUnitImportDialog(state, data) {
        state.showUnitImportDialog = data;
    },
    setShowUnitLinkDialog(state, data) {
        state.showUnitLinkDialog = data;
    },
    setLicenses(state, data) {
        state.licenses = data;
    },
    setUserId(state, data) {
        state.userId = data;
    },
    setExportState(state, exportState) {
        state.exportState = exportState;
    },
    setExportProgress(state, exportProgress) {
        state.exportProgress = exportProgress;
    },
    setUserIsTeacher(state, isTeacher) {
        state.teacherStatusLoaded = true;
        state.userIsTeacher = isTeacher;
    },
    setUrlHelper(state, urlHelper) {
        state.urlHelper = urlHelper;
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
};

export default {
    state,
    actions,
    mutations,
    getters,
};
