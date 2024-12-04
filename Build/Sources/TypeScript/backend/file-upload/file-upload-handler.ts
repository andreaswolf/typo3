import DragUploader, { Action, FileQueueItem } from '@typo3/backend/drag-uploader';
import type { CheckFileMiddleware } from '@typo3/backend/file-upload/file-checks';

export enum QueueItemState {
  CHECKING = 'checking',
  FAILED = 'failed',
}

export default class FileUploadHandler {
  constructor(
    private readonly middlewares: CheckFileMiddleware[],
    private readonly dragUploader: DragUploader
  ) {
  }

  public processFile(file: File, action: Action) {
    const queueItem = new FileQueueItem(this.dragUploader, file, action);

    for (let i = 0; i < this.middlewares.length; ++i) {
      this.middlewares[i].checkFile(queueItem);
      if (queueItem.state === QueueItemState.FAILED) {
        return;
      }
    }

    queueItem.uploadFile();
  }
}
