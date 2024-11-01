<template>
    <div
        :class="`nova-inline-text-field-index text-${field.textAlign}${editing ? ' -editing' : 'w-full'}`"
        :style="editing ? `width:${this.divWidth}px;` : ''"
        @click.stop="e => !e.target.classList.contains('inline-icon')"
        @dblclick.stop.capture="startEditing"
    >
        <template v-if="!editing && !isIndex">
            <EditIcon @click.stop.capture="startEditing" />

            <div :style="contentStyle" v-if="!hasValue"><p>&mdash;</p></div>
            <span :style="contentStyle" v-else class="whitespace-no-wrap">{{ value }}</span>
        </template>
        <template v-else-if="!editing && isIndex">
            <EditIcon @click.stop.capture="startEditing" />

            <div :style="contentStyle" v-if="!hasValue"><p>&mdash;</p></div>
            <div :style="contentStyle"
                 v-else-if="this.fieldValue.length > 50"
                 v-tooltip="value"
            >
                {{ truncateString(value) }}
            </div>
            <span :style="contentStyle" v-else class="whitespace-no-wrap">{{ value }}</span>
        </template>

        <template v-else>
            <input
                ref="input"
                v-model="fieldValue"
                @keypress="onInputKeyPress"
                type="text"
                :disabled="loading"
                class="form-control form-input form-input-bordered o1-w-full w-[300px]"
                @click.stop.capture="true"
            />

            <ConfirmIcon @click.stop.capture="!loading ? updateFieldValue() : void 0" />
            <CancelIcon @click.stop.capture="cancelEditing" />
        </template>
    </div>
</template>

<script>
import EditIcon from '../icons/EditIcon';
import CancelIcon from '../icons/CancelIcon';
import ConfirmIcon from '../icons/ConfirmIcon';

export default {
    props: ['resourceName', 'field', 'width', 'isIndex'],
    components: { EditIcon, CancelIcon, ConfirmIcon },

    data: () => ({
        editing: false,
        loading: false,
        fieldValue: '',
        divWidth: 300,
    }),

    mounted() {
        this.fieldValue = this.value;
        if (this.width) {
            this.divWidth = this.width;
        }
    },

    methods: {
        onInputKeyPress(e) {
            if (e.which === 13) this.updateFieldValue();
        },

        startEditing() {
            if (this.editing) return;
            this.fieldValue = typeof this.value === 'number' ? this.value || '' : (this.value || '').trim();
            this.editing = true;

            this.$nextTick(() => this.$refs.input && this.$refs.input.focus());
        },

        cancelEditing() {
            if (this.loading) return;
            this.editing = false;
        },

        truncateString (value, length = 50) {
            return value.length <= length ?
                value : value.substring(0, length) + "...";
        },

        async updateFieldValue() {
            this.loading = true;
            try {
                await Nova.request().post(
                    '/nova-vendor/inline-text-edit/update',
                    {
                        id: this.field.resourceId,
                        model: this.field.modelClass,
                        column: this.field.attribute,
                        value: this.fieldValue,
                    }
                );
                this.editing = false;
                this.field.value = this.fieldValue;

                Nova.success(
                    this.__('The :field has been updated!', {
                        field: this.field.attribute,
                    })
                );
            } catch (e) {
                console.error(e);
                Nova.error(this.__('There was a problem submitting the form.'));
            }
            this.loading = false;
        },
    },

    computed: {
        hasValue() {
            return this.value !== null;
        },

        value() {
            return this.field.value || this.field.displayedAs;
        },

        contentStyle() {
            return {
                maxWidth: this.field.maxWidth ? `${this.field.maxWidth}px` : void 0,
            };
        },
    },
};
</script>

<style lang="scss">
.nova-inline-text-field-index {
    position: relative;
    display: flex;
    align-items: center;

    > *:not(input) {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    > .edit-icon {
        height: 24px;
        width: 24px;
        margin-right: 2px;
        margin-bottom: 1px;
        flex-shrink: 0;
        min-width: 24px;
        cursor: pointer;
        padding: 4px;

        &:hover {
            fill: rgba(var(--colors-primary-500));
        }
    }

    > .cancel-icon,
    > .confirm-icon {
        height: 24px;
        width: 24px;
        cursor: pointer;
        margin-left: 6px;
        opacity: 0.6;

        &:hover {
            opacity: 1;
        }
    }

    > .cancel-icon {
        fill: #f87171;
    }

    > .confirm-icon {
        fill: #4ade80;
    }

    > .form-input {
        margin-right: 8px;
        max-width: 50vw;

        height: 1.75rem;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
        font-size: 14px;
        max-height: calc(100% - 2px);
    }
}
</style>
