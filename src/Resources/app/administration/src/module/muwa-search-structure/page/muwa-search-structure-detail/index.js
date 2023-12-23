import template from './muwa-search-structure-detail.html.twig';

const {Criteria} = Shopware.Data;
const {Component, Mixin} = Shopware;
const {mapPropertyErrors} = Shopware.Component.getComponentHelper();

Component.register('muwa-search-structure-detail', {
    template,

    inject: [
        'repositoryFactory'
    ],

    mixins: [
        Mixin.getByName('notification')
    ],


    metaInfo() {

        return {
            title: this.$createTitle()
        };
    },

    data() {

        return {

            isLoading: false,
            processSuccess: false,

            indexStructure: {
                type: null,
                label: null,
                content: null,
            }
        };
    },

    computed: {

        repositoryIndexStructure() {
            return this.repositoryFactory.create('muwa_index_structure');
        },

        ...mapPropertyErrors('indexStructure', [
            'type',
            'label',
            'content'
        ]),
    },


    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.getIndexStructure();
        },


        getIndexStructure() {
            this.repositoryIndexStructure.get(this.$route.params.id, Shopware.Context.api).then((entity) => {
                this.indexStructure = entity;
            });
        },

        onClickSave() {

            this.isLoading = true;
            this.repositoryIndexStructure.save(this.indexStructure, Shopware.Context.api).then(() => {
                this.isLoading = false;
                this.getIndexStructure();
                this.createNotificationSuccess({
                    title: this.$tc('muwa-search-structure.general.saveSuccessAlertTitle'),
                    message: this.$tc('muwa-search-structure.general.saveSuccessAlertMessage')
                });
            }).catch((exception) => {
                this.isLoading = false;

                this.createNotificationError({
                    title: this.$tc('muwa-search-structure.create.errorTitle'),
                    message: exception
                });
            });
        },

        saveFinish() {
            this.processSuccess = false;
        },

        onChangeLanguage(languageId) {
            Shopware.State.commit('context/setApiLanguageId', languageId);
            this.createdComponent();
        }
    }
});