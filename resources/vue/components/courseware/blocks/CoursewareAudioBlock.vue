<template>
    <div class="cw-block cw-block-audio">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @showEdit="initCurrentData"
            @storeEdit="storeBlock"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <div v-if="currentTitle !== ''" class="cw-block-title">{{ currentTitle }}</div>
                <audio
                    :src="currentURL"
                    class="cw-audio-player"
                    ref="audio"
                    @timeupdate="onTimeUpdateListener"
                    @durationchange="setDuration"
                    @ended="onEndedListener"
                />
                <div class="cw-audio-container">
                    <div
                        v-if="!userRecorderEnabled"
                        class="cw-audio-player"
                        :class="{ 'with-playlist': playlistEnabled }"
                    >
                        <div class="cw-audio-cover" :class="{ loading: loadingCover, 'with-edit-button': canEditFile }">
                            <img v-if="cover" :src="cover" class="cover" />
                            <studip-icon
                                v-else
                                :shape="emptyAudio ? 'file' : 'file-audio'"
                                :size="128"
                                role="info"
                                class="default-cover"
                            />
                            <button v-if="canEditFile" :title="$gettext('Bearbeiten')" @click="displayEditMP3">
                                <studip-icon shape="edit" />
                            </button>
                        </div>
                        <div class="cw-audio-controls-wrapper">
                            <div class="cw-audio-current-track">
                                <h2>{{ trackTitle }}</h2>
                                <h3>{{ trackArtist }}</h3>
                            </div>
                            <div class="cw-audio-controls">
                                <div class="cw-audio-progress">
                                    <template v-if="!emptyAudio">
                                        <input
                                            class="cw-audio-range"
                                            ref="range"
                                            type="range"
                                            :value="currentSeconds"
                                            min="0"
                                            :max="Math.round(durationSeconds)"
                                            @input="rangeAction"
                                        />
                                        <p class="cw-audio-time">
                                            <span>{{ currentTime }}</span>
                                            <span>{{ durationTime }}</span>
                                        </p>
                                    </template>
                                    <hr v-else />
                                </div>
                                <div class="cw-audio-buttons">
                                    <button :title="$gettext('Zurück')" :disabled="!hasPlaylist" @click="prevAudio">
                                        <studip-icon
                                            shape="arr_eol-left"
                                            :role="hasPlaylist ? 'clickable' : 'inactive'"
                                            :size="24"
                                        />
                                    </button>
                                    <button
                                        v-if="!playing"
                                        :title="$gettext('Abspielen')"
                                        :disabled="emptyAudio"
                                        @click="playAudio"
                                    >
                                        <studip-icon
                                            shape="play"
                                            :role="emptyAudio ? 'inactive' : 'clickable'"
                                            :size="48"
                                        />
                                    </button>
                                    <button v-else :title="$gettext('Pause')" @click="pauseAudio">
                                        <studip-icon shape="pause" :size="48" />
                                    </button>
                                    <button :title="$gettext('Weiter')" :disabled="!hasPlaylist" @click="nextAudio">
                                        <studip-icon
                                            shape="arr_eol-right"
                                            :role="hasPlaylist ? 'clickable' : 'inactive'"
                                            :size="24"
                                        />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="cw-audio-recorder with-playlist">
                        <div class="cw-audio-cover">
                            <studip-icon
                                shape="microphone"
                                :size="128"
                                :role="isRecording ? 'status-red' : 'info'"
                                class="default-cover"
                            />
                        </div>
                        <div class="cw-audio-controls-wrapper">
                            <div class="cw-audio-current-track">
                                <h2>{{ $gettext('Aufnahme') }}</h2>
                                <h3 v-if="isRecording">{{ $gettext('Aufnahme läuft') }}: {{ seconds2time(timer) }}</h3>
                                <h3 v-if="newRecording && !isRecording">{{ seconds2time(timer) }}</h3>
                            </div>
                            <div class="cw-audio-controls">
                                <div class="cw-recorder-visualization">
                                    <div
                                        v-for="(value, key) in recorderFrequencyData"
                                        :key="'bar' + key"
                                        :ref="'bar' + key"
                                        class="cw-recorder-visualization-bar"
                                        :class="{ 'idle-bar': !isRecording }"
                                    ></div>
                                </div>
                                <div class="cw-audio-buttons">
                                    <button
                                        v-if="newRecording && !isRecording"
                                        :title="$gettext('Aufnahme löschen')"
                                        @click="resetRecorder"
                                    >
                                        <studip-icon shape="trash" :size="24" />
                                    </button>
                                    <button
                                        v-if="!isRecording && !newRecording"
                                        :title="$gettext('Neue Aufnahme starten')"
                                        @click="startRecording"
                                    >
                                        <studip-icon shape="span-full" :size="48" role="status-red" />
                                    </button>
                                    <button
                                        v-if="isRecording"
                                        :title="$gettext('Aufnahme beenden')"
                                        @click="stopRecording"
                                    >
                                        <studip-icon shape="stop" :size="48" />
                                    </button>
                                    <button
                                        v-if="newRecording && !isRecording"
                                        :title="$gettext('Aufnahme speichern')"
                                        @click="storeRecording"
                                    >
                                        <studip-icon shape="download" :size="48" />
                                    </button>
                                    <button
                                        v-if="newRecording && !isRecording"
                                        :title="$gettext('Aufnahme wiederholen')"
                                        @click="startRecording"
                                    >
                                        <studip-icon shape="span-full" :size="24" role="status-red" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-show="playlistEnabled" class="cw-audio-playlist-wrapper">
                        <ul class="cw-audio-playlist" :class="[showRecorder ? 'with-recorder' : '']">
                            <li v-for="(file, index) in files" :key="file.id">
                                <a
                                    :aria-current="index === currentPlaylistItem ? 'true' : 'false'"
                                    :title="$gettext('Audiodatei:') + ' ' + file.name"
                                    href="#"
                                    class="cw-playlist-item"
                                    @click.prevent="setCurrentPlaylistItem(index)"
                                >
                                    <studip-icon
                                        :shape="
                                            index === currentPlaylistItem && !userRecorderEnabled
                                                ? playing
                                                    ? 'pause'
                                                    : 'play'
                                                : 'file-audio2'
                                        "
                                    />
                                    {{ file.name }}
                                </a>
                            </li>
                            <li v-if="emptyAudio">
                                <p class="cw-playlist-item">
                                    <studip-icon shape="file" role="info" />
                                    {{ $gettext('Ordner enthält keine Audio-Dateien') }}
                                </p>
                            </li>
                        </ul>
                    </div>
                </div>
                <div v-if="showRecorder && canGetMediaDevices" class="cw-call-to-action">
                    <button
                        v-if="!userRecorderEnabled"
                        :title="enableRecorderTitle"
                        @click.prevent="enableRecorder"
                    >
                        <studip-icon shape="microphone" :size="48"/>
                        {{ $gettext('Aufnahme aktivieren') }}
                    </button>
                    <button v-else  @click.prevent="resetRecorder">
                        <studip-icon shape="decline" :size="48"/>
                        {{ $gettext('Aufnahme abbrechen') }}
                    </button>
                </div>
                <studip-dialog
                    v-if="showEditMP3"
                    :title="$gettext('MP3 Metadaten bearbeiten')"
                    :confirmText="$gettext('Speichern')"
                    confirmClass="accept"
                    :closeText="$gettext('Abbrechen')"
                    closeClass="cancel"
                    @close="closeEditMP3"
                    @confirm="updateMP3"
                    height="550"
                    width="450"
                >
                    <template v-slot:dialogContent>
                        <div class="edit-mp3-cover-wrapper">
                            <img v-if="newCoverUrl" :src="newCoverUrl" class="edit-mp3-cover" />
                            <template v-else>
                                <template v-if="cover && !deleteCover">
                                    <img :src="cover" class="edit-mp3-cover" />
                                    <button
                                        v-if="cover"
                                        class="remove-cover"
                                        :title="$gettext('Cover entfernen')"
                                        @click="removeCover"
                                    >
                                        <studip-icon shape="trash" />
                                    </button>
                                </template>
                                <studip-icon
                                    v-if="cover === '' || deleteCover"
                                    shape="file-audio"
                                    :size="64"
                                    role="info"
                                    class="edit-mp3-cover default-cover"
                                />
                            </template>
                        </div>
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Cover') }}
                                <template v-if="!deleteCover">
                                    <input
                                        class="cw-file-input"
                                        type="file"
                                        ref="newCover"
                                        accept="image/jpeg"
                                        @change="updateCover"
                                    />
                                </template>
                                <input v-else type="text" disabled :placeholder="$gettext('Cover wird entfernt')" />
                            </label>
                            <label>
                                {{ $gettext('Titel') }}
                                <input type="text" v-model="currentMP3Title" />
                            </label>
                            <label>
                                {{ $gettext('Künstler') }}
                                <input type="text" v-model="currentMP3Artist" />
                            </label>
                        </form>
                    </template>
                </studip-dialog>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        {{ $gettext('Überschrift') }}
                        <input type="text" v-model="currentTitle" :placeholder="$gettext('optional')" />
                    </label>
                    <label>
                        {{ $gettext('Quelle') }}
                        <select v-model="currentSource">
                            <option value="studip_file">{{ $gettext('Dateibereich Datei') }}</option>
                            <option value="studip_folder">{{ $gettext('Dateibereich Ordner') }}</option>
                            <option value="web">{{ $gettext('Web-Adresse') }}</option>
                        </select>
                    </label>
                    <label v-show="currentSource === 'web'">
                        {{ $gettext('URL') }}
                        <input type="text" v-model="currentWebUrl" />
                    </label>
                    <label v-show="currentSource === 'studip_file'">
                        {{ $gettext('Datei') }}
                        <studip-file-chooser v-model="currentFileId" selectable="file" :courseId="context.id" :userId="userId" :isAudio="true" :excludedCourseFolderTypes="excludedCourseFolderTypes" />
                    </label>
                    <label v-show="currentSource === 'studip_folder'">
                        {{ $gettext('Ordner') }}
                        <studip-file-chooser v-model="currentFolderId" selectable="folder" :courseId="context.id" :userId="userId" :excludedCourseFolderTypes="excludedCourseFolderTypes" />
                    </label>
                    <label v-show="currentSource === 'studip_folder'">
                        {{ $gettext('Audio-Aufnahmen zulassen') }}
                        <span
                            class="tooltip tooltip-icon"
                            :data-tooltip="$gettext('Um Aufnahmen zu ermöglichen, muss ein Ordner ausgewählt werden.')"
                        ></span>
                        <select v-model="currentRecorderEnabled" :disabled="!folderSelected">
                            <option :value="true">{{ $gettext('Ja') }}</option>
                            <option :value="false">{{ $gettext('Nein') }}</option>
                        </select>
                    </label>
                </form>
            </template>
            <template #info>
                <p>{{ $gettext('Informationen zum Audio-Block') }}</p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import BlockComponents from './block-components.js';
