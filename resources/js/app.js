
import Alpine from 'alpinejs';
import './theme.js';
import './echo.js';
import './sidebar.js';
import './form-enhancements.js';
import { registerSessionGuard } from './session-guard.js';

window.Alpine = Alpine;

registerSessionGuard(Alpine);

Alpine.start();
