/**
 * @class Ext.calendar.data.EventMappings
 * @extends Object
 * A simple object that provides the field definitions for Event records so that they can be easily overridden.
 */
Ext.ns('Ext.calendar.data');

Ext.calendar.data.EventMappings = {
    EventId: {
        name: 'EventID',
        mapping: 'id',
        type: 'int'
    },
    CalendarId: {
        name: 'ColorID',
        mapping: 'cid',
        type: 'int'
    },
    Title: {
        name: 'EventTitle',
        mapping: 'title',
        type: 'string'
    },
    StartDate: {
        name: 'StartDate',
        mapping: 'start',
        dateFormat: 'Ymd'
    },
    EndDate: {
        name: 'EndDate',
        mapping: 'end',
        dateFormat: 'Ymd'
    },
    Notes: {
        name: 'notes',
        mapping: 'notes',
        type: 'string'
    },
    IsAllDay: {
        name: 'IsAllDay',
        mapping: 'ad',
        type: 'boolean'
    },
    Reminder: {
        name: 'reminder',
        mapping: 'rem',
        type: 'string'
    },
    IsNew: {
        name: 'IsNew',
        mapping: 'n',
        type: 'boolean'
    }
};
