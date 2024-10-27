<template>
    <DefaultField
        :field="currentField"
        :errors="errors"
        :show-help-text="showHelpText"
    >
        <template #field>
            <multiselect v-model="values"
                         :dusk="field.attribute"
                         :options="currentField.options"
                         :multiple="true"
                         :close-on-select="false"
                         :clear-on-select="false"
                         :preserve-search="true"
                         :placeholder="placeholder"
                         class="w-full"
                         label="label"
                         track-by="value"
                         :preselect-first="false"
                         :show-labels="true"
                         @select="handleChange"
                         @remove="handleRemove"
            >
            </multiselect>
            <div>
                <OverviewTable
                    :overview-headers="currentField.overviewHeaders"
                    :overview-items="items"
                    :overview-footer="footer"
                    :should-show-column-borders="currentField.shouldShowColumnBorders"
                />
            </div>
        </template>
    </DefaultField>
</template>

<script>
import {DependentFormField, HandlesValidationErrors} from 'laravel-nova';
import Multiselect from 'vue-multiselect';
import OverviewTable from "./OverviewTable.vue";

export default {
    mixins: [DependentFormField, HandlesValidationErrors],

    data: () => ({
        allOptions: [],
        selectedGroup: '',
        values: [],
        items: [],
        selectedItems: [],
        footer: [],
    }),

    mounted() {
        this.allOptions = this.currentField.options
    },

    methods: {
        addTag(newTag) {
            const tag = {
                label: newTag,
                value: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
            }
            this.currentField.options.push(tag)
            this.values.push(tag)
        },

        /**
         * Provide a function that fills a passed FormData object with the
         * field's internal value attribute. Here we are forcing there to be a
         * value sent to the server instead of the default behavior of
         * `this.value || ''` to avoid loose-comparison issues if the keys
         * are truthy or falsey
         */
        fill(formData) {
            this.fillIfVisible(formData, this.fieldAttribute, this.value ?? '')
        },

        handleChange(option, id) {
            this.currentField.options = this.allOptions;
            this.currentField.options = this.currentField.options.filter((o) => o.group === option.group);
            this.selectedItems.push(option.value);
            this.getItem(option.value);
            this.getFooter();

            if (this.field) {
                let ids = this.values.map((val, index) => {
                    return val.value;
                });
                console.log(ids);
                this.value = ids;
                this.emitFieldValueChange(this.fieldAttribute, ids);
            }
        },

        handleRemove (removedOption, id) {
            this.items = this.items.filter(function (e) {
                return e.id != removedOption.value
            });
            this.selectedItems = this.selectedItems.filter(function (e) {
                return e != removedOption.value
            });
            console.log(this.selectedItems);
            if (this.selectedItems.length > 0) {
                this.getFooter();
            } else {
                this.currentField.options = this.allOptions;
                this.resetFooter();
            }

            if (this.field) {
                let ids = this.values.map((val, index) => {
                    return val.value;
                });
                this.value = ids;
                this.emitFieldValueChange(this.fieldAttribute, ids);
            }
        },

        async getItem(id) {
            const {
                data: {item},
            } = await Nova.request().post(
                '/nova-vendor/select-with-overview/get-overview-item',
                {
                    id: id
                }
            );
            this.items.push(item);
        },

        async getFooter() {
            const {
                data: {footer},
            } = await Nova.request().post(
                '/nova-vendor/select-with-overview/get-overview-footer',
                {
                    ids: this.selectedItems
                }
            );

            this.footer = footer;
        },

        resetFooter() {
            this.footer = [];
        },
    },

    computed: {
        /**
         * Return the placeholder text for the field.
         */
        placeholder() {
            return this.currentField.placeholder || this.__('Choose an option')
        },

        /**
         * Determine if the field has a non-empty value.
         */
        hasValue() {
            return Boolean(
                !(this.value === undefined || this.value === null || this.value === '')
            )
        },
    },

    components: {
        OverviewTable,
        Multiselect
    },
}
</script>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style>
.dark .multiselect .multiselect__tags {
    background-color: #0f172a;
    border-color: #334155;
    color: #94a3b8;
}
.dark tfoot th {
    background-color: rgba(var(--colors-gray-800)) !important;
    border: rgba(var(--colors-gray-800)) !important;
    color: #94a3b8 !important;
}
tfoot th {
    text-align: left !important;
}
</style>
