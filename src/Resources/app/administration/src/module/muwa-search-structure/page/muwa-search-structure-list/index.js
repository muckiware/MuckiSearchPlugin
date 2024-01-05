const {Criteria} = Shopware.Data;
const {Component} = Shopware;

import template from './muwa-search-structure-list.html.twig';
import './muwa-search-structure-list.scss';

Component.register('muwa-search-structure-list', {
    template,

    inject: [
        'repositoryFactory'
    ],

    data() {
        return {
            isLoading: true,
            indexStructure: null,
            indicesStructure: null,
            httpClient: null,
            requestUrlRemove: '/_action/muwa/search/remove-indices',
            requestUrlIndices: '/_action/muwa/search/indices'
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        repositorySearchStructure() {
            return this.repositoryFactory.create('muwa_index_structure');
        },

        columns() {
            return [{
                property: 'name',
                label: this.$tc('muwa-search-structure.list.nameLabel'),
                routerLink: 'muwa.search.structure.detail',
                allowResize: true,
                primary: true,
            },{
                property: 'active',
                label: this.$tc('muwa-search-structure.list.activeLabel'),
                allowResize: true,
            },{
                property: 'entity',
                label: this.$tc('muwa-search-structure.list.entityLabel'),
                allowResize: true,
            },{
                property: 'createDate',
                label: this.$tc('muwa-search-structure.list.createDateLabel'),
                allowResize: true,
            },{
                property: 'updateDate',
                label: this.$tc('muwa-search-structure.list.updateDateLabel'),
                allowResize: true,
            }];
        },

        indicesColumns() {
            return [{
                property: 'index',
                label: this.$tc('muwa-search-structure.list.nameLabel'),
                routerLink: 'muwa.search.structure.detail',
                allowResize: true,
                primary: true,
            },{
                property: 'health',
                label: this.$tc('muwa-search-structure.list.healthLabel'),
                allowResize: true,
            },{
                property: 'status',
                label: this.$tc('muwa-search-structure.list.statusLabel'),
                allowResize: true,
            },{
                property: 'pri',
                label: this.$tc('muwa-search-structure.list.primariesLabel'),
                allowResize: true,
            },{
                property: 'rep',
                label: this.$tc('muwa-search-structure.list.replicasLabel'),
                allowResize: true,
            },{
                property: 'docscount',
                label: this.$tc('muwa-search-structure.list.docsCountLabel'),
                allowResize: true,
            },{
                property: 'storesize',
                label: this.$tc('muwa-search-structure.list.storageSizeLabel'),
                allowResize: true,
            },{
                property: 'docsdeleted',
                label: this.$tc('muwa-search-structure.list.docsDeletedLabel'),
                allowResize: true,
            }];
        },

        tab() {
            return this.$route.params.tab || 'structureList';
        },
    },

    created() {

        this.httpClient = Shopware.Application.getContainer('init').httpClient;
        this.createdComponent();
    },

    methods: {

        getIndices() {

            const apiHeader = this.getApiHeader();
            this.httpClient.get(this.requestUrlIndices, apiHeader).then((response) => {

                this.indicesStructure = response.data;
                this.indicesStructure.forEach((indices, key) => {

                    Object.entries(indices).forEach(([indicesKey, indicesValue]) => {

                        if (indicesKey.includes('.')) {
                            this.indicesStructure[key][indicesKey.replaceAll('.','')] = indicesValue;
                        }
                    });
                });
            });
        },
        createdComponent() {

            // if (!this.$route.params.tab) {
            //     this.$router.push({ name: 'muwa-search-structure-list', params: { tab: 'structureList' } });
            // }
            this.getList();
            this.getIndices();
        },

        getList() {

            this.isLoading = true;
            let criteria = new Criteria();

            this.repositorySearchStructure.search(criteria, Shopware.Context.api).then((result) => {
                this.indexStructure = result;
                this.isLoading = false;
            });
        },

        async deleteIndices(item) {

            this.isLoading = true;
            const apiHeader = this.getApiHeader();
            item.languageId = Shopware.Context.api.languageId;
            await this.httpClient.post(this.requestUrlRemove, item, {headers: apiHeader});

            this.createdComponent();
        },

        onChangeLanguage(languageId) {
            Shopware.State.commit('context/setApiLanguageId', languageId);
            this.createdComponent();
        },

        getApiHeader() {

            return {
                Accept: 'application/vnd.api+json',
                Authorization: `Bearer ${ Shopware.Context.api.authToken.access }`,
                'Content-Type': 'application/json'
            }
        }
    }
});