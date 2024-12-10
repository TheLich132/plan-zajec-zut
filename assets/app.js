import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

import 'bootstrap/dist/css/bootstrap.min.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

// FullCalendar Imports
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import plLocale from '@fullcalendar/core/locales/pl';
// FullCalendar Init
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar'); // Szuka elementu z id=calendar

    let calendar;

    // Ustawianie ekranu startowego z linku
    function setFormValuesFromURL() {
        const urlParams = new URLSearchParams(window.location.search);

        // Ustawianie wartości dla pierwszego formularza
        document.querySelector('#firstScheduleForm #lecturer').value = urlParams.get('lecturer1') || '';
        document.querySelector('#firstScheduleForm #room').value = urlParams.get('room1') || '';
        document.querySelector('#firstScheduleForm #subject').value = urlParams.get('subject1') || '';
        document.querySelector('#firstScheduleForm #group').value = urlParams.get('group1') || '';
        document.querySelector('#firstScheduleForm #albumNumber').value = urlParams.get('albumNumber1') || '';

        // Ustawianie wartości dla drugiego formularza
        document.querySelector('#secondScheduleForm #lecturer').value = urlParams.get('lecturer2') || '';
        document.querySelector('#secondScheduleForm #room').value = urlParams.get('room2') || '';
        document.querySelector('#secondScheduleForm #subject').value = urlParams.get('subject2') || '';
        document.querySelector('#secondScheduleForm #group').value = urlParams.get('group2') || '';
        document.querySelector('#secondScheduleForm #albumNumber').value = urlParams.get('albumNumber2') || '';
    }

    function fetchAndRenderEvent(){
        // Pobieranie wartości z fromularzy
        const lecturer1 = document.querySelector('#firstScheduleForm #lecturer').value;
        const room1 = document.querySelector('#firstScheduleForm #room').value;
        const subject1 = document.querySelector('#firstScheduleForm #subject').value;
        const group1 = document.querySelector('#firstScheduleForm #group').value;
        const albumNumber1 = document.querySelector('#firstScheduleForm #albumNumber').value;

        const lecturer2 = document.querySelector('#secondScheduleForm #lecturer').value;
        const room2 = document.querySelector('#secondScheduleForm #room').value;
        const subject2 = document.querySelector('#secondScheduleForm #subject').value;
        const group2 = document.querySelector('#secondScheduleForm #group').value;
        const albumNumber2 = document.querySelector('#secondScheduleForm #albumNumber').value;

        const queryParams = new URLSearchParams({
            lecturer1,
            room1,
            subject1,
            group1,
            albumNumber1,
            lecturer2,
            room2,
            subject2,
            group2,
            albumNumber2,
        });

        const eventsUrl = `/api/lessons?${queryParams.toString()}`;
        window.history.replaceState(null, '', `?${queryParams.toString()}`);

        if(calendar){
            calendar.destroy();
        }

        calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
            initialView: 'timeGridWeek',
            locale: plLocale,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,semesterView',
            },
            views: {
                timeGridWeek:{ // Usuwa "cały dzień"
                    allDaySlot: false,
                    slotMinTime:'07:00:00',
                    slotMaxTime:'21:00:00',
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
            events: eventsUrl,
            eventDataTransform:function (eventData){
                switch (eventData.type){
                    case 'wykład':
                        eventData.backgroundColor = '#009999'
                        break;
                    case 'laboratorium':
                        eventData.backgroundColor = '#009900'
                        break;
                    case 'audytoryjne':
                        eventData.backgroundColor = '#3399ff'
                        break;
                    case 'projekt':
                        eventData.backgroundColor = '#994c00'
                        break;
                    case 'seminarium':
                        eventData.backgroundColor = '#7f0077'
                        break;
                    case 'odwołane':
                        eventData.backgroundColor = '#a0a0a0'
                        break;

                }
                // kolor ramki dla planu 2
                if (eventData.plan === '2') {
                    eventData.borderColor = '#ff0000';
                    eventData.borderWidth = '5px';
                }
                return eventData;
            },

            eventDidMount: function (info){
                const tooltip = new bootstrap.Tooltip(info.el, {
                    title: info.event.extendedProps.description,
                    placement: 'top',
                    trigger: 'hover',
                });
            },
            height : 600,
        })
        calendar.render();
    }

    setFormValuesFromURL();
    fetchAndRenderEvent();

    const searchButtons = document.querySelectorAll('form button[type="submit"]');

    searchButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            fetchAndRenderEvent();
        });
    });
});