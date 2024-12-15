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

let colorInputs;

const colorConfig = {
    wykład: '#009999',
    laboratorium: '#009900',
    audytoryjne: '#3399ff',
    projekt: '#994c00',
    seminarium: '#7f0077',
    odwołane: '#a0a0a0',
    konsultacje: '#ff8800',
    brak_formy: '#424242',
};

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
    function getSemesterViewConfig() {
        const today = new Date();
        const year = today.getFullYear();

        // Semestr zimowy
        if (today.getMonth() >= 9 || today.getMonth() <= 1) {
            return {
                title: 'Semestr',
                start: new Date(`${year}-10-01`),
                end: new Date(`${year + 1}-02-28`),
                duration: { months: 5 }
            };
        } else {
            // Semestr letni
            return {
                title: 'Semestr',
                start: new Date(`${year}-03-01`),
                end: new Date(`${year}-09-30`),
                duration: { months: 7 }
            };
        }
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

        const semesterConfig = getSemesterViewConfig();

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
                semesterView: { // Widok semestralny
                    type: 'dayGrid',
                    duration: semesterConfig.duration,
                    buttonText: semesterConfig.title,
                    visibleRange: {
                        start: semesterConfig.start,
                        end: semesterConfig.end,
                    },
                },
            },
            events: eventsUrl,
            eventDataTransform: function (eventData) {
                if (eventData === null) {
                    return {};
                }
                eventData.backgroundColor = colorConfig[eventData.type] || '#ff8800';

                eventData.classNames = [eventData.type];

                if (!eventData.title) {
                    eventData.title = 'Nieznane zajęcia';
                }
                if (!eventData.start) {
                    eventData.start = new Date();
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

        //updateLegend();
        calendar.on('datesSet', () => updateLegend(calendar));
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

    function updateEventColors() {
        calendar.getEvents().forEach((event) => {
            const eventType = event.extendedProps.type;
            const backgroundColor = colorConfig[eventType] || '#ffffff';
            event.setProp('backgroundColor', backgroundColor);
        });
        updateLegend();
    }

    colorInputs = document.querySelectorAll('input[type="color"]');

    colorInputs.forEach((input) => {
        const colorType = input.name;
        input.value = colorConfig[colorType] || '#ffffff';

        input.addEventListener('change', (event) => {
            colorConfig[colorType] = event.target.value;
            updateEventColors();
        });
    });

    function updateLegend() {
        const legendContainer = document.getElementById('legendContent');
        if (!legendContainer) return;

        // Clear existing legend content
        legendContainer.innerHTML = '';

        // Get visible date range
        const view = calendar.view;
        const visibleStart = view.activeStart;
        const visibleEnd = view.activeEnd;

        // Get all events within the visible range
        const visibleEvents = calendar.getEvents().filter(event => {
            return event.start >= visibleStart && event.start < visibleEnd;
        });

        // Extract unique event types from visible events
        const eventTypes = [...new Set(visibleEvents.map(event => event.extendedProps.type))];

        // Map event types to colors using the predefined `colorConfig`
        const legendItems = eventTypes.map(type => {
            const color = colorConfig[type] || '#ffffff'; // Default to white if type is not in colorConfig
            return { type, color };
        });

        // Create legend items
        legendItems.forEach(item => {
            const legendItem = document.createElement('div');
            legendItem.className = 'legendItem d-flex align-items-center';

            const colorBox = document.createElement('input');
            colorBox.className = 'me-2';
            colorBox.type = 'color';
            colorBox.value = item.color;
            colorBox.readOnly = true;

            const labelText = document.createElement('label');
            labelText.className = 'me-4';
            labelText.textContent = item.type;

            legendItem.appendChild(colorBox);
            legendItem.appendChild(labelText);
            legendContainer.appendChild(legendItem);
        });

        // Show a message if no events are visible
        if (legendItems.length === 0) {
            const noEventsMessage = document.createElement('p');
            noEventsMessage.className = 'mb-0';
            noEventsMessage.textContent = 'Brak wydarzeń';
            noEventsMessage.style.fontStyle = 'italic';
            legendContainer.appendChild(noEventsMessage);
        }

        // Update color inputs
        colorInputs = document.querySelectorAll('input[type="color"]');
    }
});