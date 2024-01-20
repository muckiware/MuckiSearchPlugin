import template from './muwa-search-structure-detail.html.twig';
import './muwa-search-structure-detail.html.scss';

const {Criteria} = Shopware.Data;
const {Component, Mixin} = Shopware;
const {mapPropertyErrors} = Shopware.Component.getComponentHelper();
const { debounce, createId, object: { cloneDeep } } = Shopware.Utils;

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
                mappings: null,
                translated: {
                    mappings: null,
                    settings: null
                },
            },
            searchTerm: null,
            mappings: [],
            settings: [],
            currencies: [],
            languages: [],
            seoUrls: [],
            customFieldSets: [],
            addMappingEnabled: false,
            addSettingEnabled: false,
            systemRequiredFields: {
                type: Object,
                required: false,
                default() {
                    return {};
                }
            },
            options: [],
            httpClient: null,
            dataTypeOptions: null,
            requestUrlCreateIndices: '/_action/muwa/search/create-indices',
            requestUrlSaveMappingsSettings: '/_action/muwa/search/save-mappings-settings'
        };
    },

    computed: {

        repositoryIndexStructure() {
            return this.repositoryFactory.create('muwa_index_structure');
        },

        ...mapPropertyErrors('indexStructure', ['translated',]),

        salesChannelRepository() {
            return this.repositoryFactory.create('sales_channel');
        },

        salesChannelCriteria() {
            const criteria = new Criteria(1, 50);
            criteria.addFilter(Criteria.equals('active', true));

            return criteria;
        },

        languageRepository() {
            return this.repositoryFactory.create('language');
        },

        currencyRepository() {
            return this.repositoryFactory.create('currency');
        },

        customFieldSetRepository() {
            return this.repositoryFactory.create('custom_field_set');
        },

        seoUrlRepository() {
            return this.repositoryFactory.create('seo_url');
        },

        indexStructureCriteria() {
            const criteria = new Criteria();
            criteria.addAssociation('translations');
            return criteria;
        },

        languageCriteria() {
            const criteria = new Criteria(1, 500);
            criteria.addAssociation('locale');

            return criteria;
        },

        currencyCriteria() {
            return new Criteria(1, 500);
        },

        customFieldSetCriteria() {
            const criteria = new Criteria(1, 500);
            criteria.addAssociation('relations');
            criteria.addAssociation('customFields');

            return criteria;
        },

        seoUrlCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addGroupField('seoPathInfo');

            return criteria;
        },

        getMappings() {
            return this.indexStructure.translated.mappings;
        },

        getSettings() {
            return this.indexStructure.translated.settings;
        },

        mappingColumns() {
            let columns = [
                {
                    property: 'entry',
                    label: 'muwa-search-structure.mappingList.entityLabel',
                    allowResize: true,
                    width: '400px',
                },
                {
                    property: 'dataType',
                    label: 'muwa-search-structure.mappingList.inputDataTypeLabel',
                    allowResize: true,
                    width: '170px',
                },
                {
                    property: 'highlighting',
                    label: 'muwa-search-structure.mappingList.inputHighlightingLabel',
                    allowResize: true,
                    width: '70px',
                }
            ];

            return columns;
        },

        settingColumns() {
            let columns = [
                {
                    property: 'settingKey',
                    label: 'muwa-search-structure.settingList.settingKey',
                    allowResize: true,
                    width: '300px',
                },
                {
                    property: 'settingValue',
                    label: 'muwa-search-structure.settingList.settingValue',
                    allowResize: true,
                    width: '300px',
                }
            ];

            return columns;
        },

        mappingsExist() {

            if(this.indexStructure.translated.mappings) {
                return this.indexStructure.translated.mappings.length > 0;
            }
            return false;
        },

        settingsExist() {

            if(this.indexStructure.translated.settings) {
                return this.indexStructure.translated.settings.length > 0;
            }
            return false;
        },

        sortingConditionConcatenation() {
            return null;
        },
    },

    created() {

        this.httpClient = Shopware.Application.getContainer('init').httpClient;
        this.createdComponent();
    },

    methods: {

        createdComponent() {

            this.languageRepository.search(this.languageCriteria).then(languages => {
                this.languages = languages;
                this.languages.push({ locale: { code: 'DEFAULT' } });
            });

            this.currencyRepository.search(this.currencyCriteria).then(currencies => {
                this.currencies = currencies;
                this.currencies.push({ isoCode: 'DEFAULT' });
            });

            this.customFieldSetRepository.search(this.customFieldSetCriteria).then((customFieldSets) => {
                this.customFieldSets = customFieldSets;
            });

            this.seoUrlRepository.search(this.seoUrlCriteria).then((seoUrls) => {
                this.seoUrls = seoUrls;
            });

            this.getIndexStructure();
            this.getSalesChannels();
            this.loadMappings();
            this.loadSettings();
            this.getDataTypeOptions();
        },


        getIndexStructure() {
            this.repositoryIndexStructure
                .get(this.$route.params.id, Shopware.Context.api, this.indexStructureCriteria)
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

        async onClickSave() {

            this.isLoading = true;
            let apiHeader = this.getApiHeader();
            this.indexStructure.languageId = Shopware.Context.api.languageId;
            this.indexStructure.translated.languageId = Shopware.Context.api.languageId;

            try {

                await this.repositoryIndexStructure.save(this.indexStructure, Shopware.Context.api, this.indexStructureCriteria);
                await this.httpClient.post(this.requestUrlSaveMappingsSettings, this.indexStructure, {headers: apiHeader});
                await this.httpClient.post(this.requestUrlCreateIndices, this.indexStructure, {headers: apiHeader});

                this.isLoading = false;
                this.getIndexStructure();
                this.createNotificationSuccess({
                    title: this.$tc('muwa-search-structure.general.saveSuccessAlertTitle'),
                    message: this.$tc('muwa-search-structure.general.saveSuccessAlertMessage')
                });

            } catch (exception) {

                this.isLoading = false;
                this.createNotificationError({
                    title: this.$tc('muwa-search-structure.create.errorTitle'),
                    message: exception
                });
            }
        },

        getDataTypeOptions() {

            this.httpClient.get(
                '/_action/muwa/server/mapping-input-data-types',
                this.getApiHeader()
            ).then((response) => {
                this.dataTypeOptions = response.data;
            });
        },

        saveFinish() {
            this.processSuccess = false;
        },

        onChangeLanguage(languageId) {
            Shopware.State.commit('context/setApiLanguageId', languageId);
            this.createdComponent();
        },

        onDeleteMapping(id) {

            this.indexStructure.translated.mappings = this.indexStructure.translated.mappings.filter((mapping) => {
                return mapping.id !== id;
            });

            this.loadMappings();
        },

        onDeleteSetting(id) {

            this.indexStructure.translated.settings = this.indexStructure.translated.settings.filter((setting) => {
                return setting.id !== id;
            });

            this.loadSettings();
        },

        loadMappings() {

            if(this.indexStructure) {

                if(this.indexStructure.translated.mappings) {

                    this.indexStructure.translated.mappings.forEach((mapping) => {
                        if (!mapping.id) {
                            mapping.id = createId();
                        }
                    });
                }
            }
        },

        loadSettings() {

            if(this.indexStructure) {

                if(this.indexStructure.translated.settings) {

                    this.indexStructure.translated.settings.forEach((setting) => {
                        if (!setting.id) {
                            setting.id = createId();
                        }
                    });
                }
            }
        },

        onAddMapping() {

            this.indexStructure.translated.mappings.forEach(currentMapping => { currentMapping.position += 1; });
            this.indexStructure.translated.mappings.unshift({
                id: createId(),
                isDefault: false,
                dataType: '',
                key: '',
                mappedKey: '',
                position: 0
            });

            this.loadMappings();
        },

        onAddSetting() {

            this.indexStructure.translated.settings.forEach(currentSetting => { currentSetting.position += 1; });
            this.indexStructure.translated.settings.unshift({
                id: createId(),
                isDefault: false,
                key: '',
                mappedKey: '',
                settingKey: '',
                settingValue: '',
                position: 0
            });

            this.loadSettings();
        },

        isDefaultValueTextFieldDisabled(item) {
            // return this.profile.systemDefault || !item.useDefaultValue;
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