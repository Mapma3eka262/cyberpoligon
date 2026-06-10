import './bootstrap';
import 'bootstrap';

// Инициализация компонентов Bootstrap
import { Tooltip, Toast, Popover } from 'bootstrap';

// Инициализация всех компонентов Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация тултипов
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new Tooltip(tooltipTriggerEl)
    });
    
    // Инициализация popover'ов
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new Popover(popoverTriggerEl)
    });
});

// Импорт Chart.js и Choices.js если нужно
import Chart from 'chart.js/auto';
import Choices from 'choices.js';

// Экспорт для глобального использования
window.Chart = Chart;
window.Choices = Choices;