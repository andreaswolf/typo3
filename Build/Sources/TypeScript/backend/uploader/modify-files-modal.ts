import { SeverityEnum } from '@typo3/backend/enum/severity';
import { default as Modal, Sizes as ModalSizes } from '@typo3/backend/modal';
import { html, TemplateResult } from 'lit';
import { CropScaleImages } from '@typo3/backend/uploader/modify-images';


export class ModifyFilesModal {
  private readonly ready: Promise<FileList|null>
  private resolve: (files: FileList|null) => void;

  private readonly availableModifiers: FileModifier[]

  constructor(private readonly files: FileList) {
    this.ready = new Promise((resolve) => {
      this.resolve = resolve;
    });

    this.availableModifiers = [
      // TODO check if
      new CropScaleImages()
    ];
  }

  public draw(): Promise<FileList|null> {
    const filesAndModifiers: [File, FileModifier[]][] = Array.from(this.files)
      .map((file) => {
        const modifiers = this.availableModifiers.filter((modifier) => modifier.canHandleFile(file));

        return [file, modifiers];
      })

    const modalContents = html`
        <p>Modify files before upload</p>
        <table class="table">
          <thead>
          <tr>
            <th></th>
            <th>Dateiname</th>
            <th>Actions</th>
          </tr>
          </thead>
          <tbody>
            ${filesAndModifiers.map(([file, modifiers]: [File, FileModifier[]]) =>
    html`<tr>
            <td>${this.renderPreview(file)}</td>
            <td>${file.name}</td>
            <th>${modifiers.map((modifier) => html`<button class="btn btn-default" @click="${() => modifier.show(file)}">${modifier.getActionName()}</button>`)}</th>
         </tr>`)}
          </tbody>
        </table>
      `;

    const modal = Modal.advanced({
      title: 'Modify files before upload',
      content: modalContents,
      severity: SeverityEnum.info,
      buttons: [
        {
          text: TYPO3.lang['file_upload.button.cancel'] || 'Cancel',
          active: true,
          btnClass: 'btn-default',
          name: 'cancel',
        },
        {
          text: 'Upload files',
          btnClass: 'btn-warning',
          name: 'continue',
        },
      ],
      additionalCssClasses: ['modal-inner-scroll'],
      size: ModalSizes.large,
    });
    modal.addEventListener('button.clicked', (e: Event): void => {
      const button = e.target as HTMLButtonElement;
      if (button.name === 'cancel') {
        this.resolve(null);
        modal.hideModal();
      } else if (button.name === 'continue') {
        this.resolve(this.files);
        modal.hideModal();
      }
    });

    return this.ready;
  }

  private renderPreview(file: File): TemplateResult {
    switch (file.type) {
      case 'image/jpeg':
      case 'image/png':
        return html`<img src="${URL.createObjectURL(file)}" />`
      default:
        return html`No preview available`;
    }
  }
}

export interface FileModifier {
  getActionName(): string

  getIdentifier(): string

  canHandleFile(file: File): boolean

  show(file: File): Promise<File>
}
