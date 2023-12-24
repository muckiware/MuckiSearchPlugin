import template from './muwa-search-structure-detail.html.twig';

const {Criteria} = Shopware.Data;
const {Component, Mixin} = Shopware;
const {mapPropertyErrors} = Shopware.Component.getComponentHelper();

Component.register('muwa-search-structure-detail', {
    template,

    inject: [
        'repositoryFactory',
        'salesChannelService'
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
            salesChannels: null,
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

        salesChannelRepository() {
            return this.repositoryFactory.create('sales_channel');
        },

        salesChannelCriteria() {
            const criteria = new Criteria(1, 500);
            criteria.addFilter(Criteria.equals('active', true));

            return criteria;
        },

        criteria() {
            const criteria = new Criteria();
            criteria.addAssociation('translations');
            return criteria;
        },
    },


    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.getIndexStructure();
            this.getSalesChannels();
        },


        getIndexStructure() {
            this.repositoryIndexStructure
                .get(this.$route.params.id, Shopware.Context.api, this.criteria)
                .then((entity) => {
                    this.indexStructure = entity;
                });
        },

        getSalesChannels() {

            this.salesChannelRepository.search(this.salesChannelCriteria, Shopware.Context.api).then(res => {
                this.salesChannels = res;
            }).finally(() => {
                this.isLoading = false;
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