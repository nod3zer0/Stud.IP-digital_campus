const getDefaultState = () => {
    return {
        selectable: 'file',
        selectedFileId: '',
        selectedFolderId: '',
        activeFolderId: '',
        userId: '',
        courseId: '',
        isAudio: false,
        isDocument: false,
        isImage: false,
        isVideo: false,
    };
};

const initialState = getDefaultState();
const state = { ...initialState };

const getters = {
    selectable(state) {
        return state.selectable;
    },
    selectedFileId(state) {
        return state.selectedFileId;
    },
    selectedFolderId(state) {
        return state.selectedFolderId;
    },
    activeFolderId(state) {
        return state.activeFolderId;
    },
    userId(state) {
        return state.userId;
    },
    courseId(state) {
        return state.courseId;
    },
    isAudio(state) {
        return state.isAudio;
    },
    isDocument(state) {
        return state.isDocument;
    },
    isImage(state) {
        return state.isImage;
    },
    isVideo(state) {
        return state.isVideo;
    },

    activeFolder(state, getters, rootState, rootGetters) {
        const id = state.activeFolderId;
        if (id) {
            return rootGetters['folders/byId']({ id });
        }

        return null;
    },

    activeFolderRangeType(state, getters) {
        return getters.activeFolder?.relationships?.range?.data?.type;
    },

    relatedUsersFolders(state, getters, rootState, rootGetters) {
        const parent = { type: 'users', id: getters.userId };
        const relationship = 'folders';
        return rootGetters['folders/related']({ parent, relationship });
    },

    relatedCoursesFolders(state, getters, rootState, rootGetters) {
        const parent = { type: 'courses', id: getters.courseId };
        const relationship = 'folders';
        return rootGetters['folders/related']({ parent, relationship });
    },
    filterActive(state, getters) {
        return getters.isAudio || getters.isDocument || getters.isImage || getters.isVideo;
    },
    currentFolderFiles(state, getters, rootState, rootGetters) {
        const id = state.activeFolderId;
        if (id === '') {
            return [];
        }
        const parent = { type: 'folders', id: id };
        const relationship = 'file-refs';
        let files = rootGetters['file-refs/related']({ parent, relationship }) ?? [];

        if (!getters.filterActive) {
            return files;
        }

        files =
            files.filter((file) => {
                const fileTermsOfUse = rootGetters['terms-of-use/related']({
                    parent: file,
                    relationship: 'terms-of-use',
                });
                if (fileTermsOfUse !== null && fileTermsOfUse.attributes['download-condition'] !== 0) {
                    return false;
                }
                if (getters.isImage && !file.attributes['mime-type'].includes('image')) {
                    return false;
                }
                const videoConditions = ['video/mp4', 'video/ogg', 'video/webm'];
                if (
                    getters.isVideo &&
                    !videoConditions.some((condition) => file.attributes['mime-type'].includes(condition))
                ) {
                    return false;
                }
                const audioConditions = [
                    'audio/wav',
                    'audio/ogg',
                    'audio/webm',
                    'audio/flac',
                    'audio/mpeg',
                    'audio/x-m4a',
                    'audio/mp4',
                ];
                if (
                    getters.isAudio
                    && !audioConditions.some((condition) => file.attributes['mime-type'].includes(condition))
                ) {
                    return false;
                }
                const officeConditions = ['application/pdf']; //TODO enable more mime types
                if (
                    getters.isDocument
                    && !officeConditions.some((condition) => file.attributes['mime-type'].includes(condition))
                ) {
                    return false;
                }

                return true;
            }) ?? [];

        return files;
    },
    isFolderChooser(state, getters) {
        return getters.selectable === 'folder';
    },
};

const actions = {
    //setters
    setSelectable({ commit }, value) {
        commit('setSelectable', value);
    },
    setSelectedFileId({ commit }, id) {
        commit('setSelectedFileId', id);
    },
    setSelectedFolderId({ commit }, id) {
        commit('setSelectedFolderId', id);
    },
    setActiveFolderId({ commit }, id) {
        commit('setActiveFolderId', id);
    },
    setCourseId({ commit }, id) {
        commit('setCourseId', id);
    },
    setUserId({ commit }, id) {
        commit('setUserId', id);
    },
    setIsAudio({ commit }, id) {
        commit('setIsAudio', id);
    },
    setIsDocument({ commit }, id) {
        commit('setIsDocument', id);
    },
    setIsImage({ commit }, id) {
        commit('setIsImage', id);
    },
    setIsVideo({ commit }, id) {
        commit('setIsVideo', id);
    },
    // custom action
    async loadRangeFolders({ dispatch }, { rangeType, rangeId }) {
        const parent = { type: rangeType, id: rangeId };
        const relationship = 'folders';
        const options = { 'page[limit]': 10000 };

        return dispatch(
            'folders/loadRelated',
            {
                parent,
                relationship,
                options,
            },
            { root: true }
        );
    },

    loadFolderFiles({ dispatch }, { folderId }) {
        const parent = { type: 'folders', id: folderId };
        const relationship = 'file-refs';
        const options = { include: 'terms-of-use', 'page[limit]': 10000 };
        return dispatch(
            'file-refs/loadRelated',
            {
                parent,
                relationship,
                options,
            },
            { root: true }
        );
    },
};

export const mutations = {
    setSelectable(state, data) {
        state.selectable = data;
    },
    setSelectedFileId(state, data) {
        state.selectedFileId = data;
    },
    setSelectedFolderId(state, data) {
        state.selectedFolderId = data;
    },
    setActiveFolderId(state, data) {
        state.activeFolderId = data;
        state.selectedFileId = '';
    },
    setCourseId(state, data) {
        state.courseId = data;
    },
    setUserId(state, data) {
        state.userId = data;
    },
    setIsAudio(state, data) {
        state.isAudio = data;
    },
    setIsDocument(state, data) {
        state.isDocument = data;
    },
    setIsImage(state, data) {
        state.isImage = data;
    },
    setIsVideo(state, data) {
        state.isVideo = data;
    },
};

export default {
    namespaced: true,
    actions,
    getters,
    mutations,
    state,
};
