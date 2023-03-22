<template>
    <focus-trap v-model="consumModeTrap">
        <div>
            <div
                :class="{ 'cw-structural-element-consumemode': consumeMode }"
                class="cw-structural-element"
                v-if="validContext"
            >
                <div class="cw-structural-element-content" v-if="structuralElement">
                    <courseware-ribbon :canEdit="canEdit && canAddElements" :isContentBar="true" @blockAdded="updateContainerList">
                        <template #buttons>
                            <router-link v-if="prevElement" :to="'/structural_element/' + prevElement.id">
                                <div class="cw-ribbon-button cw-ribbon-button-prev" :title="textRibbon.perv" />
                            </router-link>
                            <div v-else class="cw-ribbon-button cw-ribbon-button-prev-disabled" :title="$gettext('Keine vorherige Seite')"/>
                            <router-link v-if="nextElement" :to="'/structural_element/' + nextElement.id">
                                <div class="cw-ribbon-button cw-ribbon-button-next" :title="textRibbon.next" />
                            </router-link>
                            <div v-else class="cw-ribbon-button cw-ribbon-button-next-disabled" :title="$gettext('Keine nächste Seite')"/>
                        </template>
                        <template #breadcrumbList>
                            <li
                                v-for="ancestor in ancestors"
                                :key="ancestor.id"
                                :title="ancestor.attributes.title"
                                class="cw-ribbon-breadcrumb-item"
                            >
                                <span>
                                    <router-link :to="'/structural_element/' + ancestor.id">{{ ancestor.attributes.title || "–" }}</router-link>
                                </span>
                            </li>
                            <li
                                class="cw-ribbon-breadcrumb-item cw-ribbon-breadcrumb-item-current"
                                :title="structuralElement.attributes.title"
                            >
                                <span>{{ structuralElement.attributes.title || "–" }}</span>
                                <span v-if="isTask">[ {{ solverName }} ]</span>
                            </li>
                        </template>
                        <template #breadcrumbFallback>
                            <li
                                class="cw-ribbon-breadcrumb-item cw-ribbon-breadcrumb-item-current"
                                :title="structuralElement.attributes.title"
                            >
                                <span>{{ structuralElement.attributes.title }}</span>
                            </li>
                        </template>
                        <template #menu>
                            <studip-action-menu
                                v-if="!consumeMode"
                                :items="menuItems"
                                class="cw-ribbon-action-menu"
                                :context="structuralElement.attributes.title"
                                @editCurrentElement="menuAction('editCurrentElement')"
                                @addElement="menuAction('addElement')"
                                @deleteCurrentElement="menuAction('deleteCurrentElement')"
                                @showInfo="menuAction('showInfo')"
                                @showExportOptions="menuAction('showExportOptions')"
                                @oerCurrentElement="menuAction('oerCurrentElement')"
                                @setBookmark="menuAction('setBookmark')"
                                @sortContainers="menuAction('sortContainers')"
                                @pdfExport="menuAction('pdfExport')"
                                @showSuggest="menuAction('showSuggest')"
                                @linkElement="menuAction('linkElement')"
                                @removeLock="menuAction('removeLock')"
                                @activateFullscreen="menuAction('activateFullscreen')"
                            />
                        </template>
                    </courseware-ribbon>

                    <div v-if="structuralElementLoaded && !isLink" class="cw-companion-box-wrapper">
                        <courseware-companion-box
                            v-if="!canVisit"
                            mood="sad"
                            :msgCompanion="$gettext('Diese Seite steht Ihnen leider nicht zur Verfügung.')"
                        />
                        <courseware-companion-box
                            v-if="blockedByAnotherUser"
                            :msgCompanion="$gettextInterpolate($gettext('Die Einstellungen dieser Seite werden im Moment von %{blockingUserName} bearbeitet'), {blockingUserName: blockingUserName})"
                            mood="pointing"
                        >
                            <template #companionActions>
                                <button v-if="userIsTeacher" class="button" @click="menuAction('removeLock')">
                                    {{ textRemoveLock.title }}
                                </button>
                            </template>
                        </courseware-companion-box>
                        <courseware-empty-element-box
                            v-if="showEmptyElementBox"
                            :canEdit="canEdit"
                            :noContainers="noContainers"
                        />
                        <courseware-welcome-screen v-if="noContainers && isRoot && canEdit" />
                    </div>

                    <div
                        v-if="canVisit && !editView && !isLink"
                        class="cw-container-wrapper"
                        :class="{
                            'cw-container-wrapper-consume': consumeMode,
                            'cw-container-wrapper-discuss': discussView,
                        }"
                    >
                        <courseware-structural-element-discussion
                            v-if="!noContainers && discussView"
                            :structuralElement="structuralElement"
                            :canEdit="canEdit"
                        />
                        <component
                            v-for="container in containers"
                            :key="container.id"
                            :is="containerComponent(container)"
                            :container="container"
                            :canEdit="canEdit"
                            :canAddElements="canAddElements"
                            :isTeacher="userIsTeacher"
                            class="cw-container-item"
                        />
                    </div>
                    <div
                        v-if="isLink"
                        class="cw-container-wrapper"
                        :class="{
                            'cw-container-wrapper-consume': consumeMode,
                            'cw-container-wrapper-discuss': discussView,
                        }"
                    >
                        <courseware-structural-element-discussion
                            v-if="discussView"
                            :structuralElement="structuralElement"
                            :canEdit="canEdit"
                        />
                        <div v-if="editView" class="cw-companion-box-wrapper">
                            <courseware-companion-box
                                :msgCompanion="$gettextInterpolate($gettext('Dieser Inhalt ist aus den persönlichen Lernmaterialien von %{ ownerName } verlinkt und kann nur dort bearbeitet werden.'), { ownerName: ownerName })"
                                mood="pointing"
                            />
                        </div>
                        <component
                            v-for="container in linkedContainers"
                            :key="container.id"
                            :is="containerComponent(container)"
                            :container="container"
                            :canEdit="false"
                            :canAddElements="false"
                            :isTeacher="userIsTeacher"
                            class="cw-container-item"
                        />
                    </div>
                    <div v-if="canVisit && canEdit && editView && !isLink" class="cw-container-wrapper cw-container-wrapper-edit">
                        <template v-if="!processing">
                            <span aria-live="assertive" class="assistive-text">{{ assistiveLive }}</span>
                            <span id="operation" class="assistive-text">
                                {{$gettext('Drücken Sie die Leertaste, um neu anzuordnen.')}}
                            </span>
                            <draggable
                                class="cw-structural-element-list"
                                tag="ol"
                                role="listbox"
                                v-model="containerList"
                                v-bind="dragOptions"
                                handle=".cw-sortable-handle"
                                @start="isDragging = true"
                                @end="dropContainer"
                            >
                                <li
                                    v-for="container in containerList"
                                    :key="container.id"
                                    class="cw-container-item-sortable"
                                >
                                    <span
                                        :class="{ 'cw-sortable-handle-dragging': isDragging }"
                                        class="cw-sortable-handle"
                                        tabindex="0"
                                        role="option"
                                        aria-describedby="operation"
                                        :ref="'sortableHandle' + container.id"
                                        @keydown="keyHandler($event, container.id)"
                                    ></span>
                                    <component
                                        :is="containerComponent(container)"
                                        :container="container"
                                        :canEdit="canEdit"
                                        :canAddElements="canAddElements"
                                        :isTeacher="userIsTeacher"
                                        class="cw-container-item"
                                        ref="containers"
                                        :class="{ 'cw-container-item-selected': keyboardSelected === container.id}"
                                    />
                                </li>
                            </draggable>
                        </template>
                        <studip-progress-indicator v-if="processing" :description="$gettext('Vorgang wird bearbeitet...')" />
                    </div>
                </div>

                <studip-dialog
                    v-if="showEditDialog"
                    :title="textEdit.title"
                    :confirmText="textEdit.confirm"
                    confirmClass="accept"
                    :closeText="textEdit.close"
                    closeClass="cancel"
                    height="500"
                    :width="inContent ? '720' : '500'"
                    class="studip-dialog-with-tab"
                    @close="closeEditDialog"
                    @confirm="storeCurrentElement"
                >
                    <template v-slot:dialogContent>
                        <courseware-tabs class="cw-tab-in-dialog">
                            <courseware-tab :name="textEdit.basic" :selected="true" :index="0">
                                <form class="default" @submit.prevent="">
                                    <label>
                                        <translate>Titel</translate>
                                        <input type="text" v-model="currentElement.attributes.title" />
                                    </label>
                                    <label>
                                        <translate>Beschreibung</translate>
                                        <textarea
                                            v-model="currentElement.attributes.payload.description"
                                            class="cw-structural-element-description"
                                        />
                                    </label>
                                </form>
                            </courseware-tab>
                            <courseware-tab :name="textEdit.meta" :index="1">
                                <form class="default" @submit.prevent="">
                                    <label>
                                        <translate>Farbe</translate>
                                        <studip-select
                                            v-model="currentElement.attributes.payload.color"
                                            :options="colors"
                                            :reduce="(color) => color.class"
                                            label="class"
                                            class="cw-vs-select"
                                        >
                                            <template #open-indicator="selectAttributes">
                                                <span v-bind="selectAttributes"
                                                    ><studip-icon shape="arr_1down" size="10"
                                                /></span>
                                            </template>
                                            <template #no-options>
                                                <translate>Es steht keine Auswahl zur Verfügung</translate>.
                                            </template>
                                            <template #selected-option="{ name, hex }">
                                                <span class="vs__option-color" :style="{ 'background-color': hex }"></span
                                                ><span>{{ name }}</span>
                                            </template>
                                            <template #option="{ name, hex }">
                                                <span class="vs__option-color" :style="{ 'background-color': hex }"></span
                                                ><span>{{ name }}</span>
                                            </template>
                                        </studip-select>
                                    </label>
                                    <label>
                                        <translate>Art des Lernmaterials</translate>
                                        <select v-model="currentElement.attributes.purpose">
                                            <option value="content"><translate>Inhalt</translate></option>
                                            <option v-if="!inCourse"  value="template"><translate>Aufgabenvorlage</translate></option>
                                            <option value="oer"><translate>OER-Material</translate></option>
                                            <option value="portfolio"><translate>ePortfolio</translate></option>
                                            <option value="draft"><translate>Entwurf</translate></option>
                                            <option value="other"><translate>Sonstiges</translate></option>
                                        </select>
                                    </label>
                                    <label>
                                        <translate>Lizenztyp</translate>
                                        <select v-model="currentElement.attributes.payload.license_type">
                                            <option v-for="license in licenses" :key="license.id" :value="license.id">
                                                {{ license.name }}
                                            </option>
                                        </select>
                                    </label>
                                    <label>
                                        <translate>Geschätzter zeitlicher Aufwand</translate>
                                        <input type="text" v-model="currentElement.attributes.payload.required_time" />
                                    </label>
                                    <label>
                                        <translate>Niveau</translate><br />
                                        <translate>von</translate>
                                        <select v-model="currentElement.attributes.payload.difficulty_start">
                                            <option
                                                v-for="difficulty_start in 12"
                                                :key="difficulty_start"
                                                :value="difficulty_start"
                                            >
                                                {{ difficulty_start }}
                                            </option>
                                        </select>
                                        <translate>bis</translate>
                                        <select v-model="currentElement.attributes.payload.difficulty_end">
                                            <option
                                                v-for="difficulty_end in 12"
                                                :key="difficulty_end"
                                                :value="difficulty_end"
                                            >
                                                {{ difficulty_end }}
                                            </option>
                                        </select>
                                    </label>
                                </form>
                            </courseware-tab>
                            <courseware-tab :name="textEdit.image" :index="2">
                                <form class="default" @submit.prevent="">
                                    <img
                                        v-if="showPreviewImage"
                                        :src="image"
                                        class="cw-structural-element-image-preview"
                                        :alt="$gettext('Vorschaubild')"
                                    />
                                    <label v-if="showPreviewImage">
                                        <button class="button" @click="deleteImage" v-translate>Bild löschen</button>
                                    </label>
                                    <div v-if="uploadFileError" class="messagebox messagebox_error">
                                        {{ uploadFileError }}
                                    </div>
                                    <label v-if="!showPreviewImage">
                                        <translate>Bild hochladen</translate>
                                        <input ref="upload_image" type="file" accept="image/*" @change="checkUploadFile" />
                                    </label>
                                </form>
                            </courseware-tab>
                            <courseware-tab :name="textEdit.approval" :index="3">
                                <courseware-structural-element-permissions
                                    v-if="inCourse"
                                    :element="currentElement"
                                    @updateReadApproval="updateReadApproval"
                                    @updateWriteApproval="updateWriteApproval"
                                />
                                <courseware-content-permissions
                                    v-if="inContent"
                                    :element="currentElement"
                                    @updateReadApproval="updateReadApproval"
                                    @updateWriteApproval="updateWriteApproval"
                                />
                            </courseware-tab>
                            <courseware-tab v-if="inCourse" :name="textEdit.visible" :index="4">
                                <form class="default" @submit.prevent="">
                                    <label>
                                        <translate>Sichtbar ab</translate>
                                        <input type="date" v-model="currentElement.attributes['release-date']" />
                                    </label>
                                    <label>
                                        <translate>Unsichtbar ab</translate>
                                        <input type="date" v-model="currentElement.attributes['withdraw-date']" />
                                    </label>
                                </form>
                            </courseware-tab>
                        </courseware-tabs>
                    </template>
                </studip-dialog>

                <studip-dialog
                    v-if="showAddDialog"
                    :title="$gettext('Seite hinzufügen')"
                    :confirmText="$gettext('Erstellen')"
                    confirmClass="accept"
                    :closeText="$gettext('Schließen')"
                    closeClass="cancel"
                    class="cw-structural-element-dialog"
                    :height="inCourse ? '300' : '430'"
                    @close="closeAddDialog"
                    @confirm="createElement"
                >
                    <template v-slot:dialogContent>
                        <form class="default" @submit.prevent="">
                            <label>
                                <translate>Position der neuen Seite</translate>
                                <select v-model="newChapterParent">
                                    <option v-if="!isRoot && canEditParent" value="sibling">
                                        <translate>Neben der aktuellen Seite</translate>
                                    </option>
                                    <option value="descendant"><translate>Unterhalb der aktuellen Seite</translate></option>
                                </select>
                            </label>
                            <label>
                                <translate>Name der neuen Seite</translate><br />
                                <input v-model="newChapterName" type="text" />
                            </label>
                            <label v-if="!inCourse">
                                <translate>Art des Lernmaterials</translate>
                                <select v-model="newChapterPurpose">
                                    <option value="content"><translate>Inhalt</translate></option>
                                    <option v-if="!inCourse" value="template"><translate>Aufgabenvorlage</translate></option>
                                    <option value="oer"><translate>OER-Material</translate></option>
                                    <option value="portfolio"><translate>ePortfolio</translate></option>
                                    <option value="draft"><translate>Entwurf</translate></option>
                                    <option value="other"><translate>Sonstiges</translate></option>
                                </select>
                            </label>
                            <label v-if="!inCourse">
                                <translate>Lernmaterialvorlage</translate>
                                <select v-model="newChapterTemplate">
                                    <option :value="null"><translate>ohne Vorlage</translate></option>
                                    <option
                                        v-for="template in selectableTemplates"
                                        :key="template.id"
                                        :value="template"
                                    >
                                        {{ template.attributes.name }}
                                    </option>
                                </select>
                            </label>
                        </form>
                    </template>
                </studip-dialog>

                <studip-dialog
                    v-if="showInfoDialog"
                    :title="textInfo.title"
                    :closeText="textInfo.close"
                    closeClass="cancel"
                    @close="showElementInfoDialog(false)"
                >
                    <template v-slot:dialogContent>
                        <table class="cw-structural-element-info">
                            <tr>
                                <td><translate>Titel</translate>:</td>
                                <td>{{ structuralElement.attributes.title }}</td>
                            </tr>
                            <tr>
                                <td><translate>Beschreibung</translate>:</td>
                                <td>{{ structuralElement.attributes.payload.description }}</td>
                            </tr>
                            <tr>
                                <td><translate>Seite wurde erstellt von</translate>:</td>
                                <td>{{ ownerName }}</td>
                            </tr>
                            <tr>
                                <td><translate>Seite wurde erstellt am</translate>:</td>
                                <td><iso-date :date="structuralElement.attributes.mkdate" /></td>
                            </tr>
                            <tr>
                                <td><translate>Zuletzt bearbeitet von</translate>:</td>
                                <td>{{ editorName }}</td>
                            </tr>
                            <tr>
                                <td><translate>Zuletzt bearbeitet am</translate>:</td>
                                <td><iso-date :date="structuralElement.attributes.chdate" /></td>
                            </tr>
                        </table>
                    </template>
                </studip-dialog>

                <studip-dialog
                    v-if="showExportDialog"
                    :title="textExport.title"
                    :confirmText="textExport.confirm"
                    confirmClass="accept"
                    :closeText="textExport.close"
                    closeClass="cancel"
                    height="350"
                    @close="showElementExportDialog(false)"
                    @confirm="exportCurrentElement"
                >
                    <template v-slot:dialogContent>
                        <div v-show="!exportRunning">
                            <span v-translate>Hiermit exportieren Sie die Seite "%{ currentElement.attributes.title }" als ZIP-Datei.</span>
                            <div class="cw-element-export">
                                <label>
                                    <input type="checkbox" v-model="exportChildren" />
                                    <translate>Unterseiten exportieren</translate>
                                </label>
                            </div>
                        </div>

                        <courseware-companion-box
                            v-show="exportRunning"
                            :msgCompanion="$gettext('Export läuft, bitte haben sie einen Moment Geduld...')"
                            mood="pointing"
                        />
                        <div v-show="exportRunning" class="cw-import-zip">
                            <header>{{ exportState }}:</header>
                            <div class="progress-bar-wrapper">
                                <div
                                    class="progress-bar"
                                    role="progressbar"
                                    :style="{ width: exportProgress + '%' }"
                                    :aria-valuenow="exportProgress"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                >
                                    {{ exportProgress }}%
                                </div>
                            </div>
                        </div>
                    </template>
                </studip-dialog>

                <studip-dialog
                    v-if="showPdfExportDialog"
                    :title="textExport.title"
                    :confirmText="textExport.confirm"
                    confirmClass="accept"
                    :closeText="textExport.close"
                    closeClass="cancel"
                    height="350"
                    @close="showElementPdfExportDialog(false)"
                    @confirm="pdfExportCurrentElement"
                >
                    <template v-slot:dialogContent>
                        <span v-translate>Hiermit exportieren Sie die Seite "%{ currentElement.attributes.title }" als PDF-Datei.</span>
                            <div class="cw-element-export">
                                <label>
                                    <input type="checkbox" v-model="pdfExportChildren" />
                                    <translate>Unterseiten exportieren</translate>
                                </label>
                            </div>
                    </template>
                </studip-dialog>

                <studip-dialog
                    v-if="showOerDialog"
                    height="600"
                    width="600"
                    :title="textOer.title"
                    :confirmText="textOer.confirm"
                    confirmClass="accept"
                    :closeText="textOer.close"
                    closeClass="cancel"
                    @close="showElementOerDialog(false)"
                    @confirm="publishCurrentElement"
                >
                    <template v-slot:dialogContent>
                        <form v-show="!oerExportRunning" class="default" @submit.prevent="">
                            <fieldset>
                                <legend><translate>Grunddaten</translate></legend>
                                <label>
                                    <p><translate>Vorschaubild</translate>:</p>
                                    <img
                                        v-if="currentElement.relationships.image.data"
                                        :src="currentElement.relationships.image.meta['download-url']"
                                        width="400"
                                    />
                                </label>
                                <label>
                                    <p><translate>Beschreibung</translate>:</p>
                                    <p>{{ currentElement.attributes.payload.description }}</p>
                                </label>
                                <label>
                                    <translate>Niveau</translate>:
                                    <p>
                                        {{ currentElement.attributes.payload.difficulty_start }} -
                                        {{ currentElement.attributes.payload.difficulty_end }}
                                    </p>
                                </label>
                                <label>
                                    <translate>Lizenztyp</translate>:
                                    <p>{{ currentLicenseName }}</p>
                                </label>
                                <label>
                                    <translate>Sie können diese Daten unter "Seite bearbeiten" verändern.</translate>
                                </label>
                            </fieldset>
                            <fieldset>
                                <legend><translate>Einstellungen</translate></legend>
                                <label>
                                    <translate>Unterseiten veröffentlichen</translate>
                                    <input type="checkbox" v-model="oerChildren" />
                                </label>
                            </fieldset>
                        </form>
                        <courseware-companion-box
                            v-show="oerExportRunning"
                            :msgCompanion="$gettext('Export läuft, bitte haben sie einen Moment Geduld...')"
                            mood="pointing"
                        />
                    </template>
                </studip-dialog>
                <studip-dialog
                    v-if="showSuggestOerDialog"
                    height="600"
                    width="600"
                    :title="textSuggestOer.title"
                    :confirmText="textSuggestOer.confirm"
                    confirmClass="accept"
                    :closeText="textSuggestOer.close"
                    closeClass="cancel"
                    @close="updateShowSuggestOerDialog(false)"
                    @confirm="sendOerSuggestion"
                >
                    <template v-slot:dialogContent>
                        <p v-translate>Das folgende Courseware-Material wird %{ ownerName }
                            zur Veröffentlichung im OER Campus vorgeschlagen:</p>
                        <table class="cw-structural-element-info">
                            <tr>
                                <td><translate>Titel</translate>:</td>
                                <td>{{ structuralElement.attributes.title }}</td>
                            </tr>
                            <tr>
                                <td><translate>Beschreibung</translate>:</td>
                                <td>{{ structuralElement.attributes.payload.description }}</td>
                            </tr>
                        </table>
                        <form class="default" @submit.prevent="">
                            <label>
                                <translate>Ihr Vorschlag wird anonym versendet. Falls gewünscht, können Sie
                                    zusätzlich eine Nachricht verfassen:</translate>
                                <textarea
                                    v-model="additionalText"
                                    class="cw-structural-element-description"
                                />
                            </label>
                        </form>
                    </template>
                </studip-dialog>
                <studip-dialog
                    v-if="showDeleteDialog"
                    :title="textDelete.title"
                    :question="textDelete.alert"
                    height="200"
                    @confirm="deleteCurrentElement"
                    @close="closeDeleteDialog"
                ></studip-dialog>
                <studip-dialog
                    v-if="showPublicLinkDialog && inContent"
                    :title="$gettext('Öffentlichen Link für Seite erzeugen')"
                    :confirmText="$gettext('Erstellen')"
                    confirmClass="accept"
                    :closeText="$gettext('Abbrechen')"
                    closeClass="cancel"
                    class="cw-structural-element-dialog"
                    @close="closePublicLinkDialog"
                    @confirm="createElementPublicLink"
                >
                    <template v-slot:dialogContent>
                        <form class="default" @submit.prevent="">
                            <label>
                                <translate>Passwort</translate>
                                <input type="password" v-model="publicLink.password" />
                            </label>
                            <label>
                                <translate>Ablaufdatum</translate>
                                <input v-model="publicLink['expire-date']" type="date" class="size-l" />
                            </label>
                        </form>
                    </template>
                </studip-dialog>
                <studip-dialog
                    v-if="showRemoveLockDialog"
                    :title="textRemoveLock.title"
                    :question="textRemoveLock.alert"
                    height="200"
                    width="450"
                    @confirm="executeRemoveLock"
                    @close="showElementRemoveLockDialog(false)"
                ></studip-dialog>

                <courseware-structural-element-dialog-import v-if="showImportDialog"/>
                <courseware-structural-element-dialog-copy v-if="showCopyDialog" />
                <courseware-structural-element-dialog-link v-if="showLinkDialog"/>
            </div>
            <div v-else>
                <courseware-companion-box
                    v-if="currentElement !== ''"
                    :msgCompanion="textCompanionWrongContext"
                    mood="sad"
                />
            </div>
        </div>
    </focus-trap>
