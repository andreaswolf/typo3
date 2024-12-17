import { FileModifier } from '@typo3/backend/uploader/modify-files-modal';
import { default as Modal, Sizes as ModalSizes } from '@typo3/backend/modal';
import { html } from 'lit';
import { SeverityEnum } from '@typo3/backend/enum/severity';

export class CropScaleImages implements FileModifier {
  private static readonly SUPPORTED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'bmp'];

  canHandleFile(file: File): boolean {
    const extension = file.name.split('.').pop();

    return CropScaleImages.SUPPORTED_IMAGE_EXTENSIONS.indexOf(extension) > -1;
  }

  getButtonLabel(): string {
    return 'Crop/Scale';
  }

  getIdentifier(): string {
    return 'crop-scale-images';
  }

  async show(file: File): Promise<File> {
    return (new CropScaleModal(file)).draw();
  }
}

class CropScaleModal {
  private readonly ready: Promise<File>
  private resolve: (files: File) => void;

  constructor(private readonly file: File) {
    this.ready = new Promise((resolve) => {
      this.resolve = resolve;
    });
  }

  public draw(): Promise<File> {

    const modal = Modal.advanced({
      title: 'Crop / scale image',
      content: html`<p>Test</p>`,
      severity: SeverityEnum.info,
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
    });
    modal.addEventListener('button.clicked', (e: Event): void => {
      const button = e.target as HTMLButtonElement;
      if (button.name === 'cancel') {
        this.resolve(this.file);
        modal.hideModal();
      } else if (button.name === 'continue') {
        // TODO resolve new file
        setTimeout(() => this.resolve(this.file), 1000);
        modal.hideModal();
      }
    });

    return this.ready;
  }
}
