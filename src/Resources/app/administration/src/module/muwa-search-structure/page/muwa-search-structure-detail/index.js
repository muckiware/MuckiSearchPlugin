import template from './muwa-search-structure-detail.html.twig';

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
                    mappings: null
                }
            },
            searchTerm: null,
            mappings: [],
            currencies: [],
            languages: [],
            customFieldSets: [],
            addMappingEnabled: false,
            systemRequiredFields: {
                type: Object,
                required: false,
                default() {
                    return {};
                }
            }
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

        customFieldSetRepository() {
            return this.repositoryFactory.create('custom_field_set');
        },

        indexStructureCriteria() {
            const criteria = new Criteria();
            criteria.addAssociation('translations');
            return criteria;
        },

        customFieldSetCriteria() {
            const criteria = new Criteria(1, 500);
            criteria.addAssociation('relations');
            criteria.addAssociation('customFields');

            return criteria;
        },

        getMappings() {

            console.log('this.indexStructure in getMappings()', this.indexStructure);
            console.log('this.indexStructure.translated.mappings', this.indexStructure.translated.mappings);
            return this.indexStructure.translated.mappings;
        },

        mappingColumns() {
            let columns = [
                {
                    property: 'entry',
                    label: 'sw-import-export.profile.mapping.entityLabel',
                    allowResize: true,
                    width: '300px',
                },
                {
                    property: 'defaultValue',
                    label: 'sw-import-export.profile.mapping.defaultValue',
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
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {

            this.getIndexStructure();
            this.getSalesChannels();
            this.loadMappings();
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
        },

        toggleAddMappingActionState(sourceEntity) {
            this.addMappingEnabled = !!sourceEntity;
        },

        onDeleteMapping(id) {

            this.mappings = this.mappings.filter((mapping) => {
                return mapping.id !== id;
            });

            this.loadMappings();
        },

        loadMappings() {

            // console.log('load mappings');
            // console.log('mappings', this.indexStructure.mappings);

            // if(this.indexStructure) {
            //
            //     if(this.indexStructure.mappings) {
            //
            //         this.indexStructure.mappings.forEach((mapping) => {
            //             if (!mapping.id) {
            //                 mapping.id = createId();
            //             }
            //             this.indexStructure.mappings.push(mapping);
            //         });
            //     }
            // }
        },

        onAddMapping() {

            console.log('onAddMapping()');
            console.log('this.mappings', this.mappings)

            if(this.mappings.length >= 1) {
                this.mappings.forEach(currentMapping => { currentMapping.position += 1; });
            } else {
                console.log('this.mappings is empty');
            }

            console.log('this.mappings is empty');
            this.mappings.unshift({ id: createId(), key: '', mappedKey: '', position: 0 });

            // this.loadMappings();
        },

        isDefaultValueTextFieldDisabled(item) {
            // return this.profile.systemDefault || !item.useDefaultValue;
        }
    }
});