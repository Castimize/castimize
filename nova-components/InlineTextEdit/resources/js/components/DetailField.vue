<template>
    <PanelItem :field="field">
        <template #value>
            <InlineTextEditableField :field="field" :resourceName="resourceName" :width="750" :is-index="false" />
        </template>
    </PanelItem>
</template>

<script>
import InlineTextEditableField from './InlineTextEditableField';

export default {
    components: { InlineTextEditableField },
    props: ['resource', 'resourceName', 'resourceId', 'field'],
};
</script>
