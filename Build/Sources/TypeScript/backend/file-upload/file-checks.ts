import DragUploader, { FileQueueItem } from '@typo3/backend/drag-uploader';

export interface CheckFileMiddleware {
  checkFile(queueItem: FileQueueItem): void
}

export class CheckFileSize implements CheckFileMiddleware {
  constructor(private readonly maxFileSize: number) {
  }

  checkFile(queueItem: FileQueueItem): void {
    if (this.maxFileSize > 0 && queueItem.file.size > this.maxFileSize) {
      queueItem.markAsFailed(
        TYPO3.lang['file_upload.maxFileSizeExceeded']
          .replace(/\{0\}/g, queueItem.file.name)
          .replace(/\{1\}/g, DragUploader.fileSizeAsString(this.maxFileSize))
      );
    }
  }
}

export class CheckFileDenyPattern implements CheckFileMiddleware {
  constructor(private readonly fileDenyPattern: RegExp | null) {
  }

  checkFile(queueItem: FileQueueItem): void {
    if (this.fileDenyPattern && queueItem.file.name.match(this.fileDenyPattern)) {
      queueItem.markAsFailed(TYPO3.lang['file_upload.fileNotAllowed'].replace(/\{0\}/g, queueItem.file.name));
    }
  }
}

export class CheckAllowedDisallowedExtensions implements CheckFileMiddleware {
  constructor(private readonly filesExtensionsAllowed: string, private readonly filesExtensionsDisallowed: string) {
  }

  checkFile(queueItem: FileQueueItem): void {
    const extension: string = queueItem.file.name.split('.').pop();

    if (!this.checkAllowedExtensions(extension)) {
      queueItem.markAsFailed(TYPO3.lang['file_upload.fileExtensionExpected'].replace(/\{0\}/g, this.filesExtensionsAllowed));
    } else if (!this.checkDisallowedExtensions(extension)) {
      queueItem.markAsFailed(TYPO3.lang['file_upload.fileExtensionDisallowed'].replace(/\{0\}/g, this.filesExtensionsDisallowed));
    }
  }

  public checkAllowedExtensions(extension: string): boolean {
    if (!this.filesExtensionsAllowed) {
      return false;
    }
    const allowed = this.filesExtensionsAllowed.split(',');

    return !allowed.includes(extension.toLowerCase());
  }

  public checkDisallowedExtensions(extension: string): boolean {
    if (!this.filesExtensionsDisallowed) {
      return false;
    }
    const disallowed = this.filesExtensionsDisallowed.split(',');

    return disallowed.includes(extension.toLowerCase());
  }
}
