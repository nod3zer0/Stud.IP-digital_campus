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
                <div v-if="!emptyAudio" class="cw-audio-container">
                    <div class="cw-audio-current-track">
                        <p>{{ activeTrackName }}</p>
                    </div>
                    <div class="cw-audio-controls">
                        <input
                            class="cw-audio-range"
                            ref="range"
                            type="range"
                            :value="currentSeconds"
                            min="0"
                            :max="Math.round(durationSeconds)"
                            @input="rangeAction"
                        />
                        <span class="cw-audio-time">{{ currentTime }} {{ durationTime ? '/ ' + durationTime : '' }}</span>

                        <button v-if="hasPlaylist" class="cw-audio-button cw-audio-prevbutton" :title="$gettext('Zurück')" @click="prevAudio" />
                        <button v-if="!playing" class="cw-audio-button cw-audio-playbutton" :title="$gettext('Abspielen')" @click="playAudio" />
                        <button v-if="playing" class="cw-audio-button cw-audio-pausebutton" :title="$gettext('Pause')" @click="pauseAudio" />
                        <button v-if="hasPlaylist" class="cw-audio-button cw-audio-nextbutton" :title="$gettext('Weiter')" @click="nextAudio" />
                        <button class="cw-audio-button cw-audio-stopbutton" :title="$gettext('Anhalten')" @click="stopAudio" />
                    </div>
                </div>
                <div v-if="emptyAudio" class="cw-audio-empty">
                    <p>{{ $gettext('Es ist keine Audio-Datei verfügbar') }}</p>
                </div>
                <div v-show="currentSource === 'studip_folder'" class="cw-audio-playlist-wrapper" :class="[!showRecorder && emptyAudio ? 'empty' : '']">
                    <ul v-show="hasPlaylist" class="cw-audio-playlist" :class="[showRecorder ? 'with-recorder' : '']">
                        <li v-for="(file, index) in files" :key="file.id">
                            <a
                                :aria-current="(index === currentPlaylistItem) ? 'true' : 'false'"
                                :class="{
                                    'is-playing': index === currentPlaylistItem && playing,
                                    'current-item': index === currentPlaylistItem,
                                }"
                                :title="$gettext('Audiodatei:') + ' ' + file.name"
                                href="#"
                                class="cw-playlist-item"
                                @click.prevent="setCurrentPlaylistItem(index)"
                            >
                                {{ file.name }}
                            </a>
                        </li>
                    </ul>
                    <div v-if="showRecorder && canGetMediaDevices" class="cw-audio-playlist-recorder">
                        <button 
                            v-show="!userRecorderEnabled"
                            class="button"
                            :disabled="!folderSelected || folderLoadError"
                            :title="enableRecorderTitle"
                            @click="enableRecorder"
                        >
                            {{ $gettext('Aufnahme aktivieren') }}
                        </button>
                        <button
                            v-show="userRecorderEnabled && !isRecording && !newRecording"
                            class="button"
                            @click="startRecording"
                        >
                            {{ $gettext('Aufnahme starten') }}
                        </button>
                        <button
                            v-show="newRecording && !isRecording"
                            class="button"
                            @click="startRecording"
                        >
                            {{ $gettext('Aufnahme wiederholen') }}
                        </button>
                        <button 
                            v-show="isRecording"
                            class="button"
                            @click="stopRecording"
                        >
                            {{ $gettext('Aufnahme beenden') }}
                        </button>
                        <button 
                            v-show="newRecording && !isRecording"
                            class="button"
                            @click="resetRecorder"
                        >
                            {{ $gettext('Aufnahme löschen') }}
                        </button>
                        <button 
                            v-show="newRecording && !isRecording"
                            class="button"
                            @click="storeRecording"
                        >
                            {{ $gettext('Aufnahme speichern') }}
                        </button>
                        <span v-show="isRecording">
                            {{ $gettext('Aufnahme läuft') }}: {{seconds2time(timer)}}
                        </span>
                    </div>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        {{ $gettext('Überschrift') }}
                        <input type="text" v-model="currentTitle" />
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
                        <courseware-file-chooser
                            v-model="currentFileId"
                            :isAudio="true"
                            @selectFile="updateCurrentFile"
                        />
                    </label>
                    <label v-show="currentSource === 'studip_folder'">
                        {{ $gettext('Ordner') }}
                        <courseware-folder-chooser v-model="currentFolderId" allowUserFolders />
                    </label>
                    <label v-show="currentSource === 'studip_folder'">
                        {{ $gettext('Audio Aufnahmen zulassen') }}
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
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import CoursewareFileChooser from './CoursewareFileChooser.vue';
import CoursewareFolderChooser from './CoursewareFolderChooser.vue';
import { blockMixin } from './block-mixin.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-audio-block',
    mixins: [blockMixin],
    components: {
        CoursewareDefaultBlock,
        CoursewareFileChooser,
        CoursewareFolderChooser,
    },
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
            folderLoadError: false
        };
    },
    computed: {
        ...mapGetters({
            fileRefById: 'file-refs/byId',
            relatedFileRefs: 'file-refs/related',
            urlHelper: 'urlHelper',
            userId: 'userId',
            usersById: 'users/byId',
            relatedTermOfUse: 'terms-of-use/related'
        }),
        files() {
            const files =
                this.relatedFileRefs({
                    parent: { type: 'folders', id: this.currentFolderId },
                    relationship: 'file-refs'
                }) ?? [];

            return files
                .filter((file) => {
                    if (this.relatedTermOfUse({parent: file, relationship: 'terms-of-use'}).attributes['download-condition'] !== 0) {
                        return false;
                    } 
                    if (! file.attributes['mime-type'].includes('audio')) {
                        return false;
                    }

                    return true;
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
                        mime_type: attributes['mime-type']
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
            return this.currentRecorderEnabled && this.currentSource === 'studip_folder';
        },
        hasPlaylist() {
            return this.files.length > 0 && this.currentSource === 'studip_folder';
        },
        canGetMediaDevices() {
            return navigator.mediaDevices !== undefined;
        },
        currentURL() {
            if (this.currentSource === 'studip_file') {
                return this.currentFile.download_url;
            }
            if (this.currentSource === 'studip_folder') {
                if (this.files.length > 0) {
                    return this.files[this.currentPlaylistItem].download_url;
                } else {
                    return '';
                }
            }
            if (this.currentSource === 'web') {
                return this.currentWebUrl;
            }

            return '';
        },
        activeTrackName() {
            if (this.currentSource === 'studip_file') {
                return this.currentFile.name;
            }
            if (this.currentSource === 'studip_folder') {
                if (this.files.length > 0) {
                    return this.files[this.currentPlaylistItem].name;
                } else {
                    return '';
                }
            }
            if (this.currentSource === 'web') {
                return this.currentWebUrl;
            }

            return '';
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
        }
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            loadFileRef: 'file-refs/loadById',
            loadRelatedFileRefs: 'file-refs/loadRelated',
            updateBlock: 'updateBlockInContainer',
            companionWarning: 'companionWarning',
            companionSuccess: 'companionSuccess',
            companionError: 'companionError',
            createFile: 'createFile',
        }),
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
                    options: { include: 'terms-of-use' }
                });
            } catch(error) {
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
                        info: this.$gettext('Bitte wählen Sie eine Datei aus.')
                    });
                    return false;
                }
                attributes.payload.file_id = this.currentFileId;
            } else if (this.currentSource === 'web') {
                attributes.payload.web_url = this.currentWebUrl;
            } else if (this.currentSource === 'studip_folder') {
                if (this.currentFolderId === '') {
                    this.companionWarning({
                        info: this.$gettext('Bitte wählen Sie einen Ordner aus.')
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
        setDuration() {
            let duration = this.$refs.audio.duration
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
                    info: this.$gettext('Ihr Browser unterstützt dieses Audioformat leider nicht.')
                });
                if(this.hasPlaylist) {
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
            if(this.hasPlaylist) {
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
            if (this.currentPlaylistItem === index) {
                if (this.playing) {
                    this.pauseAudio();
                } else {
                    this.playAudio();
                }
            } else {
                this.currentPlaylistItem = index;
                this.$nextTick(()=> {
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
            this.$nextTick(()=> {
                this.playAudio();
            });
        },
        nextAudio() {
            this.stopAudio();
            if (this.currentPlaylistItem < this.files.length - 1) {
                this.currentPlaylistItem = this.currentPlaylistItem + 1;
                this.$nextTick(()=> {
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
                    mime_type: fileRef.attributes['mime-type']
                });
            }
        },
        enableRecorder() {
            if (!this.folderSelected || this.folderLoadError) {
                return false;
            }
            let view = this;
            navigator.mediaDevices.getUserMedia({ audio: true })
                .then(function(stream) {
                    view.recorder = new MediaRecorder(stream, {type: 'audio/webm; codecs:vp9' });
                    view.userRecorderEnabled = true;
                    view.recorder.ondataavailable = e => {
                        view.chunks.push(e.data);
                    };
                })
                .catch(() => {
                    view.companionWarning({
                        info: view.$gettext('Sie müssen ein Mikrofon freigeben, um eine Aufnahme starten zu können.')
                    });
                });
        },
        startRecording() {
            let view = this;
            this.chunks = [];
            this.timer = 0;
            this.recorder.start();
            this.isRecording = true;
            setTimeout(function(){ view.setTimer(); }, 1000);
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
                setTimeout(function(){ view.setTimer(); }, 1000);
            }
        },
        async storeRecording() {
            let view = this;
            let user = this.usersById({id: this.userId});
            let blob = new Blob(view.chunks, {type: 'audio/webm; codecs:vp9' });
            let file = {
                attributes: {
                    name: (user.attributes["formatted-name"]).replace(/\s+/g, '_') + '.webm'
                },
                relationships: {
                    'terms-of-use': {
                        data: {
                            id: 'SELFMADE_NONPUB'
                        }
                    }
                }
            };
            let fileObj = await this.createFile({
                file: file,
                filedata: blob,
                folder: {id: this.currentFolderId}
            });
            if(fileObj && fileObj.type === 'file-refs') {
                this.companionSuccess({
                    info: this.$gettext('Die Aufnahme wurde erfolgreich im Dateibereich abgelegt.')
                });
            } else {
                this.companionError({
                    info: this.$gettext('Es ist ein Fehler aufgetreten! Die Aufnahme konnte nicht gespeichert werden.')
                });
            }
            this.newRecording = false;
            this.getFolderFiles();
        },
        resetRecorder() {
            this.newRecording = false;
            this.chunks = [];
            this.timer = 0;
            this.blob = null;
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
    },
};
</script>
