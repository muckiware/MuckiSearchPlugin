const {Module} = Shopware;

import './page/muwa-search-structure-list';
import './page/muwa-search-structure-create';
import './page/muwa-search-structure-detail';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';


Module.register('muwa-search-structure', {
    type: 'plugin',
    name: 'muwaSearchStructure',
    title: 'muwa-search-structure.general.mainMenuLabel',
    description: 'muwa-search-structure.general.description',
    color: '#4d1e00',
    icon: 'regular-search',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },
    routes: {
        index: {
            component: 'muwa-search-structure-list',
            path: 'list',
            meta: {
                parentPath: 'sw.settings.index.plugins'
            }
        },
        create: {
            component: 'muwa-search-structure-create',
            path: 'create',
            meta: {
                parentPath: 'muwa.search.structure.index'
            }
        },
        detail: {
            component: 'muwa-search-structure-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'muwa.search.structure.index'
            }
        }
    },
    settingsItem: [
        {
            name: 'muwa-search-structure-list',
            to: 'muwa.search.structure.index',
            group: 'plugins',
            icon: 'regular-search',
            label: 'muwa-search-structure.general.mainMenuLabel',
        }
    ]
});