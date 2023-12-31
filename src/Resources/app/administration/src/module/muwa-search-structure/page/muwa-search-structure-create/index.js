import template from './muwa-search-structure-create.html.twig';

const { Criteria } = Shopware.Data;
const { Component, Mixin } = Shopware;
const { mapPropertyErrors } = Shopware.Component.getComponentHelper();

Component.register('muwa-search-structure-create', {
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
            salesChannels: null,
            indexStructure: null,
            isLoading: false,
            processSuccess: false,
            httpClient: null,
            requestUrlMapping: '/_action/muwa/search/default-product-mappings',
            requestUrlSetting: '/_action/muwa/search/default-indices-settings'
        };
    },

    computed: {

        repositorySearchStructure() {
            return this.repositoryFactory.create('muwa_index_structure');
        },
        ...mapPropertyErrors('salesChannel', ['name']),
        ...mapPropertyErrors('indexStructure', ['mappings']),

        salesChannelRepository() {
            return this.repositoryFactory.create('sales_channel');
        },

        salesChannelCriteria() {
            const criteria = new Criteria(1, 500);
            criteria.addFilter(Criteria.equals('active', true));

            return criteria;
        },
    },

    created() {

        this.httpClient = Shopware.Application.getContainer('init').httpClient;

        if (!Shopware.State.getters['context/isSystemDefaultLanguage']) {
            Shopware.State.commit('context/resetLanguageToDefault');
        }

        this.createdComponent();

    },

    methods: {
        createdComponent() {
            this.getSearchStructure();
            this.getSalesChannels();
        },

        getSearchStructure() {
            this.indexStructure = this.repositorySearchStructure.create(Shopware.Context.api);
        },

        getSalesChannels() {

           this.salesChannelRepository.search(this.salesChannelCriteria, Shopware.Context.api).then(res => {
                this.salesChannels = res;
            }).finally(() => {
                this.isLoading = false;
            });
        },

        async onClickSave() {

            this.isLoading = true;
            let apiHeader = this.getApiHeader();

            try {

                let defaultMappingsResponse = await this.httpClient.get(this.requestUrlMapping, apiHeader);
                let defaultSettingsResponse = await this.httpClient.get(this.requestUrlSetting, apiHeader);

                this.indexStructure.mappings = defaultMappingsResponse.data;
                this.indexStructure.settings = defaultSettingsResponse.data;
                await this.repositorySearchStructure.save(this.indexStructure, Shopware.Context.api);

            } catch(exception) {

                this.isLoading = false;
                this.createNotificationError({
                    title: this.$tc('muwa-search-structure.create.errorTitle'),
                    message: exception
                });
            }

            this.isLoading = false;
            this.createNotificationSuccess({
                title: this.$tc('muwa-search-structure.general.saveSuccessAlertTitle'),
                message: this.$tc('muwa-search-structure.general.saveSuccessAlertMessage')
            });
            this.$router.push({name: 'muwa.search.structure.detail', params: { id: this.indexStructure.id }});
        },

        onChangeLanguage(languageId) {
            Shopware.State.commit('context/setApiLanguageId', languageId);
        },

        saveFinish() {
            this.processSuccess = false;
        },

        setSalesChannel() {
            console.log('setSalesChannel');
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