import { DateTime } from 'luxon';
import { SeverityEnum } from '../enum/severity';
import { default as Modal, type ModalElement, Sizes as ModalSizes } from '../modal';
import '@typo3/backend/element/icon-element';
import RegularEvent from '@typo3/core/event/regular-event';
import FileUploadHandler from '@typo3/backend/file-upload/file-upload-handler';
import DragUploader, { Action, type UploadedFile } from '@typo3/backend/drag-uploader';

export interface FileConflict {
  original: UploadedFile;
  uploaded: File;
  action: Action;
}

export default class FilenameConflictModal {
  constructor(
    private readonly irreObjectUid: string,
    private readonly defaultAction: Action,
    private readonly uploadHandler: FileUploadHandler,
    private readonly questions: FileConflict[]
  ) {}

  /**
   * Renders the modal for existing files
   */
  public draw(): void {
    const amountOfItems = Object.keys(this.questions).length;
    if (amountOfItems === 0) {
      return;
    }

    const $modalContent = document.createElement('div');
    let htmlContent = `
      <p>${TYPO3.lang['file_upload.existingfiles.description']}</p>
      <table class="table">
        <thead>
          <tr>
            <th></th>
            <th>${TYPO3.lang['file_upload.header.originalFile']}</th>
            <th>${TYPO3.lang['file_upload.header.uploadedFile']}</th>
            <th>${TYPO3.lang['file_upload.header.action']}</th>
          </tr>
        </thead>
        <tbody>
    `;
    for (let i = 0; i < amountOfItems; ++i) {
      const $record = `
        <tr>
          <td>
  ${this.questions[i].original.thumbUrl !== ''
    ? `<img src="${this.questions[i].original.thumbUrl}" height="40" />`
    : this.questions[i].original.icon}
          </td>
          <td>
            ${this.questions[i].original.name} (${DragUploader.fileSizeAsString(this.questions[i].original.size)})<br />
            ${DateTime.fromSeconds(this.questions[i].original.mtime).toLocaleString(DateTime.DATETIME_MED)}
          </td>
          <td>
            ${this.questions[i].uploaded.name} (${DragUploader.fileSizeAsString(this.questions[i].uploaded.size)})<br />
            ${DateTime.fromMillis(this.questions[i].uploaded.lastModified).toLocaleString(DateTime.DATETIME_MED)}
          </td>
          <td>
            <select class="form-select t3js-actions" data-override="${i}">
              ${this.irreObjectUid ? `<option value="${Action.USE_EXISTING}">${TYPO3.lang['file_upload.actions.use_existing']}</option>` : ''}
              <option value="${Action.SKIP}" ${this.defaultAction === Action.SKIP ? 'selected' : ''}>${TYPO3.lang['file_upload.actions.skip']}</option>
              <option value="${Action.RENAME}" ${this.defaultAction === Action.RENAME ? 'selected' : ''}>${TYPO3.lang['file_upload.actions.rename']}</option>
              <option value="${Action.OVERRIDE}" ${this.defaultAction === Action.OVERRIDE ? 'selected' : ''}>${TYPO3.lang['file_upload.actions.override']}</option>
            </select>
          </td>
        </tr>
      `;
      htmlContent += $record;
    }

    htmlContent += '</tbody></table>';
    $modalContent.innerHTML = htmlContent;

    const modal = Modal.advanced({
      title: TYPO3.lang['file_upload.existingfiles.title'],
      content: $modalContent,
      severity: SeverityEnum.warning,
      buttons: [
        {
          text: TYPO3.lang['file_upload.button.cancel'] || 'Cancel',
          active: true,
          btnClass: 'btn-default',
          name: 'cancel',
        },
        {
          text: TYPO3.lang['file_upload.button.continue'] || 'Continue with selected actions',
          btnClass: 'btn-warning',
          name: 'continue',
        },
      ],
      additionalCssClasses: ['modal-inner-scroll'],
      size: ModalSizes.large,
      callback: (modal: ModalElement): void => {
        const modalFooter = modal.querySelector('.modal-footer');

        const allActionLabel = document.createElement('label');
        allActionLabel.textContent = TYPO3.lang['file_upload.actions.all.label'];

        const allActionSelect = document.createElement('span');
        allActionSelect.innerHTML = `
          <select class="form-select t3js-actions-all">
            <option value="">${TYPO3.lang['file_upload.actions.all.empty']}</option>
            ${this.irreObjectUid ? `<option value="${Action.USE_EXISTING}">${TYPO3.lang['file_upload.actions.all.use_existing']}</option>` : ''}
            <option value="${Action.SKIP}" ${this.defaultAction === Action.SKIP ? 'selected' : ''}>${TYPO3.lang['file_upload.actions.all.skip']}</option>
            <option value="${Action.RENAME}" ${this.defaultAction === Action.RENAME ? 'selected' : ''}>${TYPO3.lang['file_upload.actions.all.rename']}</option>
            <option value="${Action.OVERRIDE}" ${this.defaultAction === Action.OVERRIDE ? 'selected' : ''}>${TYPO3.lang['file_upload.actions.all.override']}</option>
          </select>
        `;

        modalFooter.prepend(allActionLabel, allActionSelect);
      }
    });

    new RegularEvent('change', (event: Event, target: HTMLSelectElement) => {
      if (target.value !== '') {
        // mass action was selected, apply action to every file
        for (const select of modal.querySelectorAll('.t3js-actions') as NodeListOf<HTMLSelectElement>) {
          const index = parseInt(select.dataset.override, 10);
          select.value = target.value;
          select.disabled = true;
          this.questions[index].action = <Action>select.value;
        }
      } else {
        modal.querySelectorAll('.t3js-actions').forEach((select: HTMLSelectElement) => select.disabled = false);
      }
    }).delegateTo(modal, '.t3js-actions-all');

    new RegularEvent('change', (event: Event) => {
      const actionSelect = event.target as HTMLSelectElement,
        index = parseInt(actionSelect.dataset.override, 10);
      this.questions[index].action = <Action>actionSelect.value;
    }).delegateTo(modal, '.t3js-actions');

    modal.addEventListener('button.clicked', (e: Event): void => {
      const button = e.target as HTMLButtonElement;
      if (button.name === 'cancel') {
        Modal.dismiss();
      } else if (button.name === 'continue') {
        for (const fileInfo of this.questions) {
          if (fileInfo.action === Action.USE_EXISTING) {
            DragUploader.addFileToIrre(
              this.irreObjectUid,
              fileInfo.original,
            );
          } else if (fileInfo.action !== Action.SKIP) {
            this.uploadHandler.processFile(fileInfo.uploaded, fileInfo.action);
          }
        }
        modal.hideModal();
      }
    });
  }
}
