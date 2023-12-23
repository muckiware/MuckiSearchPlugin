import template from './muwa-search-structure-create.html.twig';

const {Criteria} = Shopware.Data;
const {Component, Mixin} = Shopware;
const {mapPropertyErrors} = Shopware.Component.getComponentHelper();

Component.register('muwa-search-structure-create', {
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
            indexStructure: null,
            isLoading: false,
            processSuccess: false
        };
    },

    computed: {
        repositorySearchStructure() {
            return this.repositoryFactory.create('muwa_index_structure');
        },

        ...mapPropertyErrors('indexStructure', [
            'type',
            'label',
            'content'
        ]),

    },

    created() {

        if (!Shopware.State.getters['context/isSystemDefaultLanguage']) {
            Shopware.State.commit('context/resetLanguageToDefault');
        }

        this.createdComponent();

    },

    methods: {
        createdComponent() {
            this.getSearchStructure();
        },

        getSearchStructure() {
            this.indexStructure = this.repositorySearchStructure.create(Shopware.Context.api);
        },

        onClickSave() {

            this.isLoading = true;
            this.repositorySearchStructure.save(this.indexStructure, Shopware.Context.api).then(() => {

                this.isLoading = false;
                this.createNotificationSuccess({
                    title: this.$tc('muwa-search-structure.general.saveSuccessAlertTitle'),
                    message: this.$tc('muwa-search-structure.general.saveSuccessAlertMessage')
                });
                this.$router.push({name: 'muwa.search.structure.detail', params: { id: this.indexStructure.id }});

            }).catch((exception) => {

                this.isLoading = false;
                this.createNotificationError({
                    title: this.$tc('muwa-search-structure.create.errorTitle'),
                    message: exception
                });
            });
        },

        onChangeLanguage(languageId) {
            Shopware.State.commit('context/setApiLanguageId', languageId);
        },

        saveFinish() {
            this.processSuccess = false;
        }
    }
});