</template>

<script>
import ContainerComponents from './container-components.js';
import CoursewarePluginComponents from './plugin-components.js';
import CoursewareStructuralElementDialogCopy from './CoursewareStructuralElementDialogCopy.vue';
import CoursewareStructuralElementDialogImport from './CoursewareStructuralElementDialogImport.vue';
import CoursewareStructuralElementDialogLink from './CoursewareStructuralElementDialogLink.vue';
import CoursewareStructuralElementDiscussion from './CoursewareStructuralElementDiscussion.vue';
import CoursewareStructuralElementPermissions from './CoursewareStructuralElementPermissions.vue';
import CoursewareContentPermissions from './CoursewareContentPermissions.vue';
import CoursewareAccordionContainer from './CoursewareAccordionContainer.vue';
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import CoursewareWelcomeScreen from './CoursewareWelcomeScreen.vue';
import CoursewareEmptyElementBox from './CoursewareEmptyElementBox.vue';
import CoursewareListContainer from './CoursewareListContainer.vue';
import CoursewareTabsContainer from './CoursewareTabsContainer.vue';
import CoursewareRibbon from './CoursewareRibbon.vue';
import CoursewareTabs from './CoursewareTabs.vue';
import CoursewareTab from './CoursewareTab.vue';
import CoursewareExport from '@/vue/mixins/courseware/export.js';
import CoursewareOerMessage from '@/vue/mixins/courseware/oermessage.js';
import colorMixin from '@/vue/mixins/courseware/colors.js';
import CoursewareDateInput from './CoursewareDateInput.vue';
import { FocusTrap } from 'focus-trap-vue';
import IsoDate from './IsoDate.vue';
import StudipDialog from '../StudipDialog.vue';
import draggable from 'vuedraggable';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-structural-element',
    components: {
        CoursewareStructuralElementDialogCopy,
        CoursewareStructuralElementDialogImport,
        CoursewareStructuralElementDialogLink,
        CoursewareStructuralElementDiscussion,
        CoursewareStructuralElementPermissions,
        CoursewareContentPermissions,
        CoursewareRibbon,
        CoursewareListContainer,
        CoursewareAccordionContainer,
        CoursewareTabsContainer,
        CoursewareCompanionBox,
        CoursewareWelcomeScreen,
        CoursewareEmptyElementBox,
        CoursewareTabs,
        CoursewareTab,
        CoursewareDateInput,
        FocusTrap,
        IsoDate,
        StudipDialog,
        draggable,
    },
    props: ['canVisit', 'orderedStructuralElements', 'structuralElement'],

    mixins: [CoursewareExport, CoursewareOerMessage, colorMixin],

    data() {
        return {
            newChapterName: '',
            newChapterParent: 'descendant',
            newChapterPurpose: 'content',
            newChapterTemplate: null,
            currentElement: '',
            uploadFileError: '',
            textCompanionWrongContext: this.$gettext('Die angeforderte Seite ist nicht Teil dieser Courseware.'),
            textEdit: {
                title: this.$gettext('Seite bearbeiten'),
                confirm: this.$gettext('Speichern'),
                close: this.$gettext('Schließen'),
                basic: this.$gettext('Grunddaten'),
                image: this.$gettext('Bild'),
                meta: this.$gettext('Metadaten'),
                approval: this.$gettext('Rechte'),
                visible: this.$gettext('Sichtbarkeit'),
            },
            textInfo: {
                title: this.$gettext('Informationen zur Seite'),
                close: this.$gettext('Schließen'),
            },
            textExport: {
                title: this.$gettext('Seite exportieren'),
                confirm: this.$gettext('Exportieren'),
                close: this.$gettext('Schließen'),
            },
            textAdd: {
                title: this.$gettext('Seite hinzufügen'),
                confirm: this.$gettext('Erstellen'),
                close: this.$gettext('Schließen'),
            },
            textRibbon: {
                perv: this.$gettext('zurück'),
                next: this.$gettext('weiter'),
            },
            textRemoveLock: {
                title: this.$gettext('Sperre aufheben'),
                alert: this.$gettext('Möchten Sie die Sperre der Seite wirklich aufheben?'),
            },
            exportRunning: false,
            exportChildren: false,
            oerExportRunning: false,
            oerChildren: true,
            pdfExportChildren: false,
            containerList: [],
            isDragging: false,
            dragOptions: {
                animation: 0,
                group: 'description',
                disabled: false,
                ghostClass: 'container-ghost',
            },
            errorEmptyChapterName: false,
            consumModeTrap: false,
            additionalText: '',

            publicLink: {
                passsword: '',
                'expire-date': ''
            },
            deletingPreviewImage: false,
            processing: false,
            keyboardSelected: null,
            assistiveLive: ''
        };
    },

    computed: {
        ...mapGetters({
            courseware: 'courseware',
            context: 'context',
            consumeMode: 'consumeMode',
            containerById: 'courseware-containers/byId',
            relatedContainers: 'courseware-containers/related',
            relatedStructuralElements: 'courseware-structural-elements/related',
            relatedTaskGroups: 'courseware-task-groups/related',
            relatedUsers: 'users/related',
            structuralElementById: 'courseware-structural-elements/byId',
            userIsTeacher: 'userIsTeacher',
            pluginManager: 'pluginManager',
            showEditDialog: 'showStructuralElementEditDialog',
            showAddDialog: 'showStructuralElementAddDialog',
            showImportDialog: 'showStructuralElementImportDialog',
            showCopyDialog: 'showStructuralElementCopyDialog',
            showLinkDialog: 'showStructuralElementLinkDialog',
            showExportDialog: 'showStructuralElementExportDialog',
            showPdfExportDialog: 'showStructuralElementPdfExportDialog',
            showInfoDialog: 'showStructuralElementInfoDialog',
            showDeleteDialog: 'showStructuralElementDeleteDialog',
            showOerDialog: 'showStructuralElementOerDialog',
            showSuggestOerDialog: 'showSuggestOerDialog',
            showPublicLinkDialog: 'showStructuralElementPublicLinkDialog',
            showRemoveLockDialog: 'showStructuralElementRemoveLockDialog',
            oerEnabled: 'oerEnabled',
            licenses: 'licenses',
            exportState: 'exportState',
            exportProgress: 'exportProgress',
            userId: 'userId',
            viewMode: 'viewMode',
            taskById: 'courseware-tasks/byId',
            userById: 'users/byId',
            lastCreatedElement: 'courseware-structural-elements/lastCreated',
            groupById: 'status-groups/byId',

            blocked: 'currentElementBlocked',
            blockerId: 'currentElementBlockerId',
            blockedByThisUser: 'currentElementBlockedByThisUser',
            blockedByAnotherUser: 'currentElementBlockedByAnotherUser',
            isLink: 'currentElementisLink',

            templates: 'courseware-templates/all',
        }),

        currentId() {
            return this.structuralElement?.id;
        },

        textOer() {
            return {
                title: this.$gettext('Lerninhalte auf dem OER Campus veröffentlichen'),
                confirm: this.$gettext('Veröffentlichen'),
                close: this.$gettext('Abbrechen'),
            };
        },

        textSuggestOer() {
            return {
                title: this.$gettext('Lerninhalt für den OER Campus vorschlagen'),
                confirm: this.$gettext('Lerninhalt vorschlagen'),
                close: this.$gettext('Abbrechen'),
            };
        },

        inCourse() {
            return this.context.type === 'courses';
        },

        inContent() {
            // The rights tab in contents will be only visible to the owner.
            return this.context.type === 'users' && this.userId === this.currentElement.relationships.user.data.id;
        },

        textDelete() {
            let textDelete = {};
            textDelete.title = this.$gettext('Seite unwiderruflich löschen');
            textDelete.alert = this.$gettext('Möchten Sie die Seite wirklich löschen?');
            if (this.structuralElementLoaded) {
                textDelete.alert =
                    this.$gettextInterpolate(
                        this.$gettext('Möchten Sie die Seite %{ pageTitle } und alle ihre Unterseiten wirklich löschen?'),
                        {pageTitle: this.structuralElement.attributes.title}
                    );
            }

            return textDelete;
        },

        validContext() {
            let valid = false;
            if (this.context.type === 'courses' && this.currentElement.relationships) {
                if (
                    this.currentElement.relationships.course &&
                    this.context.id === this.currentElement.relationships.course.data.id
                ) {
                    valid = true;
                }
            }

            if (this.context.type === 'users' && this.currentElement.relationships) {
                if (
                    this.currentElement.relationships.user &&
                    this.context.id === this.currentElement.relationships.user.data.id
                ) {
                    valid = true;
                }
            }
            if (this.context.type === 'sharedusers') {
                if (this.context.id === this.courseware.relationships.root.data.id) {
                    valid = true;
                }
            }

            if (this.context.type === 'public') {
                valid = true;
            }

            return valid;
        },

        image() {
            return this.structuralElement.relationships?.image?.meta?.['download-url'] ?? null;
        },

        showPreviewImage() {
            return this.image !== null && this.deletingPreviewImage === false;
        },

        structuralElementLoaded() {
            return this.structuralElement !== null && this.structuralElement !== {};
        },

        ancestors() {
            if (!this.structuralElement) {
                return [];
            }

            const finder = (parent) => {
                const parentId = parent.relationships?.parent?.data?.id;
                if (!parentId) {
                    return null;
                }
                const element = this.structuralElementById({ id: parentId });
                if (!element) {
                    console.error(`CoursewareStructuralElement#ancestors: Could not find parent by ID: "${parentId}".`);
                }

                return element;
            };

            const visitAncestors = function* (node) {
                const parent = finder(node);
                if (parent) {
                    yield parent;
                    yield* visitAncestors(parent);
                }
            };

            return [...visitAncestors(this.structuralElement)].reverse();
        },
        prevElement() {
            const currentIndex = this.orderedStructuralElements.indexOf(this.structuralElement.id);
            if (currentIndex <= 0) {
                return null;
            }
            const previousId = this.orderedStructuralElements[currentIndex - 1];
            const previous = this.structuralElementById({ id: previousId });

            return previous;
        },
        nextElement() {
            const currentIndex = this.orderedStructuralElements.indexOf(this.structuralElement.id);
            const lastIndex = this.orderedStructuralElements.length - 1;
            if (currentIndex === -1 || currentIndex === lastIndex) {
                return null;
            }
            const nextId = this.orderedStructuralElements[currentIndex + 1];
            const next = this.structuralElementById({ id: nextId });

            return next;
        },
        empty() {
            if (this.containers === null) {
                return true;
            } else {
                return !this.containers.some((container) => container.relationships.blocks.data.length > 0);
            }
        },
        containers() {
            if (!this.structuralElement) {
                return [];
            }

            return (
                this.relatedContainers({
                    parent: this.structuralElement,
                    relationship: 'containers',
                }) ?? []
            );
        },
        noContainers() {
            if (this.containers === null) {
                return true;
            } else {
                return this.containers.length === 0;
            }
        },

        canEdit() {
            if (!this.structuralElement) {
                return false;
            }
            return this.structuralElement.attributes['can-edit'];
        },

        canEditParent() {
            if (this.isRoot) {
                return false;
            }
            const parentId = this.structuralElement.relationships.parent.data.id;
            const parent = this.structuralElementById({ id: parentId });

            return parent.attributes['can-edit'];
        },

        isRoot() {
            return this.structuralElement.relationships.parent.data === null;
        },

        editor() {
            const editor = this.relatedUsers({
                parent: this.structuralElement,
                relationship: 'editor',
            });

            return editor ?? null;
        },

        editorName() {
            return this.editor?.attributes['formatted-name'] ?? '?';
        },

        menuItems() {
            let menu = [
                { id: 4, label: this.$gettext('Informationen anzeigen'), icon: 'info', emit: 'showInfo' },
                { id: 5, label: this.$gettext('Lesezeichen setzen'), icon: 'star', emit: 'setBookmark' },
                { id: 6, label: this.$gettext('Lerninhalt für OER Campus vorschlagen'), icon: 'oer-campus',
                    emit: 'showSuggest' }
            ];

            if (!document.documentElement.classList.contains('responsive-display')) {
                menu.push(
                    { id: 7, label: this.$gettext('Als Vollbild anzeigen'), icon: 'screen-full',
                        emit: 'activateFullscreen'},
                );
            }

            if (this.canEdit) {
                if (!this.blockedByAnotherUser) {
                    menu.push({
                        id: 1,
                        label: this.$gettext('Seite bearbeiten'),
                        icon: 'edit',
                        emit: 'editCurrentElement',
                    });
                }
                if (this.blockedByAnotherUser && this.userIsTeacher) {
                    menu.push({
                        id: 1,
                        label: this.textRemoveLock.title,
                        icon: 'lock-unlocked',
                        emit: 'removeLock',
                    });
                }
                menu.push({ id: 3, label: this.$gettext('Seite hinzufügen'), icon: 'add', emit: 'addElement' });
            }
            if (this.context.type === 'users') {
                menu.push({ id: 8, label: this.$gettext('Öffentlichen Link erzeugen'), icon: 'group', emit: 'linkElement' });
            }
            if (!this.isRoot && this.canEdit && !this.isTask && !this.blocked) {
                menu.push({
                    id: 8,
                    label: this.$gettext('Seite löschen'),
                    icon: 'trash',
                    emit: 'deleteCurrentElement',
                });
            }
            menu.sort((a, b) => a.id - b.id);

            return menu;
        },
        colors() {
            return this.mixinColors.filter(color => color.darkmode);
        },
        currentLicenseName() {
            for (let i = 0; i < this.licenses.length; i++) {
                if (this.licenses[i]['id'] == this.currentElement.attributes.payload.license_type) {
                    return this.licenses[i]['name'];
                }
            }

            return '';
        },
        blockingUser() {
            if (this.blockedByAnotherUser) {
                return this.userById({id: this.blockerId});
            }

            return null;
        },
        blockingUserName() {
            return this.blockingUser ? this.blockingUser.attributes['formatted-name'] : '';
        },
        discussView() {
            return this.viewMode === 'discuss';
        },
        editView() {
            return this.viewMode === 'edit';
        },
        pdfExportURL() {
            if (this.context.type === 'users') {
                return STUDIP.URLHelper.getURL(
                    'dispatch.php/contents/courseware/pdf_export/' + this.structuralElement.id
                );
            }
            if (this.context.type === 'courses') {
                return STUDIP.URLHelper.getURL(
                    'dispatch.php/course/courseware/pdf_export/' + this.structuralElement.id
                );
            }

            return '';
        },
        isTask() {
            return this.structuralElement?.relationships.task.data !== null;
        },
        task() {
            if (!this.isTask) {
                return null;
            }

            return this.taskById({ id: this.structuralElement.relationships.task.data.id });
        },
        solver() {
            if (this.task) {
                const solver = this.task.relationships.solver.data;
                if (solver.type === 'users') {
                    return this.userById({ id: solver.id });
                }
                if (solver.type === 'status-groups') {
                    return this.groupById({ id: solver.id });
                }
            }

            return null;
        },
        solverName() {
            if (this.solver) {
                if (this.solver.type === 'users') {
                    return this.solver.attributes['formatted-name'];
                }
                if (this.solver.type === 'status-groups') {
                    return this.solver.attributes.name;
                }
            }

            return '';
        },
        canAddElements() {
            if (!this.isTask) {
                return true;
            }

            // still loading
            if (!this.task) {
                return false;
            }

            const taskGroup = this.relatedTaskGroups({ parent: this.task, relationship: 'task-group' });

            return taskGroup?.attributes['solver-may-add-blocks'];
        },
        showEmptyElementBox() {
            if (!this.empty) {
                return false;
            }

            return (
                (!this.isRoot && this.canEdit) || !this.canEdit || (!this.noContainers && this.isRoot && this.canEdit)
            );
        },

        linkedElement() {
            if (this.isLink) {
                return this.structuralElementById({ id: this.structuralElement.attributes['target-id']});
            }

            return null;
        },

        linkedContainers() {
            let containers = [];
            let relatedContainers = this.linkedElement?.relationships?.containers?.data;

            if (relatedContainers) {
                for (const container of relatedContainers) {
                    containers.push(this.containerById({ id: container.id}));
                }
            }

            return containers;
        },

        owner() {
            const owner = this.relatedUsers({
                parent: this.structuralElement,
                relationship: 'owner',
            });
            return owner ?? null;
        },

        ownerName() {
            return this.owner?.attributes['formatted-name'] ?? '?';
        },
        selectableTemplates() {
            return this.templates.filter(template => {
                return template.attributes.purpose === this.newChapterPurpose
            });
        },
    },

    methods: {
        ...mapActions({
            createStructuralElementWithTemplate: 'createStructuralElementWithTemplate',
            updateStructuralElement: 'updateStructuralElement',
            deleteStructuralElement: 'deleteStructuralElement',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
            addBookmark: 'addBookmark',
            companionInfo: 'companionInfo',
            companionWarning: 'companionWarning',
            companionError: 'companionError',
            uploadImageForStructuralElement: 'uploadImageForStructuralElement',
            deleteImageForStructuralElement: 'deleteImageForStructuralElement',
            companionSuccess: 'companionSuccess',
            showElementEditDialog: 'showElementEditDialog',
            showElementAddDialog: 'showElementAddDialog',
            showElementExportDialog: 'showElementExportDialog',
            showElementPdfExportDialog: 'showElementPdfExportDialog',
            showElementInfoDialog: 'showElementInfoDialog',
            showElementDeleteDialog: 'showElementDeleteDialog',
            showElementOerDialog: 'showElementOerDialog',
            showElementPublicLinkDialog: 'showElementPublicLinkDialog',
            showElementRemoveLockDialog: 'showElementRemoveLockDialog',
            updateShowSuggestOerDialog: 'updateShowSuggestOerDialog',
            updateContainer: 'updateContainer',
            sortContainersInStructualElements: 'sortContainersInStructualElements',
            loadTask: 'loadTask',
            loadStructuralElement: 'loadStructuralElement',
            createLink: 'createLink',
            setCurrentElementId: 'coursewareCurrentElement',
        }),

        initCurrent() {
            this.currentElement = _.cloneDeep(this.structuralElement);
            this.uploadFileError = '';
            this.deletingPreviewImage = false;
        },
        async menuAction(action) {
            switch (action) {
                case 'removeLock':
                    this.displayRemoveLockDialog();
                    break;
                case 'editCurrentElement':
                    await this.loadStructuralElement(this.currentId);
                    if (this.blockedByAnotherUser) {
                        this.companionInfo({ info: this.$gettext('Diese Seite wird bereits bearbeitet.') });

                        return false;
                    }
                    try {
                        await this.lockObject({ id: this.currentId, type: 'courseware-structural-elements' });
                    } catch(error) {
                        if (error.status === 409) {
                            this.companionInfo({ info: this.$gettext('Diese Seite wird bereits bearbeitet.') });
                        } else {
                            console.log(error);
                        }

                        return false;
                    }
                    this.initCurrent();
                    this.showElementEditDialog(true);
                    break;
                case 'addElement':
                    this.newChapterName = '';
                    this.newChapterParent = 'descendant';
                    this.errorEmptyChapterName = false;
                    this.showElementAddDialog(true);
                    break;
                case 'deleteCurrentElement':
                    await this.loadStructuralElement(this.currentId);
                    if (this.blockedByAnotherUser) {
                        this.companionInfo({
                            info: this.$gettextInterpolate(
                                this.$gettext('Löschen nicht möglich, da %{blockingUserName} die Seite bearbeitet.'),
                                {blockingUserName: this.blockingUserName}
                            )
                        });

                        return false;
                    }
                    await this.lockObject({ id: this.currentId, type: 'courseware-structural-elements' });
                    this.showElementDeleteDialog(true);
                    break;
                case 'showInfo':
                    this.showElementInfoDialog(true);
                    break;
                case 'showExportOptions':
                    this.showElementExportDialog(true);
                    break;
                case 'oerCurrentElement':
                    this.showElementOerDialog(true);
                    break;
                case 'showSuggest':
                    this.updateShowSuggestOerDialog(true);
                    break;
                case 'setBookmark':
                    this.setBookmark();
                    break;
                case 'linkElement':
                    this.showElementPublicLinkDialog(true);
                    break;
                case 'activateFullscreen':
                    STUDIP.Fullscreen.activate();
                    break;
            }
        },
        async closeEditDialog() {
            await this.loadStructuralElement(this.currentElement.id);
            if (this.blockedByThisUser) {
                await this.unlockObject({ id: this.currentId, type: 'courseware-structural-elements' });
                await this.loadStructuralElement(this.currentElement.id);
            }
            this.showElementEditDialog(false);
            this.initCurrent();
        },
        closeAddDialog() {
            this.showElementAddDialog(false);
        },
        checkUploadFile() {
            const file = this.$refs?.upload_image?.files[0];
            if (file.size > 2097152) {
                this.uploadFileError = this.$gettext('Diese Datei ist zu groß. Bitte wählen Sie eine kleinere Datei.');
            } else if (!file.type.includes('image')) {
                this.uploadFileError = this.$gettext('Diese Datei ist kein Bild. Bitte wählen Sie ein Bild aus.');
            } else {
                this.uploadFileError = '';
            }
        },
        deleteImage() {
            if (!this.deletingPreviewImage) {
                this.deletingPreviewImage = true;
            }
        },
        async storeCurrentElement() {
            await this.loadStructuralElement(this.currentElement.id);
            if (this.blockedByAnotherUser) {
                this.companionWarning({
                    info: this.$gettextInterpolate(
                        this.$gettext('Ihre Änderungen konnten nicht gespeichert werden, da %{blockingUserName} die Bearbeitung übernommen hat.'),
                        {blockingUserName: this.blockingUserName}
                    )
                });
                this.showElementEditDialog(false);
                return false;
            }
            if (!this.blocked) {
                await this.lockObject({ id: this.currentId, type: 'courseware-structural-elements' });
            }
            const file = this.$refs?.upload_image?.files[0];
            if (file) {
                if (file.size > 2097152) {
                    return false;
                }

                this.uploadFileError = '';
                this.uploadImageForStructuralElement({
                    structuralElement: this.currentElement,
                    file,
                }).catch((error) => {
                    console.error(error);
                    this.uploadFileError = this.$gettext('Fehler beim Hochladen der Datei.');
                });
                await this.loadStructuralElement(this.currentElement.id);
            } else if (this.deletingPreviewImage) {
                await this.deleteImageForStructuralElement(this.currentElement);
            }
            this.showElementEditDialog(false);

            if (this.currentElement.attributes['release-date'] !== '') {
                this.currentElement.attributes['release-date'] =
                    new Date(this.currentElement.attributes['release-date']).getTime() / 1000;
            }

            if (this.currentElement.attributes['withdraw-date'] !== '') {
                this.currentElement.attributes['withdraw-date'] =
                    new Date(this.currentElement.attributes['withdraw-date']).getTime() / 1000;
            }

            await this.updateStructuralElement({
                element: this.currentElement,
                id: this.currentId,
            });
            await this.unlockObject({ id: this.currentId, type: 'courseware-structural-elements' });
            this.$emit('select', this.currentId);
            this.initCurrent();
        },

        dropContainer() {
            this.isDragging = false;
            this.storeSort();
        },

        async storeSort() {
            const timeout = setTimeout(() => this.processing = true, 800);
            if (this.blockedByAnotherUser) {
                this.companionInfo({ info: this.$gettext('Diese Seite wird bereits bearbeitet.') });
                clearTimeout(timeout);
                this.processing = false;
                return false;
            }
            try {
                await this.lockObject({ id: this.currentId, type: 'courseware-structural-elements' });
            } catch (error) {
                if (error.status === 409) {
                    this.companionInfo({ info: this.$gettext('Diese Seite wird bereits bearbeitet.') });
                } else {
                    console.log(error);
                }

                clearTimeout(timeout);
                this.processing = false;
                return false;
            }

            await this.sortContainersInStructualElements({
                structuralElement: this.structuralElement,
                containers: this.containerList,
            });
            this.$emit('select', this.currentId);

            clearTimeout(timeout);
            this.processing = false;
        },

        async exportCurrentElement(data) {
            if (this.exportRunning) {
                return;
            }

            this.exportRunning = true;

            await this.sendExportZip(this.currentElement.id, {
                withChildren: this.exportChildren,
            });

            this.exportRunning = false;
            this.showElementExportDialog(false);
        },

        pdfExportCurrentElement() {
            this.showElementPdfExportDialog(false);
            let url = '';
            let withChildren = this.pdfExportChildren ? '/1' : '/0';
            if (this.context.type === 'users') {
                url = STUDIP.URLHelper.getURL('dispatch.php/contents/courseware/pdf_export/' + this.structuralElement.id + withChildren);
            }
            if (this.context.type === 'courses') {
                url = STUDIP.URLHelper.getURL('dispatch.php/course/courseware/pdf_export/' + this.structuralElement.id + withChildren);
            }

            if (url) {
                window.open(url , '_blank').focus();
            }
        },

        async publishCurrentElement() {
            if (this.oerExportRunning) {
                return;
            }
            this.oerExportRunning = true;
            await this.exportToOER(this.currentElement, { withChildren: this.oerChildren });
            this.oerExportRunning = false;
            this.showElementOerDialog(false);
        },

        async closeDeleteDialog() {
            await this.loadStructuralElement(this.currentElement.id);
            if (this.blockedByThisUser) {
                await this.unlockObject({ id: this.currentId, type: 'courseware-structural-elements' });
            }
            this.showElementDeleteDialog(false);
        },
        async deleteCurrentElement() {
            await this.loadStructuralElement(this.currentElement.id);
            if (this.blockedByAnotherUser) {
                this.companionWarning({
                    info: this.$gettextInterpolate(
                        this.$gettext('Löschen nicht möglich, da %{blockingUserName} die Bearbeitung übernommen hat.'),
                        {blockingUserName: this.blockingUserName}
                    )
                });
                this.showElementDeleteDialog(false);
                return false;
            }
            let parent_id = this.structuralElement.relationships.parent.data.id;
            this.showElementDeleteDialog(false);
            this.companionInfo({ info: this.$gettext('Lösche Seite und alle darunter liegenden Elemente.') });
            this.deleteStructuralElement({
                id: this.currentId,
                parentId: this.structuralElement.relationships.parent.data.id,
            })
            .then(response => {
                this.$router.push(parent_id);
                this.companionInfo({ info: this.$gettext('Die Seite wurde gelöscht.') });
            })
            .catch(error => {
                this.companionError({ info: this.$gettext('Die Seite konnte nicht gelöscht werden.') });
            });
        },
        async createElement() {
            const title = this.newChapterName; // this is the title of the new element
            const purpose = this.newChapterPurpose;
            let parent_id = this.currentId; // new page is descandant as default

            this.errorEmptyChapterName = title.trim();
            if (this.errorEmptyChapterName === '') {
                return;
            }
            if (this.newChapterParent === 'sibling') {
                parent_id = this.structuralElement.relationships.parent.data.id;
            }
            this.showElementAddDialog(false);
            this.createStructuralElementWithTemplate({
                attributes: {
                    title: title,
                    purpose: purpose,
                },
                templateId: this.newChapterTemplate ? this.newChapterTemplate.id : null,
                parentId: parent_id,
                currentId: this.currentId,
            })
            .then(() => {
                let newElement = this.$store.getters['courseware-structural-elements/lastCreated'];
                this.companionSuccess({
                    info:
                        this.$gettextInterpolate(
                            this.$gettext('Die Seite %{ pageTitle } wurde erfolgreich angelegt.'),
                            { pageTitle: newElement.attributes.title }
                        )
                });
            })
            .catch(e => {
                let errorMessage = this.$gettext('Es ist ein Fehler aufgetreten. Die Seite konnte nicht erstellt werden.');
                if (e.status === 403) {
                    errorMessage = this.$gettext('Die Seite konnte nicht erstellt werden. Sie haben nicht die notwendigen Schreibrechte.');
                }

                this.companionError({ info: errorMessage });
            });

            let newElement = this.lastCreatedElement;
            this.companionSuccess({
                info: this.$gettextInterpolate(
                    this.$gettext('Die Seite %{ pageTitle } wurde erfolgreich angelegt.'),
                    {pageTitle: newElement.attributes.title}
                )
            });
            this.newChapterName = '';
        },
        containerComponent(container) {
            return 'courseware-' + container.attributes['container-type'] + '-container';
        },
        setBookmark() {
            this.addBookmark(this.structuralElement);
            this.companionInfo({ info: this.$gettext('Das Lesezeichen wurde gesetzt.') });
        },
        updateReadApproval(approval) {
            this.currentElement.attributes['read-approval'] = approval;
        },
        updateWriteApproval(approval) {
            this.currentElement.attributes['write-approval'] = approval;
        },
        sendOerSuggestion() {
            this.suggestViaAction(this.currentElement, this.additionalText);
            this.updateShowSuggestOerDialog(false);
        },
        async createElementPublicLink() {
            const date = this.publicLink['expire-date'];
            const publicLink = {
                attributes: {
                    password: this.publicLink.password,
                    'expire-date': date === '' ? new Date(0).toISOString() : new Date(date).toISOString()
                },
                relationships: {
                    'structural-element': {
                        data: {
                            id: this.currentElement.id,
                            type: 'courseware-structural-elements'
                        }
                    }
                }
            }

            await this.createLink({ publicLink });
            this.companionSuccess({
                info: this.$gettext('Öffentlicher Link wurde angelegt. Unter Freigaben finden Sie alle Ihre öffentlichen Links.'),
            });
            this.closeLinkDialog();
        },
        closePublicLinkDialog() {
            this.publicLink = {
                passsword: '',
                'expire-date': ''
            };
            this.showElementPublicLinkDialog(false);
        },
        displayRemoveLockDialog() {
            this.showElementRemoveLockDialog(true);
        },
        async executeRemoveLock() {
            await this.unlockObject({ id: this.currentId, type: 'courseware-structural-elements' });
            await this.loadStructuralElement(this.currentElement.id);
            this.showElementRemoveLockDialog(false);
        },
        updateContainerList() {
            this.containerList = this.containers;
            const containerRefs = this.$refs.containers;
            for (let ref of containerRefs) {
                ref.initCurrentData();
            }
        },
        keyHandler(e, containerId) {
            switch (e.keyCode) {
                case 27: // esc
                    this.abortKeyboardSorting(containerId);
                    break;
                case 32: // space
                    e.preventDefault();
                    if (this.keyboardSelected) {
                        this.storeKeyboardSorting(containerId);
                    } else {
                        this.keyboardSelected = containerId;
                        const container = this.containerById({id: containerId});
                        const index = this.containerList.findIndex(c => c.id === container.id);
                        this.assistiveLive =
                            this.$gettextInterpolate(
                                this.$gettext('%{containerTitle} Abschnitt ausgewählt. Aktuelle Position in der Liste: %{pos} von %{listLength}. Drücken Sie die Aufwärts- und Abwärtspfeiltasten, um die Position zu ändern, die Leertaste zum Ablegen, die Escape-Taste zum Abbrechen.')
                                , {containerTitle: container.attributes.title, pos: index + 1, listLength: this.containerList.length}
                            );
                    }
                    break;
            }
            if (this.keyboardSelected) {
                switch (e.keyCode) {
                    case 9: //tab
                        this.abortKeyboardSorting(containerId);
                        break;
                    case 38: // up
                        e.preventDefault();
                        this.moveItemUp(containerId);
                        break;
                    case 40: // down
                        e.preventDefault();
                        this.moveItemDown(containerId);
                        break;
                }
            }
        },
        moveItemUp(containerId) {
            const currentIndex = this.containerList.findIndex(container => container.id === containerId);
            if (currentIndex !== 0) {
                const container = this.containerById({id: containerId});
                const newPos = currentIndex - 1;
                this.containerList.splice(newPos, 0, this.containerList.splice(currentIndex, 1)[0]);
                this.assistiveLive =
                    this.$gettextInterpolate(
                        this.$gettext('%{containerTitle} Abschnitt. Aktuelle Position in der Liste: %{pos} von %{listLength}.')
                        , {containerTitle: container.attributes.title, pos: newPos + 1, listLength: this.containerList.length}
                    );
            }
        },
        moveItemDown(containerId) {
            const currentIndex = this.containerList.findIndex(container => container.id === containerId);
            if (this.containerList.length - 1 > currentIndex) {
                const container = this.containerById({id: containerId});
                const newPos = currentIndex + 1;
                this.containerList.splice(newPos, 0, this.containerList.splice(currentIndex, 1)[0]);
                this.assistiveLive =
                    this.$gettextInterpolate(
                        this.$gettext('%{containerTitle} Abschnitt. Aktuelle Position in der Liste: %{pos} von %{listLength}.')
                        , {containerTitle: container.attributes.title, pos: newPos + 1, listLength: this.containerList.length}
                    );
            }
        },
        abortKeyboardSorting(containerId) {
            const container = this.containerById({id: containerId});
            this.keyboardSelected = null;
            this.assistiveLive =
                this.$gettextInterpolate(
                    this.$gettext('%{containerTitle} Abschnitt, Neuordnung abgebrochen.')
                    , {containerTitle: container.attributes.title}
                );
            this.$emit('select', this.currentId);
        },
        storeKeyboardSorting(containerId) {
            const container = this.containerById({id: containerId});
            const currentIndex = this.containerList.findIndex(container => container.id === containerId);
            this.keyboardSelected = null;
            this.assistiveLive =
                this.$gettextInterpolate(
                    this.$gettext('%{containerTitle} Abschnitt, abgelegt. Entgültige Position in der Liste: %{pos} von %{listLength}.')
                    , {containerTitle: container.attributes.title, pos: currentIndex + 1, listLength: this.containerList.length}
                );
            this.storeSort();
        }
    },
    created() {
        this.pluginManager.registerComponentsLocally(this);
    },

    watch: {
        async structuralElement() {
            this.setCurrentElementId(this.structuralElement.id);
            this.initCurrent();
            if (this.isTask) {
                this.loadTask({
                    taskId: this.structuralElement.relationships.task.data.id,
                });
            }

            if (this.isLink) {
                this.loadStructuralElement(this.structuralElement.attributes['target-id']);
            }
        },
        containers() {
            this.containerList = this.containers;
        },
        containerList() {
            if (this.keyboardSelected) {
                this.$nextTick(() => {
                    const selected = this.$refs['sortableHandle' + this.keyboardSelected][0];
                    selected.focus();
                    selected.scrollIntoView({behavior: "smooth", block: "center"});
                });
            }
        },
        consumeMode(newState) {
            this.consumModeTrap = newState;
        },
    },

    // this line provides all the components to courseware plugins
    provide: () => ({
        containerComponents: ContainerComponents,
        coursewarePluginComponents: CoursewarePluginComponents,
    }),
};
</script>