import blockMixin from '@/vue/mixins/courseware/block.js';
import { mapActions, mapGetters } from 'vuex';
import MP3Tag from 'mp3tag.js';

export default {
    name: 'courseware-audio-block',
    mixins: [blockMixin],
    components: Object.assign(BlockComponents, {}),
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentTitle: '',
            currentSource: '',
            currentFileId: '',
            currentFolderId: '',
            currentWebUrl: '',
            currentFile: {},
            currentSeconds: 0,
            durationSeconds: 0,
            playing: false,
            currentPlaylistItem: 0,
            currentRecorderEnabled: false,
            userRecorderEnabled: false,
            recorder: null,
            chunks: [],
            timer: 0,
            isRecording: false,
            newRecording: false,
            folderLoadError: false,
            recorderAudioCtx: null,
            recorderAnalyser: null,
            recorderSource: null,
            recorderBufferLength: 0,
            recorderTimeData: null,
            recorderFrequencyData: null,

            mp3tag: null,
            loadingCover: false,
            volume: 100,

            showEditMP3: false,
            currentMP3Title: '',
            currentMP3Artist: '',
            imageBytes: null,
            newCoverUrl: '',
            deleteCover: false,
        };
    },
    computed: {
        ...mapGetters({
            fileRefById: 'file-refs/byId',
            relatedFileRefs: 'file-refs/related',
            urlHelper: 'urlHelper',
            usersById: 'users/byId',
            relatedTermOfUse: 'terms-of-use/related',
        }),
        files() {
            const files =
                this.relatedFileRefs({
                    parent: { type: 'folders', id: this.currentFolderId },
                    relationship: 'file-refs',
                }) ?? [];

            return files
                .filter((file) => {
                    if (
                        this.relatedTermOfUse({ parent: file, relationship: 'terms-of-use' }).attributes[
                            'download-condition'
                        ] !== 0
                    ) {
                        return false;
                    }
                    if (!file.attributes['mime-type'].includes('audio')) {
                        return false;
                    }

                    return true;
                })
                .sort((a, b) => {
                    return new Date(a.attributes.mkdate) - new Date(b.attributes.mkdate);
                })
                .map(({ id, attributes }) => {
                    return {
                        id,
                        name: attributes.name,
                        download_url: this.urlHelper.getURL(
                            'sendfile.php/',
                            { type: 0, file_id: id, file_name: attributes.name },
                            true
                        ),
                        mime_type: attributes['mime-type'],
                        isRecording: attributes.description === 'CoursewareRecording',
                    };
                });
        },
        currentTime() {
            return this.seconds2time(this.currentSeconds);
        },
        durationTime() {
            if (this.durationSeconds > 0) {
                return this.seconds2time(this.durationSeconds);
            }
            return false;
        },
        title() {
            return this.block?.attributes?.payload?.title;
        },
        source() {
            return this.block?.attributes?.payload?.source;
        },
        fileId() {
            return this.block?.attributes?.payload?.file_id;
        },
        folderId() {
            return this.block?.attributes?.payload?.folder_id;
        },
        folderSelected() {
            return this.currentFolderId !== '';
        },
        webUrl() {
            return this.block?.attributes?.payload?.web_url;
        },
        recorderEnabled() {
            return this.block?.attributes?.payload?.recorder_enabled;
        },
        showRecorder() {
            return this.currentRecorderEnabled && this.playlistEnabled;
        },
        hasPlaylist() {
            return this.files.length > 0 && this.playlistEnabled;
        },
        playlistEnabled() {
            return this.currentSource === 'studip_folder';
        },
        canGetMediaDevices() {
            return navigator.mediaDevices !== undefined;
        },
        activeFile() {
            if (this.currentSource === 'studip_file') {
                return this.currentFile;
            }
            if (this.playlistEnabled) {
                if (this.files.length > 0) {
                    return this.files[this.currentPlaylistItem];
                }
            }

            return null;
        },
        fileIsRecording() {
            return this.activeFile?.isRecording ?? false;
        },
        currentURL() {
            if (this.activeFile) {
                return this.activeFile.download_url;
            }
            if (this.currentSource === 'web') {
                return this.currentWebUrl;
            }

            return '';
        },
        activeTrackName() {
            if (this.activeFile) {
                return this.activeFile.name;
            }
            if (this.currentSource === 'web') {
                return this.currentWebUrl;
            }

            return '';
        },
        trackTitle() {
            if (this.emptyAudio) {
                return this.$gettext('Es ist keine Audio-Datei verfügbar');
            }
            if (this.tags && this.tags.title !== '') {
                return this.tags.title;
            }

            return this.activeTrackName;
        },
        trackArtist() {
            return this.tags?.artist ?? '';
        },
        emptyAudio() {
            if (this.currentSource === 'studip_folder' && this.currentFolderId !== '' && this.files.length > 0) {
                return false;
            }
            if (this.currentSource === 'studip_file' && this.currentFileId !== '') {
                return false;
            }
            if (this.currentSource === 'web' && this.currentWebUrl !== '') {
                return false;
            }
            return true;
        },
        enableRecorderTitle() {
            if (!this.folderSelected) {
                return this.$gettext('Aufnahme nicht möglich, es wurde kein Ordner ausgewählt.');
            }

            if (this.folderLoadError) {
                return this.$gettext('Aufnahme nicht möglich, der ausgewählte Ordner konnte nicht gefunden werden.');
            }

            return this.$gettext('Aktiviert die Aufnahmefunktion');
        },
        tags() {
            return this.mp3tag?.tags ?? {};
        },
        hasMP3Tags() {
            return Object.keys(this.tags).length > 0;
        },
        cover() {
            const image = this.tags?.v2?.APIC?.[0];
            if (image) {
                return this.imageURL(image.data, image.format);
            }

            if (this.fileIsRecording) {
                const ownerId = this.activeFileRef?.relationships?.owner?.data?.id;
                if (ownerId) {
                    const owner = this.usersById({ id: ownerId });
                    return owner?.meta?.avatar?.normal ?? '';
                }
            }

            return '';
        },
        activeFileRef() {
            return this.fileRefById({ id: this.activeFile.id });
        },
        canEditFile() {
            return this.hasMP3Tags && this.activeFileRef.attributes['is-editable'];
        },
    },
    async mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            loadFileRef: 'file-refs/loadById',
            loadRelatedFileRefs: 'file-refs/loadRelated',
            updateFileRefs: 'file-refs/update',
            updateBlock: 'updateBlockInContainer',
            companionWarning: 'companionWarning',
            companionSuccess: 'companionSuccess',
            companionError: 'companionError',
            createFile: 'createFile',
            updateFileContent: 'updateFileContent',
            loadUser: 'users/loadById',
        }),

        toDataURL(url) {
            return new Promise(function (resolve, reject) {
                var xhr = new XMLHttpRequest();
                xhr.onload = function () {
                    var reader = new FileReader();
                    reader.onloadend = function () {
                        resolve(reader.result);
                    };
                    reader.readAsArrayBuffer(xhr.response);
                };
                xhr.open('GET', url);
                xhr.responseType = 'blob';
                xhr.send();
            });
        },

        initCurrentData() {
            this.currentTitle = this.title;
            this.currentSource = this.source;
            this.currentFileId = this.fileId;
            this.currentWebUrl = this.webUrl;
            if (this.currentFileId !== '') {
                this.loadFile();
            }
            this.currentFolderId = this.folderId;
            this.currentRecorderEnabled = this.recorderEnabled;
        },
        updateCurrentFile(file) {
            this.currentFile = file;
            this.currentFileId = file.id;
        },
        async getFolderFiles() {
            try {
                await this.loadRelatedFileRefs({
                    parent: { type: 'folders', id: this.currentFolderId },
                    relationship: 'file-refs',
                    options: { include: 'terms-of-use' },
                });
            } catch (error) {
                this.folderLoadError = true;
            }
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.title = this.currentTitle;
            attributes.payload.source = this.currentSource;
            attributes.payload.file_id = '';
            attributes.payload.web_url = '';
            attributes.payload.folder_id = '';
            attributes.payload.recorder_enabled = false;
            if (this.currentSource === 'studip_file') {
                if (this.currentFileId === '') {
                    this.companionWarning({
                        info: this.$gettext('Bitte wählen Sie eine Datei aus.'),
                    });
                    return false;
                }
                attributes.payload.file_id = this.currentFileId;
            } else if (this.currentSource === 'web') {
                attributes.payload.web_url = this.currentWebUrl;
            } else if (this.currentSource === 'studip_folder') {
                if (this.currentFolderId === '') {
                    this.companionWarning({
                        info: this.$gettext('Bitte wählen Sie einen Ordner aus.'),
                    });
                    return false;
                }
                attributes.payload.folder_id = this.currentFolderId;
                attributes.payload.recorder_enabled = this.currentRecorderEnabled;
            } else {
                return false;
            }

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
        rangeAction() {
            if (this.$refs.range.value !== this.currentSeconds) {
                this.$refs.audio.currentTime = this.$refs.range.value;
            }
        },
        setVolume() {
            this.$refs.audio.volume = this.volume / 100;
        },
        setDuration() {
            let duration = this.$refs.audio.duration;
            if (!isNaN(duration) && isFinite(duration)) {
                this.durationSeconds = duration;
            } else {
                this.durationSeconds = 0;
            }
        },
        playAudio() {
            const audio = this.$refs.audio;
            let isSupported = 'unknown';
            if (this.currentSource === 'studip_file') {
                isSupported = audio.canPlayType(this.currentFile.mime_type);
            }
            if (this.currentSource === 'studip_folder') {
                isSupported = audio.canPlayType(this.files[this.currentPlaylistItem].mime_type);
            }
            if (isSupported !== '') {
                audio.play();
                this.playing = true;
            } else {
                this.companionError({
                    info: this.$gettext('Ihr Browser unterstützt dieses Audioformat leider nicht.'),
                });
                if (this.hasPlaylist) {
                    this.nextAudio();
                }
            }
        },
        pauseAudio() {
            this.$refs.audio.pause();
            this.playing = false;
        },
        stopAudio() {
            this.pauseAudio();
            this.$refs.audio.currentTime = 0;
        },
        onTimeUpdateListener() {
            this.currentSeconds = this.$refs.audio.currentTime;
        },
        onEndedListener() {
            this.stopAudio();
            if (this.hasPlaylist) {
                this.nextAudio();
            }
        },
        seconds2time(seconds) {
            seconds = Math.round(seconds);
            let hours = Math.floor(seconds / 3600);
            let minutes = Math.floor((seconds - hours * 3600) / 60);
            let time = '';
            seconds = seconds - hours * 3600 - minutes * 60;
            if (hours !== 0) {
                time = hours + ':';
            }
            if (minutes !== 0 || time !== '') {
                minutes = minutes < 10 && time !== '' ? '0' + minutes : String(minutes);
                time += minutes + ':';
            }
            if (time === '') {
                time = seconds < 10 ? '0:0' + seconds : '0:' + seconds;
            } else {
                time += seconds < 10 ? '0' + seconds : String(seconds);
            }
            return time;
        },
        setCurrentPlaylistItem(index) {
            this.userRecorderEnabled = false;
            if (this.currentPlaylistItem === index) {
                if (this.playing) {
                    this.pauseAudio();
                } else {
                    this.playAudio();
                }
            } else {
                this.currentPlaylistItem = index;
                this.$nextTick(() => {
                    this.playAudio();
                });
            }
        },
        prevAudio() {
            this.stopAudio();
            if (this.currentPlaylistItem !== 0) {
                this.currentPlaylistItem = this.currentPlaylistItem - 1;
            } else {
                this.currentPlaylistItem = this.files.length - 1;
            }
            this.$nextTick(() => {
                this.playAudio();
            });
        },
        nextAudio() {
            this.stopAudio();
            if (this.currentPlaylistItem < this.files.length - 1) {
                this.currentPlaylistItem = this.currentPlaylistItem + 1;
                this.$nextTick(() => {
                    this.playAudio();
                });
            }
        },

        async loadFile() {
            const id = this.currentFileId;
            await this.loadFileRef({ id });
            const fileRef = this.fileRefById({ id });

            if (fileRef) {
                this.updateCurrentFile({
                    id: fileRef.id,
                    name: fileRef.attributes.name,
                    download_url: this.urlHelper.getURL(
                        'sendfile.php',
                        { type: 0, file_id: fileRef.id, file_name: fileRef.attributes.name },
                        true
                    ),
                    mime_type: fileRef.attributes['mime-type'],
                });
            }
        },
        async loadTags() {
            this.mp3tag = null;
            let view = this;
            let response = await fetch(this.currentURL);
            let data = await response.blob();
            let file = new File([data], this.activeTrackName);

            let reader = new FileReader();
            reader.onload = function () {
                const buffer = this.result;
                view.mp3tag = new MP3Tag(buffer);
                view.mp3tag.read();
            };

            reader.readAsArrayBuffer(file);
        },
        imageURL(bytes, format) {
            let encoded = '';
            bytes.forEach(function (byte) {
                encoded += String.fromCharCode(byte);
            });

            return `data:${format};base64,${btoa(encoded)}`;
        },
        enableRecorder() {
            if (!this.folderSelected || this.folderLoadError) {
                return false;
            }
            let view = this;
            navigator.mediaDevices
                .getUserMedia({ audio: true })
                .then(function (stream) {
                    view.recorder = new MediaRecorder(stream, { type: 'audio/webm; codecs:vp9' });
                    view.userRecorderEnabled = true;
                    view.recorder.ondataavailable = (e) => {
                        view.chunks.push(e.data);
                    };

                    view.recorderAudioCtx = new AudioContext();
                    view.recorderAnalyser = view.recorderAudioCtx.createAnalyser();
                    view.recorderSource = view.recorderAudioCtx.createMediaStreamSource(stream);
                    view.recorderSource.connect(view.recorderAnalyser);
                    view.recorderAnalyser.fftSize = 2 ** 6;
                    view.recorderBufferLength = view.recorderAnalyser.frequencyBinCount;
                    view.recorderFrequencyData = new Uint8Array(view.recorderBufferLength);
                })
                .catch(() => {
                    view.companionWarning({
                        info: view.$gettext('Sie müssen ein Mikrofon freigeben, um eine Aufnahme starten zu können.'),
                    });
                });
        },
        startRecording() {
            let view = this;
            this.chunks = [];
            this.timer = 0;
            this.recorder.start();
            this.isRecording = true;
            setTimeout(function () {
                view.setTimer();
            }, 1000);
        },
        stopRecording() {
            this.isRecording = false;
            this.newRecording = true;
            this.recorder.stop();
        },
        setTimer() {
            let view = this;
            if (this.recorder.state === 'recording') {
                this.timer++;
                setTimeout(function () {
                    view.setTimer();
                }, 1000);
            }
        },
        recorderDrawTimeData() {
            this.recorderAnalyser.getByteFrequencyData(this.recorderFrequencyData);

            for (let i = 0; i < this.recorderFrequencyData.length; i++) {
                let ref = 'bar' + i;
                this.$refs[ref][0].style.height = (this.recorderFrequencyData[i] / 255) * 28 + 'px';
            }

            if (this.isRecording) {
                let view = this;
                requestAnimationFrame(() => view.recorderDrawTimeData());
            }
        },
        async storeRecording() {
            let view = this;
            let user = this.usersById({ id: this.userId });
            let blob = new Blob(view.chunks, { type: 'audio/webm; codecs:vp9' });

            let file = {
                attributes: {
                    name: user.attributes['formatted-name'].replace(/\s+/g, '_') + '.webm',
                },
                relationships: {
                    'terms-of-use': {
                        data: {
                            id: 'SELFMADE_NONPUB',
                        },
                    },
                },
            };
            let fileObj = await this.createFile({
                file: file,
                filedata: blob,
                folder: { id: this.currentFolderId },
            });
            if (fileObj && fileObj.type === 'file-refs') {
                this.companionSuccess({
                    info: this.$gettext('Die Aufnahme wurde erfolgreich im Dateibereich abgelegt.'),
                });
                fileObj.attributes.description = 'CoursewareRecording';
                await this.updateFileRefs(fileObj);
            } else {
                this.companionError({
                    info: this.$gettext('Es ist ein Fehler aufgetreten! Die Aufnahme konnte nicht gespeichert werden.'),
                });
            }
            this.newRecording = false;
            this.userRecorderEnabled = false;
            this.getFolderFiles();
        },
        resetRecorder() {
            this.userRecorderEnabled = false;
            this.isRecording = false;
            this.newRecording = false;
            this.chunks = [];
            this.timer = 0;
            this.blob = null;
        },
        displayEditMP3() {
            this.stopAudio();
            this.currentMP3Title = this.tags.title;
            this.currentMP3Artist = this.tags.artist;
            this.showEditMP3 = true;
        },
        closeEditMP3() {
            this.showEditMP3 = false;
            this.currentMP3Title = '';
            this.currentMP3Artist = '';
            this.imageBytes = null;
            this.newCoverUrl = '';
            this.deleteCover = false;
        },
        removeCover() {
            this.deleteCover = true;
            this.$refs.newCover.value = '';
        },
        async updateCover() {
            this.deleteCover = false;
            const file = this.$refs?.newCover?.files[0];
            const buffer = await this.readFile(file);
            this.imageBytes = new Uint8Array(buffer);
            this.newCoverUrl = this.imageURL(this.imageBytes, 'image/jpeg');
        },
        readFile(file) {
            return new Promise(function (resolve, reject) {
                const reader = new FileReader();
                reader.onload = () => {
                    resolve(reader.result);
                };
                reader.onerror = reject;
                reader.readAsArrayBuffer(file);
            });
        },
        async updateMP3() {
            this.mp3tag.tags.title = this.currentMP3Title;
            this.mp3tag.tags.artist = this.currentMP3Artist;

            if (this.imageBytes) {
                this.mp3tag.tags.v2.APIC = [
                    {
                        format: 'image/jpeg',
                        type: 3,
                        description: '',
                        data: this.imageBytes,
                    },
                ];
            }
            if (this.deleteCover) {
                this.mp3tag.tags.v2.APIC = [];
            }
            this.mp3tag.save();
            const modifiedFile = new File([this.mp3tag.buffer], this.activeTrackName, {
                type: 'audio/mpeg',
            });

            const fileRef = await this.fileRefById({ id: this.activeFile.id });

            let fileObj = await this.updateFileContent({
                file: fileRef,
                filedata: modifiedFile,
            });

            this.closeEditMP3();
            this.getFolderFiles();
        },
    },
    watch: {
        currentFolderId(newState) {
            if (newState === '') {
                this.currentRecorderEnabled = false;
            } else {
                this.getFolderFiles();
            }
        },
        currentURL() {
            this.loadingCover = true;
            this.loadTags();
            if (this.fileIsRecording) {
                const ownerId = this.activeFileRef?.relationships?.owner?.data?.id;
                if (ownerId) {
                    this.loadUser({ id: ownerId });
                }
            }
            setTimeout(() => {
                this.loadingCover = false;
            }, 200);
        },
        isRecording(newState) {
            if (newState) {
                this.recorderDrawTimeData();
            }
        },
    },
};
</script>
<style scoped lang="scss">
@import '../../../../assets/stylesheets/scss/courseware/blocks/audio.scss';
</style>
