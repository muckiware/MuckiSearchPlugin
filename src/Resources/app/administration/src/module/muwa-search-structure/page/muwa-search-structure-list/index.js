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
                property: 'createDate',
                label: this.$tc('muwa-search-structure.list.createDateLabel'),
                allowResize: true,
            },{
                property: 'updateDate',
                label: this.$tc('muwa-search-structure.list.updateDateLabel'),
                allowResize: true,
            }];
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.getList();

        },

        getList() {
            this.isLoading = true;
            let criteria = new Criteria();

            this.repositorySearchStructure.search(criteria, Shopware.Context.api).then((result) => {
                this.indexStructure = result;
                this.isLoading = false;
            });
        },

        deleteProductTabs(item) {

            let that = this;
            that.repositorySearchStructure.delete(item.id, Shopware.Context.api).then(() => {
                this.createdComponent();
            });
        },

        onChangeLanguage(languageId) {
            Shopware.State.commit('context/setApiLanguageId', languageId);
            this.createdComponent();
        }
    }
});