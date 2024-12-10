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

    function fetchAndRenderEvent(){
        // Pobieranie wartości z fromularzy
        const lecturer = document.getElementById('lecturer').value;
        const room = document.getElementById('room').value;
        const subject = document.getElementById('subject').value;
        const group = document.getElementById('group').value;
        const albumNumber = document.getElementById('albumNumber').value;


        const queryParams = new URLSearchParams({
           lecturer,
           room,
           subject,
           group,
           albumNumber,
        });

        const eventsUrl = `/api/lessons?${queryParams.toString()}`;


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

    fetchAndRenderEvent();

    const searchButton = document.querySelector('form button[type="submit"]');
    searchButton.addEventListener('click',function (e){
       e.preventDefault();
       fetchAndRenderEvent();
    });
});