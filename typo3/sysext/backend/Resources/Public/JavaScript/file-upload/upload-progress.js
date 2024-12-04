/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
import"@typo3/backend/element/progress-bar-element.js";class l{constructor(){this.element=document.createElement("typo3-backend-progress-bar"),this.indicator=document.createElement("td"),this.indicator.classList.add("col-progress")}finalize(e,t=null){this.element.value=100,this.element.severity=e,t!==null&&(this.element.label=t)}update(e,t){e!==null&&(this.element.value=e),this.element.label=t}remove(){this.indicator?.remove()}}export{l as UploadProgress};
