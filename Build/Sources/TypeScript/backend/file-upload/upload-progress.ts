import '@typo3/backend/element/progress-bar-element';
import type { ProgressBarElement } from '@typo3/backend/element/progress-bar-element';
import type { SeverityEnum } from '@typo3/backend/enum/severity';

export class UploadProgress {
  public readonly indicator: HTMLTableCellElement;
  private readonly element: ProgressBarElement;

  constructor() {
    this.element = document.createElement('typo3-backend-progress-bar');

    this.indicator = document.createElement('td');
    this.indicator.classList.add('col-progress');
  }

  public finalize(severity: SeverityEnum, label: string | null = null) {
    this.element.value = 100;
    this.element.severity = severity;
    if (label !== null) {
      this.element.label = label;
    }
  }

  public update(percentage: number | null, label: string) {
    if (percentage !== null) {
      this.element.value = percentage;
    }
    this.element.label = label;
  }

  remove() {
    this.indicator?.remove();
  }
}
