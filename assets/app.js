import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

import 'bootstrap/dist/css/bootstrap.min.css';

// FullCalendar Imports
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import plLocale from '@fullcalendar/core/locales/pl';

// Log do konsoli
console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

// FullCalendar Init
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar'); // Szuka elementu z id=calendar

    if (calendarEl) {
        const calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
            initialView: 'timeGridWeek', // Domyślny
            locale: plLocale,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,semesterView',
            },
            views: {
                timeGridWeek:{ // Usuwa "cały dzień"
                    allDaySlot: false,
                },
                timeGridDay:{
                    allDaySlot: false,
                },
                semesterView: { // Roboczo: widok semestralny
                    type: 'dayGrid',
                    duration: { months: 4 },
                    buttonText: 'Semestr',
                },
            },
            editable: false,
            events: [
                { title: 'Początek semestru', start: '2024-10-01' },
                { title: 'Sesja', start: '2025-02-01', end: '2025-02-15' },
            ],
            height:750,
        });

        calendar.render(); // Wyświetla kalendarz
    }
});
